<?php
namespace AIOSEO\Plugin\Common\Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

class RobotsTxt {
	/**
	 * Which directives are allowed to be extracted.
	 *
	 * @since 4.4.2
	 *
	 * @var array
	 */
	private $allowedDirectives = [ 'user-agent', 'allow', 'disallow', 'clean-param', 'crawl-delay' ];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'robots_txt', [ $this, 'buildRules' ], 10000 );

		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'init', [ $this, 'checkForPhysicalFiles' ] );
	}

	/**
	 * Build out the robots.txt rules.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $original The original rules to parse.
	 * @return string           The parsed/appended/modified rules.
	 */
	public function buildRules( $original ) {
		// Other plugins might call this too early.
		if ( ! property_exists( aioseo(), 'sitemap' ) ) {
			return $original;
		}

		$originalRules = $this->extractRules( $original );
		$networkRules  = [];

		if ( is_multisite() ) {
			$networkRules = aioseo()->networkOptions->tools->robots->enable ? aioseo()->networkOptions->tools->robots->rules : [];
		}

		if ( ! aioseo()->options->tools->robots->enable ) {
			$ruleset = $this->mergeRules( $originalRules, $this->groupRulesByUserAgent( $networkRules ) );
		} else {
			$ruleset = $this->mergeRules(
				$originalRules,
				$this->mergeRules( $this->groupRulesByUserAgent( $networkRules ), $this->groupRulesByUserAgent( aioseo()->options->tools->robots->rules ) ),
				true
			);
		}

		/**
		 * Any plugin can wrongly modify the robots.txt output by hoking into the `do_robots` action hook,
		 * instead of hooking into the `robots_txt` filter hook.
		 * For the first scenario, to make sure our output doesn't conflict with theirs, a new line is necessary.
		 */
		return $this->stringifyRuleset( $ruleset ) . "\n";
	}

	/**
	 * Merges two rulesets.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  array   $rules1          An array of rules to merge with.
	 * @param  array   $rules2          An array of rules to merge.
	 * @param  boolean $allowOverride   Whether to allow overriding.
	 * @param  boolean $allowDuplicates Whether to allow duplicates.
	 * @return array                    The validated rules.
	 */
	private function mergeRules( $rules1, $rules2, $allowOverride = false, $allowDuplicates = false ) {
		foreach ( $rules2 as $userAgent => $rules ) {
			if ( empty( $userAgent ) ) {
				continue;
			}

			if ( empty( $rules1[ $userAgent ] ) ) {
				$rules1[ $userAgent ] = array_unique( $rules2[ $userAgent ] );

				continue;
			}

			list( $rules1, $rules2 ) = $this->mergeRulesHelper( 'allow', $userAgent, $rules, $rules1, $rules2, $allowDuplicates, $allowOverride );
			list( $rules1, $rules2 ) = $this->mergeRulesHelper( 'disallow', $userAgent, $rules, $rules1, $rules2, $allowDuplicates, $allowOverride );

			$rules1[ $userAgent ] = array_unique( array_merge(
				$rules1[ $userAgent ],
				$rules2[ $userAgent ]
			) );
		}

		return $rules1;
	}

	/**
	 * Helper function for {@see mergeRules()}.
	 *
	 * @since   4.1.2
	 * @version 4.4.2
	 *
	 * @param  string $directive       The directive (allow/disallow).
	 * @param  string $userAgent       The user agent.
	 * @param  array  $rules           The rules.
	 * @param  array  $rules1          The original rules.
	 * @param  array  $rules2          The extra rules.
	 * @param  bool   $allowDuplicates Whether duplicates should be allowed
	 * @param  bool   $allowOverride   Whether the extra rules can override the original ones.
	 * @return array                   The original and extra rules.
	 */
	private function mergeRulesHelper( $directive, $userAgent, $rules, $rules1, $rules2, $allowDuplicates, $allowOverride ) {
		$otherDirective = ( 'allow' === $directive ) ? 'disallow' : 'allow';
		foreach ( $rules as $index1 => $rule ) {
			list( , $ruleValue ) = $this->parseRule( $rule );
			$index2 = array_search( "$otherDirective: $ruleValue", $rules1[ $userAgent ], true );
			if ( false !== $index2 && ! $allowDuplicates ) {
				if ( $allowOverride ) {
					unset( $rules1[ $userAgent ][ $index2 ] );
				} else {
					unset( $rules2[ $userAgent ][ $index1 ] );
				}
			}

			$pattern = str_replace( [ '.', '*', '?', '$' ], [ '\.', '(.*)', '\?', '\$' ], $ruleValue );

			foreach ( $rules1[ $userAgent ] as $rule1 ) {
				$matches = [];
				preg_match( "#^$otherDirective: $pattern$#", $rule1, $matches );
			}

			if ( ! empty( $matches ) && ! $allowDuplicates ) {
				unset( $rules2[ $userAgent ][ $index1 ] );
			}
		}

		return [ $rules1, $rules2 ];
	}

	/**
	 * Parses a rule and extracts the directive and value.
	 *
	 * @since 4.4.2
	 *
	 * @param  string $rule The rule to parse.
	 * @return array        An array containing the parsed directive and value.
	 */
	private function parseRule( $rule ) {
		list( $directive, $value ) = array_map( 'trim', array_pad( explode( ':', $rule, 2 ), 2, '' ) );

		return [ $directive, $value ];
	}

	/**
	 * Stringifies the parsed rules.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  array  $allRules The rules array.
	 * @return string           The stringified rules.
	 */
	private function stringifyRuleset( $allRules ) {
		$robots = [];
		foreach ( $allRules as $userAgent => $rules ) {
			if ( empty( $userAgent ) ) {
				continue;
			}

			$robots[] = "\r\nUser-agent: $userAgent";
			foreach ( $rules as $rule ) {
				list( $directive, $value ) = $this->parseRule( $rule );
				if ( empty( $directive ) || empty( $value ) ) {
					continue;
				}

				$robots[] = sprintf( '%s: %s', ucfirst( $directive ), $value );
			}
		}

		$robots      = implode( "\r\n", $robots );
		$sitemapUrls = $this->getSitemapRules();
		if ( ! empty( $sitemapUrls ) ) {
			$sitemapUrls = implode( "\r\n", $sitemapUrls );
			$robots      .= "\r\n\r\n$sitemapUrls";
		}

		return trim( $robots );
	}

	/**
	 * Get Sitemap URLs excluding the default ones.
	 *
	 * @since 4.1.7
	 *
	 * @return array An array of the Sitemap URLs.
	 */
	private function getSitemapRules() {
		$defaultSitemaps = $this->extractSitemapUrls( aioseo()->robotsTxt->getDefaultRobotsTxtContent() );
		$sitemapRules    = aioseo()->sitemap->helpers->getSitemapUrlsPrefixed();

		return array_diff( $sitemapRules, $defaultSitemaps );
	}

	/**
	 * Parses the rules.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  array $rules An array of rules.
	 * @return array        The rules grouped by user agent.
	 */
	private function groupRulesByUserAgent( $rules ) {
		$groups = [];
		foreach ( $rules as $rule ) {
			$r = json_decode( $rule, true );
			if ( empty( $r['userAgent'] ) || empty( $r['fieldValue'] ) ) {
				continue;
			}

			if ( empty( $groups[ $r['userAgent'] ] ) ) {
				$groups[ $r['userAgent'] ] = [];
			}

			$groups[ $r['userAgent'] ][] = "{$r['directive']}: {$r['fieldValue']}";
		}

		return $groups;
	}

	/**
	 * Extract rules from a string.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  string $lines The lines to extract from.
	 * @return array         An array of extracted rules.
	 */
	public function extractRules( $lines ) {
		$lines              = array_filter( array_map( 'trim', explode( "\n", (string) $lines ) ) );
		$rules              = [];
		$userAgent          = null;
		$prevDirective      = null;
		$prevValue          = null;
		$siblingsUserAgents = [];
		foreach ( $lines as $line ) {
			list( $directive, $value ) = $this->parseRule( $line );
			if ( empty( $directive ) || empty( $value ) ) {
				continue;
			}

			$directive = strtolower( $directive );
			if ( ! in_array( $directive, $this->allowedDirectives, true ) ) {
				continue;
			}

			$value = $this->sanitizeDirectiveValue( $directive, $value );
			if ( ! $value ) {
				continue;
			}

			if ( 'user-agent' === $directive ) {
				if (
					! empty( $prevDirective ) &&
					! empty( $prevValue ) &&
					'user-agent' === $prevDirective
				) {
					$siblingsUserAgents[] = $prevValue;
				}

				$userAgent           = $value;
				$rules[ $userAgent ] = ! empty( $rules[ $userAgent ] ) ? $rules[ $userAgent ] : [];
			} else {
				$rules[ $userAgent ][] = "$directive: $value";
				if ( $siblingsUserAgents ) {
					foreach ( $siblingsUserAgents as $siblingUserAgent ) {
						$rules[ $siblingUserAgent ] = $rules[ $userAgent ];
					}

					$siblingsUserAgents = [];
				}
			}

			$prevDirective = $directive;
			$prevValue     = $value;
		}

		return $rules;
	}

	/**
	 * Extract sitemap URLs from a string.
	 *
	 * @since 4.0.10
	 *
	 * @param  string $lines The lines to extract from.
	 * @return array         An array of sitemap URLs.
	 */
	public function extractSitemapUrls( $lines ) {
		$lines       = array_filter( array_map( 'trim', explode( "\n", (string) $lines ) ) );
		$sitemapUrls = [];
		foreach ( $lines as $line ) {
			$array = array_map( 'trim', explode( 'sitemap:', strtolower( $line ) ) );
			if ( ! empty( $array[1] ) ) {
				$sitemapUrls[] = trim( $line );
			}
		}

		return $sitemapUrls;
	}

	/**
	 * Sanitize the robots.txt rule directive value.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  string $directive The directive.
	 * @param  string $value     The value.
	 * @return string            The directive value.
	 */
	private function sanitizeDirectiveValue( $directive, $value ) {
		// Percent-encoded characters are stripped from our option values, so we decode.
		$value = rawurldecode( trim( $value ) );
		if ( ! $value ) {
			return $value;
		}

		$value = preg_replace( '/[><]/', '', $value );

		if ( 'user-agent' === $directive ) {
			$value = preg_replace( '/[^a-z0-9\-_*,.\s]/i', '', $value );
		}

		if ( 'allow' === $directive || 'disallow' === $directive ) {
			$value = preg_replace( '/^\/+/', '/', $value );
		}

		return $value;
	}

	/**
	 * Check if a physical robots.txt file exists, and if it does add a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function checkForPhysicalFiles() {
		if ( ! $this->hasPhysicalRobotsTxt() ) {
			return;
		}

		$notification = Models\Notification::getNotificationByName( 'robots-physical-file' );
		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'robots-physical-file',
			'title'             => __( 'Physical Robots.txt File Detected', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The plugin short name ("AIOSEO").
				__( '%1$s has detected a physical robots.txt file in the root folder of your WordPress installation. We recommend removing this file as it could cause conflicts with WordPress\' dynamically generated one. %2$s can import this file and delete it, or you can simply delete it.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				AIOSEO_PLUGIN_SHORT_NAME,
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Import and Delete', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://action#tools/import-robots-txt?redirect=aioseo-tools:robots-editor',
			'button2_label'     => __( 'Delete', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#tools/delete-robots-txt?redirect=aioseo-tools:robots-editor',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Import physical robots.txt file.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  bool       $network True if inside WordPress network administration pages.
	 * @throws \Exception          If request fails or file is not readable.
	 * @return boolean             Whether or not the file imported correctly.
	 */
	public function importPhysicalRobotsTxt( $network = false ) {
		try {
			$fs = aioseo()->core->fs;
			if ( ! $fs->isWpfsValid() ) {
				$invalid = true;
			}

			$file = trailingslashit( $fs->fs->abspath() ) . 'robots.txt';
			if (
				isset( $invalid ) ||
				! $fs->isReadable( $file )
			) {
				throw new \Exception( esc_html__( 'There was an error importing the static robots.txt file.', 'all-in-one-seo-pack' ) );
			}

			$lines = trim( (string) $fs->getContents( $file ) );
			if ( $lines ) {
				$this->importRobotsTxtFromText( $lines, $network );
			}

			return true;
		} catch ( \Exception $e ) {
			throw new \Exception( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Import robots.txt from a URL.
	 *
	 * @since 4.4.2
	 *
	 * @param  string     $text    The text to import from.
	 * @param  bool       $network True if inside WordPress network administration pages.
	 * @throws \Exception          If no User-agent is found.
	 * @return boolean             Whether the file imported correctly or not.
	 */
	public function importRobotsTxtFromText( $text, $network ) {
		$ruleset = $this->extractRules( $text );
		if ( ! key( $ruleset ) ) {
			throw new \Exception( esc_html__( 'No User-agent found in the content beginning.', 'all-in-one-seo-pack' ) );
		}

		$options = aioseo()->options;
		if ( $network ) {
			$options = aioseo()->networkOptions;
		}

		$currentRules = $this->groupRulesByUserAgent( $options->tools->robots->rules );
		$ruleset      = $this->mergeRules( $currentRules, $ruleset, false, true );

		$options->tools->robots->rules = aioseo()->robotsTxt->prepareRobotsTxt( $ruleset );

		return true;
	}

	/**
	 * Import robots.txt from a URL.
	 *
	 * @since 4.4.2
	 *
	 * @param  string     $url     The URL to import from.
	 * @param  bool       $network True if inside WordPress network administration pages.
	 * @throws \Exception          If request fails.
	 * @return bool                Whether the import was successful or not.
	 */
	public function importRobotsTxtFromUrl( $url, $network ) {
		$request          = wp_remote_get( $url, [
			'timeout'   => 10,
			'sslverify' => false
		] );
		$robotsTxtContent = wp_remote_retrieve_body( $request );
		if ( ! $robotsTxtContent ) {
			throw new \Exception( esc_html__( 'There was an error importing the robots.txt content from the URL.', 'all-in-one-seo-pack' ) );
		}

		$options = aioseo()->options;
		if ( $network ) {
			$options = aioseo()->networkOptions;
		}

		$newRules     = $this->extractRules( $robotsTxtContent );
		$currentRules = $this->groupRulesByUserAgent( $options->tools->robots->rules );
		$newRules     = $this->mergeRules( $currentRules, $newRules, false, true );

		$options->tools->robots->rules = aioseo()->robotsTxt->prepareRobotsTxt( $newRules );

		return true;
	}

	/**
	 * Deletes the physical robots.txt file.
	 *
	 * @since 4.4.5
	 *
	 * @throws \Exception If the file is not readable, or it can't be deleted.
	 * @return true       True if the file was successfully deleted.
	 */
	public function deletePhysicalRobotsTxt() {
		try {
			$fs = aioseo()->core->fs;
			if (
				! $fs->isWpfsValid() ||
				! $fs->fs->delete( trailingslashit( $fs->fs->abspath() ) . 'robots.txt' )
			) {
				throw new \Exception( __( 'There was an error deleting the physical robots.txt file.', 'all-in-one-seo-pack' ) );
			}

			Models\Notification::deleteNotificationByName( 'robots-physical-file' );

			return true;
		} catch ( \Exception $e ) {
			throw new \Exception( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Prepare robots.txt rules to save.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $allRules Array with the rules.
	 * @return array           The prepared rules array.
	 */
	public function prepareRobotsTxt( $allRules = [] ) {
		$robots = [];
		foreach ( $allRules as $userAgent => $rules ) {
			if ( empty( $userAgent ) ) {
				continue;
			}

			foreach ( $rules as $rule ) {
				list( $directive, $value ) = $this->parseRule( $rule );
				if ( empty( $directive ) || empty( $value ) ) {
					continue;
				}

				if (
					'*' === $userAgent &&
					(
						'allow' === $directive && '/wp-admin/admin-ajax.php' === $value ||
						'disallow' === $directive && '/wp-admin/' === $value
					)
				) {
					continue;
				}

				$robots[] = wp_json_encode( [
					'userAgent'  => $userAgent,
					'directive'  => $directive,
					'fieldValue' => $value
				] );
			}
		}

		return $robots;
	}

	/**
	 * Checks if a physical robots.txt file exists.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if it does, false if not.
	 */
	public function hasPhysicalRobotsTxt() {
		$fs = aioseo()->core->fs;
		if ( ! $fs->isWpfsValid() ) {
			return false;
		}

		$accessType = get_filesystem_method();
		if ( 'direct' === $accessType ) {
			$file = trailingslashit( $fs->fs->abspath() ) . 'robots.txt';

			return $fs->exists( $file );
		}

		return false;
	}

	/**
	 * Get the default Robots.txt lines (excluding our own).
	 *
	 * @since   4.1.7
	 * @version 4.4.2
	 *
	 * @return string The robots.txt content rules (excluding our own).
	 */
	public function getDefaultRobotsTxtContent() {
		// First, we need to remove our filter, so that it doesn't run unintentionally.
		remove_filter( 'robots_txt', [ $this, 'buildRules' ], 10000 );

		ob_start();
		do_robots();
		if ( is_admin() ) {
			header( 'Content-Type: text/html; charset=utf-8' );
		}
		$rules = strval( ob_get_clean() );

		// Add the filter back.
		add_filter( 'robots_txt', [ $this, 'buildRules' ], 10000 );

		return $rules;
	}

	/**
	 * A check to see if the rewrite rules are set.
	 * This isn't perfect, but it will help us know in most cases.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the rewrite rules are set or not.
	 */
	public function rewriteRulesExist() {
		// If we have a physical file, it's almost impossible to tell if the rewrite rules are set.
		// The only scenario is if we still get a 404.
		$response = wp_remote_get( aioseo()->helpers->getSiteUrl() . '/robots.txt' );
		if ( 299 < wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return true;
	}
}