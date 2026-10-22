<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Tools\RobotsTxt;

/**
 * Service for robots.txt access (rendered output) and AIOSEO's custom rule CRUD.
 *
 * Rules are stored as JSON-encoded strings in `aioseo()->options->tools->robots->rules`.
 * Each rule is `{ userAgent, directive, fieldValue }`. We expose them via stable hash IDs
 * so update/delete don't shift around when other rules change.
 *
 * Rules that Search Appearance writes (Unwanted Bots, Crawl Cleanup) share the same array but are
 * read-only; they are never exposed or changed here, only through the settings that own them.
 *
 * @internal Not a public extension surface.
 *
 * @since 4.9.8
 */
class RobotsService {
	/**
	 * Allowed directives for AIOSEO custom rules.
	 *
	 * @since   4.9.8
	 * @version 5.0.4 Sourced from {@see \AIOSEO\Plugin\Common\Tools\RobotsTxt::RULE_DIRECTIVES}.
	 *
	 * @var string[]
	 */
	const ALLOWED_DIRECTIVES = RobotsTxt::RULE_DIRECTIVES;

	/**
	 * The longest Clean-param value Yandex accepts.
	 *
	 * @since 5.0.4
	 *
	 * @var int
	 */
	const MAX_CLEAN_PARAM_LENGTH = 500;

	/**
	 * Returns the active robots.txt content that AIOSEO is serving (custom rules + WordPress defaults).
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getOutput() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read AIOSEO tools settings.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		return [
			'content' => (string) aioseo()->robotsTxt->buildRules( aioseo()->robotsTxt->getDefaultRobotsTxtContent() )
		];
	}

	/**
	 * Lists AIOSEO's custom robots.txt rules.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 List only the addressable rules; {@see mapAddressableRules()}.
	 *
	 * @return array|\WP_Error
	 */
	public function listRules() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		return [
			'rules' => array_column( $this->mapAddressableRules( $this->getRawRules() ), 'rule' )
		];
	}

	/**
	 * Adds a new custom robots.txt rule.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $rule Accepted keys: user_agent (string), directive ("allow"|"disallow"), field_value (string).
	 * @return array|\WP_Error
	 */
	public function addRule( $rule ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to manage robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$validated = $this->validateRule( $rule );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$rules = $this->getRawRules();

		// Skip duplicate (same userAgent + directive + fieldValue).
		foreach ( $rules as $existing ) {
			$existingRule = json_decode( $existing, true );
			if ( ! is_array( $existingRule ) ) {
				continue;
			}
			unset( $existingRule['id'] );
			if ( $existingRule === $validated ) {
				return new \WP_Error( 'rule_exists', __( 'A rule with the same user agent, directive, and value already exists.', 'all-in-one-seo-pack' ), [ 'status' => 409 ] );
			}
		}

		$validated['id'] = $this->generateRuleId();
		$encoded         = $this->encodeRuleForStorage( $validated );
		if ( is_wp_error( $encoded ) ) {
			return $encoded;
		}

		$rules[] = $encoded;
		$this->saveRawRules( $rules );

		return [ 'rule' => $this->decodeRule( $encoded ) ];
	}

	/**
	 * Updates an existing custom robots.txt rule, addressed by its hash ID.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Update every entry behind the ID; {@see findAddressableRule()}.
	 * @version 5.0.4 Read-only rules are rejected; {@see readOnlyRuleError()}.
	 *
	 * @param  string $id   The rule hash ID (from listRules).
	 * @param  array  $rule Accepted keys: user_agent, directive, field_value. Only present keys are updated.
	 * @return array|\WP_Error
	 */
	public function updateRule( $id, $rule ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to manage robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$id     = (string) $id;
		$rules  = $this->getRawRules();
		$target = $this->findAddressableRule( $rules, $id );
		if ( ! $target ) {
			return $this->isReadOnlyRuleId( $rules, $id )
				? $this->readOnlyRuleError()
				: new \WP_Error( 'rule_not_found', __( 'Robots rule not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		$current = json_decode( $rules[ $target['indexes'][0] ], true );
		$merged  = is_array( $current ) ? $current : [];

		// Raw values: validateRule() sanitizes them, after checking for control characters.
		if ( isset( $rule['user_agent'] ) ) {
			$merged['userAgent'] = (string) $rule['user_agent'];
		}
		if ( isset( $rule['directive'] ) ) {
			$merged['directive'] = (string) $rule['directive'];
		}
		if ( isset( $rule['field_value'] ) ) {
			$merged['fieldValue'] = (string) $rule['field_value'];
		}

		$validated = $this->validateRule( [
			'user_agent'  => $merged['userAgent'] ?? '',
			'directive'   => $merged['directive'] ?? '',
			'field_value' => $merged['fieldValue'] ?? ''
		] );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$validated['id'] = $id;
		$encoded         = $this->encodeRuleForStorage( $validated );
		if ( is_wp_error( $encoded ) ) {
			return $encoded;
		}

		// Rewrite every entry the ID resolves to — the rest would stay stored and served under the old value.
		foreach ( $target['indexes'] as $index ) {
			$rules[ $index ] = $encoded;
		}

		$this->saveRawRules( $rules );

		return [ 'rule' => $this->decodeRule( $encoded ) ];
	}

	/**
	 * Deletes a custom robots.txt rule by hash ID.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Delete every entry behind the ID; {@see findAddressableRule()}.
	 * @version 5.0.4 Read-only rules are rejected; {@see readOnlyRuleError()}.
	 *
	 * @param  string $id The rule hash ID.
	 * @return array|\WP_Error
	 */
	public function deleteRule( $id ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to manage robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$id     = (string) $id;
		$rules  = $this->getRawRules();
		$target = $this->findAddressableRule( $rules, $id );
		if ( ! $target ) {
			return $this->isReadOnlyRuleId( $rules, $id )
				? $this->readOnlyRuleError()
				: new \WP_Error( 'rule_not_found', __( 'Robots rule not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		// Drop every entry the ID resolves to — a rule stored more than once keeps serving otherwise.
		$this->saveRawRules( array_diff_key( $rules, array_flip( $target['indexes'] ) ) );

		return [ 'deleted' => true ];
	}

	/**
	 * Loads the raw rules array from options.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Normalize entries via {@see \AIOSEO\Plugin\Common\Tools\RobotsTxt::normalizeRules()}.
	 *
	 * @return array
	 */
	protected function getRawRules() {
		// Normalize at the single boundary every CRUD method reads through.
		return aioseo()->robotsTxt->normalizeRules( aioseo()->options->tools->robots->rules );
	}

	/**
	 * Saves the raw rules array back to options.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 De-duplicate via {@see \AIOSEO\Plugin\Common\Tools\RobotsTxt::uniqueRules()}.
	 *
	 * @param  array $rules The JSON-encoded rule strings to persist.
	 * @return void
	 */
	protected function saveRawRules( $rules ) {
		// Not array_unique(): the same rule can be stored under two encodings, which only decoded comparison collapses.
		aioseo()->options->tools->robots->rules = aioseo()->robotsTxt->uniqueRules( $rules );
	}

	/**
	 * Decodes a stored rule into the agent-facing shape with a stable hash ID.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Cast only scalar field values; {@see castRuleValue()}.
	 *
	 * @param  string $encoded The JSON-encoded rule string.
	 * @return array
	 */
	protected function decodeRule( $encoded ) {
		$rule = aioseo()->robotsTxt->decodeStoredRule( $encoded );
		$rule = is_array( $rule ) ? $rule : [];

		$userAgent  = $this->castRuleValue( $rule, 'userAgent' );
		$directive  = $this->castRuleValue( $rule, 'directive' );
		$fieldValue = $this->castRuleValue( $rule, 'fieldValue' );
		$id         = '' !== $this->castRuleValue( $rule, 'id' )
			? $this->castRuleValue( $rule, 'id' )
			: sha1( $userAgent . '|' . $directive . '|' . $fieldValue );

		return [
			'id'          => $id,
			'user_agent'  => $userAgent,
			'directive'   => $directive,
			'field_value' => $fieldValue
		];
	}

	/**
	 * Casts a decoded rule field to the string the agent-facing shape exposes.
	 *
	 * NOTE: a malformed entry can hold an array where a value belongs; a plain cast warns on every read.
	 *
	 * @since 5.0.2
	 *
	 * @param  array  $rule The decoded rule.
	 * @param  string $key  The field to read.
	 * @return string       The field value, or an empty string if it isn't a usable value.
	 */
	protected function castRuleValue( $rule, $key ) {
		return isset( $rule[ $key ] ) && is_scalar( $rule[ $key ] ) ? (string) $rule[ $key ] : '';
	}

	/**
	 * Encodes a validated rule for storage.
	 *
	 * @since 5.0.2
	 *
	 * @param  array            $rule The validated rule.
	 * @return string|\WP_Error       The canonical JSON string, or an error if it can't be encoded.
	 */
	protected function encodeRuleForStorage( $rule ) {
		$encoded = aioseo()->robotsTxt->encodeRule( $rule );
		if ( ! is_string( $encoded ) ) {
			return new \WP_Error( 'rule_not_encodable', __( 'The robots rule could not be saved.', 'all-in-one-seo-pack' ), [ 'status' => 500 ] );
		}

		return $encoded;
	}

	/**
	 * Maps the raw rules to the rules a caller can address, keyed by hash ID.
	 *
	 * NOTE: several entries can resolve to one ID; all of their indexes belong to that one addressable rule.
	 *
	 * @since   5.0.2
	 * @version 5.0.4 Skip read-only rules; {@see isReadOnlyRule()}.
	 *
	 * @param  array $rules The raw rules array.
	 * @return array        Addressable rules keyed by hash ID, each as [ 'rule' => array, 'indexes' => int[] ].
	 */
	protected function mapAddressableRules( $rules ) {
		$addressable = [];
		foreach ( $rules as $index => $encoded ) {
			if ( $this->isReadOnlyRule( $encoded ) ) {
				continue;
			}

			$rule = $this->decodeRule( $encoded );
			// Blank editor rows and non-rule JSON decode to empty rows that would all share one hash ID.
			if ( '' === $rule['user_agent'] ) {
				continue;
			}

			if ( isset( $addressable[ $rule['id'] ] ) ) {
				$addressable[ $rule['id'] ]['indexes'][] = $index;

				continue;
			}

			$addressable[ $rule['id'] ] = [
				'rule'    => $rule,
				'indexes' => [ $index ]
			];
		}

		return $addressable;
	}

	/**
	 * Whether a stored rule is owned by a Search Appearance setting and shown read-only in the editor.
	 *
	 * NOTE: checks the owner flags too — dropping them would leave the rule unmanaged by
	 * {@see \AIOSEO\Plugin\Common\Tools\RobotsTxt::resetSearchAppearanceRules()}.
	 *
	 * @since 5.0.4
	 *
	 * @param  mixed $encoded The stored rule entry.
	 * @return bool           Whether the rule is read-only.
	 */
	protected function isReadOnlyRule( $encoded ) {
		$rule = aioseo()->robotsTxt->decodeStoredRule( $encoded );
		if ( ! is_array( $rule ) ) {
			return false;
		}

		return ! empty( $rule['readOnly'] ) || ! empty( $rule['bot'] ) || ! empty( $rule['preventCrawling'] );
	}

	/**
	 * Whether a hash ID refers to a read-only rule.
	 *
	 * @since 5.0.4
	 *
	 * @param  array  $rules The raw rules array.
	 * @param  string $id    The hash ID to find.
	 * @return bool          Whether a read-only rule has that ID.
	 */
	protected function isReadOnlyRuleId( $rules, $id ) {
		foreach ( $rules as $encoded ) {
			if ( $this->isReadOnlyRule( $encoded ) && $this->decodeRule( $encoded )['id'] === $id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the error for an attempt to change a read-only rule.
	 *
	 * @since 5.0.4
	 *
	 * @return \WP_Error
	 */
	protected function readOnlyRuleError() {
		return new \WP_Error(
			'rule_read_only',
			__( 'This robots rule is managed by Search Appearance (Crawl Cleanup or Unwanted Bots). Change it there.', 'all-in-one-seo-pack' ),
			[ 'status' => 409 ]
		);
	}

	/**
	 * Generates a stable, opaque ID for a new robots rule.
	 *
	 * @since 4.9.8
	 *
	 * @return string
	 */
	protected function generateRuleId() {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		return sha1( uniqid( 'aioseo_robots_', true ) );
	}

	/**
	 * Validates and normalises a rule payload before persistence.
	 *
	 * NOTE: sanitizes before the emptiness checks. A value that sanitizes to empty must be rejected here,
	 * because a stored rule with an empty user agent is not addressable ({@see mapAddressableRules()}).
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Sanitize the user agent and field value before validating them.
	 * @version 5.0.4 Validate crawl-delay and clean-param values; reject control characters.
	 *
	 * @param  array $rule The raw rule input.
	 * @return array|\WP_Error Normalised rule (userAgent/directive/fieldValue) on success.
	 */
	protected function validateRule( $rule ) {
		$rule = is_array( $rule ) ? $rule : [];

		// Checked before sanitizing: sanitize_text_field() turns an inner line break into a space, merging the injected line into the value.
		if ( isset( $rule['user_agent'] ) && preg_match( '/[\x00-\x1F\x7F]/', trim( (string) $rule['user_agent'] ) ) ) {
			return new \WP_Error( 'invalid_user_agent', __( 'User agent cannot contain line breaks or other control characters.', 'all-in-one-seo-pack' ), [ 'status' => 400 ] ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}
		if ( isset( $rule['field_value'] ) && preg_match( '/[\x00-\x1F\x7F]/', trim( (string) $rule['field_value'] ) ) ) {
			return new \WP_Error( 'invalid_field_value', __( 'Field value cannot contain line breaks or other control characters.', 'all-in-one-seo-pack' ), [ 'status' => 400 ] ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}

		$userAgent  = isset( $rule['user_agent'] ) ? sanitize_text_field( (string) $rule['user_agent'] ) : '';
		$directive  = isset( $rule['directive'] ) ? strtolower( trim( (string) $rule['directive'] ) ) : '';
		$fieldValue = isset( $rule['field_value'] ) ? sanitize_text_field( (string) $rule['field_value'] ) : '';

		if ( '' === $userAgent ) {
			return new \WP_Error( 'invalid_user_agent', __( 'User agent cannot be empty.', 'all-in-one-seo-pack' ), [ 'status' => 400 ] );
		}
		if ( ! in_array( $directive, self::ALLOWED_DIRECTIVES, true ) ) {
			return new \WP_Error(
				'invalid_directive',
				/* translators: %s: comma-separated list of allowed directives. */
				sprintf( __( 'Directive must be one of: %s.', 'all-in-one-seo-pack' ), implode( ', ', self::ALLOWED_DIRECTIVES ) ),
				[ 'status' => 400 ]
			);
		}
		if ( '' === $fieldValue ) {
			return new \WP_Error( 'invalid_field_value', __( 'Field value cannot be empty.', 'all-in-one-seo-pack' ), [ 'status' => 400 ] );
		}

		// Same rules as the robots.txt editor, so a rule added here doesn't show up there as an error.
		// A plain decimal only: is_numeric() also takes exponents like "1e3", which crawlers don't read.
		if (
			'crawl-delay' === $directive &&
			( ! preg_match( '/^\d+(\.\d+)?$/', $fieldValue ) || 1 > (float) $fieldValue )
		) {
			return new \WP_Error( 'invalid_field_value', __( 'Crawl-delay must be a number starting from 1.', 'all-in-one-seo-pack' ), [ 'status' => 400 ] );
		}

		if ( 'clean-param' === $directive ) {
			// Yandex's syntax is `p0[&p1&…&pn] [path]`: non-empty params joined by "&", then at most one path.
			if (
				self::MAX_CLEAN_PARAM_LENGTH < strlen( $fieldValue ) ||
				! preg_match( '#^(?!/)[^&\s]+(&[^&\s]+)*(\s+/\S*)?$#', $fieldValue )
			) {
				return new \WP_Error(
					'invalid_field_value',
					__( 'Clean-param must start with at least one param which is optionally followed by one path.', 'all-in-one-seo-pack' ),
					[ 'status' => 400 ]
				);
			}
		}

		return [
			'userAgent'  => $userAgent,
			'directive'  => $directive,
			'fieldValue' => $fieldValue
		];
	}

	/**
	 * Finds the addressable rule a hash ID refers to.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Renamed from findRuleIndex(); returns the rule with every index behind its ID.
	 *
	 * @param  array  $rules The raw rules array.
	 * @param  string $id    The hash ID to find.
	 * @return array         The addressable rule, or an empty array if no rule has that ID.
	 */
	protected function findAddressableRule( $rules, $id ) {
		$addressable = $this->mapAddressableRules( $rules );

		return isset( $addressable[ $id ] ) ? $addressable[ $id ] : [];
	}
}