<?php
namespace AIOSEO\Plugin\Common\Standalone\BuddyPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * Handles the BuddyPress integration with AIOSEO.
 *
 * @since 4.7.6
 */
class BuddyPress {
	/**
	 * Instance of the Tags class.
	 *
	 * @since 4.7.6
	 *
	 * @var Tags
	 */
	public $tags;

	/**
	 * Instance of the Component class.
	 *
	 * @since 4.7.6
	 *
	 * @var Component
	 */
	public $component;

	/**
	 * Instance of the Sitemap class.
	 *
	 * @since 4.7.6
	 *
	 * @var Sitemap
	 */
	public $sitemap = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.6
	 */
	public function __construct() {
		if (
			aioseo()->helpers->isAjaxCronRestRequest() ||
			! aioseo()->helpers->isPluginActive( 'buddypress' )
		) {
			return;
		}

		// Hook into `plugins_loaded` to ensure BuddyPress has loaded some necessary functions.
		add_action( 'plugins_loaded', [ $this, 'maybeLoad' ], 20 );
	}

	/**
	 * Hooked into `plugins_loaded` action hook.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	public function maybeLoad() {
		// If the BuddyPress version is below 12 we bail.
		if ( ! function_exists( 'bp_get_version' ) || version_compare( bp_get_version(), '12', '<' ) ) {
			return;
		}

		// If none of the necessary BuddyPress components are active we bail.
		if (
			! BuddyPressIntegration::isComponentActive( 'activity' ) &&
			! BuddyPressIntegration::isComponentActive( 'group' ) &&
			! BuddyPressIntegration::isComponentActive( 'member' )
		) {
			return;
		}

		$this->sitemap = new Sitemap();

		add_action( 'init', [ $this, 'setTags' ], 20 );
		add_action( 'bp_parse_query', [ $this, 'setComponent' ], 20 );
	}

	/**
	 * Hooked into `init` action hook.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	public function setTags() {
		$this->tags = new Tags();
	}

	/**
	 * Hooked into `bp_parse_query` action hook.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	public function setComponent() {
		$this->component = new Component();
	}

	/**
	 * Adds the BuddyPress fake post types to the list of post types, so they appear under e.g. Search Appearance.
	 *
	 * @since 4.7.6
	 *
	 * @param  array $postTypes       Public post types from {@see \AIOSEO\Plugin\Common\Traits\Helpers\Wp::getPublicPostTypes}.
	 * @param  bool  $namesOnly       Whether only the names should be included.
	 * @param  bool  $hasArchivesOnly Whether to only include post types which have archives.
	 * @param  array $args            Additional arguments.
	 * @return void
	 */
	public function maybeAddPostTypes( &$postTypes, $namesOnly, $hasArchivesOnly, $args ) {
		// If one of these CPTs is already registered we bail, so we don't overwrite them and possibly break something.
		if (
			post_type_exists( 'bp-activity' ) ||
			post_type_exists( 'bp-group' ) ||
			post_type_exists( 'bp-member' )
		) {
			return;
		}

		/**
		 * The BP components are registered with the `buddypress` CPT which is not viewable, so we add it here to include our metadata inside <head>.
		 * {@see \AIOSEO\Plugin\Common\Main\Head::wpHead}.
		 */
		if (
			$namesOnly &&
			doing_action( 'wp_head' )
		) {
			$postTypes = array_merge( $postTypes, [ 'buddypress' ] );

			return;
		}

		$fakePostTypes = $this->getFakePostTypes();

		if ( ! BuddyPressIntegration::isComponentActive( 'activity' ) ) {
			unset( $fakePostTypes['bp-activity'] );
		}

		if ( ! BuddyPressIntegration::isComponentActive( 'group' ) ) {
			unset( $fakePostTypes['bp-group'] );
		}

		if ( ! BuddyPressIntegration::isComponentActive( 'member' ) ) {
			unset( $fakePostTypes['bp-member'] );
		}

		if ( $hasArchivesOnly ) {
			$fakePostTypes = array_filter( $fakePostTypes, function ( $postType ) {
				return $postType['hasArchive'];
			} );
		}

		if ( $namesOnly ) {
			$fakePostTypes = array_keys( $fakePostTypes );
		}

		// 0. Below we'll add/merge the BuddyPress post types only under certain conditions.
		$fakePostTypes = array_values( $fakePostTypes );
		$currentScreen = aioseo()->helpers->getCurrentScreen();

		if (
			// 1. If the `buddypress` CPT is set in the list of post types to be included.
			( ! empty( $args['include'] ) && in_array( 'buddypress', $args['include'], true ) ) ||
			// 2. If the current request is for the sitemap.
			( ! empty( aioseo()->sitemap->filename ) && 'general' === ( aioseo()->sitemap->type ?? '' ) ) ||
			// 3. If we're on the Search Appearance screen.
			( $currentScreen && strpos( $currentScreen->id, 'aioseo-search-appearance' ) !== false ) ||
			// 4. If we're on the BuddyPress component front-end screen.
			BuddyPressIntegration::isComponentPage()
		) {
			$postTypes = array_merge( $postTypes, $fakePostTypes );
		}
	}

	/**
	 * Get edit links for the SEO Preview data.
	 *
	 * @since 4.7.6
	 *
	 * @return array
	 */
	public function getVueDataSeoPreview() {
		$data = [
			'editGoogleSnippetUrl' => '',
			'editObjectBtnText'    => '',
			'editObjectUrl'        => '',
		];

		list( $postType, $suffix ) = explode( '_', aioseo()->standalone->buddyPress->component->templateType );

		$bpFakePostTypes  = $this->getFakePostTypes();
		$fakePostTypeData = array_values( wp_list_filter( $bpFakePostTypes, [ 'name' => $postType ] ) );
		$fakePostTypeData = $fakePostTypeData[0] ?? [];
		if ( ! $fakePostTypeData ) {
			return $data;
		}

		if ( 'single' === $suffix ) {
			switch ( $postType ) {
				case 'bp-activity':
					$componentId = aioseo()->standalone->buddyPress->component->activity['id'];
					break;
				case 'bp-group':
					$componentId = aioseo()->standalone->buddyPress->component->group['id'];
					break;
				case 'bp-member':
					$componentId = aioseo()->standalone->buddyPress->component->author->ID;
					break;
				default:
					$componentId = 0;
			}
		}

		$scrollToId                   = 'aioseo-card-' . $postType . ( 'single' === $suffix ? 'SA' : 'ArchiveArchives' );
		$data['editGoogleSnippetUrl'] = 'single' === $suffix
			? admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/content-types'
			: admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/archives';
		$data['editGoogleSnippetUrl'] = add_query_arg( [
			'aioseo-scroll'    => $scrollToId,
			'aioseo-highlight' => $scrollToId
		], $data['editGoogleSnippetUrl'] );

		$data['editObjectBtnText'] = sprintf(
			// Translators: 1 - A noun for something that's being edited ("Post", "Page", "Article", "Product", etc.).
			esc_html__( 'Edit %1$s', 'all-in-one-seo-pack' ),
			'single' === $suffix ? $fakePostTypeData['singular'] : $fakePostTypeData['label']
		);

		list( , $component ) = explode( '-', $postType );

		$data['editObjectUrl'] = 'single' === $suffix
			? BuddyPressIntegration::getComponentEditUrl( $component, $componentId ?? 0 )
			: BuddyPressIntegration::callFunc( 'bp_get_admin_url', add_query_arg( 'page', 'bp-rewrites', 'admin.php' ) );

		return $data;
	}

	/**
	 * Retrieves the BuddyPress fake post types.
	 *
	 * @since 4.7.6
	 *
	 * @return array The BuddyPress fake post types.
	 */
	public function getFakePostTypes() {
		return [
			'bp-activity' => [
				'name'               => 'bp-activity',
				'label'              => sprintf(
					// Translators: 1 - The hard coded string 'BuddyPress'.
					_x( 'Activities (%1$s)', 'BuddyPress', 'all-in-one-seo-pack' ),
					'BuddyPress'
				),
				'singular'           => 'Activity',
				'icon'               => 'dashicons-buddicons-buddypress-logo',
				'hasExcerpt'         => false,
				'hasArchive'         => true,
				'hierarchical'       => false,
				'taxonomies'         => [],
				'slug'               => 'bp-activity',
				'buddyPress'         => true,
				'defaultTags'        => [
					'postTypes' => [
						'title'       => [
							'bp_activity_action',
							'separator_sa',
							'site_title',
						],
						'description' => [
							'bp_activity_content',
							'separator_sa'
						]
					]
				],
				'defaultTitle'       => '#bp_activity_action #separator_sa #site_title',
				'defaultDescription' => '#bp_activity_content',
			],
			'bp-group'    => [
				'name'               => 'bp-group',
				'label'              => sprintf(
					// Translators: 1 - The hard coded string 'BuddyPress'.
					_x( 'Groups (%1$s)', 'BuddyPress', 'all-in-one-seo-pack' ),
					'BuddyPress'
				),
				'singular'           => 'Group',
				'icon'               => 'dashicons-buddicons-buddypress-logo',
				'hasExcerpt'         => false,
				'hasArchive'         => true,
				'hierarchical'       => false,
				'taxonomies'         => [],
				'slug'               => 'bp-group',
				'buddyPress'         => true,
				'defaultTags'        => [
					'postTypes' => [
						'title'       => [
							'bp_group_name',
							'separator_sa',
							'site_title',
						],
						'description' => [
							'bp_group_description',
							'separator_sa'
						]
					]
				],
				'defaultTitle'       => '#bp_group_name #separator_sa #site_title',
				'defaultDescription' => '#bp_group_description',
			],
			'bp-member'   => [
				'name'               => 'bp-member',
				'label'              => sprintf(
					// Translators: 1 - The hard coded string 'BuddyPress'.
					_x( 'Members (%1$s)', 'BuddyPress', 'all-in-one-seo-pack' ),
					'BuddyPress'
				),
				'singular'           => 'Member',
				'icon'               => 'dashicons-buddicons-buddypress-logo',
				'hasExcerpt'         => false,
				'hasArchive'         => true,
				'hierarchical'       => false,
				'taxonomies'         => [],
				'slug'               => 'bp-member',
				'buddyPress'         => true,
				'defaultTags'        => [
					'postTypes' => [
						'title'       => [
							'author_name',
							'separator_sa',
							'site_title',
						],
						'description' => [
							'author_bio',
							'separator_sa'
						]
					]
				],
				'defaultTitle'       => '#author_name #separator_sa #site_title',
				'defaultDescription' => '#author_bio',
			],
		];
	}
}