<?php

namespace Give\MultiFormGoals\ProgressBar;

use Give\ValueObjects\Money;

class Model
{

    // Settings
    protected $ids;
    protected $tags;
    protected $categories;
    protected $goal;
    protected $enddate;
    protected $color;

    // Internal
    protected $forms = [];
    protected $donationRevenueResults;

    /**
     * Constructs and sets up setting variables for a new Progress Bar model
     *
     * @since 2.9.0
     **@param array $args Arguments for new Progress Bar, including 'ids'
     */
    public function __construct(array $args)
    {
        isset($args['ids']) ? $this->ids = $args['ids'] : $this->ids = [];
        isset($args['tags']) ? $this->tags = $args['tags'] : $this->tags = [];
        isset($args['categories']) ? $this->categories = $args['categories'] : $this->categories = [];
        isset($args['goal']) ? $this->goal = $args['goal'] : $this->goal = '1000';
        isset($args['enddate']) ? $this->enddate = $args['enddate'] : $this->enddate = '';
        isset($args['color']) ? $this->color = $args['color'] : $this->color = '#28c77b';
    }

    /**
     * Get forms associated with Progress Bar
     *
     * @since 2.9.0
     **@return array
     */
    protected function getForms()
    {
        if ( ! empty($this->forms)) {
            return $this->forms;
        }

        $query_args = [
            'post_type' => 'give_forms',
            'post_status' => 'publish',
            'post__in' => $this->ids,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                'relation' => 'AND',
            ],
        ];

        if ( ! empty($this->tags)) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'give_forms_tag',
                'terms' => $this->tags,
            ];
        }

        if ( ! empty($this->categories)) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'give_forms_category',
                'terms' => $this->categories,
            ];
        }

        $query = new \WP_Query($query_args);

        if ($query->posts) {
            $this->forms = $query->posts;

            return $query->posts;
        } else {
            return false;
        }
    }

    protected function getDonations()
    {
        $query_args = [
            'post_status' => [
                'publish',
                'give_subscription',
            ],
            'number' => -1,
            'give_forms' => $this->getForms(),
        ];
        $query = new \Give_Payments_Query($query_args);

        return $query->get_payments();
    }

    /**
     * Get output markup for Progress Bar
     *
     * @since 2.9.0
     **@return string
     */
    public function getOutput()
    {
        ob_start();
        $output = '';
        require $this->getTemplatePath();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Returns query results for Donation Revenue.
     * @since 2.9.0
     * @return stdClass seem MultiFormGoals/ProgressBar/Query.php
     */
    protected function getDonationRevenueResults()
    {
        if ( ! $this->donationRevenueResults) {
            $query = new Query($this->getForms());
            $this->donationRevenueResults = $query->getResults();
        }

        return $this->donationRevenueResults;
    }

    /**
     * Get raw earnings value for Progress Bar
     *
     * @since 2.9.0
     **@return string
     */
    protected function getTotal()
    {
        $query = new Query($this->getForms());
        $results = $query->getResults();

        return Money::ofMinor($results->total, give_get_option('currency'))->getAmount();
    }

    /**
     * Get number of donations for Progress Bar
     *
     * @since 2.9.0
     **@return int
     */
    protected function getDonationCount()
    {
        $results = $this->getDonationRevenueResults();

        return $results->count;
    }

    /**
     * Get formatted total remaining (ex: $75)
     *
     * @since 2.9.0
     */
    protected function getFormattedTotalRemaining()
    {
        $total_remaining = ($this->getGoal() - $this->getTotal()) > 0 ? ($this->getGoal() - $this->getTotal()) : 0;

        return give_currency_filter(
            give_format_amount(
                $total_remaining,
                [
                    'sanitize' => false,
                    'decimal' => false,
                ]
            )
        );
    }

    /**
     * Get goal for Progress Bar
     *
     * @since 2.9.0
     **@return string
     */
    protected function getGoal()
    {
        return $this->goal;
    }

    /**
     * Get goal color for Progress Bar
     *
     * @since 2.9.0
     **@return string
     */
    protected function getColor()
    {
        return $this->color;
    }

    /**
     * Get template path for Progress Bar component template
     * @since 2.9.0
     **/
    public function getTemplatePath()
    {
        return GIVE_PLUGIN_DIR . '/src/MultiFormGoals/resources/views/progressbar.php';
    }

    protected function getFormattedTotal()
    {
        return give_currency_filter(
            give_format_amount(
                $this->getTotal(),
                [
                    'sanitize' => false,
                    'decimal' => false,
                ]
            )
        );
    }

    protected function getFormattedGoal()
    {
        return give_currency_filter(
            give_format_amount(
                $this->getGoal(),
                [
                    'sanitize' => false,
                    'decimal' => false,
                ]
            )
        );
    }

    /**
     * Get end date for Progress Bar
     *
     * @since 2.9.0
     **@return string
     */
    protected function getEndDate()
    {
        return $this->enddate;
    }

    /**
     * Get minutes remaining before Progress Bar end date
     *
     * @since 2.9.0
     **@return string
     */
    protected function getMinutesRemaining()
    {
        $enddate = strtotime($this->getEndDate());
        if ($enddate) {
            $now = current_time('timestamp', false);

            return $now < $enddate ? ($enddate - $now) / 60 : 0;
        } else {
            return false;
        }
    }

    /**
     * Get time remaining before Progress Bar end date
     *
     * @since 2.9.0
     **@return string
     */
    protected function getTimeToGo()
    {
        $minutes = $this->getMinutesRemaining();
        switch ($minutes) {
            case $minutes > 1440:
            {
                return round($minutes / 1440);
            }
            case $minutes < 1440 && $minutes > 60:
            {
                return round($minutes / 60);
            }
            case $minutes < 60:
            {
                return round($minutes);
            }
        }
    }

    /**
     * Get time remaining before Progress Bar end date
     *
     * @since 2.9.0
     **@return string
     */
    protected function getTimeToGoLabel()
    {
        $minutes = $this->getMinutesRemaining();
        switch ($minutes) {
            case $minutes > 1440:
            {
                return _n('day to go', 'days to go', $this->getTimeToGo(), 'give');
            }
            case $minutes < 1440 && $minutes > 60:
            {
                return _n('hour to go', 'hours to go', $this->getTimeToGo(), 'give');
            }
            case $minutes < 60:
            {
                return _n('minute to go', 'minutes to go', $this->getTimeToGo(), 'give');
            }
        }
    }
}
