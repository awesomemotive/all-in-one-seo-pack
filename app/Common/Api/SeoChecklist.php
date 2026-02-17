<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SeoChecklist {
	/**
	 * Get all checks (simple endpoint for initial load).
	 *
	 * @since 4.9.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getChecks() {
		// User should have access to the general settings or setup wizard.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'checks'  => array_values( aioseo()->seoChecklist->getChecks() )
		], 200 );
	}

	/**
	 * Get the list of completed check names (lightweight endpoint for polling).
	 *
	 * @since 4.9.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getCompletedChecks() {
		// User should have access to the general settings or setup wizard.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$checks          = aioseo()->seoChecklist->getChecks();
		$totalCompleted  = 0;
		$totalIncomplete = 0;

		foreach ( $checks as $check ) {
			if ( ! empty( $check['completed'] ) ) {
				$totalCompleted++;
			} else {
				$totalIncomplete++;
			}
		}

		return new \WP_REST_Response( [
			'success'         => true,
			'totalCompleted'  => $totalCompleted,
			'totalIncomplete' => $totalIncomplete
		], 200 );
	}

	/**
	 * Fetch checks with filtering, sorting, and pagination support.
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request  $request The request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function fetchChecks( $request ) {
		// User should have access to the general settings or setup wizard.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body              = $request->get_json_params();
		$filter            = ! empty( $body['filter'] ) ? sanitize_text_field( $body['filter'] ) : 'all';
		$orderBy           = ! empty( $body['orderBy'] ) ? sanitize_text_field( $body['orderBy'] ) : 'priority';
		$orderDir          = ! empty( $body['orderDir'] ) ? strtoupper( sanitize_text_field( $body['orderDir'] ) ) : 'ASC';
		$limit             = ! empty( $body['limit'] ) ? intval( $body['limit'] ) : 10;
		$offset            = ! empty( $body['offset'] ) ? intval( $body['offset'] ) : 0;
		$additionalFilters = ! empty( $body['additionalFilters'] ) ? $body['additionalFilters'] : [];

		$checks = array_values( aioseo()->seoChecklist->getChecks() );

		// Filter by status.
		if ( 'completed' === $filter ) {
			$checks = array_filter( $checks, function ( $check ) {
				return ! empty( $check['completed'] );
			} );
		} elseif ( 'incomplete' === $filter ) {
			$checks = array_filter( $checks, function ( $check ) {
				return empty( $check['completed'] );
			} );
		}

		// Filter by priority (from additional filters).
		if ( ! empty( $additionalFilters['priority'] ) && 'all' !== $additionalFilters['priority'] ) {
			$priorityFilter = sanitize_text_field( $additionalFilters['priority'] );
			$checks         = array_filter( $checks, function ( $check ) use ( $priorityFilter ) {
				return $check['priority'] === $priorityFilter;
			} );
		}

		// Re-index array after filtering.
		$checks = array_values( $checks );
		$total  = count( $checks );

		// Sort checks.
		usort( $checks, function ( $a, $b ) use ( $orderBy, $orderDir ) {
			$priorityOrder = [
				'high'     => 1,
				'medium'   => 2,
				'low'      => 3,
				'optional' => 4
			];

			if ( 'priority' === $orderBy ) {
				// Primary sort: priority
				$aVal = $priorityOrder[ $a['priority'] ?? '' ] ?? 99;
				$bVal = $priorityOrder[ $b['priority'] ?? '' ] ?? 99;
				$result = $aVal <=> $bVal;

				// Apply direction to primary sort
				$result = 'DESC' === $orderDir ? -$result : $result;

				if ( 0 !== $result ) {
					return $result;
				}

				// Secondary sort: undismissable first (dismissable = false comes first)
				$aDismissable = isset( $a['dismissable'] ) ? $a['dismissable'] : true;
				$bDismissable = isset( $b['dismissable'] ) ? $b['dismissable'] : true;
				$dismissableResult = (int) $aDismissable <=> (int) $bDismissable;

				if ( 0 !== $dismissableResult ) {
					return $dismissableResult;
				}

				// Tertiary sort: Setup Wizard always first among dismissable tasks
				$aIsSetupWizard = isset( $a['name'] ) && 'finishSetupWizard' === $a['name'];
				$bIsSetupWizard = isset( $b['name'] ) && 'finishSetupWizard' === $b['name'];
				$setupWizardResult = (int) $bIsSetupWizard <=> (int) $aIsSetupWizard;

				if ( 0 !== $setupWizardResult ) {
					return $setupWizardResult;
				}

				// Quaternary sort: time estimate (low to high)
				$aTime = is_array( $a['time'] ) ? ( $a['time']['value'] ?? 0 ) : 0;
				$bTime = is_array( $b['time'] ) ? ( $b['time']['value'] ?? 0 ) : 0;

				return $aTime <=> $bTime;
			} elseif ( 'time' === $orderBy ) {
				// Sort by the time value (in seconds).
				$aVal = is_array( $a['time'] ) ? ( $a['time']['value'] ?? 0 ) : 0;
				$bVal = is_array( $b['time'] ) ? ( $b['time']['value'] ?? 0 ) : 0;
			} else {
				$aVal = $a[ $orderBy ] ?? '';
				$bVal = $b[ $orderBy ] ?? '';
			}

			$result = $aVal <=> $bVal;

			return 'DESC' === $orderDir ? -$result : $result;
		} );

		// Apply pagination.
		$checks = array_slice( $checks, $offset, $limit );

		// Build filters with counts.
		$allChecks      = array_values( aioseo()->seoChecklist->getChecks() );
		$completedCount = count( array_filter( $allChecks, function ( $c ) {
			return ! empty( $c['completed'] );
		} ) );
		$totalCount     = count( $allChecks );

		$filters = [
			[
				'slug'   => 'incomplete',
				'name'   => __( 'Incomplete', 'all-in-one-seo-pack' ),
				'count'  => $totalCount - $completedCount,
				'active' => 'incomplete' === $filter
			],
			[
				'slug'   => 'completed',
				'name'   => __( 'Completed', 'all-in-one-seo-pack' ),
				'count'  => $completedCount,
				'active' => 'completed' === $filter
			]
		];

		return new \WP_REST_Response( [
			'success'        => true,
			'rows'           => $checks,
			'totals'         => [
				'total' => $total,
				'pages' => 0 === $total ? 1 : ceil( $total / $limit ),
				'page'  => 1
			],
			'filters'        => $filters,
			'completedCount' => $completedCount,
			'totalCount'     => $totalCount
		], 200 );
	}

	/**
	 * Complete a check.
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response         The response object.
	 */
	public static function completeCheck( $request ) {
		// User should have access to the general settings or setup wizard.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body  = $request->get_json_params();
		$check = isset( $body['check'] ) ? sanitize_text_field( $body['check'] ) : '';
		if ( ! $check ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing check.'
			], 400 );
		}

		aioseo()->seoChecklist->completeCheck( $check );

		return new \WP_REST_Response( [
			'success' => true,
			'checks'  => aioseo()->seoChecklist->getChecks()
		], 200 );
	}

	/**
	 * Uncomplete a check (remove completed status).
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response         The response object.
	 */
	public static function uncompleteCheck( $request ) {
		// User should have access to the general settings.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body  = $request->get_json_params();
		$check = isset( $body['check'] ) ? sanitize_text_field( $body['check'] ) : '';
		if ( ! $check ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing check.'
			], 400 );
		}

		aioseo()->seoChecklist->uncompleteCheck( $check );

		return new \WP_REST_Response( [
			'success' => true,
			'checks'  => aioseo()->seoChecklist->getChecks()
		], 200 );
	}

	/**
	 * Bulk complete checks.
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response         The response object.
	 */
	public static function bulkCompleteChecks( $request ) {
		// User should have access to the general settings.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body   = $request->get_json_params();
		$checks = isset( $body['checks'] ) ? $body['checks'] : [];
		if ( empty( $checks ) || ! is_array( $checks ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing checks.'
			], 400 );
		}

		foreach ( $checks as $check ) {
			aioseo()->seoChecklist->completeCheck( sanitize_text_field( $check ) );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'checks'  => aioseo()->seoChecklist->getChecks()
		], 200 );
	}

	/**
	 * Bulk uncomplete checks.
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response         The response object.
	 */
	public static function bulkUncompleteChecks( $request ) {
		// User should have access to the general settings.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body   = $request->get_json_params();
		$checks = isset( $body['checks'] ) ? $body['checks'] : [];
		if ( empty( $checks ) || ! is_array( $checks ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing checks.'
			], 400 );
		}

		foreach ( $checks as $check ) {
			aioseo()->seoChecklist->uncompleteCheck( sanitize_text_field( $check ) );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'checks'  => aioseo()->seoChecklist->getChecks()
		], 200 );
	}

	/**
	 * Execute an action for a check.
	 *
	 * @since 4.9.4
	 *
	 * @param  \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response         The response object.
	 */
	public static function doAction( $request ) {
		// User should have access to the general settings at a minimum.
		if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have access to the SEO Checklist.'
			], 403 );
		}

		$body     = $request->get_json_params();
		$check    = isset( $body['check'] ) ? sanitize_text_field( $body['check'] ) : '';
		$callback = isset( $body['callback'] ) ? sanitize_text_field( $body['callback'] ) : '';
		if ( ! $check || ! $callback ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing check or callback.'
			], 400 );
		}

		$success = aioseo()->seoChecklist->doAction( $check, $callback );
		if ( ! $success ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to execute action.'
			], 400 );
		}

		// Mark the check as completed.
		aioseo()->seoChecklist->completeCheck( $check );

		return new \WP_REST_Response( [
			'success' => $success,
			'checks'  => aioseo()->seoChecklist->getChecks()
		], 200 );
	}
}