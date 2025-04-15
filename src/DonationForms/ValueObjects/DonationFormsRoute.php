<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonationFormsRoute NAMESPACE()
 * @method static DonationFormsRoute FORM()
 * @method static DonationFormsRoute FORMS()
 * @method bool isNamespace()
 * @method bool isForm()
 * @method bool isForms()
 */
class DonationFormsRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const FORM = 'forms/(?P<id>[0-9]+)';
    const FORMS = 'forms';
}
