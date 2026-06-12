<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service for read-only access to the AIOSEO options/settings tree.
 *
 * Writes are deliberately out of scope — site-wide config writes via abilities
 * are too dangerous to expose to agents in P0. Read-only is enough for "what's
 * AIOSEO configured to do on this site?" prompts.
 *
 * @internal Not a public extension surface.
 *
 * @since 4.9.8
 */
class SettingsService {
	/**
	 * Returns the full AIOSEO settings tree, sanitised for agent consumption.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function get() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read AIOSEO settings.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$options = aioseo()->options->noConflict()->all();
		$options = is_array( $options ) ? $options : [];

		return [
			'settings' => $this->redactSecrets( $options )
		];
	}

	/**
	 * Redacts secret-bearing fields from the settings tree before it is handed to an agent/LLM.
	 *
	 * This ability is read-only context for "what is AIOSEO configured to do?" prompts — it must
	 * never surface verification tokens, third-party API keys, or admin-injected markup that may
	 * carry pasted credentials. Each sensitive leaf is replaced with a marker when populated, so the
	 * agent can still tell that a field is configured without receiving its value.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $options The full options tree.
	 * @return array          The same tree with sensitive values redacted.
	 */
	protected function redactSecrets( $options ) {
		// Dot-paths of secret-bearing leaves: webmaster verification codes, the Google Maps API key,
		// and the RSS before/after blocks (arbitrary admin-injected markup that may contain tokens).
		$paths = [
			[ 'webmasterTools', 'google' ],
			[ 'webmasterTools', 'bing' ],
			[ 'webmasterTools', 'yandex' ],
			[ 'webmasterTools', 'baidu' ],
			[ 'webmasterTools', 'pinterest' ],
			[ 'webmasterTools', 'norton' ],
			[ 'webmasterTools', 'miscellaneousVerification' ],
			[ 'rssContent', 'before' ],
			[ 'rssContent', 'after' ],
			[ 'localBusiness', 'maps', 'apiKey' ]
		];

		foreach ( $paths as $path ) {
			$options = $this->redactPath( $options, $path );
		}

		return $options;
	}

	/**
	 * Replaces a single nested leaf with a redaction marker when it holds a non-empty scalar.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $options The options tree (or subtree).
	 * @param  array $path    The remaining key path to the leaf.
	 * @return array          The tree with the targeted leaf redacted.
	 */
	protected function redactPath( $options, $path ) {
		if ( ! is_array( $options ) || empty( $path ) ) {
			return $options;
		}

		$key = array_shift( $path );
		if ( ! array_key_exists( $key, $options ) ) {
			return $options;
		}

		if ( empty( $path ) ) {
			if ( is_scalar( $options[ $key ] ) && '' !== (string) $options[ $key ] ) {
				$options[ $key ] = '[redacted]';
			}

			return $options;
		}

		$options[ $key ] = $this->redactPath( $options[ $key ], $path );

		return $options;
	}
}