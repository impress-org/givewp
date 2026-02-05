<?php

namespace Give\Framework\Permissions;

/**
 * Events do not have custom capabilities, so we use the same as donation forms.
 *
 * @since 4.14.0
 */
class EventPermissions extends DonationFormPermissions
{
}
