<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class FormFieldManager extends FormMigrationStep
{

    /** @var array {0: BlockCollection, 1: string, 2: string|null} */
    private $inserter;

    /** @var array {fieldName: {field: array, block: BlockModel}} */
    private $fieldBlockRelationships = [];

    /**
     * Suppressed settings for all fields:
     * - "field_width" (Field Width)
     * - "css" (CSS Class Name)
     *
     * @since 3.0.0 added support for conditions based on Donation Amount.
     * @since 3.0.0
     */
    public function process()
    {
        $formFields = $this->formV2->getFormFields();

        if (!$formFields) {
            return;
        }

        $this->fieldBlockRelationships['give-amount'] = [
            'field' => [], // This is a core field and doesn't have any FFM settings.
            'block' => $this->fieldBlocks->findByName('givewp/donation-amount'),
        ];

        $this->inserter = $this->getInitialInserter();

        $map = [
            'checkbox' => [$this, 'addMultiSelectField'],
            'date' => [$this, 'addDateField'],
            'select' => [$this, 'addDropdownField'],
            'email' => [$this, 'addEmailField'],
            'file_upload' => [$this, 'addFileUploadField'],
            'hidden' => [$this, 'addHiddenField'],
            'html' => [$this, 'addHtmlField'],
            'multiselect' => [$this, 'addMultiSelectField'],
            'phone' => [$this, 'addPhoneField'],
            'radio' => [$this, 'addRadioField'],
            'text' => [$this, 'addTextField'],
            'textarea' => [$this, 'addTextareaField'],
            'url' => [$this, 'addUrlField'],
        ];

        foreach ($formFields as $field) {
            if ($field['input_type'] === 'section') {
                $this->addSection($field);
                continue;
            }

            if (!array_key_exists($field['input_type'], $map) || !$field['name']) {
                continue;
            }

            $method = $map[$field['input_type']];

            $block = $this->applyCommonAttributes($method($field), $field);
            $this->fieldBlockRelationships[$field['name']] = [
                'field' => $field,
                'block' => $block
            ];
            $this->insertBlock($block);
        }

        $this->mapConditionalLogicToBlocks();
    }

    /**
     * Suppressed settings for the Date field:
     * - "time" (Enable time input)
     * - "format_time" (Time Format)
     *
     * @since 3.0.0
     */
    private function addDateField($field): BlockModel
    {
        $dateFormatOrder = [
            'yyyy' => strpos(strtolower($field['format']), 'y'),
            'mm' => strpos(strtolower($field['format']), 'm'),
            'dd' => strpos(strtolower($field['format']), 'd'),
        ];
        asort($dateFormatOrder);
        $dateFormat = implode('/', array_keys($dateFormatOrder));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/date',
            'attributes' => [
                'dateFormat' => $dateFormat,
            ]
        ]);
    }

    /**
     * Suppressed settings for the Dropdown field:
     * - "first" (Select Text)
     *
     * @since 3.0.0
     */
    private function addDropdownField($field): BlockModel
    {
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => array_key_exists('selected', $field) && $option === $field['selected'],
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/dropdown',
            'attributes' => [
                'options' => $options,
            ]
        ]);
    }

    /**
     * Suppressed settings for the Email field:
     * - "maxlength" (Max Length)
     *
     * @since 3.0.0
     */
    private function addEmailField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/email',
        ]);
    }

    /**
     * Suppressed settings for the File Upload field:
     * - "count" (Max. files)
     *
     * @since 3.0.0
     */
    private function addFileUploadField($field): BlockModel
    {
        $allowedFileTypes = array_map(function ($type) {
            switch ($type) {
                case 'images':
                    return 'image';
                default:
                    return $type;
            }
        }, $field['extension']);

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/file-upload',
            'attributes' => [
                'maxFileSize' => $field['max_size'],
                'allowedFileTypes' => $allowedFileTypes,
            ]
        ]);
    }

    /**
     * @since 3.0.0
     */
    private function addHiddenField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/hidden',
        ]);
    }

    /**
     * @since 3.0.0
     */

    private function addHtmlField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/html',
            'attributes' => [
                'htmlCode' => $field['html'],
            ]
        ]);
    }

    /**
     * Suppressed settings for the Dropdown field:
     * - "first" (Select Text)
     *
     * @since 3.0.0
     */
    private function addMultiSelectField($field): BlockModel
    {
        $fieldType = $field['template'] === 'checkbox_field' ? 'checkbox' : 'dropdown';
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => array_key_exists('selected', $field) && in_array($option, (array)$field['selected'], true),
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/multi-select',
            'attributes' => [
                'fieldType' => $fieldType,
                'options' => $options,
            ]
        ]);
    }

    /**
     * @since 3.0.0
     */

    private function addPhoneField($field): BlockModel
    {
        $phoneFormat = $field['format'] === 'domestic' ? 'domestic' : 'unformatted';

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/phone',
            'attributes' => [
                'phoneFormat' => $phoneFormat,
            ]
        ]);
    }

    /**
     * @since 3.0.0
     */

    private function addRadioField($field): BlockModel
    {
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => array_key_exists('selected', $field) && $option === $field['selected'],
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/radio',
            'attributes' => [
                'options' => $options,
            ]
        ]);
    }

    /**
     * Suppressed settings for the Text field:
     * - "maxlength" (Max Length)
     *
     * @since 3.0.0
     */
    private function addTextField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/text',
        ]);
    }

    /**
     * Suppressed settings for the Textarea field:
     * - "cols" (Columns)
     *
     * @since 3.0.0
     */
    private function addTextareaField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/textarea',
            'attributes' => [
                'rows' => $field['rows'],
            ],
        ]);
    }

    /**
     * Suppressed settings for the Text field:
     * - "maxlength" (Max Length)
     *
     * @since 3.0.0
     */
    private function addUrlField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/url',
        ]);
    }

    /**
     * @since 3.0.0
     */

    private function addSection($field): void
    {
        $block = BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => [
                'title' => $field['label'],
                'description' => ''
            ]
        ]);

        list($blockCollection, $method) = $this->inserter;
        $this->inserter = [$block->innerBlocks, 'append'];
        $found = $this->fieldBlocks->findParentByBlockCollection($blockCollection);

        if (!$found) {
            $this->fieldBlocks->append($block);
            return;
        }

        list($parentBlock, $parentBlockIndex) = $found;

        if ($method === 'insertBefore') {
            $this->fieldBlocks->insertBefore(
                $parentBlock->name,
                $block,
                $parentBlockIndex
            );
        } else {
            $this->fieldBlocks->insertAfter(
                $parentBlock->name,
                $block,
                $parentBlockIndex
            );
        }
    }

    /**
     * @since 3.0.0
     */

    private function getInitialInserter(): array
    {
        $placement = $this->formV2->getFormFieldsPlacement();

        switch ($placement) {
            case 'give_before_donation_levels':
                $parentBlock = BlockModel::make([
                    'name' => 'givewp/section',
                ]);
                $this->fieldBlocks->prepend($parentBlock);
                return [$parentBlock->innerBlocks, 'append'];
            case 'give_payment_mode_top':
                return [$this->fieldBlocks, 'insertBefore', 'givewp/donation-summary'];
            case 'give_payment_mode_bottom':
                $parentBlock = $this->fieldBlocks->findParentByChildName('givewp/donation-summary');
                return [$parentBlock->innerBlocks, 'append'];
            case 'give_donation_form_before_personal_info':
                return [$this->fieldBlocks, 'insertBefore', 'givewp/donor-name'];
            case 'give_donation_form_after_personal_info':
                $parentBlock = $this->fieldBlocks->findParentByChildName('givewp/donor-name');
                return [$parentBlock->innerBlocks, 'append'];
            case 'give_donation_form_before_cc_form':
                return [$this->fieldBlocks, 'insertBefore', 'givewp/payment-gateways'];
            case 'give_donation_form_bottom':
            case 'give_donation_form_after_cc_form':
                $parentBlock = $this->fieldBlocks->findParentByChildName('givewp/payment-gateways');
                return [$parentBlock->innerBlocks, 'append'];
            case 'give_donation_form_top':
            case 'give_after_donation_levels':
            default:
                $parentBlock = $this->fieldBlocks->findParentByChildName('givewp/donation-amount');
                return [$parentBlock->innerBlocks, 'append'];
        }
    }

    /**
     * @since 3.0.0
     */

    private function applyCommonAttributes($block, $field): BlockModel
    {
        $protectedFieldNames = [
            'donation-amount',
            'donor-name',
            'email',
        ];

        if (in_array($field['name'], $protectedFieldNames, true)) {
            $field['name'] .= '_1';
        }

        $block->setAttribute('fieldName', $field['name']);
        $block->setAttribute('displayInAdmin', true);
        $block->setAttribute('displayInReceipt', true);
        $block->setAttribute('emailTag', "meta_donation_{$field['name']}");
        $block->setAttribute('metaUUID', $block->clientId);

        if (array_key_exists('required', $field)) {
            $block->setAttribute('isRequired', $field['required'] === 'yes');
        }

        if (array_key_exists('label', $field)) {
            $block->setAttribute('label', $field['label']);
        }

        if (array_key_exists('placeholder', $field)) {
            $block->setAttribute('placeholder', $field['placeholder']);
        }

        if (array_key_exists('help', $field)) {
            $block->setAttribute('description', $field['help']);
        }

        if (array_key_exists('default', $field)) {
            $block->setAttribute('defaultValue', $field['default']);
        }

        return $block;
    }

    /**
     * @since 3.0.0
     */

    private function insertBlock($block): void
    {
        list($blockCollection, $method, $target) = array_pad($this->inserter, 3, null);
        call_user_func_array([$blockCollection, $method], array_filter([$target, $block]));
    }

    /**
     * @since 3.0.0 Fixed missing conditionalLogic attribute on custom fields.
     * @since 3.0.0
     */
    private function mapConditionalLogicToBlocks(): void
    {
        foreach ($this->fieldBlockRelationships as $item) {
            ['field' => $field, 'block' => $block] = $item;

            // Initialize conditional logic support for custom fields.
            // The `conditionalLogic` attribute is used to signal support for conditional logic.
            $block->setAttribute('conditionalLogic', [
                'enabled' => give_is_setting_enabled($field['control_field_visibility']),
                'action' => 'show',
                'boolean' => 'and',
                'rules' => [],
            ]);

            if (!array_key_exists('control_field_visibility', $field)) {
                continue;
            }

            if(!isset($this->fieldBlockRelationships[$field['controller_field_name']])) {
                continue;
            }

            $referenceBlock = $this->fieldBlockRelationships[$field['controller_field_name']]['block'];

            $block->setAttribute('conditionalLogic', [
                'enabled' => true,
                'action' => 'show',
                'boolean' => 'and',
                'rules' => [
                    [
                        'field' => $referenceBlock->clientId,
                        'operator' => $field['controller_field_operator'],
                        'value' => $field['controller_field_value'],
                    ],
                ],
            ]);
        }
    }
}
