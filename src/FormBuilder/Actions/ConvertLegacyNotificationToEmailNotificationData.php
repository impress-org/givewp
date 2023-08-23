<?php

namespace Give\FormBuilder\Actions;

use Give\FormBuilder\DataTransferObjects\EmailNotificationData;

/**
 * Convert data from legacy configuration into DTO.
 *
 * @since 3.0.0
 */
class ConvertLegacyNotificationToEmailNotificationData
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $title;

    /** @var array */
    protected $fields;

    public function __construct(array $notification)
    {
        $this->id = $notification['id'];
        $this->title = $notification['title'];
        $this->fields = $notification['fields'];
    }

    public function __invoke(): EmailNotificationData
    {
        $dto = new EmailNotificationData;

        $dto->id = $this->id;
        $dto->title = $this->title;
        $dto->statusOptions = $this->getStatusOptions();
        $dto->supportsRecipients = $this->hasRecipientField();
        $dto->defaultValues = $this->getDefaultValues();

        return $dto;
    }

    protected function getStatusOptions()
    {
        try {
            $field = $this->findFieldBy('id', "_give_{$this->id}_notification");
            $formattedOptions = [];
            foreach($field['options'] as $value => $label) {
                $formattedOptions[] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }
            return $formattedOptions;
        } catch(\Exception $e) {
            return [];
        }
    }

    protected function hasRecipientField(): bool
    {
        try {
            $this->findFieldBy('id', "_give_{$this->id}_recipient");
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    protected function findFieldBy($key, $value)
    {
        foreach( $this->fields as $field ) {
            if( isset($field[$key]) && $field[$key] === $value ) {
                return $field;
            }
        }
        throw new \Exception("Field not found with $key of '$value'");
    }

    protected function getDefaultValues(): array
    {
        $defaultValues = [];
        foreach($this->fields as $field){
            $key = str_replace("_give_{$this->id}_", '', $field['id']);
            $defaultValues[$key] = $field['default'] ?? '';
        }
        return $defaultValues;
    }
}
