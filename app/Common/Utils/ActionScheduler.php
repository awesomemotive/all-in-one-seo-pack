<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class makes sure the action scheduler tables always exist.
 *
 * @since 4.0.0
 */
class ActionScheduler {
	/**
	 * The action scheduler group.
	 *
	 * @since   4.1.5
	 * @version 4.2.7
	 *
	 * @var string
	 */
	private $actionSchedulerGroup = 'aioseo';

	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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

		if ( ! apply_filters( 'action_scheduler_enable_recreate_data_store', true ) ) {
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
			defined( 'AIOSEO_DEV_VERSION' ) ||
			// Bail if the tables don't exist.
			! aioseo()->core->db->tableExists( 'actionscheduler_actions' ) ||
			! aioseo()->core->db->tableExists( 'actionscheduler_groups' )
		) {
			return;
		}

		$prefix = aioseo()->core->db->db->prefix;

		// Clean up logs associated with entries in the actions table.
		aioseo()->core->db->execute(
			"DELETE al FROM {$prefix}actionscheduler_logs as al
			JOIN {$prefix}actionscheduler_actions as aa on `aa`.`action_id` = `al`.`action_id`
			JOIN {$prefix}actionscheduler_groups as ag on `ag`.`group_id` = `aa`.`group_id`
			WHERE `ag`.`slug` = 'aioseo'
			AND `aa`.`status` IN ('complete', 'failed', 'canceled');"
		);

		// Clean up actions.
		aioseo()->core->db->execute(
			"DELETE aa FROM {$prefix}actionscheduler_actions as aa
			JOIN {$prefix}actionscheduler_groups as ag on `ag`.`group_id` = `aa`.`group_id`
			WHERE `ag`.`slug` = 'aioseo'
			AND `aa`.`status` IN ('complete', 'failed', 'canceled');"
		);

		// Clean up logs where there was no group.
		aioseo()->core->db->execute(
			"DELETE al FROM {$prefix}actionscheduler_logs as al
			JOIN {$prefix}actionscheduler_actions as aa on `aa`.`action_id` = `al`.`action_id`
			WHERE `aa`.`hook` LIKE 'aioseo_%'
			AND `aa`.`group_id` = 0
			AND `aa`.`status` IN ('complete', 'failed', 'canceled');"
		);

		// Clean up actions that start with aioseo_ and have no group.
		aioseo()->core->db->execute(
			"DELETE aa FROM {$prefix}actionscheduler_actions as aa
			WHERE `aa`.`hook` LIKE 'aioseo_%'
			AND `aa`.`group_id` = 0
			AND `aa`.`status` IN ('complete', 'failed', 'canceled');"
		);
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
		try {
			if ( $forceSchedule || ! $this->isScheduled( $actionName, $args ) ) {
				as_schedule_single_action( time() + $time, $actionName, $args, $this->actionSchedulerGroup );

				return true;
			}
		} catch ( \RuntimeException $e ) {
			// Nothing needs to happen.
		}

		return false;
	}

	/**
	 * Checks if a given action is already scheduled.
	 *
	 * @since   4.0.13
	 * @version 4.2.7
	 *
	 * @param  string  $actionName The action name.
	 * @param  array   $args       Args passed down to the action.
	 * @return boolean             Whether the action is already scheduled.
	 */
	public function isScheduled( $actionName, $args = [] ) {
		// We need to check for pending actions specifically since we otherwise cannot schedule another action while one is running (e.g. image and video sitemap scans).
		$pendingArgs = [
			'hook'     => $actionName,
			'status'   => \ActionScheduler_Store::STATUS_PENDING,
			'per_page' => 1
		];

		$runningArgs = [
			'hook'     => $actionName,
			'status'   => \ActionScheduler_Store::STATUS_RUNNING,
			'per_page' => 1
		];

		if ( ! empty( $args ) ) {
			$pendingArgs['args'] = $args;
			$runningArgs['args'] = $args;
		}

		$actions = array_merge(
			as_get_scheduled_actions( $pendingArgs ),
			as_get_scheduled_actions( $runningArgs )
		);

		return ! empty( $actions );
	}

	/**
	 * Unschedule an action.
	 *
	 * @since   4.1.4
	 * @version 4.2.7
	 *
	 * @param  string $actionName The action name to unschedule.
	 * @param  array  $args       Args passed down to the action.
	 * @return void
	 */
	public function unschedule( $actionName, $args = [] ) {
		try {
			if ( as_next_scheduled_action( $actionName, $args ) ) {
				as_unschedule_action( $actionName, $args, $this->actionSchedulerGroup );
			}
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
		try {
			if ( ! $this->isScheduled( $actionName, $args ) ) {
				as_schedule_recurring_action( time() + $time, $interval, $actionName, $args, $this->actionSchedulerGroup );

				return true;
			}
		} catch ( \RuntimeException $e ) {
			// Nothing needs to happen.
		}

		return false;
	}

	/**
	 * Schedule a single async action.
	 *
	 * @since   4.1.6
	 * @version 4.2.7
	 *
	 * @param  string $actionName The name of the action.
	 * @param  array  $args       Any relevant arguments.
	 * @return void
	 */
	public function scheduleAsync( $actionName, $args = [] ) {
		try {
			// Run the task immediately using an async action.
			as_enqueue_async_action( $actionName, $args, $this->actionSchedulerGroup );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}
}