<?php

namespace Give\FormMigration\Commands;

use Give\DonationForms\Models\DonationForm as DonationFormV3;
use Give\DonationForms\V2\Models\DonationForm as DonationFormV2;
use Give\FormMigration\Concerns\Blocks\BlockDifference;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Pipeline;
use Give\Framework\Blocks\BlockModel;
use WP_CLI;

class MigrationCommand
{
    /**
     * Prints a greeting.
     *
     * ## OPTIONS
     *
     * <id>
     * : A form ID to migrate
     *
     * [--step]
     * : Whether to inspect each step
     */
    public function __invoke( $args, $assoc_args )
    {
        [$formIdV2] = $args;

        $payload = FormMigrationPayload::fromFormV2(
            DonationFormV2::find($formIdV2)
        );

        $pipeline = give(Pipeline::class);

        if(\WP_CLI\Utils\get_flag_value($assoc_args, 'step')) {
            $pipeline->beforeEach(function ($stepClass, $payload) {
                WP_CLI::log('Processing ' . $stepClass);
            });
            $pipeline->afterEach([$this, 'afterEach']);
        }

        $pipeline
            ->process($payload)
            ->finally(function(FormMigrationPayload $payload) {
                $payload->formV3->save();
                WP_CLI::success( 'Migration Complete ' . $payload->formV3->id );
            });
    }

    public function afterEach($stepClass, FormMigrationPayload $payload, $_payload)
    {
        WP_CLI::log('Processed ' . $stepClass);

        foreach($payload->formV3->settings->toArray() as $key => $value) {
            $previousValue = $_payload->formV3->settings->$key;
            if($previousValue != $value) { // The check is loosely typed to support Enums
                $value = is_array($value) ? '[Array]' : ( empty($value) ? '(empty)' : $value );
                $previousValue = is_array($previousValue) ? '[Array]' : ( empty($previousValue) ? '(empty)' : $previousValue );
                WP_CLI::log('');
                WP_CLI::log('Form Setting: ' . $key);
                WP_CLI::log('    ' . $previousValue . ' => ' . $value);
                WP_CLI::log('');
            }
        }

        (new BlockDifference($_payload->formV3->blocks))
            ->skip('givewp/section')
            ->onBlockAdded(function(BlockModel $block) {
                WP_CLI::log('');
                WP_CLI::log('Block Added: ' . $block->name);
                WP_CLI::log('    ' .  json_encode($block->getAttributes()));
                WP_CLI::log('');
            })
            ->onBlockDifference(function(BlockModel $block, $differences) {
                WP_CLI::log('');
                WP_CLI::log('Block Updated: ' . $block->name);
                foreach($differences as $key => $difference) {
                    WP_CLI::log('    ' . $key);
                    WP_CLI::log('    ' . '    ' . json_encode($difference['previous']) . ' => ' . json_encode($difference['current']));
                }
                WP_CLI::log('');
            })
            ->diff($payload->formV3->blocks);

        fwrite( STDOUT, 'Continue?' . ' [enter] ' );
        fgets( STDIN );
        $this->clearOutput();
    }

    protected function clearOutput()
    {
        system('clear || cls'); // Clear screen with cross-platform.
    }
}
