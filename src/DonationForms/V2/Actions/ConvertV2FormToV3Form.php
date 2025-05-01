<?php

namespace Give\DonationForms\V2\Actions;

use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\FormDesigns\MultiStepFormDesign\MultiStepFormDesign;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\DonationForms\Models\DonationForm as V3DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys as V3DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus as V3DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalType;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Framework\Blocks\BlockCollection;

/**
 * @since 4.2.0
 */
class ConvertV2FormToV3Form
{
    public $form;

    /**
     * @since 4.2.0
     */
    public function __construct(DonationForm $form)
    {
        $this->form = $form;
    }

    /**
     * @since 4.2.0
     */
    public function __invoke()
    {
        return new V3DonationForm([
            'id' => $this->form->id,
            'title' => $this->form->title,
            'status' => $this->convertStatus(),
            'settings' => $this->convertSettings(),
            'blocks' => $this->convertBlocks(),
        ]);
    }

    /**
     * @since 4.2.0
     */
    public function convertSettings()
    {
        $v3Settings = give_get_meta($this->form->id, V3DonationFormMetaKeys::SETTINGS, true);

        if ($v3Settings) {
            return FormSettings::fromJson($v3Settings);
        }

        return FormSettings::fromArray([
            'formTitle' => $this->form->title,
            'designId' => $this->convertDesignId(),
            'goalType' => $this->convertGoalType()->getValue(),
            'enableDonationGoal' => $this->convertGoalEnabled(),
            'goalAmount' => $this->convertGoalAmount(),
            'enableAutoClose' => $this->convertAutoClose(),
        ]);
    }

    /**
     * @since 4.2.0
     */
    public function convertAutoClose()
    {
        return give_is_setting_enabled(give_get_meta($this->form->id, '_give_close_form_when_goal_achieved', 'disabled'));
    }

    /**
     * @since 4.2.0
     */
    public function convertBlocks()
    {
        $v3Blocks = give_get_meta($this->form->id, V3DonationFormMetaKeys::FIELDS, true);

        if ($v3Blocks) {
            return BlockCollection::fromJson($v3Blocks);
        }

        return (new GenerateDefaultDonationFormBlockCollection())();
    }

    /**
     * @since 4.2.0
     */
    public function convertStatus(): V3DonationFormStatus
    {
        switch ($this->form->status) {
            case DonationFormStatus::DRAFT():
                return V3DonationFormStatus::DRAFT();
            case DonationFormStatus::PUBLISHED():
                return V3DonationFormStatus::PUBLISHED();
            case DonationFormStatus::TRASHED():
                return V3DonationFormStatus::TRASHED();
            case DonationFormStatus::PENDING():
                return V3DonationFormStatus::PENDING();
            case DonationFormStatus::PRIVATE():
                return V3DonationFormStatus::PRIVATE();
            default:
                throw new \Exception('Invalid form status');
        }
    }

    /**
     * @since 4.2.0
     */
    public function convertGoalType(): GoalType
    {
        $goalFormat = give_get_meta($this->form->id, '_give_goal_format', true);
        $recurringGoalFormat = (bool)give_get_meta($this->form->id, '_give_recurring_goal_format', true);

        switch ($goalFormat) {
            case 'donation':
                return $recurringGoalFormat ? GoalType::SUBSCRIPTIONS() : GoalType::DONATIONS();
            case 'donors':
                return $recurringGoalFormat ? GoalType::DONORS_FROM_SUBSCRIPTIONS() : GoalType::DONORS();
            default:
                return $recurringGoalFormat ? GoalType::AMOUNT_FROM_SUBSCRIPTIONS() : GoalType::AMOUNT();
        }
    }

    /**
     * @since 4.2.0
     */
    public function convertGoalEnabled(): bool
    {
        return give_is_setting_enabled(give_get_meta($this->form->id, '_give_goal_option', 'disabled'));
    }

    /**
     * @since 4.2.0
     */
    public function convertGoalAmount()
    {
        return give_get_meta($this->form->id, '_give_set_goal', true) ?? 0;
    }

    /**
     * @since 4.2.0
     */
    public function convertDesignId()
    {
        $template = give_get_meta($this->form->id, '_give_form_template', true);

        switch ($template) {
            case 'sequoia':
                return MultiStepFormDesign::id();
            default:
                return ClassicFormDesign::id();
        }
    }
}
