<?php
namespace AIOSEO\Plugin\Common\SeoAlerts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Slack notifications for SEO Alerts.
 *
 * @since 4.9.9
 */
class Slack {
	/**
	 * Send a Slack alert.
	 *
	 * @since 4.9.9
	 *
	 * @param  array      $triggeredAlerts The alerts that were triggered.
	 * @param  string     $webhookUrl      The Slack webhook URL.
	 * @param  array      $memberIds       The Slack member IDs to mention.
	 * @param  bool       $isTest          Whether this is a test message.
	 * @throws \Exception                  If the alert could not be sent.
	 * @return void
	 */
	public function send( $triggeredAlerts, $webhookUrl, $memberIds = [], $isTest = false ) {
		if ( empty( $webhookUrl ) || ( ! $isTest && empty( $triggeredAlerts ) ) ) {
			return;
		}

		// Never POST anywhere but Slack — guards against SSRF via a tampered webhook URL.
		if ( ! $this->isValidWebhookUrl( $webhookUrl ) ) {
			throw new \Exception( esc_html__( 'The Slack webhook URL is invalid. It must be a hooks.slack.com URL.', 'all-in-one-seo-pack' ) );
		}

		try {
			$response = wp_remote_post( $webhookUrl, [
				'headers' => [ 'Content-Type' => 'application/json' ],
				'body'    => wp_json_encode( $this->getPayload( $triggeredAlerts, $memberIds, $isTest ) )
			] );

			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			$responseCode = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $responseCode ) {
				throw new \Exception( sprintf(
					// Translators: 1 - The response code.
					__( 'Slack API returned an error response: %1$s', 'all-in-one-seo-pack' ),
					$responseCode
				) );
			}
		} catch ( \Exception $e ) {
			throw new \Exception( esc_html( $e->getMessage() ), esc_html( $e->getCode() ) );
		}
	}

	/**
	 * Whether the given URL is a valid Slack incoming webhook URL.
	 *
	 * NOTE: Slack incoming webhooks are always https://hooks.slack.com/... — restricting to that
	 * host prevents the webhook from being pointed at internal/metadata endpoints (SSRF).
	 *
	 * @since 4.9.9
	 *
	 * @param  string $url The URL to validate.
	 * @return bool        Whether the URL is a valid Slack webhook URL.
	 */
	public function isValidWebhookUrl( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return false;
		}

		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		$host   = wp_parse_url( $url, PHP_URL_HOST );

		return 'https' === strtolower( (string) $scheme ) && 'hooks.slack.com' === strtolower( (string) $host );
	}

	/**
	 * Get the Slack message payload.
	 *
	 * @since 4.9.9
	 *
	 * @param  array  $triggeredAlerts The alerts that were triggered.
	 * @param  array  $memberIds       The Slack member IDs to mention.
	 * @param  bool   $isTest          Whether this is a test message.
	 * @return array                   The Slack message payload.
	 */
	private function getPayload( $triggeredAlerts = [], $memberIds = [], $isTest = false ) {
		$blocks = [];

		// Add header section.
		$blocks[] = [
			'type' => 'header',
			'text' => [
				'type' => 'plain_text',
				'text' => $isTest
					? sprintf(
						// Translators: 1 - The site name.
						__( 'SEO Alerts Test Message for %1$s', 'all-in-one-seo-pack' ),
						get_bloginfo( 'name' )
					)
					: sprintf(
						// Translators: 1 - The site name.
						__( 'SEO Alerts - Action Required for %1$s', 'all-in-one-seo-pack' ),
						get_bloginfo( 'name' )
					)
			]
		];

		// Add description section.
		$blocks[] = [
			'type' => 'section',
			'text' => [
				'type' => 'mrkdwn',
				'text' => $isTest
					? sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__( 'This is a test message to confirm that %1$s can properly send Slack notifications about SEO issues on your site.', 'all-in-one-seo-pack' ),
						AIOSEO_PLUGIN_SHORT_NAME
					)
					: sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__( 'Important SEO issues have been detected by %1$s that require your attention:', 'all-in-one-seo-pack' ),
						AIOSEO_PLUGIN_SHORT_NAME
					)
			]
		];

		// Add divider.
		$blocks[] = [
			'type' => 'divider'
		];

		// Add alert messages.
		$alertMessages = $isTest
			? [ __( 'This is a test alert message. Your Slack notifications are working correctly!', 'all-in-one-seo-pack' ) ]
			: aioseo()->seoAlerts->getAlertMessages( $triggeredAlerts );

		if ( ! empty( $alertMessages ) ) {
			$blocks[] = [
				'type' => 'section',
				'text' => [
					'type' => 'mrkdwn',
					'text' => implode( "\n\n", $alertMessages )
				]
			];
		}

		// Add divider.
		$blocks[] = [
			'type' => 'divider'
		];

		// Add manage link.
		$blocks[] = [
			'type' => 'section',
			'text' => [
				'type' => 'mrkdwn',
				'text' => sprintf(
					'<%1$s|%2$s>',
					admin_url( 'admin.php?page=aioseo-tools#/seo-alerts' ),
					__( 'Manage SEO Alerts', 'all-in-one-seo-pack' )
				)
			]
		];

		// Add member mentions if any.
		$memberIds = array_values( array_filter( $memberIds, function( $member ) {
			return ! empty( trim( $member['memberId'], '@ ' ) );
		} ) );

		if ( ! empty( $memberIds ) ) {
			$mentions = array_map( function( $member ) {
				return '<@' . trim( $member['memberId'], '@ ' ) . '>';
			}, $memberIds );

			$blocks[] = [
				'type'     => 'context',
				'elements' => [
					[
						'type' => 'mrkdwn',
						'text' => __( 'Attention:', 'all-in-one-seo-pack' ) . ' ' . implode( ' ', $mentions )
					]
				]
			];
		}

		return [
			'blocks' => $blocks
		];
	}
}