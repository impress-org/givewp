<?php if (!defined('ABSPATH')) { exit; } ?>
<?php
/**
 * @since TBD Escape translated output.
 */
?>
<div class="form-footer">
    <div class="navigator-tracker">
        <button class="step-tracker current" data-step="0"></button>
        <button class="step-tracker" data-step="1"></button>
        <button class="step-tracker" data-step="2"></button>
    </div>
    <div class="secure-notice">
        <?php
        if (is_ssl()) : ?>
            <i class="fas fa-lock"></i>
            <?php
            esc_html_e('Secure Donation', 'give'); ?>
        <?php
        else : ?>
            <i class="fas fa-exclamation-triangle"></i>
            <?php
            esc_html_e('Insecure Donation', 'give'); ?>
        <?php
        endif; ?>
    </div>
</div>
