<?php

/**
 * Email Notification
 *
 * This class handles table html  for email notifications listing.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */
class Give_Email_Notification_Table extends WP_List_Table {
	/**
	 * @var Give_Email_Notifications $email_notifications
	 * @since  2.0
	 * @access private
	 */
	private $email_notifications;


	/**
	 * Number of email notifications per page
	 *
	 * @since  2.0
	 * @access private
	 * @var int
	 */
	private $per_page = - 1;

	/**
	 * Give_Email_Notification_Table constructor.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Give Email Notification', 'give' ),
			'plural'   => __( 'Give Email Notifications', 'give' ),
		) );

		$this->email_notifications = Give_Email_Notifications::get_instance();
	}


	/**
	 * Get table columns.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_columns() {
		/**
		 * Filter the table columns
		 *
		 * @since 2.0
		 */
		return apply_filters( 'give_email_notification_setting_columns', array(
			'cb'         => __( 'Email Status', 'give' ),
			'name'       => __( 'Email', 'give' ),
			'email_type' => __( 'Content Type', 'give' ),
			'recipient'  => __( 'Recipient(s)', 'give' ),
			'setting'    => __( 'Edit Email', 'give' ),
		) );
	}

	/**
	 * Get name column.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return  string
	 */
	public function column_name( $email ) {
		$edit_url = esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->config['id'] ) );
		$actions  = $this->get_row_actions( $email );

		ob_start();
		?>
		<a class="row-title" href="<?php echo $edit_url; ?>"><?php echo $email->config['label']; ?></a>

		<?php if ( $desc = $email->config['description'] ) : ?>
			<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo esc_attr( $desc ); ?>"></span>
		<?php endif; ?>

		<?php echo $this->row_actions( $actions ); ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get recipient column.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return string
	 */
	public function column_recipient( $email ) {
		ob_start();
		?>
		<?php
		if ( $email->get_recipient_group_name() ) {
			echo $email->get_recipient_group_name();
		} else {
			$recipients = $email->get_recipient();
			if ( is_array( $recipients ) ) {
				$recipients = implode( '<br>', $recipients );
			}

			echo $recipients;
		}
		?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get status column.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return string
	 */
	public function column_cb( $email ) {
		ob_start();

		$notification_status = $email->get_notification_status();
		$default_class       = "give-email-notification-status give-email-notification-{$notification_status} dashicons";
		$attributes['class'] = Give_Email_Notification_Util::is_email_notification_active( $email )
			? "{$default_class} dashicons-yes"
			: "{$default_class} dashicons-no-alt";

		$attributes['data-status'] = "{$notification_status}";
		$attributes['data-id']     = "{$email->config['id']}";

		$attributes['data-edit'] = (int) Give_Email_Notification_Util::is_notification_status_editable( $email );

		if ( ! $attributes['data-edit'] ) {
			$attributes['data-tooltip'] = __( 'You can not edit this notification directly. This will be enable or disable automatically on basis of plugin settings.', 'give' );
		}

		$attribute_str = '';
		foreach ( $attributes as $tag => $value ) {
			$attribute_str .= " {$tag}=\"{$value}\"";
		}
		?>
		<span<?php echo $attribute_str; ?>></span><span class="spinner"></span>
		<?php

		return ob_get_clean();
	}


	/**
	 * Get email_type column.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return string
	 */
	public function column_email_type( Give_Email_Notification $email ) {
		return Give_Email_Notification_Util::get_formatted_email_type( $email->config['content_type'] );
	}

	/**
	 * Get setting column.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return string
	 */
	public function column_setting( Give_Email_Notification $email ) {
		ob_start();
		?>
		<a class="button button-small" data-tooltip="<?php echo __( 'Edit', 'give' ); ?> <?php echo $email->config['label']; ?>" href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->config['id'] ) ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
		<?php
		return ob_get_clean();
	}


	/**
	 * Print row actions.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return array
	 */
	private function get_row_actions( $email ) {
		$edit_url = esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->config['id'] ) );

		/**
		 * Filter the row actions
		 *
		 * @since 2.0
		 *
		 * @param array $row_actions
		 */
		$row_actions = apply_filters(
			'give_email_notification_row_actions',
			array(
				'edit' => "<a href=\"{$edit_url}\">" . __( 'Edit', 'give' ) . '</a>',
			),
			$email
		);

		return $row_actions;
	}


	/**
	 * Prepare email notifications
	 *
	 * @since  2.0
	 * @access public
	 */
	public function prepare_items() {
		// Set columns.
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable, $this->get_primary_column_name() );

		// Set email notifications.
		$email_notifications = $this->email_notifications->get_email_notifications();
		$totalItems          = count( $email_notifications );
		$this->items         = $email_notifications;
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $this->per_page,
		) );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  2.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No give email notification found.' );
	}

	/**
	 * Get primary column.
	 *
	 * @since  2,0
	 * @access public
	 *
	 * @return string Name of the default primary column.
	 */
	public function get_primary_column_name() {
		return 'name';
	}
}
