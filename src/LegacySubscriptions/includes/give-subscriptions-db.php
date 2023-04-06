<?php
/**
 * Give Recurring Subscription DB
 *
 * @package     Give
 * @since       1.0
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @copyright   Copyright (c) 2016, GiveWP
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Give_Subscriptions_DB
 *
 * The Subscriptions DB Class.
 *
 * @since 2.19.0 - migrated from give-recurring
 * @since  1.0
 */
class Give_Subscriptions_DB extends Give_DB
{
    /**
     * Get things started.
     *
     * @access  public
     * @since   1.0
     */
    public function __construct()
    {
        global $wpdb;

        $this->table_name = $wpdb->prefix . 'give_subscriptions';
        $this->primary_key = 'id';
        $this->version = '1.1';

        parent::__construct();
    }

    /**
     * Get columns and formats
     *
     * @access  public
     *
     * @since 2.24.0 add payment_mode column
     * @since   1.0
     */
    public function get_columns()
    {
        return [
            'id' => '%d',
            'customer_id' => '%d',
            'period' => '%s',
            'frequency' => '%d',
            'initial_amount' => '%s',
            'recurring_amount' => '%s',
            'recurring_fee_amount' => '%F',
            'bill_times' => '%d',
            'transaction_id' => '%s',
            'parent_payment_id' => '%d',
            'product_id' => '%d',
            'created' => '%s',
            'expiration' => '%s',
            'payment_mode' => '%s',
            'status' => '%s',
            'notes' => '%s',
            'profile_id' => '%s',
        ];
    }


    /**
     * Get default column values
     *
     * @access  public
     * @since   1.0
     */
    public function get_column_defaults()
    {
        return [
            'customer_id' => 0,
            'period' => '',
            'frequency' => 1,
            'initial_amount' => '',
            'recurring_amount' => '',
            'recurring_fee_amount' => 0,
            'bill_times' => 0,
            'transaction_id' => '',
            'parent_payment_id' => 0,
            'product_id' => 0,
            'created' => date('Y-m-d H:i:s'),
            'expiration' => date('Y-m-d H:i:s'),
            'status' => '',
            'notes' => '',
            'profile_id' => '',
        ];
    }

    /**
     * Get subscription by specific data
     *
     * @since 1.6
     *
     * @param int $row_id
     *
     * @param int $column
     *
     * @return mixed|object
     */
    public function get_by($column, $row_id)
    {
        $cache_key = Give_Cache::get_key("{$column}_{$row_id}", [$column, $row_id], false);
        $subscription = Give_Recurring_Cache::get_subscription($cache_key);

        if (is_null($subscription)) {
            $subscription = parent::get_by($column, $row_id);
            Give_Recurring_Cache::set_subscription($cache_key, $subscription);
        }

        return $subscription;
    }

    /**
     * Get subscription
     *
     * @since 1.6
     *
     * @param int $row_id
     *
     * @return mixed|object
     */
    public function get($row_id)
    {
        $cache_key = Give_Cache::get_key($row_id, '', false);
        $subscription = Give_Recurring_Cache::get_subscription($cache_key);

        if (is_null($subscription)) {
            $subscription = parent::get($row_id);
            Give_Recurring_Cache::set_subscription($cache_key, $subscription);
        }

        return $subscription;
    }

    /**
     * Update subscription
     *
     * @since 1.6
     *
     * @param array $data
     * @param string $where
     *
     * @param int $row_id
     *
     * @return bool
     */
    public function update($row_id, $data = [], $where = '')
    {
        $status = parent::update($row_id, $data, $where);

        Give_Recurring_Cache::get_instance()->flush_on_subscription_update($status, $row_id, $data, $where);

        /**
         * Fire the action when subscriptions updated
         *
         * @since 1.6
         */
        do_action('give_subscription_updated', $status, $row_id, $data, $where);


        return $status;
    }

    /**
     * Create subscription
     *
     * @since 1.6
     *
     * @param array $data
     *
     * @return int|mixed
     */
    public function create($data)
    {
        $subcription_id = parent::insert($data, 'subscription');

        /**
         * Fire the action when subscriptions updated
         *
         * @since 1.6
         */
        do_action('give_subscription_inserted', $subcription_id, $data);

        return $subcription_id;
    }

    /**
     * Delete subscription
     *
     * @param int $subscription_id
     *
     * @return bool
     */
    public function delete($subscription_id = 0)
    {
        $subscriptionData = $this->get($subscription_id);
        $status = parent::delete($subscription_id);

        /**
         * Fire the action when subscriptions updated
         *
         * @since 1.6
         * @since 1.11.0 Added third parameter
         */
        do_action('give_subscription_deleted', $status, $subscription_id, $subscriptionData);

        return $status;
    }


    /**
     * Retrieve all subscriptions for a donor.
     *
     * @since   1.0
     *
     * @param array $args
     *
     * @access  public
     * @return Give_Subscription[]
     */
    public function get_subscriptions($args = [])
    {
        global $wpdb;

        $defaults = [
            'number' => 20,
            'offset' => 0,
            'search' => '',
            'form_id' => 0,
            'customer_id' => 0,
            'orderby' => 'id',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);

        if ($args['number'] < 1) {
            $args['number'] = 999999999999;
        }


        $args['orderby'] = !array_key_exists($args['orderby'], $this->get_columns()) ? 'id' : $args['orderby'];

        if ('amount' == $args['orderby']) {
            $args['orderby'] = 'amount+0';
        }

        $cache_key = Give_Cache::get_key('give_subscriptions', $args, false);
        $subscriptions = Give_Recurring_Cache::get_db_query($cache_key);

        // If no cache key, get subscriptions.
        if (is_null($subscriptions)) {
            $where = $this->generate_where_clause($args);

            $args['orderby'] = esc_sql($args['orderby']);
            $args['order'] = esc_sql($args['order']);

            $subscriptions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;",
                    absint($args['offset']),
                    absint($args['number'])
                ),
                OBJECT
            );

            if (!empty($subscriptions)) {
                foreach ($subscriptions as $key => $subscription) {
                    $subscriptions[$key] = new Give_Subscription($subscription);
                }

                Give_Recurring_Cache::set_db_query($cache_key, $subscriptions);
            }
        }

        return $subscriptions;
    }


    /**
     * Count the total number of subscriptions in the database.
     *
     * @param array $args
     *
     * @return int|array/null
     */
    public function count($args = [])
    {
        global $wpdb;

        $cache_key = Give_Cache::get_key('give_subscriptions_count', $args, false);
        $count = Give_Recurring_Cache::get_db_query($cache_key);
        $group_by_args = !empty($args['groupBy']) ? $args['groupBy'] : '';
        $return_count = empty($group_by_args);

        $result = null;

        if (null === $count) {
            $groupBy = $this->generate_groupby_clause($group_by_args);
            $where = $this->generate_where_clause($args);
            $count = $return_count ? "COUNT({$this->primary_key})" : "{$group_by_args}, COUNT({$this->primary_key})";
            $sql = "SELECT {$count} FROM {$this->table_name} {$where} {$groupBy};";

            $result = $return_count ? $wpdb->get_var($sql) : $wpdb->get_results($sql, ARRAY_A);

            // Simplify result if query for groupBy.
            if ($group_by_args && $result) {
                $temp = [];
                foreach ($result as $data) {
                    $temp[$data[$group_by_args]] = $data['COUNT(id)'];
                }

                $result = $temp;
            }

            Give_Recurring_Cache::set_db_query($cache_key, $result);
        }

        return $return_count ? absint($result) : $result;
    }

    /**
     * Create the table.
     *
     * @access  public
     * @since   1.0
     */
    public function create_table()
    {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") !== $this->table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = 'CREATE TABLE ' . $this->table_name . ' (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    customer_id bigint(20) NOT NULL,
                    period varchar(20) NOT NULL,
                    frequency bigint(20) DEFAULT "1" NOT NULL,
                    initial_amount decimal(18,10) NOT NULL,
                    recurring_amount decimal(18,10) NOT NULL,
                    recurring_fee_amount decimal(18,10) NOT NULL,
                    bill_times bigint(20) NOT NULL,
                    transaction_id varchar(60) NOT NULL,
                    parent_payment_id bigint(20) NOT NULL,
                    product_id bigint(20) NOT NULL,
                    created datetime NOT NULL,
                    expiration datetime NOT NULL,
                    status varchar(20) NOT NULL,
                    profile_id varchar(60) NOT NULL,
                    notes longtext NOT NULL,
                    PRIMARY KEY  (id),
                    KEY profile_id (profile_id),
                    KEY customer (customer_id),
                    KEY transaction (transaction_id),
                    INDEX customer_and_status ( customer_id, status)
                    ) CHARACTER SET utf8 COLLATE utf8_general_ci;';

            dbDelta($sql);

            update_option($this->table_name . '_db_version', $this->version);
        }
    }

    /**
     * Get Renewing Subscriptions
     *
     * @param string $period
     *
     * @return array|bool|mixed|null|object
     */
    public function get_renewing_subscriptions($period = '+1month')
    {
        global $wpdb;

        $args = [
            'number' => 99999,
            'status' => 'active',
            'offset' => 0,
            'orderby' => 'id',
            'order' => 'DESC',
            'expiration' => [
                'start' => date('Y-m-d H:i:s', strtotime($period . ' midnight')),
                'end' => date('Y-m-d H:i:s', strtotime($period . ' midnight') + (DAY_IN_SECONDS - 1)),
            ],
        ];

        $cache_key = Give_Cache::get_key('give_renewing_subscriptions', $args, false);
        $subscriptions = Give_Recurring_Cache::get_db_query($cache_key);

        if (is_null($subscriptions)) {
            $where = $this->generate_where_clause($args);

            $query = $wpdb->prepare(
                "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;",
                absint($args['offset']),
                absint($args['number'])
            );
            $subscriptions = $wpdb->get_results($query);
            Give_Recurring_Cache::set_db_query($cache_key, $subscriptions);
        }

        return $subscriptions;
    }

    /**
     * Get expiring subscriptions.
     *
     * @param string $period
     *
     * @return array|bool|mixed|null|object
     */
    public function get_expiring_subscriptions($period = '+1month')
    {
        global $wpdb;

        $args = [
            'number' => 99999,
            'status' => 'active',
            'offset' => 0,
            'orderby' => 'id',
            'order' => 'DESC',
            'expiration' => [
                'start' => date('Y-m-d H:i:s', strtotime($period . ' midnight')),
                'end' => date('Y-m-d H:i:s', strtotime($period . ' midnight') + (DAY_IN_SECONDS - 1)),
            ],
        ];

        $cache_key = Give_Cache::get_key('give_expiring_subscriptions', $args, false);
        $subscriptions = Give_Recurring_Cache::get_db_query($cache_key);

        if (is_null($subscriptions)) {
            $where = $this->generate_where_clause($args);
            $where .= ' AND `bill_times` != 0';
            $where .= ' AND ( SELECT COUNT(ID) FROM ' . $wpdb->prefix . 'posts WHERE `post_parent` = ' . $this->table_name . '.`parent_payment_id` OR `ID` = ' . $this->table_name . '.`parent_payment_id` ) + 1 >= `bill_times`';

            $query = $wpdb->prepare(
                "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;",
                absint($args['offset']),
                absint($args['number'])
            );
            $subscriptions = $wpdb->get_results($query);
            Give_Recurring_Cache::set_db_query($cache_key, $subscriptions);
        }

        return $subscriptions;
    }

    /**
     * Generate a cache key from args.
     *
     * @deprecated 1.6
     *
     * @param $args
     *
     * @param $prefix
     *
     * @return string
     */
    protected function generate_cache_key($prefix, $args)
    {
        return md5($prefix . serialize($args));
    }

    /**
     * Build the query args for subscriptions.
     *
     * @param array $args
     *
     * @return string The mysql "where" query part.
     */
    public function generate_where_clause($args = [])
    {
        $where = ' WHERE 1=1';

        // Specific ID.
        if (!empty($args['id'])) {
            if (is_array($args['id'])) {
                $ids = implode(',', array_map('intval', $args['id']));
            } else {
                $ids = intval($args['id']);
            }

            $where .= " AND `id` IN( {$ids} ) ";
        }

        // Specific donation forms.
        if (!empty($args['form_id'])) {
            if (is_array($args['form_id'])) {
                $form_ids = implode(',', array_map('intval', $args['form_id']));
            } else {
                $form_ids = intval($args['form_id']);
            }

            $where .= " AND `product_id` IN( {$form_ids} ) ";
        }

        // Specific parent payments
        if (!empty($args['parent_payment_id'])) {
            if (is_array($args['parent_payment_id'])) {
                $parent_payment_ids = implode(',', array_map('intval', $args['parent_payment_id']));
            } else {
                $parent_payment_ids = intval($args['parent_payment_id']);
            }

            $where .= " AND `parent_payment_id` IN( {$parent_payment_ids} ) ";
        }

        // @TODO: Remove after consolidating terminology.
        if (isset($args['donor_id']) && !empty($args['donor_id'])) {
            $args['customer_id'] = $args['donor_id'];
        }

        // Subscriptions for specific customers/donors
        if (!empty($args['customer_id'])) {
            if (is_array($args['customer_id'])) {
                $customer_ids = implode(',', array_map('intval', $args['customer_id']));
            } else {
                $customer_ids = intval($args['customer_id']);
            }

            $where .= " AND `customer_id` IN( {$customer_ids} ) ";
        }

        // Subscriptions for specific profile IDs
        if (!empty($args['profile_id'])) {
            if (is_array($args['profile_id'])) {
                $profile_ids = implode('\',\'', $args['profile_id']);
            } else {
                $profile_ids = $args['profile_id'];
            }

            $where .= " AND `profile_id` IN( '{$profile_ids}' ) ";
        }

        // Specific transaction IDs
        if (!empty($args['transaction_id'])) {
            if (is_array($args['transaction_id'])) {
                $transaction_ids = implode('\',\'', array_map('sanitize_text_field', $args['transaction_id']));
            } else {
                $transaction_ids = sanitize_text_field($args['transaction_id']);
            }

            $where .= " AND `transaction_id` IN( '{$transaction_ids}' ) ";
        }

        // Subscriptions for specific statuses
        if (!empty($args['status'])) {
            if (is_array($args['status'])) {
                $statuses = implode('\',\'', $args['status']);
                $where .= " AND `status` IN( '{$statuses}' ) ";
            } else {
                $statuses = $args['status'];
                $where .= " AND `status` = '{$statuses}' ";
            }
        }

        if (!empty($args['date'])) {
            $where .= $this->mysql_where_args_date($args);
        }

        if (!empty($args['expiration'])) {
            $where .= $this->mysql_where_args_expiration($args);
        }

        if (!empty($args['search'])) {
            $where .= $this->mysql_where_args_search($args);
        }

        return apply_filters('give_subscriptions_mysql_query', $where);
    }

    /**
     * Build the query args for subscriptions.
     *
     * @param string $groupby
     *
     * @return string The mysql "where" query part.
     */
    private function generate_groupby_clause($groupby = '')
    {
        if (!$groupby) {
            return '';
        }

        return "GROUP BY {$groupby}";
    }

    /**
     * @since 2.26.0 Replace deprecated get_page_by_title() with give_get_page_by_title().
     *
     * @param $args
     *
     * @return string
     */
    private function mysql_where_args_search($args)
    {
        $where = '';
        $donors_db = new Give_DB_Donors();
        if (is_email($args['search'])) {
            $customer = new Give_Donor($args['search']);
            if ($customer && $customer->id > 0) {
                $where = " AND `customer_id` = " . absint($customer->id) . "";
            }
        } elseif (false !== strpos($args['search'], 'txn:')) {
            $args['search'] = trim(str_replace('txn:', '', $args['search']));
            $where .= " AND `transaction_id` = '" . esc_sql($args['search']) . "'";
        } elseif (false !== strpos($args['search'], 'profile_id:')) {
            $args['search'] = trim(str_replace('profile_id:', '', $args['search']));
            $where .= " AND `profile_id` = '" . esc_sql($args['search']) . "'";
        } elseif (false !== strpos($args['search'], 'form_id:')) {
            $args['search'] = trim(str_replace('form_id:', '', $args['search']));
            $where .= " AND `product_id` = " . absint($args['search']) . "";
        } elseif (false !== strpos($args['search'], 'product_id:')) {
            $args['search'] = trim(str_replace('product_id:', '', $args['search']));
            $where .= " AND `product_id` = " . absint($args['search']) . "";
        } elseif (false !== strpos($args['search'], 'customer_id:')) {
            $args['search'] = trim(str_replace('customer_id:', '', $args['search']));
            $where .= " AND `customer_id` = '" . esc_sql($args['search']) . "'";
        } elseif (false !== strpos($args['search'], 'id:') || is_numeric($args['search'])) {
            $args['search'] = trim(str_replace('id:', '', $args['search']));
            $where .= " AND `id` = " . absint($args['search']) . "";
        } else {
            // See if search matches a product name
            $form = give_get_page_by_title(trim($args['search']), OBJECT, 'give_forms');
            if ($form) {
                $args['search'] = $form->ID;
                $where .= " AND `product_id` = " . absint($args['search']) . "";
            } else {
                global $wpdb;
                $query = $wpdb->prepare(
                    "
				SELECT id,name FROM {$donors_db->table_name}
				WHERE name
				LIKE '%s'",
                    '%' . $args['search'] . '%'
                );
                $subscription_donor_id = [];
                $donor_ids = $wpdb->get_results($query, ARRAY_A);
                if (!empty($donor_ids) && count($donor_ids) > 0) {
                    foreach ($donor_ids as $key => $val) {
                        $subscription_donor_id[] = absint($val['id']);
                    }
                }
                $subscription_donor_id = implode(',', array_map('intval', $subscription_donor_id));
                $where .= " AND {$this->table_name}.customer_id IN ({$subscription_donor_id})";
            }
        }// End if().
        return $where;
    }

    /**
     * @param $args
     *
     * @return string
     */
    private function mysql_where_args_date($args)
    {
        $where = '';
        if (is_array($args['date'])) {
            if (!empty($args['date']['start'])) {
                $start = date('Y-m-d H:i:s', strtotime($args['date']['start']));
                $where .= " AND `expiration` >= '{$start}'";
            }
            if (!empty($args['date']['end'])) {
                $end = date('Y-m-d H:i:s', strtotime($args['date']['end']));
                $where .= " AND `expiration` <= '{$end}'";
            }
        } else {
            $year = date('Y', strtotime($args['date']));
            $month = date('m', strtotime($args['date']));
            $day = date('d', strtotime($args['date']));
            $where .= " AND $year = YEAR ( created ) AND $month = MONTH ( created ) AND $day = DAY ( created )";
        }

        return $where;
    }

    /**
     * @param $args
     *
     * @return string
     */
    private function mysql_where_args_expiration($args)
    {
        $where = '';

        if (is_array($args['expiration'])) {
            if (!empty($args['expiration']['start'])) {
                $start = date('Y-m-d H:i:s', strtotime($args['expiration']['start']));

                $where .= " AND `expiration` >= '{$start}'";
            }

            if (!empty($args['expiration']['end'])) {
                $end = date('Y-m-d H:i:s', strtotime($args['expiration']['end']));

                $where .= " AND `expiration` <= '{$end}'";
            }
        } else {
            $year = date('Y', strtotime($args['expiration']));
            $month = date('m', strtotime($args['expiration']));
            $day = date('d', strtotime($args['expiration']));

            $where .= " AND $year = YEAR ( expiration ) AND $month = MONTH ( expiration ) AND $day = DAY ( expiration )";
        }

        return $where;
    }

}
