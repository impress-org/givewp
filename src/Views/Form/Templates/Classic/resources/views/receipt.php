<?php

use Give\Helpers\Form\Template;
use Give\Receipt\DonationReceipt;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\IframeContentView;
use Give_Payment as Payment;

$donationId = (new DonationAccessor())->getDonationId();
$template = Give()->templates->getTemplate();
$receipt = $template->getReceiptDetails($donationId);
$donation = new Payment($donationId);
$option = static function ($name) {
    static $options = [];

    if (empty($options)) {
        $options = Template::getOptions()['donation_receipt'];
    }

    if (isset($options[$name])) {
        return $options[$name];
    }

    return '';
};

$hasDonationFailed = static function () use ($donation) {
    return $donation->post_status === 'failed';
};

$donorDashboardUrl = get_permalink(give_get_option('donor_dashboard_page'));

$data = $hasDonationFailed()
    ? [
        'badgeIcon' => '#give-icon-cross',
        'badgeText' => esc_html__('Failed', 'give'),
        'title' => esc_html__('Donation Failed', 'give'),
        'description' => esc_html__('We\'re sorry, your donation failed to process. Please try again or contact site support.', 'give'),
    ]
    : [
        'badgeIcon' => '#give-icon-checkmark',
        'badgeText' => esc_html__('Success', 'give'),
        'title' => $receipt->heading,
        'description' => $receipt->message,
    ];

ob_start();
?>
<?php include __DIR__ . '/icon-defs.php'; ?>
    <article class="give-receipt-classic">
        <div class="give-form-header">
            <div class="give-form-header-top-wrap">
                <aside class="give-form-secure-badge">
                    <svg class="give-form-secure-icon">
                        <use href="<?= $data['badgeIcon'] ?>"/>
                    </svg>
                    <?= $data['badgeText'] ?>!
                </aside>
                <h1 class="give-receipt-title">
                    <?= $data['title'] ?>
                </h1>
                <p class="give-form-description">
                    <?= $data['description'] ?>
                </p>
            </div>
        </div>

        <?php if ('enabled' === $option('social_sharing') && ! $hasDonationFailed()) : ?>
            <div class="social-sharing">
                <p class="instruction">
                    <?= esc_html__($option('sharing_instructions'),'give' ); ?>
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
                if (!$section->getLineItems() || 'PDFReceipt' === $section->id) {
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
                            if (!$lineItem->value) {
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
                                <dd class="value"
                                    data-value="<?= esc_attr($lineItem->value) ?>"><?= $lineItem->value ?></dd>
                            </div>
                        <?php
                        endforeach; ?>

                    </dl>
                </div>

            <?php
            endforeach; ?>
        </div>

        <div class="dashboard-link-container">
            <a class="dashboard-link" href="<?= esc_url($donorDashboardUrl); ?>" target="_parent">
                <?= esc_html__('Go to my Donor Dashboard', 'give'); ?><i class="fas fa-long-arrow-alt-right"></i>
            </a>
            <?php
            if (isset($section['receiptLink'])) : ?>
                <div class="give-btn download-btn">
                    <?= $section['receiptLink']->value; ?>
                </div>
            <?php
            endif; ?>
        </div>
    </article>

<?php

$pageId = give_get_option('success_page');
echo (new IframeContentView())
    ->setTitle(esc_html__('Donation Receipt', 'give'))->setPostId($pageId)
    ->setBody(ob_get_clean())
    ->renderBody();
