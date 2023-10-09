<?php

namespace Give\FormMigration\Controllers;

use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormMigration\Actions\GetMigratedFormId;
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

            $v3FormId = (new GetMigratedFormId)($formV2->id);
            TransferFormUrl::from($formV2->id)->to($v3FormId);
            TransferDonations::from($formV2->id)->to($v3FormId);

            if($options->shouldDelete()) {
                wp_trash_post($formV2->id);
            }

            wp_update_post(['ID' => $v3FormId, 'post_status' => $formV2->status->getValue()]);
            give_update_meta($v3FormId, 'transferredFormId', true);
        });

        return new WP_REST_Response(array('errors' => [], 'successes' => [
            $formV2->id
        ]));
    }
}
