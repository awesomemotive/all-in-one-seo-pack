<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the output of the HTML sitemap.
 *
 * @since 4.1.3
 */
class Frontend {
	/**
	 * Instance of Query class.
	 *
	 * @since 4.1.3
	 *
	 * @var Query
	 */
	public $query;

	/**
	 * The attributes for the block/widget/shortcode.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.3
	 */
	public function __construct() {
		$this->query = new Query();
	}

	/**
	 * Returns the attributes.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $attributes The user-defined attributes
	 * @return array             The defaults with user-defined attributes merged.
	 */
	public function getAttributes( $attributes = [] ) {
		aioseo()->sitemap->type = 'html';

		$defaults = [
			'label_tag'        => 'h4',
			'show_label'       => true,
			'order'            => aioseo()->options->sitemap->html->sortDirection,
			'order_by'         => aioseo()->options->sitemap->html->sortOrder,
			'nofollow_links'   => false,
			'publication_date' => aioseo()->options->sitemap->html->publicationDate,
			'archives'         => aioseo()->options->sitemap->html->compactArchives,
			'post_types'       => aioseo()->sitemap->helpers->includedPostTypes(),
			'taxonomies'       => aioseo()->sitemap->helpers->includedTaxonomies(),
			'excluded_posts'   => [],
			'excluded_terms'   => [],
			'is_admin'         => false
		];

		$attributes                   = shortcode_atts( $defaults, $attributes );
		$attributes['show_label']     = filter_var( $attributes['show_label'], FILTER_VALIDATE_BOOLEAN );
		$attributes['nofollow_links'] = filter_var( $attributes['nofollow_links'], FILTER_VALIDATE_BOOLEAN );
		$attributes['is_admin']       = filter_var( $attributes['is_admin'], FILTER_VALIDATE_BOOLEAN );

		return $attributes;
	}

	/**
	 * Formats the publish date according to what's set under Settings > General.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $date The date that should be formatted.
	 * @return string       The formatted date.
	 */
	private function formatDate( $date ) {
		$dateFormat = apply_filters( 'aioseo_html_sitemap_date_format', get_option( 'date_format' ) );

		return date_i18n( $dateFormat, strtotime( $date ) );
	}

	/**
	 * Returns the posts of a given post type that should be included.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $postType       The post type.
	 * @param  array  $additionalArgs Additional arguments for the post query (optional).
	 * @return array                  The post entries.
	 */
	private function posts( $postType, $additionalArgs = [] ) {
		$posts = $this->query->posts( $postType, $additionalArgs );
		if ( ! $posts ) {
			return [];
		}

		$entries = [];
		foreach ( $posts as $post ) {
			$entry = [
				'id'     => $post->ID,
				'title'  => get_the_title( $post ),
				'loc'    => get_permalink( $post->ID ),
				'date'   => $this->formatDate( $post->post_date_gmt ),
				'parent' => ! empty( $post->post_parent ) ? $post->post_parent : null
			];

			$entries[] = $entry;
		}

		return apply_filters( 'aioseo_html_sitemap_posts', $entries, $postType );
	}

	/**
	 * Returns the terms of a given taxonomy that should be included.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $taxonomy        The taxonomy name.
	 * @param  array  $additionalArgs  Additional arguments for the query (optional).
	 * @return array                   The term entries.
	 */
	private function terms( $taxonomy, $additionalArgs = [] ) {
		$terms = $this->query->terms( $taxonomy, $additionalArgs );
		if ( ! $terms ) {
			return [];
		}

		$entries = [];
		foreach ( $terms as $term ) {
			$entries[] = [
				'id'     => $term->term_id,
				'title'  => $term->name,
				'loc'    => get_term_link( $term->term_id ),
				'parent' => ! empty( $term->parent ) ? $term->parent : null
			];
		}

		return apply_filters( 'aioseo_html_sitemap_terms', $entries, $taxonomy );
	}

	/**
	 * Outputs the sitemap to the frontend.
	 *
	 * @since 4.1.3
	 *
	 * @param  bool  $echo       Whether the sitemap should be printed to the screen.
	 * @param  array $attributes The shortcode attributes.
	 * @return string|void       The HTML sitemap.
	 */
	public function output( $echo = true, $attributes = [] ) {
		$this->attributes = $attributes;

		if ( ! aioseo()->options->sitemap->html->enable ) {
			return;
		}

		aioseo()->sitemap->type = 'html';
		if ( filter_var( $attributes['archives'], FILTER_VALIDATE_BOOLEAN ) ) {
			return ( new CompactArchive() )->output( $attributes, $echo );
		}

		if ( ! empty( $attributes['default'] ) ) {
			$attributes = $this->getAttributes();
		}

		$noResultsMessage = esc_html__( 'No posts/terms could be found.', 'all-in-one-seo-pack' );
		if ( empty( $this->attributes['post_types'] ) && empty( $this->attributes['taxonomies'] ) ) {
			if ( $echo ) {
				echo $noResultsMessage; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $noResultsMessage;
		}

		// TODO: Consider moving all remaining HTML code below to a dedicated view instead of printing it in PHP.
		$sitemap = sprintf(
			'<div class="aioseo-html-sitemap%s">',
			! $this->attributes['show_label'] ? ' labels-hidden' : ''
		);

		$sitemap .= '<style>.aioseo-html-sitemap.labels-hidden ul { margin: 0; }</style>';

		$hasPosts  = false;
		$postTypes = $this->getIncludedObjects( $this->attributes['post_types'] );
		foreach ( $postTypes as $postType ) {
			if ( 'attachment' === $postType ) {
				continue;
			}

			// Check if post type is still registered.
			if ( ! in_array( $postType, aioseo()->helpers->getPublicPostTypes( true ), true ) ) {
				continue;
			}

			$posts = $this->posts( $postType, $attributes );
			if ( empty( $posts ) ) {
				continue;
			}

			$hasPosts = true;

			$postTypeObject = get_post_type_object( $postType );
			$label          = ! empty( $postTypeObject->label ) ? $postTypeObject->label : ucfirst( $postType );

			$sitemap .= '<div class="aioseo-html-' . esc_attr( $postType ) . '-sitemap">';
			$sitemap .= $this->generateLabel( $label );

			if ( is_post_type_hierarchical( $postType ) ) {
				$sitemap .= $this->generateHierarchicalList( $posts ) . '</div>';
				if ( $this->attributes['show_label'] ) {
					$sitemap .= '<br />';
				}
				continue;
			}

			$sitemap .= $this->generateList( $posts );
			if ( $this->attributes['show_label'] ) {
				$sitemap .= '<br />';
			}
		}

		$hasTerms   = false;
		$taxonomies = $this->getIncludedObjects( $this->attributes['taxonomies'], false );
		foreach ( $taxonomies as $taxonomy ) {
			// Check if post type is still registered.
			if ( ! in_array( $taxonomy, aioseo()->helpers->getPublicTaxonomies( true ), true ) ) {
				continue;
			}

			$terms = $this->terms( $taxonomy, $attributes );
			if ( empty( $terms ) ) {
				continue;
			}

			$hasTerms = true;

			$taxonomyObject = get_taxonomy( $taxonomy );
			$label          = ! empty( $taxonomyObject->label ) ? $taxonomyObject->label : ucfirst( $taxonomy );

			$sitemap .= '<div class="aioseo-html-' . esc_attr( $taxonomy ) . '-sitemap">';
			$sitemap .= $this->generateLabel( $label );

			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				$sitemap .= $this->generateHierarchicalList( $terms ) . '</div>';
				if ( $this->attributes['show_label'] ) {
					$sitemap .= '<br />';
				}
				continue;
			}

			$sitemap .= $this->generateList( $terms );
			if ( $this->attributes['show_label'] ) {
				$sitemap .= '<br />';
			}
		}

		$sitemap .= '</div>';

		// Check if we actually were able to fetch any results.
		if ( ! $hasPosts && ! $hasTerms ) {
			$sitemap = $noResultsMessage;
		}

		if ( $echo ) {
			echo $sitemap; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $sitemap;
	}

	/**
	 * Generates the label for a section of the sitemap.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $label The label.
	 * @return string        The HTML code for the label.
	 */
	private function generateLabel( $label ) {
		$labelTag = ! empty( $this->attributes['label_tag'] ) ? $this->attributes['label_tag'] : 'h4';

		return $this->attributes['show_label']
			? wp_kses_post( sprintf( '<%2$s>%1$s</%2$s>', $label, $labelTag ) )
			: '';
	}

	/**
	 * Generates the HTML for a non-hierarchical list of objects.
	 *
	 * @since 4.1.3
	 *
	 * @param  array  $objects The object.
	 * @return string          The HTML code.
	 */
	private function generateList( $objects ) {
		$list = '<ul>';
		foreach ( $objects as $object ) {
			$list .= $this->generateListItem( $object ) . '</li>';
		}

		return $list . '</ul></div>';
	}

	/**
	 * Generates a list item for an object (without the closing tag).
	 * We cannot close it as the caller might need to generate a hierarchical structure inside the list item.
	 *
	 * @since 4.1.3
	 *
	 * @param  array  $object The object.
	 * @return string         The HTML code.
	 */
	private function generateListItem( $object ) {
		$li = '';
		if ( ! empty( $object['title'] ) ) {
			$li .= '<li>';

			// add nofollow to the link.
			if ( filter_var( $this->attributes['nofollow_links'], FILTER_VALIDATE_BOOLEAN ) ) {
				$li .= sprintf(
					'<a href="%1$s" %2$s %3$s>',
					esc_url( $object['loc'] ),
					'rel="nofollow"',
					$this->attributes['is_admin'] ? 'target="_blank"' : ''
				);
			} else {
				$li .= sprintf(
					'<a href="%1$s" %2$s>',
					esc_url( $object['loc'] ),
					$this->attributes['is_admin'] ? 'target="_blank"' : ''
				);
			}

			$li .= sprintf( '%s', esc_attr( $object['title'] ) );

			// add publication date on the list item.
			if ( ! empty( $object['date'] ) && filter_var( $this->attributes['publication_date'], FILTER_VALIDATE_BOOLEAN ) ) {
				$li .= sprintf( ' (%s)', esc_attr( $object['date'] ) );
			}

			$li .= '</a>';
		}

		return $li;
	}

	/**
	 * Generates the HTML for a hierarchical list of objects.
	 *
	 * @since 4.1.3
	 *
	 * @param  array  $objects The objects.
	 * @return string          The HTML of the hierarchical objects section.
	 */
	private function generateHierarchicalList( $objects ) {
		if ( empty( $objects ) ) {
			return '';
		}

		$objects = $this->buildHierarchicalTree( $objects );

		$list = '<ul>';
		foreach ( $objects as $object ) {
			$list .= $this->generateListItem( $object );

			if ( ! empty( $object['children'] ) ) {
				$list .= $this->generateHierarchicalTree( $object );
			}
			$list .= '</li>';
		}
		$list .= '</ul>';

		return $list;
	}

	/**
	 * Recursive helper function for generateHierarchicalList().
	 * Generates hierarchical structure for objects with child objects.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $object The object.
	 * @return string        The HTML code of the hierarchical tree.
	 */
	private function generateHierarchicalTree( $object ) {
		static $nestedLevel = 0;

		$tree = '<ul>';
		foreach ( $object['children'] as $child ) {
			$nestedLevel++;
			$tree .= $this->generateListItem( $child );
			if ( ! empty( $child['children'] ) ) {
				$tree .= $this->generateHierarchicalTree( $child );
			}
			$tree .= '</li>';
		}
		$tree .= '</ul>';

		return $tree;
	}

	/**
	 * Builds the structure for hierarchical objects that have a parent.
	 *
	 * @since 4.1.3
	 * @version 4.2.8
	 *
	 * @param  array $objects The list of hierarchical objects.
	 * @return array          Multidimensional array with the hierarchical structure.
	 */
	private function buildHierarchicalTree( $objects ) {
		$topLevelIds = [];
		$objects     = json_decode( wp_json_encode( $objects ) );

		foreach ( $objects as $listItem ) {

			// Create an array of top level IDs for later reference.
			if ( empty( $listItem->parent ) ) {
				array_push( $topLevelIds, $listItem->id );
			}

			// Create an array of children that belong to the current item.
			$children = array_filter( $objects, function( $child ) use ( $listItem ) {
				if ( ! empty( $child->parent ) ) {
					return absint( $child->parent ) === absint( $listItem->id );
				}
			} );

			if ( ! empty( $children ) ) {
				$listItem->children = $children;
			}
		}

		// Remove child objects from the root level since they've all been nested.
		$objects = array_filter( $objects, function ( $item ) use ( $topLevelIds ) {
			return in_array( $item->id, $topLevelIds, true );
		} );

		return array_values( json_decode( wp_json_encode( $objects ), true ) );
	}

	/**
	 * Returns the names of the included post types or taxonomies.
	 *
	 * @since 4.1.3
	 *
	 * @param  array|string $objects      The included post types/taxonomies.
	 * @param  boolean      $arePostTypes Whether the objects are post types.
	 * @return array                      The names of the included post types/taxonomies.
	 */
	private function getIncludedObjects( $objects, $arePostTypes = true ) {
		if ( is_array( $objects ) ) {
			return $objects;
		}

		if ( empty( $objects ) ) {
			return [];
		}

		$exploded = explode( ',', $objects );
		$objects  = array_map( function( $object ) {
			return trim( $object );
		}, $exploded );

		$publicObjects = $arePostTypes
			? aioseo()->helpers->getPublicPostTypes( true )
			: aioseo()->helpers->getPublicTaxonomies( true );

		$objects = array_filter( $objects, function( $object ) use ( $publicObjects ) {
			return in_array( $object, $publicObjects, true );
		});

		return $objects;
	}
}