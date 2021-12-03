<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Validators\FileUploadValidator;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\FieldsAPI\Types;

/**
 * Setup field validation for custom fields on the checkout error check fields hook.
 *
 * @NOTE For custom field like file, we can not process validation on ajax required.
 * This class validate these fields on donation form submission.
 *
 * @package Give\Form\LegacyConsumer\Commands
 * @since 2.10.2
 */
class SetupFieldValidation implements HookCommandInterface
{

    /** @var int */
    private $formId;

    /**
     * @since 2.10.2
     *
     * @param int $formId
     */
    public function __construct($formId)
    {
        $this->formId = $formId;
    }

    /**
     * @since 2.10.2
     * @since 2.14.0 Handle File field type and custom field type separately
     *
     * @param string $hook
     *
     * @void
     */
    public function __invoke($hook)
    {
        $collection = Group::make($hook);
        do_action("give_fields_$hook", $collection, $this->formId);
        $collection->walkFields([$this, 'validate']);
    }

    /**
     * Validate the given field.
     *
     * @since 2.14.0 Add max length validation for input and textarea field
     *
     * @param Field|File|Text $field
     *
     * @void
     */
    public function validate(Field $field)
    {
        if ($field->getType() === Types::FILE) {
            // Are we processing donation form validation on ajax?
            if (isset($_POST['give_ajax'])) {
                return;
            }

            $validator = new FileUploadValidator($field);
            $validator();
        } elseif (in_array($field->getType(), Types::all(), true)) {
            if ($field->isRequired() && ! isset($_POST[$field->getName()])) {
                give_set_error(
                    "give-{$field->getName()}-required-field-missing",
                    $field->getRequiredError()['error_message']
                );
            }

            if (
                in_array($field->getType(), [Types::TEXT, Types::TEXTAREA]) &&
                $field->getMaxLength() &&
                isset($_POST[$field->getName()]) &&
                strlen($_POST[$field->getName()]) > $field->getMaxLength()
            ) {
                give_set_error(
                    "give-{$field->getName()}-required-field-missing",
                    sprintf(
                        esc_html__('%1$s field value exceed allowed character limit.', 'give'),
                        $field->getName()
                    )
                );
            }
        } else {
            /**
             * Use this action to validate custom field which does not exist in field api.
             *
             * @since 2.14.0
             *
             * @param Field $field
             * @param int   $formId
             */
            do_action('give_fields_legacy_consumer_validate_field', $field, $this->formId);
        }
    }
}
