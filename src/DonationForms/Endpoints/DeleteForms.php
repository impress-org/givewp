<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class DeleteForms extends TrashForms
{
    protected $endpoint = 'admin/forms/delete';


    protected function updateForms($parameters)
    {
        $errors = 0;
        $successes = 0;
        $id_array = explode(',', $parameters['ids']);
        foreach($id_array as $id){
            if( !is_numeric($id) || !wp_delete_post( $id, true ) ) {
                $errors++;
            }
            else
            {
                $successes++;
            }
        }
        return array( 'errors' => $errors, 'successes' => $successes );
    }
}
