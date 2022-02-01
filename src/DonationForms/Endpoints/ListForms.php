<?php

namespace Give\DonationForms\Endpoints;

use Give\Framework\QueryBuilder\QueryBuilder;
use Give_Donate_Form;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * @unreleased
 */


class ListForms extends Endpoint
{
    protected $endpoint = 'admin/forms';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'int',
                        'required' => false,
                        'validate_callback' => [$this, 'validateInt'],
                    ],
                    'perPage' => [
                       'type' => 'int',
                        'required' => false,
                       'validate_callback' => [$this, 'validateInt'],
                    ],
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateStatus']
                    ]
                ],
            ]
        );
    }

    public function validateInt($param, $request, $key)
    {
        return is_numeric($param) && $param > 0;
    }

    public function validateStatus($param, $request, $key)
    {
        return in_array($param, array(
           'publish',
           'future',
           'draft',
           'pending',
           'trash',
           'auto-draft',
           'inherit',
           'any'
        ));
    }

    public function handleRequest( WP_REST_Request $request )
    {
        $now = hrtime(true);
        $parameters = $request->get_params();
        $forms = $this->constructFormList( $parameters );
        $then = hrtime(true);
        error_log('took ' . ( ( $then - $now ) / 1000000) . 'ms');
        return new WP_REST_Response(
            $forms
        );
    }

    /**
     * Check user permissions
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to view Donation Forms', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }


    protected function constructFormList( $parameters ) {
        $per_page = $parameters['perPage'] ?: 30;
        $page = $parameters['page'] ?: 1;
        $status = isset($parameters['status']) ? $parameters['status'] : 'any';
        // basic query to get list of forms and form meta
        global $wpdb;
        $builder = new QueryBuilder();
        $builder->from('posts', 'posts')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_title', 'title'],
            )
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', ['publish', 'draft', 'pending'])
            ->attachMeta('give_formmeta', 'id', 'form_id',
                ['_give_form_earnings', 'revenue'],
                ['_give_goal_option', 'goal_enabled'],
                ['_give_levels_minimum_amount', 'level_min'],
                ['_give_levels_maximum_amount', 'level_max'],
                ['_give_goal_format', 'goal_format'],
                ['_give_form_goal_progress', 'goal_progress']
            )
            ->limit($per_page)
            ->orderBy('ID', 'DESC')
            ->offset(($page-1)*$per_page);
        $builder_form_query = $builder->getAll();
        $found_ids = array_map(function($e) {
            return $e->id;
        }, $builder_form_query);

        // todo: add a query to get donation count from $found_ids (list of form IDs)

        $args = array(
                'output'    => 'forms',
                'post_type' => array( 'give_forms' ),
                'update_post_meta_cache' => 'false',
                'post_status' => $status,
                'posts_per_page' => $per_page,
        );
        $form_query = new \WP_Query( $args );
        //make sure we're not asking for a non-existent page
        if( $form_query->max_num_pages < $page )
        {
            $page = $form_query->max_num_pages;
        }
        $args['paged'] = $page;
        $form_query = new \WP_Query( $args );
        $results = array();
        foreach( $form_query->posts as $index=>$form ) {
            $result = new Give_Donate_Form($form->ID);
            //if there are multiple prices, get the highest and lowest
            if( is_array( $result->prices ) ) {
                $all_prices = array_column($result->prices, '_give_amount');
                $prices = $this->formatAmount(min($all_prices)) . ' - ' . $this->formatAmount(max($all_prices));
            }
            $results[] = (object) array();
            $results[$index]->id = $form->ID;
            $results[$index]->name = $result->post_title;
            $results[$index]->amount = isset( $prices ) ? $prices : $this->formatAmount( $result->price );
            if( give_is_setting_enabled( give_get_meta( $form->ID, '_give_goal_option', true ) ) )
            {
                $goal = give_goal_progress_stats( $form->ID );
                $goal['actual'] = html_entity_decode($goal['actual']);
                $goal['goal'] = html_entity_decode($goal['goal']);
                if($goal['format'] == 'donation')
                {
                    $goal['format'] = ngettext(__('donation', 'give'), __('donations', 'give'), $goal['raw_goal']);
                }
                elseif ($goal['format'] == 'donors')
                {
                    $goal['format'] = ngettext(__('donor', 'give'), __('donors', 'give'), $goal['raw_goal']);
                }
                $results[$index]->goal = $goal;
            }
            else
            {
                $results[$index]->goal = '';
            }
            $results[$index]->donations = count( give_get_payments( ['give_forms' => $form->ID ] ) );
            $results[$index]->revenue = $this->formatAmount( give_get_form_earnings_stats( $form->ID ) );
            $date = date_create( $result->post_date );
            $timestamp = $date->getTimestamp();
            $date_string = '';
            if($timestamp >= strtotime('today'))
            {
                $date_string .= __('Today', 'give');
            }
            elseif ($timestamp >= strtotime('yesterday'))
            {
                $date_string .= __('Yesterday', 'give');
            }
            else {
                $date_string .= date_i18n('Y/m/d', $date );
            }
            $date_string .= date_i18n(' \a\t h:i a', $date);
            $results[$index]->datetime = $date_string;
            $results[$index]->shortcode = "[give_form id=\"$form->ID\"]";
            $results[$index]->status = $form->post_status;
            $results[$index]->permalink = html_entity_decode(get_permalink($form->ID));
            $results[$index]->edit = html_entity_decode(get_edit_post_link($form->ID));
        }
        return (object) array(
            'forms' => $results,
            'total' => $form_query->found_posts,
            'page' => (int)$page,
            'trash' => ! defined('EMPTY_TRASH_DAYS') || constant('EMPTY_TRASH_DAYS'),
        );
    }

    protected function formatAmount ($amount) {
        return html_entity_decode( give_currency_filter( give_format_amount( $amount ) ) );
    }
}
