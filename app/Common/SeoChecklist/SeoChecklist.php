<?php
namespace AIOSEO\Plugin\Common\SeoChecklist;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SeoChecklist {
	protected $checks = [];

	/**
	 * Constructor.
	 *
	 * @since 4.9.4
	 */
	public function __construct() {
		add_action( 'update_option_blog_public', [ $this, 'onBlogPublicUpdate' ], 10, 2 );
	}

	/**
	 * Handle blog_public option update.
	 * If the site is set to discourage search engines, mark the indexing check as incomplete.
	 *
	 * @since 4.9.4
	 *
	 * @param  mixed $oldValue The old option value.
	 * @param  mixed $newValue The new option value.
	 * @return void
	 */
	public function onBlogPublicUpdate( $oldValue, $newValue ) {
		// If the new value discourages search engines (0), mark the check as incomplete.
		if ( '0' === (string) $newValue ) {
			$this->uncompleteCheck( 'enableIndexing' );
		}
	}

	/**
	 * Get all the checks.
	 *
	 * @since 4.9.4
	 *
	 * @return array
	 */
	public function getChecks() {
		// Reset checks array to prevent duplicates on multiple calls.
		$this->checks = [];

		$this->registerChecks();

		$completedChecks = aioseo()->internalOptions->internal->seoChecklist->completed;

		// Remove all checks that the user cannot access.
		$this->checks = array_values(array_filter( $this->checks, function ( $check ) {
			if ( ! isset( $check['capability'] ) ) {
				return true;
			}

			return $this->hasCapability( $check['name'] );
		} ) );

		// Once all checks are registered, check which ones are completed.
		foreach ( $completedChecks as $completedCheck ) {
			// Look up the checks by name. If it exists, get the index. Then, use the index to mark the check as completed.
			$index = array_search( $completedCheck, array_column( $this->checks, 'name' ), true );
			if ( false !== $index ) {
				$this->checks[ $index ]['completed'] = true;
			}
		}

		// Now, check the incompleted checks to see if any were completed.
		foreach ( $this->checks as $index => $check ) {
			if ( ! $check['completed'] ) {
				$isCompleted = false;
				if ( null !== $check['callback'] && is_callable( [ $this, $check['callback'] ] ) ) {
					$isCompleted = call_user_func( [ $this, $check['callback'] ] );
				}

				if ( $isCompleted ) {
					$this->checks[ $index ]['completed'] = true;

					$completedChecks[] = $check['name'];
					aioseo()->internalOptions->internal->seoChecklist->completed = $completedChecks;
				}
			}
		}

		return array_values( $this->checks );
	}

	/**
	 * Register all the checks.
	 *
	 * @since 4.9.4
	 *
	 * @return void
	 */
	protected function registerChecks() {
		// Undismissable checks first.
		$this->checks[] = [
			'name'        => 'enableIndexing',
			'title'       => __( 'Enable Indexing for Search Engines', 'all-in-one-seo-pack' ),
			'description' => __( 'Your site is currently hidden from search engines. Enable indexing so Google and other search engines can include your content in their search results.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'priority'    => 'high',
			'time'        => [
				'label' => __( 'Instant', 'all-in-one-seo-pack' ),
				'value' => 0
			],
			'callback'    => 'checkIndexingEnabled',
			'capability'  => 'manage_options',
			'actions'     => [
				[
					'label'    => __( 'Enable Indexing', 'all-in-one-seo-pack' ),
					'callback' => 'enableIndexing'
				]
			],
			'completed'   => false,
			'dismissable' => false
		];

		// Setup Wizard should be at the top of high priority tasks.
		$this->checks[] = [
			'name'                  => 'finishSetupWizard',
			'title'                 => __( 'Complete the Setup Wizard', 'all-in-one-seo-pack' ),
			'description'           => __( 'Configure essential SEO settings for your site through our guided setup process.
', 'all-in-one-seo-pack' ),
			'priority'              => 'high',
			'time'                  => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'              => 'checkFinishSetupWizard',
			'capability'            => 'aioseo_setup_wizard',
			'actions'               => [
				[
					'label' => __( 'Launch Wizard', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-setup-wizard' )
				]
			],
			'actionAfterCompletion' => [
				[
					'label' => __( 'Restart Wizard', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-setup-wizard' )
				]
			],
			'completed'             => false
		];

		$this->checks[] = [
			'name'        => 'connectGoogleSearchConsole',
			'title'       => __( 'Connect with Google Search Console', 'all-in-one-seo-pack' ),
			'description' => __( 'Link your site to Google Search Console to submit sitemaps and monitor your search performance.', 'all-in-one-seo-pack' ),
			'priority'    => 'high',
			'time'        => [
				'label' => __( '5 minutes', 'all-in-one-seo-pack' ),
				'value' => 300
			],
			'callback'    => 'checkConnectGoogleSearchConsole',
			'capability'  => 'aioseo_general_settings',
			'actions'     => [
				[
					'label' => __( 'Connect', 'all-in-one-seo-pack' ),
					'url'   => admin_url(
						'admin.php?page=aioseo-settings' .
						'&aioseo-scroll=google-search-console-settings' .
						'&aioseo-highlight=google-search-console-settings' .
						'#/webmaster-tools?activetool=googleSearchConsole'
					)
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'runHomepageAudit',
			'title'       => __( 'Run Homepage Audit', 'all-in-one-seo-pack' ),
			'description' => __( 'Analyze your homepage for SEO issues and get actionable recommendations to improve your rankings.', 'all-in-one-seo-pack' ),
			'priority'    => 'high',
			'time'        => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'    => 'checkRunHomepageAudit',
			'capability'  => 'aioseo_seo_analysis_settings',
			'actions'     => [
				[
					'label' => __( 'Run Audit', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-seo-analysis' )
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'deleteHelloWorld',
			'title'       => __( 'Delete the "Hello World" post', 'all-in-one-seo-pack' ),
			'description' => __( 'Remove the default WordPress sample post that was created when your site was installed.', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( 'Instant', 'all-in-one-seo-pack' ),
				'value' => 0
			],
			'callback'    => 'checkDeleteHelloWorld',
			'capability'  => [ $this, 'canDeleteHelloWorld' ],
			'actions'     => [
				[
					'label'    => __( 'Delete Post', 'all-in-one-seo-pack' ),
					'callback' => 'deleteHelloWorld'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'setSiteTitleAndTagline',
			'title'       => __( 'Set a Site Title and Tagline', 'all-in-one-seo-pack' ),
			'description' => __( 'The Site Title and Tagline are used throughout AIOSEO as default values and fallbacks. We recommend always having these set.', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( '2 minutes', 'all-in-one-seo-pack' ),
				'value' => 120
			],
			'callback'    => 'checkSiteTitleAndTagline',
			'capability'  => 'manage_options',
			'actions'     => [
				[
					'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'options-general.php' )
				],
				[
					'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
					'callback' => 'completeCheck'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'fillKnowledgeGraph',
			'title'       => __( 'Add Organization or Person Info', 'all-in-one-seo-pack' ),
			'description' => __( 'Add details about your organization or personal brand to help search engines understand your site better.
', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'    => null,
			'capability'  => 'aioseo_search_appearance_settings',
			'actions'     => [
				[
					'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-search-appearance&aioseo-scroll=aioseo-knowledge-graph&aioseo-highlight=aioseo-knowledge-graph#/global-settings' )
				],
				[
					'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
					'callback' => 'completeCheck'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'reviewContentTypes',
			'title'       => __( 'Configure Post Settings', 'all-in-one-seo-pack' ),
			'description' => __( 'Control how your posts and pages appear in search results.', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'    => null,
			'capability'  => 'aioseo_search_appearance_settings',
			'actions'     => [
				[
					'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-search-appearance#/content-types' )
				],
				[
					'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
					'callback' => 'completeCheck'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'reviewTaxonomies',
			'title'       => __( 'Configure Taxonomy Page Settings', 'all-in-one-seo-pack' ),
			'description' => __( 'Control how your taxonomy pages, such as categories and tags, appear in search results.', 'all-in-one-seo-pack' ),
			'priority'    => 'low',
			'time'        => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'    => null,
			'capability'  => 'aioseo_search_appearance_settings',
			'actions'     => [
				[
					'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-search-appearance#/taxonomies' )
				],
				[
					'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
					'callback' => 'completeCheck'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'reviewArchives',
			'title'       => __( 'Configure Archive Page Settings', 'all-in-one-seo-pack' ),
			'description' => __( 'Control how your archive pages appear in search results.', 'all-in-one-seo-pack' ),
			'priority'    => 'low',
			'time'        => [
				'label' => __( '10 minutes', 'all-in-one-seo-pack' ),
				'value' => 600
			],
			'callback'    => null,
			'capability'  => 'aioseo_search_appearance_settings',
			'actions'     => [
				[
					'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-search-appearance#/archives' )
				],
				[
					'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
					'callback' => 'completeCheck'
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'fillSocialProfiles',
			'title'       => __( 'Add Social Profiles', 'all-in-one-seo-pack' ),
			'description' => __( 'Add your social media profiles to enhance your online presence and help search engines connect your brand across platforms.', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( '5 minutes', 'all-in-one-seo-pack' ),
				'value' => 300
			],
			'callback'    => 'checkFillSocialProfiles',
			'capability'  => 'aioseo_social_networks_settings',
			'actions'     => [
				[
					'label' => __( 'Add Profiles', 'all-in-one-seo-pack' ),
					'url'   => admin_url( 'admin.php?page=aioseo-social-networks&aioseo-scroll=aioseo-social-profiles&aioseo-highlight=aioseo-social-profiles#/social-profiles' )
				]
			],
			'completed'   => false
		];

		$this->checks[] = [
			'name'        => 'enableLlmsTxt',
			'title'       => __( 'Enable llms.txt', 'all-in-one-seo-pack' ),
			'description' => __( 'Help AI tools like ChatGPT and Claude better understand and reference your site content.', 'all-in-one-seo-pack' ),
			'priority'    => 'medium',
			'time'        => [
				'label' => __( 'Instant', 'all-in-one-seo-pack' ),
				'value' => 0
			],
			'callback'    => 'checkLlmsTxtEnabled',
			'capability'  => 'aioseo_sitemap_settings',
			'actions'     => [
				[
					'label'    => __( 'Enable', 'all-in-one-seo-pack' ),
					'callback' => 'enableLlmsTxt'
				]
			],
			'completed'   => false
		];

		// Only show if llms.txt is enabled.
		if ( aioseo()->options->sitemap->llms->enable ) {
			$this->checks[] = [
				'name'        => 'reviewLlmsTxtSettings',
				'title'       => __( 'Configure llms.txt Settings', 'all-in-one-seo-pack' ),
				'description' => __( 'Control which content is included in your llms.txt file to optimize how AI models understand your site.', 'all-in-one-seo-pack' ),
				'priority'    => 'low',
				'time'        => [
					'label' => __( '5 minutes', 'all-in-one-seo-pack' ),
					'value' => 300
				],
				'callback'    => null,
				'capability'  => 'aioseo_sitemap_settings',
				'actions'     => [
					[
						'label' => __( 'Open Settings', 'all-in-one-seo-pack' ),
						'url'   => admin_url( 'admin.php?page=aioseo-sitemaps#/llms-sitemap' )
					],
					[
						'label'    => __( 'Mark Complete', 'all-in-one-seo-pack' ),
						'callback' => 'completeCheck'
					]
				],
				'completed'   => false
			];
		}

		// Broken Link Checker install check - always register, callback determines completion.
		$this->checks[] = [
			'name'        => 'installBrokenLinkChecker',
			'title'       => __( 'Install Broken Link Checker', 'all-in-one-seo-pack' ),
			'description' => __( 'Broken links hurt your SEO and user experience. Install the Broken Link Checker plugin to automatically find and fix them.', 'all-in-one-seo-pack' ),
			'priority'    => 'optional',
			'time'        => [
				'label' => __( 'Instant', 'all-in-one-seo-pack' ),
				'value' => 0
			],
			'callback'    => 'checkBrokenLinkCheckerInstalled',
			'capability'  => 'install_plugins',
			'actions'     => [
				[
					'label'    => __( 'Install Plugin', 'all-in-one-seo-pack' ),
					'callback' => 'installBrokenLinkChecker'
				]
			],
			'completed'   => false
		];

		// Broken Link Checker connect check - only show if BLC is installed and active.
		if ( $this->isBrokenLinkCheckerActive() ) {
			$this->checks[] = [
				'name'        => 'connectBrokenLinkChecker',
				'title'       => __( 'Connect Broken Link Checker', 'all-in-one-seo-pack' ),
				'description' => __( 'Connect your site with Broken Link Checker to start monitoring for broken links.', 'all-in-one-seo-pack' ),
				'priority'    => 'optional',
				'time'        => [
					'label' => __( '5 minutes', 'all-in-one-seo-pack' ),
					'value' => 300
				],
				'callback'    => 'checkBrokenLinkCheckerConnected',
				'capability'  => 'aioseo_broken_link_checker_settings',
				'actions'     => [
					[
						'label' => __( 'Connect BLC', 'all-in-one-seo-pack' ),
						'url'   => admin_url( 'admin.php?page=broken-link-checker#settings' )
					]
				],
				'completed'   => false
			];
		}
	}

	/**
	 * Check if the user can delete the hello world post.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if user can delete the hello world post.
	 */
	protected function canDeleteHelloWorld() {
		$post = aioseo()->core->db->start( 'posts' )
			->select( 'ID' )
			->where( 'ID', 1 )
			->run()
			->result();

		if ( empty( $post[0]->ID ) ) {
			// If the post does not exist, return true.
			// This means the post has been deleted. Otherwise, the task will be hidden.
			return true;
		}

		return current_user_can( 'delete_post', 1 );
	}

	/**
	 * Check if the hello world post has been deleted.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if hello world post has been deleted.
	 */
	protected function checkDeleteHelloWorld() {
		// Check if post with ID 1 exists.
		$post = aioseo()->core->db->start( 'posts' )
			->select( 'ID', 'post_status' )
			->where( 'ID', 1 )
			->run()
			->result();

		return empty( $post[0]->ID ) || ! empty( $post[0]->post_status ) && 'trash' === $post[0]->post_status;
	}

	/**
	 * Check if the setup wizard has been completed.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if completed.
	 */
	protected function checkFinishSetupWizard() {
		return aioseo()->internalOptions->internal->wizardCompleted;
	}

	/**
	 * Check if Google Search Console is connected.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if connected.
	 */
	protected function checkConnectGoogleSearchConsole() {
		return aioseo()->searchStatistics->api->auth->isConnected();
	}

	/**
	 * Check if a homepage SEO audit has been run.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if audit has been run.
	 */
	protected function checkRunHomepageAudit() {
		$seoAnalysis = aioseo()->internalOptions->internal->siteAnalysis->score;

		return ! empty( $seoAnalysis );
	}

	/**
	 * Check if Site Title is set.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if Site Title is set.
	 */
	protected function checkSiteTitle() {
		$siteTitle = get_option( 'blogname' );

		return ! empty( $siteTitle );
	}

	/**
	 * Check if Tagline is set.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if Tagline is set.
	 */
	protected function checkTagline() {
		$tagline = get_option( 'blogdescription' );

		return ! empty( $tagline );
	}

	/**
	 * Check if both Site Title and Tagline are set.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if both Site Title and Tagline are set.
	 */
	protected function checkSiteTitleAndTagline() {
		return $this->checkSiteTitle() && $this->checkTagline();
	}

	/**
	 * Check if Social Profiles have been filled out.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if at least one social profile is configured.
	 */
	protected function checkFillSocialProfiles() {
		$profiles = aioseo()->options->social->profiles->all();

		// Check if "Use the same username for multiple social networks" is enabled with a username and at least one network selected.
		if (
			! empty( $profiles['sameUsername']['enable'] ) &&
			! empty( $profiles['sameUsername']['username'] ) &&
			! empty( $profiles['sameUsername']['included'] )
		) {
			return true;
		}

		// Check if any social URL is set.
		$urls = $profiles['urls'] ?? [];
		foreach ( $urls as $url ) {
			if ( ! empty( $url ) ) {
				return true;
			}
		}

		// Check if additional profiles URLs are set.
		if ( ! empty( trim( $profiles['additionalUrls'] ?? '' ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if site indexing is enabled.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if indexing is enabled (blog_public is 1).
	 */
	protected function checkIndexingEnabled() {
		return '1' === get_option( 'blog_public' );
	}

	/**
	 * Check if llms.txt is enabled.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if llms.txt is enabled.
	 */
	protected function checkLlmsTxtEnabled() {
		return aioseo()->options->sitemap->llms->enable;
	}

	/**
	 * Check if Broken Link Checker is installed.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if BLC is installed.
	 */
	protected function checkBrokenLinkCheckerInstalled() {
		$pluginData = aioseo()->helpers->getPluginData();

		return ! empty( $pluginData['brokenLinkChecker']['installed'] );
	}

	/**
	 * Check if Broken Link Checker is connected.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if BLC is connected.
	 */
	protected function checkBrokenLinkCheckerConnected() {
		return $this->isBrokenLinkCheckerConnected();
	}

	/**
	 * Helper to check if Broken Link Checker is active.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if BLC is installed and active.
	 */
	protected function isBrokenLinkCheckerActive() {
		return function_exists( 'aioseoBrokenLinkChecker' );
	}

	/**
	 * Helper to check if Broken Link Checker is connected.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if BLC is connected.
	 */
	protected function isBrokenLinkCheckerConnected() {
		if ( ! $this->isBrokenLinkCheckerActive() ) {
			return false;
		}

		// Check if BLC has completed its initial scan or has an API key set.
		return aioseoBrokenLinkChecker()->license->isActive();
	}

	/**
	 * Complete a check.
	 *
	 * @since 4.9.4
	 *
	 * @param string $checkName The name of the check to complete.
	 * @return void
	 */
	public function completeCheck( $checkName ) {
		// Check permissions if the user is logged in.
		// Don't do this if wp_get_current_user() is not loaded.
		// This prevents errors when the method is called inside the plugin during init, and not via the API.
		if ( function_exists( 'wp_get_current_user' ) ) {
			// User needs access to the general settings or setup wizard.
			if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
				return;
			}

			// User should also have the cap required to complete this check.
			if ( ! $this->hasCapability( $checkName ) ) {
				return;
			}
		}

		$completedChecks   = aioseo()->internalOptions->internal->seoChecklist->completed;
		$completedChecks[] = $checkName;

		$completedChecks = array_values( array_unique( $completedChecks ) );

		aioseo()->internalOptions->internal->seoChecklist->completed = $completedChecks;
	}

	/**
	 * Uncomplete a check (remove completed status).
	 *
	 * @since 4.9.4
	 *
	 * @param  string $checkName The name of the check to uncomplete.
	 * @return void
	 */
	public function uncompleteCheck( $checkName ) {
		// Check permissions if the user is logged in.
		// Don't do this if wp_get_current_user() is not loaded.
		// This prevents errors when the method is called inside the plugin during init, and not via the API.
		if ( function_exists( 'wp_get_current_user' ) ) {
			// User needs access to the general settings or setup wizard.
			if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
				return;
			}

			// User should also have the cap required to uncomplete this check.
			if ( ! $this->hasCapability( $checkName ) ) {
				return;
			}
		}

		$completedChecks = aioseo()->internalOptions->internal->seoChecklist->completed;
		$completedChecks = array_values( array_unique( array_diff( $completedChecks, [ $checkName ] ) ) );

		aioseo()->internalOptions->internal->seoChecklist->completed = $completedChecks;
	}

	/**
	 * Execute an action for a check.
	 *
	 * @since 4.9.4
	 *
	 * @param string $checkName The name of the check to execute the action for.
	 * @param string $action    The action to execute.
	 * @return bool             True if successful.
	 */
	public function doAction( $checkName, $action ) {
		// Check if the user is logged in and has access to the general settings or setup wizard.
		// Don't do this if wp_get_current_user() is not loaded.
		// This prevents errors when the method is called inside the plugin during init, and not via the API.
		if ( function_exists( 'wp_get_current_user' ) ) {
			// User needs access to the general settings or setup wizard.
			if ( ! aioseo()->access->hasCapability( 'aioseo_general_settings' ) && ! aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) {
				return;
			}

			// User should also have the cap required to uncomplete this check.
			if ( ! $this->hasCapability( $checkName ) ) {
				return;
			}
		}

		switch ( $action ) {
			case 'completeCheck':
				// Just mark as complete, no additional action needed.
				return true;
			case 'deleteHelloWorld':
				return $this->deleteHelloWorld();
			case 'enableIndexing':
				return $this->enableIndexing();
			case 'enableLlmsTxt':
				return $this->enableLlmsTxt();
			case 'installBrokenLinkChecker':
				return $this->installBrokenLinkChecker();
			default:
				return false;
		}
	}

	/**
	 * Delete the Hello World post.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if successful.
	 */
	private function deleteHelloWorld() {
		// Check if user has permission to delete the post.
		if ( ! current_user_can( 'delete_post', 1 ) ) {
			return false;
		}

		// Delete the post.
		wp_trash_post( 1 );

		return true;
	}

	/**
	 * Enable site indexing.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if successful.
	 */
	private function enableIndexing() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		update_option( 'blog_public', '1' );

		return true;
	}

	/**
	 * Enable llms.txt.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if successful.
	 */
	private function enableLlmsTxt() {
		if ( ! aioseo()->access->hasCapability( 'aioseo_sitemap_settings' ) ) {
			return false;
		}

		aioseo()->options->sitemap->llms->enable = true;

		// Schedule llms.txt generation.
		if ( aioseo()->llms ) {
			aioseo()->llms->scheduleSingleGenerationForLlmsTxt();
		}

		return true;
	}

	/**
	 * Install Broken Link Checker.
	 *
	 * @since 4.9.4
	 *
	 * @return bool True if successful.
	 */
	private function installBrokenLinkChecker() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		if ( ! aioseo()->addons->canInstall() ) {
			return false;
		}

		$installed = aioseo()->addons->installAddon( 'brokenLinkChecker' );
		if ( $installed && function_exists( 'aioseoBrokenLinkChecker' ) ) {
			aioseoBrokenLinkChecker()->core->cache->delete( 'activation_redirect' );
		}

		return $installed;
	}

	/**
	 * Check if the user has the capability to complete a check.
	 *
	 * @since 4.9.4
	 *
	 * @param string $checkName The name of the check.
	 * @return bool True if the user has the capability.
	 */
	public function hasCapability( $checkName ) {
		// Ensure checks are registered before looking up capabilities.
		if ( empty( $this->checks ) ) {
			$this->registerChecks();
		}

		// Find the check by name.
		$check = array_filter( $this->checks, function ( $check ) use ( $checkName ) {
			return $check['name'] === $checkName;
		} );
		$check = reset( $check );

		// If the check was not found, deny access by default.
		if ( false === $check ) {
			return false;
		}

		// If the check does not have a capability, return true.
		if ( ! isset( $check['capability'] ) ) {
			return true;
		}

		if ( is_callable( $check['capability'] ) ) {
			return call_user_func( $check['capability'] );
		}

		if ( is_string( $check['capability'] ) ) {
			return aioseo()->access->hasCapability( $check['capability'] );
		}

		if ( is_array( $check['capability'] ) ) {
			foreach ( $check['capability'] as $capability ) {
				// If user has any allowed capability, return true.
				if ( aioseo()->access->hasCapability( $capability ) ) {
					return true;
				}
			}

			return false;
		}

		return false;
	}
}