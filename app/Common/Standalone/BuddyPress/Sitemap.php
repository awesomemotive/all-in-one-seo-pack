<?php

namespace AIOSEO\Plugin\Common\Standalone\BuddyPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * BuddyPress Sitemap class.
 *
 * @since 4.7.6
 */
class Sitemap {
	/**
	 * Returns the indexes for the sitemap root index.
	 *
	 * @since 4.7.6
	 *
	 * @return array The indexes.
	 */
	public function indexes() {
		$indexes           = [];
		$includedPostTypes = array_flip( aioseo()->sitemap->helpers->includedPostTypes() );
		$filterPostTypes   = array_filter( [
			BuddyPressIntegration::isComponentActive( 'activity' ) && isset( $includedPostTypes['bp-activity'] ) ? 'bp-activity' : '',
			BuddyPressIntegration::isComponentActive( 'group' ) && isset( $includedPostTypes['bp-group'] ) ? 'bp-group' : '',
			BuddyPressIntegration::isComponentActive( 'member' ) && isset( $includedPostTypes['bp-member'] ) ? 'bp-member' : '',
		] );

		foreach ( $filterPostTypes as $postType ) {
			$indexes = array_merge( $indexes, $this->buildIndexesPostType( $postType ) );
		}

		return $indexes;
	}

	/**
	 * Builds BuddyPress related root indexes.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $postType The BuddyPress fake post type.
	 * @return array            The BuddyPress related root indexes.
	 */
	private function buildIndexesPostType( $postType ) {
		switch ( $postType ) {
			case 'bp-activity':
				return $this->buildIndexesActivity();
			case 'bp-group':
				return $this->buildIndexesGroup();
			case 'bp-member':
				return $this->buildIndexesMember();
			default:
				return [];
		}
	}

	/**
	 * Builds activity root indexes.
	 *
	 * @since 4.7.6
	 *
	 * @return array The activity root indexes.
	 */
	private function buildIndexesActivity() {
		$activityTable = aioseo()->core->db->prefix . 'bp_activity';
		$linksPerIndex = aioseo()->sitemap->linksPerIndex;
		$items         = aioseo()->core->db->execute(
			aioseo()->core->db->db->prepare(
				"SELECT id, date_recorded
				FROM (
					SELECT @row := @row + 1 AS rownum, id, date_recorded
					FROM (
						SELECT a.id, a.date_recorded FROM $activityTable as a
						WHERE a.is_spam = 0
							AND a.hide_sitewide = 0
							AND a.type NOT IN ('activity_comment', 'last_activity')
						ORDER BY a.date_recorded DESC
					) AS x
					CROSS JOIN (SELECT @row := 0) AS vars
					ORDER BY date_recorded DESC
				) AS y
				WHERE rownum = 1 OR rownum % %d = 1;",
				[
					$linksPerIndex
				]
			),
			true
		)->result();

		$totalItems = aioseo()->core->db->execute(
			"SELECT COUNT(*) as count
			FROM $activityTable as a
			WHERE a.is_spam = 0
				AND a.hide_sitewide = 0
				AND a.type NOT IN ('activity_comment', 'last_activity')
			",
			true
		)->result();

		$indexes = [];
		if ( $items ) {
			$filename = aioseo()->sitemap->filename;
			$count    = count( $items );
			for ( $i = 0; $i < $count; $i++ ) {
				$indexNumber = 0 !== $i && 1 < $count ? $i + 1 : '';

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/bp-activity-$filename$indexNumber.xml" ),
					'lastmod' => aioseo()->helpers->dateTimeToIso8601( $items[ $i ]->date_recorded ),
					'count'   => $linksPerIndex
				];
			}

			// We need to update the count of the last index since it won't necessarily be the same as the links per index.
			$indexes[ count( $indexes ) - 1 ]['count'] = $totalItems[0]->count - ( $linksPerIndex * ( $count - 1 ) );
		}

		return $indexes;
	}

	/**
	 * Builds group root indexes.
	 *
	 * @since 4.7.6
	 *
	 * @return array The group root indexes.
	 */
	private function buildIndexesGroup() {
		$groupsTable     = aioseo()->core->db->prefix . 'bp_groups';
		$groupsMetaTable = aioseo()->core->db->prefix . 'bp_groups_groupmeta';
		$linksPerIndex   = aioseo()->sitemap->linksPerIndex;
		$items           = aioseo()->core->db->execute(
			aioseo()->core->db->db->prepare(
				"SELECT id, date_modified
				FROM (
					SELECT @row := @row + 1 AS rownum, id, date_modified
					FROM (
						SELECT g.id, gm.group_id, MAX(gm.meta_value) as date_modified FROM $groupsTable as g
						INNER JOIN $groupsMetaTable AS gm ON g.id = gm.group_id
						WHERE g.status = 'public'
							AND gm.meta_key = 'last_activity'
						GROUP BY g.id
						ORDER BY date_modified DESC
					) AS x
					CROSS JOIN (SELECT @row := 0) AS vars
					ORDER BY date_modified DESC
				) AS y
				WHERE rownum = 1 OR rownum % %d = 1;",
				[
					$linksPerIndex
				]
			),
			true
		)->result();

		$totalItems = aioseo()->core->db->execute(
			"SELECT COUNT(*) as count
			FROM $groupsTable as g
			WHERE g.status = 'public'
			",
			true
		)->result();

		$indexes = [];
		if ( $items ) {
			$filename = aioseo()->sitemap->filename;
			$count    = count( $items );
			for ( $i = 0; $i < $count; $i++ ) {
				$indexNumber = 0 !== $i && 1 < $count ? $i + 1 : '';

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/bp-group-$filename$indexNumber.xml" ),
					'lastmod' => aioseo()->helpers->dateTimeToIso8601( $items[ $i ]->date_modified ),
					'count'   => $linksPerIndex
				];
			}

			// We need to update the count of the last index since it won't necessarily be the same as the links per index.
			$indexes[ count( $indexes ) - 1 ]['count'] = $totalItems[0]->count - ( $linksPerIndex * ( $count - 1 ) );
		}

		return $indexes;
	}

	/**
	 * Builds member root indexes.
	 *
	 * @since 4.7.6
	 *
	 * @return array The member root indexes.
	 */
	private function buildIndexesMember() {
		$activityTable = aioseo()->core->db->prefix . 'bp_activity';
		$linksPerIndex = aioseo()->sitemap->linksPerIndex;
		$items         = aioseo()->core->db->execute(
			aioseo()->core->db->db->prepare(
				"SELECT user_id, date_recorded
				FROM (
					SELECT @row := @row + 1 AS rownum, user_id, date_recorded
					FROM (
						SELECT a.user_id, a.date_recorded FROM $activityTable as a
						WHERE a.component = 'members'
							AND a.type = 'last_activity'
						ORDER BY a.date_recorded DESC
					) AS x
					CROSS JOIN (SELECT @row := 0) AS vars
					ORDER BY date_recorded DESC
				) AS y
				WHERE rownum = 1 OR rownum % %d = 1;",
				[
					$linksPerIndex
				]
			),
			true
		)->result();

		$totalItems = aioseo()->core->db->execute(
			"SELECT COUNT(*) as count
			FROM $activityTable as a
			WHERE a.component = 'members'
				AND a.type = 'last_activity'
			",
			true
		)->result();

		$indexes = [];
		if ( $items ) {
			$filename = aioseo()->sitemap->filename;
			$count    = count( $items );
			for ( $i = 0; $i < $count; $i++ ) {
				$indexNumber = 0 !== $i && 1 < $count ? $i + 1 : '';

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/bp-member-$filename$indexNumber.xml" ),
					'lastmod' => aioseo()->helpers->dateTimeToIso8601( $items[ $i ]->date_recorded ),
					'count'   => $linksPerIndex
				];
			}

			// We need to update the count of the last index since it won't necessarily be the same as the links per index.
			$indexes[ count( $indexes ) - 1 ]['count'] = $totalItems[0]->count - ( $linksPerIndex * ( $count - 1 ) );
		}

		return $indexes;
	}
}