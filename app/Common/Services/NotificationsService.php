<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Service for listing AIOSEO admin notifications.
 *
 * Surfaces the warnings/errors AIOSEO would show in the admin so agents can answer
 * "what's broken with my AIOSEO setup?" without needing screen-reading access.
 *
 * @internal Not a public extension surface.
 *
 * @since 4.9.8
 */
class NotificationsService {
	/**
	 * Returns currently-active AIOSEO notifications, optionally including dismissed ones.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $filters Accepted: include_dismissed (bool), limit (int 1-100), offset (int >= 0).
	 * @return array|\WP_Error
	 */
	public function listActive( $filters = [] ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to view AIOSEO notifications.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$filters          = is_array( $filters ) ? $filters : [];
		$includeDismissed = ! empty( $filters['include_dismissed'] );
		$limit            = isset( $filters['limit'] ) ? min( max( 1, (int) $filters['limit'] ), 100 ) : 20;
		$offset           = isset( $filters['offset'] ) ? max( 0, (int) $filters['offset'] ) : 0;

		// Delegate active selection to the Notification model so we honour the same rules
		// the AIOSEO admin uses: dismissed=0, current time within start/end window, plus
		// the model's filterNotifications() pass (addon/cap gating, etc.).
		$active = Models\Notification::getAllActiveNotifications();

		$pool = $active;
		if ( $includeDismissed ) {
			$pool = array_merge( $pool, $this->getDismissedRows() );
		}

		$total = count( $pool );
		$slice = array_slice( $pool, $offset, $limit );

		$notifications = [];
		foreach ( $slice as $row ) {
			$row             = is_array( $row ) ? $row : (array) $row;
			$level           = $row['level'] ?? '';
			$notifications[] = [
				'slug'           => (string) ( $row['slug'] ?? '' ),
				'title'          => (string) ( $row['title'] ?? '' ),
				'content'        => (string) ( $row['content'] ?? '' ),
				'type'           => (string) ( $row['type'] ?? '' ),
				'level'          => is_array( $level ) ? array_values( array_map( 'strval', $level ) ) : [ (string) $level ],
				'button1_label'  => (string) ( $row['button1_label'] ?? '' ),
				'button1_action' => (string) ( $row['button1_action'] ?? '' ),
				'button2_label'  => (string) ( $row['button2_label'] ?? '' ),
				'button2_action' => (string) ( $row['button2_action'] ?? '' ),
				'dismissed'      => (bool) ( $row['dismissed'] ?? false ),
				'created'        => (string) ( $row['created'] ?? '' ),
				'updated'        => (string) ( $row['updated'] ?? '' )
			];
		}

		return [
			'notifications' => $notifications,
			'total'         => $total
		];
	}

	/**
	 * Returns the dismissed notification rows as associative arrays, scoped to the same
	 * time-window the Notification model uses for "active." Used only when the caller
	 * explicitly opts in via include_dismissed.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function getDismissedRows() {
		$rows = aioseo()->core->db
			->start( 'aioseo_notifications' )
			->where( 'dismissed', 1 )
			->whereRaw( "(start <= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR start IS NULL)" )
			->whereRaw( "(end >= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR end IS NULL)" )
			->orderBy( 'updated DESC' )
			->run()
			->result();

		return json_decode( wp_json_encode( $rows ), true ) ?: [];
	}
}