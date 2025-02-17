<?php

namespace AIOSEO\Plugin\Common\EmailReports\Summary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Summary class.
 *
 * @since 4.7.2
 */
class Summary {
	/**
	 * The action hook to execute when the event is run.
	 *
	 * @since 4.7.2
	 *
	 * @var string
	 */
	public $actionHook = 'aioseo_report_summary';

	/**
	 * Recipient for the email. Multiple recipients can be separated by a comma.
	 *
	 * @since 4.7.2
	 *
	 * @var string
	 */
	private $recipient;

	/**
	 * Email chosen frequency. Can be either 'weekly' or 'monthly'.
	 *
	 * @since 4.7.2
	 *
	 * @var string
	 */
	private $frequency;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.2
	 */
	public function __construct() {
		// No need to run any of this during a WP AJAX request.
		if ( wp_doing_ajax() ) {
			return;
		}

		// No need to keep trying scheduling unless on admin.
		add_action( 'admin_init', [ $this, 'maybeSchedule' ], 20 );

		add_action( $this->actionHook, [ $this, 'cronTrigger' ] );
	}

	/**
	 * The summary cron callback.
	 * Hooked into `{@see self::$actionHook}` action hook.
	 *
	 * @since 4.7.2
	 *
	 * @param  string $frequency The frequency of the email.
	 * @return void
	 */
	public function cronTrigger( $frequency ) {
		// Keep going only if the feature is enabled.
		if (
			! aioseo()->options->advanced->emailSummary->enable ||
			! apply_filters( 'aioseo_report_summary_enable', true, $frequency )
		) {
			return;
		}

		// Get all recipients for the given frequency.
		$recipients = wp_list_filter( aioseo()->options->advanced->emailSummary->recipients, [ 'frequency' => $frequency ] );
		if ( ! $recipients ) {
			return;
		}

		try {
			// Get only the email addresses.
			$recipients = array_column( $recipients, 'email' );

			$this->run( [
				'recipient' => implode( ',', $recipients ),
				'frequency' => $frequency,
			] );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Trigger the sending of the summary.
	 *
	 * @since 4.7.2
	 *
	 * @param  array      $data All the initial data needed for the summary to be sent.
	 * @throws \Exception       If the email could not be sent.
	 * @return void
	 */
	public function run( $data ) {
		try {
			$this->recipient = $data['recipient'] ?? '';
			$this->frequency = $data['frequency'] ?? '';

			aioseo()->emailReports->mail->send( $this->getRecipient(), $this->getSubject(), $this->getContentHtml(), $this->getHeaders() );
		} catch ( \Exception $e ) {
			throw new \Exception( esc_html( $e->getMessage() ), esc_html( $e->getCode() ) );
		}
	}

	/**
	 * Maybe (re)schedule the summary.
	 *
	 * @since 4.7.2
	 *
	 * @return void
	 */
	public function maybeSchedule() {
		$allowedFrequencies = $this->getAllowedFrequencies();

		// Add at least 6 hours after the day starts.
		$addToStart = HOUR_IN_SECONDS * 6;
		// Add the timezone offset.
		$addToStart -= aioseo()->helpers->getTimeZoneOffset();
		// Add a random time offset to avoid all emails being sent at the same time. 1440 * 3 = 3 days range.
		$addToStart += aioseo()->helpers->generateRandomTimeOffset( aioseo()->helpers->getSiteDomain( true ), 1440 * 3 ) * MINUTE_IN_SECONDS;

		foreach ( $allowedFrequencies as $frequency => $data ) {
			aioseo()->actionScheduler->scheduleRecurrent( $this->actionHook, $data['start'] + $addToStart, $data['interval'], compact( 'frequency' ) );
		}
	}

	/**
	 * Get one or more valid recipients.
	 *
	 * @since 4.7.2
	 *
	 * @throws \Exception If no valid recipient was set for the email.
	 * @return string     The valid recipients.
	 */
	private function getRecipient() {
		$recipients = array_map( 'trim', explode( ',', $this->recipient ) );
		$recipients = array_filter( $recipients, 'is_email' );

		if ( empty( $recipients ) ) {
			throw new \Exception( 'No valid recipient was set for the email.' ); // Not shown to the user.
		}

		return implode( ',', $recipients );
	}

	/**
	 * Get email subject.
	 *
	 * @since 4.7.2
	 *
	 * @return string The email subject.
	 */
	private function getSubject() {
		// Translators: 1 - Date range.
		$out = esc_html__( 'Your SEO Performance Report for %1$s', 'all-in-one-seo-pack' );

		$dateRange = $this->getDateRange();
		$suffix    = date_i18n( 'F', $dateRange['endDateRaw'] );
		if ( 'weekly' === $this->frequency ) {
			$suffix = $dateRange['range'];
		}

		return sprintf( $out, $suffix );
	}

	/**
	 * Get content html.
	 *
	 * @since 4.7.2
	 *
	 * @return string The email content.
	 */
	private function getContentHtml() { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$dateRange        = $this->getDateRange();
		$content          = new Content( $dateRange );
		$upsell           = [
			'search-statistics' => []
		];
		$preHeader        = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			esc_html__( 'Dive into your top-performing pages with %1$s and uncover growth opportunities.', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_SHORT_NAME
		);
		$iconCalendar     = 'weekly' === $this->frequency
			? 'icon-calendar-weekly'
			: 'icon-calendar-monthly';
		$heading          = 'weekly' === $this->frequency
			? esc_html__( 'Your Weekly SEO Email Summary', 'all-in-one-seo-pack' )
			: esc_html__( 'Your Monthly SEO Email Summary', 'all-in-one-seo-pack' );
		$subheading       = 'weekly' === $this->frequency
			? esc_html__( 'Let\'s take a look at your SEO updates and content progress this week.', 'all-in-one-seo-pack' )
			: esc_html__( 'Let\'s take a look at your SEO updates and content progress this month.', 'all-in-one-seo-pack' );
		$statisticsReport = [
			'posts'      => [],
			'keywords'   => [],
			'milestones' => [],
			'cta'        => [
				'text' => esc_html__( 'See All SEO Statistics', 'all-in-one-seo-pack' ),
				'url'  => $content->searchStatisticsUrl
			],
		];

		if ( ! $content->allowSearchStatistics() ) {
			$upsell['search-statistics'] = [
				'cta' => [
					'text' => esc_html__( 'Unlock Search Statistics', 'all-in-one-seo-pack' ),
					'url'  => $content->searchStatisticsUrl,
				],
			];
		}

		if ( ! $upsell['search-statistics'] ) {
			$subheading = 'weekly' === $this->frequency
				? esc_html__( 'Let\'s take a look at how your site has performed in search results this week.', 'all-in-one-seo-pack' )
				: esc_html__( 'Let\'s take a look at how your site has performed in search results this month.', 'all-in-one-seo-pack' );

			$statisticsReport['posts']      = $content->getPostsStatistics();
			$statisticsReport['keywords']   = $content->getKeywords();
			$statisticsReport['milestones'] = $content->getMilestones();
		}

		$mktUrl = trailingslashit( AIOSEO_MARKETING_URL );
		$medium = 'email-report-summary';

		$posts     = $content->getAioPosts();
		$resources = [
			'posts' => array_map( function ( $item ) use ( $medium, $content ) {
				return array_merge( $item, [
					'url'   => aioseo()->helpers->utmUrl( $item['url'], $medium ),
					'image' => [
						'url' => ! empty( $item['image']['sizes']['medium']['source_url'] )
							? $item['image']['sizes']['medium']['source_url']
							: $content->featuredImagePlaceholder
					]
				] );
			}, $content->getResources() ),
			'cta'   => [
				'text' => esc_html__( 'See All Resources', 'all-in-one-seo-pack' ),
				'url'  => aioseo()->helpers->utmUrl( 'https://aioseo.com/blog/', $medium ),
			],
		];
		$links     = [
			'disable'        => admin_url( 'admin.php?page=aioseo-settings&aioseo-scroll=aioseo-email-summary-row&aioseo-highlight=aioseo-email-summary-row&aioseo-tab=advanced' ),
			'update'         => admin_url( 'update-core.php' ),
			'marketing-site' => aioseo()->helpers->utmUrl( $mktUrl, $medium ),
			'facebook'       => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/facebook', $medium ),
			'linkedin'       => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/linkedin', $medium ),
			'youtube'        => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/youtube', $medium ),
			'twitter'        => aioseo()->helpers->utmUrl( $mktUrl . 'plugin/twitter', $medium ),
		];

		ob_start();
		require AIOSEO_DIR . '/app/Common/Views/report/summary.php';

		return ob_get_clean();
	}

	/**
	 * Get email headers.
	 *
	 * @since 4.7.2
	 *
	 * @return array The email headers.
	 */
	private function getHeaders() {
		return [ 'Content-Type: text/html; charset=UTF-8' ];
	}

	/**
	 * Get all allowed frequencies.
	 *
	 * @since 4.7.2
	 *
	 * @return array The email allowed frequencies.
	 */
	private function getAllowedFrequencies() {
		$time           = time();
		$secondsTillNow = $time - strtotime( 'today' );

		return [
			'weekly'  => [
				'interval' => WEEK_IN_SECONDS,
				'start'    => strtotime( 'next Monday' ) - $time
			],
			'monthly' => [
				'interval' => MONTH_IN_SECONDS,
				'start'    => ( strtotime( 'first day of next month' ) + ( DAY_IN_SECONDS * 2 ) - $secondsTillNow ) - $time
			]
		];
	}

	/**
	 * Retrieves the date range data based on the frequency.
	 *
	 * @since 4.7.3
	 *
	 * @return array The date range data.
	 */
	private function getDateRange() {
		$dateFormat = get_option( 'date_format' );

		// If frequency is 'monthly'.
		$endDateRaw   = strtotime( 'last day of last month' );
		$startDateRaw = strtotime( 'first day of last month' );

		// If frequency is 'weekly'.
		if ( 'weekly' === $this->frequency ) {
			$endDateRaw   = strtotime( 'last Saturday' );
			$startDateRaw = strtotime( 'last Sunday', $endDateRaw );
		}

		$endDate   = date_i18n( $dateFormat, $endDateRaw );
		$startDate = date_i18n( $dateFormat, $startDateRaw );

		return [
			'endDate'      => $endDate,
			'endDateRaw'   => $endDateRaw,
			'startDate'    => $startDate,
			'startDateRaw' => $startDateRaw,
			'range'        => "$startDate - $endDate",
		];
	}
}