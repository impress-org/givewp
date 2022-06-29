<?php
/**
 * This template is used to display the donation grid with [give_donor_wall]
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/** @var $args array */
/** @var $donation array kind of like Give_Payment */
/** @var $donor Give_Donor */
/** @var $atts array  Shortcode attributes */
/** @var $give_settings array  Give settings */

list($donation, $give_settings, $atts, $donor) = $args;

$primary_color = $atts['color'];
$avatarSize = (int)$atts['avatar_size'];
$tribute_background_color = !empty($atts['color']) ? $atts['color'] . '20' : '#219653';

?>


<div class="give-grid__item">
    <div class="give-donor give-card">
        <div class="give-donor-container">
            <?php
            if ($atts['show_avatar']) {
                if (!empty($donation['_give_anonymous_donation'])) {
                    // Donor gave anonymously
                    $imageUrl = GIVE_PLUGIN_URL . 'assets/dist/images/anonymous-user.svg';
                    $alt = __('Anonymous User', 'give');

                    echo "
                            <div class='give-donor-container__image' >
                                <img class='give-donor-container__image__anonymous' src='$imageUrl' alt='$alt' style='height: {$avatarSize}px;'/>
                            </div>
                        ";
                } elseif ($donation['_give_payment_donor_email'] && give_validate_gravatar(
                        $donation['_give_payment_donor_email']
                    )) {
                    // Donor has a valid Gravatar
                    $hash = md5(strtolower(trim($donation['_give_payment_donor_email'])));


                    echo "
                            <div class='give-donor-container__image' >
                                <img src='https://gravatar.com/avatar/$hash' alt='$donor->name' style='height: $avatarSize px;'/>
                            </div>
                        ";
                } else {
                    // Everyone else

                    $initial = esc_html($donation['name_initial']);
                    echo "
                           <div class='give-donor-container__image' style='height: {$avatarSize}px; width: {$avatarSize}px;'>
                             <span class='give-donor-container__image__name_initial'>$initial</span>
                           </div>
                        ";
                }
            }
                ?>
                <div class="give-donor-container-variation"
                     style="
                            flex-direction: <?php echo $atts['show_avatar'] ? 'column' : 'row'; ?>;
                            align-items:  <?php echo $atts['show_avatar'] ? 'center' : 'flex-end'; ?>;
                         ">
                    <?php if ( $atts['show_name'] ) : ?>
                        <h3 class="give-donor-container-variation__name">
                            <?php
                            // Get donor name based on donation parameter.
                            $donor_name = ! empty( $donation['_give_anonymous_donation'] )
                                ? esc_html__( 'Anonymous', 'give' )
                                : trim( $donation['_give_donor_billing_first_name'] . ' ' . $donation['_give_donor_billing_last_name'] );
                            ?>
                            <?php echo esc_html( $donor_name ); ?>
                        </h3>
                    <?php endif; ?>

                    <?php if ( $atts['show_company_name'] && isset( $donation['_give_donation_company'] ) ) : ?>
                        <h3 class="give-donor-container-variation__name">
                            <?php echo esc_html( $donation['_give_donation_company'] ); ?>
                        </h3>
                    <?php endif; ?>

                    <?php if ( $atts['show_time'] ) : ?>
                        <p class="give-donor-container-variation__timestamp">
                            <?php echo esc_html( give_get_formatted_date( $donation['donation_date'], give_date_format(), 'Y-m-d H:i:s', true ) ); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php
            if (
                $atts['show_comments']
                && absint($atts['comment_length'])
                && !empty($donation['donor_comment'])
            ) :
                ?>
                <div class="give-donor-wrapper">
                    <div class="give-donor-content"
                         style="border-color: <?php echo !empty($atts['color']) ? $atts['color'] : '#219653' ?>">
                        <?php
                        $comment = esc_html($donation['donor_comment']);
                        $stripped_comment = str_replace(' ', '', $comment);

                        $total_chars = strlen($stripped_comment);
                        $max_chars = $atts['comment_length'];
                        $read_more_text = $atts['readmore_text'];

                        // A truncated excerpt is displayed if the comment is too long.
                        if ($max_chars < $total_chars) {
                            $excerpt = '';
                            $offset = -($total_chars - $max_chars);
                            $last_space = strrpos($comment, ' ', $offset);

                            if ($last_space) {
                                // Truncate excerpt at last space before limit.
                                $excerpt = substr($comment, 0, $last_space);
                            } else {
                                // There are no spaces, so truncate excerpt at limit.
                                $excerpt = substr($comment, 0, $max_chars);
                            }

                            $excerpt = trim($excerpt, '.!,:;');

                            echo "<p class='give-donor-content__excerpt'>$excerpt &hellip;
                                    <span> <a class='give-donor-content__read-more' style='color: $primary_color'> $read_more_text </a></span>
                                   </p>";

                            echo "<p class='give-donor-content__comment'> $comment </p>";

                        }
                        else     {
                            echo "<p class='give-donor-content__comment'>
                                    $comment
                            </p>";
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="give-donor-details">
                <div class="give-donor-details__wrapper">
                    <?php
                    $full_form_name = esc_html($donation['_give_payment_form_title']);
                    $word_count = 3;
                    preg_match("/(\S+\s*){0,$word_count}/", esc_html($donation['_give_payment_form_title']), $regs);
                    $truncated_form_name = trim($regs[0] . "...");

                    // Determine whether to truncate form name based on amount of words
                    if ($atts['show_form'] && $atts['show_total'] && isset($donation['_give_payment_form_title'])) {
                        if (str_word_count($donation['_give_payment_form_title'], 0) <= $word_count) {
                            echo "<span class='give-donor-details__form_title'> $full_form_name </span>";
                        } else {
                            echo "<span class='give-donor-details__form_title'> $truncated_form_name </span>";
                        }
                    } // Display full form name if ['show_total'] is false
                    else {
                        if ($atts['show_form'] && !$atts['show_total'] && isset($donation['_give_payment_form_title'])) {
                            echo "<span class='give-donor-details__form_title' style='text-align: center'> $full_form_name </span>";
                        }
                    }

                    if ($atts['show_total']) {
                        echo sprintf(
                            '<span class=\'give - donor - details__amount_donated\'>%1$s</span>',
                                    esc_html__('Amount Donated', 'give')
                                );
                            }
                    ?>
                </div>

                <?php
                $donation_amount = give_donation_amount(esc_html($donation['donation_id']), true);

                if ($atts['show_total']) {
                    echo "
                             <span class= 'give-donor-details__total' style='color: $primary_color'> $donation_amount </span>
                        ";
                }
                ?>
            </div>
        </div>
        <?php
        if ($atts['show_tributes'] && (isset($donation['_give_tributes_first_name']) || isset($donation['_give_tributes_Last_name']))) {
            $tribute_message = esc_html($donation['_give_tributes_type']);
            $honoree_first_name = esc_html($donation['_give_tributes_first_name']);
            $honoree_last_name = esc_html($donation['_give_tributes_last_name']);

            $honoree_full_name =
                //Determine if a last name is available
                $donation['_give_tributes_last_name'] ?
                    //Remove full last name, and add as an initial.
                    trim($honoree_first_name . " " . strtoupper($honoree_last_name [0]) . ".") :
                    // Else add period at the end of first name
                    trim($honoree_first_name) . ".";

            echo
            "<div class='give-donor-tribute' style='background-color: {$tribute_background_color} '>
                    <span>
                        <svg width='16' height='16' viewBox='0 0 16 16'  xmlns='http://www.w3.org/2000/svg' class='give-donor-tribute__svg'>
                             <path fill='$primary_color' opacity='0.4' d='M11.9667 5.13998L11.8734 5.08665L10.9467 4.55332L9.0334 3.44665C8.44673 3.10665 7.5534 3.10665 6.96673 3.44665L5.0534 4.55332L4.12673 5.09332L4.00673 5.15998C2.8134 5.95998 2.7334 6.10665 2.7334 7.39332V10.4667C2.7334 11.7533 2.8134 11.9 4.0334 12.72L6.96673 14.4133C7.26007 14.5867 7.62673 14.6667 8.00007 14.6667C8.36673 14.6667 8.74007 14.5867 9.0334 14.4133L11.9934 12.7C13.1867 11.9 13.2667 11.7533 13.2667 10.4667V7.39332C13.2667 6.10665 13.1867 5.95998 11.9667 5.13998Z' fill='#15AE56'/>
                             <path fill='$primary_color' d='M4.12671 5.09337L5.05338 4.55337L6.88004 3.50004L6.96671 3.44671C7.55337 3.10671 8.44671 3.10671 9.03338 3.44671L9.12004 3.50004L10.9467 4.55337L11.8734 5.08671V3.66004C11.8734 2.16004 11.0467 1.33337 9.54671 1.33337H6.44671C4.94671 1.33337 4.12671 2.16004 4.12671 3.66004V5.09337Z' fill='#15AE56'/>
                             <path fill='$primary_color' d='M9.89333 8.89327L9.47999 9.39994C9.41333 9.47327 9.36666 9.61994 9.37333 9.71994L9.41333 10.3733C9.43999 10.7733 9.15333 10.9799 8.77999 10.8333L8.17333 10.5933C8.07999 10.5599 7.91999 10.5599 7.82666 10.5933L7.21999 10.8333C6.84666 10.9799 6.55999 10.7733 6.58666 10.3733L6.62666 9.71994C6.63333 9.61994 6.58666 9.47327 6.51999 9.39994L6.10666 8.89327C5.84666 8.5866 5.95999 8.2466 6.34666 8.1466L6.97999 7.9866C7.07999 7.95994 7.19999 7.8666 7.25333 7.77994L7.60666 7.23327C7.82666 6.89327 8.17333 6.89327 8.39333 7.23327L8.74666 7.77994C8.79999 7.8666 8.91999 7.95994 9.01999 7.9866L9.65333 8.1466C10.04 8.2466 10.1533 8.5866 9.89333 8.89327Z' fill='#15AE56'/>
                        </svg>
                    </span>

                    <span class='give-donor-tribute__message'>
                        <span> $tribute_message </span>
                        <span> $honoree_full_name </span>
                    </span>
                </div>";
        }
        ?>
    </div>
</div>
