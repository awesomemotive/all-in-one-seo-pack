<?php

include_once AIOSEOP_UNIT_TESTING_DIR . '\base\class-aioseop-notices-testcase.php';

class Test_AIOSEOP_Notices_AdminEnqueueScripts extends AIOSEOP_Notices_TestCase {

	/**
	 * Set Up
	 */
	public function setUp() {
		parent::setUp();

		global $aiosp;
		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		if ( null === $aiosp ) {
			$aiosp = new All_in_One_SEO_Pack();
		}
	}
	/**
	 * Mock Notice
	 *
	 * @return array
	 */
	protected function mock_notice() {
		$rtn_notice               = parent::mock_notice();
		$rtn_notice['delay_time'] = 0;
		return $rtn_notice;
	}

//	/**
//	 * @param array $notice
//	 */
//	public function test_add_notice( $notice = array() ) {
//		parent::test_add_notice( $notice );
//	}

	/**
	 * Test enqueue scripts on screens.
	 *
	 * Attempted test
	 *
	 * * should not enqueue if before delayed amount of time.
	 * * notices with screen restrictions should be true only on set screens
	 * * (Test Render) Should not display content if script doesn't enqueue; also should send a Debug notice.
	 *
	 *
	 */
	public function t_enqueue_scripts_on_screens() {

		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		$this->validate_class_aioseop_notices( $aioseop_notices );

		// Should be empty.
		$this->assertTrue( empty( $aioseop_notices->active_notices ) );

		$notice = $this->mock_notice();

		// Insert Successful and activated.
		$this->assertTrue( $aioseop_notices->insert_notice( $notice ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $aioseop_notices->active_notices[ $notice['slug'] ] );

		$this->validate_class_aioseop_notices( $aioseop_notices );

		$admin_screens = $this->data_screens();

		foreach ( $admin_screens as $screen ) {
			set_current_screen( $screen['screen_id'] );
			$this->go_to( $screen['url'] );

			$aioseop_notices = new AIOSEOP_Notices();

			set_current_screen( $screen['screen_id'] );

			do_action( 'admin_enqueue_scripts' );

			$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen['screen_id'] );
			$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'enqueued' ) );
			$t01 = wp_script_is( 'aioseop-admin-notice-js', 'done' );
			// //$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'done' ) );
			$t02 = wp_script_is( 'aioseop-admin-notice-js', 'to_do' );
			// //$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'to_do' ) );
		}

//		foreach ( $admin_screen_urls as $screen => $screen_url ) {
//			set_current_screen( $screen );
//			$this->go_to( $screen_url );
//
//			$aioseop_notices = new AIOSEOP_Notices();
//
//			set_current_screen( $screen );
//
//			do_action( 'admin_enqueue_scripts' );
//
//			$t01 = wp_script_is( 'aioseop-admin-notice-js', 'registered' );
//			$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen );
//			$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'enqueued' ) );
//			$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'done' ) );
//			$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'to_do' ) );
//
//		}

	}

	/**
	 * Test enqueue scripts on screens.
	 *
	 * Function: Enqueue Scripts and Styles with the WP Enqueue hook.
	 * Expected: Registered and enqueue scripts on target screens; provided by data_screens.
	 * Actual: As expected; no current issue.
	 * Result: Scripts are ready to be printed via enqueue.
	 *
	 * * should not enqueue if before delayed amount of time.
	 * * -notices with screen restrictions should be true only on set screens
	 * * (Test Render) Should not display content if script doesn't enqueue; also should send a Debug notice.
	 *
	 * @dataProvider data_screens
	 *
	 * @param string $screen_id
	 * @param string $url
	 * @param string $dir
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {

		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		$this->validate_class_aioseop_notices( $aioseop_notices );

		// Should be empty.
		$this->assertTrue( empty( $aioseop_notices->active_notices ) );

		$notice = $this->mock_notice();

		// Insert Successful and activated.
		$this->assertTrue( $aioseop_notices->insert_notice( $notice ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $aioseop_notices->active_notices[ $notice['slug'] ] );

		$this->validate_class_aioseop_notices( $aioseop_notices );

		wp_deregister_script( 'aioseop-admin-notice-js' );
		wp_deregister_style( 'aioseop-admin-notice-css' );
		$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		set_current_screen( $screen_id );
		$this->go_to( $url );

		$aioseop_notices = new AIOSEOP_Notices();

		set_current_screen( $screen_id );

		$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		do_action( 'admin_enqueue_scripts' );

		$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );
		$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'enqueued' ) );
	}

//	/**
//	 * DataProvider for Screens
//	 * @return array
//	 */
//	public function data_screens() {
//		$notice = $this->mock_notice();
//
//		$screens = array();
//		if ( empty( $notice['screens'] ) ) {
//			$screens = array_merge( $this->data_screens_wp(), $this->data_screens_aioseop() );
//		} elseif ( 'aioseop' === $notice['screens'] ) {
//			$screens = $this->data_screens_aioseop();
//		} elseif ( ! empty( $notice['screens'] ) ) {
//			$all_screens = array_merge( $this->data_screens_wp(), $this->data_screens_aioseop() );
//
//			foreach ( $notice['screens'] as $n_screen ) {
//				foreach ( $all_screens as $screen ) {
//					if ( $n_screen === $screen['screen_id'] ) {
//						$screens[] = $screen;
//					}
//				}
//			}
//		}
//
//		return $screens;
//	}
//
//	/**
//	 * @return array
//	 */
//	protected function data_screens_wp() {
//		return array(
//			array(
//				'screen_id' => 'dashboard',
//				'url'       => site_url() . '/wp-admin/index.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/index.php',
//			),
//			array(
//				'screen_id' => 'update-core',
//				'url'       => site_url() . '/wp-admin/update-core.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/update-core.php',
//			),
//
//			array(
//				'screen_id' => 'edit-post',
//				'url'       => site_url() . '/wp-admin/edit.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit.php',
//			),
//			array(
//				'screen_id' => 'post',
//				'url'       => site_url() . '/wp-admin/post-new.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post-new.php',
//			),
////			array(
////				'screen_id' => 'post',
////				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
////			),
//			array(
//				'screen_id' => 'edit-category',
//				'url'       => site_url() . '/wp-admin/edit-tags.php?taxonomy=category',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?taxonomy=category',
//			),
////			array(
////				'screen_id' => 'edit-category',
////				'url'       => site_url() . '/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=###&post_type=post',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=###&post_type=post',
////			),
//			array(
//				'screen_id' => 'edit-post_tag',
//				'url'       => site_url() . '/wp-admin/edit-tags.php?taxonomy=post_tag',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?taxonomy=post_tag',
//			),
////			array(
////				'screen_id' => 'edit-post_tag',
////				'url'       => site_url() . '/wp-admin/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=###&post_type=post',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=###&post_type=post',
////			),
//			// Custom Post Types.
//			// Custom Taxonomies.
//			array(
//				'screen_id' => 'upload',
//				'url'       => site_url() . '/wp-admin/upload.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/upload.php',
//			),
//			array(
//				'screen_id' => 'media',
//				'url'       => site_url() . '/wp-admin/media-new.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/media-new.php',
//			),
////			array(
////				'screen_id' => 'attachment',
////				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
////			),
//			array(
//				'screen_id' => 'edit-page',
//				'url'       => site_url() . '/wp-admin/edit.php?post_type=page',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit.php?post_type=page',
//			),
//			array(
//				'screen_id' => 'page',
//				'url'       => site_url() . '/wp-admin/post-new.php?post_type=page',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post-new.php?post_type=page',
//			),
////			array(
////				'screen_id' => 'page',
////				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
////			),
//			array(
//				'screen_id' => 'edit-comments',
//				'url'       => site_url() . '/wp-admin/edit-comments.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-comments.php',
//			),
////			array(
////				'screen_id' => 'comment',
////				'url'       => site_url() . '/wp-admin/comment.php?action=editcomment&c=###',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/comment.php?action=editcomment&c=###',
////			),
//			array(
//				'screen_id' => 'themes',
//				'url'       => site_url() . '/wp-admin/themes.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/themes.php',
//			),
//			array(
//				'screen_id' => 'widgets',
//				'url'       => site_url() . '/wp-admin/widgets.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/widgets.php',
//			),
//			array(
//				'screen_id' => 'nav-menus',
//				'url'       => site_url() . '/wp-admin/nav-menus.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/nav-menus.php',
//			),
//			array(
//				'screen_id' => 'theme-editor',
//				'url'       => site_url() . '/wp-admin/theme-editor.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/theme-editor.php',
//			),
////			array(
////				'screen_id' => 'appearance_page_{page}',
////				'url'       => site_url() . '/wp-admin/themes.php?page={page}',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/themes.php?page={page}',
////			),
//
//			array(
//				'screen_id' => 'plugins',
//				'url'       => site_url() . '/wp-admin/plugins.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugins.php',
//			),
//			array(
//				'screen_id' => 'plugin-install',
//				'url'       => site_url() . '/wp-admin/plugin-install.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugin-install.php',
//			),
//			array(
//				'screen_id' => 'plugin-editor',
//				'url'       => site_url() . '/wp-admin/plugin-editor.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugin-editor.php',
//			),
//
//			array(
//				'screen_id' => 'users',
//				'url'       => site_url() . '/wp-admin/users.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/users.php',
//			),
//			array(
//				'screen_id' => 'user-new',
//				'url'       => site_url() . '/wp-admin/user-new.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/user-new.php',
//			),
////			array(
////				'screen_id' => 'user-edit',
////				'url'       => site_url() . '/wp-admin/user-edit.php?user_id=###',
////				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/user-edit.php?user_id=###',
////			),
//			array(
//				'screen_id' => 'profile',
//				'url'       => site_url() . '/wp-admin/profile.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/profile.php',
//			),
//
//			array(
//				'screen_id' => 'tools',
//				'url'       => site_url() . '/wp-admin/tools.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/tools.php',
//			),
//			array(
//				'screen_id' => 'import',
//				'url'       => site_url() . '/wp-admin/import.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/import.php',
//			),
//			array(
//				'screen_id' => 'export',
//				'url'       => site_url() . '/wp-admin/export.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/export.php',
//			),
//
//			array(
//				'screen_id' => 'options-general',
//				'url'       => site_url() . '/wp-admin/options-general.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-general.php',
//			),
//			array(
//				'screen_id' => 'options-writing',
//				'url'       => site_url() . '/wp-admin/options-writing.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-writing.php',
//			),
//			array(
//				'screen_id' => 'options-reading',
//				'url'       => site_url() . '/wp-admin/options-reading.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-reading.php',
//			),
//			array(
//				'screen_id' => 'options-discussion',
//				'url'       => site_url() . '/wp-admin/options-discussion.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-discussion.php',
//			),
//			array(
//				'screen_id' => 'options-media',
//				'url'       => site_url() . '/wp-admin/options-media.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-media.php',
//			),
//			array(
//				'screen_id' => 'options-permalink',
//				'url'       => site_url() . '/wp-admin/options-permalink.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-permalink.php',
//			),
//
//		);
//	}
//
//	protected function data_screens_aioseop() {
//		return array(
//			array(
//				'screen_id' => 'toplevel_page_all-in-one-seo-pack/aioseop_class',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Faioseop_class.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Faioseop_class.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_performance',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_performance.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_performance.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_sitemap',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_sitemap.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_sitemap.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_aiosp_opengraph',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aiosp_opengraph',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aiosp_opengraph',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_aiosp_robots_generator',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aiosp_robots_generator',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aiosp_robots_generator',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_file_editor',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_file_editor.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_file_editor.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_importer_exporter',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_importer_exporter.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_importer_exporter.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_bad_robots',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_bad_robots.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_bad_robots.php',
//			),
//			array(
//				'screen_id' => 'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_feature_manager',
//				'url'       => site_url() . '/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_feature_manager.php',
//				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=aioseop%2Fmodules%2Faioseop_feature_manager.php',
//			),
//		);
//
//	}
}
