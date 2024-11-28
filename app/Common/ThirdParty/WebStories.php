<?php
namespace AIOSEO\Plugin\Common\ThirdParty;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrates with Google Web Stories plugin.
 *
 * @since {next)
 */
class WebStories {
	/**
	 * Class constructor.
	 *
	 * @since 4.7.6
	 */
	public function __construct() {
		add_action( 'web_stories_story_head', [ $this, 'stripDefaultTags' ], 0 );
		add_action( 'web_stories_story_head', [ $this, 'outputAioseoTags' ] );
	}

	/**
	 * Strip all meta tags that are added by default by the Web Stories plugin.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	public function stripDefaultTags() {
		add_filter( 'web_stories_enable_metadata', '__return_false' );
		add_filter( 'web_stories_enable_schemaorg_metadata', '__return_false' );
		add_filter( 'web_stories_enable_open_graph_metadata', '__return_false' );
		add_filter( 'web_stories_enable_twitter_metadata', '__return_false' );

		remove_action( 'web_stories_story_head', 'rel_canonical' );
		remove_action( 'web_stories_story_head', 'wp_robots' );

		// This is needed to prevent multiple robots meta tags from being output.
		add_filter( 'wp_robots', '__return_empty_array' );
	}

	/**
	 * Output the AIOSEO tags.
	 *
	 * @since 4.7.6
	 *
	 * @return void
	 */
	public function outputAioseoTags() {
		aioseo()->head->wpHead();
	}

	/**
	 * Checks if the plugin is active.
	 *
	 * @since 4.7.6
	 *
	 * @return bool True if the plugin is active.
	 */
	public function isPluginActive() {
		return class_exists( 'Google\Web_Stories\Plugin' );
	}
}