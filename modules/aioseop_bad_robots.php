<?php
/**
 * Bad Robots Module
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Bad_Robots' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Bad_Robots
	 */
	class All_in_One_SEO_Pack_Bad_Robots extends All_in_One_SEO_Pack_Module {

		/**
		 * All_in_One_SEO_Pack_Bad_Robots constructor.
		 */
		function __construct() {
			$this->name   = __( 'Bad Bot Blocker', 'all-in-one-seo-pack' );    // Human-readable name of the plugin.
			$this->prefix = 'aiosp_bad_robots_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			parent::__construct();

			$this->default_options = array(
				'block_bots'   => array( 'name' => __( 'Block Bad Bots using HTTP', 'all-in-one-seo-pack' ) ),
				'block_refer'  => array( 'name' => __( 'Block Referral Spam using HTTP', 'all-in-one-seo-pack' ) ),
				'track_blocks' => array( 'name' => __( 'Track Blocked Bots', 'all-in-one-seo-pack' ) ),
				'edit_blocks'  => array( 'name' => __( 'Use Custom Blocklists', 'all-in-one-seo-pack' ) ),
				'blocklist'    => array(
					'name'     => __( 'User Agent Blocklist', 'all-in-one-seo-pack' ),
					'type'     => 'textarea',
					'rows'     => 5,
					'cols'     => 120,
					'condshow' => array( "{$this->prefix}edit_blocks" => 'on' ),
					'default'  => join( "\n", $this->default_bad_bots() ),
				),
				'referlist'    => array(
					'name'     => __( 'Referer Blocklist', 'all-in-one-seo-pack' ),
					'type'     => 'textarea',
					'rows'     => 5,
					'cols'     => 120,
					'condshow' => array(
						"{$this->prefix}edit_blocks" => 'on',
						"{$this->prefix}block_refer" => 'on',
					),
					'default'  => join( "\n", $this->default_bad_referers() ),
				),
				'blocked_log'  => array(
					'name'     => __( 'Log Of Blocked Bots', 'all-in-one-seo-pack' ),
					'default'  => __( 'No requests yet.', 'all-in-one-seo-pack' ),
					'type'     => 'esc_html',
					'disabled' => 'disabled',
					'save'     => false,
					'label'    => 'top',
					'rows'     => 5,
					'cols'     => 120,
					'style'    => 'min-width:950px',
					'condshow' => array( "{$this->prefix}track_blocks" => 'on' ),
				),
			);

			add_filter( $this->prefix . 'display_options', array( $this, 'filter_display_options' ) );

			// Load initial options / set defaults.
			$this->update_options();

			if ( $this->option_isset( 'edit_blocks' ) ) {
				add_filter( $this->prefix . 'badbotlist', array( $this, 'filter_bad_botlist' ) );
				if ( $this->option_isset( 'block_refer' ) ) {
					add_filter( $this->prefix . 'badreferlist', array( $this, 'filter_bad_referlist' ) );
				}
			}

			if ( $this->option_isset( 'block_bots' ) ) {
				if ( ! $this->allow_bot() ) {
					status_header( 503 );
					$ip         = $this->validate_ip( $_SERVER['REMOTE_ADDR'] );
					$user_agent = $_SERVER['HTTP_USER_AGENT'];
					$this->blocked_message( sprintf( __( 'Blocked bot with IP %1$s -- matched user agent %2$s found in blocklist.', 'all-in-one-seo-pack' ), $ip, $user_agent ) );
					exit();
				} elseif ( $this->option_isset( 'block_refer' ) && $this->is_bad_referer() ) {
					status_header( 503 );
					$ip      = $this->validate_ip( $_SERVER['REMOTE_ADDR'] );
					$referer = $_SERVER['HTTP_REFERER'];
					$this->blocked_message( sprintf( __( 'Blocked bot with IP %1$s -- matched referer %2$s found in blocklist.', 'all-in-one-seo-pack' ), $ip, $referer ) );
				}
			}
		}

		/**
		 * Validate IP.
		 *
		 * @since 2.3.7
		 *
		 * @param $ip
		 * @return string
		 */
		function validate_ip( $ip ) {

			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				// Valid IPV4.
				return $ip;
			}

			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				// Valid IPV6.
				return $ip;
			}

			// Doesn't seem to be a valid IP.
			return 'invalid IP submitted';

		}

		/**
		 * Filter Bad Refer List
		 *
		 * @since ?
		 *
		 * @param $referlist
		 * @return array
		 */
		function filter_bad_referlist( $referlist ) {
			if ( $this->option_isset( 'edit_blocks' ) && $this->option_isset( 'block_refer' ) && $this->option_isset( 'referlist' ) ) {
				$referlist = preg_split( '/\r\n|[\r\n]/', $this->options[ "{$this->prefix}referlist" ] );
			}

			return $referlist;
		}

		/**
		 * Filter Bad Bot List
		 *
		 * @since ?
		 *
		 * @param $botlist
		 * @return array
		 */
		function filter_bad_botlist( $botlist ) {
			if ( $this->option_isset( 'edit_blocks' ) && $this->option_isset( 'blocklist' ) ) {
				$botlist = preg_split( '/\r\n|[\r\n]/', $this->options[ "{$this->prefix}blocklist" ] );
			}

			return $botlist;
		}


		/**
		 * Blocked Message
		 *
		 * Updates blocked message.
		 *
		 * @since 2.3.11.1
		 *
		 * @param string $msg
		 */
		function blocked_message( $msg ) {

			if ( ! $this->option_isset( 'track_blocks' ) ) {
				return; // Only log if track blocks is checked.
			}

			if ( empty( $this->options[ "{$this->prefix}blocked_log" ] ) ) {
				$this->options[ "{$this->prefix}blocked_log" ] = '';
			}
			$this->options[ "{$this->prefix}blocked_log" ] = date( 'Y-m-d H:i:s' ) . " {$msg}\n" . $this->options[ "{$this->prefix}blocked_log" ];
			if ( $this->strlen( $this->options[ "{$this->prefix}blocked_log" ] ) > 4096 ) {
				$end = $this->strrpos( $this->options[ "{$this->prefix}blocked_log" ], "\n" );
				if ( false === $end ) {
					$end = 4096;
				}
				$this->options[ "{$this->prefix}blocked_log" ] = $this->substr( $this->options[ "{$this->prefix}blocked_log" ], 0, $end );
			}
			$this->update_class_option( $this->options );
		}


		/**
		 * Filter display options.
		 *
		 * Add in options for status display on settings page, sitemap rewriting on multisite.
		 *
		 * @since ?
		 *
		 * @param $options
		 * @return mixed
		 */
		function filter_display_options( $options ) {

			if ( $this->option_isset( 'blocked_log' ) ) {
				if ( preg_match( '/\<(\?php|script)/', $options[ "{$this->prefix}blocked_log" ] ) ) {
					$options[ "{$this->prefix}blocked_log" ] = "Probable XSS attempt detected!\n" . $options[ "{$this->prefix}blocked_log" ];
				}
			}

			return $options;
		}
	}
}
