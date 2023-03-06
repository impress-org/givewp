<?php

use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Receipt\DonationReceipt;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\Form\Templates\Sequoia\Sequoia;
use Give\Views\IframeContentView;
use Give_Payment as Payment;

$donationSessionAccessor = new DonationAccessor();
$donation = new Payment($donationSessionAccessor->getDonationId());
$options = FormTemplateUtils::getOptions();

/* @var Sequoia $sequoiaTemplate */
$sequoiaTemplate = Give()->templates->getTemplate();
$receipt = $sequoiaTemplate->getReceiptDetails($donation->ID);

/* @var LineItem|null $pdfReceiptDownloadLinkDetailItem */
$pdfReceiptLinkDetailItem = null;

ob_start();
?>
    <div class="give-receipt-wrap give-embed-receipt">
        <div class="give-section receipt">
            <?php
            // Donation failed
            if ($donation->post_status === 'failed'): ?>
                <div class="error_icon">
                    <i class="fas fa-times"></i>
                </div>
                <h2 class="headline">
                    <?= __('Donation Failed', 'give') ?>
                </h2>
                <p class="message">
                    <?= esc_html__('We\'re sorry, your donation failed to process. Please try again or contact site support.', 'give') ?>
                </p>
            <?php
            // Donation completed
            else: ?>
                <?php if (!empty($options['thank-you']['image'])) : ?>
                    <div class="image">
                        <img src="<?php echo $options['thank-you']['image']; ?>"/>
                    </div>
                <?php else : ?>
                    <div class="checkmark">
                        <i class="fas fa-check"></i>
                    </div>
                <?php endif; ?>

                <h2 class="headline">
                    <?php echo $receipt->heading; ?>
                </h2>
                <p class="message">
                    <?php echo $receipt->message; ?>
                </p>

                <?php require 'social-sharing.php'; ?>
            <?php endif; ?>

            <?php
            /* @var Section $section */
            foreach ($receipt as $section) {
                // Continue if section does not have line items.
                if (!$section->getLineItems()) {
                    continue;
                }

                if ('PDFReceipt' === $section->id) {
                    $pdfReceiptLinkDetailItem = $section['receiptLink'];
                    continue;
                }

                echo '<div class="details ' . sanitize_title($section->id) . '-section">';
                if ($section->label) {
                    printf('<h3 class="headline">%1$s</h3>', $section->label);
                }
                echo '<div class="details-table">';

                /* @var LineItem $lineItem */
                foreach ($section as $lineItem) {
                    // Continue if line item does not have value.
                    if (!$lineItem->value) {
                        continue;
                    }

                    $detailRowClass = sanitize_title($lineItem->id) . '-row';
                    // This class is required to highlight total donation amount in receipt.
                    if (DonationReceipt::DONATIONSECTIONID === $section->id) {
                        $detailRowClass .= ('totalAmount' === $lineItem->id ? ' total' : '');
                    }

                    printf(
                        '<div class="details-row %1$s">%2$s<div class="detail">%3$s</div><div class="value">%4$s</div></div>',
                        $detailRowClass,
                        $lineItem->icon,
                        $lineItem->label,
                        $lineItem->value
                    );
                }
                echo '</div>';
                echo '</div>';
            }
            ?>

            <?php if ($pdfReceiptLinkDetailItem) : ?>
                <div class="give-btn download-btn">
                    <?php
                    echo $pdfReceiptLinkDetailItem->value; ?>
                </div>
            <?php endif; ?>

        </div>
        <div class="form-footer">
            <div class="secure-notice">
                <i class="fas fa-lock"></i>
                <?php
                _e('Secure Donation', 'give'); ?>
            </div>
        </div>
    </div>

<?php

$pageId     = give_get_option('success_page');
$iframeView = new IframeContentView();

echo $iframeView->setTitle(esc_html__('Donation Receipt', 'give'))->setPostId($pageId)
                ->setBody(ob_get_clean())
                ->renderBody();
