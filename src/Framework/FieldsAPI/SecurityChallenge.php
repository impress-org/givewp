<?php

namespace Give\Framework\FieldsAPI;


/**
 * Security challenge fields are a special snowflake.
 * They can typically only be validated once on the server.
 * Extending this abstract field will ensure that the field is not validated on the server
 * before the form is fully submitted, avoiding pre-validation conflicting endpoints.
 *
 * @since 4.1.0
 */
abstract class SecurityChallenge extends Field
{
}
