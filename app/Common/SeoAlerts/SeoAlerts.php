<?php
namespace AIOSEO\Plugin\Common\SeoAlerts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle SEO Alerts functionality.
 *
 * @since 4.9.9
 */
class SeoAlerts {
	/**
	 * The Email class instance.
	 *
	 * @since 4.9.9
	 *
	 * @var Email
	 */
	private $email;

	/**
	 * The Slack class instance.
	 *
	 * @since 4.9.9
	 *
	 * @var Slack
	 */
	public $slack;

	/**
	 * The action name for our alerts check.
	 *
	 * @since 4.9.9
	 *
	 * @var string
	 */
	private $actionName = 'aioseo_seo_alerts_check';

	/**
	 * The name for the cache key for the last alert time.
	 *
	 * @since 4.9.9
	 *
	 * @var string
	 */
	private $lastAlertTimeCacheKey = 'seo_alerts_last_sent';

	/**
	 * Class constructor.
	 *
	 * @since 4.9.9
	 */
	public function __construct() {
		$this->email = new Email();
		$this->slack = new Slack();

		add_action( 'init', [ $this, 'scheduleRecurringEvents' ] );
		add_action( $this->actionName, [ $this, 'runAlertsCheck' ] );
	}

	/**
	 * Maybe schedule the recurring alerts check using Action Scheduler.
	 *
	 * @since 4.9.9
	 *
	 * @return void
	 */
	public function scheduleRecurringEvents() {
		// Unschedule when alerts can't be sent, or when one was already sent within the cooldown
		// window — there's no point checking in the background until the cooldown expires.
		if (
			! $this->isEnabled() ||
			aioseo()->core->cache->get( $this->lastAlertTimeCacheKey )
		) {
			aioseo()->actionScheduler->unschedule( $this->actionName );

			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->actionName ) ) {
			return;
		}

		// Schedule a check every hour.
		aioseo()->actionScheduler->scheduleRecurrent( $this->actionName, 0, HOUR_IN_SECONDS );
	}

	/**
	 * Whether alerts are enabled and at least one delivery channel (email or Slack) is configured.
	 *
	 * @since 4.9.9
	 *
	 * @return bool Whether alerts can actually be sent.
	 */
	public function isEnabled() {
		if ( ! aioseo()->options->tools->seoAlerts->enable ) {
			return false;
		}

		$emails       = array_filter( array_map( 'trim', array_column( (array) aioseo()->options->tools->seoAlerts->recipients, 'email' ) ) );
		$hasRecipient = ! empty( $emails );
		$hasWebhook   = aioseo()->sensitiveOptions->hasValue( 'seoAlertsSlackWebhookUrl' );

		return $hasRecipient || $hasWebhook;
	}

	/**
	 * Run the alerts check and send notifications if issues are found.
	 *
	 * @since 4.9.9
	 *
	 * @return void
	 */
	public function runAlertsCheck() {
		// Skip if alerts are disabled or no delivery channel is configured.
		if ( ! $this->isEnabled() ) {
			return;
		}

		$triggeredAlerts = [];

		// Check each alert type if it's enabled.
		$noindexHomepage = aioseo()->options->tools->seoAlerts->alerts->noindexHomepage;
		$robotsTxtError  = aioseo()->options->tools->seoAlerts->alerts->robotsTxtError;
		$xmlSitemapError = aioseo()->options->tools->seoAlerts->alerts->xmlSitemapError;

		if ( ! empty( $noindexHomepage ) && $this->isHomepageNoindexed() ) {
			$triggeredAlerts['noindexHomepage'] = true;
		}

		if ( ! empty( $robotsTxtError ) && $this->hasRobotsTxtError() ) {
			$triggeredAlerts['robotsTxtError'] = true;
		}

		if ( ! empty( $xmlSitemapError ) ) {
			$sitemapErrors = $this->hasXmlSitemapError();
			if ( ! empty( $sitemapErrors ) ) {
				$triggeredAlerts['xmlSitemapError'] = $sitemapErrors;
			}
		}

		// If alerts were triggered, send notifications.
		if ( ! empty( $triggeredAlerts ) ) {
			$this->sendAlerts( $triggeredAlerts );
		}
	}

	/**
	 * Returns the HTTP request args shared by all alert checks.
	 *
	 * NOTE: sslverify is disabled and the timeout kept short so self-signed certificates and slow
	 * loopback requests don't trigger false-positive alerts.
	 *
	 * @since 4.9.9
	 *
	 * @return array The request args.
	 */
	private function getRequestArgs() {
		return apply_filters( 'aioseo_seo_alerts_request_args', [
			'blocking'    => true,
			'timeout'     => 10,
			'redirection' => 1,
			'sslverify'   => false
		] );
	}

	/**
	 * Check if the homepage is set to noindex.
	 *
	 * @since 4.9.9
	 *
	 * @return boolean Whether the homepage is noindexed.
	 */
	public function isHomepageNoindexed() {
		// First check if homepage is noindexed according to settings.
		if ( $this->isHomepageNoindexedBySettings() ) {
			return true;
		}

		// If not noindexed by settings, make a request to the homepage to check the actual output.
		$response = wp_remote_get( home_url(), $this->getRequestArgs() );

		if ( is_wp_error( $response ) ) {
			// If we can't access the homepage, assume it's not noindexed.
			return false;
		}

		// A noindex can be set via the X-Robots-Tag header regardless of the page markup, so
		// check it first — a header-only noindex won't appear anywhere in the body.
		$headers = wp_remote_retrieve_headers( $response );
		if ( isset( $headers['x-robots-tag'] ) && false !== strpos( (string) $headers['x-robots-tag'], 'noindex' ) ) {
			return true;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			// If the body is empty, assume it's not noindexed.
			return false;
		}

		// Check if noindex appears in the body directly (quick check).
		if ( false !== strpos( $body, 'noindex' ) ) {
			// Load HTML into DOMDocument for proper parsing.
			$dom = new \DOMDocument();
			libxml_use_internal_errors( true ); // Suppress errors for malformed HTML.

			if ( $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $body ) ) {
				$xpath = new \DOMXPath( $dom );

				// Find meta robots tags.
				$metaRobots = $xpath->query( '//meta[@name="robots"]' );

				$metaRobotsNode = $metaRobots->item( 0 );
				if ( $metaRobotsNode instanceof \DOMElement ) {
					$content = $metaRobotsNode->getAttribute( 'content' );
					if ( false !== strpos( $content, 'noindex' ) ) {
						// "noindex" found in meta robots tag.
						libxml_clear_errors(); // Clear libxml errors.

						return true;
					}
				}
			}

			libxml_clear_errors(); // Clear libxml errors.
		}

		// If we got here, the homepage is not noindexed.
		return false;
	}

	/**
	 * Check if the homepage is set to noindex based on settings.
	 * This is a fallback method if we can't check the actual page output.
	 *
	 * @since 4.9.9
	 *
	 * @return boolean Whether the homepage is noindexed according to settings.
	 */
	private function isHomepageNoindexedBySettings() {
		// Check if WordPress core is set to discourage search engines.
		if ( aioseo()->helpers->isSearchEnginesDiscouraged() ) {
			return true;
		}

		// For static homepage.
		if ( aioseo()->helpers->isStaticHomePage() ) {
			$homePage = aioseo()->helpers->getHomePage();
			if ( ! $homePage ) {
				return false;
			}

			// Check if AIOSEO is set to noindex the homepage.
			$metaData = aioseo()->meta->metaData->getMetaData( $homePage );
			if ( $metaData && $metaData->robots_noindex ) {
				return true;
			}

			return false;
		}

		// For dynamic homepage (latest posts).
		if ( aioseo()->helpers->isDynamicHomePage() ) {
			// Check if blog is set to noindex.
			if (
				aioseo()->options->searchAppearance->archives->date->show &&
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default &&
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the robots.txt file has an error.
	 *
	 * @since 4.9.9
	 *
	 * @return boolean Whether the robots.txt has an error.
	 */
	public function hasRobotsTxtError() {
		$robotsTxtUrl = home_url( '/robots.txt' );
		$response     = wp_remote_get( $robotsTxtUrl, $this->getRequestArgs() );

		// Check if we received a valid response.
		if ( is_wp_error( $response ) ) {
			return true;
		}

		$responseCode = wp_remote_retrieve_response_code( $response );

		// Response code should be 200 for a valid robots.txt.
		if ( 200 !== $responseCode ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if any XML sitemap has an error.
	 *
	 * @since 4.9.9
	 *
	 * @return array An array of sitemap errors, or empty array if no errors.
	 */
	public function hasXmlSitemapError() {
		$errors = [];

		// getSitemapUrls() returns every active sitemap, including addon sitemaps
		// (e.g. video, news), so new sitemap types are covered automatically.
		$sitemapUrls = aioseo()->sitemap->helpers->getSitemapUrls();

		foreach ( $sitemapUrls as $sitemapUrl ) {
			$response = wp_remote_get( $sitemapUrl, $this->getRequestArgs() );

			// Check if we received a valid response.
			if ( is_wp_error( $response ) ) {
				$errors[ $sitemapUrl ] = [
					'code'    => 'request_failed',
					'message' => $response->get_error_message()
				];

				continue;
			}

			$responseCode = wp_remote_retrieve_response_code( $response );

			// Response code should be 200 for a valid sitemap.
			if ( 200 !== $responseCode ) {
				$errors[ $sitemapUrl ] = [
					'code'    => $responseCode,
					'message' => wp_remote_retrieve_response_message( $response )
				];

				continue;
			}

			// Check if the response is valid XML.
			$body = wp_remote_retrieve_body( $response );

			// Suppress XML errors.
			libxml_use_internal_errors( true );

			$xml = simplexml_load_string( $body );
			if ( false === $xml ) {
				$xmlErrors = libxml_get_errors();

				// Clear XML errors.
				libxml_clear_errors();

				$errors[ $sitemapUrl ] = [
					'code'    => 'invalid_xml',
					'message' => ! empty( $xmlErrors ) ? $xmlErrors[0]->message : __( 'Invalid XML', 'all-in-one-seo-pack' )
				];

				continue;
			}
		}

		// Clear any XML parsing errors.
		libxml_clear_errors();

		return $errors;
	}

	/**
	 * Send alerts via email and/or Slack.
	 *
	 * @since 4.9.9
	 *
	 * @param  array $triggeredAlerts Array of triggered alert types.
	 * @return void
	 */
	private function sendAlerts( $triggeredAlerts ) {
		// Check if we've sent an alert in the last 24 hours.
		$lastAlertTime = aioseo()->core->cache->get( $this->lastAlertTimeCacheKey );
		if ( $lastAlertTime && ( time() - $lastAlertTime ) < DAY_IN_SECONDS ) {
			return;
		}

		$alertsSent = false;

		// Send email alerts if recipients are configured.
		$recipients = aioseo()->options->tools->seoAlerts->recipients;
		if ( ! empty( $recipients ) ) {
			try {
				$this->email->send( $triggeredAlerts, $recipients );
				$alertsSent = true;
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyCatchClause.Found
				// Let other delivery channels proceed if this one fails.
			}
		}

		// Send Slack alerts if webhook URL is configured.
		$webhookUrl = aioseo()->sensitiveOptions->get( 'seoAlertsSlackWebhookUrl' );
		$memberIds  = aioseo()->options->tools->seoAlerts->slackMemberIds;
		if ( ! empty( $webhookUrl ) ) {
			try {
				$this->slack->send( $triggeredAlerts, $webhookUrl, $memberIds );
				$alertsSent = true;
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyCatchClause.Found
				// Let other delivery channels proceed if this one fails.
			}
		}

		// If we sent any alerts, update the last sent time.
		if ( $alertsSent ) {
			aioseo()->core->cache->update( $this->lastAlertTimeCacheKey, time(), DAY_IN_SECONDS );
		}
	}

	/**
	 * Send a test email alert.
	 *
	 * @since 4.9.9
	 *
	 * @param  string $recipient The email address to send to.
	 * @return boolean          Whether the email was sent successfully.
	 */
	public function sendTestEmail( $recipient ) {
		try {
			$this->email->send( [], [ [ 'email' => $recipient ] ], true );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Send a test Slack alert.
	 *
	 * @since 4.9.9
	 *
	 * @param  string $webhookUrl The Slack webhook URL.
	 * @param  array  $memberIds  The Slack member IDs to mention.
	 * @return boolean           Whether the message was sent successfully.
	 */
	public function sendTestSlack( $webhookUrl, $memberIds = [] ) {
		try {
			$this->slack->send( [], $webhookUrl, $memberIds, true );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get alert messages for the triggered alerts.
	 *
	 * @since 4.9.9
	 *
	 * @param  array $triggeredAlerts Array of triggered alert types.
	 * @return array                  Array of alert messages.
	 */
	public function getAlertMessages( $triggeredAlerts ) {
		$messages = [];

		foreach ( $triggeredAlerts as $alertType => $alertDetails ) {
			// Handle xmlSitemapError which is an array of errors keyed by sitemap URL.
			if ( 'xmlSitemapError' === $alertType && is_array( $alertDetails ) ) {
				foreach ( $alertDetails as $sitemapUrl => $error ) {
					$messages[] = '🚨 ' . sprintf(
						// Translators: 1 - The sitemap URL, 2 - The error message.
						__( 'Your sitemap at %1$s returned an error: %2$s. This can prevent search engines from properly indexing your content.', 'all-in-one-seo-pack' ),
						$sitemapUrl,
						$error['message']
					);
				}
				continue;
			}

			switch ( $alertType ) {
				case 'noindexHomepage':
					$messages[] = '🚨 ' . __( 'Your homepage is set to noindex, which prevents search engines from indexing it. This can severely impact your SEO.', 'all-in-one-seo-pack' );
					break;
				case 'robotsTxtError':
					$messages[] = '🚨 ' . __( 'Your robots.txt file returned an error. This can prevent search engines from properly crawling your site.', 'all-in-one-seo-pack' );
					break;
				default:
					break;
			}
		}

		return $messages;
	}
}