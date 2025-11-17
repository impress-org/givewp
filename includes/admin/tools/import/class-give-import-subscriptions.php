<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;

if (!class_exists('Give_Import_Subscriptions')) {

    /**
     * Give_Import_Subscriptions.
     *
     * @since 4.11.0
     */
    final class Give_Import_Subscriptions
    {
        /**
         * Importer type
         *
         * @var string
         */
        private $importer_type = 'import_subscriptions';

        /**
         * Instance.
         *
         * @var static
         */
        private static $instance;

        /**
         * Importing rows per page.
         *
         * @var int
         */
        public static $per_page = 25;

        /**
         * CSV valid redirect URL
         *
         * @var string|bool
         */
        public $is_csv_valid = false;

        /**
         * Singleton
         * @since 4.11.0
         */
        private function __construct()
        {
            self::$per_page = !empty($_GET['per_page']) ? absint($_GET['per_page']) : self::$per_page;
        }

        /**
         * Get instance
         *
         * @since 4.11.0
         * @return static
         */
        public static function get_instance()
        {
            if (null === static::$instance) {
                self::$instance = new static();
            }

            return self::$instance;
        }

        /**
         * Setup
         *
         * @since 4.11.0
         */
        public function setup()
        {
            $this->setup_hooks();
        }

        /**
         * Setup Hooks.
         *
         * @since 4.11.0
         */
        private function setup_hooks()
        {
            if (!$this->is_subscriptions_import_page()) {
                return;
            }

            // Do not render main import tools page.
            remove_action('give_admin_field_tools_import', ['Give_Settings_Import', 'render_import_field']);

            // Render subscriptions import page
            add_action('give_admin_field_tools_import', [$this, 'render_page']);

            // Print the HTML.
            add_action('give_tools_import_subscriptions_form_start', [$this, 'html'], 10);

            // Handle submit
            add_action('give-tools_save_import', [$this, 'save']);

            add_action('give-tools_update_notices', [$this, 'update_notices'], 11, 1);

            // Used to add submit button.
            add_action('give_tools_import_subscriptions_form_end', [$this, 'submit'], 10);
        }

        /**
         * Update notice
         *
         * @since 4.11.0
         * @param $messages
         *
         * @return mixed
         */
        public function update_notices($messages)
        {
            if (!empty($_GET['tab']) && 'import' === give_clean($_GET['tab'])) {
                unset($messages['give-setting-updated']);
            }

            return $messages;
        }

        /**
         * Print submit and nonce button.
         *
         * @since 4.11.0
         */
        public function submit()
        {
            wp_nonce_field('give-save-settings', '_give-save-settings');
            ?>
            <input type="hidden" class="import-step" id="import-step" name="step"
                   value="<?php echo esc_attr($this->get_step()); ?>" />
            <input type="hidden" class="importer-type" value="<?php echo esc_attr($this->importer_type); ?>" />
            <?php
        }

        /**
         * Print the HTML for importer.
         *
         * @since 4.11.0
         */
        public function html()
        {
            $step = $this->get_step();

            // Show progress.
            $this->render_progress();
            ?>
            <section>
                <table
                    class="widefat export-options-table give-table <?php echo esc_attr("step-{$step}"); ?> <?php echo esc_attr((1 === $step && !empty($this->is_csv_valid) ? 'give-hidden' : '')); ?>  "
                    id="<?php echo esc_attr("step-{$step}"); ?>">
                    <tbody>
                    <?php
                    switch ($step) {
                        case 1:
                            $this->render_media_csv();
                            break;

                        case 2:
                            $this->render_dropdown();
                            break;

                        case 3:
                            $this->start_import();
                            break;

                        case 4:
                            $this->import_success();
                    }
                    if (false === $this->check_for_dropdown_or_import()) {
                        ?>
                        <tr valign="top">
                            <th>
                                <input type="submit"
                                       class="button button-primary button-large button-secondary <?php echo esc_attr("step-{$step}"); ?>"
                                       id="recount-stats-submit"
                                       value="
                                           <?php
                                           echo esc_attr(apply_filters('give_import_subscription_submit_button_text', __('Submit', 'give')));
                                           ?>
                                            " />
                            </th>
                            <th>
                                <?php
                                do_action('give_import_subscription_submit_button');
                                ?>
                            </th>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </section>
            <?php
        }

        /**
         * Show success notice
         *
         * @since 4.11.0
         */
        public function import_success()
        {
            check_admin_referer('give_subscription_import_success');

            $delete_csv = (!empty($_GET['delete_csv']) ? absint($_GET['delete_csv']) : false);
            $csv = (!empty($_GET['csv']) ? absint($_GET['csv']) : false);
            if (!empty($delete_csv) && !empty($csv)) {
                wp_delete_attachment($csv, true);
            }

            $report = $this->get_report();

            $total = (int)$_GET['total'];
            --$total;
            $success = (bool)$_GET['success'];
            $dry_run = empty($_GET['dry_run']) ? 0 : absint($_GET['dry_run']);
            ?>
            <tr valign="top" class="give-import-dropdown">
                <th colspan="2">
                    <h2>
                        <?php
                        if ($success) {
                            if ($dry_run) {
                                printf(
                                    _n('Dry run import complete! %s row processed', 'Dry run import complete! %s rows processed', $total, 'give'),
                                    "<strong>{$total}</strong>"
                                );
                            } else {
                                printf(
                                    _n('Import complete! %s row processed', 'Import complete! %s rows processed', $total, 'give'),
                                    "<strong>{$total}</strong>"
                                );
                            }
                        } else {
                            printf(
                                _n('Failed to import %s row', 'Failed to import %s rows', $total, 'give'),
                                "<strong>{$total}</strong>"
                            );
                        }
                        ?>
                    </h2>

                    <?php
                    $text = __('Import Subscriptions', 'give');
                    $query_arg = [
                        'post_type' => 'give_forms',
                        'page' => 'give-tools',
                        'tab' => 'import',
                    ];
                    if ($success) {
                        if ($dry_run) {
                            $query_arg = [
                                'post_type' => 'give_forms',
                                'page' => 'give-tools',
                                'tab' => 'import',
                                'importer-type' => 'import_subscriptions',
                            ];
                            $text = __('Start Import', 'give');
                        } else {
                            $query_arg = [
                                'post_type' => 'give_forms',
                                'page' => 'give-subscriptions',
                            ];
                            $text = __('View Subscriptions', 'give');
                        }
                    }

                    if (!empty($report)) {
                        if (isset($report['create_subscription'])) {
                            echo '<p>' . sprintf(_n('%s subscription created', '%s subscriptions created', (int)$report['create_subscription'], 'give'), (int)$report['create_subscription']) . '</p>';
                        }
                        if (isset($report['failed_subscription'])) {
                            echo '<p>' . sprintf(_n('%s subscription failed', '%s subscriptions failed', (int)$report['failed_subscription'], 'give'), (int)$report['failed_subscription']) . '</p>';
                        }
                        if (!empty($report['failed_subscription_initial_donation'])) {
                            echo '<p>' . sprintf(_n('%s initial donation failed', '%s initial donations failed', (int)$report['failed_subscription_initial_donation'], 'give'), (int)$report['failed_subscription_initial_donation']) . '</p>';
                        }
                        if (!empty($report['errors']) && is_array($report['errors'])) {
                            echo '<div class="notice notice-error" style="margin-top:10px;">';
                            echo '<p><strong>' . esc_html__('Errors', 'give') . ':</strong></p>';
                            echo '<ul style="margin-left:20px;list-style:disc;">';
                            foreach ($report['errors'] as $err) {
                                echo '<li>' . esc_html($err) . '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }
                    }
                    ?>

                    <p>
                        <a class="button button-large button-secondary"
                           href="<?php echo esc_url(add_query_arg($query_arg, admin_url('edit.php'))); ?>"><?php echo esc_html($text); ?></a>
                    </p>
                </th>
            </tr>
            <?php
        }

        /**
         * Start Import
         * @since 4.11.0
         */
        public function start_import()
        {
            $this->reset_report();

            $csv = absint($_REQUEST['csv']);
            $delimiter = (!empty($_REQUEST['delimiter']) ? give_clean($_REQUEST['delimiter']) : 'csv');
            $index_start = 1;
            $next = true;
            $total = self::get_csv_total($csv);
            if (self::$per_page < $total) {
                $total_ajax = ceil($total / self::$per_page);
                $index_end = self::$per_page;
            } else {
                $total_ajax = 1;
                $index_end = $total;
                $next = false;
            }
            $current_percentage = 100 / ($total_ajax + 1);

            ?>
            <tr valign="top" class="give-import-dropdown">
                <th colspan="2">
                    <h2 id="give-import-title"><?php _e('Importing', 'give'); ?></h2>
                    <p class="give-field-description"><?php _e('Your subscriptions are now being imported...', 'give'); ?></p>
                </th>
            </tr>

            <tr valign="top" class="give-import-dropdown">
                <th colspan="2">
                    <span class="spinner is-active"></span>
                    <div class="give-progress"
                         data-current="1"
                         data-total_ajax="<?php echo esc_attr((int)$total_ajax); ?>"
                         data-start="<?php echo esc_attr((int)$index_start); ?>"
                         data-end="<?php echo esc_attr((int)$index_end); ?>"
                         data-next="<?php echo esc_attr((int)$next); ?>"
                         data-total="<?php echo esc_attr((int)$total); ?>"
                         data-per_page="<?php echo esc_attr((int)self::$per_page); ?>">

                        <div style="width: <?php echo esc_attr((float)$current_percentage); ?>%"></div>
                    </div>
                    <input type="hidden" value="3" name="step">
                    <input type="hidden" value='<?php echo esc_attr(maybe_serialize($_REQUEST['mapto'])); ?>' name="mapto" class="mapto">
                    <input type="hidden" value="<?php echo esc_attr((int)$csv); ?>" name="csv" class="csv">
                    <input type="hidden" value="<?php echo esc_attr(isset($_REQUEST['mode']) ? sanitize_text_field((string)$_REQUEST['mode']) : ''); ?>" name="mode" class="mode">
                    <input type="hidden" value="<?php echo esc_attr(isset($_REQUEST['create_user']) ? (int)$_REQUEST['create_user'] : 0); ?>" name="create_user" class="create_user">
                    <input type="hidden" value="<?php echo esc_attr(isset($_REQUEST['delete_csv']) ? (int)$_REQUEST['delete_csv'] : 0); ?>" name="delete_csv" class="delete_csv">
                    <input type="hidden" value="<?php echo esc_attr($delimiter); ?>" name="delimiter">
                    <input type="hidden" value="<?php echo esc_attr(isset($_REQUEST['dry_run']) ? (int)$_REQUEST['dry_run'] : 0); ?>" name="dry_run">
                    <input type="hidden" value='<?php echo esc_attr(maybe_serialize(self::get_importer($csv, 0, $delimiter))); ?>' name="main_key" class="main_key">
                </th>
            </tr>
            <?php
        }

        /**
         * Validate required mapped fields
         * @since 4.11.0
         */
        public function check_for_dropdown_or_import()
        {
            $return = true;
            if (isset($_REQUEST['mapto'])) {
                $mapto = (array)$_REQUEST['mapto'];
                $required = ['form_id', 'donor_id', 'period', 'frequency', 'amount', 'status'];
                foreach ($required as $key) {
                    if (false === in_array($key, $mapto)) {
                        Give_Admin_Settings::add_error('give-import-csv-subscriptions', sprintf(__('A column must be mapped to "%s".', 'give'), $key));
                        $return = false;
                    }
                }
            } else {
                $return = false;
            }

            return $return;
        }

        /**
         * Print the Dropdown option for CSV.
         * @since 4.11.0
         */
        public function render_dropdown()
        {
            if (!$this->is_nonce_valid()) {
                Give_Admin_Settings::add_error('give-import-csv', __('Something went wrong.', 'give'));
                ?>
                <input type="hidden" name="csv_not_valid" class="csv_not_valid" value="<?php echo esc_attr(give_import_page_url()); ?>" />
                <?php
                wp_die();
            }

            $csv = (int)$_GET['csv'];
            $delimiter = (!empty($_GET['delimiter']) ? give_clean($_GET['delimiter']) : 'csv');

            if (!$this->is_valid_csv($csv)) {
                $url = give_import_page_url();
                ?>
                <input type="hidden" name="csv_not_valid" class="csv_not_valid" value="<?php echo esc_attr($url); ?>" />
                <?php
            } else {
                ?>
                <tr valign="top" class="give-import-dropdown">
                    <th colspan="2">
                        <h2 id="give-import-title"><?php _e('Map CSV fields to subscriptions', 'give'); ?></h2>

                        <p class="give-import-donation-required-fields-title"><?php _e('Required Fields', 'give'); ?></p>

                        <p class="give-field-description"><?php _e('These fields are required for the import to be submitted', 'give'); ?></p>

                        <ul class="give-import-subscription-required-fields">
                            <li class="give-import-subscription-required-donorId" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Form ID', 'give'); ?></span>
                            </li>
                            <li class="give-import-subscription-required-donationFormId" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Donor ID or Donor Email', 'give'); ?></span>
                            </li>
                            <li class="give-import-subscription-required-period" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Period', 'give'); ?> (day, week, month, year)</span>
                            </li>
                            <li class="give-import-subscription-required-frequency" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Frequency', 'give'); ?></span>
                            </li>
                            <li class="give-import-subscription-required-amount" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Amount (donor facing amount)', 'give'); ?></span>
                            </li>
                            <li class="give-import-subscription-required-status" title="Please configure all required fields to start the import process.">
                                <span class="give-import-donation-required-text"><?php _e('Status', 'give'); ?> (active, expired, cancelled, suspended, paused, pending)</span>
                            </li>
                        </ul>

                        <p class="give-field-description"><?php _e('Select fields from your CSV file to map against subscription fields or to ignore during import.', 'give'); ?></p>
                    </th>
                </tr>

                <tr valign="top" class="give-import-dropdown">
                    <th><b><?php _e('Column name', 'give'); ?></b></th>
                    <th><b><?php _e('Map to field', 'give'); ?></b></th>
                </tr>

                <?php
                $selectedOptions = [];
                $raw_key = $this->get_importer($csv, 0, $delimiter);
                $mapto = (array)(isset($_REQUEST['mapto']) ? $_REQUEST['mapto'] : []);

                foreach ($raw_key as $index => $value) {
                    ?>
                    <tr valign="middle" class="give-import-option">
                        <th><?php echo esc_html($value); ?></th>
                        <th>
                            <?php $this->get_columns($index, $value, $mapto, $selectedOptions); ?>
                        </th>
                    </tr>
                    <?php
                }
            }
        }

        /**
         * Determine selected option by heuristics
         */
        public function selected($option_value, $value)
        {
            $option_value = strtolower($option_value);
            $value = strtolower($value);

            $selected = '';
            if (stristr($value, $option_value)) {
                $selected = 'selected';
            }

            return $selected;
        }

        /**
         * Print the columns from the CSV.
         */
        private function get_columns($index, $value = false, $mapto = [], &$selectedOptions = [])
        {
            $default = give_import_default_options();
            $current_mapto = (string)(!empty($mapto[$index]) ? $mapto[$index] : '');
            ?>
            <select name="mapto[<?php echo esc_attr($index); ?>]">
                <?php $this->get_dropdown_option_html($default, $current_mapto, $value, $selectedOptions); ?>

                <optgroup label="<?php _e('Subscriptions', 'give'); ?>">
                    <?php $this->get_dropdown_option_html($this->get_subscription_options(), $current_mapto, $value, $selectedOptions); ?>
                </optgroup>
            </select>
            <?php
        }

        /**
         * Print the option html for select in importer
         */
        public function get_dropdown_option_html($options, $current_mapto, $value = false, &$selectedOptions = [])
        {
            foreach ($options as $option => $option_value) {
                $ignore = [];
                if (isset($option_value['ignore']) && is_array($option_value['ignore'])) {
                    $ignore = $option_value['ignore'];
                    unset($option_value['ignore']);
                }

                $option_value_texts = (array)$option_value;
                $option_text = $option_value_texts[0];

                $selected = false;

                if ($current_mapto === $option && !in_array($option, $selectedOptions)) {
                    $selected = 'selected';
                    $selectedOptions[] = $option;
                } else {
                    if (!in_array($value, $ignore) && !in_array($option, $selectedOptions)) {
                        foreach ($option_value_texts as $option_value_text) {
                            $selected = $this->selected($option_value_text, $value);
                            if ($selected) {
                                $selectedOptions[] = $option;
                                break;
                            }
                        }
                        // Extra heuristics: match header to option key by normalized token
                        if (!$selected) {
                            $normalize = static function ($str) {
                                $str = strtolower((string)$str);
                                return preg_replace('/[^a-z0-9]/', '', $str);
                            };

                            $valueNorm = $normalize($value);
                            $optionNorm = $normalize($option);

                            if ($valueNorm && $optionNorm && $valueNorm === $optionNorm) {
                                $selected = 'selected';
                                $selectedOptions[] = $option;
                            } else {
                                // Try normalized match against visible label too
                                $labelNorm = $normalize($option_text);
                                if ($labelNorm && $valueNorm && $labelNorm === $valueNorm) {
                                    $selected = 'selected';
                                    $selectedOptions[] = $option;
                                }
                            }
                        }
                    }
                }
                ?>
                <option value="<?php echo esc_attr($option); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($option_text); ?></option>
                <?php
            }
        }

        /**
         * Get column count of csv file.
         */
        public function get_csv_total($file_id)
        {
            $total = false;
            if ($file_id) {
                $file_dir = get_attached_file($file_id);
                if ($file_dir) {
                    $total = $this->get_csv_data_from_file_dir($file_dir);
                }
            }

            return $total;
        }

        /**
         * Get data from File
         */
        public function get_csv_data_from_file_dir($file_dir)
        {
            $total = false;
            if ($file_dir) {
                $file = new SplFileObject($file_dir, 'r');
                $file->seek(PHP_INT_MAX);
                $total = $file->key() + 1;
            }

            return $total;
        }

        /**
         * Read a slice of CSV rows for subscriptions import
         */
        public function get_subscription_data_from_csv($file_id, $start, $end, $delimiter = 'csv')
        {
            $delimiter = (string)apply_filters('give_import_delimiter_set', $delimiter);
            $file_dir = give_get_file_data_by_file_id($file_id);
            return give_get_raw_data_from_file($file_dir, $start, $end, $delimiter);
        }

        /**
         * Get the CSV fields title from the CSV.
         */
        public function get_importer($file_id, $index = 0, $delimiter = 'csv')
        {
            $delimiter = (string)apply_filters('give_import_delimiter_set', $delimiter);

            $raw_data = false;
            $file_dir = get_attached_file($file_id);
            if ($file_dir) {
                if (false !== ($handle = fopen($file_dir, 'r'))) {
                    $raw_data = fgetcsv($handle, $index, $delimiter);
                    if (isset($raw_data[0])) {
                        $raw_data[0] = $this->remove_utf8_bom($raw_data[0]);
                    }
                }
            }

            return $raw_data;
        }

        /**
         * Remove UTF-8 BOM signature.
         */
        public function remove_utf8_bom($string)
        {
            if ('efbbbf' === substr(bin2hex($string), 0, 6)) {
                $string = substr($string, 3);
            }

            return $string;
        }

        /**
         * Render progress steps
         */
        public function render_progress()
        {
            $step = $this->get_step();
            ?>
            <ol class="give-progress-steps">
                <li class="<?php echo esc_attr(1 === $step ? 'active' : ''); ?>">
                    <?php _e('Upload CSV file', 'give'); ?>
                </li>
                <li class="<?php echo esc_attr(2 === $step ? 'active' : ''); ?>">
                    <?php _e('Column mapping', 'give'); ?>
                </li>
                <li class="<?php echo esc_attr(3 === $step ? 'active' : ''); ?>">
                    <?php _e('Import', 'give'); ?>
                </li>
                <li class="<?php echo esc_attr(4 === $step ? 'active' : ''); ?>">
                    <?php _e('Done!', 'give'); ?>
                </li>
            </ol>
            <?php
        }

        /**
         * Will return the import step.
         */
        public function get_step()
        {
            $step = (int)(isset($_REQUEST['step']) ? give_clean($_REQUEST['step']) : 0);
            $on_step = 1;

            if (empty($step) || 1 === $step) {
                $on_step = 1;
            } elseif ($this->check_for_dropdown_or_import()) {
                $on_step = 3;
            } elseif (2 === $step) {
                $on_step = 2;
            } elseif (4 === $step) {
                $on_step = 4;
            }

            return $on_step;
        }

        /**
         * Render subscriptions import page
         */
        public function render_page()
        {
            include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-subscriptions.php';
        }

        /**
         * Dry Run checkbox and helper
         */
        public function give_import_subscription_submit_button_render_media_csv()
        {
            $dry_run = isset($_POST['dry_run']) ? absint($_POST['dry_run']) : 1;
            ?>
            <div>
                <label for="dry_run">
                    <input type="hidden" name="dry_run" value="0" />
                    <input type="checkbox" name="dry_run" id="dry_run" class="dry_run"
                           value="1" <?php checked(1, $dry_run); ?>>
                    <strong><?php _e('Dry Run', 'give'); ?></strong>
                </label>
                <p class="give-field-description">
                    <?php _e('Preview what the import would look like without making any changes.', 'give'); ?>
                </p>
            </div>
            <?php
        }

        /**
         * Change submit button text on first step
         */
        function give_import_subscription_submit_text_render_media_csv($text)
        {
            return __('Begin Import', 'give');
        }

        /**
         * Add CSV upload HTMl
         */
        public function render_media_csv()
        {
            add_filter(
                'give_import_subscription_submit_button_text',
                [$this, 'give_import_subscription_submit_text_render_media_csv']
            );
            add_action(
                'give_import_subscription_submit_button',
                [$this, 'give_import_subscription_submit_button_render_media_csv']
            );
            ?>
            <tr valign="top">
                <th colspan="2">
                    <h2 id="give-import-title"><?php _e('Import subscriptions from a CSV file', 'give'); ?></h2>
                    <p class="give-field-description"><?php _e('This tool allows you to import subscription data via a CSV file.', 'give'); ?></p>
                </th>
            </tr>
            <?php
            $csv = (isset($_POST['csv']) ? give_clean($_POST['csv']) : '');
            $csv_id = (isset($_POST['csv_id']) ? give_clean($_POST['csv_id']) : '');
            $delimiter = (isset($_POST['delimiter']) ? give_clean($_POST['delimiter']) : 'csv');
            $mode = empty($_POST['mode']) ? 'disabled' : (give_is_setting_enabled(give_clean($_POST['mode'])) ? 'enabled' : 'disabled');
            $create_user = empty($_POST['create_user']) ? 'disabled' : (give_is_setting_enabled(give_clean($_POST['create_user'])) ? 'enabled' : 'disabled');
            $delete_csv = empty($_POST['delete_csv']) ? 'enabled' : (give_is_setting_enabled(give_clean($_POST['delete_csv'])) ? 'enabled' : 'disabled');

            if (empty($csv_id) || !$this->is_valid_csv($csv_id, $csv)) {
                $csv_id = $csv = '';
            }
            $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : self::$per_page;

            $sample_file_text = sprintf(
                '%s <a href="%s">%s</a>.',
                __('Download the sample file', 'give'),
                esc_url(GIVE_PLUGIN_URL . 'sample-data/sample-subscriptions.csv'),
                __('here', 'give')
            );

            $csv_description = sprintf(
                '%1$s %2$s',
                __('The file must be a Comma Separated Values (CSV) file type only.', 'give'),
                $sample_file_text
            );

            $settings = [
                [
                    'id' => 'csv',
                    'name' => __('Choose a CSV file:', 'give'),
                    'type' => 'file',
                    'attributes' => [
                        'editing' => 'false',
                        'library' => 'text',
                    ],
                    'description' => $csv_description,
                    'fvalue' => 'url',
                    'default' => $csv,
                ],
                [
                    'id' => 'csv_id',
                    'type' => 'hidden',
                    'value' => $csv_id,
                ],
                [
                    'id' => 'delimiter',
                    'name' => __('CSV Delimiter:', 'give'),
                    'description' => __('If your CSV uses a different delimiter (like a tab), set that here.', 'give'),
                    'default' => $delimiter,
                    'type' => 'select',
                    'options' => [
                        'csv' => __('Comma', 'give'),
                        'tab-separated-values' => __('Tab', 'give'),
                    ],
                ],
                [
                    'id' => 'mode',
                    'name' => __('Test Mode:', 'give'),
                    'description' => __('Select whether these subscriptions should be marked as "test".', 'give'),
                    'default' => $mode,
                    'type' => 'radio_inline',
                    'options' => [
                        'enabled' => __('Enabled', 'give'),
                        'disabled' => __('Disabled', 'give'),
                    ],
                ],
                [
                    'id' => 'create_user',
                    'name' => __('Create WP users for new donors:', 'give'),
                    'description' => __('Automatically create a WordPress user account for newly created donors.  This is required for donors to access their Donor Dashboard and manage their subscriptions.', 'give'),
                    'default' => $create_user,
                    'type' => 'radio_inline',
                    'options' => [
                        'enabled' => __('Enabled', 'give'),
                        'disabled' => __('Disabled', 'give'),
                    ],
                ],
                [
                    'id' => 'delete_csv',
                    'name' => __('Delete CSV after import:', 'give'),
                    'description' => __('Delete the uploaded CSV from the Media Library after import.', 'give'),
                    'default' => $delete_csv,
                    'type' => 'radio_inline',
                    'options' => [
                        'enabled' => __('Enabled', 'give'),
                        'disabled' => __('Disabled', 'give'),
                    ],
                ],
                [
                    'id' => 'per_page',
                    'name' => __('Process Rows Per Batch:', 'give'),
                    'type' => 'number',
                    'description' => __('Determine how many rows you would like to import per cycle.', 'give'),
                    'default' => $per_page,
                    'class' => 'give-text-small',
                ],
            ];

            $settings = apply_filters('give_import_file_upload_html', $settings);

            if (empty($this->is_csv_valid)) {
                Give_Admin_Settings::output_fields($settings, 'give_settings');
            } else {
                ?>
                <input type="hidden" name="is_csv_valid" class="is_csv_valid"
                       value="<?php echo esc_attr($this->is_csv_valid); ?>">
                <?php
            }
        }

        /**
         * Run when user click on the submit button.
         */
        public function save()
        {
            if (!$this->is_nonce_valid()) {
                wp_die();
            }

            $step = $this->get_step();

            if (1 === $step) {
                $csv_id = absint($_POST['csv_id']);

                if ($this->is_valid_csv($csv_id, esc_url($_POST['csv']))) {
                    $url = give_import_page_url(
                        [
                            'step' => '2',
                            'importer-type' => $this->importer_type,
                            'csv' => $csv_id,
                            'delimiter' => isset($_REQUEST['delimiter']) ? give_clean($_REQUEST['delimiter']) : 'csv',
                            'mode' => empty($_POST['mode']) ? '0' : (give_is_setting_enabled(give_clean($_POST['mode'])) ? '1' : '0'),
                            'create_user' => empty($_POST['create_user']) ? '0' : (give_is_setting_enabled(give_clean($_POST['create_user'])) ? '1' : '0'),
                            'delete_csv' => empty($_POST['delete_csv']) ? '1' : (give_is_setting_enabled(give_clean($_POST['delete_csv'])) ? '1' : '0'),
                            'per_page' => isset($_POST['per_page']) ? absint($_POST['per_page']) : self::$per_page,
                            'dry_run' => isset($_POST['dry_run']) ? absint($_POST['dry_run']) : 0,
                        ]
                    );

                    $this->is_csv_valid = wp_nonce_url($url, 'give-save-settings', '_give-save-settings');
                }
            }
        }

        /**
         * Check if user uploaded csv is valid or not.
         */
        private function is_valid_csv($csv = false, $match_url = '')
        {
            $is_valid_csv = true;

            if ($csv) {
                $csv_url = wp_get_attachment_url($csv);

                $delimiter = (!empty($_REQUEST['delimiter']) ? give_clean($_REQUEST['delimiter']) : 'csv');

                if (
                    !$csv_url ||
                    (!empty($match_url) && ($csv_url !== $match_url)) ||
                    (($mime_type = get_post_mime_type($csv)) && !strpos($mime_type, $delimiter))
                ) {
                    $is_valid_csv = false;
                    Give_Admin_Settings::add_error('give-import-csv', __('Please upload or provide a valid CSV file.', 'give'));
                }
            } else {
                $is_valid_csv = false;
                Give_Admin_Settings::add_error('give-import-csv', __('Please upload or provide a valid CSV file.', 'give'));
            }

            return $is_valid_csv;
        }

        /**
         * Get if current page import donations page or not
         */
        private function is_subscriptions_import_page()
        {
            return 'import' === give_get_current_setting_tab() &&
                isset($_GET['importer-type']) &&
                $this->importer_type === give_clean($_GET['importer-type']);
        }

        /**
         * Nonce validation
         */
        private function is_nonce_valid()
        {
            return !empty($_REQUEST['_give-save-settings']) && wp_verify_nonce($_REQUEST['_give-save-settings'], 'give-save-settings');
        }

        /**
         * Import a single subscription row from CSV
         *
         * @param array $raw_key
         * @param array $row_data
         * @param array $main_key
         * @param array $import_setting
         * @return bool|int|string
         */
        public function import_row($raw_key, $row_data, $main_key = [], $import_setting = [])
        {
            $report = $this->get_report();
            $dry_run = isset($import_setting['dry_run']) ? (bool)$import_setting['dry_run'] : false;

            if (
                empty($row_data) || (is_array($row_data) && 0 === count(array_filter($row_data, function ($v) {
                    return $v !== null && $v !== '';
                })))
            ) {
                return true;
            }

            if (!is_array($row_data) || count($row_data) !== count($raw_key)) {
                $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                $this->update_report($report);
                return false;
            }

            $data = array_combine($raw_key, $row_data);

            $required = ['form_id', 'period', 'frequency', 'amount', 'status'];
            foreach ($required as $key) {
                if (empty($data[$key]) && '0' !== (string)(isset($data[$key]) ? $data[$key] : '')) {
                    $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                    $report['errors'][] = sprintf(__('Row %1$d: Missing required field "%2$s"', 'give'), (int)(isset($import_setting['row_key']) ? $import_setting['row_key'] : 0), $key);
                    $this->update_report($report);
                    return 'Missing required field ' . $key;
                }
            }
            if (empty($data['donor_id']) && empty($data['email'])) {
                $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                $report['errors'][] = sprintf(__('Row %d: Either donor_id or email is required to resolve the donor', 'give'), (int)(isset($import_setting['row_key']) ? $import_setting['row_key'] : 0));
                $this->update_report($report);
                return 'Missing donor identifier (donor_id or email)';
            }

            try {
                $currency = !empty($data['currency']) && array_key_exists($data['currency'], give_get_currencies_list()) ? $data['currency'] : give_get_currency();

                $attributes = [];
                $attributes['donationFormId'] = (int)$data['form_id'];

                $resolvedDonorId = 0;
                if (!empty($data['donor_id'])) {
                    $resolvedDonorId = (int)$data['donor_id'];
                } else {
                    try {
                        $email = (string)$data['email'];
                        $firstNameCsv = (string)(isset($data['first_name']) ? $data['first_name'] : '');
                        $lastNameCsv = (string)(isset($data['last_name']) ? $data['last_name'] : '');
                        $donorModel = give(\Give\DonationForms\Actions\GetOrCreateDonor::class)(null, $email, $firstNameCsv, $lastNameCsv, null, null);
                        if (!empty($import_setting['create_user']) && (int)$import_setting['create_user'] === 1) {
                            try {
                                $donorModel = give(\Give\Donors\Actions\CreateUserFromDonor::class)($donorModel);
                            } catch (\Throwable $e) {
                            }
                        }
                        $resolvedDonorId = (int)$donorModel->id;
                    } catch (\Throwable $e) {
                        $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                        $this->update_report($report);
                        return false;
                    }
                }
                $attributes['donorId'] = $resolvedDonorId;

                $rawPeriod = strtolower(trim((string)$data['period']));
                $periodAliases = [
                    'daily' => 'day',
                    'days' => 'day',
                    'day' => 'day',
                    'weekly' => 'week',
                    'weeks' => 'week',
                    'week' => 'week',
                    'monthly' => 'month',
                    'months' => 'month',
                    'month' => 'month',
                    'quarterly' => 'quarter',
                    'quarters' => 'quarter',
                    'qtr' => 'quarter',
                    'qtrs' => 'quarter',
                    'quarter' => 'quarter',
                    'yearly' => 'year',
                    'annually' => 'year',
                    'annual' => 'year',
                    'yrs' => 'year',
                    'yr' => 'year',
                    'years' => 'year',
                    'year' => 'year',
                ];
                $normalizedPeriod = isset($periodAliases[$rawPeriod]) ? $periodAliases[$rawPeriod] : $rawPeriod;
                if (!\Give\Subscriptions\ValueObjects\SubscriptionPeriod::isValid($normalizedPeriod)) {
                    throw new \UnexpectedValueException(sprintf(
                        __('Invalid subscription period "%1$s". Valid options: %2$s. You can also use: daily, weekly, monthly, quarterly, yearly.', 'give'),
                        (string)$data['period'],
                        implode(', ', array_values(\Give\Subscriptions\ValueObjects\SubscriptionPeriod::toArray()))
                    ));
                }
                $attributes['period'] = new \Give\Subscriptions\ValueObjects\SubscriptionPeriod($normalizedPeriod);
                $attributes['frequency'] = (int)$data['frequency'];
                $attributes['installments'] = isset($data['installments']) ? (int)$data['installments'] : 0;
                $attributes['transactionId'] = isset($data['transaction_id']) ? (string)$data['transaction_id'] : '';

                if (!empty($data['mode'])) {
                    $mode = strtolower((string)$data['mode']);
                } else {
                    $mode = (isset($import_setting['mode']) && $import_setting['mode']) ? 'test' : (give_is_test_mode() ? 'test' : 'live');
                }
                $attributes['mode'] = new \Give\Subscriptions\ValueObjects\SubscriptionMode($mode);

                $amountDecimal = is_string($data['amount']) ? preg_replace('/[\$,]/', '', $data['amount']) : $data['amount'];
                $attributes['amount'] = \Give\Framework\Support\ValueObjects\Money::fromDecimal($amountDecimal, $currency);

                if (isset($data['fee_amount_recovered']) && $data['fee_amount_recovered'] !== '') {
                    $feeDecimal = is_string($data['fee_amount_recovered']) ? preg_replace('/[\$,]/', '', $data['fee_amount_recovered']) : $data['fee_amount_recovered'];
                    $attributes['feeAmountRecovered'] = \Give\Framework\Support\ValueObjects\Money::fromDecimal($feeDecimal, $currency);
                }

                $attributes['status'] = new \Give\Subscriptions\ValueObjects\SubscriptionStatus(strtolower(trim((string)$data['status'])));

                if (!empty($data['gateway_id'])) {
                    $attributes['gatewayId'] = (string)$data['gateway_id'];
                }
                if (!empty($data['gateway_subscription_id'])) {
                    $attributes['gatewaySubscriptionId'] = (string)$data['gateway_subscription_id'];
                }

                if (!empty($data['created_at'])) {
                    $attributes['createdAt'] = new \DateTime((string)$data['created_at']);
                }
                if (!empty($data['renews_at'])) {
                    $attributes['renewsAt'] = new \DateTime((string)$data['renews_at']);
                }

                if ($dry_run) {
                    $report['create_subscription'] = (!empty($report['create_subscription']) ? (absint($report['create_subscription']) + 1) : 1);
                    $this->update_report($report);
                    return true;
                }

                $subscription = \Give\Subscriptions\Models\Subscription::create($attributes);

                if ($subscription && $subscription->id) {
                    try {
                        $donorModel = null;
                        try {
                            $donorModel = \Give\Donors\Models\Donor::find($subscription->donorId);
                        } catch (\Throwable $e) {
                            $donorModel = null;
                        }

                        $donorEmail = ($donorModel && isset($donorModel->email)) ? (string)$donorModel->email : '';
                        $donorName = ($donorModel && isset($donorModel->name)) ? (string)$donorModel->name : '';
                        $firstName = '';
                        $lastName = '';
                        if ($donorName) {
                            $parts = preg_split('/\s+/', trim($donorName));
                            if ($parts) {
                                $firstName = (string)array_shift($parts);
                                $lastName = (string)trim(implode(' ', $parts));
                            }
                        }

                        $donationAttributes = [
                            'subscriptionId' => $subscription->id,
                            'gatewayId' => !empty($attributes['gatewayId']) ? $attributes['gatewayId'] : 'manual',
                            'amount' => $subscription->amount,
                            'status' => \Give\Donations\ValueObjects\DonationStatus::COMPLETE(),
                            'type' => \Give\Donations\ValueObjects\DonationType::SUBSCRIPTION(),
                            'donorId' => $subscription->donorId,
                            'formId' => $subscription->donationFormId,
                            'feeAmountRecovered' => $subscription->feeAmountRecovered,
                            'mode' => $subscription->mode->isLive() ? \Give\Donations\ValueObjects\DonationMode::LIVE() : \Give\Donations\ValueObjects\DonationMode::TEST(),
                            'firstName' => $firstName,
                            'lastName' => $lastName,
                            'email' => $donorEmail,
                        ];

                        if (!empty($data['first_name'])) {
                            $donationAttributes['firstName'] = (string)$data['first_name'];
                        }
                        if (!empty($data['last_name'])) {
                            $donationAttributes['lastName'] = (string)$data['last_name'];
                        }
                        if (!empty($data['email'])) {
                            $donationAttributes['email'] = (string)$data['email'];
                        }

                        if (!empty($attributes['transactionId'])) {
                            $donationAttributes['gatewayTransactionId'] = (string)$attributes['transactionId'];
                        }
                        if (!empty($subscription->createdAt)) {
                            $donationAttributes['createdAt'] = $subscription->createdAt;
                        }

                        $initialDonation = \Give\Donations\Models\Donation::create($donationAttributes);

                        if ($initialDonation && $initialDonation->id) {
                            give()->subscriptions->updateLegacyParentPaymentId($subscription->id, $initialDonation->id);
                            $this->update_legacy_after_initial_donation($initialDonation);
                        }
                    } catch (\Throwable $e) {
                        $report['failed_subscription_initial_donation'] = (!empty($report['failed_subscription_initial_donation']) ? (absint($report['failed_subscription_initial_donation']) + 1) : 1);
                        $report['errors'][] = sprintf(__('Row %1$d: Initial donation creation failed (%2$s)', 'give'), (int)(isset($import_setting['row_key']) ? $import_setting['row_key'] : 0), $e->getMessage());
                    }
                    $report['create_subscription'] = (!empty($report['create_subscription']) ? (absint($report['create_subscription']) + 1) : 1);
                    $this->update_report($report);
                    return (int)$subscription->id;
                }

                $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                $this->update_report($report);
                return false;
            } catch (\Throwable $e) {
                $report['failed_subscription'] = (!empty($report['failed_subscription']) ? (absint($report['failed_subscription']) + 1) : 1);
                $report['errors'][] = sprintf(__('Row %1$d: %2$s', 'give'), (int)(isset($import_setting['row_key']) ? $import_setting['row_key'] : 0), $e->getMessage());
                $this->update_report($report);
                return $e->getMessage();
            }
        }

        /**
         * Get current import report
         * @since 4.11.0
         */
        public function get_report()
        {
            return get_option('give_import_subscription_report', []);
        }

        /**
         * Update import report
         * @since 4.11.0
         */
        private function update_report($value = [])
        {
            update_option('give_import_subscription_report', $value, false);
        }

        /**
         * Reset import report
         * @since 4.11.0
         */
        public function reset_report()
        {
            update_option('give_import_subscription_report', [], false);
        }

        /**
         * Update legacy donor totals and fee meta for newly created initial donation
         * @since 4.11.0
         */
        private function update_legacy_after_initial_donation(Donation $donation)
        {
            try {
                $donor = $donation->donor;
                if ($donor && isset($donor->id)) {
                    give()->donors->updateLegacyColumns(
                        $donor->id,
                        [
                            'purchase_value' => $this->get_donor_total_intended_amount((int)$donor->id),
                            'purchase_count' => $donor->totalDonations(),
                        ]
                    );
                }
                if (null !== $donation->feeAmountRecovered) {
                    give()->payment_meta->update_meta(
                        $donation->id,
                        '_give_fee_donation_amount',
                        give_sanitize_amount_for_db(
                            $donation->intendedAmount()->formatToDecimal(),
                            ['currency' => $donation->amount->getCurrency()]
                        )
                    );
                }
            } catch (\Throwable $e) {
            }
        }

        /**
         * Calculate donor total intended amount across donations
         * @since 4.11.0
         */
        private function get_donor_total_intended_amount($donorId)
        {
            return (float)DB::table('posts', 'posts')
                ->join(function ($join) {
                    $join->leftJoin('give_donationmeta', 'donor_meta')
                        ->on('posts.ID', 'donor_meta.donation_id')
                        ->andOn('donor_meta.meta_key', DonationMetaKeys::DONOR_ID, true);
                })
                ->join(function ($join) {
                    $join->leftJoin('give_donationmeta', 'amount_meta')
                        ->on('posts.ID', 'amount_meta.donation_id')
                        ->andOn('amount_meta.meta_key', DonationMetaKeys::AMOUNT, true);
                })
                ->join(function ($join) {
                    $join->leftJoin('give_donationmeta', 'fee_meta')
                        ->on('posts.ID', 'fee_meta.donation_id')
                        ->andOn('fee_meta.meta_key', DonationMetaKeys::FEE_AMOUNT_RECOVERED, true);
                })
                ->where('posts.post_type', 'give_payment')
                ->where('donor_meta.meta_value', (int)$donorId)
                ->whereIn('posts.post_status', ['publish', 'give_subscription'])
                ->sum('IFNULL(amount_meta.meta_value, 0) - IFNULL(fee_meta.meta_value, 0)');
        }

        /**
         * Subscription mapping options for CSV column selection
         * @since 4.11.0
         */
        public function get_subscription_options()
        {
            return (array)apply_filters(
                'give_import_subscription_options',
                [
                    'form_id' => [__('Donation Form ID', 'give'), __('Form ID', 'give')],
                    'donor_id' => [__('Donor ID', 'give')],
                    'first_name' => [__('Donor First Name', 'give'), __('First Name', 'give')],
                    'last_name' => [__('Donor Last Name', 'give'), __('Last Name', 'give')],
                    'email' => [__('Donor Email', 'give'), __('Email', 'give')],
                    'period' => [__('Period', 'give'), __('Subscription Period', 'give')],
                    'frequency' => [__('Frequency', 'give')],
                    'installments' => [__('Installments', 'give')],
                    'amount' => [__('Amount', 'give'), __('Recurring Amount', 'give')],
                    'fee_amount_recovered' => [__('Recovered Fee Amount', 'give')],
                    'status' => [__('Status', 'give')],
                    'mode' => [__('Mode', 'give'), __('Payment Mode', 'give')],
                    'transaction_id' => [__('Transaction ID', 'give')],
                    'gateway_id' => [__('Gateway ID', 'give'), __('Gateway', 'give')],
                    'gateway_subscription_id' => [__('Gateway Subscription ID', 'give')],
                    'created_at' => [__('Created At', 'give'), __('Start Date', 'give')],
                    'renews_at' => [__('Renews At', 'give'), __('Next Renewal Date', 'give')],
                    'currency' => [__('Currency', 'give')],
                ]
            );
        }
    }

    Give_Import_Subscriptions::get_instance()->setup();
}
