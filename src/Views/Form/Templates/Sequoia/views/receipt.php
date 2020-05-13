<?php

use Give\Receipt\Detail;
use Give\Receipt\DonationDetailsGroup\Details\TotalAmount as TotalAmountDetailItem;
use Give\Receipt\DetailGroup;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\Form\Templates\Sequoia\Sequoia;
use Give\Views\IframeContentView;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give_Payment as Payment;
use GivePdfReceipt\Receipt\PdfReceiptDetailsGroup\Details\Receipt as PdfReceiptDetailItem;

$donationSessionAccessor = new DonationAccessor();
$donation                = new Payment( $donationSessionAccessor->getDonationId() );
$options                 = FormTemplateUtils::getOptions();

/* @var Sequoia $sequoiaTemplate */
$sequoiaTemplate = Give()->templates->getTemplate();

$receiptDetails = $sequoiaTemplate->getReceiptDetails( $donation->ID );

/* @var PdfReceiptDetailItem $pdfReceiptDownloadLinkDetailItem */
$pdfReceiptDownloadLinkDetailItem = null;

ob_start();
?>
<div class="give-receipt-wrap give-embed-receipt">
	<div class="give-section receipt">
		<?php if ( ! empty( $options['thank-you']['image'] ) ) : ?>
			<div class="image">
				<img src="<?php echo $options['thank-you']['image']; ?>"/>
			</div>
		<?php else : ?>
			<div class="checkmark">
				<i class="fas fa-check"></i>
			</div>
		<?php endif; ?>
		<h2 class="headline">
			<?php echo $receiptDetails->heading; ?>
		</h2>
		<p class="message">
			<?php echo $receiptDetails->message; ?>
		</p>
		<?php require 'social-sharing.php'; ?>
		<?php
		/* @global DetailGroup $group */
		foreach ( $receiptDetails->getDetailGroupList() as $detailGroupClassName ) {
			$group = $receiptDetails->getDetailGroupObject( $detailGroupClassName );

			if ( ! $group->canShow() ) {
				continue;
			}

			echo '<div class="details">';
			if ( $group->heading ) {
				printf( '<h3 class="headline">%1$s</h3>', $group->heading );
			}

			if ( $detailList = $group->getDetailsList() ) {
				echo '<div class="details-table">';

				/* @var Detail $detail */
				foreach ( $detailList as $detailItemClassName ) {
					$detail = $group->getDetailItemObject( $detailItemClassName );
					$value  = $detail->getValue();

					if ( ! $value ) {
						continue;
					}

					// We will render download pdf receipt separately.
					if ( PdfReceiptDetailItem::class === $detailItemClassName ) {
						$pdfReceiptDownloadLinkDetailItem = $detail;
						continue;
					}

					// This class is required to highlight total donation amount in receipt.
					$detailRowClass = $detailItemClassName === TotalAmountDetailItem::class ? ' total' : '';

					printf(
						'<div class="details-row%1$s">',
						$detailRowClass
					);

					echo $detail->getIcon();

					printf(
						'<div class="detail">%1$s</div><div class="value">%2$s</div>',
						$detail->getLabel(),
						$value
					);

					echo '</div>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
		?>

		<?php if ( $pdfReceiptDownloadLinkDetailItem instanceof PdfReceiptDetailItem ) : ?>
			<div class="give-btn download-btn">
				<?php echo $pdfReceiptDownloadLinkDetailItem->getValue(); ?>
			</div>
		<?php endif; ?>

	</div>
	<div class="form-footer">
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
?>
