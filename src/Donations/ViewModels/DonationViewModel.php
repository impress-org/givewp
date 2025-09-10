<?php

namespace Give\Donations\ViewModels;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationAnonymousMode;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Types;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.6.0
 */
class DonationViewModel
{
    private Donation $donation;
    private DonationAnonymousMode $anonymousMode;
    private bool $includeSensitiveData = false;

    /**
     * @since 4.6.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @since 4.6.0
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): DonationViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }

    /**
     * @since 4.6.0
     */
    public function anonymousMode(DonationAnonymousMode $mode): DonationViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @since 4.6.0
     */
    public function exports(): array
    {
        $data = array_merge(
            $this->donation->toArray(),
            [
                'customFields' => $this->getCustomFields(),
                'eventTicketsAmount' => $this->donation->eventTicketsAmount(),
                'eventTickets' => $this->getEventTickets(),
                'gateway' => $this->getGatewayDetails(),
            ]
        );

        if (!$this->includeSensitiveData) {
            $sensitiveDataExcluded = [
                'donorIp',
                'email',
                'phone',
                'billingAddress',
                'purchaseKey',
                'customFields'
            ];

            foreach ($sensitiveDataExcluded as $propertyName) {
                switch ($propertyName) {
                    case 'billingAddress':
                        $data[$propertyName] = null;
                        break;
                    case 'customFields':
                        $data[$propertyName] = [];
                        break;
                    default:
                        $data[$propertyName] = '';
                        break;
                }
            }
        }

        if (isset($this->anonymousMode) && $this->anonymousMode->isRedacted() && $this->donation->anonymous) {
            $anonymousDataRedacted = [
                'donorId',
                'honorific',
                'firstName',
                'lastName',
                'company',
                'customFields'
            ];

            foreach ($anonymousDataRedacted as $propertyName) {
                switch ($propertyName) {
                    case 'donorId':
                        $data[$propertyName] = 0;
                        break;
                    case 'customFields':
                        $data[$propertyName] = [];
                        break;
                    default:
                        $data[$propertyName] = __('anonymous', 'give');
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Get custom fields for the donation
     *
     * @since 4.6.0
     */
    private function getCustomFields(): array
    {
        $customFields = [];

        // get custom fields from v3 forms
        $v3Form = $this->getDonationForm();

        if ($v3Form) {
            $displayedFields = $this->getDisplayedDonationMetaFieldsForForm($v3Form);

            foreach ($displayedFields as $field) {
                $value = $this->getFieldValue($field);

                if (empty($value)) {
                    continue;
                }

                $customFields[] = [
                    'label' => method_exists($field, 'getLabel') ? $field->getLabel() : $field->getName(),
                    'value' => $value,
                ];
            }
        }

        return apply_filters('givewp_donation_details_custom_fields', $customFields, $this->donation->id);
    }

    /**
     * Get donation form for the donation
     *
     * @since 4.6.0
     */
    private function getDonationForm(): ?DonationForm
    {
        $formId = $this->donation->formId;

        if (!$formId || give(DonationFormRepository::class)->isLegacyForm($formId)) {
            return null;
        }

        return DonationForm::find($formId);
    }

    /**
     * Get displayed donation meta fields for a form
     *
     * @since 4.6.0
     */
    private function getDisplayedDonationMetaFieldsForForm(DonationForm $form): array
    {
        return array_filter($form->schema()->getFields(), static function (Field $field): bool {
            return $field->shouldShowInAdmin() && !$field->shouldStoreAsDonorMeta();
        });
    }

    /**
     * Get field value for a custom field
     *
     * @since 4.6.0
     */
    private function getFieldValue(Field $field): string
    {
        $metaValue = give()->payment_meta->get_meta($this->donation->id, $field->getName(), true);

        if (empty($metaValue)) {
            return '';
        }

        if ($field->getType() === Types::FILE) {
            $attachmentLink = wp_get_attachment_link($metaValue);
            return $attachmentLink ?: '';
        }

        return (string) $metaValue;
    }

    /**
     * @since 4.6.0
     */
    private function getEventTickets(): array
    {
        return give(EventTicketRepository::class)->getEventTicketDetails($this->donation);
    }

    /**
     * @since 4.8.0 Return empty array if gateway is not registered
     * @since 4.6.0
     */
    private function getGatewayDetails(): array
    {
        if ( ! give(PaymentGatewayRegister::class)->hasPaymentGateway($this->donation->gatewayId)) {
            return [];
        }

        return array_merge(
            $this->donation->gateway()->toArray(),
            [
                'transactionUrl' => $this->donation->gateway()->getTransactionUrl($this->donation),
            ]
        );
    }
}
