<?php

namespace Give\Log;

use Exception;
use Give\Log\Helpers\Environment;
use Give\Log\ValueObjects\LogType;

/**
 * Class Log
 *
 * The static facade intended to be the primary way of logging within GiveWP to make life easier.
 *
 * @since 2.21.0 Use array_diff_key to filter context data to prevent php warning with multi-dimension array
 * @since 2.20.0 add sensitive information redaction; store context as arrays for JSON serialization
 * @since 2.19.6 added debug
 * @since 2.10.0
 *
 * @note There are two special keywords used in the context that are representing category and source.
 * The default value for the Category is "Core" and for the source is "Give Core"
 * If you want to change the category and/or source, you should provide them as context attributes.
 * Source and category attributes should be written lowercase.
 *
 * @example
 *
 * Log::error( 'Error message', [
 *     'category' => 'Payment',
 *     'source' => 'Stripe add-on'
 * ] );
 *
 * @note Use as many contexts attributes as you need. The more the better.
 *
 * @example
 *
 *  Log::error( 'Error message', [
 *     'category' => 'Payment',
 *     'source' => 'Stripe add-on',
 *     'donation_id' => $donationId,
 *     'donor_id' => $donorId
 * ] );
 *
 * @note You can use an array or object as a context attribute value.
 *
 * @example
 *
 * try {
 *     something();
 * } catch ( Exception $exception ) {
 *   Log::error( 'Something went wrong', [
 *      'exception' => $exception,
 *      'additional_info' => [
 *          'donation_id' => $donationId
 *       ]
 *   ] );
 * }
 *
 *
 * @method static error(string $message, array $context = [])
 * @method static warning(string $message, array $context = [])
 * @method static notice(string $message, array $context = [])
 * @method static success(string $message, array $context = [])
 * @method static info(string $message, array $context = [])
 * @method static http(string $message, array $context = [])
 * @method static spam(string $message, array $context = [])
 * @method static debug(string $message, array $context = [])
 */
class Log
{
    public function __call($name, $arguments)
    {
        list ($message, $context) = array_pad($arguments, 2, null);

        if (is_array($context)) {
            $context = $this->serializeAndRedactContext($context);

            // Default fields
            $data = array_filter(
                $context,
                function ($key) {
                    return array_key_exists($key, LogFactory::getDefaults());
                },
                ARRAY_FILTER_USE_KEY
            );

            // Additional context
            $data['context'] = array_diff_key(
                $context,
                $data
            );
        }

        // Set message
        if (!is_null($message)) {
            $data['message'] = $message;
        }

        // Set type
        $data['type'] = $name;

        try {
            $log = LogFactory::makeFromArray($data);
            $log->save();

            return $log;
        } catch (Exception $exception) {
            error_log($exception->getMessage());
        }
    }

    /**
     * Takes the context array, serializes it, and redacts sensitive data.
     *
     * @since 2.20.0
     */
    private function serializeAndRedactContext(array $context): array
    {
        $redactedData = [];

        foreach ($context as $key => $value) {
            foreach (self::getRedactionList() as $redaction) {
                if (stripos($key, $redaction) !== false) {
                    $redactedData[$key] = '[[redacted]]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $value = $this->serializeAndRedactContext($value);
            } elseif (is_object($value)) {
                $value = $this->serializeAndRedactContext(
                    array_merge(
                        ['Object Class' => get_class($value)],
                        (array)$value
                    )
                );
            }

            $redactedData[$key] = $value;
        }

        return $redactedData;
    }

    /**
     * Static helper for calling the logger methods
     *
     * @since 2.19.6 added conditional for logging debug()
     * @since 2.18.0 - always log errors, warnings & only log all if WP_DEBUG_LOG is enabled
     * @since 2.11.1
     *
     * @param array $arguments
     *
     * @param string $name
     */
    public static function __callStatic($name, $arguments)
    {
        /** @var Log $logger */
        $logger = give(__CLASS__);

        if ($name !== LogType::DEBUG && (in_array($name, ['error', 'warning']) || Environment::isWPDebugLogEnabled())) {
            call_user_func_array([$logger, $name], $arguments);
        }

        if (Environment::isGiveDebugEnabled()) {
            call_user_func_array([$logger, $name], $arguments);
        }
    }

    /**
     * @since 2.20.0
     *
     * Retrieves the redaction list after applying filters.
     */
    public static function getRedactionList(): array
    {
        static $list = null;

        if ($list === null) {
            $list = apply_filters('give_log_redaction_list', ['card', 'password', 'secret', 'token']);
        }

        return $list;
    }
}
