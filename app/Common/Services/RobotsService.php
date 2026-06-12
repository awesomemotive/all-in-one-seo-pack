<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service for robots.txt access (rendered output) and AIOSEO's custom rule CRUD.
 *
 * Rules are stored as JSON-encoded strings in `aioseo()->options->tools->robots->rules`.
 * Each rule is `{ userAgent, directive, fieldValue }`. We expose them via stable hash IDs
 * so update/delete don't shift around when other rules change.
 *
 * @internal Not a public extension surface.
 *
 * @since 4.9.8
 */
class RobotsService {
	/**
	 * Allowed directives for AIOSEO custom rules.
	 *
	 * @since 4.9.8
	 *
	 * @var string[]
	 */
	const ALLOWED_DIRECTIVES = [ 'allow', 'disallow' ];

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
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function listRules() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		return [
			'rules' => array_values( array_map( [ $this, 'decodeRule' ], $this->getRawRules() ) )
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
		$encoded         = wp_json_encode( $validated );
		$rules[]         = $encoded;
		$this->saveRawRules( $rules );

		return [ 'rule' => $this->decodeRule( $encoded ) ];
	}

	/**
	 * Updates an existing custom robots.txt rule, addressed by its hash ID.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $id   The rule hash ID (from listRules).
	 * @param  array  $rule Accepted keys: user_agent, directive, field_value. Only present keys are updated.
	 * @return array|\WP_Error
	 */
	public function updateRule( $id, $rule ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to manage robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$id    = (string) $id;
		$rules = $this->getRawRules();
		$index = $this->findRuleIndex( $rules, $id );
		if ( -1 === $index ) {
			return new \WP_Error( 'rule_not_found', __( 'Robots rule not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		$current = json_decode( $rules[ $index ], true );
		$merged  = is_array( $current ) ? $current : [];

		if ( isset( $rule['user_agent'] ) ) {
			$merged['userAgent'] = sanitize_text_field( (string) $rule['user_agent'] );
		}
		if ( isset( $rule['directive'] ) ) {
			$merged['directive'] = strtolower( sanitize_text_field( (string) $rule['directive'] ) );
		}
		if ( isset( $rule['field_value'] ) ) {
			$merged['fieldValue'] = sanitize_text_field( (string) $rule['field_value'] );
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
		$rules[ $index ] = wp_json_encode( $validated );
		$this->saveRawRules( array_values( $rules ) );

		return [ 'rule' => $this->decodeRule( $rules[ $index ] ) ];
	}

	/**
	 * Deletes a custom robots.txt rule by hash ID.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $id The rule hash ID.
	 * @return array|\WP_Error
	 */
	public function deleteRule( $id ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_tools_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to manage robots rules.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$id    = (string) $id;
		$rules = $this->getRawRules();
		$index = $this->findRuleIndex( $rules, $id );
		if ( -1 === $index ) {
			return new \WP_Error( 'rule_not_found', __( 'Robots rule not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		array_splice( $rules, $index, 1 );
		$this->saveRawRules( $rules );

		return [ 'deleted' => true ];
	}

	/**
	 * Loads the raw rules array from options.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function getRawRules() {
		$rules = aioseo()->options->tools->robots->rules;

		return is_array( $rules ) ? $rules : [];
	}

	/**
	 * Saves the raw rules array back to options.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $rules The JSON-encoded rule strings to persist.
	 * @return void
	 */
	protected function saveRawRules( $rules ) {
		aioseo()->options->tools->robots->rules = array_values( array_unique( $rules ) );
	}

	/**
	 * Decodes a stored rule into the agent-facing shape with a stable hash ID.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $encoded The JSON-encoded rule string.
	 * @return array
	 */
	protected function decodeRule( $encoded ) {
		$rule = json_decode( (string) $encoded, true );
		$rule = is_array( $rule ) ? $rule : [];

		$userAgent  = isset( $rule['userAgent'] ) ? (string) $rule['userAgent'] : '';
		$directive  = isset( $rule['directive'] ) ? (string) $rule['directive'] : '';
		$fieldValue = isset( $rule['fieldValue'] ) ? (string) $rule['fieldValue'] : '';
		$id         = isset( $rule['id'] ) && '' !== $rule['id']
			? (string) $rule['id']
			: sha1( $userAgent . '|' . $directive . '|' . $fieldValue );

		return [
			'id'          => $id,
			'user_agent'  => $userAgent,
			'directive'   => $directive,
			'field_value' => $fieldValue
		];
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
	 * @since 4.9.8
	 *
	 * @param  array $rule The raw rule input.
	 * @return array|\WP_Error Normalised rule (userAgent/directive/fieldValue) on success.
	 */
	protected function validateRule( $rule ) {
		$rule = is_array( $rule ) ? $rule : [];

		$userAgent  = isset( $rule['user_agent'] ) ? trim( (string) $rule['user_agent'] ) : '';
		$directive  = isset( $rule['directive'] ) ? strtolower( trim( (string) $rule['directive'] ) ) : '';
		$fieldValue = isset( $rule['field_value'] ) ? trim( (string) $rule['field_value'] ) : '';

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

		return [
			'userAgent'  => sanitize_text_field( $userAgent ),
			'directive'  => $directive,
			'fieldValue' => sanitize_text_field( $fieldValue )
		];
	}

	/**
	 * Finds the index of a rule by its hash ID.
	 *
	 * @since 4.9.8
	 *
	 * @param  array  $rules The raw rules array.
	 * @param  string $id    The hash ID to find.
	 * @return int Index, or -1 if not found.
	 */
	protected function findRuleIndex( $rules, $id ) {
		foreach ( $rules as $index => $encoded ) {
			$decoded = $this->decodeRule( $encoded );
			if ( $decoded['id'] === $id ) {
				return $index;
			}
		}

		return -1;
	}
}