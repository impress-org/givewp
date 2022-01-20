<?php

use Give\Helpers\Form\Template;
use Give\Receipt\DonationReceipt;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\IframeContentView;

$template = Give()->templates->getTemplate();
$receipt  = $template->getReceiptDetails(( new DonationAccessor() )->getDonationId());
$option   = function ($name) use ($template) {
    static $options = [];

    if (empty($options)) {
        $options = Template::getOptions()[ 'donation_receipt' ];
    }

    if (isset($options[ $name ])) {
        return $options[ $name ];
    }

    return '';
};

$donorDashboardUrl = get_permalink(give_get_option('donor_dashboard_page'));

ob_start();
?>
    <?php include __DIR__ . '/icon-defs.php'; ?>
    <article class="give-receipt-classic">
        <div class="give-form-header">
            <div class="give-form-header-top-wrap">
                <aside class="give-form-secure-badge">
                    <svg class="give-form-secure-icon">
                        <use href="#give-icon-checkmark"/>
                    </svg>
                    <?= esc_html__('Success', 'give'); ?>!
                </aside>
                <h1 class="give-receipt-title">
                    <?= $receipt->heading; ?>
                </h1>
                <p class="give-form-description">
                    <?= $receipt->message; ?>
                </p>
            </div>
        </div>

        <?php if ('enabled' === $option('social_sharing')) : ?>
            <div class="social-sharing">
                <p class="instruction">
                    <?= esc_html__($option('sharing_instructions')); ?>
                </p>
                <div class="btn-row">
                    <button class="give-btn social-btn facebook-btn" onclick="GiveClassicTemplate.share(this);">
                        <?= esc_html__('Share on Facebook', 'give'); ?>
                        <i class="fab fa-facebook"></i>
                    </button>
                    <button class="give-btn social-btn twitter-btn" onclick="GiveClassicTemplate.share(this);">
                        <?= esc_html__('Share on Twitter', 'give'); ?>
                        <i class="fab fa-twitter"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="receipt-sections">

        <?php
        foreach ($receipt as $section) :
            /* @var Section $section */
            if (! $section->getLineItems() || 'PDFReceipt' === $section->id) {
                continue;
            }
            ?>

            <div class="details">
            <?php if ($section->label) : ?>
                    <h2 class="headline">
                        <?= $section->label; ?>
                    </h2>
            <?php endif; ?>
                <dl class="details-table">

                <?php
                foreach ($section as $lineItem) :
                    /* @var LineItem $lineItem */
                    if (! $lineItem->value) {
                        continue;
                    }

                    $class = '';
                    if (DonationReceipt::DONATIONSECTIONID === $section->id) {
                        $class = 'totalAmount' === $lineItem->id ? 'total' : '';
                    }
                    ?>

                        <div class="details-row details-row--<?= $lineItem->id ?>">
                        <?= $lineItem->icon ?>
                            <dt class="detail"><?= $lineItem->label ?></dt>
                            <dd class="value" data-value="<?= esc_attr($lineItem->value) ?>"><?= $lineItem->value ?></dd>
                        </div>
                <?php endforeach; ?>

                </dl>
            </div>

        <?php endforeach; ?>
        </div>

        <div class="dashboard-link-container">
            <a class="dashboard-link" href="<?= esc_url($donorDashboardUrl); ?>" target="_parent">
                <?= esc_html__('Go to my Donor Dashboard', 'give'); ?><i class="fas fa-long-arrow-alt-right"></i>
            </a>
            <?php if (isset($section[ 'receiptLink' ])) : ?>
                <div class="give-btn download-btn">
                    <?= $section[ 'receiptLink' ]->value; ?>
                </div>
            <?php endif; ?>
        </div>
    </article>

<?php

echo ( new IframeContentView() )
    ->setTitle(esc_html__('Donation Receipt', 'give'))
    ->setBody(ob_get_clean())
    ->renderBody();
