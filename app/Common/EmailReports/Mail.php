<?php

namespace AIOSEO\Plugin\Common\EmailReports;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mail class.
 *
 * @since 4.7.2
 */
class Mail {
	/**
	 * Send the email.
	 *
	 * @since 4.7.2
	 *
	 * @param  mixed $to      Receiver.
	 * @param  mixed $subject Email subject.
	 * @param  mixed $message Message.
	 * @param  array $headers Email headers.
	 * @return bool           Whether the email was sent successfully.
	 */
	public function send( $to, $subject, $message, $headers = [ 'Content-Type: text/html; charset=UTF-8' ] ) {
		return wp_mail( $to, $subject, $message, $headers );
	}
}