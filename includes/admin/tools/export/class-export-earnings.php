<?php
/**
 * Earnings Export Class
 *
 * This class handles earnings export
 *
 * @package     Give
 * @since       1.0
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @subpackage  Admin/Reports
 */

// Exit if accessed directly.

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Give_Earnings_Export Class
 *
 * @since 1.0
 */
class Give_Earnings_Export extends Give_Export
{

    /**
     * Our export type. Used for export-type specific filters/actions
     *
     * @since 1.0
     * @var string
     */
    public $export_type = 'earnings';

    /**
     * Set the export headers
     *
     * @access public
     * @since  1.6
     * @return void
     */
    public function headers()
    {
        give_ignore_user_abort();

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename=' . apply_filters(
                'give_earnings_export_filename',
                'give-export-' . $this->export_type . '-' . date('n') . '-' . date('Y')
            ) . '.csv'
        );
        header('Expires: 0');
    }

    /**
     * Set the CSV columns
     *
     * @access public
     * @since  1.0
     * @return array $cols All the columns
     */
    public function csv_cols()
    {
        $cols = [
            'date' => __('Date', 'give'),
            'donations' => __('Donations', 'give'),
            /* translators: %s: currency */
            'earnings' => sprintf(__('Revenue (%s)', 'give'), give_currency_symbol('', true)),
        ];

        return $cols;
    }

    /**
     * Include a nonce check when authenticating
     *
     * @since 2.21.4
     *
     * @return bool
     */
    public function can_export() {
        return (bool) apply_filters( 'give_export_capability', current_user_can( 'export_give_reports' ) && wp_verify_nonce($_REQUEST['give-nonce'], 'give_earnings_export'));
    }

    /**
     * Get the Export Data
     *
     * @access public
     * @since  1.0
     * @return array $data The data for the CSV file
     */
    public function get_data()
    {
        $dates = $this->getDatesFromRequest();

        $data = [];
        $year = $dates->startYear;
        $stats = new Give_Payment_Stats();

        while ($year <= $dates->endYear) {
            if ($year === $dates->startYear && $year === $dates->endYear) {
                $m1 = $dates->startMonth;
                $m2 = $dates->endMonth;
            } elseif ($year === $dates->startYear) {
                $m1 = $dates->startMonth;
                $m2 = 12;
            } elseif ($year === $dates->endYear) {
                $m1 = 1;
                $m2 = $dates->endMonth;
            } else {
                $m1 = 1;
                $m2 = 12;
            }

            while ($m1 <= $m2) {
                $date1 = mktime(0, 0, 0, $m1, 1, $year);
                $date2 = mktime(0, 0, 0, $m1, cal_days_in_month(CAL_GREGORIAN, $m1, $year), $year);

                $data[] = [
                    'date' => date_i18n('F Y', $date1),
                    'donations' => $stats->get_sales(0, $date1, $date2),
                    'earnings' => give_format_amount($stats->get_earnings(0, $date1, $date2), ['sanitize' => false]),
                ];

                $m1++;
            }

            $year++;
        }

        $data = apply_filters('give_export_get_data', $data);
        $data = apply_filters("give_export_get_data_{$this->export_type}", $data);

        return $data;
    }

    /**
     * @since 2.21.2
     *
     * @return object|null
     */
    private function getDatesFromRequest()
    {
        $dates = new stdClass();
        $firstDonation = give()->donations->getFirstDonation();
        $lastDonation = give()->donations->getLatestDonation();

        if ($firstDonation === null ) {
            return (object)[
                'startYear' => date('Y'),
                'endYear' => date('Y' ),
                'startMonth' => date('m'),
                'endMonth' => date('m')
            ];
        }

        if (!isset($_POST['start_year'], $_POST['end_year'], $_POST['start_month'], $_POST['end_month'])) {
            throw new \Give\Framework\Exceptions\Primitives\InvalidArgumentException(
                'Start year & month, End year & month can not be empty. Please enter validate dates to export revenue and donation stats.'
            );
        }

        $dates->startYear = (string)absint($_POST['start_year']);
        $dates->endYear = (string)absint($_POST['end_year']);
        $dates->startMonth = (string)absint($_POST['start_month']);
        $dates->endMonth = (string)absint($_POST['end_month']);

        if ($firstDonation && $firstDonation->createdAt->format('Y') > $dates->startYear) {
            throw new \Give\Framework\Exceptions\Primitives\InvalidArgumentException(
                'Start year cannot be less than first donation year'
            );
        }

        if ($lastDonation && $lastDonation->createdAt->format('Y') < $dates->endYear) {
            throw new \Give\Framework\Exceptions\Primitives\InvalidArgumentException(
                'End year cannot be greater than last donation year'
            );
        }

        return $dates;
    }
}
