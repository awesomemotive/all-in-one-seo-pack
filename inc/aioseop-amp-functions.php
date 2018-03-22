<?php
/**
 * AMP plugin related functions.
 *
 * @package All-in-One-SEO-Pack
 */

add_filter( 'aiosp_sitemap_extra', 'aiosp_sitemap_amp_extra' );
add_filter( 'aiosp_sitemap_rewrite_rules', 'aiosp_sitemap_amp_rewrite_rules', 10, 2 );
add_filter( 'aiosp_sitemap_custom_amp_sitemap', 'aiosp_sitemap_amp_sitemap', 10, 4 );
add_filter( 'aiosp_sitemap_custom_amppost', 'aiosp_sitemap_amp_post_sitemap', 10, 4 );
add_filter( 'aioseop_sitemap_index_filenames', 'aioseop_sitemap_amp_index', 10, 5 );


if ( ! function_exists( 'aiosp_sitemap_amp_post_types_supported' ) ) {
	/**
	 * Return the post types supported by the AMP plugin.
	 */
	function aiosp_sitemap_amp_post_types_supported() {
		// switch on/off AMP plugin support.
		if ( ! apply_filters( 'aioseop_support_amp', true ) ) {
			return array();
		}
		return array( 'post' );
	}
}

if ( ! function_exists( 'aiosp_sitemap_amp_supported' ) ) {
	/**
	 * Validate if AMP is supported and whether we need to process this particular request.
	 *
	 * @param array $options The array of options.
	 */
	function aiosp_sitemap_amp_supported( $options ) {
		return function_exists( 'amp_get_permalink' ) && ! empty( $options['aiosp_sitemap_posttypes'] ) && count( array_intersect( aiosp_sitemap_amp_post_types_supported(), $options['aiosp_sitemap_posttypes'] ) ) > 0;
	}
}

if ( ! function_exists( 'aiosp_sitemap_amp_extra' ) ) {
	/**
	 * Add the AMP sitemap.
	 *
	 * @param array $extra The array of extra sitemaps.
	 */
	function aiosp_sitemap_amp_extra( $extra ) {
		if ( function_exists( 'amp_get_permalink' ) ) {
			$extra[] = 'amp_sitemap';
			foreach ( aiosp_sitemap_amp_post_types_supported() as $type ) {
				$extra[] = "amp$type";
			}
		}
		return $extra;
	}
}

if ( ! function_exists( 'aiosp_sitemap_amp_rewrite_rules' ) ) {
	/**
	 * Add the AMP sitemap rewrite rules.
	 *
	 * @param array $rules The array of rules.
	 * @param array $options The array of options.
	 */
	function aiosp_sitemap_amp_rewrite_rules( $rules, $options ) {
		if ( aiosp_sitemap_amp_supported( $options ) ) {
			$rules[ $options['aiosp_sitemap_filename'] . '_amp.xml' ]            = 'index.php?aiosp_sitemap_path=amp_sitemap';
			$rules[ $options['aiosp_sitemap_filename'] . '_(.+)_(\d+).xml' ]     = 'index.php?aiosp_sitemap_path=$matches[1]&aiosp_sitemap_page=$matches[2]';
			$rules[ $options['aiosp_sitemap_filename'] . '_(.+).xml' ]           = 'index.php?aiosp_sitemap_path=$matches[1]';

			if ( $options['aiosp_sitemap_gzipped'] ) {
				$rules[ $options['aiosp_sitemap_filename'] . '.xml.gz' ]            = 'index.php?aiosp_sitemap_gzipped=1&aiosp_sitemap_path=root.gz';
				$rules[ $options['aiosp_sitemap_filename'] . '_(.+)_(\d+).xml.gz' ] = 'index.php?aiosp_sitemap_path=$matches[1].gz&aiosp_sitemap_page=$matches[2]';
				$rules[ $options['aiosp_sitemap_filename'] . '_(.+).xml.gz' ]       = 'index.php?aiosp_sitemap_path=$matches[1].gz';
			}
		}
		return $rules;
	}
}

if ( ! function_exists( 'aiosp_sitemap_amp_sitemap' ) ) {
	/**
	 * Add the AMP plugin generated URLs to the sitemap.
	 *
	 * @param array                       $sitemap_data The array of sitemap data (empty).
	 * @param int                         $page         Page number.
	 * @param array                       $options      The array of options.
	 * @param All_in_One_SEO_Pack_Sitemap $module       The sitemap module.
	 */
	function aiosp_sitemap_amp_sitemap( $sitemap_data, $page, $options, $module ) {
		if ( ! aiosp_sitemap_amp_supported( $options ) ) {
			return $sitemap_data;
		}

		$args = array(
			'post_type' => aiosp_sitemap_amp_post_types_supported(),
			'post_status' => 'publish',
			'offset' => $page * $module->get_max_posts(),
		);

		$posts = $module->get_all_post_type_data( $args );
		return $module->get_prio_from_posts( $posts, $module->get_default_priority( 'post', true ), $module->get_default_frequency( 'post', true ), 'aiosp_amp_sitemap_url' );
	}
}

if ( ! function_exists( 'aiosp_sitemap_amp_post_sitemap' ) ) {
	/**
	 * Add the AMP plugin generated URLs specific to post type 'post' to the sitemap.
	 *
	 * @param array                       $sitemap_data The array of sitemap data (empty).
	 * @param int                         $page         Page number.
	 * @param array                       $options      The array of options.
	 * @param All_in_One_SEO_Pack_Sitemap $module       The sitemap module.
	 */
	function aiosp_sitemap_amp_post_sitemap( $sitemap_data, $page, $options, $module ) {
		if ( ! aiosp_sitemap_amp_supported( $options ) || ! in_array( 'post', $options['aiosp_sitemap_posttypes'], true ) || empty( $options['aiosp_sitemap_indexes'] ) ) {
			return $sitemap_data;
		}

		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'offset' => $page * $module->get_max_posts(),
		);

		$posts = $module->get_all_post_type_data( $args );
		return $module->get_prio_from_posts( $posts, $module->get_default_priority( 'post', true ), $module->get_default_frequency( 'post', true ), 'aiosp_amp_sitemap_url' );
	}
}

if ( ! function_exists( 'aiosp_amp_sitemap_url' ) ) {
	/**
	 * The method amp_get_permalink() accepts a post_id so we have to create a wrapper around it.
	 *
	 * @param WP_Post $post The post object.
	 */
	function aiosp_amp_sitemap_url( $post ) {
		if ( ! function_exists( 'amp_get_permalink' ) ) {
			return get_permalink( $post );
		}
		return amp_get_permalink( $post->ID );
	}
}

if ( ! function_exists( 'aioseop_sitemap_amp_index' ) ) {
	/**
	 * Add the index file for the AMP URLs.
	 *
	 * @param array                       $files   The array of index files.
	 * @param string                      $prefix  The prefix of the filename.
	 * @param string                      $suffix  The suffix of the filename.
	 * @param array                       $options The array of options.
	 * @param All_in_One_SEO_Pack_Sitemap $module  The sitemap module.
	 */
	function aioseop_sitemap_amp_index( $files, $prefix, $suffix, $options, $module ) {
		if ( ! aiosp_sitemap_amp_supported( $options ) ) {
			return $files;
		}

		$supported = aiosp_sitemap_amp_post_types_supported();
		$post_counts = $module->get_all_post_counts( array(
			'post_type'   => $supported,
			'post_status' => 'publish',
		) );

		if ( ! is_array( $post_counts ) && 1 === count( $supported ) ) {
			$post_counts = array(
				$supported[0] => $post_counts,
			);
		}

		$max_posts = $module->get_max_posts();
		foreach ( $supported as $sm ) {
			if ( 0 === $post_counts[ $sm ] ) {
				continue;
			}

			$prio        = $module->get_default_priority( $sm );
			$freq        = $module->get_default_frequency( $sm );

			if ( ! empty( $options['aiosp_sitemap_indexes'] ) ) {
				if ( $post_counts[ $sm ] > $max_posts ) {
					$count = 1;
					for ( $post_count = 0; $post_count < $post_counts[ $sm ]; $post_count += $max_posts ) {
						$files[] = array(
							'loc'        => aioseop_home_url( '/' . $prefix . '_amp' . $sm . '_' . ( $count ++ ) . $suffix ),
							'priority'   => $prio,
							'changefreq' => $freq,
						);
					}
				} else {
					$files[] = array(
						'loc'        => aioseop_home_url( '/' . $prefix . '_amp' . $sm . $suffix ),
						'priority'   => $prio,
						'changefreq' => $freq,
					);
				}
			} else {
				$files[] = array(
					'loc'        => aioseop_home_url( '/' . $prefix . '_amp' . $sm . $suffix ),
					'priority'   => $prio,
					'changefreq' => $freq,
				);
			}
		}

		return $files;
	}
}
