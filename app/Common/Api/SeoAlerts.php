<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API class for the SEO Alerts feature.
 *
 * @since 4.9.9
 */
class SeoAlerts {
	/**
	 * Send a test email for SEO Alerts.
	 *
	 * @since 4.9.9
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function sendTestEmail( $request ) {
		$body  = $request->get_json_params();
		$email = ! empty( $body['email'] ) ? sanitize_email( $body['email'] ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Please provide a valid email address.', 'all-in-one-seo-pack' )
			], 400 );
		}

		// Use the SeoAlerts class to send the test email
		$sent = aioseo()->seoAlerts->sendTestEmail( $email );

		if ( ! $sent ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Failed to send the test email. Please check your email configuration.', 'all-in-one-seo-pack' )
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			// Translators: %s is the email address.
			'message' => sprintf( __( 'A test email was sent to %s.', 'all-in-one-seo-pack' ), $email )
		], 200 );
	}

	/**
	 * Send a test Slack message for SEO Alerts.
	 *
	 * @since 4.9.9
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function sendTestSlack( $request ) {
		$body       = $request->get_json_params();
		$webhookUrl = ! empty( $body['webhookUrl'] ) ? esc_url_raw( trim( $body['webhookUrl'] ) ) : '';

		// Fall back to the saved (write-only) webhook when the field wasn't re-entered.
		if ( empty( $webhookUrl ) ) {
			$webhookUrl = aioseo()->sensitiveOptions->get( 'seoAlertsSlackWebhookUrl' );
		}

		if ( empty( $webhookUrl ) || ! aioseo()->seoAlerts->slack->isValidWebhookUrl( $webhookUrl ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Please provide a valid Slack webhook URL.', 'all-in-one-seo-pack' )
			], 400 );
		}

		$memberIds = [];
		if ( ! empty( $body['memberIds'] ) && is_array( $body['memberIds'] ) ) {
			foreach ( $body['memberIds'] as $member ) {
				if ( ! empty( $member['memberId'] ) ) {
					$memberIds[] = [ 'memberId' => sanitize_text_field( $member['memberId'] ) ];
				}
			}
		}

		$sent = aioseo()->seoAlerts->sendTestSlack( $webhookUrl, $memberIds );

		if ( ! $sent ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Failed to send the test message. Please check your Slack webhook URL.', 'all-in-one-seo-pack' )
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => __( 'A test message was sent to your Slack workspace.', 'all-in-one-seo-pack' )
		], 200 );
	}
}