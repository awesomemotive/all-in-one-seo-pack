<?php

/**
 * @return bool
 */
function oauth_init() {
	if ( ! is_user_logged_in() || ! current_user_can( 'aiosp_manage_seo' ) ) {
		return false;
	}
	$this->token  = 'anonymous';
	$this->secret = 'anonymous';
	$preload      = $this->get_class_option();
	$manual_ua    = '';
	if ( ! empty( $_POST ) ) {
		if ( ! empty( $_POST[ "{$this->prefix}google_connect" ] ) ) {
			$manual_ua = 1;
		}
	} elseif ( ! empty( $preload[ "{$this->prefix}google_connect" ] ) ) {
		$manual_ua = 1;
	}
	if ( ! empty( $manual_ua ) ) {
		foreach ( array( 'token', 'secret', 'access_token', 'ga_token', 'account_cache' ) as $v ) {
			if ( ! empty( $preload[ "{$this->prefix}{$v}" ] ) ) {
				unset( $preload[ "{$this->prefix}{$v}" ] );
				unset( $this->$v );
			}
		}
		$this->update_class_option( $preload );
		$this->update_options();
		// return;
	}
	foreach ( array( 'token', 'secret', 'access_token', 'ga_token', 'account_cache' ) as $v ) {
		if ( ! empty( $preload[ "{$this->prefix}{$v}" ] ) ) {
			$this->$v = $preload[ "{$this->prefix}{$v}" ];
		}
	}
	$callback_url = null;
	if ( ! empty( $_REQUEST['oauth_verifier'] ) ) {
		$this->verifier = $_REQUEST['oauth_verifier'];
		if ( ! empty( $_REQUEST['oauth_token'] ) ) {
			if ( isset( $this->token ) && $this->token === $_REQUEST['oauth_token'] ) {
				$this->access_token = $this->oauth_get_token( $this->verifier );
				if ( is_array( $this->access_token ) && ! empty( $this->access_token['oauth_token'] ) ) {
					unset( $this->token );
					unset( $this->secret );
					$this->ga_token = $this->access_token['oauth_token'];
					foreach ( array( 'token', 'secret', 'access_token', 'ga_token' ) as $v ) {
						if ( ! empty( $this->$v ) ) {
							$preload[ "{$this->prefix}{$v}" ] = $this->$v;
						}
					}
					$this->update_class_option( $preload );
				}
			}
			wp_redirect( menu_page_url( plugin_basename( $this->file ), false ) );
			exit;
		}
	}
	if ( ! empty( $this->ga_token ) ) {
		if ( ! empty( $this->account_cache ) ) {
			$ua       = $this->account_cache['ua'];
			$profiles = $this->account_cache['profiles'];
		} else {
			$this->token  = $this->access_token['oauth_token'];
			$this->secret = $this->access_token['oauth_token_secret'];

			$data = $this->oauth_get_data( 'https://www.googleapis.com/analytics/v2.4/management/accounts/~all/webproperties/~all/profiles' );

			$http_code = wp_remote_retrieve_response_code( $data );

			if ( 200 === $http_code ) {
				$response = wp_remote_retrieve_body( $data );
				$xml      = $this->xml_string_to_array( $response );
				$ua       = array();
				$profiles = array();
				if ( ! empty( $xml['entry'] ) ) {
					$rec     = array();
					$results = array();
					if ( ! empty( $xml['entry'][0] ) ) {
						$results = $xml['entry'];
					} else {
						$results[] = $xml['entry'];
					}
					foreach ( $results as $r ) {
						foreach ( $r as $k => $v ) {
							switch ( $k ) {
								case 'id':
									$rec['id'] = $v;
									break;
								case 'title':
									$rec['title'] = $v['@content'];
									break;
								case 'dxp:property':
									$attr = array();
									foreach ( $v as $a => $f ) {
										if ( is_array( $f ) && ! empty( $f['@attributes'] ) ) {
											$rec[ $f['@attributes']['name'] ] = $f['@attributes']['value'];
										}
									}
									break;
							}
						}
						$ua[ $rec['title'] ]                  = array( $rec['ga:webPropertyId'] => $rec['ga:webPropertyId'] );
						$profiles[ $rec['ga:webPropertyId'] ] = $rec['ga:profileId'];
					}
				}
				$this->account_cache                     = array();
				$this->account_cache['ua']               = $ua;
				$this->account_cache['profiles']         = $profiles;
				$preload[ "{$this->prefix}account_cache" ] = $this->account_cache;
			} else {
				unset( $this->token );
				unset( $this->secret );
				unset( $this->ga_token );
				unset( $preload[ "{$this->prefix}ga_token" ] ); // error condition here -- pdb
				$response = wp_remote_retrieve_body( $data );
				$xml      = $this->xml_string_to_array( $response );
				if ( ! empty( $xml ) && ! empty( $xml['error'] ) ) {
					$error = 'Error: ';
					if ( ! empty( $xml['error']['internalReason'] ) ) {
						$error .= $xml['error']['internalReason'];
					} else {
						foreach ( $xml['error'] as $k => $v ) {
							$error .= "$k: $v\n";
						}
					}
					$this->output_error( $error );
				}
			}
		}
	}
	if ( ! empty( $this->ga_token ) ) {
		$this->default_options['google_analytics_id']['type']            = 'select';
		$this->default_options['google_analytics_id']['initial_options'] = $ua;
		$this->default_options['google_connect']['type']                 = 'html';
		$this->default_options['google_connect']['nolabel']              = 1;
		$this->default_options['google_connect']['save']                 = true;
		$this->default_options['google_connect']['name']                 = __( 'Disconnect From Google Analytics', 'all-in-one-seo-pack' );
		$this->default_options['google_connect']['default']              = "<input name='aiosp_google_connect' type=submit  class='button-primary' value='" . __( 'Remove Stored Credentials', 'all-in-one-seo-pack' ) . "'>";
		add_filter( $this->prefix . 'override_options', array( $this, 'override_options' ), 10, 3 );
	} else {
		$this->default_options['google_connect']['type']    = 'html';
		$this->default_options['google_connect']['nolabel'] = 1;
		$this->default_options['google_connect']['save']    = false;
		$url                                                = $this->oauth_connect();
		$this->default_options['google_connect']['default'] = "<a href='{$url}' class='button-primary'>" . __( 'Connect With Google Analytics', 'all-in-one-seo-pack' ) . '</a>';
		foreach ( array( 'token', 'secret', 'access_token', 'ga_token', 'account_cache' ) as $v ) {
			if ( ! empty( $this->$v ) ) {
				$preload[ "{$this->prefix}{$v}" ] = $this->$v;
			}
		}
	}
	$this->update_class_option( $preload );
	$this->update_options();
	// $url = $this->report_query();
	if ( ! empty( $this->account_cache ) && ! empty( $this->options[ "{$this->prefix}google_analytics_id" ] ) && ! empty( $this->account_cache['profiles'][ $this->options[ "{$this->prefix}google_analytics_id" ] ] ) ) {
		$this->profile_id = $this->account_cache['profiles'][ $this->options[ "{$this->prefix}google_analytics_id" ] ];
	}
}

/**
 * @param $oauth_url
 * @param null $args
 *
 * @return array|WP_Error
 */
function oauth_get_data( $oauth_url, $args = null ) {
	if ( ! class_exists( 'OAuthConsumer' ) ) {
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/extlib/OAuth.php' );
	}
	if ( $args === null ) {
		$args = array(
			'scope'              => 'https://www.googleapis.com/auth/analytics.readonly',
			'xoauth_displayname' => AIOSEOP_PLUGIN_NAME . ' ' . __( 'Google Analytics', 'all-in-one-seo-pack' ),
		);
	}
	$req_token = new OAuthConsumer( $this->token, $this->secret );
	$req       = $this->oauth_get_creds( $oauth_url, $req_token, $args );

	return wp_remote_get( $req->to_url() );
}

/**
 * @param $oauth_url
 * @param null $req_token
 * @param array $args
 * @param null $callback
 *
 * @return OAuthRequest
 */
function oauth_get_creds( $oauth_url, $req_token = null, $args = array(), $callback = null ) {
	if ( ! class_exists( 'OAuthConsumer' ) ) {
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/extlib/OAuth.php' );
	}
	if ( ! empty( $callback ) ) {
		$args['oauth_callback'] = $callback;
	}
	if ( empty( $this->sig_method ) ) {
		$this->sig_method = new OAuthSignatureMethod_HMAC_SHA1();
	}
	if ( empty( $this->consumer ) ) {
		$this->consumer = new OAuthCOnsumer( 'anonymous', 'anonymous' );
	}
	$req_req = OAuthRequest::from_consumer_and_token( $this->consumer, $req_token, 'GET', $oauth_url, $args );
	$req_req->sign_request( $this->sig_method, $this->consumer, $req_token );

	return $req_req;
}

/**
 * @param $oauth_verifier
 *
 * @return array
 */
function oauth_get_token( $oauth_verifier ) {
	if ( ! class_exists( 'OAuthConsumer' ) ) {
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/extlib/OAuth.php' );
	}
	$args                   = array(
		'scope'              => 'https://www.google.com/analytics/feeds/',
		'xoauth_displayname' => AIOSEOP_PLUGIN_NAME . ' ' . __( 'Google Analytics', 'all-in-one-seo-pack' ),
	);
	$args['oauth_verifier'] = $oauth_verifier;
	$oauth_access_token     = 'https://www.google.com/accounts/OAuthGetAccessToken';
	$reqData                = $this->oauth_get_data( $oauth_access_token, $args );
	$reqOAuthData           = OAuthUtil::parse_parameters( wp_remote_retrieve_body( $reqData ) );

	return $reqOAuthData;
}

/**
 * @param int $count
 *
 * @return string
 */
function oauth_connect( $count = 0 ) {
	global $aiosp_activation;
	if ( ! class_exists( 'OAuthConsumer' ) ) {
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/extlib/OAuth.php' );
	}
	$url                 = '';
	$callback_url        = null;
	$consumer_key        = 'anonymous';
	$consumer_secret     = 'anonymous';
	$oauth_request_token = 'https://www.google.com/accounts/OAuthGetRequestToken';
	$oauth_authorize     = 'https://www.google.com/accounts/OAuthAuthorizeToken';
	$oauth_access_token  = 'https://www.google.com/accounts/OAuthGetAccessToken';
	if ( $aiosp_activation ) {
		$oauth_current = false;
	} else {
		$oauth_current = get_transient( 'aioseop_oauth_current' );
	}
	if ( ! empty( $this->token ) && ( $this->token != 'anonymous' ) && $oauth_current ) {
		return $oauth_authorize . '?oauth_token=' . $this->token;
	} else {
		set_transient( 'aioseop_oauth_current', 1, 3600 );
		unset( $this->token );
		unset( $this->secret );
	}
	$args = array(
		'scope'              => 'https://www.google.com/analytics/feeds/',
		'xoauth_displayname' => AIOSEOP_PLUGIN_NAME . ' ' . __( 'Google Analytics', 'all-in-one-seo-pack' ),
	);
	if ( AIOSEOPPRO ) {
		$req_req = $this->oauth_get_creds( $oauth_request_token, null, $args, admin_url( 'admin.php?page=all-in-one-seo-pack-pro/aioseop_class.php' ) );
	} else {
		$req_req = $this->oauth_get_creds( $oauth_request_token, null, $args, admin_url( 'admin.php?page=all-in-one-seo-pack/aioseop_class.php' ) );
	}
	$reqData      = wp_remote_get( $req_req->to_url() );
	$reqOAuthData = OAuthUtil::parse_parameters( wp_remote_retrieve_body( $reqData ) );
	if ( ! empty( $reqOAuthData['oauth_token'] ) ) {
		$this->token = $reqOAuthData['oauth_token'];
	}
	if ( ! empty( $reqOAuthData['oauth_token_secret'] ) ) {
		$this->secret = $reqOAuthData['oauth_token_secret'];
	}
	if ( ! empty( $this->token ) && ( $this->token != 'anonymous' ) && $oauth_current ) {
		$url = $oauth_authorize . "?oauth_token={$this->token}";
	} else {
		if ( ! $count ) {
			return $this->oauth_connect( 1 );
		}
	}

	return $url;
}
