<?php

namespace Give\Framework\Database;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use WP_Error;

/**
 * Class DB
 *
 * A static decorator for the $wpdb class and decorator function which does SQL error checking when performing queries.
 * If a SQL error occurs a DatabaseQueryException is thrown.
 *
 * @method static int|bool query(string $query)
 * @method static int|false insert(string $table, array $data, array|string $format)
 * @method static int|false delete(string $table, array $where, array|string $where_format)
 * @method static int|false update(string $table, array $where, array|string $where_format)
 * @method static int|false replace(string $table, array $data, array|string $format)
 * @method static null|string get_var(string $query = null, int $x = 0, int $y = 0)
 * @method static array|object|null|void get_row(string $query = null, string $output = OBJECT, int $y = 0)
 * @method static array get_col(string $query = null, int $x = 0)
 * @method static array|object|null get_results(string $query = null, string $output = OBJECT)
 * @method static string get_charset_collate()
 */
class DB
{
    /**
     * Runs the dbDelta function and returns a WP_Error with any errors that occurred during the process
     *
     * @see dbDelta() for parameter and return details
     *
     * @since 2.9.2
     *
     * @param $delta
     *
     * @return array
     * @throws DatabaseQueryException
     */
    public static function delta($delta)
    {
        return self::runQueryWithErrorChecking(
            function () use ($delta) {
                return dbDelta($delta);
            }
        );
    }

    /**
     * A convenience method for the $wpdb->prepare method
     *
     * @see WPDB::prepare() for usage details
     *
     * @since 2.9.6
     *
     * @param string $query
     * @param mixed  ...$args
     *
     * @return false|mixed
     */
    public static function prepare($query, ...$args)
    {
        global $wpdb;

        return $wpdb->prepare($query, ...$args);
    }

    /**
     * Magic method which calls the static method on the $wpdb while performing error checking
     *
     * @since 2.9.6
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws DatabaseQueryException
     */
    public static function __callStatic($name, $arguments)
    {
        return self::runQueryWithErrorChecking(
            function () use ($name, $arguments) {
                global $wpdb;

                return call_user_func_array([$wpdb, $name], $arguments);
            }
        );
    }

    /**
     * Get last insert ID
     *
     * @since 2.10.0
     * @return int
     */
    public static function last_insert_id()
    {
        global $wpdb;

        return $wpdb->insert_id;
    }

    /**
     * Runs a query callable and checks to see if any unique SQL errors occurred when it was run
     *
     * @since 2.9.2
     *
     * @param Callable $queryCaller
     *
     * @return mixed
     * @throws DatabaseQueryException
     */
    private static function runQueryWithErrorChecking($queryCaller)
    {
        global $wpdb, $EZSQL_ERROR;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $errorCount = is_array($EZSQL_ERROR) ? count($EZSQL_ERROR) : 0;
        $hasShowErrors = $wpdb->hide_errors();

        $output = $queryCaller();

        if ($hasShowErrors) {
            $wpdb->show_errors();
        }

        $wpError = self::getQueryErrors($errorCount);

        if ( ! empty($wpError->errors)) {
            throw DatabaseQueryException::create($wpError->get_error_messages());
        }

        return $output;
    }

    /**
     * Retrieves the SQL errors stored by WordPress
     *
     * @since 2.9.2
     *
     * @param int $initialCount
     *
     * @return WP_Error
     */
    private static function getQueryErrors($initialCount = 0)
    {
        global $EZSQL_ERROR;

        $wpError = new WP_Error();

        if (is_array($EZSQL_ERROR)) {
            for ($index = $initialCount, $indexMax = count($EZSQL_ERROR); $index < $indexMax; $index++) {
                $error = $EZSQL_ERROR[$index];

                if (empty($error['error_str']) || empty($error['query']) || 0 === strpos(
                        $error['query'],
                        'DESCRIBE '
                    )) {
                    continue;
                }

                $wpError->add('db_delta_error', $error['error_str']);
            }
        }

        return $wpError;
    }
}
