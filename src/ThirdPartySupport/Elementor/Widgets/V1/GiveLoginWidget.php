<?php

namespace Give\ThirdPartySupport\Elementor\Widgets\V1;

use Elementor\Widget_Base;

/**
 * Elementor Give Login Widget.
 *
 * Elementor widget that inserts the GiveWP [give_login] shortcode to output a login form.
 *
 * @since 4.7.0 migrated from givewp-elementor-widgets
 */

class GiveLoginWidget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve Give Login widget name.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'Give Login';
    }

    /**
     * Get widget title.
     *
     * Retrieve Give Login widget title.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __('Give Login (Legacy)', 'give');
    }

    /**
     * Get widget icon.
     *
     * Retrieve Give Login widget icon.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'give-icon';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the Give Login widget belongs to.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['givewp-category-legacy'];
    }

    /**
     * Widget inner wrapper.
     *
     * Use optimized DOM structure, without the inner wrapper.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access public
     */
    public function has_widget_inner_wrapper(): bool
    {
        return false;
    }

    /**
     * Login Give Login widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'give_login_settings',
            [
                'label' => __('GiveWP Login Widget', 'give'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'login_url',
            [
                'label' => __('Redirect URL', 'give'),
                'type' => \Elementor\Controls_Manager::URL,
                'show_external' => false,
            ]
        );

        $this->add_control(
            'give_form_info',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'give-info',
                'raw' => '
					<div class="give">
						<p class="info-head">
							' . __('GIVEWP LOGIN WIDGET', 'give') . '</p>
						<p class="info-message">' . __('This is the GiveWP Login widget.', 'give') . '</p>
						<p class="info-message">' . __('This is a sample Login form with all fields exposed. This is only to help position and style the form with Elementor. If you want to see the live form, go to this page while logged out or in an Icognito browser.', 'give') . '</p>
						<p class="give-docs-links">
							<a href="https://givewp.com/documentation/core/shortcodes/give_login/?utm_source=plugin_settings&utm_medium=referral&utm_campaign=Free_Addons&utm_content=givelementor" rel="noopener noreferrer" target="_blank"><i class="fa fa-book" aria-hidden="true"></i>' . __('Visit the GiveWP Docs for more info on the GiveWP Login.', 'give') . '</a>
						</p>
				</div>'
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the [give_login] output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.7.0 migrated from givewp-elementor-widgets
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $link = esc_url($settings['login_url']['url']);

        $html = do_shortcode(
            '[give_login login_redirect="' . $link . '"]'
        );

        ob_start(); ?>
		<div class="givewp-elementor-widget give-login-shortcode-wrap">
			<form id="give-login-form" class="give-form">
				<fieldset>
					<legend><?php _e('Log into Your Account', 'give'); ?></legend>
					<div class="give-login-username give-login">
						<label for="give_user_login"><?php _e('Username or Email Address', 'give'); ?></label>
						<input name="give_user_login" id="give_user_login" class="give-required give-input" type="text" required="" aria-required="true">
					</div>

					<div class="give-login-password give-login">
						<label for="give_user_pass"><?php _e('Password', 'give'); ?></label>
						<input name="give_user_pass" id="give_user_pass" class="give-password give-required give-input" type="password" required="" aria-required="true">
					</div>

					<div class="give-login-submit give-login">
						<input id="give_login_submit" type="submit" class="give_submit" value="Log In">
					</div>

					<div class="give-lost-password give-login">
						<a href="<?php echo get_site_url(); ?>/wp-login.php?action=lostpassword">
							<?php _e('Reset Password', 'give'); ?></a>
					</div>
				</fieldset>
			</form>
		</div>
<?php

        ob_get_contents();

        $preview = ob_get_clean();

        echo '<div class="givewp-elementor-widget give-login-shortcode-wrap">';

        // Conditionally show frontend or preview form
        if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo $html;
        } else {
            echo $preview;
        }

        echo '</div>';
    }
}
