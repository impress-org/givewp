<?php

namespace Give\FormBuilder\DataTransferObjects;

use Give\FormBuilder\Actions\ConvertLegacyNotificationToEmailNotificationData;

/**
 * @unreleased
 */
class EmailNotificationData
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var array */
    public $statusOptions;

    /** @var bool */
    public $supportsRecipients;

    /** @var array */
    public $defaultValues;

    /**
     * @unreleased
     * @param array $notification
     * @return EmailNotificationData
     */
    public static function fromLegacyNotification($notification): EmailNotificationData
    {
        return (new ConvertLegacyNotificationToEmailNotificationData($notification))->__invoke();
    }
}
