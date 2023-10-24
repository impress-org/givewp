<?php

namespace Give\FormMigration\DataTransferObjects;

class DesignHeaderSettings
{
    /** @var bool */
    protected $enabled;

    /** @var string */
    protected $heading;

    /** @var string */
    protected $description;

    public function __construct($enabled, $heading, $description)
    {
        $this->enabled = give_is_setting_enabled($enabled);
        $this->heading = $heading;
        $this->description = $description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function hasHeading(): bool
    {
        return !empty($this->getHeading());
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function hasDescription(): bool
    {
        return !empty($this->getDescription());
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
