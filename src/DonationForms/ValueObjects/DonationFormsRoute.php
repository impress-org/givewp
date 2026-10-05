<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since TBD Add DETACH_FORMS_FROM_CAMPAIGN
 * @since 4.2.0
 *
 * @method static DonationFormsRoute NAMESPACE()
 * @method static DonationFormsRoute FORM()
 * @method static DonationFormsRoute FORMS()
 * @method static DonationFormsRoute ASSOCIATE_FORMS_WITH_CAMPAIGN()
 * @method static DonationFormsRoute DETACH_FORMS_FROM_CAMPAIGN()
 * @method bool isNamespace()
 * @method bool isForm()
 * @method bool isForms()
 * @method bool isAssociateFormsWithCampaign()
 * @method bool isDetachFormsFromCampaign()
 */
class DonationFormsRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const FORM = 'forms/(?P<id>[0-9]+)';
    const FORMS = 'forms';
    const ASSOCIATE_FORMS_WITH_CAMPAIGN = 'associate-forms-with-campaign';
    const DETACH_FORMS_FROM_CAMPAIGN = 'detach-forms-from-campaign';
}
