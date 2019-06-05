<?php
/**
 * AIOSEOP Helper/Info Class
 *
 * Description. (use period)
 *
 * @link URL
 *
 * @package All-in-One-SEO-Pack
 * @since 2.4.2
 */

/**
 * All in One SEO Plugin Helper
 *
 * @since 2.4.2
 */
class AIOSEOP_Helper {
	/**
	 * Help Text for jQuery UI Tooltips.
	 *
	 * @since 2.4.2
	 * @var array $help_text {
	 *     @type string
	 * }
	 */
	private $help_text = array();

	/**
	 * Constructor
	 *
	 * @since 2.4.2
	 *
	 * @param string $module Module/Class name.
	 */
	public function __construct( $module = '' ) {
		if ( current_user_can( 'aiosp_manage_seo' ) ) {
			$this->_set_help_text( $module );
		}
	}

	/**
	 * Set this Help Text
	 *
	 * Sets the Help Text according to the module/class in use, but if there is
	 * no class name in $module, then this Help Text will add all module help texts.
	 *
	 * @ignore
	 * @since 3.0
	 * @access private
	 *
	 * @param string $module All_in_One_SEO_Pack module.
	 */
	private function _set_help_text( $module ) {

		switch ( $module ) {
			case 'All_in_One_SEO_Pack':
				$this->help_text = $this->help_text_general();
				$this->help_text = array_merge( $this->help_text, $this->help_text_post_meta() );
				break;
			case 'All_in_One_SEO_Pack_Performance':
				$this->help_text = $this->help_text_performance();
				break;
			case 'All_in_One_SEO_Pack_Sitemap':
				$this->help_text = $this->help_text_sitemap();
				break;
			case 'All_in_One_SEO_Pack_Opengraph':
				$this->help_text = $this->help_text_opengraph();
				break;
			case 'All_in_One_SEO_Pack_Robots':
				$this->help_text = $this->help_text_robots_generator();
				break;
			case 'All_in_One_SEO_Pack_File_Editor':
				$this->help_text = $this->help_text_file_editor();
				break;
			case 'All_in_One_SEO_Pack_Importer_Exporter':
				$this->help_text = $this->help_text_importer_exporter();
				break;
			case 'All_in_One_SEO_Pack_Bad_Robots':
				$this->help_text = $this->help_text_bad_robots();
				break;
		}

		/**
		 * Set Help Text
		 *
		 * @since 3.0
		 *
		 * @param array  $this->help_text Contains an array of help text for each setting.
		 * @param string $module          Shows which class module is using the function.
		 */
		$this->help_text = apply_filters( 'aioseop_helper_set_help_text', $this->help_text, $module );
	}

	/**
	 * Help Text General Settings
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_general() {
		/*
		 * Consider changing the construction of the macros.
		 *
		 * The name of the macro should NOT be inside _e() or __() because it does not make sense as it
		 * won't change with the language.
		 *
		 * Moreover, it will confuse WPCS and it will try to replace %c (as in %category%) to %$1c.
		 * Placeholder %s (%something) has been bug fixed.
		 * @link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/698
		 */
		// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
		// phpcs:disable WordPress.WP.I18n.UnorderedPlaceholdersText
		$rtn_help_text = array(
			// General Settings.
			'aiosp_can'                         => __( 'This option will automatically generate Canonical URLs for your entire WordPress installation.  This will help to prevent duplicate content penalties by Google.', 'all-in-one-seo-pack' ),
			'aiosp_no_paged_canonical_links'    => __( 'Checking this option will set the Canonical URL for all paginated content to the first page.', 'all-in-one-seo-pack' ),
			'aiosp_use_original_title'          => __( 'Use wp_title to get the title used by the theme; this is disabled by default. If you use this option, set your title formats appropriately, as your theme might try to do its own title SEO as well.', 'all-in-one-seo-pack' ),
			'aiosp_schema_markup'               => __( 'Check this to support Schema.org markup, i.e., itemprop on supported metadata.', 'all-in-one-seo-pack' ),
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_do_log'                      => sprintf( __( 'Check this and %s will create a log of important events (all-in-one-seo-pack.log) in its plugin directory which might help debugging. Make sure this directory is writable.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),

			// Home Page Settings.
			'aiosp_home_title'                  => __( 'As the name implies, this will be the Meta Title of your homepage. This is independent of any other option. If not set, the default Site Title (found in WordPress under Settings, General, Site Title) will be used.', 'all-in-one-seo-pack' ),
			'aiosp_home_description'            => __( 'This will be the Meta Description for your homepage. This is independent of any other option. The default is no Meta Description at all if this is not set.', 'all-in-one-seo-pack' ),
			'aiosp_home_keywords'               => __( 'Enter a comma separated list of your most important keywords for your site that will be written as Meta Keywords on your homepage. Do not stuff everything in here.', 'all-in-one-seo-pack' ),
			'aiosp_use_static_home_info'        => __( 'Checking this option uses the title, description, and keywords set on your static Front Page.', 'all-in-one-seo-pack' ),

			// Title Settings.
			'aiosp_home_page_title_format'      =>
				__( 'This controls the format of the title tag for your Homepage.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%page_title%</dt>' .
					/* translators: %s is replaced with a content type such as Post, Page, etc. */
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_login%</dt>' .
					/* translators: Example sentence: "The first name of the author of the Post" */
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'username', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_nicename%</dt>' .
					'<dd>' . sprintf(
						__( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ),
						/* translators: The "nicename" is the sanitized version of a username. */
						__( 'nicename', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' )
					) . '</dd>' .
					'<dt>%page_author_firstname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'first name', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_lastname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'last name', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_date%</dt>' .
					/* translators: %s is replaced with a time related term such as Date, Year, Month, etc. */
					'<dd>' . sprintf( __( 'The current %s (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					/* translators: %s is replaced with a time related term such as Date, Year, Month, etc. */
					'<dd>' . sprintf( __( 'The current %s', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%cf_fieldname%</dt>' .
					'<dd>' . __( 'The name of a custom field', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_page_title_format'           =>
				__( 'This controls the format of the title tag for Pages.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%page_title%</dt>' .
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_login%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'username', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_nicename%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'nicename', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_firstname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'first name', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_lastname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'last name', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_date%</dt>' .
					/* translators: %s is replaced with a time related term such as Date, Year, Month, etc. */
					'<dd>' . sprintf( __( 'The current %s (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					'<dd>' . sprintf( __( 'The current %s', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_date%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_year%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_month%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'month', 'all-in-one-seo-pack' ), __( 'Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%cf_fieldname%</dt>' .
					'<dd>' . __( 'The name of a custom field', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_post_title_format'           =>
				__( 'This controls the format of the title tag for Posts.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%post_title%</dt>' .
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%category_title%</dt>' .
					/* translators: %s is replaced with a content type such as Post, Page, etc. */
					'<dd>' . sprintf( __( 'The (main) Category of the %s', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_login%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'username', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_nicename%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'nicename', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_firstname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'first name', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%page_author_lastname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'last name', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_date%</dt>' .
					'<dd>' . sprintf( __( 'The current %s (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					'<dd>' . sprintf( __( 'The current %s', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_date%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_year%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_month%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'month', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%cf_fieldname%</dt>' .
					'<dd>' . __( 'The name of a custom field', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_category_title_format'       =>
				__( 'This controls the format of the title tag for Category Archives.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%category_title%</dt>' .
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), __( 'Category', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%category_description%</dt>' .
					/* translators: %s is replaced with a content type such as Post, Page, etc. */
					'<dd>' . sprintf( __( 'The description of the %s', 'all-in-one-seo-pack' ), __( 'Category', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					'<dd>' . __( 'The current year', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_archive_title_format'        =>
				__( 'This controls the format of the title tag for Custom Post Archives.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%archive_title%</dt>' .
					'<dd>' . sprintf(
						__( 'The original title of the %s', 'all-in-one-seo-pack' ),
						/* translators: "Archive" is used in the context of a WordPress archive page. */
						__( 'Archive', 'all-in-one-seo-pack' )
					) . '</dd>' .
				'</dl>',
			'aiosp_date_title_format'           =>
				__( 'This controls the format of the title tag for Date Archives.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%date%</dt>' .
					'<dd>' . __( 'The original archive title (localized), e.g. "2019" or "2019 August"', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%day%</dt>' .
					'<dd>' . __( 'The original archive day, e.g. "17"', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%month%</dt>' .
					'<dd>' . __( 'The original archive month (localized), e.g. "August"', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%year%</dt>' .
					'<dd>' . __( 'The original archive year, e.g. "2019"', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_author_title_format'         =>
				__( 'This controls the format of the title tag for Author Archives.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%author%</dt>' .
					'<dd>' . __( 'The original archive title, e.g. "Steve" or "John Smith"', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_tag_title_format'            =>
				__( 'This controls the format of the title tag for Tag Archives.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%tag%</dt>' .
					'<dd>' . sprintf( __( 'The name of the %s', 'all-in-one-seo-pack' ), __( 'Tag', 'all-in-one-seo-pack' ) ) . '</dd>' .
				'</dl>',
			'aiosp_search_title_format'         =>
				__( 'This controls the format of the title tag for the Search page.', 'all-in-one-seo-pack' ) . '<br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%search%</dt>' .
					'<dd>' . __( 'The search term that was entered', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_description_format'          =>
				__( 'This controls the format of Meta Descriptions. The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%description%</dt>' .
					'<dd>' . __( 'This outputs the description you write for each page/post or the autogenerated description, if enabled. Auto-generated descriptions are generated from the excerpt or the first 160 characters of the content if there is no excerpt.', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%post_title%</dt>' .
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), __( 'Post', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%wp_title%</dt>' .
					'<dd>' . __( 'The original WordPress title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%current_date%</dt>' .
					'<dd>' . sprintf( __( 'The current %s (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					'<dd>' . sprintf( __( 'The current %s', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_date%</dt>' .
					'<dd>' . sprintf(
						__( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ),
						/* translators: "Post/Page" are the two main content types in WordPress. */
						__( 'Post/Page', 'all-in-one-seo-pack' )
					) . '</dd>' .
					'<dt>%post_year%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ), __( 'Post/Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_month%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'month', 'all-in-one-seo-pack' ), __( 'Post/Page', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%cf_fieldname%</dt>' .
					'<dd>' . __( 'The name of a custom field', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_404_title_format'            =>
				__( 'This controls the format of the title tag for the 404 page.', 'all-in-one-seo-pack' ) . ' <br />' .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%request_url%</dt>' .
					'<dd>' . __( 'The original URL path, like "/url-that-does-not-exist/"', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%request_words%</dt>' .
					'<dd>' . __( 'The URL path in human readable form, like "Url That Does Not Exist"', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%404_title%</dt>' .
					'<dd>' . __( 'Additional 404 title input', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			'aiosp_paged_format'                =>
				__( 'This string gets appended/prepended to titles of paged index pages (like home or archive pages).', 'all-in-one-seo-pack' ) .
				__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<dl>' .
					'<dt>%page%</dt>' .
					'<dd>' . __( 'The page number', 'all-in-one-seo-pack' ) . '</dd>' .
				'</dl>',
			//phpcs:enable

			// Custom Post Type Settings.
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_cpostactive'                 => sprintf( __( 'Use these checkboxes to select which Content Types you want to use %s with.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),

			// Display Settings.
			'aiosp_posttypecolumns'             => __( 'This lets you select which screens display the SEO Title, SEO Keywords and SEO Description columns.', 'all-in-one-seo-pack' ),

			// Webmaster Verification.
			'aiosp_google_verify'               => __( 'Enter your verification code here to verify your site with Google Search Console.', 'all-in-one-seo-pack' ),
			'aiosp_bing_verify'                 => __( 'Enter your verification code here to verify your site with Bing Webmaster Tools.', 'all-in-one-seo-pack' ),
			'aiosp_pinterest_verify'            => __( 'Enter your verification code here to verify your site with Pinterest.', 'all-in-one-seo-pack' ),
			'aiosp_yandex_verify'               => __( 'Enter your verification code here to verify your site with Yandex Webmaster Tools.', 'all-in-one-seo-pack' ),
			'aiosp_baidu_verify'                => __( 'Enter your verification code here to verify your site with Baidu Webmaster Tools.', 'all-in-one-seo-pack' ),

			// Google Settings.
			'aiosp_google_sitelinks_search'     => __( 'Add markup to display the Google Sitelinks Search Box next to your search results in Google.', 'all-in-one-seo-pack' ),
			'aiosp_google_analytics_id'         => __( 'Enter your Google Analytics ID here to track visitor behavior on your site using Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_advanced_options'         => __( 'Check to use advanced Google Analytics options.', 'all-in-one-seo-pack' ),
			'aiosp_ga_domain'                   => __( 'Enter your domain name without the http:// to set your cookie domain.', 'all-in-one-seo-pack' ),
			'aiosp_ga_multi_domain'             => __( 'Use this option to enable tracking of multiple or additional domains.', 'all-in-one-seo-pack' ),
			'aiosp_ga_addl_domains'             => __( 'Add a list of additional domains to track here.  Enter one domain name per line without the http://.', 'all-in-one-seo-pack' ),
			'aiosp_ga_anonymize_ip'             => __( 'This enables support for IP Anonymization in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_display_advertising'      => __( 'This enables support for the Display Advertiser Features in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_exclude_users'            => __( 'Exclude logged-in users from Google Analytics tracking by role.', 'all-in-one-seo-pack' ),
			'aiosp_ga_track_outbound_links'     => __( 'Check this if you want to track outbound links with Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_link_attribution'         => __( 'This enables support for the Enhanced Link Attribution in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_enhanced_ecommerce'       => __( 'This enables support for the Enhanced Ecommerce in Google Analytics.', 'all-in-one-seo-pack' ),

			// Noindex Settings.
			'aiosp_cpostnoindex'                => __( 'Set the default NOINDEX setting for each Post Type.', 'all-in-one-seo-pack' ),
			'aiosp_cpostnofollow'               => __( 'Set the default NOFOLLOW setting for each Post Type.', 'all-in-one-seo-pack' ),
			'aiosp_category_noindex'            => __( 'Check this to ask search engines not to index Category Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_archive_date_noindex'        => __( 'Check this to ask search engines not to index Date Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_archive_author_noindex'      => __( 'Check this to ask search engines not to index Author Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_tags_noindex'                => __( 'Check this to ask search engines not to index Tag Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_search_noindex'              => __( 'Check this to ask search engines not to index the Search page. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_404_noindex'                 => __( 'Check this to ask search engines not to index the 404 page.', 'all-in-one-seo-pack' ),
			'aiosp_paginated_noindex'           => __( 'Check this to ask search engines not to index paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_paginated_nofollow'          => __( 'Check this to ask search engines not to follow links from paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_tax_noindex'                 => __( 'Check this to ask search engines not to index custom Taxonomy archive pages. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),

			// Advanced Settings.
			'aiosp_generate_descriptions'       => __( 'Check this and your Meta Descriptions for any Post Type will be auto-generated using the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt. You can overwrite any auto-generated Meta Description by editing the post or page.', 'all-in-one-seo-pack' ),
			'aiosp_skip_excerpt'                => __( 'This option will auto generate your meta descriptions from your post content instead of your post excerpt. This is useful if you want to use your content for your autogenerated meta descriptions instead of the excerpt. WooCommerce users should read the documentation regarding this setting.', 'all-in-one-seo-pack' ),
			'aiosp_run_shortcodes'              => __( 'Check this and shortcodes will get executed for descriptions auto-generated from content.', 'all-in-one-seo-pack' ),
			'aiosp_hide_paginated_descriptions' => __( 'Check this and your Meta Descriptions will be removed from page 2 or later of paginated content.', 'all-in-one-seo-pack' ),
			'aiosp_dont_truncate_descriptions'  => __( 'Check this to prevent your Description from being truncated regardless of its length.', 'all-in-one-seo-pack' ),
			'aiosp_unprotect_meta'              => __( "Check this to unprotect internal postmeta fields for use with XMLRPC. If you don't know what that is, leave it unchecked.", 'all-in-one-seo-pack' ),
			'aiosp_redirect_attachement_parent' => __( 'Redirect attachment pages to post parent.', 'all-in-one-seo-pack' ),
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_ex_pages'                    => sprintf( __( 'Enter a comma separated list of pages here to be excluded by %s.  This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: <em>/forum/, /contact/</em><br />For instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is add "forum" or "/forum" or "/forum/" or any URL with the word "forum" in it here, such as "http://mysite.com/forum" or "http://mysite.com/forum/someforumpage", and it will be excluded.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
			'aiosp_post_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Posts. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'aiosp_page_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Pages. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'aiosp_front_meta_tags'             => __( 'What you enter here will be copied verbatim to the header of the front page if you have set a static page in Settings, Reading, Front Page Displays. You can enter whatever additional headers you want here, even references to stylesheets. This will fall back to using Additional Page Headers if you have them set and nothing is entered here.', 'all-in-one-seo-pack' ),
			'aiosp_home_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of the home page if you have Front page displays your latest posts selected in Settings, Reading.  It will also be copied verbatim to the header on the Posts page if you have one set in Settings, Reading. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),

			// Keyword Settings.
			'aiosp_togglekeywords'              => __( 'This option allows you to toggle the use of Meta Keywords throughout the whole of the site.', 'all-in-one-seo-pack' ),
			'aiosp_use_categories'              => __( 'Check this if you want your categories for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'aiosp_use_tags_as_keywords'        => __( 'Check this if you want your tags for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'aiosp_dynamic_postspage_keywords'  => __( 'Check this if you want your keywords on your Posts page (set in WordPress under Settings, Reading, Front Page Displays) and your archive pages to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.', 'all-in-one-seo-pack' ),

			// Unknown Location.
			'aiosp_google_connect'              => __( 'Press the connect button to connect with Google Analytics; or if already connected, press the disconnect button to disable and remove any stored analytics credentials.', 'all-in-one-seo-pack' ),

		);

		// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
		$post_types = get_post_types( '', 'names' );
		foreach ( $post_types as $v1_pt ) {
			if ( ! isset( $rtn_help_text[ 'aiosp_' . $v1_pt . '_title_format' ] ) ) {
				$name = ucwords( preg_replace( '/-|\_/', ' ', get_post_type_object( $v1_pt )->labels->singular_name ) );

				$help_text_macros =
					'<dt>%site_title%</dt>' .
					'<dd>' . __( 'Your site title', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%site_description%</dt>' .
					'<dd>' . __( 'Your site description', 'all-in-one-seo-pack' ) . '</dd>' .
					'<dt>%post_title%</dt>' .
					'<dd>' . sprintf( __( 'The original title of the %s', 'all-in-one-seo-pack' ), $name ) . '</dd>';

				$pt_obj_taxes = get_object_taxonomies( $v1_pt, 'objects' );
				foreach ( $pt_obj_taxes as $k2_slug => $v2_tax_obj ) {
					if ( $v2_tax_obj->public ) {
						$help_text_macros .= sprintf( '<dt>%%tax_%1$s%%</dt><dd>' . __( 'The title of the %2$s taxonomy that is associated to this %3$s', 'all-in-one-seo-pack' ) . '</dd>', $k2_slug, ucwords( $v2_tax_obj->label ), $name );
					}
				}

				$help_text_macros .=
					'<dt>%page_author_login%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'username', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%page_author_nicename%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'nicename', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%page_author_firstname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'first name', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%page_author_lastname%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s of the author of the %2$s', 'all-in-one-seo-pack' ), __( 'last name', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%current_date%</dt>' .
					'<dd>' . sprintf( __( 'The current %s (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%current_year%</dt>' .
					'<dd>' . sprintf( __( 'The current %s', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ) ) . '</dd>' .
					'<dt>%post_date%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'date', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%post_year%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'year', 'all-in-one-seo-pack' ), $name ) . '</dd>' .
					'<dt>%post_month%</dt>' .
					'<dd>' . sprintf( __( 'The %1$s when the %2$s was published (localized)', 'all-in-one-seo-pack' ), __( 'month', 'all-in-one-seo-pack' ), $name ) . '</dd>' .

				$rtn_help_text[ 'aiosp_' . $v1_pt . '_title_format' ] = __( 'The following macros are supported:', 'all-in-one-seo-pack' ) . '<dl>' . $help_text_macros . '</dl>' . '<br /><a href="https://semperplugins.com/documentation/custom-post-type-settings/#custom-titles" target="_blank">' . __( 'Click here for documentation on this setting', 'all-in-one-seo-pack' ) . '</a>';
			}
		}
		// phpcs:enable

		$help_doc_link = array(
			// General Settings.
			'aiosp_can'                         => 'https://semperplugins.com/documentation/general-settings/#canonical-urls',
			'aiosp_no_paged_canonical_links'    => 'https://semperplugins.com/documentation/general-settings/#no-pagination-for-canonical-urls',
			'aiosp_use_original_title'          => 'https://semperplugins.com/documentation/general-settings/#use-original-title',
			'aiosp_schema_markup'               => 'https://semperplugins.com/documentation/general-settings/#use-schema-markup',
			'aiosp_do_log'                      => 'https://semperplugins.com/documentation/general-settings/#log-important-events',

			// Home Page Settings.
			'aiosp_home_title'                  => 'https://semperplugins.com/documentation/home-page-settings/#home-title',
			'aiosp_home_description'            => 'https://semperplugins.com/documentation/home-page-settings/#home-description',
			'aiosp_home_keywords'               => 'https://semperplugins.com/documentation/home-page-settings/#home-keywords',
			'aiosp_use_static_home_info'        => 'https://semperplugins.com/documentation/home-page-settings/#use-static-front-page-instead',

			// Title Settings.
			'aiosp_home_page_title_format'      => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_page_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_post_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_category_title_format'       => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_archive_title_format'        => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_date_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_author_title_format'         => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_tag_title_format'            => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_search_title_format'         => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_description_format'          => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_404_title_format'            => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_paged_format'                => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',

			// Custom Post Type Settings.
			'aiosp_cpostactive'                 => 'https://semperplugins.com/documentation/custom-post-type-settings/#seo-on-only-these-post-types',

			// Display Settings.
			'aiosp_posttypecolumns'             => 'https://semperplugins.com/documentation/display-settings/#show-column-labels-for-custom-post-types',

			// Webmaster Verification.
			'aiosp_google_verify'               => 'https://semperplugins.com/documentation/google-search-console-verification/',
			'aiosp_bing_verify'                 => 'https://semperplugins.com/documentation/bing-webmaster-verification/',
			'aiosp_pinterest_verify'            => 'https://semperplugins.com/documentation/pinterest-site-verification/',
			'aiosp_yandex_verify'               => 'https://semperplugins.com/documentation/yandex-webmaster-verification/',
			'aiosp_baidu_verify'                => 'https://semperplugins.com/documentation/baidu-webmaster-verification/',

			// Google Settings.
			'aiosp_google_sitelinks_search'     => 'https://semperplugins.com/documentation/google-settings/#display-sitelinks-search-box',
			'aiosp_google_analytics_id'         => 'https://semperplugins.com/documentation/setting-up-google-analytics/',
			'aiosp_ga_advanced_options'         => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/',
			'aiosp_ga_domain'                   => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#tracking-domain',
			'aiosp_ga_multi_domain'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-multiple-domains-additional-domains',
			'aiosp_ga_addl_domains'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-multiple-domains-additional-domains',
			'aiosp_ga_anonymize_ip'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#anonymize-ip-addresses',
			'aiosp_ga_display_advertising'      => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#display-advertiser-tracking',
			'aiosp_ga_exclude_users'            => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#exclude-users-from-tracking',
			'aiosp_ga_track_outbound_links'     => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-outbound-links',
			'aiosp_ga_link_attribution'         => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#enhanced-link-attribution',
			'aiosp_ga_enhanced_ecommerce'       => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#enhanced-ecommerce',

			// Noindex Settings.
			'aiosp_cpostnoindex'                => 'https://semperplugins.com/documentation/noindex-settings/#noindex',
			'aiosp_cpostnofollow'               => 'https://semperplugins.com/documentation/noindex-settings/#nofollow',
			'aiosp_category_noindex'            => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_archive_date_noindex'        => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_archive_author_noindex'      => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_tags_noindex'                => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_search_noindex'              => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-search-page',
			'aiosp_404_noindex'                 => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-404-page',
			'aiosp_paginated_noindex'           => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-paginated-pages-posts',
			'aiosp_paginated_nofollow'          => 'https://semperplugins.com/documentation/noindex-settings/#use-nofollow-for-paginated-pages-posts',
			'aiosp_tax_noindex'                 => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-taxonomy-archives',

			// Advanced Settings.
			'aiosp_generate_descriptions'       => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#autogenerate-descriptions',
			'aiosp_skip_excerpt'                => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#remove-descriptions-for-paginated-pages',
			'aiosp_run_shortcodes'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#never-shorten-long-descriptions',
			'aiosp_hide_paginated_descriptions' => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#unprotect-post-meta-fields',
			'aiosp_dont_truncate_descriptions'  => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#never-shorten-long-descriptions',
			'aiosp_unprotect_meta'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#unprotect-post-meta-fields',
			'aiosp_redirect_attachement_parent' => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#redirect-attachments-to-post-parent',
			'aiosp_ex_pages'                    => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#exclude-pages',
			'aiosp_post_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-post-headers',
			'aiosp_page_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-page-headers',
			'aiosp_front_meta_tags'             => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-front-page-headers',
			'aiosp_home_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-blog-page-headers',

			// Keyword Settings.
			'aiosp_togglekeywords'              => 'https://semperplugins.com/documentation/keyword-settings/#use-keywords',
			'aiosp_use_categories'              => 'https://semperplugins.com/documentation/keyword-settings/#use-categories-for-meta-keywords',
			'aiosp_use_tags_as_keywords'        => 'https://semperplugins.com/documentation/keyword-settings/#use-tags-for-meta-keywords',
			'aiosp_dynamic_postspage_keywords'  => 'https://semperplugins.com/documentation/keyword-settings/#dynamically-generate-keywords-for-posts-page',

			// Unknown/Pro?
			// 'aiosp_google_connect'              => '',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			// Any help text that ends with a ul or ol element will cause text to start at the next line.
			$tooltips_with_ul = array(
				'aiosp_home_page_title_format',
				'aiosp_page_title_format',
				'aiosp_post_title_format',
				'aiosp_category_title_format',
				'aiosp_archive_title_format',
				'aiosp_date_title_format',
				'aiosp_author_title_format',
				'aiosp_tag_title_format',
				'aiosp_search_title_format',
				'aiosp_description_format',
				'aiosp_404_title_format',
				'aiosp_paged_format',
			);

			$br = '<br /><br />';
			if ( in_array( $k1_slug, $tooltips_with_ul, true ) ) {
				$br = '<br />';
			}

			$rtn_help_text[ $k1_slug ] .= $br . '<a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Performance Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_performance() {
		$rtn_help_text = array(
			'aiosp_performance_memory_limit'   => __( 'This setting allows you to raise your PHP memory limit to a reasonable value. Note: WordPress core and other WordPress plugins may also change the value of the memory limit.', 'all-in-one-seo-pack' ),
			'aiosp_performance_execution_time' => __( 'This setting allows you to raise your PHP execution time to a reasonable value.', 'all-in-one-seo-pack' ),
			'aiosp_performance_force_rewrites' => __( 'Use output buffering to ensure that the title gets rewritten. Enable this option if you run into issues with the title tag being set by your theme or another plugin.', 'all-in-one-seo-pack' ),
		);

		$help_doc_link = array(
			'aiosp_performance_memory_limit'   => 'https://semperplugins.com/documentation/performance-settings/#raise-memory-limit',
			'aiosp_performance_execution_time' => 'https://semperplugins.com/documentation/performance-settings/#raise-execution-time',
			'aiosp_performance_force_rewrites' => 'https://semperplugins.com/documentation/performance-settings/#force-rewrites',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Sitemap Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_sitemap() {
		$rtn_help_text = array(
			// XML Sitemap.
			'aiosp_sitemap_rss_sitemap'     => __( 'Generate an RSS sitemap in addition to the regular XML Sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_daily_cron'      => __( 'Notify search engines based on the selected schedule, and also update static sitemap daily if in use. (this uses WP-Cron, so make sure this is working properly on your server as well)', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_indexes'         => __( 'Organize sitemap entries into distinct files in your sitemap. Enable this only if your sitemap contains over 50,000 URLs or the file is over 5MB in size.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_max_posts'       => __( 'Allows you to specify the maximum number of posts in a sitemap (up to 50,000).', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_posttypes'       => __( 'Select which Post Types appear in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_taxonomies'      => __( 'Select which taxonomy archives appear in your sitemap', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_archive'         => __( 'Include Date Archives in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_author'          => __( 'Include Author Archives in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_images'          => __( 'Exclude Images in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_robots'          => __( 'Places a link to your Sitemap.xml into your virtual Robots.txt file.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_rewrite'         => __( 'Dynamically creates the XML sitemap instead of using a static file.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_url'        => __( 'URL to the page. This field only accepts absolute URLs with the protocol specified.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_prio'       => __( 'The priority of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_freq'       => __( 'The frequency of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_mod'        => __( 'Last modified date of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_excl_terms'      => __( 'Exclude any category, tag or custom taxonomy from the XML sitemap. Start typing the name of a category, tag or taxonomy term in the field and a dropdown will populate with the matching terms for you to select from.<br/><br/>This will also exclude any content belonging to the specified term.  For example, if you exclude the "Uncategorized" category then all posts in that category will also be excluded from the sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_excl_pages'      => __( 'Use page slugs or page IDs, separated by commas, to exclude pages from the sitemap.', 'all-in-one-seo-pack' ),

			// Priorities.
			'aiosp_sitemap_prio_homepage'       => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_prio_post'           => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), __( 'Posts', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_prio_taxonomies'     => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), __( 'Taxonomies', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_prio_archive'        => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), __( 'Archive Pages', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_prio_author'         => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), __( 'Author Pages', 'all-in-one-seo-pack' ) ),

			// Frequencies.
			'aiosp_sitemap_freq_homepage'       => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), __( 'Homepage', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_freq_post'           => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), __( 'Posts', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_freq_taxonomies'     => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), __( 'Taxonomies', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_freq_archive'        => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), __( 'Archive Pages', 'all-in-one-seo-pack' ) ),
			'aiosp_sitemap_freq_author'         => sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), __( 'Author Pages', 'all-in-one-seo-pack' ) ),

		);

		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'names' );
		foreach ( $post_types as $pt ) {
			$pt_obj = get_post_type_object( $pt );
			$rtn_help_text[ 'aiosp_sitemap_prio_post_' . $pt ] = sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), ucwords( $pt_obj->label ) );
			$rtn_help_text[ 'aiosp_sitemap_freq_post_' . $pt ] = sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), ucwords( $pt_obj->label ) );
			$help_doc_link[ 'aiosp_sitemap_prio_post_' . $pt ] = 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies';
			$help_doc_link[ 'aiosp_sitemap_freq_post_' . $pt ] = 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies';
		}

		$taxonomies = get_taxonomies( $args, 'object' );
		foreach ( $taxonomies as $tax ) {
			$rtn_help_text[ 'aiosp_sitemap_prio_taxonomies_' . $tax->name ] = sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'priority', 'all-in-one-seo-pack' ), ucwords( $tax->label ) );
			$rtn_help_text[ 'aiosp_sitemap_freq_taxonomies_' . $tax->name ] = sprintf( __( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' ), __( 'frequency', 'all-in-one-seo-pack' ), ucwords( $tax->label ) );
			$help_doc_link[ 'aiosp_sitemap_prio_taxonomies_' . $tax->name ] = 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies';
			$help_doc_link[ 'aiosp_sitemap_freq_taxonomies_' . $tax->name ] = 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies';
		}

		$help_doc_link = array(
			// XML Sitemap.
			'aiosp_sitemap_rss_sitemap'     => 'https://semperplugins.com/documentation/xml-sitemaps-module/#create-rss_sitemap',
			'aiosp_sitemap_daily_cron'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#schedule-updates',
			'aiosp_sitemap_indexes'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#enable-sitemap-indexes',
			'aiosp_sitemap_max_posts'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#enable-sitemap-indexes',
			'aiosp_sitemap_posttypes'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#post-types-and-taxonomies',
			'aiosp_sitemap_taxonomies'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#post-types-and-taxonomies',
			'aiosp_sitemap_archive'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#include-archive-pages',
			'aiosp_sitemap_author'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#include-archive-pages',
			'aiosp_sitemap_images'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#exclude-images',
			'aiosp_sitemap_robots'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#link-from-virtual-robots',
			'aiosp_sitemap_rewrite'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#dynamically-generate-sitemap',

			// Additional Pages.
			'aiosp_sitemap_addl_url'        => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_prio'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_freq'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_mod'        => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',

			// Exclude Items.
			'aiosp_sitemap_excl_terms' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#excluded-items',
			'aiosp_sitemap_excl_pages'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#excluded-items',

			// Priorities.
			'aiosp_sitemap_prio_homepage'   => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_post'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_taxonomies' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_archive'    => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_author'     => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',

			// Frequencies.
			'aiosp_sitemap_freq_homepage'   => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_post'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_taxonomies' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_archive'    => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_author'     => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',

		);

		/*
		 * Currently has no links, but may be added later.
		foreach ( $post_types as $pt ) {
			$help_doc_link[ 'aiosp_sitemap_prio_post_' . $pt ] = '';
			$help_doc_link[ 'aiosp_sitemap_freq_post_' . $pt ] = '';
		}
		*/

		/*
		 * Currently has no links, but may be added later.
		foreach ( $taxonomies as $tax ) {
			$help_doc_link[ 'aiosp_sitemap_prio_taxonomies_' . $tax->name ] = '';
			$help_doc_link[ 'aiosp_sitemap_freq_taxonomies_' . $tax->name ] = '';
		}
		*/

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Opengraph Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_opengraph() {
		$rtn_help_text = array(
			// Home Page Settings.
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_opengraph_setmeta'                      => sprintf( __( 'Checking this box will use the Home Title and Home Description set in %s, General Settings as the Open Graph title and description for your home page.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
			'aiosp_opengraph_sitename'                     => __( 'The Site Name is the name that is used to identify your website.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_hometitle'                    => __( 'The Home Title is the Open Graph title for your home page.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_description'                  => __( 'The Home Description is the Open Graph description for your home page.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_homeimage'                    => __( 'The Home Image is the Open Graph image for your home page.', 'all-in-one-seo-pack' ),

			// Image Settings.
			'aiosp_opengraph_defimg'                       => __( 'This option lets you choose which image will be displayed by default for the Open Graph image. You may override this on individual posts.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_fallback'                     => __( 'This option lets you fall back to the default image if no image could be found above.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimg'                         => __( 'This option sets a default image that can be used for the Open Graph image. You can upload an image, select an image from your Media Library or paste the URL of an image here.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimgwidth'                    => __( 'This option lets you set a default width for your images, where unspecified.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimgheight'                   => __( 'This option lets you set a default height for your images, where unspecified.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_meta_key'                     => __( 'Enter the name of a custom field (or multiple field names separated by commas) to use that field to specify the Open Graph image on Pages or Posts.', 'all-in-one-seo-pack' ),

			// Social Profile Links.
			'aiosp_opengraph_profile_links'                => __( 'Add URLs for your website\'s social profiles here (Facebook, Twitter, Google+, Instagram, LinkedIn), one per line.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_person_or_org'                => __( 'Are the social profile links for your website for a person or an organization?', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_social_name'                  => __( 'Add the name of the person or organization who owns these profiles.', 'all-in-one-seo-pack' ),

			// Facebook Settings.
			'aiosp_opengraph_key'                          => __( 'Enter your Facebook Admin ID here. You can enter multiple IDs separated by a comma. You can look up your Facebook ID using this tool http://findmyfbid.com/', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_appid'                        => __( 'Enter your Facebook App ID here. Information about how to get your Facebook App ID can be found at https://developers.facebook.com/docs/apps/register', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_tags'                     => __( 'Automatically generate article tags for Facebook type article when not provided.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_keywords'                 => __( 'Use keywords in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_categories'               => __( 'Use categories in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_post_tags'                => __( 'Use post tags in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_types'                        => __( 'Select which Post Types you want to set Open Graph meta values for.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_facebook_publisher'           => __( 'Link articles to the Facebook page associated with your website.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_facebook_author'              => __( 'Allows your authors to be identified by their Facebook pages as content authors on the Opengraph meta for their articles.', 'all-in-one-seo-pack' ),

			// Twitter Settings.
			'aiosp_opengraph_defcard'                      => __( 'Select the default type of Twitter Card to display.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_site'                 => __( 'Enter the Twitter username associated with your website here.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_creator'              => __( 'Allows your authors to be identified by their Twitter usernames as content creators on the Twitter cards for their posts.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_domain'               => __( 'Enter the name of your website here.', 'all-in-one-seo-pack' ),

			// Advanced Settings.
			'aiosp_opengraph_title_shortcodes'             => __( 'Run shortcodes that appear in social title meta tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_description_shortcodes'       => __( 'Run shortcodes that appear in social description meta tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_generate_descriptions'        => __( 'This option will auto generate your Open Graph descriptions from your post content instead of your post excerpt. WooCommerce users should read the documentation regarding this setting.', 'all-in-one-seo-pack' ),

			// POST META.
			'aioseop_opengraph_settings_title'             => __( 'This is the Open Graph title of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_desc'              => __( 'This is the Open Graph description of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_image'             => __( 'This option lets you select the Open Graph image that will be used for this Page or Post, overriding the default settings.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_customimg'         => __( 'This option lets you upload an image to use as the Open Graph image for this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_imagewidth'        => __( 'Enter the width for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_imageheight'       => __( 'Enter the height for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_video'             => __( 'This option lets you specify a link to the Open Graph video used on this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_videowidth'        => __( 'Enter the width for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_videoheight'       => __( 'Enter the height for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_category'          => __( 'Select the Open Graph type that best describes the content of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_facebook_debug'    => __( 'Press this button to have Facebook re-fetch and debug this page.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_section'           => __( 'This Open Graph meta allows you to add a general section name that best describes this content.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_tag'               => __( 'This Open Graph meta allows you to add a list of keywords that best describe this content.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_setcard'           => __( 'Select the Twitter Card type to use for this Page or Post, overriding the default setting.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_customimg_twitter' => __( 'This option lets you upload an image to use as the Twitter image for this Page or Post.', 'all-in-one-seo-pack' ),
		);

		$args_1 = array(
			'public' => true,
		);
		$args_2 = array(
			'public' => false,
		);

		$post_types = array_merge( get_post_types( $args_1, 'names' ), get_post_types( $args_2, 'names' ) );
		foreach ( $post_types as $pt ) {
			$rtn_help_text[ 'aiosp_opengraph_' . $pt . '_fb_object_type' ]  = __( 'Choose a default value that best describes the content of your post type.', 'all-in-one-seo-pack' );
			$rtn_help_text[ 'aiosp_opengraph_' . $pt . '_fb_object_type' ] .= '<br /><br /><a href="https://semperplugins.com/documentation/social-meta-module/#content-object-types" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		$help_doc_link = array(
			// Home Page Settings.
			'aiosp_opengraph_setmeta'                      => 'https://semperplugins.com/documentation/social-meta-module/#use-aioseo-title-and-description',
			'aiosp_opengraph_sitename'                     => 'https://semperplugins.com/documentation/social-meta-module/#site-name',
			'aiosp_opengraph_hometitle'                    => 'https://semperplugins.com/documentation/social-meta-module/#home-title-and-description',
			'aiosp_opengraph_description'                  => 'https://semperplugins.com/documentation/social-meta-module/#home-title-and-description',
			'aiosp_opengraph_homeimage'                    => 'https://semperplugins.com/documentation/social-meta-module/#home-image',

			// Image Settings.
			'aiosp_opengraph_defimg'                       => 'https://semperplugins.com/documentation/social-meta-module/#select-og-image-source',
			'aiosp_opengraph_fallback'                     => 'https://semperplugins.com/documentation/social-meta-module/#use-default-if-no-image-found',
			'aiosp_opengraph_dimg'                         => 'https://semperplugins.com/documentation/social-meta-module/#default-og-image',
			'aiosp_opengraph_dimgwidth'                    => 'https://semperplugins.com/documentation/social-meta-module/#default-image-width',
			'aiosp_opengraph_dimgheight'                   => 'https://semperplugins.com/documentation/social-meta-module/#default-image-height',
			'aiosp_opengraph_meta_key'                     => 'https://semperplugins.com/documentation/social-meta-module/#use-custom-field-for-image',

			// Social Profile Links.
			'aiosp_opengraph_profile_links'                => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',
			'aiosp_opengraph_person_or_org'                => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',
			'aiosp_opengraph_social_name'                  => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',

			// Facebook Settings.
			'aiosp_opengraph_key'                          => 'https://semperplugins.com/documentation/social-meta-module/#facebook-admin-id',
			'aiosp_opengraph_appid'                        => 'https://semperplugins.com/documentation/social-meta-module/#facebook-app-id',
			'aiosp_opengraph_gen_tags'                     => 'https://semperplugins.com/documentation/social-meta-module/#automatically-generate-article-tags',
			'aiosp_opengraph_gen_keywords'                 => 'https://semperplugins.com/documentation/social-meta-module/#use-keywords-in-article-tags',
			'aiosp_opengraph_gen_categories'               => 'https://semperplugins.com/documentation/social-meta-module/#use-categories-in-article-tags',
			'aiosp_opengraph_gen_post_tags'                => 'https://semperplugins.com/documentation/social-meta-module/#use-post-tags-in-article-tags',
			'aiosp_opengraph_types'                        => 'https://semperplugins.com/documentation/social-meta-module/#enable-facebook-meta-for',
			'aiosp_opengraph_facebook_publisher'           => 'https://semperplugins.com/documentation/social-meta-module/#show-facebook-publisher-on-articles',
			'aiosp_opengraph_facebook_author'              => 'https://semperplugins.com/documentation/social-meta-module/#show-facebook-author-on-articles',

			// Twitter Settings.
			'aiosp_opengraph_defcard'                      => 'https://semperplugins.com/documentation/social-meta-module/#default-twitter-card',
			'aiosp_opengraph_twitter_site'                 => 'https://semperplugins.com/documentation/social-meta-module/#twitter-site',
			'aiosp_opengraph_twitter_creator'              => 'https://semperplugins.com/documentation/social-meta-module/#show-twitter-author',
			'aiosp_opengraph_twitter_domain'               => 'https://semperplugins.com/documentation/social-meta-module/#twitter-domain',

			// Advanced Settings.
			'aiosp_opengraph_title_shortcodes'             => 'https://semperplugins.com/documentation/social-meta-module/#run-shortcodes-in-title',
			'aiosp_opengraph_description_shortcodes'       => 'https://semperplugins.com/documentation/social-meta-module/#run-shortcodes-in-description',
			'aiosp_opengraph_generate_descriptions'        => 'https://semperplugins.com/documentation/social-meta-module/#auto-generate-og-descriptions',

			// POST META.
			'aioseop_opengraph_settings_title'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#title',
			'aioseop_opengraph_settings_desc'              => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#description',
			'aioseop_opengraph_settings_image'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#image',
			'aioseop_opengraph_settings_customimg'         => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-image',
			'aioseop_opengraph_settings_imagewidth'        => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
			'aioseop_opengraph_settings_imageheight'       => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
			'aioseop_opengraph_settings_video'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-video',
			'aioseop_opengraph_settings_videowidth'        => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
			'aioseop_opengraph_settings_videoheight'       => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
			'aioseop_opengraph_settings_category'          => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-object-type',
			'aioseop_opengraph_settings_facebook_debug'    => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-debug',
			'aioseop_opengraph_settings_section'           => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-section',
			'aioseop_opengraph_settings_tag'               => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-tags',
			'aioseop_opengraph_settings_setcard'           => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#twitter-card-type',
			'aioseop_opengraph_settings_customimg_twitter' => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-twitter-image',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Robots Generator Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_robots_generator() {
		$rtn_help_text = array(
			'aiosp_robots_type'  => __( 'Rule Type', 'all-in-one-seo-pack' ),
			'aiosp_robots_agent' => __( 'User Agent', 'all-in-one-seo-pack' ),
			'aiosp_robots_path'  => __( 'Directory Path', 'all-in-one-seo-pack' ),
		);

		return $rtn_help_text;
	}

	/**
	 * Help Text File Editor Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_file_editor() {
		return array(
			'aiosp_file_editor_htaccfile' => __( '.htaccess editor', 'all-in-one-seo-pack' ),
		);
	}

	/**
	 * Help Text Importer Exporter Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_importer_exporter() {
		$rtn_help_text = array(
			// Possible HTML link concept IF links become usable inside jQuery UI Tooltips.
			/* translators: %1$s and 12$s are placeholders, which means these should not be translated. These will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_importer_exporter_import_submit'     => sprintf( __( 'Choose a valid %1$s .ini file and click &quot;Import&quot; to import options from a previous state or install of %1$s.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
			'aiosp_importer_exporter_export_choices'    => __( 'You may choose to export settings from active modules, and content from post data.', 'all-in-one-seo-pack' ),
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			'aiosp_importer_exporter_export_post_types' => sprintf( __( 'Select which Post Types you want to export your %s meta data for.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
		);

		$help_doc_link = array(
			'aiosp_importer_exporter_import_submit'     => 'https://semperplugins.com/documentation/importer-exporter-module/',
			'aiosp_importer_exporter_export_choices'    => 'https://semperplugins.com/documentation/importer-exporter-module/',
			'aiosp_importer_exporter_export_post_types' => 'https://semperplugins.com/documentation/importer-exporter-module/',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Bad Robots Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_bad_robots() {
		return array(
			'aiosp_bad_robots_block_bots'   => __( 'Block requests from user agents that are known to misbehave with 503.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_block_refer'  => __( 'Block Referral Spam using HTTP.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_track_blocks' => __( 'Log and show recent requests from blocked bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_edit_blocks'  => __( 'Check this to edit the list of disallowed user agents for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_blocklist'    => __( 'This is the list of disallowed user agents used for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_referlist'    => __( 'This is the list of disallowed referers used for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_blocked_log'  => __( 'Shows log of most recent requests from blocked bots. Note: this will not track any bots that were already blocked at the web server / .htaccess level.', 'all-in-one-seo-pack' ),
		);
	}

	/**
	 * Help Text Post Meta (Core Module)
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @see self::_help_text_opengraph() Also adds Post Meta info.
	 *
	 * @return array
	 */
	private function help_text_post_meta() {
		$rtn_help_text = array(
			'aiosp_snippet'           => __( 'A preview of what this page might look like in search engine results.', 'all-in-one-seo-pack' ),
			'aiosp_title'             => __( 'A custom title that shows up in the title tag for this page.', 'all-in-one-seo-pack' ),
			'aiosp_description'       => __( 'The META description for this page. This will override any autogenerated descriptions.', 'all-in-one-seo-pack' ),
			'aiosp_keywords'          => __( 'A comma separated list of your most important keywords for this page that will be written as META keywords.', 'all-in-one-seo-pack' ),
			'aiosp_custom_link'       => __( 'Override the canonical URLs for this post.', 'all-in-one-seo-pack' ),
			'aiosp_noindex'           => __( 'Check this box to ask search engines not to index this page.', 'all-in-one-seo-pack' ),
			'aiosp_nofollow'          => __( 'Check this box to ask search engines not to follow links from this page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_exclude'   => __( 'Don\'t display this page in the sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_disable'           => __( 'Disable SEO on this page.', 'all-in-one-seo-pack' ),
			'aiosp_disable_analytics' => __( 'Disable Google Analytics on this page.', 'all-in-one-seo-pack' ),
		);

		$help_doc_link = array(
			'aiosp_snippet'           => 'https://semperplugins.com/documentation/post-settings/#preview-snippet',
			'aiosp_title'             => 'https://semperplugins.com/documentation/post-settings/#title',
			'aiosp_description'       => 'https://semperplugins.com/documentation/post-settings/#description',
			'aiosp_keywords'          => 'https://semperplugins.com/documentation/post-settings/#keywords',
			'aiosp_custom_link'       => 'https://semperplugins.com/documentation/post-settings/#custom-canonical-url',
			'aiosp_noindex'           => 'https://semperplugins.com/documentation/post-settings/#robots-meta-noindex',
			'aiosp_nofollow'          => 'https://semperplugins.com/documentation/post-settings/#robots-meta-nofollow',
			'aiosp_sitemap_exclude'   => 'https://semperplugins.com/documentation/post-settings/#exclude-from-sitemap',
			'aiosp_disable'           => 'https://semperplugins.com/documentation/post-settings/#disable-on-this-post',
			'aiosp_disable_analytics' => 'https://semperplugins.com/documentation/post-settings/#disable-google-analytics',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Get Help Text
	 *
	 * Gets an individual help text if it exists, otherwise an error is returned
	 * to notify the AIOSEOP Devs.
	 * NOTE: Returning an empty string causes issues with the UI.
	 *
	 * @since 2.4.2
	 *
	 * @param string $slug Module option slug.
	 * @return string
	 */
	public function get_help_text( $slug ) {
		if ( isset( $this->help_text[ $slug ] ) ) {
			return esc_html( $this->help_text[ $slug ] );
		}
		return 'DEV: Missing Help Text: ' . $slug;
	}
}
