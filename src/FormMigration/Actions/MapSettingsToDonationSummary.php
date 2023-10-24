<?php

namespace Give\FormMigration\Actions;

use Give\FormMigration\DataTransferObjects\DonationSummarySettings;
use Give\Framework\Blocks\BlockCollection;

class MapSettingsToDonationSummary
{
    /**
     * @var BlockCollection
     */
    protected $blocks;

    public function __construct(BlockCollection $blocks)
    {
        $this->blocks = $blocks;
    }

    public static function make(BlockCollection $blocks)
    {
        return new self($blocks);
    }

    public function __invoke(DonationSummarySettings $settings)
    {
        if($settings->isEnabled()){
            $block = $this->blocks->findByName('givewp/donation-summary');
            $block->setAttribute('label', $settings->getHeading());

            if($settings->isBeforePaymentFields()) {
                // Remove and re-insert is the same as "move".
                $this->blocks->remove('givewp/donation-summary');
                $this->blocks->insertBefore('givewp/payment-gateways', $block );
            }
        } else {
            $this->blocks->remove('givewp/donation-summary');
        }
    }
}
