<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;
use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the user meta from Yoast SEO.
 *
 * @since 4.0.0
 */
class UserMeta {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function scheduleImport() {
		aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->yoastSeo->userActionName, 30 );

		if ( ! aioseo()->core->cache->get( 'import_user_meta_yoast_seo' ) ) {
			aioseo()->core->cache->update( 'import_user_meta_yoast_seo', 0, WEEK_IN_SECONDS );
		}
	}

	/**
	 * Imports the post meta.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function importUserMeta() {
		$usersPerAction = 100;
		$offset         = aioseo()->core->cache->get( 'import_user_meta_yoast_seo' );

		$usersMeta = aioseo()->core->db
			->start( aioseo()->core->db->db->usermeta . ' as um', true )
			->whereRaw( "um.meta_key IN ('facebook', 'twitter')" )
			->whereRaw( "um.meta_value != ''" )
			->limit( $offset . ',' . $usersPerAction )
			->run()
			->result();

		if ( ! $usersMeta || ! count( $usersMeta ) ) {
			aioseo()->core->cache->delete( 'import_user_meta_yoast_seo' );

			return;
		}

		foreach ( $usersMeta as $meta ) {
			update_user_meta( $meta->user_id, 'aioseo_' . $meta->meta_key, $meta->meta_value );
		}

		if ( count( $usersMeta ) === $usersPerAction ) {
			aioseo()->core->cache->update( 'import_user_meta_yoast_seo', 100 + $offset, WEEK_IN_SECONDS );
			aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->yoastSeo->userActionName, 5, [], true );
		} else {
			aioseo()->core->cache->delete( 'import_user_meta_yoast_seo' );
		}
	}
}