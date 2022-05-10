<?php
/**
 * This template is used to display the donation grid with [give_donor_wall]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $donor Give_Donor */
$donation = $args[0];

$give_settings = $args[1]; // Give settings.
$atts          = $args[2]; // Shortcode attributes.
?>


<div class="give-grid__item">
    <div class="give-donor">
        <div class="give-card-container">
            <div class="give-donor-header">
                <?php
                if ( true === $atts['show_avatar'] ) {

                    // Get anonymous donor image.
                    $anonymous_donor_img = sprintf(
                        '<img src="%1$s" alt="%2$s">',
                        esc_url( GIVE_PLUGIN_URL . 'assets/dist/images/anonymous-user.svg' ),
                        esc_attr__( 'Anonymous User', 'give' )
                    );

                    $donor_avatar = sprintf(
                        '%2$s<p class="give-donor-header__name_initial">%1$s</p>',
                        $donation['name_initial'],
                        $anonymous_donor_img
                    );

                    // Validate donor gravatar.
                    $validate_gravatar = ! empty( $donation['_give_anonymous_donation'] ) ? 0 : give_validate_gravatar( $donation['_give_payment_donor_email'] );

                    // Maybe display the Avatar.
                    echo sprintf(
                        '<div class="give-donor-header__image" data-donor_email="%1$s" data-has-valid-gravatar="%2$s" data-avatar-size="%3$s" data-anonymous-donation="%5$s" style="max-width:%3$spx;">%4$s</div>',
                        md5( strtolower( trim( $donation['_give_payment_donor_email'] ) ) ),
                        absint( $validate_gravatar ),
                        $atts['avatar_size'],
                        $donor_avatar,
                        (int) ! empty( $donation['_give_anonymous_donation'] )
                    );
                }
                ?>
                <?php if ( true === $atts['show_name'] ) : ?>
                    <h3 class="give-donor-header__name" style='<?php echo ($atts['show_avatar']) ? "text-align: center" : "text-align: left"?>'>
                        <?php
                        // Get donor name based on donation parameter.
                        $donor_name = ! empty( $donation['_give_anonymous_donation'] )
                            ? esc_html__( 'Anonymous', 'give' )
                            : trim( $donation['_give_donor_billing_first_name'] . ' ' . $donation['_give_donor_billing_last_name'] );
                        ?>
                        <?php echo esc_html( $donor_name ); ?>
                    </h3>
                <?php endif; ?>
                <?php if ( true === $atts['show_company_name'] && isset( $donation['_give_donation_company'] ) ) : ?>
                    <h3 class="give-donor-header__name">
                        <?php echo esc_html( $donation['_give_donation_company'] ); ?>
                    </h3>
                <?php endif; ?>
                </div>

                <?php
                if (
                    true === $atts['show_comments']
                    && absint( $atts['comment_length'] )
                    && ! empty( $donation['donor_comment'] )
                    && ! $donation['_give_anonymous_donation']
                ) :
                    ?>
                    <div class="give-donor-content">
                        <?php
                        $comment     = trim( $donation['donor_comment'] );
                        $total_chars = strlen( $comment );
                        $max_chars   = $atts['comment_length'];

                        // A truncated excerpt is displayed if the comment is too long.
                        if ( $max_chars < $total_chars ) {
                            $excerpt    = '';
                            $offset     = -( $total_chars - $max_chars );
                            $last_space = strrpos( $comment, ' ', $offset );

                            if ( $last_space ) {
                                // Truncate excerpt at last space before limit.
                                $excerpt = substr( $comment, 0, $last_space );
                            } else {
                                // There are no spaces, so truncate excerpt at limit.
                                $excerpt = substr( $comment, 0, $max_chars );
                            }

                            $excerpt = trim( $excerpt, '.!,:;' );

                            echo sprintf(
                                '<p class="give-donor-content__excerpt">%s&hellip;<span> <a class="give-donor-content__read-more">%s</a></span></p>',
                                nl2br( esc_html( $excerpt ) ),
                                esc_html( $atts['readmore_text'] )
                            );
                        }

                        echo sprintf(
                            '<p class="give-donor-content__comment">%s</p>',
                            nl2br( esc_html( $comment ) )
                        );
                        ?>
                    </div>
                <?php endif; ?>
                <div class="give-donor-details">
    <!--            --><?php //if ( true === $atts['show_form'] && isset( $donation['_give_payment_form_title'] ) ) : ?>
    <!--                <div class="give-donor__form_title">-->
    <!--                    <span>-->
    <!--                    --><?php //echo esc_html( $donation['_give_payment_form_title'] ); ?>
    <!--                    </span>-->
    <!--                    <span class="give-donor__details_amount_donated">Amount Donated</span>-->
    <!--                </div>-->
    <!--            --><?php //endif; ?>

    <!-- PlaceHolder / form title is not displaying -->
                <div class="give-donor-details__wrapper">
                    <span class="give-donor-details__form_title">
                        Save The Planet
                    </span>
                    <span class="give-donor-details__amount_donated"> Amount Donated </span>
                </div>
<!--  End placeholder   -->
                <?php if ( true === $atts['show_total'] ) : ?>
                    <span class="give-donor-details__total">
                            <?php echo esc_html( give_donation_amount( $donation['donation_id'], true ) ); ?>
                    </span>
                <?php endif; ?>
                </div>
            </div>
            <div class="give-donor-tribute">
                    <span>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4" d="M11.9667 5.13998L11.8734 5.08665L10.9467 4.55332L9.0334 3.44665C8.44673 3.10665 7.5534 3.10665 6.96673 3.44665L5.0534 4.55332L4.12673 5.09332L4.00673 5.15998C2.8134 5.95998 2.7334 6.10665 2.7334 7.39332V10.4667C2.7334 11.7533 2.8134 11.9 4.0334 12.72L6.96673 14.4133C7.26007 14.5867 7.62673 14.6667 8.00007 14.6667C8.36673 14.6667 8.74007 14.5867 9.0334 14.4133L11.9934 12.7C13.1867 11.9 13.2667 11.7533 13.2667 10.4667V7.39332C13.2667 6.10665 13.1867 5.95998 11.9667 5.13998Z" fill="#15AE56"/>
                            <path d="M4.12671 5.09337L5.05338 4.55337L6.88004 3.50004L6.96671 3.44671C7.55337 3.10671 8.44671 3.10671 9.03338 3.44671L9.12004 3.50004L10.9467 4.55337L11.8734 5.08671V3.66004C11.8734 2.16004 11.0467 1.33337 9.54671 1.33337H6.44671C4.94671 1.33337 4.12671 2.16004 4.12671 3.66004V5.09337Z" fill="#15AE56"/>
                            <path d="M9.89333 8.89327L9.47999 9.39994C9.41333 9.47327 9.36666 9.61994 9.37333 9.71994L9.41333 10.3733C9.43999 10.7733 9.15333 10.9799 8.77999 10.8333L8.17333 10.5933C8.07999 10.5599 7.91999 10.5599 7.82666 10.5933L7.21999 10.8333C6.84666 10.9799 6.55999 10.7733 6.58666 10.3733L6.62666 9.71994C6.63333 9.61994 6.58666 9.47327 6.51999 9.39994L6.10666 8.89327C5.84666 8.5866 5.95999 8.2466 6.34666 8.1466L6.97999 7.9866C7.07999 7.95994 7.19999 7.8666 7.25333 7.77994L7.60666 7.23327C7.82666 6.89327 8.17333 6.89327 8.39333 7.23327L8.74666 7.77994C8.79999 7.8666 8.91999 7.95994 9.01999 7.9866L9.65333 8.1466C10.04 8.2466 10.1533 8.5866 9.89333 8.89327Z" fill="#15AE56"/>
                        </svg>
                    </span>
<!--   Make Tribute Message Dynamic?        -->
                <p class="give-donor-tribute__message"> <span> This was given in honor of </span> <span> Devin Walker </span></p>
            </div>
        </div>
    </div>


