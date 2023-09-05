<?php

namespace Give\FormMigration\Controllers;

use Give\DonationForms\V2\Models\DonationForm;
use Give\FormMigration\Actions\TransferDonations;
use Give\FormMigration\Actions\TransferFormUrl;
use Give\FormMigration\DataTransferObjects\TransferOptions;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

class TransferController
{
    protected $debugContext;

    /**
     * @var WP_REST_Request
     */
    protected $request;

    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    public function __invoke(DonationForm $formV2, TransferOptions $options)
    {
        DB::transaction(function() use ($formV2, $options) {
            global $wpdb;
            $v3FormId = DB::get_var(
                DB::prepare(
                    "
                    SELECT `form_id`
                    FROM `{$wpdb->prefix}give_formmeta`
                    JOIN `{$wpdb->posts}`
                        ON `{$wpdb->posts}`.`ID` = `{$wpdb->prefix}give_formmeta`.`form_id`
                    WHERE `post_status` != 'trash'
                      AND `meta_key` = 'migratedFormId'
                      AND `meta_value` = %d",
                    $formV2->id
                )
            );

            TransferDonations::from($formV2->id)->to($v3FormId);

            if($options->shouldChangeUrl()) {
                TransferFormUrl::from($formV2->id)->to($v3FormId);
            }

            if($options->shouldDelete()) {
                wp_trash_post($formV2->id);
            }

            if($options->shouldRedirect()) {
                give_update_meta($v3FormId, 'redirectedFormId', $formV2->id);
            }

            give_update_meta($v3FormId, 'transferredFormId', true);
        });

        return new WP_REST_Response(array('errors' => [], 'successes' => [
            $formV2->id
        ]));
    }
}
