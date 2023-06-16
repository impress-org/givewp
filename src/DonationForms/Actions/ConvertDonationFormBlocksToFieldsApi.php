<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\Rules\AuthenticationRule;
use Give\DonationForms\Rules\GatewayRule;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Authentication;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\DonationSummary;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Name;
use Give\Framework\FieldsAPI\Paragraph;
use Give\Framework\FieldsAPI\PaymentGateways;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use WP_User;

/**
 * @since 0.1.0
 */
class ConvertDonationFormBlocksToFieldsApi
{
    /**
     * @var int
     */
    protected $formId;
    /**
     * @var string
     */
    protected $currency;

    /**
     * @since 0.4.0 conditionally append blocks if block has inner blocks. Add blockIndex to inner blocks node converter.
     * @since 0.3.3 conditionally append blocks if block has inner blocks
     * @since 0.1.0
     *
     * @throws TypeNotSupported|NameCollisionException
     */
    public function __invoke(BlockCollection $blocks, int $formId): Form
    {
        $this->formId = $formId;
        $this->currency = give_get_currency($formId);

        $form = new Form('donation-form');
        $blockIndex = 0;
        foreach ($blocks->getBlocks() as $block) {
            $blockIndex++;
            $section = $this->convertTopLevelBlockToSection($block, $blockIndex);

            if ($block->innerBlocks) {
                $innerBlocks = $block->innerBlocks->getBlocks();
                $nodes = array_filter(
                    array_map([$this, 'convertInnerBlockToNode'], $innerBlocks, array_keys($innerBlocks)),
                    static function ($node) {
                        return $node instanceof Node;
                    }
                );

                $section->append(
                    ...$nodes
                );
            }

            $form->append($section);
        }

        return $form;
    }

    /**
     * @since 0.3.3 remove innerBlock appending
     * @since 0.1.0
     */
    protected function convertTopLevelBlockToSection(BlockModel $block, int $blockIndex): Section
    {
        return Section::make($block->getShortName() . '-' . $blockIndex)
            ->label($block->getAttribute('title'))
            ->description($block->getAttribute('description'));
    }

    /**
     * @since 0.1.0
     *
     * @throws EmptyNameException|NameCollisionException
     *
     * @return Node|null
     */
    protected function convertInnerBlockToNode(BlockModel $block, int $blockIndex)
    {
        $node = $this->createNodeFromBlockWithUniqueAttributes($block, $blockIndex);

        if ($node instanceof Node) {
            return $this->mapGenericBlockAttributesToNode($node, $block);
        }

        return null;
    }

    /**
     * @since 0.4.0 add blockIndex for unique field names, add filter `givewp_donation_form_block_render` filters
     * @since 0.1.0
     *
     * @return Node|null
     * @throws NameCollisionException
     *
     * @throws EmptyNameException
     */
    protected function createNodeFromBlockWithUniqueAttributes(BlockModel $block, int $blockIndex)
    {
        $blockName = $block->name;

        switch ($blockName) {
            case "givewp/donation-amount":
                return $this->createNodeFromAmountBlock($block);

            case "givewp/donor-name":
                return $this->createNodeFromDonorNameBlock($block);

            case "givewp/paragraph":
                return Paragraph::make($block->getShortName() . '-' . $blockIndex)
                    ->content($block->getAttribute('content'));

            case "givewp/email":
                return Email::make('email')
                    ->emailTag('email')
                    ->rules('required', 'email')
                    ->tap(function ($email) use ($block) {
                        if(is_user_logged_in()) {
                            $email->defaultValue(wp_get_current_user()->user_email);
                        }
                        return $email;
                    });

            case "givewp/payment-gateways":
                $defaultGatewayId = give(DonationFormRepository::class)->getDefaultEnabledGatewayId($this->formId);

                return PaymentGateways::make('gatewayId')
                    ->rules(new GatewayRule())
                    ->required()
                    ->defaultValue($defaultGatewayId);

            case "givewp/donation-summary":
                return DonationSummary::make('donation-summary');

            case "givewp/company":
                return Text::make('company');

            case "givewp/text":
                return Text::make(
                    $block->hasAttribute('fieldName') ?
                        $block->getAttribute('fieldName') :
                        $block->getShortName() . '-' . $blockIndex
                )->storeAsDonorMeta(
                    $block->hasAttribute('storeAsDonorMeta') ? $block->getAttribute('storeAsDonorMeta') : false
                );

            case "givewp/login":
                return Authentication::make('login')
                    ->required($block->getAttribute('required'))
                    ->isAuthenticated(is_user_logged_in())
                    ->lostPasswordUrl(wp_lostpassword_url())
                    ->loginRedirect($block->getAttribute('loginRedirect'))
                    ->loginRedirectUrl(wp_login_url())
                    ->loginNotice($block->getAttribute('loginNotice'))
                    ->loginConfirmation($block->getAttribute('loginConfirmation'))
                    ->tapNode('login', function($field) use ($block) {
                        if($block->getAttribute('required')) {
                            if (!is_user_logged_in()) {
                                $field->required();
                            }

                            $field->rules(new AuthenticationRule());
                        }
                    });

            default:
                $customField = apply_filters(
                    'givewp_donation_form_block_render',
                    null,
                    $block,
                    $blockIndex,
                    $this->formId
                );

                $customField = apply_filters(
                    "givewp_donation_form_block_render_{$blockName}",
                    $customField,
                    $block,
                    $blockIndex,
                    $this->formId
                );

                if ($customField instanceof Node) {
                    return $customField;
                }

                return null;
        }
    }

    /**
     * @since 0.1.0
     */
    protected function createNodeFromDonorNameBlock(BlockModel $block): Node
    {
        return Name::make('name')->tap(function ($group) use ($block) {
            $group->getNodeByName('firstName')
                ->label($block->getAttribute('firstNameLabel'))
                ->placeholder($block->getAttribute('firstNamePlaceholder'))
                ->required($block->getAttribute('requireFirstName'))
                ->rules('required', 'max:255');

            $group->getNodeByName('lastName')
                ->label($block->getAttribute('lastNameLabel'))
                ->placeholder($block->getAttribute('lastNamePlaceholder'))
                ->required($block->getAttribute('requireLastName'))
                ->rules('max:255');

            if (is_user_logged_in()) {
                /** @var WP_User $user */
                $user = wp_get_current_user();

                if ($user->first_name) {
                    $group->getNodeByName('firstName')->defaultValue($user->first_name);
                }

                if ($user->last_name) {
                    $group->getNodeByName('lastName')->defaultValue($user->last_name);
                }
            }


            if ($block->hasAttribute('showHonorific') && $block->getAttribute('showHonorific') === true) {
                $group->getNodeByName('honorific')
                    ->label('Title')
                    ->options(...$block->getAttribute('honorifics'));
            } else {
                $group->remove('honorific');
            }
        });
    }

    /**
     * @since 0.2.0
     *
     * @throws NameCollisionException
     * @throws EmptyNameException
     */
    protected function createNodeFromAmountBlock(BlockModel $block): Node
    {
        return (new ConvertDonationAmountBlockToFieldsApi())($block, $this->currency);
    }

    /**
     * @since 0.1.0
     */
    protected function mapGenericBlockAttributesToNode(Node $node, BlockModel $block): Node
    {
        if ('field' === $node->getNodeType()) {
            // Label
            if ($block->hasAttribute('label')) {
                $node->label($block->getAttribute('label'));
            }

            // Placeholder
            if ($block->hasAttribute('placeholder')) {
                $node->placeholder($block->getAttribute('placeholder'));
            }

            // Required
            if ($block->hasAttribute('isRequired')) {
                $node->required($block->getAttribute('isRequired'));
            }

            if ($block->hasAttribute('displayInAdmin') && $block->getAttribute('displayInAdmin')) {
                $node->showInAdmin($block->getAttribute('displayInAdmin'));
            }

            if ($block->hasAttribute('displayInReceipt') && $block->getAttribute('displayInReceipt')) {
                $node->showInReceipt($block->getAttribute('displayInReceipt'));
            }
        }

        return $node;
    }
}
