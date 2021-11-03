<?php

use Give\Helpers\Form\Template;
use Give\Receipt\LineItem;
use Give\Receipt\Section;
use Give\Views\Form\Templates\Classic\DonationReceipt;
use Give\Views\IframeContentView;

$receipt = new DonationReceipt();
$option  = function ( $name ) {
	static $options = [];

	if ( empty( $options ) ) {
		$options = Template::getOptions()[ 'donation_receipt' ];
	}

	if ( isset( $options[ $name ] ) ) {
		return $options[ $name ];
	}

	return null;
};

ob_start();
?>
	<div class="give-receipt">
		<div class="give-form-header">
			<div class="give-form-header-top-wrap">
				<aside class="give-form-secure-badge">
					<svg class="give-form-secure-icon">
						<use href="#give-icon-checkmark"/>
					</svg>
					<?= esc_html__( 'Success', 'give' ); ?>!
				</aside>
				<h1 class="give-receipt-title">
					<?= $receipt->replaceTags( $option( 'headline' ) ); ?>
				</h1>
				<p class="give-form-description">
					<?= $receipt->replaceTags( $option( 'description' ) ); ?>
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

		<?php
			/* @var Section $section */
			foreach ( $receipt as $section ) :
				if ( ! $section->getLineItems() || 'PDFReceipt' === $section->id )
					continue;
			?>

			<div class="details">
				<?php if ( $section->label ): ?>
					<h3 class="headline">
						<?= $section->label; ?>
					</h3>
				<?php endif; ?>
				<div class="details-table">

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
							<div class="detail"><?= $lineItem->label; ?></div>
							<div class="value"><?= $lineItem->value; ?></div>
						</div>
					<?php endforeach; ?>

				</div>
			</div>

		<?php endforeach; ?>

		<?php if ( isset( $section[ 'receiptLink' ] ) ) : ?>
			<div class="give-btn download-btn">
				<?= $section[ 'receiptLink' ]->value; ?>
			</div>
		<?php endif; ?>
	</div>

<?php

echo ( new IframeContentView() )
	->setTitle( esc_html__( 'Donation Receipt', 'give' ) )
	->setBody( ob_get_clean() )
	->renderBody();
