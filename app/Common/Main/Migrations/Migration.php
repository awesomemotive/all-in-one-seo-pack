<?php
namespace AIOSEO\Plugin\Common\Main\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contract for a discrete, individually-logged migration.
 *
 * Migrations registered with the {@see MigrationRunner} are tracked by name in
 * the migration log option. The runner uses {@see self::verify()} as the source
 * of truth for completion — {@see self::up()}'s exit status is not trusted,
 * because that's exactly the failure mode that left 4.9.7.1 sites half-applied
 * (the version flag advanced even when the work hadn't actually landed).
 *
 * @since 4.9.7.2
 */
interface Migration {
	/**
	 * Stable identifier. Becomes the key in the migration log option.
	 *
	 * IMPORTANT: never rename after the migration has shipped — the runner
	 * keys log entries off this string. A rename would cause the migration
	 * to re-run on every site that already had it.
	 *
	 * Convention: snake_case verb_noun, no version suffix.
	 *
	 * @since 4.9.7.2
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Release this migration was introduced in. Diagnostic only.
	 *
	 * @since 4.9.7.2
	 *
	 * @return string
	 */
	public function version();

	/**
	 * Perform the migration. Throw on hard failure — the runner catches and
	 * logs as status = 0 so the migration is retried on the next request.
	 *
	 * Must be idempotent: the runner may invoke up() multiple times across
	 * retries (e.g. when verify() returned false after a previous up()).
	 *
	 * @since 4.9.7.2
	 *
	 * @return void
	 */
	public function up();

	/**
	 * Post-condition check. Returns true when the migration's intended state
	 * is in place. Called BEFORE up() to short-circuit when already applied,
	 * and AFTER up() to confirm success.
	 *
	 * @since 4.9.7.2
	 *
	 * @return bool
	 */
	public function verify();
}