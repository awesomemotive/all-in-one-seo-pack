<?php

if ( ! class_exists( 'AIOSEOP_Opengraph_Public' ) ) {

	/**
	 * Class aioseop_opengraph_public.
	 *
	 * Handles the public-facing duties of opengraph things
	 *
	 * @since 2.3.5
	 *
	 */
	class AIOSEOP_Opengraph_Public {

		/**
		 * Prepare twitter username for public display.
		 *
		 * We do things like strip out the URL, etc and return just (at)username.
		 * At the moment, we'll check for 1 of 3 things... (at)username, username, and https://twitter.com/username.
		 * In the future, we'll need to start validating the information on the way in, so we don't have to do it one the way out.
		 *
		 * @param $twitter_profile
		 *
		 * @return string
		 * @since 2.3.5
		 */
		public static function prepare_twitter_username( $twitter_profile ) {

			// $twitter_profile = 'https://twitter.com/sdfsdfsf'; //testing purposes only, remove for release
			// test for valid twitter username, with or without @
			if ( preg_match( '/^(\@)?[A-Za-z0-9_]+$/', $twitter_profile ) ) {

				$twitter_profile = self::prepend_at_symbol( $twitter_profile );

				return $twitter_profile;
			}

			// check if it has twitter.com
			if ( strpos( $twitter_profile, 'twitter.com' ) ) {

				$twitter_profile = esc_url( $twitter_profile );

				$new_profile = self::twitter_url_to_user( $twitter_profile );

				if ( $new_profile ) {
					$new_profile = self::prepend_at_symbol( $new_profile );

					return $new_profile;

				}
			}

			// if all else fails, just send it back
			return $twitter_profile;

		}


		/**
		 * @param $twitter_profile
		 *
		 * @return mixed
		 * @since 2.3.5
		 * @since 2.3.5.1 Bail if < WordPress 4.4 for now.
		 */
		public static function twitter_url_to_user( $twitter_profile ) {

			// If they're below 4.4 (no wp_parse_url) bail. We can improve this for <4.4 later or just hold out until we drop 4.4 support.
			if ( ! function_exists( 'wp_parse_url' ) ) {
				return $twitter_profile;
			}

			// extract the twitter username from the url
			$parsed_twitter_profile = wp_parse_url( $twitter_profile );

			$path            = $parsed_twitter_profile['path'];
			$path_parts      = explode( '/', $path );
			$twitter_profile = $path_parts[1];

			return $twitter_profile;

		}


		/**
		 * @param $twitter_profile
		 *
		 * @return string
		 * @since 2.3.5
		 *
		 * TODO- this claims to just validate, but it's actually validating and adding the AT symbol as needed rather than returning true/false
		 */
		public static function validate_twitter_profile( $twitter_profile ) {
			// test for valid twitter username, with or without @
			if ( preg_match( '/^(\@)?[A-Za-z0-9_]+$/', $twitter_profile ) ) {

				$twitter_profile = self::prepend_at_symbol( $twitter_profile );

				return $twitter_profile;
			}
		}


		/**
		 * @param $twitter_profile
		 *
		 * @return string
		 * @since 2.3.5
		 */
		public static function prepend_at_symbol( $twitter_profile ) {
			// checks for @ in the beginning, if it's not there adds it
			if ( '@' !== $twitter_profile[0] ) {
				$twitter_profile = '@' . $twitter_profile;
			}

			return $twitter_profile;
		}
	}
}
