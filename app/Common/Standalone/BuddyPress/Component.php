<?php
namespace AIOSEO\Plugin\Common\Standalone\BuddyPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;
use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * BuddyPress Component class.
 *
 * @since 4.7.6
 */
class Component {
	/**
	 * The current component template type.
	 *
	 * @since 4.7.6
	 *
	 * @var string|null
	 */
	public $templateType = null;

	/**
	 * The component ID.
	 *
	 * @since 4.7.6
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * The component author.
	 *
	 * @since 4.7.6
	 *
	 * @var \WP_User|false
	 */
	public $author = false;

	/**
	 * The component date.
	 *
	 * @since 4.7.6
	 *
	 * @var int|false
	 */
	public $date = false;

	/**
	 * The activity single page data.
	 *
	 * @since 4.7.6
	 *
	 * @var array
	 */
	public $activity = [];

	/**
	 * The group single page data.
	 *
	 * @since 4.7.6
	 *
	 * @var array
	 */
	public $group = [];

	/**
	 * The type of the group archive page.
	 *
	 * @since 4.7.6
	 *
	 * @var array
	 */
	public $groupType = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.7.6
	 */
	public function __construct() {
		if ( is_admin() ) {
			return;
		}

		$this->setTemplateType();
		$this->setId();
		$this->setAuthor();
		$this->setDate();
		$this->setActivity();
		$this->setGroup();
		$this->setGroupType();
	}

	/**
	 * Sets the template type.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setTemplateType() {
		if ( BuddyPressIntegration::callFunc( 'bp_is_single_activity' ) ) {
			$this->templateType = 'bp-activity_single';
		} elseif ( BuddyPressIntegration::callFunc( 'bp_is_group' ) ) {
			$this->templateType = 'bp-group_single';
		} elseif (
			BuddyPressIntegration::callFunc( 'bp_is_user' ) &&
			false === BuddyPressIntegration::callFunc( 'bp_is_single_activity' )
		) {
			$this->templateType = 'bp-member_single';
		} elseif ( BuddyPressIntegration::callFunc( 'bp_is_activity_directory' ) ) {
			$this->templateType = 'bp-activity_archive';
		} elseif ( BuddyPressIntegration::callFunc( 'bp_is_members_directory' ) ) {
			$this->templateType = 'bp-member_archive';
		} elseif ( BuddyPressIntegration::callFunc( 'bp_is_groups_directory' ) ) {
			$this->templateType = 'bp-group_archive';
		} elseif (
			BuddyPressIntegration::callFunc( 'bp_is_current_action', 'feed' ) &&
			BuddyPressIntegration::callFunc( 'bp_is_activity_component' )
		) {
			$this->templateType = 'bp-activity_feed';
		}
	}

	/**
	 * Sets the component ID.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setId() {
		switch ( $this->templateType ) {
			case 'bp-activity_single':
				$id = get_query_var( 'bp_member_action' );
				break;
			case 'bp-group_single':
				$id = get_query_var( 'bp_group' );
				break;
			case 'bp-member_single':
				$id = get_query_var( 'bp_member' );
				break;
			default:
				$id = $this->id;
		}

		$this->id = $id;
	}

	/**
	 * Sets the component author.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setAuthor() {
		switch ( $this->templateType ) {
			case 'bp-activity_single':
				if ( ! $this->activity ) {
					$this->setActivity();
				}

				if ( $this->activity ) {
					$this->author = get_user_by( 'id', $this->activity['user_id'] );

					return;
				}

				break;
			case 'bp-group_single':
				if ( ! $this->group ) {
					$this->setGroup();
				}

				if ( $this->group ) {
					$this->author = get_user_by( 'id', $this->group['creator_id'] );

					return;
				}

				break;
			case 'bp-member_single':
				$this->author = get_user_by( 'slug', $this->id );

				return;
		}
	}

	/**
	 * Sets the component date.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setDate() {
		switch ( $this->templateType ) {
			case 'bp-activity_single':
				if ( ! $this->activity ) {
					$this->setActivity();
				}
				$date = strtotime( $this->activity['date_recorded'] );
				break;
			case 'bp-group_single':
				if ( ! $this->group ) {
					$this->setGroup();
				}
				$date = strtotime( $this->group['date_created'] );
				break;
			default:
				$date = $this->date;
		}

		$this->date = $date;
	}

	/**
	 * Sets the activity data.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setActivity() {
		if ( 'bp-activity_single' !== $this->templateType ) {
			return;
		}

		$activities = BuddyPressIntegration::callFunc( 'bp_activity_get_specific', [
			'activity_ids'     => [ $this->id ],
			'display_comments' => true
		] );
		if ( ! empty( $activities['activities'] ) ) {
			list( $activity ) = current( $activities );

			$this->activity = (array) $activity;

			// The `content_rendered` is AIOSEO specific.
			$this->activity['content_rendered'] = $this->activity['content'] ?? '';
			if ( ! empty( $this->activity['content'] ) ) {
				$this->activity['content_rendered'] = apply_filters( 'bp_get_activity_content', $this->activity['content'] );
			}

			return;
		}

		$this->resetComponent();
	}

	/**
	 * Sets the group data.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setGroup() {
		if ( 'bp-group_single' !== $this->templateType ) {
			return;
		}

		$group = BuddyPressIntegration::callFunc( 'bp_get_group_by', 'slug', $this->id );
		if ( ! empty( $group ) ) {
			$this->group = (array) $group;

			return;
		}

		$this->resetComponent();
	}

	/**
	 * Sets the group type.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function setGroupType() {
		if ( 'bp-group_archive' !== $this->templateType ) {
			return;
		}

		$type = BuddyPressIntegration::callFunc( 'bp_get_current_group_directory_type' );
		if ( ! $type ) {
			return;
		}

		$term = get_term_by( 'slug', $type, 'bp_group_type' );
		if ( ! $term ) {
			return;
		}

		$meta = get_metadata( 'term', $term->term_id );
		if ( ! $meta ) {
			return;
		}

		$this->groupType = [
			'singular' => $meta['bp_type_singular_name'][0] ?? '',
			'plural'   => $meta['bp_type_name'][0] ?? '',
		];
	}

	/**
	 * Resets some of the component properties.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	private function resetComponent() {
		$this->templateType = null;
		$this->id           = 0;
	}

	/**
	 * Retrieves the SEO metadata value.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $which The SEO metadata to get.
	 * @return string        The SEO metadata value.
	 */
	public function getMeta( $which ) {
		list( $postType, $suffix ) = explode( '_', $this->templateType );

		switch ( $which ) {
			case 'title':
				$meta = 'single' === $suffix
					? aioseo()->meta->title->getPostTypeTitle( $postType )
					: aioseo()->meta->title->getArchiveTitle( $postType );
				$meta = aioseo()->meta->description->helpers->bpSanitize( $meta, $this->id );
				break;
			case 'description':
				$meta = 'single' === $suffix
					? aioseo()->meta->description->getPostTypeDescription( $postType )
					: aioseo()->meta->description->getArchiveDescription( $postType );
				$meta = aioseo()->meta->description->helpers->bpSanitize( $meta, $this->id );
				break;
			case 'keywords':
				$meta = 'single' === $suffix
					? ''
					: aioseo()->meta->keywords->getArchiveKeywords( $postType );
				$meta = aioseo()->meta->keywords->prepareKeywords( $meta );
				break;
			case 'robots':
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();
				if ( 'single' === $suffix && $dynamicOptions->searchAppearance->postTypes->has( $postType ) ) {
					aioseo()->meta->robots->globalValues( [ 'postTypes', $postType ], true );
				} elseif ( $dynamicOptions->searchAppearance->archives->has( $postType ) ) {
					aioseo()->meta->robots->globalValues( [ 'archives', $postType ], true );
				}

				$meta = aioseo()->meta->robots->metaHelper();
				break;
			case 'canonical':
				$meta = '';
				if ( 'single' === $suffix ) {
					if ( 'bp-member' === $postType ) {
						$meta = BuddyPressIntegration::getComponentSingleUrl( 'member', $this->author->ID );
					} elseif ( 'bp-group' === $postType ) {
						$meta = BuddyPressIntegration::getComponentSingleUrl( 'group', $this->group['id'] );
					}
				}
				break;
			default:
				$meta = '';
		}

		return $meta;
	}

	/**
	 * Determines the schema type for the current component.
	 *
	 * @since 4.7.6
	 *
	 * @param  \AIOSEO\Plugin\Common\Schema\Context $contextInstance The Context class instance.
	 * @return void
	 */
	public function determineSchemaGraphsAndContext( $contextInstance ) {
		list( $postType ) = explode( '_', $this->templateType );

		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		if ( $dynamicOptions->searchAppearance->postTypes->has( $postType ) ) {
			$defaultType = $dynamicOptions->searchAppearance->postTypes->{$postType}->schemaType;
			switch ( $defaultType ) {
				case 'Article':
					aioseo()->schema->graphs[] = $dynamicOptions->searchAppearance->postTypes->{$postType}->articleType;
					break;
				case 'WebPage':
					aioseo()->schema->graphs[] = $dynamicOptions->searchAppearance->postTypes->{$postType}->webPageType;
					break;
				default:
					aioseo()->schema->graphs[] = $defaultType;
			}
		}

		switch ( $this->templateType ) {
			case 'bp-activity_single':
				$datePublished = $this->activity['date_recorded'];
				$contextUrl    = BuddyPressIntegration::getComponentSingleUrl( 'activity', $this->activity['id'] );

				break;
			case 'bp-group_single':
				$datePublished = $this->group['date_created'];
				$contextUrl    = BuddyPressIntegration::getComponentSingleUrl( 'group', $this->group['id'] );

				break;
			case 'bp-member_single':
				aioseo()->schema->graphs[] = 'ProfilePage';

				$contextUrl = BuddyPressIntegration::getComponentSingleUrl( 'member', $this->author->ID );

				break;
			case 'bp-activity_archive':
			case 'bp-group_archive':
			case 'bp-member_archive':
				list( , $component ) = explode( '-', $postType );

				$contextUrl     = BuddyPressIntegration::getComponentArchiveUrl( $component );
				$breadcrumbType = 'CollectionPage';

				break;
			default:
				break;
		}

		if ( ! empty( $datePublished ) ) {
			CommonGraphs\Article\NewsArticle::setOverwriteGraphData( [
				'properties' => compact( 'datePublished' )
			] );
		}

		if ( ! empty( $contextUrl ) ) {
			$name                = aioseo()->meta->title->getTitle();
			$description         = aioseo()->meta->description->getDescription();
			$breadcrumbPositions = [
				'name'        => $name,
				'description' => $description,
				'url'         => $contextUrl,
			];

			if ( ! empty( $breadcrumbType ) ) {
				$breadcrumbPositions['type'] = $breadcrumbType;
			}

			aioseo()->schema->context = [
				'name'        => $name,
				'description' => $description,
				'url'         => $contextUrl,
				'breadcrumb'  => $contextInstance->breadcrumb->setPositions( $breadcrumbPositions ),
			];
		}
	}

	/**
	 * Gets the breadcrumbs for the current component.
	 *
	 * @since 4.7.6
	 *
	 * @return array
	 */
	public function getCrumbs() {
		$crumbs = [];
		switch ( $this->templateType ) {
			case 'bp-activity_single':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb(
					BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'activity' ),
					BuddyPressIntegration::getComponentArchiveUrl( 'activity' )
				);
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( sanitize_text_field( $this->activity['action'] ) );
				break;
			case 'bp-group_single':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb(
					BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'groups' ),
					BuddyPressIntegration::getComponentArchiveUrl( 'group' )
				);
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( $this->group['name'] );
				break;
			case 'bp-member_single':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb(
					BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'members' ),
					BuddyPressIntegration::getComponentArchiveUrl( 'member' )
				);
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( $this->author->display_name );
				break;
			case 'bp-activity_archive':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'activity' ) );
				break;
			case 'bp-group_archive':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'groups' ) );
				break;
			case 'bp-member_archive':
				$crumbs[] = aioseo()->breadcrumbs->makeCrumb( BuddyPressIntegration::callFunc( 'bp_get_directory_title', 'members' ) );
				break;
			default:
				break;
		}

		return $crumbs;
	}
}