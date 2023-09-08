<?php

namespace Give\FormMigration\Controllers;

use Give\DonationForms\V2\Models\DonationForm;
use Give\FormMigration\Concerns\Blocks\BlockDifference;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Pipeline;
use Give\Framework\Blocks\BlockModel;
use Give\Log\Log;
use WP_REST_Request;
use WP_REST_Response;

class MigrationController
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

    public function __invoke(DonationForm $formV2)
    {
        $payload = FormMigrationPayload::fromFormV2($formV2);

        give(Pipeline::class)
            ->afterEach(function($stepClass, $payload, $_payload) {
                (new BlockDifference($_payload->formV3->blocks))
                    ->skip('givewp/section')
                    ->onBlockAdded(function(BlockModel $block) {
                        $this->debugContext[] = [
                            'ADDED' => $block->name,
                            'ATTRIBUTES' => $block->getAttributes(),
                        ];
                    })
                    ->onBlockDifference(function(BlockModel $block, $differences) {
                        $this->debugContext[] = [
                            'UPDATED' => $block->name,
                            'ATTRIBUTES' => $differences,
                        ];
                    })
                    ->diff($payload->formV3->blocks);
            })
            ->process($payload)
            ->finally(function(FormMigrationPayload $payload) {
                $payload->formV3->save();
                Log::info(esc_html__('Form migrated from v2 to v3.', 'give'), $this->debugContext);
            });

        return new WP_REST_Response([
            'v2FormId' => $payload->formV2->id,
            'v3FormId' => $payload->formV3->id,
            'redirect' => add_query_arg([
                'post_type' => 'give_forms',
                'page' => 'givewp-form-builder',
                'donationFormID' => $payload->formV3->id,
            ], admin_url('edit.php')),
        ]);
    }
}
