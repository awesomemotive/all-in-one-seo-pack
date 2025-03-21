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
			->whereRaw( "um.meta_key IN ('facebook', 'twitter', 'instagram', 'linkedin', 'myspace', 'pinterest', 'soundcloud', 'tumblr', 'wikipedia', 'youtube', 'mastodon')" )
			->whereRaw( "um.meta_value != ''" )
			->limit( $usersPerAction, $offset )
			->run()
			->result();

		if ( ! $usersMeta || ! count( $usersMeta ) ) {
			aioseo()->core->cache->delete( 'import_user_meta_yoast_seo' );

			return;
		}

		$mappedMeta = [
			'facebook'   => 'aioseo_facebook_page_url',
			'twitter'    => 'aioseo_twitter_url',
			'instagram'  => 'aioseo_instagram_url',
			'linkedin'   => 'aioseo_linkedin_url',
			'myspace'    => 'aioseo_myspace_url',
			'pinterest'  => 'aioseo_pinterest_url',
			'soundcloud' => 'aioseo_sound_cloud_url',
			'tumblr'     => 'aioseo_tumblr_url',
			'wikipedia'  => 'aioseo_wikipedia_url',
			'youtube'    => 'aioseo_youtube_url',
			'mastodon'   => 'aioseo_profiles_additional_urls',
		];

		foreach ( $usersMeta as $meta ) {
			if ( isset( $mappedMeta[ $meta->meta_key ] ) ) {
				$value = 'twitter' === $meta->meta_key ? 'https://x.com/' . $meta->meta_value : $meta->meta_value;
				update_user_meta( $meta->user_id, $mappedMeta[ $meta->meta_key ], $value );
			}
		}

		if ( count( $usersMeta ) === $usersPerAction ) {
			aioseo()->core->cache->update( 'import_user_meta_yoast_seo', 100 + $offset, WEEK_IN_SECONDS );
			aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->yoastSeo->userActionName, 5, [], true );
		} else {
			aioseo()->core->cache->delete( 'import_user_meta_yoast_seo' );
		}
	}
}