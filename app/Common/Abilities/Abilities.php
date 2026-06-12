<?php
namespace AIOSEO\Plugin\Common\Abilities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Services\AuditService;
use AIOSEO\Plugin\Common\Services\NotificationsService;
use AIOSEO\Plugin\Common\Services\PostSeoService;
use AIOSEO\Plugin\Common\Services\RobotsService;
use AIOSEO\Plugin\Common\Services\SettingsService;

/**
 * Registers AIOSEO abilities with the WordPress Abilities API.
 *
 * Abilities are exposed at /wp-json/wp-abilities/v1/ when WordPress 6.9+ is
 * present. They are additionally surfaced as MCP tools when a compatible MCP
 * adapter (e.g. wordpress/mcp-adapter) is installed alongside the plugin.
 *
 * This class is the registration surface only — all business logic lives in
 * Common\Services\* (see ../Services/README.md). Each execute_callback is a
 * thin delegate that instantiates the relevant service and forwards the input.
 *
 * @since 4.9.8
 */
class Abilities {
	/**
	 * Class constructor.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'registerCategories' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'registerAbilities' ] );
	}

	/**
	 * Registers the AIOSEO ability category.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	public function registerCategories() {
		$categories = [
			'aioseo-posts'         => __( 'AIOSEO — Posts', 'all-in-one-seo-pack' ),
			'aioseo-settings'      => __( 'AIOSEO — Settings', 'all-in-one-seo-pack' ),
			'aioseo-notifications' => __( 'AIOSEO — Notifications', 'all-in-one-seo-pack' ),
			'aioseo-robots'        => __( 'AIOSEO — Robots.txt', 'all-in-one-seo-pack' ),
			'aioseo-audit'         => __( 'AIOSEO — Audit', 'all-in-one-seo-pack' )
		];

		foreach ( $categories as $slug => $label ) {
			wp_register_ability_category( $slug, [
				'label'       => $label,
				'description' => __( 'SEO management abilities provided by AIOSEO.', 'all-in-one-seo-pack' )
			] );
		}
	}

	/**
	 * Registers the Common (Lite + Pro) abilities.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	public function registerAbilities() {
		$this->registerPostAbilities();
		$this->registerSettingsAbility();
		$this->registerNotificationsAbility();
		$this->registerRobotsAbilities();
		$this->registerAuditAbilities();
	}

	/**
	 * Registers post-related abilities.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function registerPostAbilities() {
		wp_register_ability( 'aioseo-posts/seo-data-get', [
			'label'               => __( 'Get Post SEO Data', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns the SEO snapshot for a post: title, meta description, focus keyphrases, robots flags, canonical URL, social meta, schema type, pillar flag and TruSEO score. Optionally includes the full TruSEO analysis breakdown when "analysis" is passed in include.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-posts',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'postId'  => [
						'type'        => 'integer',
						'description' => __( 'The post ID.', 'all-in-one-seo-pack' )
					],
					'include' => [
						'type'        => 'array',
						'description' => __( 'Optional list of additional sections. Currently supports: "analysis" (full TruSEO breakdown).', 'all-in-one-seo-pack' ),
						'items'       => [
							'type' => 'string',
							'enum' => [ 'analysis' ]
						],
						'default'     => []
					]
				],
				'required'             => [ 'postId' ],
				'additionalProperties' => false
			],
			'output_schema'       => $this->postSnapshotOutputSchema( true ),
			'execute_callback'    => [ $this, 'getPostSeoData' ],
			'permission_callback' => [ $this, 'canEditPosts' ],
			'meta'                => $this->readonlyMeta()
		] );

		wp_register_ability( 'aioseo-posts/seo-data-update', [
			'label'               => __( 'Update Post SEO Data', 'all-in-one-seo-pack' ),
			'description'         => __( 'Updates SEO fields for a post: title, description, focus keyphrase, additional keyphrases, robots flags, canonical URL, pillar flag and social meta. Only the fields provided in input are changed; others are preserved. Fields not listed (e.g. schema, EEAT) are rejected — use the AIOSEO admin UI for those.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-posts',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'postId'                => [
						'type'        => 'integer',
						'description' => __( 'The post ID.', 'all-in-one-seo-pack' )
					],
					'title'                 => [
						'type'        => [ 'string', 'null' ],
						'description' => __( 'SEO title; pass null to clear and inherit the default.', 'all-in-one-seo-pack' )
					],
					'description'           => [ 'type' => [ 'string', 'null' ] ],
					'canonical_url'         => [ 'type' => [ 'string', 'null' ] ],
					'focus_keyphrase'       => [ 'type' => [ 'string', 'null' ] ],
					'additional_keyphrases' => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ]
					],
					'pillar_content'        => [ 'type' => 'boolean' ],
					'robots'                => $this->robotsInputSchema(),
					'social'                => $this->socialInputSchema()
				],
				'required'             => [ 'postId' ],
				'additionalProperties' => false
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'updated' => [ 'type' => 'boolean' ],
					'post'    => $this->postSnapshotOutputSchema( false )
				]
			],
			'execute_callback'    => [ $this, 'updatePostSeoData' ],
			'permission_callback' => [ $this, 'canEditPosts' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ]
			]
		] );

		wp_register_ability( 'aioseo-posts/list-missing-seo', [
			'label'               => __( 'List Posts Missing SEO Data', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns posts where one or more SEO fields are unset (title, description, or focus keyphrase). Defaults to focus_keyphrase. Useful for "which posts need SEO attention?" prompts.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-posts',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'missing_fields' => [
						'type'    => 'array',
						'items'   => [
							'type' => 'string',
							'enum' => [ 'title', 'description', 'focus_keyphrase' ]
						],
						'default' => [ 'focus_keyphrase' ]
					],
					'post_type'      => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ]
					],
					'status'         => [
						'type'    => 'array',
						'items'   => [ 'type' => 'string' ],
						'default' => [ 'publish' ]
					],
					'limit'          => [
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 20
					],
					'offset'         => [
						'type'    => 'integer',
						'minimum' => 0,
						'default' => 0
					]
				],
				'additionalProperties' => false,
				// All properties are optional — default the whole input to an empty object so
				// a no-argument call (the natural "list" prompt from an MCP agent) validates.
				'default'              => []
			],
			'output_schema'       => $this->postListOutputSchema( true ),
			'execute_callback'    => [ $this, 'listPostsMissingSeo' ],
			'permission_callback' => [ $this, 'canEditPosts' ],
			'meta'                => $this->readonlyMeta()
		] );

		wp_register_ability( 'aioseo-posts/list-truseo-score', [
			'label'               => __( 'List Posts by TruSEO Score', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns posts ordered by their TruSEO score, with optional min/max thresholds. Useful for finding the worst (or best) performing content.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-posts',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'min_score' => [
						'type'    => 'integer',
						'minimum' => 0,
						'maximum' => 100,
						'default' => 0
					],
					'max_score' => [
						'type'    => 'integer',
						'minimum' => 0,
						'maximum' => 100,
						'default' => 100
					],
					'order'     => [
						'type'    => 'string',
						'enum'    => [ 'asc', 'desc' ],
						'default' => 'asc'
					],
					'post_type' => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ]
					],
					'status'    => [
						'type'    => 'array',
						'items'   => [ 'type' => 'string' ],
						'default' => [ 'publish' ]
					],
					'limit'     => [
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 20
					],
					'offset'    => [
						'type'    => 'integer',
						'minimum' => 0,
						'default' => 0
					]
				],
				'additionalProperties' => false,
				// All properties are optional — default the whole input to an empty object so
				// a no-argument call (the natural "list" prompt from an MCP agent) validates.
				'default'              => []
			],
			'output_schema'       => $this->postListOutputSchema( false ),
			'execute_callback'    => [ $this, 'listPostsByTruseoScore' ],
			'permission_callback' => [ $this, 'canEditPosts' ],
			'meta'                => $this->readonlyMeta()
		] );
	}

	/**
	 * Registers the settings ability.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function registerSettingsAbility() {
		wp_register_ability( 'aioseo-settings/get', [
			'label'               => __( 'Get AIOSEO Settings', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns the full AIOSEO settings tree. Read-only: settings cannot be modified via abilities — use the AIOSEO admin UI.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-settings',
			'input_schema'        => $this->noInputSchema(),
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'settings' => [
						'type'        => 'object',
						'description' => __( 'The AIOSEO settings tree. Shape is internal AIOSEO; treat as opaque structured data.', 'all-in-one-seo-pack' )
					]
				]
			],
			'execute_callback'    => [ $this, 'getSettings' ],
			'permission_callback' => [ $this, 'canReadGeneralSettings' ],
			'meta'                => $this->readonlyMeta()
		] );
	}

	/**
	 * Registers the notifications ability.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function registerNotificationsAbility() {
		wp_register_ability( 'aioseo-notifications/list', [
			'label'               => __( 'List Active Notifications', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns currently-active AIOSEO admin notifications (warnings, errors, action-required items). Pass include_dismissed=true to also list dismissed ones.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-notifications',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'include_dismissed' => [
						'type'    => 'boolean',
						'default' => false
					],
					'limit'             => [
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 100,
						'default' => 20
					],
					'offset'            => [
						'type'    => 'integer',
						'minimum' => 0,
						'default' => 0
					]
				],
				'additionalProperties' => false,
				// All properties are optional — default the whole input to an empty object so
				// a no-argument call (the natural "list" prompt from an MCP agent) validates.
				'default'              => []
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'notifications' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'slug'           => [ 'type' => 'string' ],
								'title'          => [ 'type' => 'string' ],
								'content'        => [ 'type' => 'string' ],
								'type'           => [ 'type' => 'string' ],
								'level'          => [
									'type'  => 'array',
									'items' => [ 'type' => 'string' ]
								],
								'button1_label'  => [ 'type' => 'string' ],
								'button1_action' => [ 'type' => 'string' ],
								'button2_label'  => [ 'type' => 'string' ],
								'button2_action' => [ 'type' => 'string' ],
								'dismissed'      => [ 'type' => 'boolean' ],
								'created'        => [ 'type' => 'string' ],
								'updated'        => [ 'type' => 'string' ]
							]
						]
					],
					'total'         => [ 'type' => 'integer' ]
				]
			],
			'execute_callback'    => [ $this, 'listNotifications' ],
			'permission_callback' => [ $this, 'canReadGeneralSettings' ],
			'meta'                => $this->readonlyMeta()
		] );
	}

	/**
	 * Registers robots.txt abilities (output + rules CRUD).
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function registerRobotsAbilities() {
		wp_register_ability( 'aioseo-robots/output-get', [
			'label'               => __( 'Get Robots.txt Output', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns the active robots.txt content that AIOSEO is serving for this site, including custom rules and WordPress defaults.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-robots',
			'input_schema'        => $this->noInputSchema(),
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [ 'content' => [ 'type' => 'string' ] ]
			],
			'execute_callback'    => [ $this, 'getRobotsOutput' ],
			'permission_callback' => [ $this, 'canManageTools' ],
			'meta'                => $this->readonlyMeta()
		] );

		wp_register_ability( 'aioseo-robots/rules-list', [
			'label'               => __( 'List Robots.txt Rules', 'all-in-one-seo-pack' ),
			'description'         => __( 'Lists AIOSEO\'s custom robots.txt rules. Each rule has a stable id usable with update/delete.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-robots',
			'input_schema'        => $this->noInputSchema(),
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'rules' => [
						'type'  => 'array',
						'items' => $this->robotsRuleSchema()
					]
				]
			],
			'execute_callback'    => [ $this, 'listRobotsRules' ],
			'permission_callback' => [ $this, 'canManageTools' ],
			'meta'                => $this->readonlyMeta()
		] );

		wp_register_ability( 'aioseo-robots/rules-add', [
			'label'               => __( 'Add Robots.txt Rule', 'all-in-one-seo-pack' ),
			'description'         => __( 'Adds a new custom robots.txt rule for a given user agent. Directive must be "allow" or "disallow".', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-robots',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'user_agent'  => [
						'type'        => 'string',
						'description' => __( 'User-agent the rule applies to (e.g. "*", "Googlebot").', 'all-in-one-seo-pack' )
					],
					'directive'   => [
						'type' => 'string',
						'enum' => [ 'allow', 'disallow' ]
					],
					'field_value' => [
						'type'        => 'string',
						'description' => __( 'Path or pattern the directive applies to (e.g. "/private/").', 'all-in-one-seo-pack' )
					]
				],
				'required'             => [ 'user_agent', 'directive', 'field_value' ],
				'additionalProperties' => false
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [ 'rule' => $this->robotsRuleSchema() ]
			],
			'execute_callback'    => [ $this, 'addRobotsRule' ],
			'permission_callback' => [ $this, 'canManageTools' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ]
			]
		] );

		wp_register_ability( 'aioseo-robots/rules-update', [
			'label'               => __( 'Update Robots.txt Rule', 'all-in-one-seo-pack' ),
			'description'         => __( 'Updates an existing custom robots.txt rule by its id. Only the fields provided are changed.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-robots',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'id'          => [
						'type'        => 'string',
						'description' => __( 'Rule id from robots/rules/list.', 'all-in-one-seo-pack' )
					],
					'user_agent'  => [ 'type' => 'string' ],
					'directive'   => [
						'type' => 'string',
						'enum' => [ 'allow', 'disallow' ]
					],
					'field_value' => [ 'type' => 'string' ]
				],
				'required'             => [ 'id' ],
				'additionalProperties' => false
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [ 'rule' => $this->robotsRuleSchema() ]
			],
			'execute_callback'    => [ $this, 'updateRobotsRule' ],
			'permission_callback' => [ $this, 'canManageTools' ],
			'meta'                => [
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ]
			]
		] );

		wp_register_ability( 'aioseo-robots/rules-delete', [
			'label'               => __( 'Delete Robots.txt Rule', 'all-in-one-seo-pack' ),
			'description'         => __( 'Deletes a custom robots.txt rule by its id.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-robots',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'id' => [
						'type'        => 'string',
						'description' => __( 'Rule id from robots/rules/list.', 'all-in-one-seo-pack' )
					]
				],
				'required'             => [ 'id' ],
				'additionalProperties' => false
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [ 'deleted' => [ 'type' => 'boolean' ] ]
			],
			'execute_callback'    => [ $this, 'deleteRobotsRule' ],
			'permission_callback' => [ $this, 'canManageTools' ],
			'meta'                => [
				'annotations'  => [ 'destructive' => true ],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ]
			]
		] );
	}

	/**
	 * Registers audit abilities (homepage + site).
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function registerAuditAbilities() {
		wp_register_ability( 'aioseo-audit/homepage-get', [
			'label'               => __( 'Get Homepage SEO Audit', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns the cached SEO Site Analysis scan for the homepage: score, totals by severity, and the full per-check issue list.', 'all-in-one-seo-pack' ),
			'category'            => 'aioseo-audit',
			'input_schema'        => $this->noInputSchema(),
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'score'  => [ 'type' => 'integer' ],
					'totals' => [
						'type'       => 'object',
						'properties' => [
							'errors'   => [ 'type' => 'integer' ],
							'warnings' => [ 'type' => 'integer' ],
							'passed'   => [ 'type' => 'integer' ]
						]
					],
					'issues' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'code'     => [ 'type' => 'string' ],
								'group'    => [ 'type' => 'string' ],
								'severity' => [
									'type' => 'string',
									'enum' => [ 'errors', 'warnings', 'passed' ]
								],
								'status'   => [ 'type' => 'string' ]
							]
						]
					]
				]
			],
			'execute_callback'    => [ $this, 'getHomepageAudit' ],
			'permission_callback' => [ $this, 'canRunSeoAnalysis' ],
			'meta'                => $this->readonlyMeta()
		] );

		wp_register_ability( 'aioseo-audit/site-get', [
			'label'               => __( 'Get Site SEO Audit', 'all-in-one-seo-pack' ),
			'description'         => __( 'Returns a site-wide SEO health snapshot: homepage score + totals, sitemap on/off, custom robots rule count, public post type list, and GSC connection status.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'category'            => 'aioseo-audit',
			'input_schema'        => $this->noInputSchema(),
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'score'                     => [ 'type' => 'integer' ],
					'totals'                    => [
						'type'       => 'object',
						'properties' => [
							'errors'   => [ 'type' => 'integer' ],
							'warnings' => [ 'type' => 'integer' ],
							'passed'   => [ 'type' => 'integer' ]
						]
					],
					'sitemap_enabled'           => [ 'type' => 'boolean' ],
					'sitemap_index_url'         => [ 'type' => 'string' ],
					'custom_robots_rules_count' => [ 'type' => 'integer' ],
					'public_post_types'         => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ]
					],
					'search_console_connected'  => [ 'type' => 'boolean' ]
				]
			],
			'execute_callback'    => [ $this, 'getSiteAudit' ],
			'permission_callback' => [ $this, 'canRunSeoAnalysis' ],
			'meta'                => $this->readonlyMeta()
		] );
	}

	// =========================================================================
	// Permission callbacks
	// =========================================================================

	/**
	 * Returns a permission_callback closure that gates on the given access declaration.
	 *
	 * Accepts everything {@see Access::hasAccess()} accepts: a single AIOSEO capability,
	 * an array of caps (OR semantics), or one of the shorthand modes
	 * ('all'/'everyone' — any logged-in user, 'any' — any AIOSEO cap,
	 * 'options' — any non-page AIOSEO cap).
	 *
	 * Use this in ability registrations so access declarations read inline:
	 *
	 *     'permission_callback' => $this->gate( 'aioseo_general_settings' ),
	 *     'permission_callback' => $this->gate( [ 'aioseo_redirects_manage', 'aioseo_page_redirects_manage' ] ),
	 *     'permission_callback' => $this->gate( 'any' ),
	 *
	 * @since 4.9.8
	 *
	 * @param  string|array $access The access declaration.
	 * @return callable
	 */
	protected function gate( $access ) {
		return function() use ( $access ) {
			return aioseo()->access->hasAccess( $access );
		};
	}

	/**
	 * Permission callback: caller can edit posts.
	 *
	 * @since 4.9.8
	 *
	 * @return bool
	 */
	public function canEditPosts() {
		return aioseo()->access->hasAccess( 'aioseo_page_general_settings' );
	}

	/**
	 * Permission callback: caller can run the site-wide SEO audit.
	 *
	 * @since 4.9.8
	 *
	 * @return bool
	 */
	public function canRunSeoAnalysis() {
		return aioseo()->access->hasAccess( 'aioseo_seo_analysis_settings' );
	}

	/**
	 * Permission callback: caller can manage AIOSEO tools (robots, htaccess, etc.).
	 *
	 * @since 4.9.8
	 *
	 * @return bool
	 */
	public function canManageTools() {
		return aioseo()->access->hasAccess( 'aioseo_tools_settings' );
	}

	/**
	 * Permission callback: caller can read AIOSEO general settings.
	 *
	 * @since 4.9.8
	 *
	 * @return bool
	 */
	public function canReadGeneralSettings() {
		return aioseo()->access->hasAccess( 'aioseo_general_settings' );
	}

	// =========================================================================
	// Execute callbacks — thin delegates to Common\Services\*.
	// =========================================================================

	/**
	 * Delegate to PostSeoService::getSeoData.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function getPostSeoData( $input ) {
		return ( new PostSeoService() )->getSeoData(
			isset( $input['postId'] ) ? (int) $input['postId'] : 0,
			isset( $input['include'] ) && is_array( $input['include'] ) ? $input['include'] : []
		);
	}

	/**
	 * Delegate to PostSeoService::updateSeoData.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function updatePostSeoData( $input ) {
		$input  = is_array( $input ) ? $input : [];
		$postId = isset( $input['postId'] ) ? (int) $input['postId'] : 0;
		$fields = $input;
		unset( $fields['postId'] );

		return ( new PostSeoService() )->updateSeoData( $postId, $fields );
	}

	/**
	 * Delegate to PostSeoService::listMissingSeo.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function listPostsMissingSeo( $input ) {
		return ( new PostSeoService() )->listMissingSeo( is_array( $input ) ? $input : [] );
	}

	/**
	 * Delegate to PostSeoService::listByTruseoScore.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function listPostsByTruseoScore( $input ) {
		return ( new PostSeoService() )->listByTruseoScore( is_array( $input ) ? $input : [] );
	}

	/**
	 * Delegate to SettingsService::get.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getSettings() {
		return ( new SettingsService() )->get();
	}

	/**
	 * Delegate to NotificationsService::listActive.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function listNotifications( $input ) {
		return ( new NotificationsService() )->listActive( is_array( $input ) ? $input : [] );
	}

	/**
	 * Delegate to RobotsService::getOutput.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getRobotsOutput() {
		return ( new RobotsService() )->getOutput();
	}

	/**
	 * Delegate to RobotsService::listRules.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function listRobotsRules() {
		return ( new RobotsService() )->listRules();
	}

	/**
	 * Delegate to RobotsService::addRule.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function addRobotsRule( $input ) {
		return ( new RobotsService() )->addRule( is_array( $input ) ? $input : [] );
	}

	/**
	 * Delegate to RobotsService::updateRule.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function updateRobotsRule( $input ) {
		$input = is_array( $input ) ? $input : [];
		$id    = isset( $input['id'] ) ? (string) $input['id'] : '';
		$rule  = $input;
		unset( $rule['id'] );

		return ( new RobotsService() )->updateRule( $id, $rule );
	}

	/**
	 * Delegate to RobotsService::deleteRule.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $input The input data.
	 * @return array|\WP_Error
	 */
	public function deleteRobotsRule( $input ) {
		$input = is_array( $input ) ? $input : [];

		return ( new RobotsService() )->deleteRule( isset( $input['id'] ) ? (string) $input['id'] : '' );
	}

	/**
	 * Delegate to AuditService::getHomepage.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getHomepageAudit() {
		return ( new AuditService() )->getHomepage();
	}

	/**
	 * Delegate to AuditService::getSite.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getSiteAudit() {
		return ( new AuditService() )->getSite();
	}

	// =========================================================================
	// Schema helpers (reused across multiple ability registrations).
	// =========================================================================

	/**
	 * Shared meta block for read-only abilities.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function readonlyMeta() {
		return [
			'annotations'  => [ 'readonly' => true ],
			'show_in_rest' => true,
			'mcp'          => [ 'public' => true ]
		];
	}

	/**
	 * Input schema for abilities that take no input.
	 *
	 * Without an input schema the Abilities API rejects any provided input with
	 * `ability_missing_input_schema` — including the empty array MCP clients and
	 * WP-CLI pass for a no-argument call. A permissive empty-object schema with a
	 * `default` lets both `null` and `[]` validate.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function noInputSchema() {
		// No `properties` key: an empty PHP array serializes to JSON `[]` (an array, not an
		// object `{}`), which stricter Abilities API validators reject as a malformed schema —
		// they then treat the input schema as absent and fail with a missing-schema error.
		return [
			'type'                 => 'object',
			'additionalProperties' => false,
			'default'              => []
		];
	}

	/**
	 * Input schema for the robots flags object on post SEO updates.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function robotsInputSchema() {
		return [
			'type'                 => 'object',
			'properties'           => [
				'use_default'  => [ 'type' => 'boolean' ],
				'noindex'      => [ 'type' => 'boolean' ],
				'nofollow'     => [ 'type' => 'boolean' ],
				'noarchive'    => [ 'type' => 'boolean' ],
				'nosnippet'    => [ 'type' => 'boolean' ],
				'noimageindex' => [ 'type' => 'boolean' ],
				'notranslate'  => [ 'type' => 'boolean' ],
				'noodp'        => [ 'type' => 'boolean' ]
			],
			'additionalProperties' => false
		];
	}

	/**
	 * Input schema for the social meta object on post/term SEO updates.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function socialInputSchema() {
		return [
			'type'                 => 'object',
			'properties'           => [
				'og_title'            => [ 'type' => [ 'string', 'null' ] ],
				'og_description'      => [ 'type' => [ 'string', 'null' ] ],
				'twitter_title'       => [ 'type' => [ 'string', 'null' ] ],
				'twitter_description' => [ 'type' => [ 'string', 'null' ] ]
			],
			'additionalProperties' => false
		];
	}

	/**
	 * Output schema for a single post SEO snapshot.
	 *
	 * @since 4.9.8
	 *
	 * @param  bool $includeAnalysis Whether to declare the optional `analysis` field on the schema.
	 * @return array
	 */
	protected function postSnapshotOutputSchema( $includeAnalysis ) {
		$properties = [
			'title'                 => [ 'type' => [ 'string', 'null' ] ],
			'description'           => [ 'type' => [ 'string', 'null' ] ],
			'canonical_url'         => [ 'type' => [ 'string', 'null' ] ],
			'focus_keyphrase'       => [ 'type' => [ 'string', 'null' ] ],
			'additional_keyphrases' => [
				'type'  => 'array',
				'items' => [ 'type' => 'string' ]
			],
			'seo_score'             => [ 'type' => 'integer' ],
			'pillar_content'        => [ 'type' => 'boolean' ],
			'robots'                => [
				'type'       => 'object',
				'properties' => [
					'use_default'  => [ 'type' => 'boolean' ],
					'noindex'      => [ 'type' => 'boolean' ],
					'nofollow'     => [ 'type' => 'boolean' ],
					'noarchive'    => [ 'type' => 'boolean' ],
					'nosnippet'    => [ 'type' => 'boolean' ],
					'noimageindex' => [ 'type' => 'boolean' ],
					'notranslate'  => [ 'type' => 'boolean' ],
					'noodp'        => [ 'type' => 'boolean' ]
				]
			],
			'social'                => [
				'type'       => 'object',
				'properties' => [
					'og_title'            => [ 'type' => [ 'string', 'null' ] ],
					'og_description'      => [ 'type' => [ 'string', 'null' ] ],
					'twitter_title'       => [ 'type' => [ 'string', 'null' ] ],
					'twitter_description' => [ 'type' => [ 'string', 'null' ] ]
				]
			],
			'schema_type'           => [ 'type' => [ 'string', 'null' ] ]
		];

		if ( $includeAnalysis ) {
			$properties['analysis'] = [
				'type'        => 'object',
				'description' => __( 'Full TruSEO analysis breakdown. Only present when "analysis" is in include.', 'all-in-one-seo-pack' )
			];
		}

		return [
			'type'       => 'object',
			'properties' => $properties
		];
	}

	/**
	 * Output schema for a list of posts.
	 *
	 * @since 4.9.8
	 *
	 * @param  bool $includeMissingFields Whether to include the `missing_fields` array per post.
	 * @return array
	 */
	protected function postListOutputSchema( $includeMissingFields ) {
		$itemProperties = [
			'id'         => [ 'type' => 'integer' ],
			'post_title' => [ 'type' => 'string' ],
			'post_type'  => [ 'type' => 'string' ],
			'status'     => [ 'type' => 'string' ],
			'permalink'  => [ 'type' => [ 'string', 'null' ] ],
			'seo_score'  => [ 'type' => 'integer' ]
		];

		if ( $includeMissingFields ) {
			$itemProperties['missing_fields'] = [
				'type'  => 'array',
				'items' => [
					'type' => 'string',
					'enum' => [ 'title', 'description', 'focus_keyphrase' ]
				]
			];
		}

		return [
			'type'       => 'object',
			'properties' => [
				'posts' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => $itemProperties
					]
				],
				'total' => [ 'type' => 'integer' ]
			]
		];
	}

	/**
	 * Output schema for a single robots.txt rule.
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected function robotsRuleSchema() {
		return [
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'string' ],
				'user_agent'  => [ 'type' => 'string' ],
				'directive'   => [
					'type' => 'string',
					'enum' => [ 'allow', 'disallow' ]
				],
				'field_value' => [ 'type' => 'string' ]
			]
		];
	}
}