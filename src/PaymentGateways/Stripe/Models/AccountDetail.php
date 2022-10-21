<?php

namespace Give\PaymentGateways\Stripe\Models;

use Exception;
use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Models
 * @since   2.10.2
 *
 * @property-read  string $type
 * @property-read  string $accountName
 * @property-read  string $accountSlug
 * @property-read  string $accountEmail
 * @property-read  string $accountCountry
 * @property-read  string $accountId
 * @property-read  string $liveSecretKey
 * @property-read  string $livePublishableKey
 * @property-read  string $testSecretKey
 * @property-read  string $testPublishableKey
 * @property-read  string $statementDescriptor
 */
class AccountDetail
{
    use HasStripeStatementDescriptorText;

    protected $args;
    protected $propertiesArgs;
    protected $requiredArgs = [
        'type',
        'account_name',
        'account_slug',
        'account_email',
        'account_country',
        'account_id',
        'live_secret_key',
        'live_publishable_key',
        'test_secret_key',
        'test_publishable_key',
        'statement_descriptor',
    ];

    /**
     * AccountDetail constructor.
     *
     * @since 2.10.2
     *
     * @param array $args
     *
     * @throws InvalidPropertyName
     */
    final public function __construct(array $args)
    {
        $this->args = $args;
        $args = $this->addSupportFormNewStatementDescriptorParam($args);
        $this->propertiesArgs = ArrayDataSet::camelCaseKeys($args);
        $this->validate($args);
    }

    /**
     * @since 2.13.0
     * @throws InvalidPropertyName
     */
    public static function fromArray($array)
    {
        return new static($array);
    }

    /**
     * @since 2.13.0
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => $this->type,
            'account_id' => $this->accountId,
            'account_slug' => $this->accountSlug,
            'account_name' => $this->accountName,
            'account_country' => $this->accountCountry,
            'account_email' => $this->accountEmail,
            'live_secret_key' => $this->liveSecretKey,
            'test_secret_key' => $this->testSecretKey,
            'live_publishable_key' => $this->livePublishableKey,
            'test_publishable_key' => $this->testPublishableKey,
            'statement_descriptor' => $this->statementDescriptor,
        ];
    }

    /**
     * @since 2.10.2
     *
     * @param string $key
     *
     * @return mixed
     * @throws InvalidPropertyName
     */
    public function __get($key)
    {
        if ( ! array_key_exists($key, $this->propertiesArgs)) {
            throw new InvalidPropertyName(
                sprintf(
                    '$1%s property does not exist in %2$s class',
                    $key,
                    __CLASS__
                )
            );
        }

        return $this->propertiesArgs[$key];
    }

    /**
     * Validate array format.
     *
     * @since 2.10.2
     *
     * @param array $array
     *
     * @throws InvalidPropertyName
     */
    private function validate($array)
    {
        if (array_diff($this->requiredArgs, array_keys($array))) {
            throw new InvalidPropertyName(
                sprintf(
                    'To create a %1$s object, please provide valid: %2$s',
                    __CLASS__,
                    implode(' , ', $this->requiredArgs)
                )
            );
        }
    }

    /**
     * We decided to define statement descriptor per stripe account.
     * Statement descriptor default text for each account will be set to blog title.
     * @see   : https://github.com/impress-org/givewp/issues/6021
     *
     * @since 2.19.0
     * @since 2.19.1 Use old stripe statement descriptor requirements to filter text.
     *             https://github.com/impress-org/givewp/pull/6269
     * @deprecated
     *
     * @param array $args
     *
     * @return array
     */
    private function addSupportFormNewStatementDescriptorParam($args)
    {
        $propertyName = 'statement_descriptor';
        if ( ! array_key_exists($propertyName, $args) || empty($args[$propertyName])) {
            $statementDescriptor = give_get_option('stripe_statement_descriptor', get_bloginfo('name'));
            $args[$propertyName] = $this->filterOldStatementDescriptor($statementDescriptor);
        }

        return $args;
    }
}
