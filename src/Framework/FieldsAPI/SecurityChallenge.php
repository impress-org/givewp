<?php

namespace Give\Framework\FieldsAPI;


/**
 * Security challenge fields are a special snowflake.
 * They can typically only be validated once on the server.
 * Using this field type will ensure that the field is not validated on the server
 * before the form is fully submitted, avoiding pre-validation endpoints.
 *
 * @unreleased
 */
abstract class SecurityChallenge extends Field
{
    /**
     * @unreleased
     */
    protected int $serverValidationLimit = 1;

    /**
     * @unreleased
     */
    public function serverValidationLimit(int $limit): SecurityChallenge
    {
        $this->serverValidationLimit = $limit;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getServerValidationLimit(): int
    {
        return $this->serverValidationLimit;
    }
}
