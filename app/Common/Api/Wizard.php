<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Wizard {
	/**
	 * Save the wizard information.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveWizard( $request ) {
		$body           = $request->get_json_params();
		$section        = ! empty( $body['section'] ) ? sanitize_text_field( $body['section'] ) : null;
		$wizard         = ! empty( $body['wizard'] ) ? $body['wizard'] : null;
		$network        = ! empty( $body['network'] ) ? $body['network'] : false;
		$options        = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		aioseo()->internalOptions->internal->wizard = wp_json_encode( $wizard );

		// Process the importers.
		if ( 'importers' === $section && ! empty( $wizard['importers'] ) ) {
			$importers = $wizard['importers'];

			try {
				foreach ( $importers as $plugin ) {
					aioseo()->importExport->startImport( $plugin, [
						'settings',
						'postMeta',
						'termMeta'
					] );
				}
			} catch ( \Exception $e ) {
				// Import failed. Let's create a notification but move on.
				$notification = Models\Notification::getNotificationByName( 'import-failed' );
				if ( ! $notification->exists() ) {
					Models\Notification::addNotification( [
						'slug'              => uniqid(),
						'notification_name' => 'import-failed',
						'title'             => __( 'SEO Plugin Import Failed', 'all-in-one-seo-pack' ),
						'content'           => __( 'Unfortunately, there was an error importing your SEO plugin settings. This could be due to an incompatibility in the version installed. Make sure you are on the latest version of the plugin and try again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						'type'              => 'error',
						'level'             => [ 'all' ],
						'button1_label'     => __( 'Try Again', 'all-in-one-seo-pack' ),
						'button1_action'    => 'http://route#aioseo-tools&aioseo-scroll=aioseo-import-others&aioseo-highlight=aioseo-import-others:import-export',
						'start'             => gmdate( 'Y-m-d H:i:s' )
					] );
				}
			}
		}

		// Save the category section.
		if (
			( 'category' === $section || 'searchAppearance' === $section ) && // We allow the user to update the site title/description in search appearance.
			! empty( $wizard['category'] )
		) {
			$category = $wizard['category'];
			if ( ! empty( $category['category'] ) ) {
				aioseo()->internalOptions->internal->category = $category['category'];
			}

			if ( ! empty( $category['categoryOther'] ) ) {
				aioseo()->internalOptions->internal->categoryOther = $category['categoryOther'];
			}

			// If the home page is a static page, let's find and set that,
			// otherwise set our home page settings.
			$staticHomePage = 'page' === get_option( 'show_on_front' ) ? get_post( get_option( 'page_on_front' ) ) : null;
			if ( ! empty( $staticHomePage ) ) {
				$update = false;
				$page   = Models\Post::getPost( $staticHomePage->ID );
				if ( ! empty( $category['siteTitle'] ) ) {
					$update      = true;
					$page->title = $category['siteTitle'];
				}

				if ( ! empty( $category['metaDescription'] ) ) {
					$update            = true;
					$page->description = $category['metaDescription'];
				}

				if ( $update ) {
					$page->save();
				}
			}

			if ( empty( $staticHomePage ) ) {
				if ( ! empty( $category['siteTitle'] ) ) {
					$options->searchAppearance->global->siteTitle = $category['siteTitle'];
				}

				if ( ! empty( $category['metaDescription'] ) ) {
					$options->searchAppearance->global->metaDescription = $category['metaDescription'];
				}
			}
		}

		// Save the additional information section.
		if ( 'additionalInformation' === $section && ! empty( $wizard['additionalInformation'] ) ) {
			$additionalInformation = $wizard['additionalInformation'];
			if ( ! empty( $additionalInformation['siteRepresents'] ) ) {
				$options->searchAppearance->global->schema->siteRepresents = $additionalInformation['siteRepresents'];
			}

			if ( ! empty( $additionalInformation['person'] ) ) {
				$options->searchAppearance->global->schema->person = $additionalInformation['person'];
			}

			if ( ! empty( $additionalInformation['organizationName'] ) ) {
				$options->searchAppearance->global->schema->organizationName = $additionalInformation['organizationName'];
			}

			if ( ! empty( $additionalInformation['phone'] ) ) {
				$options->searchAppearance->global->schema->phone = $additionalInformation['phone'];
			}

			if ( ! empty( $additionalInformation['organizationLogo'] ) ) {
				$options->searchAppearance->global->schema->organizationLogo = $additionalInformation['organizationLogo'];
			}

			if ( ! empty( $additionalInformation['personName'] ) ) {
				$options->searchAppearance->global->schema->personName = $additionalInformation['personName'];
			}

			if ( ! empty( $additionalInformation['personLogo'] ) ) {
				$options->searchAppearance->global->schema->personLogo = $additionalInformation['personLogo'];
			}

			if ( ! empty( $additionalInformation['contactType'] ) ) {
				$options->searchAppearance->global->schema->contactType = $additionalInformation['contactType'];
			}

			if ( ! empty( $additionalInformation['contactTypeManual'] ) ) {
				$options->searchAppearance->global->schema->contactTypeManual = $additionalInformation['contactTypeManual'];
			}

			if ( ! empty( $additionalInformation['socialShareImage'] ) ) {
				$options->social->facebook->general->defaultImagePosts = $additionalInformation['socialShareImage'];
				$options->social->twitter->general->defaultImagePosts  = $additionalInformation['socialShareImage'];
			}

			if ( ! empty( $additionalInformation['social'] ) && ! empty( $additionalInformation['social']['profiles'] ) ) {
				$profiles = $additionalInformation['social']['profiles'];
				if ( ! empty( $profiles['sameUsername'] ) ) {
					$sameUsername = $profiles['sameUsername'];
					if ( isset( $sameUsername['enable'] ) ) {
						$options->social->profiles->sameUsername->enable = $sameUsername['enable'];
					}

					if ( ! empty( $sameUsername['username'] ) ) {
						$options->social->profiles->sameUsername->username = $sameUsername['username'];
					}

					if ( ! empty( $sameUsername['included'] ) ) {
						$options->social->profiles->sameUsername->included = $sameUsername['included'];
					}
				}

				if ( ! empty( $profiles['urls'] ) ) {
					$urls = $profiles['urls'];
					if ( ! empty( $urls['facebookPageUrl'] ) ) {
						$options->social->profiles->urls->facebookPageUrl = $urls['facebookPageUrl'];
					}

					if ( ! empty( $urls['twitterUrl'] ) ) {
						$options->social->profiles->urls->twitterUrl = $urls['twitterUrl'];
					}

					if ( ! empty( $urls['instagramUrl'] ) ) {
						$options->social->profiles->urls->instagramUrl = $urls['instagramUrl'];
					}

					if ( ! empty( $urls['tiktokUrl'] ) ) {
						$options->social->profiles->urls->tiktokUrl = $urls['tiktokUrl'];
					}

					if ( ! empty( $urls['pinterestUrl'] ) ) {
						$options->social->profiles->urls->pinterestUrl = $urls['pinterestUrl'];
					}

					if ( ! empty( $urls['youtubeUrl'] ) ) {
						$options->social->profiles->urls->youtubeUrl = $urls['youtubeUrl'];
					}

					if ( ! empty( $urls['linkedinUrl'] ) ) {
						$options->social->profiles->urls->linkedinUrl = $urls['linkedinUrl'];
					}

					if ( ! empty( $urls['tumblrUrl'] ) ) {
						$options->social->profiles->urls->tumblrUrl = $urls['tumblrUrl'];
					}

					if ( ! empty( $urls['yelpPageUrl'] ) ) {
						$options->social->profiles->urls->yelpPageUrl = $urls['yelpPageUrl'];
					}

					if ( ! empty( $urls['soundCloudUrl'] ) ) {
						$options->social->profiles->urls->soundCloudUrl = $urls['soundCloudUrl'];
					}

					if ( ! empty( $urls['wikipediaUrl'] ) ) {
						$options->social->profiles->urls->wikipediaUrl = $urls['wikipediaUrl'];
					}

					if ( ! empty( $urls['myspaceUrl'] ) ) {
						$options->social->profiles->urls->myspaceUrl = $urls['myspaceUrl'];
					}

					if ( ! empty( $urls['googlePlacesUrl'] ) ) {
						$options->social->profiles->urls->googlePlacesUrl = $urls['googlePlacesUrl'];
					}
				}
			}

			return new \WP_REST_Response( [
				'success' => true
			], 200 );
		}

		// Save the features section.
		if ( 'features' === $section && ! empty( $wizard['features'] ) ) {
			$features   = $wizard['features'];
			$pluginData = aioseo()->helpers->getPluginData();

			// Install MI.
			if ( in_array( 'analytics', $features, true ) ) {
				$cantInstall = false;
				if ( ! $pluginData['miPro']['activated'] && ! $pluginData['miLite']['activated'] ) {
					if ( $pluginData['miPro']['installed'] ) {
						aioseo()->addons->installAddon( 'miPro', $network );

						// Stop the redirect from happening.
						delete_transient( '_monsterinsights_activation_redirect' );
					} else {
						if ( $pluginData['miPro']['installed'] || aioseo()->addons->canInstall() ) {
							aioseo()->addons->installAddon( 'miLite', $network );

							// Stop the redirect from happening.
							delete_transient( '_monsterinsights_activation_redirect' );
						} else {
							$cantInstall = true;
						}
					}
				}

				if ( $cantInstall ) {
					$notification = Models\Notification::getNotificationByName( 'install-mi' );
					if ( ! $notification->exists() ) {
						Models\Notification::addNotification( [
							'slug'              => uniqid(),
							'notification_name' => 'install-mi',
							'title'             => __( 'Install MonsterInsights', 'all-in-one-seo-pack' ),
							'content'           => sprintf(
								// Translators: 1 - The plugin short name ("AIOSEO").
								__( 'You selected to install the free MonsterInsights Analytics plugin during the setup of %1$s, but there was an issue during installation. Click below to manually install.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
								AIOSEO_PLUGIN_SHORT_NAME
							),
							'type'              => 'info',
							'level'             => [ 'all' ],
							'button1_label'     => __( 'Install MonsterInsights', 'all-in-one-seo-pack' ),
							'button1_action'    => $pluginData['miLite']['wpLink'],
							'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
							'button2_action'    => 'http://action#notification/install-mi-reminder',
							'start'             => gmdate( 'Y-m-d H:i:s' )
						] );
					}
				}
			}

			// Install OM.
			if ( in_array( 'conversion-tools', $features, true ) ) {
				if ( ! $pluginData['optinMonster']['activated'] ) {
					if ( aioseo()->addons->canInstall() ) {
						// Install and/or activate.
						aioseo()->addons->installAddon( 'optinMonster', $network );

						// Stop the redirect from happening.
						delete_transient( 'optin_monster_api_activation_redirect' );
					} else {
						$notification = Models\Notification::getNotificationByName( 'install-om' );
						if ( ! $notification->exists() ) {
							Models\Notification::addNotification( [
								'slug'              => uniqid(),
								'notification_name' => 'install-om',
								'title'             => __( 'Install OptinMonster', 'all-in-one-seo-pack' ),
								'content'           => sprintf(
									// Translators: 1 - The plugin short name ("AIOSEO").
									__( 'You selected to install the free OptinMonster Conversion Tools plugin during the setup of %1$s, but there was an issue during installation. Click below to manually install.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
									AIOSEO_PLUGIN_SHORT_NAME
								),
								'type'              => 'info',
								'level'             => [ 'all' ],
								'button1_label'     => __( 'Install OptinMonster', 'all-in-one-seo-pack' ),
								'button1_action'    => $pluginData['optinMonster']['wpLink'],
								'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
								'button2_action'    => 'http://action#notification/install-om-reminder',
								'start'             => gmdate( 'Y-m-d H:i:s' )
							] );
						}
					}
				}
			}
		}

		// Save the search appearance section.
		if ( 'searchAppearance' === $section && ! empty( $wizard['searchAppearance'] ) ) {
			$searchAppearance = $wizard['searchAppearance'];

			if ( isset( $searchAppearance['underConstruction'] ) ) {
				update_option( 'blog_public', ! $searchAppearance['underConstruction'] );
			}

			if (
				! empty( $searchAppearance['postTypes'] ) &&
				! empty( $searchAppearance['postTypes']['postTypes'] )
			) {
				// Robots.
				if ( ! empty( $searchAppearance['postTypes']['postTypes']['all'] ) ) {
					foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
						if ( $dynamicOptions->searchAppearance->postTypes->has( $postType ) ) {
							$dynamicOptions->searchAppearance->postTypes->$postType->show                          = true;
							$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = true;
							$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = false;
						}
					}
				} else {
					foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
						if ( $dynamicOptions->searchAppearance->postTypes->has( $postType ) ) {
							if ( in_array( $postType, (array) $searchAppearance['postTypes']['postTypes']['included'], true ) ) {
								$dynamicOptions->searchAppearance->postTypes->$postType->show                          = true;
								$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = true;
								$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = false;
							} else {
								$dynamicOptions->searchAppearance->postTypes->$postType->show                          = false;
								$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = false;
								$dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = true;
							}
						}
					}
				}

				// Sitemaps.
				if ( isset( $searchAppearance['postTypes']['postTypes']['all'] ) ) {
					$options->sitemap->general->postTypes->all = $searchAppearance['postTypes']['postTypes']['all'];
				}

				if ( isset( $searchAppearance['postTypes']['postTypes']['included'] ) ) {
					$options->sitemap->general->postTypes->included = $searchAppearance['postTypes']['postTypes']['included'];
				}
			}

			if ( isset( $searchAppearance['multipleAuthors'] ) ) {
				$options->searchAppearance->archives->author->show                          = $searchAppearance['multipleAuthors'];
				$options->searchAppearance->archives->author->advanced->robotsMeta->default = $searchAppearance['multipleAuthors'];
				$options->searchAppearance->archives->author->advanced->robotsMeta->noindex = ! $searchAppearance['multipleAuthors'];
			}

			if ( isset( $searchAppearance['redirectAttachmentPages'] ) && $dynamicOptions->searchAppearance->postTypes->has( 'attachment' ) ) {
				$dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = $searchAppearance['redirectAttachmentPages'] ? 'attachment' : 'disabled';
			}
		}

		// Save the smart recommendations section.
		if ( 'smartRecommendations' === $section && ! empty( $wizard['smartRecommendations'] ) ) {
			$smartRecommendations = $wizard['smartRecommendations'];
			if ( ! empty( $smartRecommendations['accountInfo'] ) && ! aioseo()->internalOptions->internal->siteAnalysis->connectToken ) {
				$url      = defined( 'AIOSEO_CONNECT_DIRECT_URL' ) ? AIOSEO_CONNECT_DIRECT_URL : 'https://aioseo.com/wp-json/aioseo-lite-connect/v1/connect/';
				$response = wp_remote_post( $url, [
					'timeout'    => 10,
					'headers'    => array_merge( [
						'Content-Type' => 'application/json'
					], aioseo()->helpers->getApiHeaders() ),
					'user-agent' => aioseo()->helpers->getApiUserAgent(),
					'body'       => wp_json_encode( [
						'accountInfo' => $smartRecommendations['accountInfo'],
						'homeurl'     => home_url()
					] )
				] );

				$token = json_decode( wp_remote_retrieve_body( $response ) );
				if ( ! empty( $token->token ) ) {
					aioseo()->internalOptions->internal->siteAnalysis->connectToken = $token->token;
				}
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'options' => aioseo()->options->all()
		], 200 );
	}
}