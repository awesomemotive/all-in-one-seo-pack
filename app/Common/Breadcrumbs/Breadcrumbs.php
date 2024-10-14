<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Class Breadcrumbs.
	 *
	 * @since 4.1.1
	 */
	class Breadcrumbs {
		/** Instance of the frontend class.
		 *
		 * @since 4.1.1
		 *
		 * @var \AIOSEO\Plugin\Common\Breadcrumbs\Frontend|\AIOSEO\Plugin\Pro\Breadcrumbs\Frontend
		 */
		public $frontend;

		/**
		 * Instance of the shortcode class.
		 *
		 * @since 4.1.1
		 *
		 * @var Shortcode
		 */
		public $shortcode;

		/**
		 * Instance of the block class.
		 *
		 * @since 4.1.1
		 *
		 * @var Block
		 */
		public $block;

		/**
		 * Instance of the tags class.
		 *
		 * @since 4.1.1
		 *
		 * @var Tags
		 */
		public $tags;

		/**
		 * Array of crumbs.
		 *
		 * @since 4.1.1
		 *
		 * @var array An array of crumbs.
		 */
		public $breadcrumbs;

		/**
		 * Breadcrumbs constructor.
		 *
		 * @since 4.1.1
		 */
		public function __construct() {
			$this->frontend  = new Frontend();
			$this->shortcode = new Shortcode();
			$this->block     = new Block();

			add_action( 'widgets_init', [ $this, 'registerWidget' ] );

			// Init Tags class later as we need post types registered.
			add_action( 'init', [ $this, 'init' ], 50 );
		}

		public function init() {
			$this->tags = new Tags();
		}

		/**
		 * Helper to add crumbs on the breadcrumb array.
		 *
		 * @since 4.1.1
		 *
		 * @param  array $crumbs A single crumb or an array of crumbs.
		 * @return void
		 */
		public function addCrumbs( $crumbs ) {
			if ( empty( $crumbs ) || ! is_array( $crumbs ) ) {
				return;
			}

			// If it's a single crumb put it inside an array to merge.
			if ( isset( $crumbs['label'] ) ) {
				$crumbs = [ $crumbs ];
			}

			$this->breadcrumbs = array_merge( $this->breadcrumbs, $crumbs );
		}

		/**
		 * Builds a crumb array based on a type and a reference.
		 *
		 * @since 4.1.1
		 *
		 * @param  string $type       The type of breadcrumb ( post, single, page, category, tag, taxonomy, postTypeArchive, date,
		 *                            author, search, notFound, blog ).
		 * @param  mixed  $reference  The reference can be an object ( WP_Post | WP_Term | WP_Post_Type | WP_User ), an array, an int or a string.
		 * @param  array  $paged      A reference for a paged crumb.
		 * @return array              An array of breadcrumbs with their label, link, type and reference.
		 */
		public function buildBreadcrumbs( $type, $reference, $paged = [] ) {
			// Clear the breadcrumb array and build a new one.
			$this->breadcrumbs = [];

			// Add breadcrumb prefix.
			$this->addCrumbs( $this->getPrefixCrumb( $type, $reference ) );

			// Set a home page in the beginning of the breadcrumb.
			$this->addCrumbs( $this->maybeGetHomePageCrumb( $type, $reference ) );

			// Woocommerce shop page support.
			$this->addCrumbs( $this->maybeGetWooCommerceShopCrumb() );

			// Blog home.
			if (
				aioseo()->options->breadcrumbs->showBlogHome &&
				in_array( $type, [ 'category', 'tag', 'post', 'author', 'date' ], true )
			) {
				$this->addCrumbs( $this->getBlogCrumb() );
			}

			switch ( $type ) {
				case 'post':
				case 'single':
					$this->addCrumbs( $this->getPostArchiveCrumb( $reference ) );
					$this->addCrumbs( $this->getPostTaxonomyCrumbs( $reference ) );
					$this->addCrumbs( $this->getPostParentCrumbs( $reference ) );
					$this->addCrumbs( $this->getPostCrumb( $reference ) );
					break;
				case 'page':
					$this->addCrumbs( $this->getPostParentCrumbs( $reference, 'page' ) );
					$this->addCrumbs( $this->getPostCrumb( $reference, 'page' ) );
					break;
				case 'category':
				case 'tag':
				case 'taxonomy':
					$this->addCrumbs( $this->getTermTaxonomyParentCrumbs( $reference ) );
					$this->addCrumbs( $this->getTermTaxonomyCrumb( $reference ) );
					break;
				case 'postTypeArchive':
					$this->addCrumbs( $this->getPostTypeArchiveCrumb( $reference ) );
					break;
				case 'date':
					$this->addCrumbs( $this->getDateCrumb( $reference ) );
					break;
				case 'author':
					$this->addCrumbs( $this->getAuthorCrumb( $reference ) );
					break;
				case 'blog':
					$this->addCrumbs( $this->getBlogCrumb() );
					break;
				case 'search':
					$this->addCrumbs( $this->getSearchCrumb( $reference ) );
					break;
				case 'notFound':
					$this->addCrumbs( $this->getNotFoundCrumb() );
					break;
				case 'preview':
					$this->addCrumbs( $this->getPreviewCrumb( $reference ) );
					break;
				case 'wcProduct':
					$this->addCrumbs( $this->getPostTaxonomyCrumbs( $reference ) );
					$this->addCrumbs( $this->getPostParentCrumbs( $reference ) );
					$this->addCrumbs( $this->getPostCrumb( $reference ) );
					break;
			}

			// Paged crumb.
			if ( ! empty( $paged['paged'] ) ) {
				$this->addCrumbs( $this->getPagedCrumb( $paged ) );
			}

			// Maybe remove the last crumb.
			if ( ! $this->showCurrentItem( $type, $reference ) ) {
				array_pop( $this->breadcrumbs );
			}

			// Remove empty crumbs.
			$this->breadcrumbs = array_filter( $this->breadcrumbs );

			return $this->breadcrumbs;
		}

		/**
		 * Gets the prefix crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  string $type      The type of breadcrumb.
		 * @param  mixed  $reference The breadcrumb reference.
		 * @return array             A crumb.
		 */
		public function getPrefixCrumb( $type, $reference ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
			if ( 0 === strlen( aioseo()->options->breadcrumbs->breadcrumbPrefix ) ) {
				return [];
			}

			return $this->makeCrumb( aioseo()->options->breadcrumbs->breadcrumbPrefix, '', 'prefix' );
		}

		/**
		 * Gets the 404 crumb.
		 *
		 * @since 4.1.1
		 *
		 * @return array A crumb.
		 */
		public function getNotFoundCrumb() {
			return $this->makeCrumb( aioseo()->options->breadcrumbs->errorFormat404, '', 'notFound' );
		}

		/**
		 * Gets the search crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  string $searchQuery The search query for reference.
		 * @return array               A crumb.
		 */
		public function getSearchCrumb( $searchQuery ) {
			return $this->makeCrumb( aioseo()->options->breadcrumbs->searchResultFormat, get_search_link( $searchQuery ), 'search', $searchQuery );
		}

		/**
		 * Gets the preview crumb.
		 *
		 * @since 4.1.5
		 *
		 * @param  string $label The preview label.
		 * @return array         A crumb.
		 */
		public function getPreviewCrumb( $label ) {
			return $this->makeCrumb( $label, '', 'preview' );
		}

		/**
		 * Gets the post type archive crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  \WP_Post_Type $postType The post type object for reference.
		 * @return array                   A crumb.
		 */
		public function getPostTypeArchiveCrumb( $postType ) {
			return $this->makeCrumb( aioseo()->options->breadcrumbs->archiveFormat, get_post_type_archive_link( $postType->name ), 'postTypeArchive', $postType );
		}

		/**
		 * Gets a post crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  \WP_Post $post    A post object for reference.
		 * @param  string   $type    The breadcrumb type.
		 * @param  string   $subType The breadcrumb subType.
		 * @return array             A crumb.
		 */
		public function getPostCrumb( $post, $type = 'single', $subType = '' ) {
			return $this->makeCrumb( get_the_title( $post ), get_permalink( $post ), $type, $post, $subType );
		}

		/**
		 * Gets the term crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  \WP_Term $term    The term object for reference.
		 * @param  string   $subType The breadcrumb subType.
		 * @return array             A crumb.
		 */
		public function getTermTaxonomyCrumb( $term, $subType = '' ) {
			return $this->makeCrumb( $term->name, get_term_link( $term ), 'taxonomy', $term, $subType );
		}

		/**
		 * Gets the paged crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  array $reference The paged array for reference.
		 * @return array             A crumb.
		 */
		public function getPagedCrumb( $reference ) {
			return $this->makeCrumb( sprintf( '%1$s %2$s', __( 'Page', 'all-in-one-seo-pack' ), $reference['paged'] ), $reference['link'], 'paged', $reference );
		}

		/**
		 * Gets the author crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  \WP_User $wpUser A WP_User object.
		 * @return array            A crumb.
		 */
		public function getAuthorCrumb( $wpUser ) {
			return $this->makeCrumb( $wpUser->display_name, get_author_posts_url( $wpUser->ID ), 'author', $wpUser );
		}

		/**
		 * Gets the date crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  array $reference An array of year, month and day values.
		 * @return array            A crumb.
		 */
		public function getDateCrumb( $reference ) {
			$dateCrumb = [];
			$addMonth  = false;
			$addYear   = false;
			if ( ! empty( $reference['day'] ) ) {
				$addMonth    = true;
				$addYear     = true;
				$dateCrumb[] = $this->makeCrumb(
					zeroise( (int) $reference['day'], 2 ),
					get_day_link( $reference['year'], $reference['month'], $reference['day'] ),
					'day',
					$reference['day']
				);
			}
			if ( ! empty( $reference['month'] ) || $addMonth ) {
				$addYear     = true;
				$dateCrumb[] = $this->makeCrumb(
					zeroise( (int) $reference['month'], 2 ),
					get_month_link( $reference['year'], $reference['month'] ),
					'month',
					$reference['month']
				);

			}
			if ( ! empty( $reference['year'] ) || $addYear ) {
				$dateCrumb[] = $this->makeCrumb(
					$reference['year'],
					get_year_link( $reference['year'] ),
					'year',
					$reference['year']
				);
			}

			return array_reverse( $dateCrumb );
		}

		/**
		 * Gets an array of crumbs parents for the term.
		 *
		 * @since 4.1.1
		 *
		 * @param  \WP_Term $term A WP_Term object.
		 * @return array          An array of parent crumbs.
		 */
		public function getTermTaxonomyParentCrumbs( $term ) {
			$crumbs = [];

			$termHierarchy = $this->getTermHierarchy( $term->term_id, $term->taxonomy );
			if ( ! empty( $termHierarchy ) ) {
				foreach ( $termHierarchy as $parentTermId ) {
					$parentTerm = get_term( $parentTermId, $term->taxonomy );
					$crumbs[]   = $this->getTermTaxonomyCrumb( $parentTerm, 'parent' );
				}
			}

			return $crumbs;
		}

		/**
		 * Helper function to create a standard crumb array.
		 *
		 * @since 4.1.1
		 *
		 * @param  string $label     The crumb label.
		 * @param  string $link      The crumb url.
		 * @param  null   $type      The crumb type.
		 * @param  null   $reference The crumb reference.
		 * @param  null   $subType   The crumb subType ( single/parent ).
		 * @return array             A crumb array.
		 */
		public function makeCrumb( $label, $link = '', $type = null, $reference = null, $subType = null ) {
			return [
				'label'     => $label,
				'link'      => $link,
				'type'      => $type,
				'subType'   => $subType,
				'reference' => $reference
			];
		}

		/**
		 * Gets a post archive crumb if it's post type has archives.
		 *
		 * @since 4.1.1
		 *
		 * @param  int|\WP_Post $post An ID or a WP_Post object.
		 * @return array              A crumb.
		 */
		public function getPostArchiveCrumb( $post ) {
			$postType = get_post_type_object( get_post_type( $post ) );
			if ( ! $postType || ! $postType->has_archive ) {
				return [];
			}

			return $this->makeCrumb( $postType->labels->name, get_post_type_archive_link( $postType->name ), 'postTypeArchive', $postType );
		}

		/**
		 * Gets a post's taxonomy crumbs.
		 *
		 * @since 4.1.1
		 *
		 * @param  int|\WP_Post $post     An ID or a WP_Post object.
		 * @param  null         $taxonomy A taxonomy to use. If none is provided the first one with terms selected will be used.
		 * @return array                  An array of term crumbs.
		 */
		public function getPostTaxonomyCrumbs( $post, $taxonomy = null ) {
			$crumbs = [];

			if ( $taxonomy && ! is_array( $taxonomy ) ) {
				$taxonomy = [ $taxonomy ];
			}

			$termHierarchy = $this->getPostTaxTermHierarchy( $post, $taxonomy );
			if ( ! empty( $termHierarchy['terms'] ) ) {
				foreach ( $termHierarchy['terms'] as $termId ) {
					$term     = get_term( $termId, $termHierarchy['taxonomy'] );
					$crumbs[] = $this->makeCrumb( $term->name, get_term_link( $term, $termHierarchy['taxonomy'] ), 'taxonomy', $term, 'parent' );
				}
			}

			return $crumbs;
		}

		/**
		 * Gets the post's parent crumbs.
		 *
		 * @since 4.1.1
		 *
		 * @param  int|\WP_Post $post An ID or a WP_Post object.
		 * @param  string       $type The crumb type.
		 * @return array              An array of the post parent crumbs.
		 */
		public function getPostParentCrumbs( $post, $type = 'single' ) {
			$crumbs = [];
			if ( ! is_post_type_hierarchical( get_post_type( $post ) ) ) {
				return $crumbs;
			}

			$postHierarchy = $this->getPostHierarchy( $post );
			if ( ! empty( $postHierarchy ) ) {
				foreach ( $postHierarchy as $parentID ) {
					// Do not include the Home Page.
					if ( aioseo()->helpers->getHomePageId() === $parentID ) {
						continue;
					}
					$crumbs[] = $this->getPostCrumb( get_post( $parentID ), $type, 'parent' );
				}
			}

			return $crumbs;
		}

		/**
		 * Function to extend on pro for extra functionality.
		 *
		 * @since 4.1.1
		 *
		 * @param  string $type      The type of breadcrumb.
		 * @param  mixed  $reference The breadcrumb reference.
		 * @return bool              Show current item.
		 */
		public function showCurrentItem( $type = null, $reference = null ) {
			return apply_filters( 'aioseo_breadcrumbs_show_current_item', aioseo()->options->breadcrumbs->showCurrentItem, $type, $reference );
		}

		/**
		 * Gets a home page crumb.
		 *
		 * @since 4.1.1
		 *
		 * @param  string     $type      The type of breadcrumb.
		 * @param  mixed      $reference The breadcrumb reference.
		 * @return array|void            The home crumb.
		 */
		public function maybeGetHomePageCrumb( $type = null, $reference = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
			if ( aioseo()->options->breadcrumbs->homepageLink ) {
				return $this->getHomePageCrumb();
			}
		}

		/**
		 * Gets a home page crumb.
		 *
		 * @since 4.1.1
		 *
		 * @return array The home crumb.
		 */
		public function getHomePageCrumb() {
			$homePageId = aioseo()->helpers->getHomePageId();

			$label = '';
			if ( $homePageId ) {
				$label = get_the_title( $homePageId );
			}

			if ( 0 < strlen( aioseo()->options->breadcrumbs->homepageLabel ) ) {
				$label = aioseo()->options->breadcrumbs->homepageLabel;
			}

			// Label fallback.
			if ( empty( $label ) ) {
				$label = __( 'Home', 'all-in-one-seo-pack' );
			}

			return $this->makeCrumb( $label, get_home_url(), 'homePage', aioseo()->helpers->getHomePage() );
		}

		/**
		 * Gets the blog crumb.
		 *
		 * @since 4.1.1
		 *
		 * @return array The blog crumb.
		 */
		public function getBlogCrumb() {
			$crumb = [];

			$blogPage = aioseo()->helpers->getBlogPage();
			if ( null !== $blogPage ) {
				$crumb = $this->makeCrumb( $blogPage->post_title, get_permalink( $blogPage ), 'blog', $blogPage );
			}

			return $crumb;
		}

		/**
		 * Maybe add the shop crumb to products and product categories.
		 *
		 * @since 4.5.5
		 *
		 * @return array The shop crumb.
		 */
		public function maybeGetWooCommerceShopCrumb() {
			$crumb = [];
			if (
				aioseo()->helpers->isWooCommerceShopPage() ||
				aioseo()->helpers->isWooCommerceProductPage() ||
				aioseo()->helpers->isWooCommerceTaxonomyPage()
			) {
				$crumb = $this->getWooCommerceShopCrumb();
			}

			return $crumb;
		}

		/**
		 * Gets the shop crumb.
		 * @see WC_Breadcrumb::prepend_shop_page()
		 *
		 * @since 4.5.5
		 *
		 * @return array The shop crumb.
		 */
		public function getWooCommerceShopCrumb() {
			$crumb = [];

			if (
				! function_exists( 'wc_get_page_id' ) ||
				apply_filters( 'aioseo_woocommerce_breadcrumb_hide_shop', false )
			) {
				return $crumb;
			}

			$shopPageId = wc_get_page_id( 'shop' );
			$shopPage   = get_post( $shopPageId );

			// WC checks if the permalink contains the shop page in the URI, but we prefer to
			// always show the shop page as the first crumb if it exists and it's not the home page.
			if (
				$shopPageId &&
				$shopPage &&
				aioseo()->helpers->getHomePageId() !== $shopPageId
			) {
				$crumb = $this->makeCrumb( get_the_title( $shopPage ), get_permalink( $shopPage ), 'wcShop' );
			}

			return $crumb;
		}

		/**
		 * Gets a post's term hierarchy for a list of taxonomies selecting the one that has a lengthier hierarchy.
		 *
		 * @since 4.1.1
		 *
		 * @param  int|\WP_Post $post                An ID or a WP_Post object.
		 * @param  array        $taxonomies          An array of taxonomy names.
		 * @param  false        $skipUnselectedTerms Allow unselected terms to be filtered out from the crumbs.
		 * @return array                             An array of the taxonomy name + a term hierarchy.
		 */
		public function getPostTaxTermHierarchy( $post, $taxonomies = [], $skipUnselectedTerms = false ) {
			// Get all taxonomies attached to the post.
			if ( empty( $taxonomies ) ) {
				$taxonomies = get_object_taxonomies( get_post_type( $post ), 'objects' );
				$taxonomies = wp_filter_object_list( $taxonomies, [ 'public' => true ], 'and', 'name' );
			}

			foreach ( $taxonomies as $taxonomy ) {
				$primaryTerm = aioseo()->standalone->primaryTerm->getPrimaryTerm( $post->ID, $taxonomy );
				$terms       = wp_get_object_terms( $post->ID, $taxonomy );
				// Use the first taxonomy with terms.
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					continue;
				}

				// Determines the lengthier term hierarchy.
				$termHierarchy = [];
				foreach ( $terms as $term ) {
					// Gets our filtered ancestors.
					$ancestors = $this->getFilteredTermHierarchy( $term->term_id, $term->taxonomy, $skipUnselectedTerms ? $terms : [] );

					// Merge the current term to be used in the breadcrumbs.
					$ancestors = array_merge( $ancestors, [ $term->term_id ] );

					// If the current term is the primary term, use it.
					if ( is_a( $primaryTerm, 'WP_Term' ) && $primaryTerm->term_id === $term->term_id ) {
						$termHierarchy = $ancestors;
						break;
					}

					$termHierarchy = ( count( $termHierarchy ) < count( $ancestors ) ) ? $ancestors : $termHierarchy;
				}

				// Return a top to bottom hierarchy.
				return [
					'taxonomy' => $taxonomy,
					'terms'    => $termHierarchy
				];
			}

			return [];
		}

		/**
		 * Filters a term's parent hierarchy against other terms.
		 *
		 * @since 4.1.1
		 *
		 * @param  int    $termId               A term id.
		 * @param  string $taxonomy             The taxonomy name.
		 * @param  array  $termsToFilterAgainst Terms to filter out of the hierarchy.
		 * @return array                        The term's parent hierarchy.
		 */
		public function getFilteredTermHierarchy( $termId, $taxonomy, $termsToFilterAgainst = [] ) {
			$ancestors = $this->getTermHierarchy( $termId, $taxonomy );

			// Keep only selected terms in the hierarchy.
			if ( ! empty( $termsToFilterAgainst ) ) {
				// If it's a WP_Term array make it a term_id array.
				if ( is_a( current( $termsToFilterAgainst ), 'WP_Term' ) ) {
					$termsToFilterAgainst = wp_list_pluck( $termsToFilterAgainst, 'term_id' );
				}

				$ancestors = array_intersect( $ancestors, $termsToFilterAgainst );
			}

			return $ancestors;
		}

		/**
		 * Gets a term's parent hierarchy.
		 *
		 * @since 4.1.1
		 *
		 * @param  int    $termId   A term id.
		 * @param  string $taxonomy A taxonomy name.
		 * @return array            The term parent hierarchy.
		 */
		public function getTermHierarchy( $termId, $taxonomy ) {
			// Return a top to bottom hierarchy.
			return array_reverse( get_ancestors( $termId, $taxonomy, 'taxonomy' ) );
		}

		/**
		 * Gets a post's parent hierarchy.
		 *
		 * @since 4.1.1
		 *
		 * @param  int|\WP_Post $post An ID or a WP_Post object.
		 * @return array              The post parent hierarchy.
		 */
		public function getPostHierarchy( $post ) {
			$postId = ! empty( $post->ID ) ? $post->ID : $post;

			// Return a top to bottom hierarchy.
			return array_reverse( get_ancestors( $postId, '', 'post_type' ) );
		}

		/**
		 * Register our breadcrumb widget.
		 *
		 * @since 4.1.1
		 *
		 * @return void
		 */
		public function registerWidget() {
			if ( aioseo()->helpers->canRegisterLegacyWidget( 'aioseo-breadcrumb-widget' ) ) {
				register_widget( 'AIOSEO\Plugin\Common\Breadcrumbs\Widget' );
			}
		}
	}
}

namespace {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! function_exists( 'aioseo_breadcrumbs' ) ) {
		/**
		 * Global function for breadcrumbs output.
		 *
		 * @since 4.1.1
		 *
		 * @param  boolean     $echo Echo or return the output.
		 * @return string|void       The output.
		 */
		function aioseo_breadcrumbs( $echo = true ) {
			return aioseo()->breadcrumbs->frontend->display( $echo );
		}
	}
}