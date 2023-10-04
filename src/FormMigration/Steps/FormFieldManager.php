<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class FormFieldManager extends FormMigrationStep
{

    /** @var string $placement */
    private $placement;

    public function process()
    {
        $formFields = $this->formV2->getFormFields();
        $this->placement = $this->formV2->getFormFieldsPlacement();

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
            'url' => [$this, 'addUrlField'],
        ];

        foreach ($formFields as $field) {
            if (!array_key_exists($field['input_type'], $map) || !$field['name']) {
                continue;
            }

            $method = $map[$field['input_type']];

            $block = $this->applyCommonAttributes($method($field), $field);
            $this->fieldBlocks->insertAfter($this->placement, $block);
        }
    }

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
                'label' => $field['label'],
                'description' => $field['help'],
                'dateFormat' => $dateFormat,
            ]
        ]);
    }

    private function addDropdownField($field): BlockModel
    {
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => $option === $field['selected'],
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/dropdown',
            'attributes' => [
                'label' => $field['label'],
                'description' => $field['help'],
                'options' => $options,
            ]
        ]);
    }

    private function addEmailField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/email',
            'attributes' => [
                'label' => $field['label'],
                'placeholder' => $field['placeholder'],
                'description' => $field['help'],
                'defaultValue' => $field['default'],
            ]
        ]);
    }

    private function addFileUploadField($field): BlockModel
    {
        $allowedFileTypes = array_map(function ($type) {
            switch ($type) {
                case 'images': return 'image';
                default: return $type;
            }
        }, $field['extension']);

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/fileUpload',
            'attributes' => [
                'label' => $field['label'],
                'description' => $field['help'],
                'maxFileSize' => $field['max_size'],
                'allowedFileTypes' => $allowedFileTypes,
            ]
        ]);
    }

    private function addHiddenField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/hidden',
            'attributes' => [
                'label' => $field['label'],
                'defaultValue' => $field['default'],
            ]
        ]);
    }

    private function addHtmlField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/html',
            'attributes' => [
                'label' => $field['label'],
                'htmlCode' => $field['html'],
            ]
        ]);
    }

    private function addMultiSelectField($field): BlockModel
    {
        $fieldType = $field['template'] === 'checkbox_field' ? 'checkbox' : 'dropdown';
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => in_array($option, $field['selected'], true),
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/multi-select',
            'attributes' => [
                'label' => $field['label'],
                'description' => $field['help'],
                'fieldType' => $fieldType,
                'options' => $options,
            ]
        ]);
    }

    private function addPhoneField($field): BlockModel
    {
        $phoneFormat = $field['format'] === 'domestic' ? 'domestic' : 'unformatted';

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/phone',
            'attributes' => [
                'label' => $field['label'],
                'placeholder' => $field['placeholder'],
                'description' => $field['help'],
                'defaultValue' => $field['default'],
                'format' => $phoneFormat,
            ]
        ]);
    }

    private function addRadioField($field): BlockModel
    {
        $options = array_map(function ($option) use ($field) {
            return [
                'label' => $option,
                'value' => '',
                'checked' => $option === $field['selected'],
            ];
        }, array_filter($field['options']));

        return BlockModel::make([
            'name' => 'givewp-form-field-manager/radio',
            'attributes' => [
                'label' => $field['label'],
                'description' => $field['help'],
                'options' => $options,
            ]
        ]);
    }

    private function addTextField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/text',
            'attributes' => [
                'label' => $field['label'],
                'placeholder' => $field['placeholder'],
                'description' => $field['help'],
                'defaultValue' => $field['default'],
            ]
        ]);
    }

    private function addUrlField($field): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp-form-field-manager/url',
            'attributes' => [
                'label' => $field['label'],
                'placeholder' => $field['placeholder'],
                'description' => $field['help'],
                'defaultValue' => $field['default'],
            ]
        ]);
    }

    private function applyCommonAttributes($block, $field): BlockModel
    {
        $block->setAttribute('fieldName', $field['name']);

        if ($field['required']) {
            $block->setAttribute('required', $field['required'] === 'yes');
        }

        return $block;
    }
}
