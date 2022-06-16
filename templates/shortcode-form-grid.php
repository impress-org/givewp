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
            return "<span style='color: $tag_text_color; background-color: $tag_bg_color;'>$term->name</span>";
        }, $tags
    );

    $tag_elements = implode('', $tag_elements);
    $styles = $apply_styles ? "style='background-color: $tag_container_color;'" : '';

    return "
         <div class='$wrapper_class' $styles >
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
		<div class="give-form-grid" style="flex-direction:<?php echo $flex_direction ?>">
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
                                <img class='give-form-grid-media' src='$imageSrc' alt='' />

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
                                 <div class='give-form-grid-media' >
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
                        if ( $raw_content = $formTemplate->getFormExcerpt( $form_id ) ) {
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
                        $button_text = ! empty( $atts['donate_button_text'] )
                            ? $atts['donate_button_text']
                            : give_get_meta( $form_id, '_give_form_grid_donate_button_text', true );

                        $button_text_color = ! empty( $atts['donate_button_text_color'] )
                            ? $atts['donate_button_text_color']
                            : '#fff';
                        ?>
                        <button style="text-decoration-color: <?php echo $button_text_color; ?>">
                                    <span style="color: <?php echo $button_text_color; ?>">
                                        <?php echo $button_text ?: __( 'Donate', 'give' ); ?>
                                    </span>
                        </button>
                    <?php endif; ?>

                </div>
                <?php
                    // Maybe display the goal progress bar.
                    if (
                        give_is_setting_enabled( get_post_meta( $form_id, '_give_goal_option', true ) )
                        && true === $atts['show_goal']
                    ) {
                        echo '<div class="give-form-grid-content__progress">';
                        give_show_goal_progress( $form_id, [
                            'show_bar' => $atts['show_bar'],
                            'progress_bar_color' => $atts['progress_bar_color'],
                        ] );
                        echo '</div>';
                    }
                ?>
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
