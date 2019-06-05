<?php
/**
 * General functions file.
 *
 * We'll eventually move these to a better place, and figure out ones not being used anymore.
 *
 * @package All-in-One-SEO-Pack
 * @version 2.3.13
 */

if ( ! function_exists( 'aioseop_get_permalink' ) ) {
	/**
	 * Support UTF8 URLs.
	 *
	 * @param int|object|null $post_id The post.
	 */
	function aioseop_get_permalink( $post_id = null ) {
		if ( is_null( $post_id ) ) {
			global $post;
			$post_id = $post;
		}

		return urldecode( get_permalink( $post_id ) );
	}
}

if ( ! function_exists( 'aioseop_load_modules' ) ) {
	/**
	 * Load the module manager.
	 */
	function aioseop_load_modules() {
		global $aioseop_modules, $aioseop_module_list;
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_manager.php' );
		$aioseop_modules = new All_in_One_SEO_Pack_Module_Manager( apply_filters( 'aioseop_module_list', $aioseop_module_list ) );
		$aioseop_modules->load_modules();
	}
}

if ( ! function_exists( 'aioseop_get_options' ) ) {
	/**
	 * @return mixed|void
	 */
	function aioseop_get_options() {
		global $aioseop_options;
		$aioseop_options = get_option( 'aioseop_options' );
		$aioseop_options = apply_filters( 'aioseop_get_options', $aioseop_options );

		return $aioseop_options;
	}
}

if ( ! function_exists( 'aioseop_update_settings_check' ) ) {
	/**
	 * Check if settings need to be updated / migrated from old version.
	 *
	 * @TODO See when this is from and if we can move it elsewhere... our new db updates/upgrades class? This is called every single time a page is loaded both on the front-end or backend.
	 */
	function aioseop_update_settings_check() {
		global $aioseop_options;
		if ( empty( $aioseop_options ) || isset( $_POST['aioseop_migrate_options'] ) ) {
			aioseop_initialize_options();
		}
		// WPML has now attached to filters, read settings again so they can be translated.
		aioseop_get_options();
		$update_options = false;
		if ( ! empty( $aioseop_options ) ) {
			if ( ! empty( $aioseop_options['aiosp_archive_noindex'] ) ) { // Migrate setting for noindex archives.
				$aioseop_options['aiosp_archive_date_noindex'] = $aioseop_options['aiosp_archive_author_noindex'] = $aioseop_options['aiosp_archive_noindex'];
				unset( $aioseop_options['aiosp_archive_noindex'] );
				$update_options = true;
			}
			if ( ! empty( $aioseop_options['aiosp_archive_title_format'] ) && empty( $aioseop_options['aiosp_date_title_format'] ) ) {
				$aioseop_options['aiosp_date_title_format'] = $aioseop_options['aiosp_archive_title_format'];
				unset( $aioseop_options['aiosp_archive_title_format'] );
				$update_options = true;
			}
			if ( ! empty( $aioseop_options['aiosp_archive_title_format'] ) && ( $aioseop_options['aiosp_archive_title_format'] === '%date% | %site_title%' ) ) {
				$aioseop_options['aiosp_archive_title_format'] = '%archive_title% | %site_title%';
				$update_options                                = true;
			}
			if ( $update_options ) {
				update_option( 'aioseop_options', $aioseop_options );
			}
		}
	}
}

if ( ! function_exists( 'aioseop_initialize_options' ) ) {
	/**
	 * Initialize settings to defaults. Changed name from the abstruse 'aioseop_mrt_mkarry' to 'aioseop_initialize_options'.
	 *
	 * @TODO Should also move.
	 */
	function aioseop_initialize_options() {
		global $aiosp;
		global $aioseop_options;
		$naioseop_options = $aiosp->default_options();

		if ( get_option( 'aiosp_post_title_format' ) ) {
			foreach ( $naioseop_options as $aioseop_opt_name => $value ) {
				if ( $aioseop_oldval = get_option( $aioseop_opt_name ) ) {
					$naioseop_options[ $aioseop_opt_name ] = $aioseop_oldval;
				}
				if ( $aioseop_oldval == '' ) {
					$naioseop_options[ $aioseop_opt_name ] = '';
				}
				delete_option( $aioseop_opt_name );
			}
		}
		add_option( 'aioseop_options', $naioseop_options );
		$aioseop_options = $naioseop_options;
	}
}

if ( ! function_exists( 'aioseop_get_version' ) ) {
	/**
	 * Returns the version.
	 *
	 * I'm not sure why we have BOTH a function and a constant for this. -mrt
	 *
	 * @return string
	 */
	function aioseop_get_version() {
		return AIOSEOP_VERSION;
	}
}

if ( ! function_exists( 'aioseop_option_isset' ) ) {
	/**
	 * Checks if an option isset.
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	function aioseop_option_isset( $option ) {
		global $aioseop_options;

		return ( isset( $aioseop_options[ $option ] ) && $aioseop_options[ $option ] );
	}
}

if ( ! function_exists( 'aioseop_addmycolumns' ) ) {
	/**
	 * Adds posttype columns.
	 *
	 */
	function aioseop_addmycolumns() {
		global $aioseop_options, $pagenow;
		$aiosp_posttypecolumns = array();
		if ( ! empty( $aioseop_options ) && ! empty( $aioseop_options['aiosp_posttypecolumns'] ) ) {
			$aiosp_posttypecolumns = $aioseop_options['aiosp_posttypecolumns'];
		}
		if ( ! empty( $pagenow ) && ( $pagenow === 'upload.php' ) ) {
			$post_type = 'attachment';
		} elseif ( ! isset( $_REQUEST['post_type'] ) ) {
			$post_type = 'post';
		} else {
			$post_type = $_REQUEST['post_type'];
		}
		if ( is_array( $aiosp_posttypecolumns ) && in_array( $post_type, $aiosp_posttypecolumns ) ) {
			add_action( 'admin_head', 'aioseop_admin_head' );
			if ( $post_type === 'page' ) {
				add_filter( 'manage_pages_columns', 'aioseop_mrt_pcolumns' );
			} elseif ( $post_type === 'attachment' ) {
				add_filter( 'manage_media_columns', 'aioseop_mrt_pcolumns' );
			} else {
				add_filter( 'manage_posts_columns', 'aioseop_mrt_pcolumns' );
			}
			if ( $post_type === 'attachment' ) {
				add_action( 'manage_media_custom_column', 'aioseop_mrt_pccolumn', 10, 2 );
			} elseif ( is_post_type_hierarchical( $post_type ) ) {
				add_action( 'manage_pages_custom_column', 'aioseop_mrt_pccolumn', 10, 2 );
			} else {
				add_action( 'manage_posts_custom_column', 'aioseop_mrt_pccolumn', 10, 2 );
			}
		}
	}
}

if ( ! function_exists( 'aioseop_mrt_pcolumns' ) ) {

	/**
	 * @param $aioseopc
	 *
	 * @return mixed
	 */
	function aioseop_mrt_pcolumns( $aioseopc ) {
		global $aioseop_options;
		$aioseopc['seotitle'] = __( 'SEO Title', 'all-in-one-seo-pack' );
		$aioseopc['seodesc']  = __( 'SEO Description', 'all-in-one-seo-pack' );
		if ( empty( $aioseop_options['aiosp_togglekeywords'] ) ) {
			$aioseopc['seokeywords'] = __( 'SEO Keywords', 'all-in-one-seo-pack' );
		}

		return $aioseopc;
	}
}

if ( ! function_exists( 'aioseop_admin_head' ) ) {

	function aioseop_admin_head() {
		wp_enqueue_script( 'aioseop_welcome_js', AIOSEOP_PLUGIN_URL . 'js/quickedit_functions.js', array( 'jquery' ), AIOSEOP_VERSION );
		?>
		<style>
			.aioseop_mpc_admin_meta_options {
				float: left;
				display: block;
				opacity: 1;
				max-height: 75px;
				overflow: hidden;
				width: 100%;
			}

			.aioseop_mpc_admin_meta_options.aio_editing {
				max-height: initial;
				overflow: visible;
			}

			.aioseop_mpc_admin_meta_content {
				float: left;
				width: 100%;
				margin: 0 0 10px 0;
			}

			td.seotitle.column-seotitle,
			td.seodesc.column-seodesc,
			td.seokeywords.column-seokeywords {
				overflow: visible;
			}

			@media screen and (max-width: 782px) {
				body.wp-admin th.column-seotitle, th.column-seodesc, th.column-seokeywords, td.seotitle.column-seotitle, td.seodesc.column-seodesc, td.seokeywords.column-seokeywords {
					display: none;
				}
			}
		</style>
		<?php
		wp_print_scripts( array( 'sack' ) );
		?>
		<script type="text/javascript">
			//<![CDATA[
			var aioseopadmin = {
				blogUrl: "<?php print get_bloginfo( 'url' ); ?>",
				pluginUrl: "<?php print AIOSEOP_PLUGIN_URL; ?>",
				requestUrl: "<?php print WP_ADMIN_URL . '/admin-ajax.php'; ?>",
				imgUrl: "<?php print AIOSEOP_PLUGIN_IMAGES_URL; ?>",
				Edit: "<?php _e( 'Edit', 'all-in-one-seo-pack' ); ?>",
				Post: "<?php _e( 'Post', 'all-in-one-seo-pack' ); ?>",
				Save: "<?php _e( 'Save', 'all-in-one-seo-pack' ); ?>",
				Cancel: "<?php _e( 'Cancel', 'all-in-one-seo-pack' ); ?>",
				postType: "post",
				pleaseWait: "<?php _e( 'Please wait...', 'all-in-one-seo-pack' ); ?>",
				slugEmpty: "<?php _e( 'Slug may not be empty!', 'all-in-one-seo-pack' ); ?>",
				Revisions: "<?php _e( 'Revisions', 'all-in-one-seo-pack' ); ?>",
				Time: "<?php _e( 'Insert time', 'all-in-one-seo-pack' ); ?>"
			}
			//]]>
		</script>
		<?php
	}
}

if ( ! function_exists( 'aioseop_handle_ignore_notice' ) ) {

	function aioseop_handle_ignore_notice() {

		if ( ! empty( $_GET ) ) {
			global $current_user;
			$user_id = $current_user->ID;

			if ( ! empty( $_GET['aioseop_reset_notices'] ) ) {
				delete_user_meta( $user_id, 'aioseop_ignore_notice' );
			}
			if ( ! empty( $_GET['aioseop_ignore_notice'] ) ) {
				add_user_meta( $user_id, 'aioseop_ignore_notice', $_GET['aioseop_ignore_notice'], false );
			}
		}
	}
}

if ( ! function_exists( 'aioseop_output_notice' ) ) {

	/**
	 * @param $message
	 * @param string $id
	 * @param string $class
	 *
	 * @return bool
	 */
	function aioseop_output_notice( $message, $id = '', $class = 'updated fade' ) {
		$class = 'aioseop_notice ' . $class;
		if ( ! empty( $class ) ) {
			$class = ' class="' . esc_attr( $class ) . '"';
		}
		if ( ! empty( $id ) ) {
			$class .= ' id="' . esc_attr( $id ) . '"';
		}
		$dismiss = ' ';
		echo "<div{$class}>" . wp_kses_post( $message ) . '</div>';

		return true;
	}
}

if ( ! function_exists( 'aioseop_output_dismissable_notice' ) ) {

	/**
	 * @param $message
	 * @param string $id
	 * @param string $class
	 *
	 * @return bool
	 */
	function aioseop_output_dismissable_notice( $message, $id = '', $class = 'updated fade' ) {
		global $current_user;
		if ( ! empty( $current_user ) ) {
			$user_id = $current_user->ID;
			$msgid   = md5( $message );
			$ignore  = get_user_meta( $user_id, 'aioseop_ignore_notice' );
			if ( ! empty( $ignore ) && in_array( $msgid, $ignore ) ) {
				return false;
			}
			global $wp;
			$qa = array();
			wp_parse_str( $_SERVER['QUERY_STRING'], $qa );
			$qa['aioseop_ignore_notice'] = $msgid;
			$url                         = '?' . build_query( $qa );
			$message                     = '<p class=alignleft>' . $message . '</p><p class="alignright"><a class="aioseop_dismiss_link" href="' . $url . '">Dismiss</a></p>';
		}

		return aioseop_output_notice( $message, $id, $class );
	}
}

if ( ! function_exists( 'aioseop_ajax_save_meta' ) ) {

	function aioseop_ajax_save_meta() {
		if ( ! empty( $_POST['_inline_edit'] ) && ( $_POST['_inline_edit'] !== 'undefined' ) ) {
			check_ajax_referer( 'inlineeditnonce', '_inline_edit' );
		}
		$post_id  = intval( $_POST['post_id'] );
		$new_meta = strip_tags( $_POST['new_meta'] );
		$target   = $_POST['target_meta'];
		check_ajax_referer( 'aioseop_meta_' . $target . '_' . $post_id, '_nonce' );
		$result = '';
		if ( in_array(
			$target, array(
				'title',
				'description',
				'keywords',
			)
		) && current_user_can( 'edit_post', $post_id )
		) {
			update_post_meta( $post_id, '_aioseop_' . $target, esc_attr( $new_meta ) );
			$result = get_post_meta( $post_id, '_aioseop_' . $target, true );
		} else {
			die();
		}
		if ( $result != '' ) :
			$label = "<label id='aioseop_label_{$target}_{$post_id}' class='aioseop-label-quickedit' for='{$target}editlink{$post_id}'>" . $result . '</label>';
		else :
			$label = "<label id='aioseop_label_{$target}_{$post_id}' class='aioseop-label-quickedit' for='{$target}editlink{$post_id}'></label><strong><i>" . __( 'No', 'all-in-one-seo-pack' ) . ' ' . $target . '</i></strong>';
		endif;
		$nonce  = wp_create_nonce( "aioseop_meta_{$target}_{$post_id}" );
		$output = '<a id="' . $target . 'editlink' . $post_id . '" '
			. 'class="aioseop_edit_link aioseop-icon-cog-edit" '
			. 'href="javascript:void(0);" '
			. 'onclick=\'aioseop_ajax_edit_meta_form(' . $post_id . ', "' . $target . '", "' . $nonce . '");return false;\' '
			. 'title="' . __( 'Edit' ) . '"></a>';
		$output .= $label;
		die(
			"jQuery('div#aioseop_" . $target . '_' . $post_id . "').fadeOut('fast', function() {
				 var my_label = " . json_encode( $output ) . ";
				 jQuery('div#aioseop_" . $target . '_' . $post_id . "').html(my_label).fadeIn('fast');
			});"
		);
	}
}

if ( ! function_exists( 'aioseop_ajax_init' ) ) {

	function aioseop_ajax_init() {
		if ( ! empty( $_POST ) && ! empty( $_POST['settings'] ) && ( ! empty( $_POST['nonce-aioseop'] ) || ( ! empty( $_POST['nonce-aioseop-edit'] ) ) ) && ! empty( $_POST['options'] ) ) {
			$_POST    = stripslashes_deep( $_POST );
			$settings = esc_attr( $_POST['settings'] );
			if ( ! defined( 'AIOSEOP_AJAX_MSG_TMPL' ) ) {
				define( 'AIOSEOP_AJAX_MSG_TMPL', "jQuery('div#aiosp_$settings').fadeOut('fast', function(){jQuery('div#aiosp_$settings').html('%s').fadeIn('fast');});" );
			}

			if ( ! wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) ) {
				die( sprintf( AIOSEOP_AJAX_MSG_TMPL, __( 'Unauthorized access; try reloading the page.', 'all-in-one-seo-pack' ) ) );
			}
		} else {
			die( 0 );
		}
	}
}

/**
 * @param $return
 * @param $url
 * @param $attr
 *
 * @return mixed
 */
function aioseop_embed_handler_html( $return, $url, $attr ) {
	return AIO_ProGeneral::aioseop_embed_handler_html();
}

if ( ! function_exists( 'aioseop_ajax_save_url' ) ) {

	function aioseop_ajax_save_url() {
		$valid   = true;
		$invalid_msg    = null;
		aioseop_ajax_init();
		$options = array();
		parse_str( $_POST['options'], $options );
		foreach ( $options as $k => $v ) {
			// all values are mandatory while adding to the sitemap.
			// this should work in the same way for news and video sitemaps too, but tackling only regular sitemaps for now.
			if ( 'sitemap_addl_pages' === $_POST['settings'] ) {
				if ( empty( $v ) ) {
					$valid    = false;
				} elseif ( 'aiosp_sitemap_addl_url' === $k && ! aiosp_common::is_url_valid( $v ) ) {
					$valid    = false;
					$invalid_msg    = __( 'Please provide absolute URLs (including http or https).', 'all-in-one-seo-pack' );
				}
				if ( ! $valid ) {
					break;
				}
			}
			$_POST[ $k ] = $v;
		}
		if ( $valid ) {
			$_POST['action'] = 'aiosp_update_module';
			global $aiosp, $aioseop_modules;
			aioseop_load_modules();
			$aiosp->admin_menu();
			if ( ! empty( $_POST['settings'] ) && ( $_POST['settings'] === 'video_sitemap_addl_pages' ) ) {
				$module = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_Video_Sitemap' );
			} elseif ( ! empty( $_POST['settings'] ) && ( $_POST['settings'] === 'news_sitemap_addl_pages' ) ) {
				$module = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_News_Sitemap' );
			} else {
				$module = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_Sitemap' );
			}
			$_POST['location'] = null;
			$_POST['Submit']   = 'ajax';
			$module->add_page_hooks();
			$prefix = $module->get_prefix();
			$_POST  = $module->get_current_options( $_POST, null );
			$module->handle_settings_updates( null );
			$options = $module->get_current_options( array(), null );
			$output  = $module->display_custom_options(
				'', array(
					'name'  => $prefix . 'addl_pages',
					'type'  => 'custom',
					'save'  => true,
					'value' => $options[ $prefix . 'addl_pages' ],
					'attr'  => '',
				)
			);
			$output  = str_replace( "'", "\'", $output );
			$output  = str_replace( "\n", '\n', $output );
		} else {
			if ( $invalid_msg ) {
				$output = $invalid_msg;
			} else {
				$output   = __( 'All values are mandatory.', 'all-in-one-seo-pack' );
			}
		}
		die( sprintf( AIOSEOP_AJAX_MSG_TMPL, $output ) );
	}
}

if ( ! function_exists( 'aioseop_ajax_delete_url' ) ) {

	function aioseop_ajax_delete_url() {
		aioseop_ajax_init();
		$options         = array();
		$options         = esc_attr( $_POST['options'] );
		$_POST['action'] = 'aiosp_update_module';
		global $aiosp, $aioseop_modules;
		aioseop_load_modules();
		$aiosp->admin_menu();
		$module            = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_Sitemap' );
		$_POST['location'] = null;
		$_POST['Submit']   = 'ajax';
		$module->add_page_hooks();
		$_POST = (array) $module->get_current_options( $_POST, null );
		if ( ! empty( $_POST['aiosp_sitemap_addl_pages'] ) && is_object( $_POST['aiosp_sitemap_addl_pages'] ) ) {
			$_POST['aiosp_sitemap_addl_pages'] = (array) $_POST['aiosp_sitemap_addl_pages'];
		}
		if ( ! empty( $_POST['aiosp_sitemap_addl_pages'] ) && ( ! empty( $_POST['aiosp_sitemap_addl_pages'][ $options ] ) ) ) {
			unset( $_POST['aiosp_sitemap_addl_pages'][ $options ] );
			if ( empty( $_POST['aiosp_sitemap_addl_pages'] ) ) {
				$_POST['aiosp_sitemap_addl_pages'] = '';
			} else {
				$_POST['aiosp_sitemap_addl_pages'] = json_encode( $_POST['aiosp_sitemap_addl_pages'] );
			}
			$module->handle_settings_updates( null );
			$options = $module->get_current_options( array(), null );
			$output  = $module->display_custom_options(
				'', array(
					'name'  => 'aiosp_sitemap_addl_pages',
					'type'  => 'custom',
					'save'  => true,
					'value' => $options['aiosp_sitemap_addl_pages'],
					'attr'  => '',
				)
			);
			$output  = str_replace( "'", "\'", $output );
			$output  = str_replace( "\n", '\n', $output );
		} else {
			$output = sprintf( __( 'Row %s not found; no rows were deleted.', 'all-in-one-seo-pack' ), esc_attr( $options ) );
		}
		die( sprintf( AIOSEOP_AJAX_MSG_TMPL, $output ) );
	}
}

if ( ! function_exists( 'aioseop_ajax_scan_header' ) ) {

	function aioseop_ajax_scan_header() {
		$_POST['options'] = 'foo';
		aioseop_ajax_init();
		$options = array();
		parse_str( $_POST['options'], $options );
		foreach ( $options as $k => $v ) {
			$_POST[ $k ] = $v;
		}
		$_POST['action']   = 'aiosp_update_module';
		$_POST['location'] = null;
		$_POST['Submit']   = 'ajax';
		ob_start();
		do_action( 'wp' );
		global $aioseop_modules;
		$module = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_Opengraph' );
		wp_head();
		$output = ob_get_clean();
		global $aiosp;
		$output   = $aiosp->html_string_to_array( $output );
		$meta     = '';
		$metatags = array(
			'facebook' => array( 'name' => 'property', 'value' => 'content' ),
			'twitter'  => array( 'name' => 'name', 'value' => 'value' ),
		);
		$metadata = array(
			'facebook' => array(
				'title'       => 'og:title',
				'type'        => 'og:type',
				'url'         => 'og:url',
				'thumbnail'   => 'og:image',
				'sitename'    => 'og:site_name',
				'key'         => 'fb:admins',
				'description' => 'og:description',
			),
			'twitter'  => array(
				'card'        => 'twitter:card',
				'url'         => 'twitter:url',
				'title'       => 'twitter:title',
				'description' => 'twitter:description',
				'thumbnail'   => 'twitter:image',
			),
		);
		if ( ! empty( $output ) && ! empty( $output['head'] ) && ! empty( $output['head']['meta'] ) ) {
			foreach ( $output['head']['meta'] as $v ) {
				if ( ! empty( $v['@attributes'] ) ) {
					$m = $v['@attributes'];
					foreach ( $metatags as $type => $tags ) {
						if ( ! empty( $m[ $tags['name'] ] ) && ! empty( $m[ $tags['value'] ] ) ) {
							foreach ( $metadata[ $type ] as $tk => $tv ) {
								if ( $m[ $tags['name'] ] == $tv ) {
									$meta .= "<tr><th style='color:red;'>" . sprintf( __( 'Duplicate %s Meta' ), ucwords( $type ) ) . '</th><td>' . ucwords( $tk ) . "</td><td>{$m[$tags['name']]}</td><td>{$m[$tags['value']]}</td></tr>\n";
								}
							}
						}
					}
				}
			}
		}
		if ( empty( $meta ) ) {
			$meta = '<span style="color:green;">' . __( 'No duplicate meta tags found.', 'all-in-one-seo-pack' ) . '</span>';
		} else {
			$meta = "<table cellspacing=0 cellpadding=0 width=80% class='aioseop_table'><tr class='aioseop_table_header'><th>Meta For Site</th><th>Kind of Meta</th><th>Element Name</th><th>Element Value</th></tr>" . $meta . '</table>';
			$meta .= "<p><div class='aioseop_meta_info'><h3 style='padding:5px;margin-bottom:0px;'>" . __( 'What Does This Mean?', 'all-in-one-seo-pack' ) . "</h3><div style='padding:5px;padding-top:0px;'>"
					 /* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
					 . '<p>' . sprintf( __( '%s has detected that a plugin(s) or theme is also outputting social meta tags on your site. You can view this social meta in the source code of your site (check your browser help for instructions on how to view source code).', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME )
					 . '</p><p>' . __( 'You may prefer to use the social meta tags that are being output by the other plugin(s) or theme. If so, then you should deactivate this Social Meta feature in the Feature Manager.', 'all-in-one-seo-pack' )
					 . '</p><p>' . __( 'You should avoid duplicate social meta tags. You can use these free tools from Facebook and Twitter to validate your social meta and check for errors:', 'all-in-one-seo-pack' ) . '</p>';

			foreach (
				array(
					'https://developers.facebook.com/tools/debug',
					'https://dev.twitter.com/docs/cards/validation/validator',
				) as $link
			) {
				$meta .= "<a href='{$link}' target='_blank'>{$link}</a><br />";
			}
			$meta .= '<p>' . __( 'Please refer to the document for each tool for help in using these to debug your social meta.', 'all-in-one-seo-pack' ) . '</div></div>';
		}
		$output = $meta;
		$output = str_replace( "'", "\'", $output );
		$output = str_replace( "\n", '\n', $output );
		die( sprintf( AIOSEOP_AJAX_MSG_TMPL, $output ) );
	}
}

if ( ! function_exists( 'aioseop_ajax_save_settings' ) ) {

	function aioseop_ajax_save_settings() {
		aioseop_ajax_init();
		$options = array();
		parse_str( $_POST['options'], $options );
		$_POST           = $options;
		$_POST['action'] = 'aiosp_update_module';
		global $aiosp, $aioseop_modules;
		aioseop_load_modules();
		$aiosp->admin_menu();
		$module = $aioseop_modules->return_module( $_POST['module'] );
		unset( $_POST['module'] );
		if ( empty( $_POST['location'] ) ) {
			$_POST['location'] = null;
		}
		$_POST['Submit'] = 'ajax';
		$module->add_page_hooks();
		$output = $module->handle_settings_updates( $_POST['location'] );

		if ( AIOSEOPPRO ) {
			$output = '<div id="aioseop_settings_header"><div id="message" class="updated fade"><p>' . $output . '</p></div></div><style>body.all-in-one-seo_page_all-in-one-seo-pack-pro-aioseop_feature_manager .aioseop_settings_left { margin-top: 45px !important; }</style>';
		} else {
			$output = '<div id="aioseop_settings_header"><div id="message" class="updated fade"><p>' . $output . '</p></div></div><style>body.all-in-one-seo_page_all-in-one-seo-pack-aioseop_feature_manager .aioseop_settings_left { margin-top: 45px !important; }</style>';
		}

		if ( defined( 'AIOSEOP_UNIT_TESTING' ) ) {
			return;
		}

		die( sprintf( AIOSEOP_AJAX_MSG_TMPL, $output ) );
	}
}

if ( ! function_exists( 'aioseop_ajax_get_menu_links' ) ) {

	function aioseop_ajax_get_menu_links() {
		aioseop_ajax_init();
		$options = array();
		parse_str( $_POST['options'], $options );
		$_POST           = $options;
		$_POST['action'] = 'aiosp_update_module';
		global $aiosp, $aioseop_modules;
		aioseop_load_modules();
		$aiosp->admin_menu();
		if ( empty( $_POST['location'] ) ) {
			$_POST['location'] = null;
		}
		$_POST['Submit'] = 'ajax';
		$modlist         = $aioseop_modules->get_loaded_module_list();
		$links           = array();
		$link_list       = array();
		$link            = $aiosp->get_admin_links();
		if ( ! empty( $link ) ) {
			foreach ( $link as $l ) {
				if ( ! empty( $l ) ) {
					if ( empty( $link_list[ $l['order'] ] ) ) {
						$link_list[ $l['order'] ] = array();
					}
					$link_list[ $l['order'] ][ $l['title'] ] = $l['href'];
				}
			}
		}
		if ( ! empty( $modlist ) ) {
			foreach ( $modlist as $k => $v ) {
				$mod = $aioseop_modules->return_module( $v );
				if ( is_object( $mod ) ) {
					$mod->add_page_hooks();
					$link = $mod->get_admin_links();
					foreach ( $link as $l ) {
						if ( ! empty( $l ) ) {
							if ( empty( $link_list[ $l['order'] ] ) ) {
								$link_list[ $l['order'] ] = array();
							}
							$link_list[ $l['order'] ][ $l['title'] ] = $l['href'];
						}
					}
				}
			}
		}
		if ( ! empty( $link_list ) ) {
			ksort( $link_list );
			foreach ( $link_list as $ll ) {
				foreach ( $ll as $k => $v ) {
					$links[ $k ] = $v;
				}
			}
		}
		$output = '<ul>';
		if ( ! empty( $links ) ) {
			foreach ( $links as $k => $v ) {
				if ( $k === 'Feature Manager' ) {
					$current = ' class="current"';
				} else {
					$current = '';
				}
				$output .= "<li{$current}><a href='" . esc_url( $v ) . "'>" . esc_attr( $k ) . '</a></li>';
			}
		}
		$output .= '</ul>';
		die( sprintf( "jQuery('{$_POST['target']}').fadeOut('fast', function(){jQuery('{$_POST['target']}').html('%s').fadeIn('fast');});", addslashes( $output ) ) );
	}
}

if ( ! function_exists( 'aioseop_mrt_pccolumn' ) ) {

	/**
	 * @param $aioseopcn
	 * @param $aioseoppi
	 */
	function aioseop_mrt_pccolumn( $aioseopcn, $aioseoppi ) {
		$id     = $aioseoppi;
		$target = null;
		if ( $aioseopcn === 'seotitle' ) {
			$target = 'title';
		}
		if ( $aioseopcn === 'seokeywords' ) {
			$target = 'keywords';
		}
		if ( $aioseopcn === 'seodesc' ) {
			$target = 'description';
		}
		if ( ! $target ) {
			return;
		}
		if ( current_user_can( 'edit_post', $id ) ) {
			?>
			<div class="aioseop_mpc_admin_meta_container">
				<div class="aioseop_mpc_admin_meta_options"
					 id="aioseop_<?php print $target; ?>_<?php echo $id; ?>"
					 style="float:left;">
					<?php
					$content = strip_tags( stripslashes( get_post_meta( $id, '_aioseop_' . $target, true ) ) );
					if ( ! empty( $content ) ) :
						$label = "<label id='aioseop_label_{$target}_{$id}' class='aioseop-label-quickedit'>" . $content . '</label>';
					else :
						$label = "<label id='aioseop_label_{$target}_{$id}' class='aioseop-label-quickedit'></label><strong><i>" . __( 'No', 'all-in-one-seo-pack' ) . ' ' . $target . '</i></strong>';
					endif;
					$nonce = wp_create_nonce( "aioseop_meta_{$target}_{$id}" );
					echo '<a id="' . $target . 'editlink' . $id . '" '
						. 'class="aioseop_edit_link aioseop-icon-cog-edit" '
						. 'href="javascript:void(0);" '
						. 'onclick=\'aioseop_ajax_edit_meta_form(' . $id . ', "' . $target . '", "' . $nonce . '");return false;\' '
						. 'title="' . __( 'Edit' ) . '"></a>';
					echo $label;
					?>
				</div>
			</div>
			<?php
		}
	}
}

if ( ! function_exists( 'aioseop_unprotect_meta' ) ) {

	/**
	 * @param $protected
	 * @param $meta_key
	 * @param $meta_type
	 *
	 * @return bool
	 */
	function aioseop_unprotect_meta( $protected, $meta_key, $meta_type ) {
		if ( isset( $meta_key ) && ( substr( $meta_key, 0, 9 ) === '_aioseop_' ) ) {
			return false;
		}

		return $protected;
	}
}

if ( ! function_exists( 'aioseop_mrt_exclude_this_page' ) ) {

	/**
	 * @param null $url
	 *
	 * @return bool
	 */
	function aioseop_mrt_exclude_this_page( $url = null ) {
		static $excluded = false;
		if ( $excluded === false ) {
			global $aioseop_options;
			$ex_pages = '';
			if ( isset( $aioseop_options['aiosp_ex_pages'] ) ) {
				$ex_pages = trim( $aioseop_options['aiosp_ex_pages'] );
			}
			if ( ! empty( $ex_pages ) ) {
				$excluded = explode( ',', $ex_pages );
				if ( ! empty( $excluded ) ) {
					foreach ( $excluded as $k => $v ) {
						$excluded[ $k ] = trim( $v );
						if ( empty( $excluded[ $k ] ) ) {
							unset( $excluded[ $k ] );
						}
					}
				}
				if ( empty( $excluded ) ) {
					$excluded = null;
				}
			}
		}
		if ( ! empty( $excluded ) ) {
			if ( $url === null ) {
				$url = $_SERVER['REQUEST_URI'];
			} else {
				$url = parse_url( $url );
				if ( ! empty( $url['path'] ) ) {
					$url = $url['path'];
				} else {
					return false;
				}
			}
			if ( ! empty( $url ) ) {
				foreach ( $excluded as $exedd ) {
					if ( $exedd && ( stripos( $url, $exedd ) !== false ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'aioseop_add_contactmethods' ) ) {

	/**
	 * @param $contactmethods
	 *
	 * @return mixed
	 */
	function aioseop_add_contactmethods( $contactmethods ) {
		global $aioseop_options, $aioseop_modules;

		if ( ! empty( $aioseop_modules ) && is_object( $aioseop_modules ) ) {
			$m = $aioseop_modules->return_module( 'All_in_One_SEO_Pack_Opengraph' );
			if ( ( $m !== false ) && is_object( $m ) ) {

				if ( $m->option_isset( 'twitter_creator' ) || $m->option_isset( 'facebook_author' ) ) {
					$contactmethods['aioseop_edit_profile_header'] = AIOSEOP_PLUGIN_NAME;
				}

				if ( $m->option_isset( 'twitter_creator' ) ) {
					$contactmethods['twitter'] = __( 'Twitter', 'all-in-one-seo-pack' );
				}
				if ( $m->option_isset( 'facebook_author' ) ) {
					$contactmethods['facebook'] = __( 'Facebook', 'all-in-one-seo-pack' );
				}
			}
		}
		return $contactmethods;
	}
}

if ( ! function_exists( 'aioseop_localize_script_data' ) ) {

	/**
	 * AIOSEOP Localize Script Data
	 *
	 * Used by the module base class script enqueue to localize data.
	 *
	 * @since ?
	 */
	function aioseop_localize_script_data() {
		static $loaded = 0;
		if ( ! $loaded ) {
			$data = apply_filters( 'aioseop_localize_script_data', array() );
			wp_localize_script( 'aioseop-module-script', 'aiosp_data', $data );
			$loaded = 1;
		}
	}
}

if ( ! function_exists( 'aioseop_array_insert_after' ) ) {
	/**
	 * Utility function for inserting elements into associative arrays by key.
	 *
	 * @param $arr
	 * @param $insertKey
	 * @param $newValues
	 *
	 * @return array
	 */
	function aioseop_array_insert_after( $arr, $insertKey, $newValues ) {
		$keys        = array_keys( $arr );
		$vals        = array_values( $arr );
		$insertAfter = array_search( $insertKey, $keys ) + 1;
		$keys2       = array_splice( $keys, $insertAfter );
		$vals2       = array_splice( $vals, $insertAfter );
		foreach ( $newValues as $k => $v ) {
			$keys[] = $k;
			$vals[] = $v;
		}

		return array_merge( array_combine( $keys, $vals ), array_combine( $keys2, $vals2 ) );
	}
}

if ( ! function_exists( 'fnmatch' ) ) {
	/**
	 * Support for fnmatch() doesn't exist on Windows pre PHP 5.3.
	 *
	 * @param $pattern
	 * @param $string
	 *
	 * @return int
	 */
	function fnmatch( $pattern, $string ) {
		return preg_match(
			'#^' . strtr(
				preg_quote( $pattern, '#' ), array(
					'\*' => '.*',
					'\?' => '.',
				)
			) . '$#i', $string
		);
	}
}

if ( ! function_exists( 'aiosp_log' ) ) {
	function aiosp_log( $log, $force = false ) {

		global $aioseop_options;

		if ( ( ! empty( $aioseop_options ) && isset( $aioseop_options['aiosp_do_log'] ) && $aioseop_options['aiosp_do_log'] ) || $force || defined( 'AIOSEOP_DO_LOG' ) ) {

			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

if ( ! function_exists( 'parse_ini_string' ) ) {
	/**
	 * Parse_ini_string() doesn't exist pre PHP 5.3.
	 *
	 * @param $string
	 * @param $process_sections
	 *
	 * @return array|bool
	 */
	function parse_ini_string( $string, $process_sections ) {

		if ( ! class_exists( 'parse_ini_filter' ) ) {

			/**
			 * Class parse_ini_filter
			 *
			 * Define our filter class.
			 */
			// @codingStandardsIgnoreStart
			class parse_ini_filter extends php_user_filter {
			// @codingStandardsIgnoreEnd
				static $buf = '';

				/**
				 * The actual filter for parsing.
				 *
				 * @param $in
				 * @param $out
				 * @param $consumed
				 * @param $closing
				 *
				 * @return int
				 */
				function filter( $in, $out, &$consumed, $closing ) {
					$bucket = stream_bucket_new( fopen( 'php://memory', 'wb' ), self::$buf );
					stream_bucket_append( $out, $bucket );

					return PSFS_PASS_ON;
				}
			}

			// Register our filter with PHP.
			if ( ! stream_filter_register( 'parse_ini', 'parse_ini_filter' ) ) {
				return false;
			}
		}
		parse_ini_filter::$buf = $string;

		return parse_ini_file( 'php://filter/read=parse_ini/resource=php://memory', $process_sections );
	}
}

/**
 * @deprecated 2.4.2
 */
function aioseop_update_user_visibilitynotice() {

	update_user_meta( get_current_user_id(), 'aioseop_visibility_notice_dismissed', true );
}

function aioseop_update_yst_detected_notice() {

	update_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed', true );
}

/**
 * Returns home_url() value compatible for any use.
 * Thought for compatibility purposes.
 *
 * @since 2.3.12.3
 *
 * @param string $path Relative path to home_url().
 *
 * @return string url.
 */
function aioseop_home_url( $path = '/' ) {

	$url = apply_filters( 'aioseop_home_url', $path );
	return $path === $url ? home_url( $path ) : $url;
}


if ( ! function_exists( 'aiosp_include_images' ) ) {
	function aiosp_include_images() {
		if ( false === apply_filters( 'aioseo_include_images_in_sitemap', true ) ) {
			return false;
		}

		global $aioseop_options;

		if ( isset( $aioseop_options['modules'] ) &&
			isset( $aioseop_options['modules']['aiosp_sitemap_options'] ) &&
			isset( $aioseop_options['modules']['aiosp_sitemap_options']['aiosp_sitemap_images'] ) &&
			'on' === $aioseop_options['modules']['aiosp_sitemap_options']['aiosp_sitemap_images']
		 ) {
			return false;
		}

		return true;
	}
}


if ( ! function_exists( 'aioseop_formatted_date' ) ) {
	/**
	 * Get formatted date. For custom formatting, the user has 2 options:
	 * 1. provide the native date_i18n filter.
	 * 2. provide a custom aioseop_format_date filter.
	 *
	 * @param int    $date Date in UNIX timestamp format.
	 * @param string $format Require date format.
	 */
	function aioseop_formatted_date( $date = null, $format = null ) {
		if ( ! $format ) {
			$format = get_option( 'date_format' );
		}
		if ( ! $date ) {
			$date = time();
		}

		$formatted_date = date_i18n( $format, $date );
		return apply_filters( 'aioseop_format_date', $formatted_date, $date, $format );
	}
}

/**
 * SVG menu icon function.
 *
 * Returns a base64 data URI for the SVG icon in the menu.
 *
 * @since 3.0
 *
 * @return string
 */
if ( ! function_exists( 'aioseop_get_menu_icon' ) ) {

	function aioseop_get_menu_icon() {
		$svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg"
	   width="16px" height="16px" viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
    <g>
	    <g>
		    <path fill="#a0a5aa" d="M6.6356587,16.0348835c-0.0206718,0-0.0413432,0-0.0620155,0
			    c-0.067409-0.5687227-0.188632-1.1286116-0.2770367-1.6938677c-0.0116553-0.0745268-0.0655184-0.0857201-0.1188116-0.1016665
			    c-0.3916383-0.1171865-0.7678571-0.2725677-1.1279092-0.4651537c-0.0950913-0.0508642-0.1637669-0.0440636-0.2516775,0.0184937
			    c-0.4121995,0.2933187-0.8315198,0.5766335-1.2435973,0.8701181c-0.0922408,0.0656958-0.1460404,0.0679903-0.2289517-0.0181942
			    c-0.6222079-0.6467686-1.2487767-1.2893686-1.878032-1.9292908c-0.0701602-0.0713491-0.0678169-0.1162405-0.0118753-0.1922131
			    c0.3030721-0.4115992,0.5985562-0.8287926,0.9025542-1.2396946c0.0631523-0.0853596,0.0758619-0.1488447,0.0193999-0.2455435
			    c-0.2010608-0.344347-0.3531485-0.711894-0.4586703-1.095892C1.8667084,9.8243389,1.8056024,9.7895813,1.6982909,9.7728567
			    C1.1987077,9.6949921,0.7006906,9.6068258,0.2005107,9.5332375C0.086966,9.516531,0.0595014,9.4774542,0.0604039,9.3681087
			    c0.0040068-0.485467-0.001498-0.9710121-0.0035627-1.4565291c0.0033759-0.4542298,0.0067518-0.9084601,0.0101276-1.36269
			    c0.5357779-0.0816574,1.0710917-0.1666121,1.6077318-0.2421441c0.1052274-0.014811,0.1534867-0.0610075,0.1793816-0.156611
			    C1.9584855,5.7646813,2.1191192,5.401351,2.3082211,5.0513253c0.0522738-0.0967579,0.0481837-0.162436-0.0161171-0.250216
			    C1.9869013,4.3844619,1.6903805,3.9614599,1.3860248,3.5441806c-0.0582591-0.0798743-0.0660335-0.1283553,0.0108961-0.205359
			    c0.2363812-0.2366092,0.4708829-0.4750328,0.6985862-0.7207708c0.3790767-0.4091005,0.7861221-0.7921721,1.175601-1.1918454
			    c0.073673-0.0756011,0.1193006-0.0768266,0.2023387-0.0167576C3.8918667,1.7121295,4.316906,2.0056617,4.7353082,2.308367
			    C4.8231764,2.3719378,4.8931837,2.3785665,4.9881315,2.32724c0.3547778-0.1917841,0.7246637-0.3497989,1.111764-0.4646662
			    c0.0834813-0.0247719,0.1245975-0.064445,0.1387806-0.1575147c0.0761976-0.5000092,0.16292-0.9984057,0.2415481-1.4980611
			    c0.0154085-0.0979107,0.0362725-0.1528581,0.1583104-0.1525809C7.5273662,0.0564359,8.4162264,0.0512272,9.305047,0.0441977
			    c0.1012211-0.0008005,0.1417351,0.0252949,0.1585598,0.1328662C9.53936,0.6614148,9.6292553,1.1435384,9.7077475,1.6274875
			    c0.0177774,0.1096017,0.058032,0.1689863,0.1729288,0.1986653c0.3962202,0.102347,0.7708454,0.2639885,1.1316824,0.4550474
			    c0.0918427,0.04863,0.1530666,0.0429895,0.2356024-0.017288c0.4170818-0.3046039,0.8413315-0.5994209,1.2571535-0.9056976
			    c0.0917759-0.0675981,0.1401968-0.0588857,0.2166672,0.0191574c0.6248131,0.6376669,1.2525311,1.272517,1.883935,1.9036541
			    c0.0746508,0.07462,0.0591631,0.1200178,0.0068951,0.1928642c-0.3040953,0.4238276-0.6021757,0.8519745-0.9068089,1.2754121
			    c-0.055665,0.0773745-0.062233,0.1379747-0.0156651,0.2230096c0.1986971,0.3628144,0.3654804,0.740099,0.4793482,1.1387706
			    c0.0208931,0.0731559,0.0545502,0.1125269,0.1340227,0.124958c0.5150261,0.080555,1.0287666,0.1695499,1.5444088,0.2457719
			    c0.1157055,0.0171032,0.1522121,0.0537534,0.1517,0.1727324c-0.0038252,0.8888292-0.0027952,1.7777138,0.0044317,2.6665182
			    c0.0009861,0.1212635-0.0400152,0.1560354-0.1516571,0.1713991c-0.506238,0.0696716-1.01122,0.1484213-1.5170298,0.2212944
			    c-0.0849352,0.0122366-0.1369514,0.0427141-0.1609879,0.1339951c-0.1068697,0.4058342-0.2684164,0.7910061-0.4649954,1.1610003
			    c-0.0476036,0.0895996-0.0424118,0.1538601,0.0197964,0.2369499c0.3095427,0.4134502,0.6102238,0.8335266,0.9184151,1.2480059
			    c0.0534544,0.0718899,0.0545559,0.1134748-0.0114231,0.1797924c-0.2578106,0.2591314-0.5192184,0.5143776-0.769351,0.7817802
			    c-0.3668623,0.392189-0.7637119,0.7561789-1.1404953,1.1393509c-0.0824919,0.08389-0.1328821,0.0722904-0.217783,0.011488
			    c-0.4072781-0.2916708-0.8208151-0.5745983-1.2280502-0.8663273c-0.0825233-0.0591173-0.144722-0.067111-0.236228-0.0173359
			    c-0.3357944,0.1826582-0.6816397,0.3475332-1.0514994,0.4474249c-0.1470699,0.0397205-0.2045288,0.1080666-0.2260523,0.2567778
			    c-0.0761395,0.5260658-0.1672792,1.0499601-0.2527313,1.5746784c-0.4212217,0.0021896-0.8424425,0.0043812-1.2636642,0.0065708
			    c-0.4936438-0.0006676-0.9872875-0.0013523-1.4809322-0.0019608C6.7227592,16.005888,6.6766686,16.0087776,6.6356587,16.0348835z
			    M5.5945344,8.0587454c0-0.2738581,0.0047617-0.5478387-0.0023174-0.8215132
			    C5.5893402,7.1260171,5.6286783,7.0980015,5.7342682,7.101109c0.2426443,0.0071421,0.485899-0.004355,0.7283907,0.0050206
			    C6.582684,7.1107702,6.6087341,7.0694351,6.6071982,6.9564962C6.6010141,6.5018721,6.6034818,6.047111,6.6051793,5.5924091
			    C6.6059542,5.3847222,6.7331271,5.2457314,6.9168048,5.244235c0.1906495-0.0015526,0.3085308,0.127861,0.3090096,0.3434582
			    C7.2268128,6.0372324,7.2299843,6.486825,7.2238765,6.9362822C7.2222071,7.059145,7.2495227,7.1088743,7.3848124,7.1063519
			    c0.439054-0.0081887,0.8784418-0.0078368,1.3175068,0.0000539C8.8253679,7.1086168,8.8466787,7.0647745,8.845252,6.9549446
			    C8.8396749,6.5261455,8.8435812,6.0972285,8.8424616,5.6683593c-0.0001907-0.072803,0.0000401-0.1455956,0.0266342-0.213613
			    c0.0515699-0.1318998,0.146349-0.2095218,0.2923908-0.2100406C9.3081264,5.244184,9.4056911,5.3223853,9.4531078,5.4547076
			    c0.0219107,0.0611463,0.022418,0.1320171,0.0227108,0.1985226c0.0019064,0.4340315,0.004344,0.8681149-0.0010605,1.3020859
			    C9.4733648,7.0671229,9.4999199,7.1107302,9.6208115,7.106133c0.2476625-0.0094175,0.496212,0.0041265,0.7438431-0.0057049
			    c0.1215019-0.0048237,0.1450939,0.038619,0.1435795,0.1499443c-0.0059738,0.439126,0.0016041,0.8784308-0.0038633,1.3175702
			    c-0.0045824,0.3680878-0.0652542,0.7269754-0.226469,1.062129c-0.3337469,0.6938353-0.8668461,1.1507959-1.613966,1.3531427
			    c-0.0617809,0.0167313-0.1411858,0.0100212-0.1393509,0.1228523c0.0067186,0.4132614,0.0039825,0.8267059,0.0015554,1.2400627
			    c-0.0004635,0.0790262,0.0286264,0.0947142,0.102293,0.0837212c0.888093-0.1325045,1.6820068-0.4789791,2.3410072-1.0896969
			    c1.1879272-1.1008902,1.6558428-2.4656649,1.4010391-4.0640707c-0.1778069-1.1154013-0.7301302-2.025878-1.6186838-2.7184963
			    c-1.047287-0.8163497-2.2356091-1.1035333-3.5431743-0.8636246C6.1200128,3.893697,5.2326531,4.4406323,4.5548649,5.3104329
			    c-0.8100188,1.0394912-1.079107,2.221858-0.8649251,3.5168509c0.1360686,0.8227005,0.491719,1.543438,1.0476153,2.1613245
			    c0.7106156,0.7898598,1.5925984,1.2679882,2.6462483,1.4320927c0.1402783,0.0218477,0.1544113-0.0172405,0.1528563-0.1345453
			    c-0.0050645-0.3823004-0.0053444-0.7647629,0.0003886-1.1470451c0.0015402-0.1027012-0.0355787-0.1348372-0.131959-0.1631641
			    c-0.8400359-0.2468815-1.4050922-0.7891521-1.6920962-1.6152534C5.5662184,8.9382191,5.5952759,8.4963045,5.5945344,8.0587454z"/>
	    </g>
    </g>
    </svg>';
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}
}

/**
 * Runs shortcodes in autogenerated titles & descriptions.
 *
 * @since 3.0.0
 *
 * @param string $content Content of the post
 *
 * @return string $content Content after shortcodes have been run.
 */
function aioseop_do_shortcodes( $content ) {
	$conflicting_shortcodes = array(
		'WooCommerce Login' => '[woocommerce_my_account]',
	);

	$rtn_conflict_shortcodes = array();
	foreach ( $conflicting_shortcodes as $shortcode ) {
		// Second check is needed for shortcodes in Gutenberg Classic blocks.
		if ( stripos( $content, $shortcode, 0 ) || 0 === stripos( $content, $shortcode, 0 ) ) {
			global $shortcode_tags;
			$shortcode_tag = str_replace( array('[', ']'), '', $shortcode );
			if ( array_key_exists( $shortcode_tag, $shortcode_tags ) ) {
				$rtn_conflict_shortcodes[ $shortcode_tag ] = $shortcode_tags[ $shortcode_tag ];
			}
		}
	}

	if ( ! empty( $rtn_conflict_shortcodes ) ) {
		return aioseop_do_shortcode_helper( $content, $rtn_conflict_shortcodes );
	}

	return do_shortcode( $content );
}

/**
 * Ignores shortcodes that are known to conflict.
 * Acts as a helper function for aioseop_do_shortcodes().
 *
 * @since 3.0.0
 *
 * @param string $content Content of the post
 * @param array  $conflicting_shortcodes List of conflicting shortcodes
 *
 * @return string $content Content after shortcodes have been run whilst ignoring conflicting shortcodes.
 */
function aioseop_do_shortcode_helper( $content, $conflicting_shortcodes ) {

	foreach ( $conflicting_shortcodes as $shortcode_tag => $shortcode_callback ) {
		remove_shortcode( $shortcode_tag );
	}

	$content = do_shortcode( $content );

	// Adds shortcodes back since remove_shortcode() disables them site-wide.
	foreach ( $conflicting_shortcodes as $shortcode_tag => $shortcode_callback ) {
		add_shortcode( $shortcode_tag, $shortcode_callback );
	}

	return $content;
}
