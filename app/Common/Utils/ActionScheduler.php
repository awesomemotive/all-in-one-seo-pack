<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all Action Scheduler related tasks.
 *
 * @since 4.0.0
 */
class ActionScheduler {
	/**
	 * The Action Scheduler group.
	 *
	 * @since   4.1.5
	 * @version 4.2.7
	 *
	 * @var string
	 */
	private $actionSchedulerGroup = 'aioseo';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'action_scheduler_after_execute', [ $this, 'cleanup' ], 1000, 2 );

		// Note: \ActionScheduler is first loaded on `plugins_loaded` action hook.
		add_action( 'plugins_loaded', [ $this, 'maybeRecreateTables' ] );
	}

	/**
	 * Maybe register the `{$table_prefix}_actionscheduler_{$suffix}` tables with WordPress and create them if needed.
	 * Hooked into `plugins_loaded` action hook.
	 *
	 * @since 4.2.7
	 *
	 * @return void
	 */
	public function maybeRecreateTables() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! apply_filters( 'action_scheduler_enable_recreate_data_store', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			return;
		}

		if (
			! class_exists( 'ActionScheduler' ) ||
			! class_exists( 'ActionScheduler_HybridStore' ) ||
			! class_exists( 'ActionScheduler_StoreSchema' ) ||
			! class_exists( 'ActionScheduler_LoggerSchema' )
		) {
			return;
		}

		$store = \ActionScheduler::store();

		if ( ! is_a( $store, 'ActionScheduler_HybridStore' ) ) {
			$store = new \ActionScheduler_HybridStore();
		}

		$tableList = [
			'actionscheduler_actions',
			'actionscheduler_logs',
			'actionscheduler_groups',
			'actionscheduler_claims',
		];

		foreach ( $tableList as $tableName ) {
			if ( ! aioseo()->core->db->tableExists( $tableName ) ) {
				add_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10, 2 );

				$storeSchema  = new \ActionScheduler_StoreSchema();
				$loggerSchema = new \ActionScheduler_LoggerSchema();
				$storeSchema->register_tables( true );
				$loggerSchema->register_tables( true );

				remove_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ] );

				break;
			}
		}
	}

	/**
	 * Cleans up the Action Scheduler tables after one of our actions completes.
	 * Hooked into `action_scheduler_after_execute` action hook.
	 *
	 * @since 4.0.10
	 *
	 * @param  int                     $actionId The action ID processed.
	 * @param  \ActionScheduler_Action $action   Class instance.
	 * @return void
	 */
	public function cleanup( $actionId, $action = null ) {
		if (
			// Bail if this isn't one of our actions or if we're in a dev environment.
			'aioseo' !== $action->get_group() ||
			( defined( 'WP_ENVIRONMENT_TYPE' ) && 'development' === WP_ENVIRONMENT_TYPE ) ||
			// Bail if the tables don't exist.
			! aioseo()->core->db->tableExists( 'actionscheduler_actions' ) ||
			! aioseo()->core->db->tableExists( 'actionscheduler_groups' ) ||
			// Bail if it hasn't been long enough since the last cleanup.
			aioseo()->core->cache->get( 'action_scheduler_log_cleanup' )
		) {
			return;
		}

		$prefix = aioseo()->core->db->db->prefix;

		// Clean up logs associated with entries in the actions table.
		aioseo()->core->db->execute(
			"DELETE al FROM {$prefix}actionscheduler_logs as al
			JOIN {$prefix}actionscheduler_actions as aa on `aa`.`action_id` = `al`.`action_id`
			LEFT JOIN {$prefix}actionscheduler_groups as ag on `ag`.`group_id` = `aa`.`group_id`
			WHERE (
				(`ag`.`slug` = '{$this->actionSchedulerGroup}' AND `aa`.`status` IN ('complete', 'failed', 'canceled'))
				OR
				(`aa`.`hook` LIKE 'aioseo_%' AND `aa`.`group_id` = 0 AND `aa`.`status` IN ('complete', 'failed', 'canceled'))
			);"
		);

		// Clean up actions.
		aioseo()->core->db->execute(
			"DELETE aa FROM {$prefix}actionscheduler_actions as aa
			LEFT JOIN {$prefix}actionscheduler_groups as ag on `ag`.`group_id` = `aa`.`group_id`
			WHERE (
				(`ag`.`slug` = '{$this->actionSchedulerGroup}' AND `aa`.`status` IN ('complete', 'failed', 'canceled'))
				OR
				(`aa`.`hook` LIKE 'aioseo_%' AND `aa`.`group_id` = 0 AND `aa`.`status` IN ('complete', 'failed', 'canceled'))
			);"
		);

		// Set a transient to prevent this from running again for a while.
		aioseo()->core->cache->update( 'action_scheduler_log_cleanup', true, DAY_IN_SECONDS );
	}

	/**
	 * Schedules a single action at a specific time in the future.
	 *
	 * @since   4.0.13
	 * @version 4.2.7
	 *
	 * @param  string  $actionName    The action name.
	 * @param  int     $time          The time to add to the current time.
	 * @param  array   $args          Args passed down to the action.
	 * @param  bool    $forceSchedule Whether we should schedule a new action regardless of whether one is already set.
	 * @return boolean                Whether the action was scheduled.
	 */
	public function scheduleSingle( $actionName, $time = 0, $args = [], $forceSchedule = false ) {
		$lockKey = $this->getLockKey( $actionName, $args );

		// Skip if another request is currently scheduling this action.
		if ( get_transient( $lockKey ) ) {
			return false;
		}

		// Set lock for 5 seconds.
		set_transient( $lockKey, 1, 5 );

		try {
			if ( $forceSchedule || ! $this->isScheduled( $actionName, $args ) ) {
				as_schedule_single_action( time() + $time, $actionName, $args, $this->actionSchedulerGroup );
				delete_transient( $lockKey );

				return true;
			}
		} catch ( \RuntimeException $e ) {
			// Nothing needs to happen.
		}

		delete_transient( $lockKey );

		return false;
	}

	/**
	 * Checks if a given action is already scheduled.
	 *
	 * @since   4.0.13
	 * @version 4.2.7
	 * @version 4.9.4.2 Refactored to check all arguments.
	 * @version 4.9.4.2  Use as_has_scheduled_action() for improved performance.
	 *
	 * @param  string  $actionName The action name.
	 * @param  array   $args       Args passed down to the action.
	 * @return boolean             Whether the action is already scheduled.
	 */
	public function isScheduled( $actionName, $args = [] ) {
		if ( ! function_exists( 'as_has_scheduled_action' ) ) { // In case site is loading older AS version.
			return as_next_scheduled_action( $actionName, $args, $this->actionSchedulerGroup );
		}

		// Use Action Scheduler's optimized existence check.
		// as_has_scheduled_action() was introduced in Action Scheduler 3.3.0.
		return as_has_scheduled_action( $actionName, $args, $this->actionSchedulerGroup );
	}

	/**
	 * Unschedule all pending instances of an action.
	 *
	 * Uses as_unschedule_all_actions() to ensure recurring actions are fully stopped,
	 * since as_unschedule_action() only cancels the next occurrence.
	 *
	 * @since   4.1.4
	 * @version 4.2.7
	 * @version 4.9.4.2 Use as_unschedule_all_actions() to properly stop recurring actions.
	 *
	 * @param  string $actionName The action name to unschedule.
	 * @param  array  $args       Args passed down to the action.
	 * @return void
	 */
	public function unschedule( $actionName, $args = [] ) {
		try {
			as_unschedule_all_actions( $actionName, $args, $this->actionSchedulerGroup );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Schedules a recurring action.
	 *
	 * @since   4.1.5
	 * @version 4.2.7
	 *
	 * @param  string  $actionName The action name.
	 * @param  int     $time       The seconds to add to the current time.
	 * @param  int     $interval   The interval in seconds.
	 * @param  array   $args       Args passed down to the action.
	 * @return boolean             Whether the action was scheduled.
	 */
	public function scheduleRecurrent( $actionName, $time, $interval = 60, $args = [] ) {
		$lockKey = $this->getLockKey( $actionName, $args );

		// Skip if another request is currently scheduling this action.
		if ( get_transient( $lockKey ) ) {
			return false;
		}

		// Set lock for 5 seconds.
		set_transient( $lockKey, 1, 5 );

		try {
			if ( ! $this->isScheduled( $actionName, $args ) ) {
				as_schedule_recurring_action( time() + $time, $interval, $actionName, $args, $this->actionSchedulerGroup );
				delete_transient( $lockKey );

				return true;
			}
		} catch ( \RuntimeException $e ) {
			// Nothing needs to happen.
		}

		delete_transient( $lockKey );

		return false;
	}

	/**
	 * Schedule a single async action.
	 *
	 * @since   4.1.6
	 * @version 4.9.4.2 Added check for duplicates.
	 *
	 * @param  string $actionName The name of the action.
	 * @param  array  $args       Any relevant arguments.
	 * @return void
	 */
	public function scheduleAsync( $actionName, $args = [] ) {
		$lockKey = $this->getLockKey( $actionName, $args );

		// Skip if another request is currently scheduling this action.
		if ( get_transient( $lockKey ) ) {
			return;
		}

		// Set lock for 5 seconds.
		set_transient( $lockKey, 1, 5 );

		try {
			// Only schedule if not already scheduled to prevent duplicate actions.
			if ( ! $this->isScheduled( $actionName, $args ) ) {
				// Run the task immediately using an async action.
				as_enqueue_async_action( $actionName, $args, $this->actionSchedulerGroup );
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}

		delete_transient( $lockKey );
	}

	/**
	 * Returns a transient lock key for the given action name and args.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $actionName The action name.
	 * @param  array  $args       The action arguments.
	 * @return string             The lock key.
	 */
	private function getLockKey( $actionName, $args = [] ) {
		return 'aioseo_as_lock_' . md5( $actionName . wp_json_encode( $args ) );
	}
}