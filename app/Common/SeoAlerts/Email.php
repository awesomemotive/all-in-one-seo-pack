<?php
namespace AIOSEO\Plugin\Common\SeoAlerts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the email notifications for SEO Alerts.
 *
 * @since 4.9.9
 */
class Email {
	/**
	 * Send an email alert.
	 *
	 * @since 4.9.9
	 *
	 * @param  array      $triggeredAlerts The alerts that were triggered.
	 * @param  array      $recipients      The recipients to send the alert to.
	 * @param  bool       $isTest          Whether this is a test email.
	 * @throws \Exception                  If the email could not be sent.
	 * @return void
	 */
	public function send( $triggeredAlerts, $recipients, $isTest = false ) {
		if ( empty( $recipients ) || ( ! $isTest && empty( $triggeredAlerts ) ) ) {
			return;
		}

		try {
			$subject = $this->getSubject( $isTest );
			$content = $this->getContentHtml( $triggeredAlerts, $isTest );
			$headers = $this->getHeaders();

			// Send an individual email to each recipient so their addresses aren't disclosed to one another.
			foreach ( $this->getRecipients( $recipients ) as $email ) {
				aioseo()->emailReports->mail->send( $email, $subject, $content, $headers );
			}
		} catch ( \Exception $e ) {
			throw new \Exception( esc_html( $e->getMessage() ), esc_html( $e->getCode() ) );
		}
	}

	/**
	 * Get the list of valid recipient email addresses.
	 *
	 * @since 4.9.9
	 *
	 * @param  array      $recipients The recipients array.
	 * @throws \Exception            If no valid recipient was set for the email.
	 * @return array                 The valid recipient email addresses.
	 */
	private function getRecipients( $recipients ) {
		$emails = array_map( function( $recipient ) {
			return trim( $recipient['email'] );
		}, $recipients );

		$emails = array_filter( $emails, 'is_email' );

		if ( empty( $emails ) ) {
			throw new \Exception( 'No valid recipient was set for the email.' );
		}

		return array_values( $emails );
	}

	/**
	 * Get email subject.
	 *
	 * @since 4.9.9
	 *
	 * @param  bool   $isTest Whether this is a test email.
	 * @return string         The email subject.
	 */
	private function getSubject( $isTest = false ) {
		if ( $isTest ) {
			return sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The site name.
				__( '%1$s SEO Alerts - Test Email for %2$s', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME,
				get_bloginfo( 'name' )
			);
		}

		return sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The site name.
			__( '%1$s SEO Alerts - Action Required for %2$s', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_SHORT_NAME,
			get_bloginfo( 'name' )
		);
	}

	/**
	 * Get content html.
	 *
	 * @since 4.9.9
	 *
	 * @param  array   $triggeredAlerts The alerts that were triggered.
	 * @param  bool    $isTest          Whether this is a test email.
	 * @return string                   The email content.
	 */
	private function getContentHtml( $triggeredAlerts = [], $isTest = false ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$preHeader = $isTest
			? sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'This is a test email from %1$s SEO Alerts.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			)
			: sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'Important SEO issues have been detected by %1$s that require your attention.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			);

		$heading = $isTest
			? __( 'SEO Alerts Test Email', 'all-in-one-seo-pack' )
			: __( 'SEO Alerts - Action Required', 'all-in-one-seo-pack' );

		$subheading = $isTest
			? sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'This is a test email to confirm that %1$s can properly send email notifications about SEO issues on your site.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			)
			: __( 'The following SEO issues have been detected on your site and require your attention:', 'all-in-one-seo-pack' );

		$alertMessages = $isTest
			? [ __( 'This is a test alert message. Your email notifications are working correctly!', 'all-in-one-seo-pack' ) ]
			: aioseo()->seoAlerts->getAlertMessages( $triggeredAlerts );

		$mktUrl = trailingslashit( AIOSEO_MARKETING_URL );
		$medium = 'seo-alerts-email';

		$links = [
			'manage'         => admin_url( 'admin.php?page=aioseo-tools#/seo-alerts' ),
			'marketing-site' => aioseo()->helpers->utmUrl( $mktUrl, $medium ),
			'facebook'       => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/facebook', $medium ),
			'linkedin'       => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/linkedin', $medium ),
			'youtube'        => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/youtube', $medium ),
			'twitter'        => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/twitter', $medium ),
		];

		ob_start();
		require AIOSEO_DIR . '/app/Common/Views/alerts/email.php';

		return ob_get_clean();
	}

	/**
	 * Get email headers.
	 *
	 * @since 4.9.9
	 *
	 * @return array The email headers.
	 */
	private function getHeaders() {
		return [ 'Content-Type: text/html; charset=UTF-8' ];
	}
}