<?php

use Give\Receipt\DonationReceipt;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\Form\Templates\Classic\Classic;
use Give\Views\IframeContentView;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give_Payment as Payment;

$donationSessionAccessor = new DonationAccessor();
$donation                = new Payment( $donationSessionAccessor->getDonationId() );
$options                 = FormTemplateUtils::getOptions();

/* @var Classic $sequoiaTemplate */
$sequoiaTemplate = Give()->templates->getTemplate();
$receipt         = $sequoiaTemplate->getReceiptDetails( $donation->ID );

/* @var LineItem|null $pdfReceiptDownloadLinkDetailItem */
$pdfReceiptLinkDetailItem = null;

ob_start();
?>
<div class="give-receipt-wrap give-embed-receipt">
	<div class="give-section receipt">
		<h2 class="headline">
			<?php echo $receipt->heading; ?>
		</h2>
		<p class="message">
			<?php echo $receipt->message; ?>
		</p>

		<?php if ( isset( $options[ 'donation_receipt' ][ 'social_sharing' ] ) && $options[ 'donation_receipt' ][ 'social_sharing' ] === 'enabled' ) : ?>
			<div class="social-sharing">
				<p class="instruction">
					<?php echo esc_html( $options[ 'donation_receipt' ][ 'sharing_instructions' ] ); ?>
				</p>
				<div class="btn-row">
					<!-- Use inline onclick listener to avoid popup blockers -->
					<button class="give-btn social-btn facebook-btn" onclick="GiveClassicTemplate.share(this);">
						<?php esc_html_e( 'Share on Facebook', 'give' ); ?><i class="fab fa-facebook"></i>
					</button>
					<!-- Use inline onclick listener to avoid popup blockers -->
					<button class="give-btn social-btn twitter-btn" onclick="GiveClassicTemplate.share(this);">
						<?php esc_html_e( 'Share on Twitter', 'give' ); ?><i class="fab fa-twitter"></i>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<?php
		/* @var Section $section */
		foreach ( $receipt as $section ) {
			// Continue if section does not have line items.
			if ( ! $section->getLineItems() ) {
				continue;
			}

			if ( 'PDFReceipt' === $section->id ) {
				$pdfReceiptLinkDetailItem = $section[ 'receiptLink' ];
				continue;
			}

			echo '<div class="details">';
			if ( $section->label ) {
				printf( '<h3 class="headline">%1$s</h3>', $section->label );
			}
			echo '<div class="details-table">';

			/* @var LineItem $lineItem */
			foreach ( $section as $lineItem ) {
				// Continue if line item does not have value.
				if ( ! $lineItem->value ) {
					continue;
				}

				// This class is required to highlight total donation amount in receipt.
				$detailRowClass = '';
				if ( DonationReceipt::DONATIONSECTIONID === $section->id ) {
					$detailRowClass = 'totalAmount' === $lineItem->id ? ' total' : '';
				}

				printf(
					'<div class="details-row%1$s">%2$s<div class="detail">%3$s</div><div class="value">%4$s</div></div>',
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

		<?php if ( $pdfReceiptLinkDetailItem ) : ?>
			<div class="give-btn download-btn">
				<?php echo $pdfReceiptLinkDetailItem->value; ?>
			</div>
		<?php endif; ?>

	</div>
	<div class="form-footer">1
		<div class="secure-notice">
			<i class="fas fa-lock"></i>
			<?php _e( 'Secure Donation', 'give' ); ?>
		</div>
	</div>
</div>


<?php
$iframeView = new IframeContentView();

echo $iframeView->setTitle( esc_html__( 'Donation Receipt', 'give' ) )
				->setBody( ob_get_clean() )
				->renderBody();
