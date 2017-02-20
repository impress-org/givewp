<?php
/**
 * Emails
 *
 * This class handles all emails sent through Give
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Emails Class.
 *
 * @property $from_address
 * @property $from_name
 * @property $content_type
 * @property $headers
 * @property $html
 * @property $template
 * @property $heading
 *
 * @since 1.0
 */
class Give_Emails {

	/**
	 * Holds the from address.
	 *
	 * @since 1.0
	 */
	private $from_address;

	/**
	 * Holds the from name.
	 *
	 * @since 1.0
	 */
	private $from_name;

	/**
	 * Holds the email content type.
	 *
	 * @since 1.0
	 */
	private $content_type;

	/**
	 * Holds the email headers.
	 *
	 * @since 1.0
	 */
	private $headers;

	/**
	 * Whether to send email in HTML.
	 *
	 * @since 1.0
	 */
	private $html = true;

	/**
	 * The email template to use.
	 *
	 * @since 1.0
	 */
	private $template;

	/**
	 * The header text for the email.
	 *
	 * @since  1.0
	 */
	private $heading = '';

	/**
	 * Get things going.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'give_email_send_before', array( $this, 'send_before' ) );
		add_action( 'give_email_send_after', array( $this, 'send_after' ) );

	}

	/**
	 * Set a property.
	 *
	 * @since 1.0
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get the email from name.
	 *
	 * @since 1.0
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = give_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'give_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email from address.
	 *
	 * @since 1.0
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = give_get_option( 'from_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'give_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the email content type.
	 *
	 * @since 1.0
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'give_email_default_content_type', 'text/html', $this );
		} else if ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'give_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers.
	 *
	 * @since 1.0
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'give_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve email templates.
	 *
	 * @since 1.0
	 */
	public function get_templates() {
		$templates = array(
			'default' => esc_html__( 'Default Template', 'give' ),
			'none'    => esc_html__( 'No template, plain text only', 'give' )
		);

		return apply_filters( 'give_email_templates', $templates );
	}

	/**
	 * Get the enabled email template.
	 *
	 * @since 1.0
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = give_get_option( 'email_template', 'default' );
		}

		return apply_filters( 'give_email_template', $this->template );
	}

	/**
	 * Get the header text for the email.
	 *
	 * @since 1.0
	 */
	public function get_heading() {
		return apply_filters( 'give_email_heading', $this->heading );
	}

	/**
	 * Parse email template tags.
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function parse_tags( $content ) {
		return $content;
	}

	/**
	 * Build the final email.
	 *
	 * @since 1.0
	 *
	 * @param $message
	 *
	 * @return string
	 */
	public function build_email( $message ) {

		if ( false === $this->html ) {
			return apply_filters( 'give_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );

		$template = $this->get_template();

		ob_start();

		give_get_template_part( 'emails/header', $template, true );

		/**
		 * Fires in the email head.
		 *
		 * @since 1.0
		 *
		 * @param Give_Emails $this The email object.
		 */
		do_action( 'give_email_header', $this );

		if ( has_action( 'give_email_template_' . $template ) ) {
			/**
			 * Fires in a specific email template.
			 *
			 * @since 1.0
			 */
			do_action( "give_email_template_{$template}" );
		} else {
			give_get_template_part( 'emails/body', $template, true );
		}

		/**
		 * Fires in the email body.
		 *
		 * @since 1.0
		 *
		 * @param Give_Emails $this The email object.
		 */
		do_action( 'give_email_body', $this );

		give_get_template_part( 'emails/footer', $template, true );

		/**
		 * Fires in the email footer.
		 *
		 * @since 1.0
		 *
		 * @param Give_Emails $this The email object.
		 */
		do_action( 'give_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'give_email_message', $message, $this );
	}

	/**
	 * Send the email.
	 *
	 * @param  string       $to          The To address to send to.
	 * @param  string       $subject     The subject line of the email to send.
	 * @param  string       $message     The body of the email to send.
	 * @param  string|array $attachments Attachments to the email in a format supported by wp_mail().
	 *
	 * @return bool
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'You cannot send email with Give_Emails until init/admin_init has been reached.', 'give' ), null );

			return false;
		}

		/**
		 * Fires before sending an email.
		 *
		 * @since 1.0
		 *
		 * @param Give_Emails $this The email object.
		 */
		do_action( 'give_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		$message = $this->build_email( $message );

		$attachments = apply_filters( 'give_email_attachments', $attachments, $this );

		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );

		/**
		 * Fires after sending an email.
		 *
		 * @since 1.0
		 *
		 * @param Give_Emails $this The email object.
		 */
		do_action( 'give_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent.
	 *
	 * @since 1.0
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent.
	 *
	 * @since 1.0
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Reset heading to an empty string
		$this->heading = '';
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since 1.0
	 */
	public function text_to_html( $message ) {

		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = wpautop( $message );
		}

		return $message;
	}

}