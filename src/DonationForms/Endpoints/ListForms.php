<?php

namespace Give\DonationForms\Endpoints;

use Give_Donate_Form;
use Give_Forms_Query;
use WP_REST_Request;
use WP_REST_Response;

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
                    ]
                ],
            ]
        );
    }

    public function validateInt($param, $request, $key)
    {
        return is_numeric($param) && $param > 0;
    }

    public function handleRequest( WP_REST_Request $request )
    {
        $parameters = $request->get_params();
        $forms = $this->constructFormList( $parameters );

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
        $args = array(
                'output'    => 'forms',
                'post_type' => array( 'give_forms' ),
                'update_post_meta_cache' => 'false',
                'post_status' => 'any',
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
            $results[$index]->datetime = date_i18n('Y/m/d \a\t h:i a', date_create( $result->post_date ));
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
