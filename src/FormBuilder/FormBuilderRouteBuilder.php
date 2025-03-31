<?php

namespace Give\FormBuilder;

use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Helpers\Language;

class FormBuilderRouteBuilder
{
    const SLUG = 'givewp-form-builder';

    /**
     * @var int|string
     */
    protected $donationFormID;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @since 3.22.0 Add locale support
     * @since 3.0.0
     *
     * @param  int|string  $donationFormID
     */
    protected function __construct($donationFormID, string $locale = '')
    {
        $this->donationFormID = $donationFormID;
        $this->locale = ! empty($locale) ? $locale : Language::getLocale();
    }

    /**
     * @since 3.22.0 Add locale support
     * @since 3.0.0
     */
    public static function makeCreateFormRoute(string $locale = ''): self
    {
        // @todo Refactor create route so as not to mix types for $donationFormID.
        return new self('new', $locale);
    }

    /**
     * @since 3.22.0 Add locale support
     * @since 3.0.0
     */
    public static function makeEditFormRoute(int $donationFormID, string $locale = ''): self
    {
        return new self($donationFormID, $locale);
    }

    /**
     * @since 3.0.0
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @since 4.0.0 add p2p param
     * @since 3.22.0 Add locale support
     * @since 3.0.0
     */
    public function getUrl(): string
    {
        $queryArgs = [
            'post_type' => 'give_forms',
            'page' => self::SLUG,
            'donationFormID' => $this->donationFormID,
            'locale' => $this->locale,
        ];

        if (isset($_GET['campaignId'])) {
            $queryArgs['campaignId'] = $_GET['campaignId'];
        }

        // Check if it's P2P form
        $form = DB::table('give_campaigns')
            ->where('form_id', $this->donationFormID)
            ->where('campaign_type', CampaignType::CORE, '!=')
            ->get();

        if ($form) {
            $queryArgs['p2p'] = true;
        }

        return add_query_arg(
            [
                $queryArgs,
            ],
            admin_url('edit.php')
        );
    }

    /**
     * @since 3.0.0
     */
    public static function isRoute(): bool
    {
        return isset($_GET['post_type'], $_GET['page']) && $_GET['post_type'] === 'give_forms' && $_GET['page'] === self::SLUG;
    }
}
