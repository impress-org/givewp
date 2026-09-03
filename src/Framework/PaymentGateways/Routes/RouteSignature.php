<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\Shims\Shim;

/**
 * Route signature for creating secure gateway route methods
 *
 * @since 2.19.0
 */
class RouteSignature
{
    /**
     * @var string
     */
    private $signature;
    /**
     * @var string
     */
    public $expiration;
    /**
     * Keys of the query args covered by this signature, sorted.
     *
     * @since 4.16.8
     * @var string[]
     */
    public $argKeys;

    /**
     * @since 2.19.5 replace wp_create_nonce with wp_hash and timestamp expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @since 4.16.8 add $args so the route's own query args are covered
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @param  string  $expiration
     * @param  array  $args  Query args the route carries, which the signature then covers. Values the
     *                        URL cannot carry — false, and null or empty arrays at any depth — are dropped.
     */
    public function __construct($gatewayId, $gatewayMethod, $donationId, $expiration = null, array $args = [])
    {
        // add_query_arg leaves a false arg off the URL entirely, but only at the top level; a nested
        // false is serialized as '0' by both query builders and round-trips.
        $args = array_filter($args, static function ($value) {
            return $value !== false;
        });

        // Null and empty array values produce no query parameter at any depth, so signing them would
        // produce a signature the request coming back could never rebuild.
        $args = self::pruneArgs($args);

        ksort($args);

        $this->argKeys = array_keys($args);
        $this->expiration = $expiration ?: $this->createExpirationTimestamp();
        $this->signature = $this->generateSignatureString(
            $gatewayId,
            $gatewayMethod,
            $donationId,
            $this->expiration,
            $args
        );
    }


    /**
     * Rebuilds the signature a request claims to carry, from the args that request was signed with.
     *
     * Args the gateway appended on the way back are not among them, so they are ignored; one that was
     * signed and has since been edited or dropped changes the hash.
     *
     * @since 4.16.8
     */
    public static function fromRouteData(GatewayRouteData $data): self
    {
        return new self(
            $data->gatewayId,
            $data->gatewayMethod,
            $data->routeSignatureId,
            $data->routeSignatureExpiration,
            array_intersect_key($data->queryParams, array_flip($data->routeSignatureArgKeys))
        );
    }

    /**
     * Drops null and empty array values at any depth, children first, so a parent left holding
     * nothing goes with them.
     *
     * @since 4.16.8
     */
    private static function pruneArgs(array $args): array
    {
        $args = array_map(static function ($value) {
            return is_array($value) ? self::pruneArgs($value) : $value;
        }, $args);

        return array_filter($args, static function ($value) {
            return $value !== null && $value !== [];
        });
    }

    /**
     * Normalizes args to what the request coming back will carry: PHP urldecodes each query value
     * once (%XX to its character, + to a space), and GatewayRoute then runs the request through
     * give_clean(). Signing the raw values instead rejects a genuine return whenever a value
     * changes shape in transit — a rawurlencoded return URL being the everyday case.
     *
     * Only URL generation runs this; values arriving on a request have been through the real thing.
     *
     * @since 4.16.8 normalize nested values too, as PHP decodes and give_clean() cleans at every depth
     * @since 4.16.8
     */
    public static function normalizeArgs(array $args): array
    {
        return array_map(static function ($value) {
            if (is_array($value)) {
                return self::normalizeArgs($value);
            }

            return is_string($value) ? give_clean(urldecode($value)) : $value;
        }, $args);
    }

    /**
     * Encodes normalized args for the URL. add_query_arg() writes values as given, so a raw value
     * holding an ampersand — a legacy form's success URL under plain permalinks — is split into
     * separate parameters on the way back and the signed value never round-trips. Encoding what was
     * signed means PHP's single decode hands the route exactly that value, however the gateway
     * shaped it.
     *
     * @since 4.16.8
     */
    public static function encodeArgs(array $args): array
    {
        return array_map(static function ($value) {
            if (is_array($value)) {
                return self::encodeArgs($value);
            }

            return is_string($value) ? rawurlencode($value) : $value;
        }, $args);
    }

    /**
     * @since 2.19.5
     *
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @since 4.16.8 append the route's query args
     *
     * @param  string  $expiration
     * @return string
     */
    private function generateSignatureString($gatewayId, $gatewayMethod, $donationId, $expiration, array $args = [])
    {
        $signature = "$gatewayId@$gatewayMethod:$donationId|$expiration";

        // A signature made before args were covered has none, and has to keep hashing to what it did then.
        return $args ? $signature . '|' . http_build_query($args) : $signature;
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function toString()
    {
        return $this->signature;
    }

    /**
     * @since 2.19.5
     *
     * @return string
     */
    public function toHash()
    {
        return wp_hash($this->signature);
    }

    /**
     * Create expiration timestamp
     *
     * @since 2.19.5
     *
     * @return string
     */
    public function createExpirationTimestamp()
    {
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }


    /**
     * @since 2.19.5
     *
     * @param  string  $suppliedSignature
     * @return bool
     */
    public function isValid($suppliedSignature)
    {
        $isSignatureValid = hash_equals(
            $suppliedSignature,
            $this->toHash()
        );

        $isNotExpired = ((int)$this->expiration) >= current_datetime()->getTimestamp();

        return $isSignatureValid && $isNotExpired;
    }
}
