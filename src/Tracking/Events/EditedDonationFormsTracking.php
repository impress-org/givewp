<?php

namespace Give\Tracking\Events;

use Give\DonationForms\Models\DonationForm;
use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Repositories\TrackEvents;
use Give\Tracking\TrackingData\EditedDonationFormsData;
use Give\Tracking\TrackRegisterer;

/**
 * Class EditedDonationFormsTracking
 *
 * @package Give\Tracking\Events
 * @since 2.10.2
 */
class EditedDonationFormsTracking extends TrackEvent
{
    /**
     * @var string
     */
    protected $dataClassName = EditedDonationFormsData::class;

    /**
     * @var TrackEvents
     */
    private $trackEvents;

    /**
     * GivePluginSettingsTracking constructor.
     *
     * @since 2.10.0
     *
     * @param TrackRegisterer $track
     * @param TrackEvents     $trackEvents
     */
    public function __construct(TrackRegisterer $track, TrackEvents $trackEvents)
    {
        $this->eventType = new EventType(EventType::DONATION_FORM_UPDATED);
        $this->trackEvents = $trackEvents;

        parent::__construct($track);
    }

    /**
     * sav_post hook handler.
     *
     * @since 2.10.2
     *
     * @param int $formId
     */
    public function savePostHookHandler($formId)
    {
        $this->trackEvents->saveRecentlyEditedDonationForm($formId);
        $this->record();
    }

    /**
     * @since 3.0.0
     */
    public function formBuilderUpdatedHookHandler(DonationForm $form)
    {
        $this->trackEvents->saveRecentlyEditedDonationForm($form->id);
        $this->record();
    }
}
