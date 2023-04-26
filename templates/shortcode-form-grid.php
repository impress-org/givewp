<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
use Give\Helpers\Form\Template;
use Give\Helpers\Form\Utils as FormUtils;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * List of changes
 *
 * @unreleased Use get_the_excerpt function to get short description of donation form to display in form grid.
 */

$form_id          = get_the_ID(); // Form ID.
$give_settings    = $args[0]; // Give settings.
$atts             = $args[1]; // Shortcode attributes.
$raw_content      = ''; // Raw form content.
$stripped_content = ''; // Form content stripped of HTML tags and shortcodes.
$excerpt          = ''; // Trimmed form excerpt ready for display.

$flex_direction = $atts['columns'] === '1' ? "row" : "column";

$activeTemplate = FormUtils::isLegacyForm( $form_id ) ? 'legacy' : Template::getActiveID( $form_id );

/* @var \Give\Form\Template $formTemplate */
$formTemplate = Give()->templates->getTemplate( $activeTemplate );

$renderTags = static function($wrapper_class, $apply_styles = true) use($form_id , $atts ) {
    if( !taxonomy_exists( 'give_forms_tag' ) ) {
        return '';
    }

    $tags = wp_get_post_terms($form_id,'give_forms_tag');

    $tag_bg_color = ! empty( $atts['tag_background_color'] )
        ? $atts['tag_background_color']
        : '#69b86b';

    $tag_text_color = ! empty( $atts['tag_text_color'] )
        ? $atts['tag_text_color']
        : '#ffffff';

    $tag_container_color = count($tags) >= 1
        ? 'rgba(0, 0, 0, 0.35)'
        : 'none';

    $tag_elements = array_map(
        static function($term)use($tag_text_color,$tag_bg_color){
            $style = sprintf(
                'color: %s; background-color: %s;',
                esc_attr($tag_text_color),
                esc_attr($tag_bg_color)
            );
            return "<span style='$style'>$term->name</span>";
        }, $tags
    );

    $tag_elements = implode('', $tag_elements);
    $styles = sprintf(
            "background-color: %s;",
            $apply_styles ? esc_attr($tag_container_color) : ''
        );

    return "
         <div class='$wrapper_class' style='$styles' >
            $tag_elements
         </div>
    ";
};

?>

<div class="give-grid__item">
    <?php
    // Print the opening anchor tag based on display style.
    if ( 'redirect' === $atts['display_style'] ) {

        $form_grid_option = give_get_meta( $form_id, '_give_form_grid_option', true );
        $form_grid_redirect_url = esc_url(give_get_meta( $form_id, '_give_form_grid_redirect_url', true ));

        $url = ( $form_grid_option === 'custom' && filter_var($form_grid_redirect_url, FILTER_VALIDATE_URL) )
            ? $form_grid_redirect_url
            : get_the_permalink();

        printf(
            '<a id="give-card-%1$s" onclick="return !document.body.classList.contains( \'block-editor-page\' )" class="give-card" href="%2$s">',
            esc_attr( $form_id ),
            esc_attr( $url )
        );
    } elseif ( 'modal_reveal' === $atts['display_style'] ) {
        printf(
            '<a id="give-card-%1$s" class="give-card js-give-grid-modal-launcher" data-effect="mfp-zoom-out" href="#give-modal-form-%1$s">',
            esc_attr( $form_id )
        );
    }
    ?>
    <div class="give-form-grid" style="flex-direction:<?php echo esc_attr($flex_direction) ?>">
        <?php
        // Maybe display the featured image.
        if (
            give_is_setting_enabled($give_settings['form_featured_img'])
            && ($imageSrc = $formTemplate->getFormFeaturedImage($form_id))
            && $atts['show_featured_image']
            && $atts['columns'] !== '1'
        ) {
            /*
             * Filters the image size used in card layouts.
             *
             * @param string The image size.
             * @param array  Form grid attributes.
             */
            $image_size = apply_filters('give_form_grid_image_size', $atts['image_size'], $atts);
            $image_attr = '';

            if ('auto' !== $atts['image_height']) {
                $image_attr = [
                    'style' => 'height: ' . $atts['image_height'],
                ];
            }

            $image = wp_get_attachment_image( attachment_url_to_postid( $imageSrc ), $image_size, false, $image_attr );


            echo "
                        <div class='give-form-grid-media'>
                            <div class='give-card__media'> $image </div>

                            {$renderTags('give-form-grid-media__tags')}
                        </div>
                    ";
        } elseif(
            give_is_setting_enabled($give_settings['form_featured_img'])
            && ($imageSrc = $formTemplate->getFormFeaturedImage($form_id))
            && $atts['show_featured_image']
            && $atts['columns'] === '1')
        {
            echo "
                            <div id='row-media' class='give-form-grid-media'>
                                <img class='give-form-grid-media' src='". esc_url($imageSrc). "' alt='' />

                                {$renderTags('give-form-grid-media__tags')}
                            </div>
                        ";
        }
        ?>

        <div class="give-form-grid-container">
            <div class="give-form-grid-content">
                <?php
                if( !$atts['show_featured_image']){
                    echo "
                                 <div class='give-form-grid-media'>
                                        {$renderTags('give-form-grid-media__tags_no_image', false)}
                                   </div>
                            ";
                }
                ?>

                <?php

                // Maybe display the form title.
                if ( true === $atts['show_title'] ) {
                    printf(
                        '<h3 class="give-form-grid-content__title">%1$s</h3>',
                        $formTemplate->getFormHeading( $form_id )
                    );
                }

                // Maybe display the form excerpt.
                if ( true === $atts['show_excerpt'] ) {
                    if ( $raw_content = get_the_excerpt( $form_id ) ) {
                        $stripped_content = wp_strip_all_tags(
                            strip_shortcodes( $raw_content )
                        );
                    } else {
                        // Get content from the form post's content field.
                        $raw_content = give_get_meta( $form_id, '_give_form_content', true );

                        if ( ! empty( $raw_content ) ) {
                            $stripped_content = wp_strip_all_tags(
                                strip_shortcodes( $raw_content )
                            );
                        }
                    }

                    // Maybe truncate excerpt.
                    if ( 0 < $atts['excerpt_length'] ) {
                        $excerpt = wp_trim_words( $stripped_content, $atts['excerpt_length'] );
                    } else {
                        $excerpt = $stripped_content;
                    }

                    printf( '<p class="give-form-grid-content__text">%s</p>', $excerpt );
                }

                if ($atts['show_donate_button']):
                    $button_text = ! empty($atts['donate_button_text'])
                        ? $atts['donate_button_text']
                        : give_get_meta($form_id, '_give_form_grid_donate_button_text', true);

                    /**
                     * @since 2.23.1 Updated the default text color for the donate button, see #6591.
                     */
                    $button_text_color = ! empty($atts['donate_button_text_color'])
                        ? $atts['donate_button_text_color']
                        : '#000000';
                    ?>
                    <button style="text-decoration-color: <?php
                    echo esc_attr($button_text_color); ?>">
                                    <span style="color: <?php
                                    echo esc_attr($button_text_color); ?>">
                                        <?php
                                        echo esc_html($button_text) ?: __('Donate', 'give'); ?>
                                    </span>
                    </button>
                <?php endif; ?>

            </div>
            <?php
            $form        = new Give_Donate_Form( $form_id );
            $goal_option = give_get_meta( $form->ID, '_give_goal_option', true );

            // Sanity check - ensure form has pass all condition to show goal.
            $hide_goal = ( isset( $atts['show_goal'] ) && ! filter_var( $atts['show_goal'], FILTER_VALIDATE_BOOLEAN ) )
                         || empty( $form->ID )
                         || ( is_singular( 'give_forms' ) && ! give_is_setting_enabled( $goal_option ) )
                         || ! give_is_setting_enabled( $goal_option ) || 0 === $form->goal;

            // Maybe display the goal progress bar.

            if (!$hide_goal) :
                $goal_progress_stats = give_goal_progress_stats( $form );
                $goal_format         = $goal_progress_stats['format'];
                $color               = $atts['progress_bar_color'];
                $show_goal           = isset( $atts['show_goal'] ) ? filter_var( $atts['show_goal'], FILTER_VALIDATE_BOOLEAN ) : true;
                $shortcode_stats = apply_filters(
                    'give_goal_shortcode_stats',
                    array(
                        'income' => $form->get_earnings(),
                        'goal'   => $goal_progress_stats['raw_goal'],
                    ),
                    $form_id,
                    $goal_progress_stats,
                    $args
                );

                $income = $shortcode_stats['income'];
                $goal   = $shortcode_stats['goal'];

                switch ( $goal_format ) {

                    case 'donation':
                        $progress           = $goal ? round( ( $form->get_sales() / $goal ) * 100, 2 ) : 0;
                        $progress_bar_value = $form->get_sales() >= $goal ? 100 : $progress;
                        break;

                    case 'donors':
                        $progress           = $goal ? round( ( give_get_form_donor_count( $form->ID ) / $goal ) * 100, 2 ) : 0;
                        $progress_bar_value = give_get_form_donor_count( $form->ID ) >= $goal ? 100 : $progress;
                        break;

                    case 'percentage':
                        $progress           = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
                        $progress_bar_value = $income >= $goal ? 100 : $progress;
                        break;

                    default:
                        $progress           = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
                        $progress_bar_value = $income >= $goal ? 100 : $progress;
                        break;

                }

                ?>
                <div class="give-form-grid-progress">
                    <?php
                    $style = "width:$progress_bar_value%;";
                    $style .= "background: linear-gradient(180deg, {$color} 0%, {$color} 100%); background-blend-mode: multiply;";
                    echo "
                                            <div class='give-form-grid-progress-bar'>
                                                    <div class='give-progress-bar' role='progressbar' aria-valuemin='0' aria-valuemax='100' aria-valuenow='$progress_bar_value'>
                                                        <span style='" . esc_attr($style) . "'></span>
                                                    </div>
                                            </div>
                                        ";

                    ?>
                    <div class="form-grid-raised">
                        <div class="form-grid-raised__details">
                            <?php
                            if ( 'amount' === $goal_format ) :

                                /**
                                 * Filter the give currency.
                                 *
                                 * @since 1.8.17
                                 */
                                $form_currency = apply_filters( 'give_goal_form_currency', give_get_currency( $form_id ), $form_id );

                                /**
                                 * Filter the income formatting arguments.
                                 *
                                 * @since 1.8.17
                                 */
                                $income_format_args = apply_filters(
                                    'give_goal_income_format_args',
                                    array(
                                        'sanitize' => false,
                                        'currency' => $form_currency,
                                        'decimal'  => false,
                                    ),
                                    $form_id
                                );

                                /**
                                 * Filter the goal formatting arguments.
                                 *
                                 * @since 1.8.17
                                 */
                                $goal_format_args = apply_filters(
                                    'give_goal_amount_format_args',
                                    array(
                                        'sanitize' => false,
                                        'currency' => $form_currency,
                                        'decimal'  => false,
                                    ),
                                    $form_id
                                );

                                /**
                                 * This filter will be used to convert the goal amounts to different currencies.
                                 *
                                 * @since 2.5.4
                                 *
                                 * @param array $amounts List of goal amounts.
                                 * @param int   $form_id Donation Form ID.
                                 */
                                $goal_amounts = apply_filters(
                                    'give_goal_amounts',
                                    array(
                                        $form_currency => $goal,
                                    ),
                                    $form_id
                                );

                                /**
                                 * This filter will be used to convert the income amounts to different currencies.
                                 *
                                 * @since 2.5.4
                                 *
                                 * @param array $amounts List of goal amounts.
                                 * @param int   $form_id Donation Form ID.
                                 */
                                $income_amounts = apply_filters(
                                    'give_goal_raised_amounts',
                                    array(
                                        $form_currency => $income,
                                    ),
                                    $form_id
                                );

                                // Get human readable donation amount.
                                $income = give_human_format_large_amount( give_format_amount( $income, $income_format_args ), array( 'currency' => $form_currency ) );
                                $goal   = give_human_format_large_amount( give_format_amount( $goal, $goal_format_args ), array( 'currency' => $form_currency ) );

                                // Format the human readable donation amount.
                                $formatted_income = give_currency_filter(
                                    $income,
                                    array(
                                        'form_id' => $form_id,
                                    )
                                );

                                $formatted_goal = give_currency_filter(
                                    $goal,
                                    array(
                                        'form_id' => $form_id,
                                    )
                                );
                                echo sprintf(
                                /* translators: 1: amount of income raised 2: goal target amount. */
                                    __( '<span class="amount"  data-amounts="%1$s">%2$s</span>
                                                     <span class="goal" data-amounts="%3$s">of %4$s</span>', 'give' ),
                                    esc_attr( wp_json_encode( $income_amounts, JSON_PRETTY_PRINT ) ),
                                    esc_attr( $formatted_income ),
                                    esc_attr( wp_json_encode( $goal_amounts, JSON_PRETTY_PRINT ) ),
                                    esc_attr( $formatted_goal )
                                );

                            elseif ( 'percentage' === $goal_format ) :

                                echo sprintf( /* translators: %s: percentage of the amount raised compared to the goal target */
                                    __( '
                                                   <span class="amount">%s%%</span>
                                                   <span class="goal">of 100&#37;</span>', 'give' ),
                                    round( $progress )
                                );

                            elseif ( 'donation' === $goal_format ) :?>

                                <span class="amount">
                                    <?php echo give_format_amount( $form->get_sales(), array( 'decimal' => false ))?>
                                </span>

                                <span class="goal">
                                    <?php echo sprintf(_n('of %s donation', 'of %s donations', $goal, 'give'),
                                        give_format_amount( $goal, array( 'decimal' => false ))); ?>
                                </span>

                            <?php elseif ( 'donors' === $goal_format ) : ?>

                                <span class="amount"> <?php echo give_get_form_donor_count( $form->ID ) ?> </span>
                                <span class="goal">
                                    <?php echo sprintf(_n('of %s donor', 'of %s donors', $goal, 'give'),
                                        give_format_amount( $goal, array( 'decimal' => false ))); ?>
                                </span>
                            <?php endif ?>
                        </div>

                        <div class="form-grid-raised__details">
                            <span class="amount form-grid-raised__details_donations">
                                <?php echo give_format_amount( $form->get_sales(), array( 'decimal' => false )) ?>
                            </span>
                            <span class="goal">
                                <?php echo _n('donation', 'donations', $goal, 'give')?> </span>
                        </div>
                    </div>
                </div>
            <?php endif?>
        </div>
    </div>
    </a>
    <?php
    // If modal, print form in hidden container until it is time to be revealed.
    if ( 'modal_reveal' === $atts['display_style'] ) {
        if (
            ! isset($_GET['context']) // check if we are in block editor
            && ! FormUtils::isLegacyForm( $form_id )
        ) {
            echo give_form_shortcode(
                [
                    'id'            => $form_id,
                    'display_style' => 'button',
                ]
            );

        } else {
            printf(
                '<div id="give-modal-form-%1$s" class="give-donation-grid-item-form give-modal--slide mfp-hide">',
                $form_id
            );
            give_get_donation_form( $form_id );
            echo '</div>';
        }
    }
    ?>
</div>

