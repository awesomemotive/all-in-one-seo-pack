<?php
namespace AIOSEO\Plugin\Common\Standalone\BuddyPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Tags class.
 *
 * @since 4.7.6
 */
class Tags {
	/**
	 * Class constructor.
	 *
	 * @since 4.7.6
	 */
	public function __construct() {
		aioseo()->tags->addContext( $this->getContexts() );
		aioseo()->tags->addTags( $this->getTags() );
	}

	/**
	 * Retrieves the contexts for BuddyPress.
	 *
	 * @since 4.7.6
	 *
	 * @return array An array of contextual data.
	 */
	public function getContexts() {
		return [
			'bp-activityTitle'              => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'post_date',
				'post_day',
				'post_month',
				'post_year',
				'separator_sa',
				'site_title',
				'tagline',
				'bp_activity_action',
				'bp_activity_content',
			],
			'bp-activityArchiveTitle'       => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
			],
			'bp-activityDescription'        => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'post_date',
				'post_day',
				'post_month',
				'post_year',
				'separator_sa',
				'site_title',
				'tagline',
				'bp_activity_action',
				'bp_activity_content',
			],
			'bp-activityArchiveDescription' => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
			],
			'bp-groupTitle'                 => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'post_date',
				'post_day',
				'post_month',
				'post_year',
				'separator_sa',
				'site_title',
				'tagline',
				'bp_group_name',
				'bp_group_description',
			],
			'bp-groupArchiveTitle'          => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
				'bp_group_type_singular_name',
				'bp_group_type_plural_name',
			],
			'bp-groupDescription'           => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'post_date',
				'post_day',
				'post_month',
				'post_year',
				'separator_sa',
				'site_title',
				'tagline',
				'bp_group_name',
				'bp_group_description',
			],
			'bp-groupArchiveDescription'    => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
				'bp_group_type_singular_name',
				'bp_group_type_plural_name',
			],
			'bp-memberTitle'                => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
			],
			'bp-memberArchiveTitle'         => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
			],
			'bp-memberDescription'          => [
				'author_first_name',
				'author_last_name',
				'author_name',
				'author_bio',
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
			],
			'bp-memberArchiveDescription'   => [
				'current_date',
				'current_day',
				'current_month',
				'current_year',
				'separator_sa',
				'site_title',
				'tagline',
				'archive_title',
			],
		];
	}

	/**
	 * Retrieves the custom tags for BuddyPress.
	 *
	 * @since 4.7.6
	 *
	 * @return array An array of tags.
	 */
	public function getTags() {
		return [
			[
				'id'          => 'bp_activity_action',
				'name'        => _x( 'Activity Action', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The activity action.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
			[
				'id'          => 'bp_activity_content',
				'name'        => _x( 'Activity Content', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The activity content.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
			[
				'id'          => 'bp_group_name',
				'name'        => _x( 'Group Name', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The group name.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
			[
				'id'          => 'bp_group_description',
				'name'        => _x( 'Group Description', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The group description.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
			[
				'id'          => 'bp_group_type_singular_name',
				'name'        => _x( 'Group Type Singular Name', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The group type singular name.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
			[
				'id'          => 'bp_group_type_plural_name',
				'name'        => _x( 'Group Type Plural Name', 'BuddyPress', 'all-in-one-seo-pack' ),
				'description' => _x( 'The group type plural name.', 'BuddyPress', 'all-in-one-seo-pack' ),
				'instance'    => $this,
			],
		];
	}

	/**
	 * Replace the tags in the string provided.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $string The string to look for tags in.
	 * @param  int    $id     The object ID.
	 * @return string         The string with tags replaced.
	 */
	public function replaceTags( $string, $id ) {
		if ( ! $string || ! preg_match( '/' . aioseo()->tags->denotationChar . '/', $string ) ) {
			return $string;
		}

		foreach ( array_unique( aioseo()->helpers->flatten( $this->getContexts() ) ) as $tag ) {
			$tagId   = aioseo()->tags->denotationChar . $tag;
			$pattern = "/$tagId(?![a-zA-Z0-9_])/im";
			if ( preg_match( $pattern, $string ) ) {
				$tagValue = $this->getTagValue( [ 'id' => $tag ], $id );
				$string   = preg_replace( $pattern, '%|%' . aioseo()->helpers->escapeRegexReplacement( $tagValue ), $string );
			}
		}

		return str_replace( '%|%', '', $string );
	}

	/**
	 * Get the value of the tag to replace.
	 *
	 * @since 4.7.6
	 *
	 * @param  array    $tag        The tag to look for.
	 * @param  int|null $id         The object ID.
	 * @param  bool     $sampleData Whether to fill empty values with sample data.
	 * @return string               The value of the tag.
	 */
	public function getTagValue( $tag, $id = null, $sampleData = false ) {
		$sampleData = $sampleData || empty( aioseo()->standalone->buddyPress->component->templateType );

		switch ( $tag['id'] ) {
			case 'author_bio':
				$out = $sampleData
					? __( 'Sample author biography', 'all-in-one-seo-pack' )
					: aioseo()->standalone->buddyPress->component->author->description;
				break;
			case 'author_first_name':
				$out = $sampleData
					? wp_get_current_user()->first_name
					: aioseo()->standalone->buddyPress->component->author->first_name;
				break;
			case 'author_last_name':
				$out = $sampleData
					? wp_get_current_user()->last_name
					: aioseo()->standalone->buddyPress->component->author->last_name;
				break;
			case 'author_name':
				$out = $sampleData
					? wp_get_current_user()->display_name
					: aioseo()->standalone->buddyPress->component->author->display_name;
				break;
			case 'post_date':
				$out = $sampleData
					? aioseo()->tags->formatDateAsI18n( date_i18n( 'U' ) )
					: aioseo()->tags->formatDateAsI18n( aioseo()->standalone->buddyPress->component->date );
				break;
			case 'post_day':
				$out = $sampleData
					? date_i18n( 'd' )
					: date( 'd', aioseo()->standalone->buddyPress->component->date );
				break;
			case 'post_month':
				$out = $sampleData
					? date_i18n( 'F' )
					: date( 'F', aioseo()->standalone->buddyPress->component->date );
				break;
			case 'post_year':
				$out = $sampleData
					? date_i18n( 'Y' )
					: date( 'Y', aioseo()->standalone->buddyPress->component->date );
				break;
			case 'archive_title':
				$out = $sampleData
					? __( 'Sample Archive Title', 'all-in-one-seo-pack' )
					: esc_html( get_the_title() );
				break;
			case 'bp_activity_action':
				$out = $sampleData
					? _x( 'Sample Activity Action', 'BuddyPress', 'all-in-one-seo-pack' )
					: aioseo()->standalone->buddyPress->component->activity['action'];
				break;
			case 'bp_activity_content':
				$out = $sampleData
					? _x( 'Sample activity content', 'BuddyPress', 'all-in-one-seo-pack' )
					: aioseo()->standalone->buddyPress->component->activity['content_rendered'];
				break;
			case 'bp_group_name':
				$out = $sampleData
					? _x( 'Sample Group Name', 'BuddyPress', 'all-in-one-seo-pack' )
					: aioseo()->standalone->buddyPress->component->group['name'];
				break;
			case 'bp_group_description':
				$out = $sampleData
					? _x( 'Sample group description', 'BuddyPress', 'all-in-one-seo-pack' )
					: aioseo()->standalone->buddyPress->component->group['description'];
				break;
			case 'bp_group_type_singular_name':
				$out = $sampleData ? _x( 'Sample Type Singular', 'BuddyPress', 'all-in-one-seo-pack' ) : '';
				if ( ! empty( aioseo()->standalone->buddyPress->component->groupType ) ) {
					$out = aioseo()->standalone->buddyPress->component->groupType['singular'];
				}
				break;
			case 'bp_group_type_plural_name':
				$out = $sampleData ? _x( 'Sample Type Plural', 'BuddyPress', 'all-in-one-seo-pack' ) : '';
				if ( ! empty( aioseo()->standalone->buddyPress->component->groupType ) ) {
					$out = aioseo()->standalone->buddyPress->component->groupType['plural'];
				}
				break;
			default:
				$out = aioseo()->tags->getTagValue( $tag, $id );
		}

		return $out ?? '';
	}
}