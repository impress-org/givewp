<?php

namespace Give\Framework\Database;

use Exception;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\QueryBuilder;
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
 * @method static int|false update(string $table, array $data, array $where, array|string $format = null, array|string $where_format = null)
 * @method static int|false replace(string $table, array $data, array|string $format)
 * @method static null|string get_var(string $query = null, int $x = 0, int $y = 0)
 * @method static array|object|null|void get_row(string $query = null, string $output = OBJECT, int $y = 0)
 * @method static array get_col(string $query = null, int $x = 0)
 * @method static array|object|null get_results(string $query = null, string $output = OBJECT)
 * @method static string get_charset_collate()
 * @method static string esc_like(string $text)
 * @method static string remove_placeholder_escape(string $text)
 */
class DB
{
    /**
     * Runs the dbDelta function and returns a WP_Error with any errors that occurred during the process
     *
     * @since 2.9.2
     *
     * @param $delta
     *
     * @return array
     * @throws DatabaseQueryException
     * @see   dbDelta() for parameter and return details
     *
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
     * @since 2.9.6
     *
     * @param string $query
     * @param mixed  ...$args
     *
     * @return false|mixed
     * @see   WPDB::prepare() for usage details
     *
     */
    public static function prepare($query, ...$args)
    {
        global $wpdb;

        return $wpdb->prepare($query, ...$args);
    }

    /**
     * Magic method which calls the static method on the $wpdb while performing error checking
     *
     * @since 2.22.0 add givewp_db_pre_query action
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
            static function () use ($name, $arguments) {
                global $wpdb;

                if (in_array($name, ['get_row', 'get_col', 'get_results', 'query'], true)) {
                    do_action('givewp_db_pre_query', current($arguments));
                }

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
     * Prefix given table name with $wpdb->prefix
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function prefix($tableName)
    {
        global $wpdb;

        return $wpdb->prefix . $tableName;
    }

    /**
     * Create QueryBuilder instance
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return QueryBuilder
     */
    public static function table($table, $alias = null)
    {
        $builder = new QueryBuilder();
        $builder->from($table, $alias);

        return $builder;
    }

    /**
     * Runs a transaction. If the callable works then the transaction is committed. If the callable throws an exception
     * then the transaction is rolled back.
     *
     * @since 2.19.6
     *
     * @param callable $callback
     *
     * @return void
     * @throws Exception
     */
    public static function transaction(callable $callback)
    {
        self::beginTransaction();

        try {
            $callback();
        } catch (Exception $e) {
            self::rollback();
            throw $e;
        }

        self::commit();
    }

    /**
     * Manually starts a transaction
     *
     * @since 2.19.6
     *
     * @return void
     */
    public static function beginTransaction()
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
    }

    /**
     * Manually rolls back a transaction
     *
     * @since 2.19.6
     *
     * @return void
     */
    public static function rollback()
    {
        global $wpdb;
        $wpdb->query('ROLLBACK');
    }

    /**
     * Manually commits a transaction
     *
     * @since 2.19.6
     *
     * @return void
     */
    public static function commit()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
    }

    /**
     * Used as a flag to tell QueryBuilder not to process the provided SQL
     * If $args are provided, we will assume that dev wants to use DB::prepare method with raw SQL
     *
     * @param string $sql
     * @param array  ...$args
     *
     * @return RawSQL
     */
    public static function raw($sql, ...$args)
    {
        return new RawSQL($sql, ...$args);
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
            throw new DatabaseQueryException($wpdb->last_query, $wpError->errors);
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
