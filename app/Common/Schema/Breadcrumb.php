<?php
namespace AIOSEO\Plugin\Common\Schema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines the breadcrumb trail.
 *
 * @since 4.0.0
 */
class Breadcrumb {
	/**
	 * Returns the breadcrumb trail for the homepage.
	 *
	 * @since 4.0.0
	 *
	 * @return array The breadcrumb trail.
	 */
	public function home() {
		// Since we just need the root breadcrumb (homepage), we can call this immediately without passing any breadcrumbs.
		return $this->setPositions();
	}

	/**
	 * Returns the breadcrumb trail for the requested post.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return array          The breadcrumb trail.
	 */
	public function post( $post ) {
		// Check if page is the static homepage.
		if ( aioseo()->helpers->isStaticHomePage() ) {
			return $this->home();
		}

		if ( is_post_type_hierarchical( $post->post_type ) ) {
			return $this->setPositions( $this->postHierarchical( $post ) );
		}

		return $this->setPositions( $this->postNonHierarchical( $post ) );
	}

	/**
	 * Returns the breadcrumb trail for a hierarchical post.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return array          The breadcrumb trail.
	 */
	private function postHierarchical( $post ) {
		$breadcrumbs = [];
		do {
			array_unshift(
				$breadcrumbs,
				[
					'name'        => $post->post_title,
					'description' => aioseo()->meta->description->getDescription( $post ),
					'url'         => get_permalink( $post ),
					'type'        => aioseo()->helpers->isWooCommerceShopPage( $post->ID ) || is_home() ? 'CollectionPage' : $this->getPostWebPageGraph()
				]
			);

			if ( $post->post_parent ) {
				$post = get_post( $post->post_parent );
			} else {
				$post = false;
			}
		} while ( $post );

		return $breadcrumbs;
	}

	/**
	 * Returns the breadcrumb trail for a non-hierarchical post.
	 *
	 * In this case we need to compare the permalink structure with the permalink of the requested post and loop through all objects we're able to find.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return array          The breadcrumb trail.
	 */
	private function postNonHierarchical( $post ) {
		global $wp_query;
		$homeUrl   = aioseo()->helpers->escapeRegex( home_url() );
		$permalink = get_permalink();
		$slug      = preg_replace( "/$homeUrl/", '', $permalink );
		$tags      = array_filter( explode( '/', get_option( 'permalink_structure' ) ) ); // Permalink structure exploded into separate tag strings.
		$objects   = array_filter( explode( '/', $slug ) ); // Permalink slug exploded into separate object slugs.
		$postGraph = $this->getPostWebPageGraph();

		if ( count( $tags ) !== count( $objects ) ) {
			return [
				'name'        => $post->post_title,
				'description' => aioseo()->meta->description->getDescription( $post ),
				'url'         => $permalink,
				'type'        => $postGraph
			];
		}

		$pairs = array_reverse( array_combine( $tags, $objects ) );

		$breadcrumbs = [];
		$dateName    = null;
		$timestamp   = strtotime( $post->post_date );
		foreach ( $pairs as $tag => $object ) {
			// Escape the delimiter.
			$escObject = aioseo()->helpers->escapeRegex( $object );
			// Determine the slug for the object.
			preg_match( "/.*{$escObject}[\/]/", $permalink, $url );
			if ( empty( $url[0] ) ) {
				continue;
			}

			$breadcrumb = [];
			switch ( $tag ) {
				case '%category%':
					$term = aioseo()->standalone->primaryTerm->getPrimaryTerm( $post->ID, 'category' );
					if ( ! $term ) {
						$term = get_category_by_slug( $object );
					}

					if ( ! $term ) {
						break;
					}

					$oldQueriedObject         = $wp_query->queried_object;
					$wp_query->queried_object = $term;
					$wp_query->is_category    = true;

					$breadcrumb = [
						'name'        => $term->name,
						'description' => aioseo()->meta->description->getDescription(),
						'url'         => get_term_link( $term ),
						'type'        => 'CollectionPage'
					];

					$wp_query->queried_object = $oldQueriedObject;
					$wp_query->is_category    = false;
					break;
				case '%author%':
					$breadcrumb = [
						'name'        => get_the_author_meta( 'display_name', $post->post_author ),
						'description' => aioseo()->meta->description->helpers->prepare( aioseo()->options->searchAppearance->archives->author->metaDescription ),
						'url'         => $url[0],
						'type'        => 'ProfilePage'
					];
					break;
				case '%postid%':
				case '%postname%':
					$breadcrumb = [
						'name'        => $post->post_title,
						'description' => aioseo()->meta->description->getDescription( $post ),
						'url'         => $url[0],
						'type'        => $postGraph
					];
					break;
				case '%year%':
					$dateName = gmdate( 'Y', $timestamp );
				case '%monthnum%':
					if ( ! $dateName ) {
						$dateName = gmdate( 'F', $timestamp );
					}
				case '%day%':
					if ( ! $dateName ) {
						$dateName = gmdate( 'j', $timestamp );
					}
					$breadcrumb = [
						'name'        => $dateName,
						'description' => aioseo()->meta->description->helpers->prepare( aioseo()->options->searchAppearance->archives->date->metaDescription ),
						'url'         => $url[0],
						'type'        => 'CollectionPage'
					];
					$dateName = null;
					break;
				default:
					break;
			}

			if ( $breadcrumb ) {
				array_unshift( $breadcrumbs, $breadcrumb );
			}
		}

		return $breadcrumbs;
	}

	/**
	 * Returns the breadcrumb trail for the requested term.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Term $term The term object.
	 * @return array          The breadcrumb trail.
	 */
	public function term( $term ) {
		$breadcrumbs = [];
		do {
			array_unshift(
				$breadcrumbs,
				[
					'name'        => $term->name,
					'description' => aioseo()->meta->description->getDescription(),
					'url'         => get_term_link( $term, $term->taxonomy ),
					'type'        => 'CollectionPage'
				]
			);

			if ( $term->parent ) {
				$term = get_term( $term->parent, $term->taxonomy );
			} else {
				$term = false;
			}
		} while ( $term );

		return $this->setPositions( $breadcrumbs );
	}

	/**
	 * Returns the breadcrumb trail for the requested date archive.
	 *
	 * @since 4.0.0
	 *
	 * @return array The breadcrumb trail.
	 */
	public function date() {
		global $wp_query;

		$oldYear            = $wp_query->is_year;
		$oldMonth           = $wp_query->is_month;
		$oldDay             = $wp_query->is_day;
		$wp_query->is_year  = true;
		$wp_query->is_month = false;
		$wp_query->is_day   = false;

		$breadcrumbs = [
			[
				'name'        => get_the_date( 'Y' ),
				'description' => aioseo()->meta->description->getDescription(),
				'url'         => trailingslashit( get_year_link( $wp_query->query_vars['year'] ) ),
				'type'        => 'CollectionPage'
			]
		];

		$wp_query->is_year = $oldYear;

		// Fall through if data archive is more specific than the year.
		if ( is_year() ) {
			return $this->setPositions( $breadcrumbs );
		}

		$wp_query->is_month = true;

		$breadcrumbs[] = [
			'name'        => get_the_date( 'F, Y' ),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => trailingslashit( get_month_link(
				$wp_query->query_vars['year'],
				$wp_query->query_vars['monthnum']
			) ),
			'type'        => 'CollectionPage'
		];

		$wp_query->is_month = $oldMonth;

		// Fall through if data archive is more specific than the year & month.
		if ( is_month() ) {
			return $this->setPositions( $breadcrumbs );
		}

		$wp_query->is_day = $oldDay;

		$breadcrumbs[] = [
			'name'        => get_the_date(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => trailingslashit( get_day_link(
				$wp_query->query_vars['year'],
				$wp_query->query_vars['monthnum'],
				$wp_query->query_vars['day']
			) ),
			'type'        => 'CollectionPage'
		];

		return $this->setPositions( $breadcrumbs );
	}

	/**
	 * Sets the position for each breadcrumb after adding the root breadcrumb first.
	 *
	 * If no breadcrumbs are passed, then we assume we're on the homepage and just need the root breadcrumb.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $breadcrumbs The breadcrumb trail.
	 * @return array              The modified breadcrumb trail.
	 */
	public function setPositions( $breadcrumbs = [] ) {
		// If the array isn't two-dimensional, then we need to wrap it in another array before continuing.
		if (
			count( $breadcrumbs ) &&
			count( $breadcrumbs ) === count( $breadcrumbs, COUNT_RECURSIVE )
		) {
			$breadcrumbs = [ $breadcrumbs ];
		}

		// The homepage needs to be root item of all trails.
		$homepage = [
			// Translators: This refers to the homepage of the site.
			'name'        => apply_filters( 'aioseo_schema_breadcrumbs_home', __( 'Home', 'all-in-one-seo-pack' ) ),
			'description' => aioseo()->meta->description->getHomePageDescription(),
			'url'         => trailingslashit( home_url() ),
			'type'        => 'posts' === get_option( 'show_on_front' ) ? 'CollectionPage' : 'WebPage'
		];
		array_unshift( $breadcrumbs, $homepage );

		$breadcrumbs = array_filter( $breadcrumbs );
		foreach ( $breadcrumbs as $index => &$breadcrumb ) {
			$breadcrumb['position'] = $index + 1;
		}

		return $breadcrumbs;
	}

	/**
	 * Returns the most relevant WebPage graph for the post.
	 *
	 * @since 4.2.5
	 *
	 * @return string The graph name.
	 */
	private function getPostWebPageGraph() {
		foreach ( aioseo()->schema->graphs as $graphName ) {
			if ( in_array( $graphName, aioseo()->schema->webPageGraphs, true ) ) {
				return $graphName;
			}
		}

		// Return the default if no WebPage graph was found.
		return 'WebPage';
	}
}