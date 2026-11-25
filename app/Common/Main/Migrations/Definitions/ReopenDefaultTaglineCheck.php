<?php
namespace AIOSEO\Plugin\Common\Main\Migrations\Definitions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main\Migrations\Migration;
use AIOSEO\Plugin\Common\Main\Migrations\MigrationLog;

/**
 * Re-opens the "Set a Site Title and Tagline" checklist task on sites that still use the default tagline.
 *
 * NOTE: Older releases stored the task as complete for the default tagline, and stored tasks are never re-checked.
 *
 * @since 5.0.6
 */
class ReopenDefaultTaglineCheck implements Migration {
	/**
	 * The checklist task name.
	 *
	 * @since 5.0.6
	 *
	 * @var string
	 */
	private $checkName = 'setSiteTitleAndTagline';

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function name() {
		return 'reopen_default_tagline_check';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function version() {
		return '5.0.6';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function up() {
		$completedChecks = aioseo()->internalOptions->internal->seoChecklist->completed;

		aioseo()->internalOptions->internal->seoChecklist->completed = array_values( array_diff( $completedChecks, [ $this->checkName ] ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * NOTE: Passes once logged, so a later manual completion isn't undone on every release.
	 *
	 * @since 5.0.6
	 */
	public function verify() {
		$log = ( new MigrationLog() )->read();
		if ( 1 === ( $log[ $this->name() ]['status'] ?? 0 ) ) {
			return true;
		}

		if ( ! in_array( $this->checkName, aioseo()->internalOptions->internal->seoChecklist->completed, true ) ) {
			return true;
		}

		return ! aioseo()->helpers->isDefaultTagline( get_option( 'blogdescription' ) );
	}
}