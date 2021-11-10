<?php

use Give\Helpers\Form\Template;
use Give\Receipt\DonationReceipt;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Views\IframeContentView;

$template = Give()->templates->getTemplate();
$receipt  = $template->getReceiptDetails( ( new DonationAccessor() )->getDonationId() );
$option   = function ( $name ) use ( $template ) {
	static $options = [];

	if ( empty( $options ) ) {
		$options = Template::getOptions()[ 'donation_receipt' ];
	}

	if ( isset( $options[ $name ] ) ) {
		return $options[ $name ];
	}

	return '';
};

$donorDashboardUrl = get_permalink( give_get_option( 'donor_dashboard_page' ) );

ob_start();
?>
	<div class="give-receipt-classic">
		<div class="give-form-header">
			<div class="give-form-header-top-wrap">
				<aside class="give-form-secure-badge">
					<svg class="give-form-secure-icon">
						<use href="#give-icon-checkmark"/>
					</svg>
					<?= esc_html__( 'Success', 'give' ); ?>!
				</aside>
				<h1 class="give-receipt-title">
					<?= $receipt->heading; ?>
				</h1>
				<p class="give-form-description">
					<?= $receipt->message; ?>
				</p>
			</div>
		</div>

		<?php if ( 'enabled' === $option( 'social_sharing' ) ) : ?>
			<div class="social-sharing">
				<p class="instruction">
					<?= esc_html__( $option( 'sharing_instructions' ) ); ?>
				</p>
				<div class="btn-row">
					<button class="give-btn social-btn facebook-btn" onclick="GiveClassicTemplate.share(this);">
						<?= esc_html__( 'Share on Facebook', 'give' ); ?>
						<i class="fab fa-facebook"></i>
					</button>
					<button class="give-btn social-btn twitter-btn" onclick="GiveClassicTemplate.share(this);">
						<?= esc_html__( 'Share on Twitter', 'give' ); ?>
						<i class="fab fa-twitter"></i>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<div class="receipt-sections">

		<?php
			/* @var Section $section */
			foreach ( $receipt as $section ) :
				if ( ! $section->getLineItems() || 'PDFReceipt' === $section->id )
					continue;
			?>

			<div class="details">
				<?php if ( $section->label ): ?>
					<h2 class="headline">
						<?= $section->label; ?>
					</h2>
				<?php endif; ?>
				<dl class="details-table">

					<?php
						/* @var LineItem $lineItem */
						foreach ( $section as $lineItem ):
							if ( ! $lineItem->value )
								continue;

							$class = '';
							if ( DonationReceipt::DONATIONSECTIONID === $section->id ) {
								$class = 'totalAmount' === $lineItem->id ? 'total' : '';
							}
						?>

						<div class="details-row <?= $class; ?>">
							<?= $lineItem->icon; ?>
							<dt class="detail"><?= $lineItem->label; ?></dt>
							<dd class="value"><?= $lineItem->value; ?></dd>
						</div>
					<?php endforeach; ?>

				</dl>
			</div>

		<?php endforeach; ?>
		</div>

		<div class="dashboard-link-container">
			<a class="dashboard-link" href="<?= esc_url( $donorDashboardUrl ); ?>">
				<?= esc_html__( 'Go to my Donor Dashboard', 'give' ); ?>
			</a>
			<?php if ( isset( $section[ 'receiptLink' ] ) ) : ?>
				<div class="give-btn download-btn">
					<?= $section[ 'receiptLink' ]->value; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

<?php

echo ( new IframeContentView() )
	->setTitle( esc_html__( 'Donation Receipt', 'give' ) )
	->setBody( ob_get_clean() )
	->renderBody();
