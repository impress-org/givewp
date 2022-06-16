<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;
use Give\Helpers\Date;

/**
 * Class DonationFormsResponseData
 *
 * @since 2.21.0
 */
class DonationFormsResponseData implements Arrayable
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $status;
    /**
     * @var array|false
     */
    public $goal;
    /**
     * @var int
     */
    public $donations;
    /**
     * @var string
     */
    public $amount;
    /**
     * @var string
     */
    public $revenue;
    /**
     * @var string
     */
    public $datetime;
    /**
     * @var string
     */
    public $shortcode;
    /**
     * @var string
     */
    public $permalink;
    /**
     * @var string
     */
    public $edit;


    /**
     * Convert from object to DonationForm
     *
     * @param object $form
     *
     * @since 2.21.0
     */
    public static function fromObject($form): self
    {
        $self = new static();

        $self->id = $form->id;
        $self->name = $form->title;
        $self->status = $form->status;
        $self->goal = $form->goalEnabled === 'enabled' ? $self->getGoal($form->id) : false;
        $self->donations = give()->donationFormsRepository->getFormDonationsCount($form->id);
        $self->amount = $self->getFormAmount($form);
        $self->revenue = $self->formatAmount($form->revenue ?? '');
        $self->datetime = Date::getDateTime($form->createdAt);
        $self->shortcode = sprintf('[give_form id="%d"]', $form->id);
        $self->permalink = html_entity_decode(get_permalink($form->id));
        $self->edit = html_entity_decode(get_edit_post_link($form->id));

        return $self;
    }

    /**
     * Convert DTO to array
     *
     * @since 2.21.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param int $formId
     *
     * @return array
     *
     * @since 2.21.0
     */
    private function getGoal(int $formId): array
    {
        $goal = give_goal_progress_stats($formId);

        $getFormatFromGoal = static function ($goal) {
            switch ($goal['format']) {
                case 'donation':
                    return _n('donation', 'donations', $goal['raw_goal'], 'give');

                case 'donors':
                    return _n('donor', 'donors', $goal['raw_goal'], 'give');

                case 'amount':
                    return __('amount', 'give');

                case 'percentage':
                    return __('percentage', 'give');

                default:
                    return '';
            }
        };

        return [
            'actual' => html_entity_decode($goal['actual']),
            'goal' => html_entity_decode($goal['goal']),
            'progress' => html_entity_decode($goal['progress']),
            'format' => $getFormatFromGoal($goal)
        ];
    }

    /**
     * @param object $form
     *
     * @return string
     *
     * @since 2.21.0
     */
    private function getFormAmount($form): string
    {
        $donationLevels = unserialize($form->donationLevels, ['allowed_classes' => false]);

        if (
            is_array($donationLevels)
            && $amount = array_column($donationLevels, '_give_amount')
        ) {
            return $this->formatAmount(min($amount)) . ' - ' . $this->formatAmount(max($amount));
        }

        return $this->formatAmount($form->setPrice ?? '');
    }

    /**
     * @param string $amount
     *
     * @return string
     *
     * @since 2.21.0
     */
    private function formatAmount(string $amount): string
    {
        return html_entity_decode(give_currency_filter(give_format_amount($amount)));
    }
}
