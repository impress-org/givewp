<?php

/**
 * Email Notification
 *
 * This class handles table html  for email notifications listing.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
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
		parent::__construct(
			array(
				'singular' => 'giveemailnotification',
				'plural'   => 'giveemailnotifications',
			)
		);

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
		return apply_filters(
			'give_email_notification_setting_columns',
			array(
				'cb'         => __( 'Email Status', 'give' ),
				'name'       => __( 'Email', 'give' ),
				'email_type' => __( 'Content Type', 'give' ),
				'recipient'  => __( 'Recipient(s)', 'give' ),
				'setting'    => __( 'Edit Email', 'give' ),
			)
		);
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
			<?php echo Give()->tooltips->render_help( esc_attr( $desc ) ); ?>
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

		if ( Give_Email_Notification_Util::has_recipient_field( $email ) ) {
			$recipients = $email->get_recipient();
			if ( is_array( $recipients ) ) {
				$recipients = implode( '<br>', $recipients );
			}

			echo $recipients;

		} elseif ( ! empty( $email->config['recipient_group_name'] ) ) {
			echo $email->config['recipient_group_name'];
		}

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
		$notification_status  = $email->get_notification_status();
		$user_can_edit_status = (int) Give_Email_Notification_Util::is_notification_status_editable( $email );
		$icon_classes         = Give_Email_Notification_Util::is_email_notification_active( $email )
			? 'dashicons dashicons-yes'
			: 'dashicons dashicons-no-alt';
		$attributes           = array(
			'class'       => "give-email-notification-status give-email-notification-{$notification_status}",
			'data-id'     => $email->config['id'],
			'data-status' => $email->get_notification_status(),
			'data-edit'   => $user_can_edit_status,
		);

		if ( ! $user_can_edit_status ) {
			$icon_classes = 'dashicons dashicons-lock';

			$attributes['data-notice'] = esc_attr( $email->config['notices']['non-notification-status-editable'] );
		}

		$html = sprintf(
			'<span %1$s><i class="%2$s"></i></span></span><span class="spinner"></span>',
			give_get_attribute_str( $attributes ),
			$icon_classes
		);

		return $html;
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
		$email_content_type_label = apply_filters(
			"give_email_list_render_{$email->config['id']}_email_content_type",
			Give_Email_Notification_Util::get_formatted_email_type( $email->config['content_type'] ),
			$email
		);

		return $email_content_type_label;
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
		return Give()->tooltips->render_link(
			array(
				'label'       => __( 'Edit', 'give' ) . " {$email->config['label']}",
				'tag_content' => '<span class="dashicons dashicons-admin-generic"></span>',
				'link'        => esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->config['id'] ) ),
				'attributes'  => array(
					'class' => 'button button-small',
				),
			)
		);
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
		$email_notifications   = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable, $this->get_primary_column_name() );

		// Get email section
		$current_section = give_get_current_setting_section();

		// Set email notifications.
		/* @var Give_Email_Notification $email_notification */
		foreach ( $this->email_notifications->get_email_notifications() as $email_notification ) {
            if ( ! Give_Email_Notification_Util::is_show_on_emails_setting_page($email_notification)) {
                continue;
            }

            if ('donor-email' === $current_section) {
                // Add donor emails to email array list.
                if (empty($email_notification->config['has_recipient_field'])) {
                    $email_notifications[] = $email_notification;
                }
            } elseif ('admin-email' === $current_section) {
                // Add admin emails to email array list.
                if ( ! empty($email_notification->config['has_recipient_field'])) {
                    $email_notifications[] = $email_notification;
                }
            }

            /**
             * @since 2.23.0
             */
            $email_notifications = apply_filters('give_email_notification_table_items', $email_notifications,
                $email_notification, $current_section);
        }

		$total_items = count( $email_notifications );
		$this->items = $email_notifications;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
			)
		);
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  2.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No give email notification found.', 'give' );
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
