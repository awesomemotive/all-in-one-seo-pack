<?php
/**
 * @package All-in-One-SEO-Pack
 */
/**
 * The module base class; handles settings, options, menus, metaboxes, etc.
 */
if ( !class_exists( 'All_in_One_SEO_Pack_Module' ) ) {
	abstract class All_in_One_SEO_Pack_Module {
		public static $instance = null;
		protected $plugin_name;
		protected $name;
		protected $menu_name;
		protected $prefix;
		protected $file;
		protected $options;
		protected $option_name;
		protected $default_options;
		protected $help_text = Array();
		protected $help_anchors = Array();
		protected $locations = null;	// organize settings into settings pages with a menu items and/or metaboxes on post types edit screen; optional
		protected $layout = null;		// organize settings on a settings page into multiple, separate metaboxes; optional
		protected $tabs = null;			// organize layouts on a settings page into multiple, separate tabs; optional
		protected $current_tab = null;	// the current tab
		protected $pagehook = null;		// the current page hook
		protected $store_option = false;
		protected $parent_option = 'aioseop_options';
		protected $post_metaboxes = Array();
		protected $tabbed_metaboxes = true;
		protected $credentials = false; // used for WP Filesystem
		protected $script_data = null;	// used for passing data to JavaScript
		protected $plugin_path = null;
		protected $pointers = Array();
		protected $form = 'dofollow';
		
		/**
		 * Handles calls to display_settings_page_{$location}, does error checking.
		 */
		function __call( $name, $arguments ) {
			if ( $this->strpos( $name, "display_settings_page_" ) === 0 )
				return $this->display_settings_page( $this->substr( $name, 22 ) );
			$error = __( sprintf( "Method %s doesn't exist", $name ), 'all-in-one-seo-pack' );
			if ( class_exists( 'BadMethodCallException' ) )
				throw new BadMethodCallException( $error );
			throw new Exception( $error );
		}
		
		function __construct() {
			if ( empty( $this->file ) ) $this->file = __FILE__;
			$this->plugin_name = AIOSEOP_PLUGIN_NAME;
			$this->plugin_path = Array();
//			$this->plugin_path['dir'] = plugin_dir_path( $this->file );
			$this->plugin_path['basename'] = plugin_basename( $this->file );
			$this->plugin_path['dirname'] = dirname( $this->plugin_path['basename'] );
			$this->plugin_path['url'] = plugin_dir_url( $this->file );
			$this->plugin_path['images_url'] = $this->plugin_path['url'] . 'images';
			$this->script_data['plugin_path'] = $this->plugin_path;
		}

		/**
		 * Get options for module, stored individually or together.
		 */
		function get_class_option( ) {
			$option_name = $this->get_option_name();
			if ( $this->store_option || $option_name == $this->parent_option ) {
				return get_option( $option_name );
			} else {
				$option = get_option( $this->parent_option );
				if ( isset( $option['modules'] ) && isset( $option['modules'][$option_name] ) )
					return $option['modules'][$option_name];
			}
			return false;
		}

		/**
		 * Update options for module, stored individually or together.
		 */
		function update_class_option( $option_data, $option_name = false ) {
			if ( $option_name == false )
				$option_name = $this->get_option_name();
			if ( $this->store_option || $option_name == $this->parent_option ) {
				return update_option( $option_name, $option_data );
			} else {
				$option = get_option( $this->parent_option );
				if ( !isset( $option['modules'] ) ) $option['modules'] = Array();
				$option['modules'][$option_name] = $option_data;
				return update_option( $this->parent_option, $option );
			}
		}
		
		/**
		 * Delete options for module, stored individually or together.
		 */
		function delete_class_option( $delete = false ) {
			$option_name = $this->get_option_name();
			if ( $this->store_option || $delete ) {
				delete_option( $option_name );
			} else {
				$option = get_option( $this->parent_option );
				if ( isset( $option['modules'] ) && isset( $option['modules'][$option_name] ) ) {
					unset( $option['modules'][$option_name] );
					return update_option( $this->parent_option, $option );
				}
			}
			return false;
		}

		/**
		 * Get the option name with prefix.
		 */
		function get_option_name() {
			if ( !isset( $this->option_name ) || empty( $this->option_name ) )
				$this->option_name = $this->prefix . 'options';
			return $this->option_name;
		}

		/**
		 * Convenience function to see if an option is set.
		 */
		function option_isset( $option, $location = null ) {
			$prefix = $this->get_prefix( $location );
			$opt = $prefix . $option;
			return ( ( isset( $this->options[$opt] ) ) && $this->options[$opt] );
		}
		
		/*** Case conversion; handle non UTF-8 encodings and fallback ***/

		function convert_case( $str, $mode = 'upper' ) {
			static $charset = null;
			if ( $charset == null ) $charset = get_bloginfo( 'charset' );
			$str = (string)$str;
			if ( $mode == 'title' ) {
				if ( function_exists( 'mb_convert_case' ) )
					return mb_convert_case( $str, MB_CASE_TITLE, $charset );
				else
					return ucwords( $str );
			}
			
			if ( $charset == 'UTF-8' ) {
				global $UTF8_TABLES;
				include_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_UTF8.php' );
				if ( is_array( $UTF8_TABLES ) ) {
					if ( $mode == 'upper' ) return strtr( $str, $UTF8_TABLES['strtoupper'] );
					if ( $mode == 'lower' ) return strtr( $str, $UTF8_TABLES['strtolower'] );
				}
			}
			
			if ( $mode == 'upper' ) {
				if ( function_exists( 'mb_strtoupper' ) )
					return mb_strtoupper( $str, $charset );
				else
					return strtoupper( $str );
			}

			if ( $mode == 'lower' ) {
				if ( function_exists( 'mb_strtolower' ) )
					return mb_strtolower( $str, $charset );
				else
					return strtolower( $str );
			}
			
			return $str;
		}

		/**      
		 * Convert a string to lower case
		 * Compatible with mb_strtolower(), an UTF-8 friendly replacement for strtolower()
		 */
		function strtolower( $str ) {
			return $this->convert_case( $str, 'lower' );
		}

		/**      
		 * Convert a string to upper case
		 * Compatible with mb_strtoupper(), an UTF-8 friendly replacement for strtoupper()
		 */
		function strtoupper( $str ) {
			return $this->convert_case( $str, 'upper' );
		}

		/**      
		 * Convert a string to title case
		 * Compatible with mb_convert_case(), an UTF-8 friendly replacement for ucwords()
		 */
		function ucwords( $str ) {
			return $this->convert_case( $str, 'title' );
		}
		
		/**
		 * Wrapper for strlen() - uses mb_strlen() if possible.
		 */		
		function strlen( $string ) {
			if ( function_exists( 'mb_strlen' ) )
				return mb_strlen( $string );
			return strlen( $string );
		}

		/**
		 * Wrapper for substr() - uses mb_substr() if possible.
		 */
		function substr( $string, $start = 0, $length = 2147483647 ) {
			$args = func_get_args();
			if ( function_exists( 'mb_substr' ) )
				return call_user_func_array( 'mb_substr', $args );
			return call_user_func_array( 'substr', $args );
		}
		
		/**
		 * Wrapper for strpos() - uses mb_strpos() if possible.
		 */
		function strpos( $haystack, $needle, $offset = 0 ) {
			if ( function_exists( 'mb_strpos' ) )
				return mb_strpos( $haystack, $needle, $offset );
			return strpos( $haystack, $needle, $offset );
		}
		
		/**
		 * Wrapper for strrpos() - uses mb_strrpos() if possible.
		 */
		function strrpos( $haystack, $needle, $offset = 0 ) {
			if ( function_exists( 'mb_strrpos' ) )
				return mb_strrpos( $haystack, $needle, $offset );
			return strrpos( $haystack, $needle, $offset );
		}

		/**
		  * convert xml string to php array - useful to get a serializable value
		  *
		  * @param string $xmlstr
		  * @return array
		  *
		  * @author Adrien aka Gaarf & contributors
		  * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
		*/
		function html_string_to_array( $xmlstr ) {
		  if ( !class_exists( 'DOMDocument' ) ) {
			return Array();
		  } else {
			  $doc = new DOMDocument();
			  $doc->loadHTML( $xmlstr );
			  return $this->domnode_to_array( $doc->documentElement );			
		  }
		}

		function xml_string_to_array( $xmlstr ) {
		  if ( !class_exists( 'DOMDocument' ) ) {
			  return Array();
		  } else {
			  $doc = new DOMDocument();
			  $doc->loadXML( $xmlstr );
			  return $this->domnode_to_array( $doc->documentElement );			
		  }
		}

		function domnode_to_array( $node ) {
		  switch ( $node->nodeType ) {
		    case XML_CDATA_SECTION_NODE:
		    case XML_TEXT_NODE:
		      return trim( $node->textContent );
		    break;
		    case XML_ELEMENT_NODE:
			  $output = array();
		      for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
		        $child = $node->childNodes->item($i);
		        $v = $this->domnode_to_array($child);
		        if(isset($child->tagName)) {
		          $t = $child->tagName;
		          if(!isset($output[$t]))
		            $output[$t] = array();
				  if ( is_array($output) )
		          	$output[$t][] = $v;
		        }
		        elseif($v || $v === '0')
		          $output = (string) $v;
		      }
		      if($node->attributes->length && !is_array($output)) //Has attributes but isn't an array
		        $output = array('@content'=>$output); //Change output into an array.
		      if(is_array($output)) {
		        if($node->attributes->length) {
		          $a = array();
		          foreach($node->attributes as $attrName => $attrNode)
		            $a[$attrName] = (string) $attrNode->value;
		          $output['@attributes'] = $a;
		        }
		        foreach ($output as $t => $v)
		          if(is_array($v) && count($v)==1 && $t!='@attributes')
		            $output[$t] = $v[0];
		      }
		  }
		  if ( empty( $output ) ) return '';
		  return $output;
		}
		
		/*** adds support for using %cf_(name of field)% for using custom fields / Advanced Custom Fields in titles / descriptions etc. ***/
		function apply_cf_fields( $format ) {
			return preg_replace_callback( '/%cf_([^%]*?)%/', Array( $this, 'cf_field_replace' ), $format );
		}

		function cf_field_replace( $matches ) {
			$result = '';
			if ( !empty( $matches ) ) {
				if ( !empty( $matches[1] ) ) {
					if ( function_exists( 'get_field' ) ) $result = get_field( $matches[1] );
					if ( empty( $result ) ) {
						global $post;
						if ( !empty( $post ) ) $result = get_post_meta( $post->ID, $matches[1], true );
					}
					if ( empty( $result ) ) $result = $matches[0];
				} else $result = $matches[0];
			}
			$result = strip_tags( $result );
			return $result;
		}
		
		/**
		 * Returns child blogs of parent in a multisite.
		 */
		function get_child_blogs() {
			global $wpdb, $blog_id;
			$site_id = $wpdb->siteid;
			if ( is_multisite() ) {
				if ( $site_id != $blog_id ) return false;				
				return $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = {$blog_id} AND site_id != blog_id" );
			}
			return false;
		}
		
		/**
		 * Checks if the plugin is active on a given blog by blogid on a multisite.
		 */
		function is_aioseop_active_on_blog( $bid = false ) {
			global $blog_id;
			if ( empty( $bid ) || ( $bid == $blog_id ) || !is_multisite() ) return true;
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
			   require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			if ( is_plugin_active_for_network( AIOSEOP_PLUGIN_BASENAME ) )  return true;
			return in_array( AIOSEOP_PLUGIN_BASENAME, (array) get_blog_option( $bid, 'active_plugins', array() ) );
		}
		
		function quote_list_for_regex( $list, $quote = '/' ) {
			$regex = '';
			$cont = 0;
			foreach( $list as $l ) {
			        if ( $cont ) {
			                $regex .= '|';
			        }
			        $cont = 1;
			        $regex .= preg_quote( trim( $l ), $quote );
			}
			return $regex;
		}
		
		// original code thanks to Sean M. Brown -- http://smbrown.wordpress.com/2009/04/29/verify-googlebot-forward-reverse-dns/
		function is_good_bot() {
			$botlist = Array(
				"Yahoo! Slurp" => "crawl.yahoo.net",
				"googlebot" => ".googlebot.com",
				"msnbot" => "search.msn.com"
			);
			$botlist = apply_filters( $this->prefix . "botlist", $botlist );
			if ( !empty( $botlist ) ) {
			    $ua = $_SERVER['HTTP_USER_AGENT'];
				$uas = $this->quote_list_for_regex( $botlist );
			    if ( preg_match( '/' . $uas . '/i', $ua ) ) {
					$ip = $_SERVER['REMOTE_ADDR'];
					$hostname = gethostbyaddr( $ip );
					$ip_by_hostname = gethostbyname( $hostname );
			        if ( $ip_by_hostname == $ip ) {
						$hosts = array_values( $botlist );
						foreach( $hosts as $k => $h )
							$hosts[$k] = preg_quote( $h ) . '$';
						$hosts = join( '|', $hosts );
						if ( preg_match( '/' . $hosts . '/i', $hostname ) )
							return true;
					}
				}
				return false;
			}
		}
		
		function default_bad_bots() {
			$botlist = Array(
				"Abonti",
				"aggregator",
				"AhrefsBot",
				"asterias",
				"BDCbot",
				"BLEXBot",
				"BuiltBotTough",
				"Bullseye",
				"BunnySlippers",
				"ca-crawler",
				"CCBot",
				"Cegbfeieh",
				"CheeseBot",
				"CherryPicker",
				"CopyRightCheck",
				"cosmos",
				"Crescent",
				"discobot",
				"DittoSpyder",
				"DotBot",
				"Download Ninja",
				"EasouSpider",
				"EmailCollector",
				"EmailSiphon",
				"EmailWolf",
				"EroCrawler",
				"Exabot",
				"ExtractorPro",
				"Fasterfox",
				"FeedBooster",
				"Foobot",
				"Genieo",
				"grub-client",
				"Harvest",
				"hloader",
				"httplib",
				"HTTrack",
				"humanlinks",
				"ieautodiscovery",
				"InfoNaviRobot",
				"IstellaBot",
				"Java/1.",
				"JennyBot",
				"k2spider",
				"Kenjin Spider",
				"Keyword Density/0.9",
				"larbin",
				"LexiBot",
				"libWeb",
				"libwww",
				"LinkextractorPro",
				"linko",
				"LinkScan/8.1a Unix",
				"LinkWalker",
				"LNSpiderguy",
				"lwp-trivial",
				"magpie",
				"Mata Hari",
				'MaxPointCrawler',
				'MegaIndex',
				"Microsoft URL Control",
				"MIIxpc",
				"Mippin",
				"Missigua Locator",
				"Mister PiX",
				"MJ12bot",
				"moget",
				"MSIECrawler",
				"NetAnts",
				"NICErsPRO",
				"Niki-Bot",
				"NPBot",
				"Nutch",
				"Offline Explorer",
				"Openfind",
				'panscient.com',
				"PHP/5.{",
				"ProPowerBot/2.14",
				"ProWebWalker",
				"Python-urllib",
				"QueryN Metasearch",
				"RepoMonkey",
				"RMA",
				'SemrushBot',
				"SeznamBot",
				"SISTRIX",
				"sitecheck.Internetseer.com",
				"SiteSnagger",
				"SnapPreviewBot",
				"Sogou",
				"SpankBot",
				"spanner",
				"spbot",
				"Spinn3r",
				"suzuran",
				"Szukacz/1.4",
				"Teleport",
				"Telesoft",
				"The Intraformant",
				"TheNomad",
				"TightTwatBot",
				"Titan",
				"toCrawl/UrlDispatcher",
				"True_Robot",
				"turingos",
				"TurnitinBot",
				"UbiCrawler",
				"UnisterBot",
				"URLy Warning",
				"VCI",
				"WBSearchBot",
				"Web Downloader/6.9",
				"Web Image Collector",
				"WebAuto",
				"WebBandit",
				"WebCopier",
				"WebEnhancer",
				"WebmasterWorldForumBot",
				"WebReaper",
				"WebSauger",
				"Website Quester",
				"Webster Pro",
				"WebStripper",
				"WebZip",
				"Wotbox",
				"wsr-agent",
				"WWW-Collector-E",
				"Xenu",
				"Zao",
				"Zeus",
				"ZyBORG",
				'coccoc',
				'Incutio',
				'lmspider',
				'memoryBot',
				'SemrushBot',
				'serf',
				'Unknown',
				'uptime files'
			);
			return $botlist;
		}
		
		function is_bad_bot() {
			$botlist = $this->default_bad_bots();
			$botlist = apply_filters( $this->prefix . "badbotlist", $botlist );
			if ( !empty( $botlist ) ) {
			    $ua = $_SERVER['HTTP_USER_AGENT'];
				$uas = $this->quote_list_for_regex( $botlist );
			    if ( preg_match( '/' . $uas . '/i', $ua ) ) {
					return true;
				}
			}
			return false;
		}
		
		function default_bad_referers() {
			$referlist = Array(
				'semalt.com',
				'kambasoft.com',
				'savetubevideo.com',
				'buttons-for-website.com',
				'sharebutton.net',
				'soundfrost.org',
				'srecorder.com',
				'softomix.com',
				'softomix.net',
				'myprintscreen.com',
				'joinandplay.me',
				'fbfreegifts.com',
				'openmediasoft.com',
				'zazagames.org',
				'extener.org',
				'openfrost.com',
				'openfrost.net',
				'googlsucks.com',
				'best-seo-offer.com',
				'buttons-for-your-website.com',
				'www.Get-Free-Traffic-Now.com',
				'best-seo-solution.com',
				'buy-cheap-online.info',
				'site3.free-share-buttons.com',
				'webmaster-traffic.com'
			);
			return $referlist;
		}

		function is_bad_referer() {
			$referlist = $this->default_bad_referers();
			$referlist = apply_filters( $this->prefix . "badreferlist", $referlist );

			if ( !empty( $referlist ) && !empty( $_SERVER ) && !empty( $_SERVER['HTTP_REFERER'] ) ) {
			    $ref = $_SERVER['HTTP_REFERER'];
				$regex = $this->quote_list_for_regex( $referlist );
			    if ( preg_match( '/' . $regex . '/i', $ref ) ) {
					return true;
				}
			}
			return false;
		}
		
		function allow_bot() {
			$allow_bot = true;
			if ( ( !$this->is_good_bot() ) && ( $this->is_bad_bot() ) && !is_user_logged_in() )
				$allow_bot = false;
			return apply_filters( $this->prefix . "allow_bot", $allow_bot );
		}

		/**
		 * Displays tabs for tabbed locations on a settings page.
		 */
		function display_tabs( $location ) {
			if ( ( $location != null ) && isset( $locations[$location]['tabs'] ) )
				$tabs = $locations['location']['tabs'];
			else
				$tabs = $this->tabs;
			if ( !empty( $tabs ) ) {
					?><div class="aioseop_tabs_div"><label class="aioseop_head_nav">
					<?php
					foreach ( $tabs as $k => $v ) {
					?>
						<a class="aioseop_head_nav_tab aioseop_head_nav_<?php if ( $this->current_tab != $k ) echo "in"; ?>active" href="<?php echo esc_url( add_query_arg( 'tab', $k ) ); ?>"><?php echo $v['name']; ?></a>
					<?php
					}
					?>
					</label></div>
				<?php
			}
		}
		
		function get_object_labels( $post_objs ) {
			$pt = array_keys( $post_objs );
			$post_types = Array();
			foreach ( $pt as $p )
				if ( !empty( $post_objs[$p]->label ) )
					$post_types[$p] = $post_objs[$p]->label;
				else
					$post_types[$p] = $p;
			return $post_types;
		}

		function get_term_labels( $post_objs ) {
			$post_types = Array();
			foreach ( $post_objs as $p )
				if ( !empty( $p->name ) )
					$post_types[$p->term_id] = $p->name;
			return $post_types;
		}
		
		function get_post_type_titles( $args = Array() ) {
			return $this->get_object_labels( get_post_types( $args, 'objects' ) );
		}

		function get_taxonomy_titles( $args = Array() ) {
			return $this->get_object_labels( get_taxonomies( $args, 'objects' ) );
		}
		
		function get_category_titles( $args = Array() ) {
			return $this->get_term_labels( get_categories( $args ) );
		}
		
		/**
		 * Helper function for exporting settings on post data.
		 */
		function post_data_export( $prefix = '_aioseop', $query = Array( 'posts_per_page' => -1 ) ) {
			$buf = '';
			$posts_query = new WP_Query( $query );
			while ($posts_query->have_posts() ) {
				$posts_query->the_post();
				global $post;
				$guid = $post->guid; $type = $post->post_type; $title = $post->post_title; $date = $post->post_date;
				$data = '';
				$post_custom_fields = get_post_custom( $post->ID );
				$has_data = null;

				if( is_array( $post_custom_fields ) ) {
					foreach( $post_custom_fields as $field_name => $field ){
						if( ( $this->strpos( $field_name, $prefix ) === 0 ) && ( $field[0] ) ) {
							$has_data = true;
							$data .= $field_name . " = '" . $field[0] . "'\n";
						} 
					}
				}
				if ( !empty( $data ) ) $has_data = true;

				if( $has_data != null ){
					$post_info = "\n[post_data]\n\n";
					$post_info .= "post_title = '" . $title . "'\n";
					$post_info .= "post_guid = '" . $guid . "'\n";
					$post_info .= "post_date = '" . $date . "'\n";
					$post_info .= "post_type = '" . $type . "'\n";
					if ( $data ) $buf .= $post_info . $data . "\n";
				}
			}
			wp_reset_postdata();
			return $buf;
		}
		
		/**
		 * Handles exporting settings data for a module.
		 */
		function settings_export( $buf ) {
			global $aiosp; 
			$post_types = null;
			$has_data = null;
			$general_settings = null;
			$exporter_choices = '';
			if ( !empty( $_REQUEST[ 'aiosp_importer_exporter_export_choices' ] ) )
				$exporter_choices = $_REQUEST[ 'aiosp_importer_exporter_export_choices' ];
			if ( !empty( $exporter_choices ) && is_array( $exporter_choices ) ) {
				foreach( $exporter_choices as $ex ) {
					if ( $ex == 1 ) $general_settings = true;
					if ( $ex == 2 ) {
						if ( isset( $_REQUEST[ 'aiosp_importer_exporter_export_post_types' ] ) ) 
							$post_types = $_REQUEST[ 'aiosp_importer_exporter_export_post_types' ];
					}
				}
			}

			if ( ( $post_types != null ) && ( $this === $aiosp ) )
				$buf .= $this->post_data_export( '_aioseop', Array( 'posts_per_page' => -1, 'post_type' => $post_types ) );

			/* Add all active settings to settings file */
			$name = $this->get_option_name();
			$options = $this->get_class_option();
			if( !empty( $options ) && $general_settings != null ) {
				$buf .= "\n[$name]\n\n";
				foreach ( $options as $key => $value ) {
					if ( ( $name == $this->parent_option ) && ( $key == 'modules' ) ) continue; // don't re-export all module settings -- pdb
					if ( is_array($value) )
						$value = "'" . str_replace( Array( "'", "\n", "\r" ), Array( "\'", '\n', '\r' ), trim( serialize( $value ) ) ) . "'";
					else
						$value = str_replace( Array( "\n", "\r" ), Array( '\n', '\r' ), trim( var_export($value, true) ) );
					$buf .= "$key = $value\n";
				}
			}
			return $buf;
		}
		
		/**
		 * Order for adding the menus for the aioseop_modules_add_menus hook.
		 */
		function menu_order() {
			return 10;
		}
		
		/**
		 * Print a basic error message.
		 */
		function output_error( $error ) {
			echo "<div class='aioseop_module error'>$error</div>";
			return FALSE;
		}
		
		/***
		 * Backwards compatibility - see http://php.net/manual/en/function.str-getcsv.php
		 */
		function str_getcsv( $input, $delimiter = ",", $enclosure = '"', $escape = "\\" ) {
			$fp = fopen( "php://memory", 'r+' );
			fputs( $fp, $input );
			rewind( $fp );
			$data = fgetcsv( $fp, null, $delimiter, $enclosure ); // $escape only got added in 5.3.0
			fclose( $fp );
			return $data;
		}
		
		/***
		 * Helper function to convert csv in key/value pair format to an associative array.
		 */
		function csv_to_array( $csv ) {
			$args = Array();
			if ( !function_exists( 'str_getcsv' ) )
				$v = $this->str_getcsv( $csv );
			else
				$v = str_getcsv( $csv );
			$size = count( $v );
			if ( is_array( $v ) && isset( $v[0] ) && $size >= 2 )
				for( $i = 0; $i < $size; $i += 2 )
					$args[ $v[ $i ] ] = $v[ $i + 1 ];
			return $args;
		}

		/** Allow modules to use WP Filesystem if available and desired, fall back to PHP filesystem access otherwise. */
		function use_wp_filesystem( $method = '', $form_fields = false, $url = '', $error = false ) {
			if ( empty( $method ) )
				$this->credentials = request_filesystem_credentials( $url );
			else
				$this->credentials = request_filesystem_credentials( $url, $method, $error, false, $form_fields );
			return $this->credentials;
		}
		
		/**
		 * Wrapper function to get filesystem object.
		 */
		function get_filesystem_object( ) {
			$cred = get_transient( 'aioseop_fs_credentials' );
			if ( !empty( $cred ) ) $this->credentials = $cred;
			
			if ( function_exists( 'WP_Filesystem' ) && ( WP_Filesystem( $this->credentials ) ) ) {
				global $wp_filesystem;
				return $wp_filesystem;
			} else {
				require_once( ABSPATH . 'wp-admin/includes/template.php' );
				require_once( ABSPATH . 'wp-admin/includes/screen.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );

				if ( !WP_Filesystem( $this->credentials ) )
					$this->use_wp_filesystem();

				if ( !empty( $this->credentials ) )
					set_transient( 'aioseop_fs_credentials', $this->credentials, 10800 );
				global $wp_filesystem;
				if ( is_object( $wp_filesystem ) )
					return $wp_filesystem;
			}
			return false;
		}
		
		/**
		 * See if a file exists using WP Filesystem.
		 */
		function file_exists( $filename ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) )
				return $wpfs->exists( $filename );
			return $wpfs;
		}

		/**
		 * See if the directory entry is a file using WP Filesystem.
		 */
		function is_file( $filename ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) )
				return $wpfs->is_file( $filename );
			return $wpfs;
		}
		
		/**
		 * List files in a directory using WP Filesystem.
		 */
		function scandir( $path ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) ) {
				$dirlist = $wpfs->dirlist( $path );
				if ( empty( $dirlist ) ) return $dirlist;
				return array_keys( $dirlist );
			}
			return $wpfs;			
		}
		
		/**
		 * Load a file through WP Filesystem; implement basic support for offset and maxlen.
		 */
		function load_file( $filename, $use_include_path = false, $context = null, $offset = -1, $maxlen = -1 ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) ) {
				if ( !$wpfs->exists( $filename ) ) return false;
				if ( ( $offset > 0 ) || ( $maxlen >= 0 ) ) {
					if ( $maxlen === 0 ) return '';
					if ( $offset < 0 ) $offset = 0;
					$file = $wpfs->get_contents( $filename );
					if ( !is_string( $file ) || empty( $file ) ) return $file;
					if ( $maxlen < 0 )
						return $this->substr( $file, $offset );
					else
						return $this->substr( $file, $offset, $maxlen );
				} else {
					return $wpfs->get_contents( $filename );
				}
			}
			return false;
		}
		
		/**
		 * Save a file through WP Filesystem.
		 */
		function save_file( $filename, $contents ) {
			$failed_str = __( sprintf( "Failed to write file %s!\n", $filename ), 'all-in-one-seo-pack' );
			$readonly_str = __( sprintf( "File %s isn't writable!\n", $filename ), 'all-in-one-seo-pack' );
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) ) {
				$file_exists = $wpfs->exists( $filename );
				if ( !$file_exists || $wpfs->is_writable( $filename ) ) {
					if ( $wpfs->put_contents( $filename, $contents ) === FALSE) return $this->output_error( $failed_str );
				} else return $this->output_error( $readonly_str );
				return true;
			}
			return false;
		}
		
		/**
		 * Delete a file through WP Filesystem.
		 */
		function delete_file( $filename ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) ) {
				if ( $wpfs->exists( $filename ) ) {
					if ( $wpfs->delete( $filename ) === FALSE)
						$this->output_error( __( sprintf( "Failed to delete file %s!\n", $filename ), 'all-in-one-seo-pack' ) );
					else
						return true;
				} else $this->output_error( __( sprintf( "File %s doesn't exist!\n", $filename ), 'all-in-one-seo-pack' ) );
			}
			return false;
		}
		
		/**
		 * Rename a file through WP Filesystem.
		 */
		function rename_file( $filename, $newname ) {
			$wpfs = $this->get_filesystem_object();
			if ( is_object( $wpfs ) ) {
				$file_exists = $wpfs->exists( $filename );
				$newfile_exists = $wpfs->exists( $newname );
				if ( $file_exists && !$newfile_exists ) {
					if ( $wpfs->move( $filename, $newname ) === FALSE)
						$this->output_error( __( sprintf( "Failed to rename file %s!\n", $filename ), 'all-in-one-seo-pack' ) );
					else
						return true;
				} else {
					if ( !$file_exists )
						$this->output_error( __( sprintf( "File %s doesn't exist!\n", $filename ), 'all-in-one-seo-pack' ) );
					elseif ( $newfile_exists )
						$this->output_error( __( sprintf( "File %s already exists!\n", $newname ), 'all-in-one-seo-pack' ) );
				}
			}
			return false;
		}

		/**
		 * Load multiple files.
		 */
		function load_files( $options, $opts, $prefix ) {
			foreach ( $opts as $opt => $file ) {
				$opt = $prefix . $opt;
				$file = ABSPATH . $file;
				$contents = $this->load_file( $file );
				if ( $contents !== false ) $options[$opt] = $contents;
			}
			return $options;
		}
		
		/**
		 * Save multiple files.
		 */
		function save_files( $opts, $prefix ) {
			foreach ( $opts as $opt => $file ) {
				$opt = $prefix . $opt;
				if ( isset($_POST[$opt] ) ) {
					$output = stripslashes_deep( $_POST[$opt] );
					$file = ABSPATH . $file;
					$this->save_file( $file, $output );
				}
			}
		}
		
		/**
		 * Delete multiple files.
		 */
		function delete_files( $opts ) {
			foreach ( $opts as $opt => $file ) {
				$file = ABSPATH . $file;
				$this->delete_file( $file );
			}
		}
		
		function get_all_images_by_type( $options = null, $p = null ) {
			$img = Array();
			if ( empty( $img ) ) {
				$size = apply_filters( 'post_thumbnail_size', 'large' );

				global $aioseop_options, $wp_query, $aioseop_opengraph;

				if ( $p === null ) {
					global $post;
				} else {
					$post = $p;
				}

				$count = 1;

				if ( !empty( $post ) ) {
					if ( !is_object( $post ) ) $post = get_post( $post );
					if ( is_object( $post ) && function_exists('get_post_thumbnail_id' ) ) {
						if ( $post->post_type == 'attachment' )
							$post_thumbnail_id = $post->ID;
						else
							$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
						if ( !empty( $post_thumbnail_id ) )	{
							$image = wp_get_attachment_image_src( $post_thumbnail_id, $size );
							if ( is_array( $image ) ) {
								$img[] = Array( 'type' => 'featured', 'id' => $post_thumbnail_id, 'link' => $image[0] );
							}
						}
					}

					$post_id = $post->ID;
					$p = $post; $w = $wp_query;

					$meta_key = '';
					if ( is_array( $options ) ) {
						if ( isset( $options['meta_key'] ) ) {
							$meta_key = $options['meta_key'];
						}
					}

					if ( !empty( $meta_key ) && !empty( $post ) ) {
						$meta_key = explode( ',', $meta_key );
						$image = $this->get_the_image_by_meta_key( Array( 'post_id' => $post->ID, 'meta_key' => $meta_key ) );
						if ( !empty( $image ) ) {
							$img[] = Array( 'type' => 'meta_key', 'id' => $meta_key, 'link' => $image );
						}
					}

					if (! $post->post_modified_gmt != '' )
						$wp_query = new WP_Query( array( 'p' => $post_id, 'post_type' => $post->post_type ) );
					if ( $post->post_type == 'page' )
						$wp_query->is_page = true;
					elseif ( $post->post_type == 'attachment' )
						$wp_query->is_attachment = true;
					else
						$wp_query->is_single = true;
					if 	( get_option( 'show_on_front' ) == 'page' ) {
						if ( $post->ID == get_option( 'page_for_posts' ) )
							$wp_query->is_home = true;
					}
					$args['options']['type'] = 'html';
					$args['options']['nowrap'] = false;
					$args['options']['save'] = false;
					$wp_query->queried_object = $post;

					$attachments = get_children( Array( 'post_parent' 		=> $post->ID, 
														'post_status' 		=> 'inherit', 
														'post_type' 		=> 'attachment', 
														'post_mime_type'	=> 'image', 
														'order' 			=> 'ASC', 
														'orderby' 			=> 'menu_order ID' ) );
					if ( !empty( $attachments ) )
						foreach( $attachments as $id => $attachment ) {
							$image = wp_get_attachment_image_src( $id, $size );
							if ( is_array( $image ) ) {
								$img[] = Array( 'type' => 'attachment', 'id' => $id, 'link' => $image[0] );
							}
						}
					$matches = Array();
					preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $post->ID ), $matches );
					if ( isset( $matches ) && !empty( $matches[1] ) && !empty( $matches[1][0] ) )
						foreach( $matches[1] as $i => $m ) {
							$img[] = Array( 'type' => 'post_content', 'id' => 'post' . $count++, 'link' => $m );
						}
					wp_reset_postdata();
					$wp_query = $w; $post = $p;
				}
			}
			return $img;
		}
		
		function get_all_images( $options = null, $p = null ) {
			$img = $this->get_all_images_by_type( $options, $p );
			$legacy = Array();
			foreach( $img as $k => $v ) {
				if ( $v['type'] == 'featured' ) {
					$legacy[$v['link']] = 1;
				} else {					
					$legacy[$v['link']] = $v['id'];
				}
			}
			return $legacy;
		}

		/*** Thanks to Justin Tadlock for the original get-the-image code - http://themehybrid.com/plugins/get-the-image ***/

		function get_the_image( $options = null, $p = null ) {
			
			if ( $p === null ) {
				global $post;
			} else {
				$post = $p;
			}
			
			$meta_key = '';
			if ( is_array( $options ) ) {
				if ( isset( $options['meta_key'] ) ) {
					$meta_key = $options['meta_key'];
				}
			}

			if ( !empty( $meta_key ) && !empty( $post ) ) {
				$meta_key = explode( ',', $meta_key );
				$image = $this->get_the_image_by_meta_key( Array( 'post_id' => $post->ID, 'meta_key' => $meta_key ) );				
			}
			if ( empty( $image ) ) $image = $this->get_the_image_by_post_thumbnail( $post );
			if ( empty( $image ) ) $image = $this->get_the_image_by_attachment( $post );
			if ( empty( $image ) ) $image = $this->get_the_image_by_scan( $post );
			if ( empty( $image ) ) $image = $this->get_the_image_by_default( $post );
			return $image;
		}
		
		function get_the_image_by_default( $p = null ) {
			return '';
		}

		function get_the_image_by_meta_key( $args = array() ) {

			/* If $meta_key is not an array. */
			if ( !is_array( $args['meta_key'] ) )
				$args['meta_key'] = array( $args['meta_key'] );

			/* Loop through each of the given meta keys. */
			foreach ( $args['meta_key'] as $meta_key ) {
				/* Get the image URL by the current meta key in the loop. */
				$image = get_post_meta( $args['post_id'], $meta_key, true );
				/* If a custom key value has been given for one of the keys, return the image URL. */
				if ( !empty( $image ) )
					return $image;
			}
			return false;
		}

		function get_the_image_by_post_thumbnail( $p = null ) {

			if ( $p === null ) {
				global $post;
			} else {
				$post = $p;
			}
			
			$post_thumbnail_id = null;
			if ( function_exists('get_post_thumbnail_id' ) ) {
				$post_thumbnail_id = get_post_thumbnail_id( $post->ID );				
			}

			if ( empty( $post_thumbnail_id ) ) 
				return false;

			$size = apply_filters( 'post_thumbnail_size', 'large' );
			$image = wp_get_attachment_image_src( $post_thumbnail_id, $size ); 
				return $image[0];
		}

		function get_the_image_by_attachment( $p = null ) {
			
			if ( $p === null ) {
				global $post;
			} else {
				$post = $p;
			}

			$attachments = get_children( Array( 'post_parent' 		=> $post->ID, 
												'post_status' 		=> 'inherit', 
												'post_type' 		=> 'attachment', 
												'post_mime_type'	=> 'image', 
												'order' 			=> 'ASC', 
												'orderby' 			=> 'menu_order ID' ) );

			if ( empty( $attachments ) ) {
				if ( 'attachment' == get_post_type( $post->ID ) ) {
					$image = wp_get_attachment_image_src( $post->ID, 'large' );
				}
			}

			/* If no attachments or image is found, return false. */
			if ( empty( $attachments ) && empty( $image ) )
				return false;

			/* Set the default iterator to 0. */
			$i = 0;

			/* Loop through each attachment. Once the $order_of_image (default is '1') is reached, break the loop. */
			foreach ( $attachments as $id => $attachment ) {
				if ( ++$i == 1 ) {
					$image = wp_get_attachment_image_src( $id, 'large' );
					$alt = trim( strip_tags( get_post_field( 'post_excerpt', $id ) ) );
					break;
				}
			}

			/* Return the image URL. */
			return $image[0];

		}

		function get_the_image_by_scan( $p = null ) {

			if ( $p === null ) {
				global $post;
			} else {
				$post = $p;
			}
			
			/* Search the post's content for the <img /> tag and get its URL. */
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $post->ID ), $matches );

			/* If there is a match for the image, return its URL. */
			if ( isset( $matches ) && !empty( $matches[1][0] ) )
				return $matches[1][0];

			return false;
		}
		
		/** crude approximization of whether current user is an admin */
		function is_admin() {
			return current_user_can( 'level_8' );
		}
		
		function help_text_helper( &$default_options, $options, $help_link = '' ) {
			foreach( $options as $o ) {
				$ht = '';
				if ( !empty( $this->help_text[$o] ) )
					$ht = $this->help_text[$o];
				elseif ( !empty( $default_options[$o]["help_text"] ) )
					$ht = $default_options[$o]["help_text"];
				if ( $ht && !is_array( $ht ) ) {
					$ha = '';
					$hl = $help_link;
					if ( strpos( $o, 'ga_' ) === 0 ) { // special case -- pdb
						$hl = 'http://semperplugins.com/documentation/advanced-google-analytics-settings/';
					}
					if ( !empty( $this->help_anchors[$o] ) ) $ha = $this->help_anchors[$o];
					if ( ( !empty( $ha ) && ( $pos = strrpos( $hl, '#' ) ) ) ) {
						$hl = substr( $hl, 0, $pos );
					}
					if ( ( !empty( $ha ) && ( $ha[0] == 'h' ) ) ) $hl = '';
					if ( !empty( $ha ) || !isset( $this->help_anchors[$o] ) ) {
						$ht .= "<br /><a href='" . $hl . $ha . "' target='_blank'>" . __( "Click here for documentation on this setting", 'all-in-one-seo-pack' ) . "</a>";						
					}
					$default_options[$o]['help_text'] = $ht;
				}
			}
		}
		
		function add_help_text_links() {
			if ( !empty( $this->help_text ) ) {
				foreach( $this->layout as $k => $v ) {
					$this->help_text_helper( $this->default_options, $v['options'], $v['help_link'] );
				}
				if ( !empty( $this->locations ) )
					foreach( $this->locations as $k => $v ) {
						if ( !empty( $v['default_options'] ) && !empty( $v['options'] ) ) {
							$this->help_text_helper( $this->locations[$k]['default_options'], $v['options'], $v['help_link'] );
						}
					}
			}
		}
		
		/**
		 * Load scripts and styles for metaboxes.
		 * 
		 * edit-tags exists only for pre 4.5 support... remove when we drop 4.5 support.
		 * Also, that check and others should be pulled out into their own functions
		 */
		function enqueue_metabox_scripts( ) {
			$screen = '';
			if ( function_exists( 'get_current_screen' ) )
				$screen = get_current_screen();
			$bail = false;
			if ( empty( $screen ) ) $bail = true;
			if ( ( $screen->base != 'post' ) && ( $screen->base != 'term' ) && ( $screen->base != 'edit-tags' ) && ( $screen->base != 'toplevel_page_shopp-products' ) ) $bail = true;
			$prefix = $this->get_prefix();
			$bail = apply_filters( $prefix . 'bail_on_enqueue', $bail, $screen );
			if ( $bail ) return;
			$this->form = 'post';
			if ( $screen->base == 'term' || $screen->base == 'edit-tags' ) $this->form = 'edittag';
			if ( $screen->base == 'toplevel_page_shopp-products' ) $this->form = 'product';
			$this->form = apply_filters( $prefix . 'set_form_on_enqueue', $this->form, $screen );
			foreach( $this->locations as $k => $v ) {
				if ( $v['type'] === 'metabox' ) {
					if ( isset( $v['display'] ) && !empty( $v['display'] ) ) {
						$enqueue_scripts = false;
						$enqueue_scripts = ( ( ( $screen->base == 'toplevel_page_shopp-products' ) && in_array( 'shopp_product', $v['display'] ) ) ) || in_array( $screen->post_type, $v['display'] );
						$enqueue_scripts = apply_filters( $prefix . 'enqueue_metabox_scripts', $enqueue_scripts, $screen, $v );
						if ( $enqueue_scripts ) {
							add_filter( 'aioseop_localize_script_data', Array( $this, 'localize_script_data' ) );	
							add_action( "admin_print_scripts", Array( $this, 'enqueue_scripts' ), 20 );
							add_action( "admin_print_scripts", Array( $this, 'enqueue_styles' ), 20 );
						}
					}
				}
			}
		}
		
		/**
		 * Load styles for module.
		 */
		function enqueue_styles( ) {
			wp_enqueue_style( 'thickbox' );
			if ( !empty( $this->pointers ) ) wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_style(  'aioseop-module-style',  AIOSEOP_PLUGIN_URL . 'css/modules/aioseop_module.css' );
			if ( function_exists( 'is_rtl' ) && is_rtl() )
				wp_enqueue_style(  'aioseop-module-style-rtl',  AIOSEOP_PLUGIN_URL . 'css/modules/aioseop_module-rtl.css', array('aioseop-module-style') );
		}
		
		/**
		 * Load scripts for module, can pass data to module script.
		 */
		function enqueue_scripts( ) {
			wp_enqueue_script( 'sack' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			if ( !empty( $this->pointers ) ) {
				wp_enqueue_script( 'wp-pointer', false, array( 'jquery' ) );
			}
			wp_enqueue_script( 'aioseop-module-script', AIOSEOP_PLUGIN_URL . 'js/modules/aioseop_module.js', Array(), AIOSEOP_VERSION );
			if ( !empty( $this->script_data ) ) {
				aioseop_localize_script_data();
			}
		}
		
		function localize_script_data( $data ) {
			if ( !is_array( $data ) ) {
				$data = Array( 0 => $data );
			}
			if ( empty( $this->script_data ) ) $this->script_data = Array();
			if ( !empty( $this->pointers ) )
				$this->script_data['pointers'] = $this->pointers;
			if ( empty( $data[0]['condshow'] ) ) $data[0]['condshow'] = Array();
			if ( empty( $this->script_data['condshow'] ) ) $this->script_data['condshow'] = Array();
			$condshow = $this->script_data['condshow'];
			$data[0]['condshow'] = array_merge( $data[0]['condshow'], $condshow );
			unset( $this->script_data['condshow'] );
			$data[0] = array_merge( $this->script_data, $data[0] );
			$this->script_data['condshow'] = $condshow;
			return $data;
		}
		
		/**
		 * Override this to run code at the beginning of the settings page.
		 */
		function settings_page_init() {
			
		}
		
		/**
		 * Filter out admin pointers that have already been clicked.
		 */
		function filter_pointers() {
			if ( !empty( $this->pointers ) ) {
				$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
				foreach( $dismissed as $d )
					if ( isset( $this->pointers[$d] ) )
						unset( $this->pointers[$d] );
			}
		}
		
		/**
		 * Add basic hooks when on the module's page.
		 */
		function add_page_hooks() {
			$hookname = current_filter();
			if ( $this->strpos( $hookname, 'load-' ) === 0 )
				$this->pagehook = $this->substr( $hookname, 5 );
			add_action( "admin_print_scripts", Array( $this, 'enqueue_scripts' ) );
			add_action( "admin_print_styles", Array( $this, 'enqueue_styles' ) );
			add_filter( 'aioseop_localize_script_data', Array( $this, 'localize_script_data' ) );	
			add_action( $this->prefix . 'settings_header', Array( $this, 'display_tabs' ) );
		}
		
		function get_admin_links() {
			if ( !empty( $this->menu_name ) )
				$name = $this->menu_name;
			else
				$name = $this->name;
			
			$hookname = plugin_basename( $this->file );
			
			$links = Array();
			$url = '';
            if ( function_exists( 'menu_page_url' ) )
                    $url = menu_page_url( $hookname, 0 );
            if ( empty( $url ) )
                    $url = esc_url( admin_url( 'admin.php?page=' . $hookname ) );
			
			if ( $this->locations === null )
				array_unshift( $links, array( 'parent' => AIOSEOP_PLUGIN_DIRNAME, 'title' => $name, 'id' => $hookname, 'href' => $url, 'order' => $this->menu_order() ) );
			else {
				foreach( $this->locations as $k => $v ) {
					if ( $v['type'] === 'settings' ) {
						if ( $k === 'default' ) {
							array_unshift( $links, array( 'parent' => AIOSEOP_PLUGIN_DIRNAME, 'title' => $name, 'id' => $hookname, 'href' => $url, 'order' => $this->menu_order() ) );
						} else {
							if ( !empty( $v['menu_name'] ) )
								$name = $v['menu_name'];
							else
								$name = $v['name'];
							array_unshift( $links, array( 'parent' => AIOSEOP_PLUGIN_DIRNAME, 'title' => $name, 'id' => $this->get_prefix( $k ) . $k, 'href' => esc_url( admin_url( 'admin.php?page=' . $this->get_prefix( $k ) . $k ) ), 'order' => $this->menu_order() ) );
						}
					}
				}
			}
			return $links;
		}
		
		function add_admin_bar_submenu() {
			global $aioseop_admin_menu, $wp_admin_bar;
			
			if ( $aioseop_admin_menu ) {
				$links = $this->get_admin_links();
				if ( !empty( $links ) )
					foreach( $links as $l )
						$wp_admin_bar->add_menu( $l );
			}
		}
		
		/**
		 * Collect metabox data together for tabbed metaboxes.
		 */
		function filter_return_metaboxes( $args ) {
			return array_merge( $args, $this->post_metaboxes );
		}
		
		/** Add submenu for module, call page hooks, set up metaboxes. */
		function add_menu( $parent_slug ) {
			if ( !empty( $this->menu_name ) )
				$name = $this->menu_name;
			else
				$name = $this->name;
			if ( $this->locations === null ) {
				$hookname = add_submenu_page( $parent_slug, $name, $name, apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ), plugin_basename( $this->file ), Array( $this, 'display_settings_page' ) );
				add_action( "load-{$hookname}", Array( $this, 'add_page_hooks' ) );
				return true;
			}
			foreach( $this->locations as $k => $v ) {
				if ( $v['type'] === 'settings' ) {
					if ( $k === 'default' ) {
						if ( !empty( $this->menu_name ) )
							$name = $this->menu_name;
						else
							$name = $this->name;
						$hookname = add_submenu_page( $parent_slug, $name, $name, apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ), plugin_basename( $this->file ), Array( $this, 'display_settings_page' ) );
					} else {
						if ( !empty( $v['menu_name'] ) )
							$name = $v['menu_name'];
						else
							$name = $v['name'];
						$hookname = add_submenu_page( $parent_slug, $name, $name, apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ), $this->get_prefix( $k ) . $k, Array( $this, "display_settings_page_$k" ) );
					}
					add_action( "load-{$hookname}", Array( $this, 'add_page_hooks' ) );
				} elseif ( $v['type'] === 'metabox' ) {
					$this->setting_options( $k ); // hack -- make sure this runs anyhow, for now -- pdb
					add_action( 'edit_post',		array( $this, 'save_post_data' ) );
					add_action( 'publish_post',		array( $this, 'save_post_data' ) );
					add_action( 'add_attachment',	array( $this, 'save_post_data' ) );
					add_action( 'edit_attachment',	array( $this, 'save_post_data' ) );
					add_action( 'save_post',		array( $this, 'save_post_data' ) );
					add_action( 'edit_page_form',	array( $this, 'save_post_data' ) );
					if ( isset( $v['display'] ) && !empty( $v['display'] ) ) {
						add_action( "admin_print_scripts", Array( $this, 'enqueue_metabox_scripts' ), 5 );
						if ( $this->tabbed_metaboxes )
							add_filter( 'aioseop_add_post_metabox', Array( $this, 'filter_return_metaboxes' ) );
						foreach ( $v['display'] as $posttype ) {
							$v['location'] = $k;
							$v['posttype'] = $posttype;
							if ( !isset($v['context']  ) ) $v['context']  = 'advanced';
							if ( !isset($v['priority'] ) ) $v['priority'] = 'default';
							if ( $this->tabbed_metaboxes ) {
								$this->post_metaboxes[] = Array( 'id' => $v['prefix'] . $k, 'title' => $v['name'], 'callback' => Array( $this, 'display_metabox' ),
																 'post_type' => $posttype, 'context' => $v['context'], 'priority' => $v['priority'], 'callback_args' => $v );
							} else {
								$title = $v['name'];
								if ( $title != $this->plugin_name ) $title = $this->plugin_name . ' - ' . $title;
								if ( !empty( $v['help_link'] ) )
									$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $lopts['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . "</span></a>";
								add_meta_box( $v['prefix'] . $k, $title, Array( $this, 'display_metabox' ), $posttype, $v['context'], $v['priority'], $v );
							}
						}
					}
				}
			}
		}
		
		/**
		 * Update postmeta for metabox.
		 */
		function save_post_data( $post_id ) {
			static $update = false;
			if ( $update ) return;
			if ( $this->locations !== null ) {
				foreach( $this->locations as $k => $v ) {
					if ( isset($v['type']) && ( $v['type'] === 'metabox' ) ) {
						$opts = $this->default_options( $k );
						$options = Array();
						$update = false;
						foreach ( $opts as $l => $o ) {
							if ( isset($_POST[$l] ) ) {
								$options[$l] = stripslashes_deep( $_POST[$l] );
								$options[$l] = esc_attr( $options[$l] );
								$update = true;
							}
						}
						if ( $update ) {
							$prefix = $this->get_prefix( $k );
							$options = apply_filters( $prefix . 'filter_metabox_options', $options, $k, $post_id );
							update_post_meta( $post_id, '_' . $prefix . $k, $options );
						}
					}
				}
			}
		}
		
		/**
		 * Outputs radio buttons, checkboxes, selects, multiselects, handles groups.
		 */		
		function do_multi_input( $args ) {
			extract( $args );
			$buf1 = '';
			$type = $options['type'];
			if ( ( $type == 'radio' ) || ( $type == 'checkbox' ) ) {
				$strings = Array(
					'block'		=> "%s\n",
					'group'		=> "\t<b>%s</b><br>\n%s\n",
					'item'		=> "\t<label class='aioseop_option_setting_label'><input type='$type' %s name='%s' value='%s' %s> %s</label>\n",
					'item_args' => Array( 'sel', 'name', 'v', 'attr', 'subopt' ),
					'selected'	=> 'checked '
					);
			} else {
				$strings = Array(
						'block'		=> "<select name='$name' $attr>%s\n</select>\n",
						'group'		=> "\t<optgroup label='%s'>\n%s\t</optgroup>\n",
						'item'		=> "\t<option %s value='%s'>%s</option>\n",
						'item_args' => Array( 'sel', 'v', 'subopt' ),
						'selected'	=> 'selected '
					);
			}
			$setsel = $strings['selected'];
			if ( isset($options['initial_options'] ) && is_array($options['initial_options']) ) {
				foreach ( $options['initial_options'] as $l => $option ) {
					$is_group = is_array( $option );
					if ( !$is_group ) $option = Array( $l => $option );
					$buf2 = '';
					foreach ( $option as $v => $subopt ) {
						$sel = '';
						$is_arr = is_array( $value );
						if ( is_string( $v ) || is_string( $value ) ) {
							if ( is_string( $value ) )
								$cmp = !strcmp( $v, $value );
							else
								$cmp = !strcmp( $v, "" );
						//	$cmp = !strcmp( (string)$v, (string)$value );							
						} else
							$cmp = ( $value == $v );
						if ( ( !$is_arr && $cmp ) || ( $is_arr && in_array( $v, $value ) ) )
							$sel = $setsel;
						$item_arr = Array();
						foreach( $strings['item_args'] as $arg ) $item_arr[] = $$arg;
						$buf2 .= vsprintf( $strings['item'], $item_arr );
					}
					if ( $is_group )
						$buf1 .= sprintf( $strings['group'], $l, $buf2);
					else
						$buf1 .= $buf2;
				}				
				$buf1 = sprintf( $strings['block'], $buf1 );
			}
			return $buf1;
		}
		
		/**
		 * Outputs a setting item for settings pages and metaboxes.
		 */
		function get_option_html( $args ) {
			static $n = 0;
			extract( $args );
			if ( $options['type'] == 'custom' )
				return apply_filters( "{$prefix}output_option", '', $args );				
			if ( in_array( $options['type'], Array( 'multiselect', 'select', 'multicheckbox', 'radio', 'checkbox', 'textarea', 'text', 'submit', 'hidden' ) ) && ( is_string( $value ) ) )
				$value = esc_attr( $value );
			$buf = '';
			$onload = '';
			if ( !empty( $options['count'] ) ) {
				$n++;
				$attr .= " onKeyDown='if (typeof countChars == \"function\") countChars(document.{$this->form}.$name,document.{$this->form}.{$prefix}length$n)' onKeyUp='if (typeof countChars == \"function\") countChars(document.{$this->form}.$name,document.{$this->form}.{$prefix}length$n)'";
				$onload = "if (typeof countChars == \"function\") countChars(document.{$this->form}.$name,document.{$this->form}.{$prefix}length$n);";
			}
			if ( isset( $opts['id'] ) ) $attr .= " id=\"{$opts['id']}\" ";
			switch ( $options['type'] ) {
				case 'multiselect':   $attr .= ' MULTIPLE';
									  $args['attr'] = $attr;
									  $args['name'] = $name = "{$name}[]";
				case 'select':		  $buf .= $this->do_multi_input( $args ); break;
				case 'multicheckbox': $args['name'] = $name = "{$name}[]";
									  $args['options']['type'] = $options['type'] = 'checkbox';
				case 'radio':		  $buf .= $this->do_multi_input( $args ); break;
				case 'checkbox':	  if ( $value ) $attr .= ' CHECKED';
									  $buf .= "<input name='$name' type='{$options['type']}' $attr>\n"; break;
				case 'textarea':	  $buf .= "<textarea name='$name' $attr>$value</textarea>"; break;
				case 'image':		  $buf .= "<input class='aioseop_upload_image_button button-primary' type='button' value='Upload Image' style='float:left;' />" .
											  "<input class='aioseop_upload_image_label' name='$name' type='text' $attr value='$value' size=57 style='float:left;clear:left;'>\n";
									  break;
				case 'html':		  $buf .= $value; break;
				default:			  $buf .= "<input name='$name' type='{$options['type']}' $attr value='$value'>\n";
			}
			if ( !empty( $options['count'] ) ) {
				$size = 60;
				if ( isset( $options['size'] ) ) $size = $options['size'];
				elseif ( isset( $options['rows'] ) && isset( $options['cols'] ) ) $size = $options['rows'] * $options['cols'];
				if ( isset( $options['count_desc'] ) )
					$count_desc = $options['count_desc'];
				else
					$count_desc = __( ' characters. Most search engines use a maximum of %s chars for the %s.', 'all-in-one-seo-pack' );
				$buf .= "<br /><input readonly type='text' name='{$prefix}length$n' size='3' maxlength='3' style='width:53px;height:23px;margin:0px;padding:0px 0px 0px 10px;' value='" . $this->strlen($value) . "' />"
					 . sprintf( $count_desc, $size, trim( $this->strtolower( $options['name'] ), ':' ) );
				if ( !empty( $onload ) ) $buf .= "<script>jQuery( document ).ready(function() { {$onload} });</script>";
			}
			return $buf;
		}
		
		const DISPLAY_HELP_START	= '<a class="aioseop_help_text_link" style="cursor:pointer;" title="%s" onclick="toggleVisibility(\'%s_tip\');"><label class="aioseop_label textinput">%s</label></a>';
		const DISPLAY_HELP_END		= '<div class="aioseop_help_text_div" style="display:none" id="%s_tip"><label class="aioseop_help_text">%s</label></div>';
		const DISPLAY_LABEL_FORMAT  = '<span class="aioseop_option_label" style="text-align:%s;vertical-align:top;">%s</span>';
		const DISPLAY_TOP_LABEL		= "</div>\n<div class='aioseop_input aioseop_top_label'>\n";
		const DISPLAY_ROW_TEMPLATE	= '<div class="aioseop_wrapper%s" id="%s_wrapper"><div class="aioseop_input">%s<span class="aioseop_option_input"><div class="aioseop_option_div" %s>%s</div>%s</span><p style="clear:left"></p></div></div>';
		
		/**
		 * Format a row for an option on a settings page.
		 */
		function get_option_row( $name, $opts, $args ) {
			$label_text = $input_attr = $help_text_2 = $id_attr = '';
			if ( $opts['label'] == 'top' )
				$align	= 'left';
			else
				$align = 'right';
			if ( isset( $opts['id'] ) ) $id_attr .= " id=\"{$opts['id']}_div\" ";
			if ( $opts['label'] != 'none' ) { 
				if ( isset( $opts['help_text'] ) ) {
					$help_text = sprintf(	All_in_One_SEO_Pack_Module::DISPLAY_HELP_START, __( 'Click for Help!', 'all-in-one-seo-pack' ), $name, $opts['name'] );
					$help_text_2 = sprintf(	All_in_One_SEO_Pack_Module::DISPLAY_HELP_END, $name, $opts['help_text'] );
				} else $help_text = $opts['name'];
				$label_text = sprintf( All_in_One_SEO_Pack_Module::DISPLAY_LABEL_FORMAT, $align, $help_text );
			} else $input_attr .= ' aioseop_no_label ';
			if ( $opts['label'] == 'top' ) $label_text .= All_in_One_SEO_Pack_Module::DISPLAY_TOP_LABEL;
			$input_attr .= " aioseop_{$opts['type']}_type";
			return sprintf( All_in_One_SEO_Pack_Module::DISPLAY_ROW_TEMPLATE, $input_attr, $name, $label_text, $id_attr, $this->get_option_html( $args ), $help_text_2 );
		}
		
		/**
		 * Display options for settings pages and metaboxes, allows for filtering settings, custom display options.
		 */
		function display_options( $location = null, $meta_args = null ) {
				static $location_settings = Array();
				$defaults = null;
				$prefix = $this->get_prefix( $location );
				$help_link = '';
				if ( is_array( $meta_args['args'] ) && !empty( $meta_args['args']['default_options'] ) ) {
					$defaults = $meta_args['args']['default_options'];
				}
				if ( !empty( $meta_args['callback_args'] ) ) {
					if ( !empty( $meta_args['callback_args']['help_link'] ) ) {
						$help_link = $meta_args['callback_args']['help_link'];
					}
				}
				if ( !empty( $help_link ) )
					echo "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $help_link . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . "</span></a>";
				
				if ( !isset( $location_settings[$prefix] ) ) {
					$current_options = apply_filters( "{$this->prefix}display_options",  $this->get_current_options( Array(), $location, $defaults ), $location );
					$settings		 = apply_filters( "{$this->prefix}display_settings", $this->setting_options( $location, $defaults ), $location, $current_options );
					$current_options = apply_filters( "{$this->prefix}override_options", $current_options, $location, $settings );
					$location_settings[$prefix]['current_options'] = $current_options;
					$location_settings[$prefix]['settings']		   = $settings;
				} else {
					$current_options = $location_settings[$prefix]['current_options'];
					$settings		 = $location_settings[$prefix]['settings'];
				}
				// $opts["snippet"]["default"] = sprintf( $opts["snippet"]["default"], "foo", "bar", "moby" );
				$container = "<div class='aioseop aioseop_options {$this->prefix}settings'>";
				if ( is_array( $meta_args['args'] ) && !empty( $meta_args['args']['options'] ) ) {
					$args = Array();
					$arg_keys = Array();
					foreach ( $meta_args['args']['options'] as $a ) {
						if ( !empty($location) ) {
							$key = $prefix . $location . '_' . $a;
							if ( !isset( $settings[$key] ) ) $key = $a;
						} else $key = $prefix . $a;
						if ( isset( $settings[$key] ) ) $arg_keys[$key] = 1;
						elseif ( isset( $settings[$a] ) ) $arg_keys[$a] = 1;
					}
					$setting_keys = array_keys( $settings );
					foreach ( $setting_keys as $s )
						if ( !empty( $arg_keys[$s] ) ) $args[$s] = $settings[$s];
				} else $args = $settings;
				foreach ( $args as $name => $opts ) {
					$attr_list = Array( 'class', 'style', 'readonly', 'disabled', 'size', 'placeholder' );
					if ( $opts['type'] == 'textarea' ) $attr_list = array_merge( $attr_list, Array('rows', 'cols') );
					$attr = '';
					foreach ( $attr_list as $a )
						if ( isset( $opts[$a] ) ) $attr .= ' ' . $a . '="' . esc_attr( $opts[$a] ) . '" ';
					$opt = '';
					if ( isset( $current_options[$name] ) ) $opt = $current_options[$name];
 					if ( $opts['label'] == 'none' && $opts['type'] == 'submit' && $opts['save'] == false ) $opt = $opts['name'];
					if ( $opts['type'] == 'html' && empty( $opt ) && $opts['save'] == false ) $opt = $opts['default'];

					$args = Array( 'name' => $name, 'options' => $opts, 'attr' => $attr, 'value' => $opt, 'prefix' => $prefix );
					if ( !empty( $opts['nowrap'] ) )
						echo $this->get_option_html( $args );
					else {
						if ( $container ) {
							echo $container;
							$container = '';
						}
						echo $this->get_option_row( $name, $opts, $args );
					}
				}
			if ( !$container ) echo "</div>";
		}
		
		function sanitize_domain( $domain ) {
			$domain = trim( $domain );
			$domain = $this->strtolower( $domain );
			if ( $this->strpos( $domain, "http://" ) === 0 ) $domain = $this->substr( $domain, 7 );
			elseif ( $this->strpos( $domain, "https://" ) === 0 ) $domain = $this->substr( $domain, 8 );
			$domain = untrailingslashit( $domain );
			return $domain;
		}

		/** Sanitize options */
		function sanitize_options( $location = null ) {
			foreach ( $this->setting_options( $location ) as $k => $v ) {
				if ( isset( $this->options[$k] ) ) {
					if ( !empty( $v['sanitize'] ) )
						$type = $v['sanitize'];
					else
						$type = $v['type'];
					switch ( $type ) {
						case 'multiselect':
						case 'multicheckbox': $this->options[$k] = urlencode_deep( $this->options[$k] );
											  break;
						case 'textarea':	  $this->options[$k] = wp_kses_post( $this->options[$k] );
											  $this->options[$k] = htmlspecialchars( $this->options[$k], ENT_QUOTES );
											  break;
						case 'filename':	  $this->options[$k] = sanitize_file_name( $this->options[$k] );
											  break;
						case 'text':		  $this->options[$k] = wp_kses_post( $this->options[$k] );
						case 'checkbox':
						case 'radio':
						case 'select':
						default:			  if ( !is_array( $this->options[$k] ) ) $this->options[$k] = esc_attr( $this->options[$k] );
					}
				}
			}
		}

		/**
		 * Display metaboxes with display_options()
		 */
		function display_metabox( $post, $metabox ) {
			$this->display_options( $metabox['args']['location'], $metabox );
		}

		/**
		 * Handle resetting options to defaults.
		 */
		function reset_options( $location = null, $delete = false ) {
			if ( $delete === true ) {
				$this->delete_class_option( $delete );
				$this->options = Array();
			}
			$default_options = $this->default_options( $location );
			foreach ( $default_options as $k => $v ) {
				$this->options[$k] = $v;
			}
			$this->update_class_option( $this->options );
		}
		
		/** handle option resetting and updating */
		function handle_settings_updates( $location = null ) {
			$message = '';
			if ( (isset($_POST['action']) && $_POST['action'] == 'aiosp_update_module' &&
			     ( isset( $_POST['Submit_Default'] ) || isset( $_POST['Submit_All_Default'] ) || !empty( $_POST['Submit'] ) ) ) ) {
				$nonce = $_POST['nonce-aioseop'];
				if (!wp_verify_nonce($nonce, 'aioseop-nonce')) die ( __( 'Security Check - If you receive this in error, log out and back in to WordPress', 'all-in-one-seo-pack' ) );
				if ( isset( $_POST['Submit_Default'] ) || isset( $_POST['Submit_All_Default'] ) ) {
					$message = __( "Options Reset.", 'all-in-one-seo-pack' );
					if ( isset($_POST['Submit_All_Default']) ) {
						$this->reset_options( $location, true );
						do_action( 'aioseop_options_reset' );
					} else {
						$this->reset_options( $location );
					}
				}
				if ( !empty( $_POST['Submit'] ) ) {
					$message = __("All in One SEO Options Updated.", 'all-in-one-seo-pack');
					$default_options = $this->default_options( $location );
					foreach( $default_options as $k => $v ) {
						if ( isset( $_POST[$k] ) )
							$this->options[$k] = stripslashes_deep( $_POST[$k] );
						else
							$this->options[$k] = '';
					}
					$this->sanitize_options( $location );
					$this->options = apply_filters( $this->prefix . 'update_options', $this->options, $location );
					$this->update_class_option( $this->options );
					wp_cache_flush();
				}
				do_action( $this->prefix . 'settings_update', $this->options, $location );
			}
			return $message;
		}

		/** Update / reset settings, printing options, sanitizing, posting back */
		function display_settings_page( $location = null ) {
				if ( $location != null ) $location_info = $this->locations[$location];
				$name = null;
				if ( ( $location ) && ( isset( $location_info['name'] ) ) ) $name = $location_info['name'];
				if ( !$name ) $name = $this->name;
				$message = $this->handle_settings_updates( $location );
				$this->settings_page_init();
	?>
				<div class="wrap <?php echo get_class( $this ); ?>">
					<div id="aioseop_settings_header">
					<?php if ( !empty( $message ) ) echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>"; ?>
					<div id="icon-aioseop" class="icon32"><br></div><h2><?php echo $name; ?></h2>
								<div id="dropmessage" class="updated" style="display:none;"></div>
					</div>
<?php				
					do_action( 'aioseop_global_settings_header', $location );
					do_action( $this->prefix . 'settings_header', $location );
?>	<form id="aiosp_settings_form" name="dofollow" enctype="multipart/form-data" action="" method="post">
		<div id="aioseop_top_button">
			<div id="aiosp_ajax_settings_message"></div>
<?php

			$submit_options = Array('action'		=> Array( 'type' => 'hidden', 'value' => 'aiosp_update_module' ),
									'module'		=> Array( 'type' => 'hidden', 'value' => get_class( $this ) ), 
									'location'		=> Array( 'type' => 'hidden', 'value' => $location ), 
									'nonce-aioseop'	=> Array( 'type' => 'hidden', 'value' => wp_create_nonce('aioseop-nonce') ),
									'page_options'	=> Array( 'type' => 'hidden', 'value' => 'aiosp_home_description' ),
									'Submit'		=> Array( 'type' => 'submit', 'class' => 'button-primary', 'value' => __('Update Options', 'all-in-one-seo-pack') . ' &raquo;' ),
									'Submit_Default'=> Array( 'type' => 'submit', 'class' => 'button-secondary', 'value' => __( sprintf( 'Reset %s Settings to Defaults', $name ), 'all-in-one-seo-pack') . ' &raquo;' )
								   );
			$submit_options = apply_filters( "{$this->prefix}submit_options", $submit_options, $location );
			foreach ( $submit_options as $k => $s ) {
				if ( $s['type'] == 'submit' && $k != 'Submit' ) continue;
				$class = '';
				if ( isset( $s['class'] ) ) $class = " class='{$s['class']}' ";
				echo $this->get_option_html( Array( 'name' => $k, 'options' => $s, 'attr' => $class, 'value' => $s['value'] ) );
			}
?>
		</div>
					<div class="aioseop_options_wrapper aioseop_settings_left">
					<?php $opts = $this->get_class_option();
						  if ($opts !== FALSE) $this->options = $opts;						
						if ( is_array( $this->layout ) ) {
							foreach( $this->layout as $l => $lopts ) {
								if ( !isset( $lopts['tab'] ) || ( $this->current_tab == $lopts['tab'] ) ) {
									$title = $lopts['name'];
									if ( !empty( $lopts['help_link'] ) )
										$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $lopts['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . "</span></a>";
									add_meta_box( $this->get_prefix( $location ) . $l . "_metabox", $title, array($this, 'display_options' ),
												"{$this->prefix}settings", 'advanced', 'default', $lopts );									
								}
							}
						} else add_meta_box( $this->get_prefix( $location ) . "metabox", $name, array($this, 'display_options'), "{$this->prefix}settings", 'advanced');
						do_meta_boxes( "{$this->prefix}settings", 'advanced', $location );
?>				<p class="submit" style="clear:both;"><?php
					foreach( Array( 'action', 'nonce-aioseop', 'page_options' ) as $submit_field )
						if ( isset( $submit_field ) ) unset( $submit_field );
					foreach ( $submit_options as $k => $s ) {
						$class = '';
						if ( isset( $s['class'] ) ) $class = " class='{$s['class']}' ";
						echo $this->get_option_html( Array( 'name' => $k, 'options' => $s, 'attr' => $class, 'value' => $s['value'] ) );
					}
?>	</p>
				</div>
				</form>
					<?php 	do_action( $this->prefix . 'settings_footer', $location ); 
							do_action( 'aioseop_global_settings_footer', $location ); ?>
				</div>	<?php
		}
		
		/**
		 * Get the prefix used for a given location.
		 */
		function get_prefix( $location = null ) {
			if ( ($location != null ) && isset($this->locations[$location]['prefix'] ) )
				return $this->locations[$location]['prefix'];
			return $this->prefix;
		}

		/** Sets up initial settings */
		function setting_options( $location = null, $defaults = null ) {
			if ( $defaults === null )
				$defaults = $this->default_options;
			$prefix = $this->get_prefix( $location );
			$opts = Array();
			if ( $location == null || $this->locations[$location]['options'] === null )
				$options = $defaults;
			else {
				$options = Array();
				$prefix = "{$prefix}{$location}_";
				if ( !empty( $this->locations[$location]['default_options'] ) )
					$options = $this->locations[$location]['default_options'];
				foreach( $this->locations[$location]['options'] as $opt ) {
					if ( isset( $defaults[$opt] ) )
						$options[$opt] = $defaults[$opt];
				}
			}
			if ( !$prefix ) $prefix = $this->prefix;
			if ( !empty( $options ) )
				foreach ($options as $k => $v) {
					if ( !isset( $v['name'] ) )		$v['name'] = $this->ucwords( strtr( $k, '_', ' ' ) );
					if ( !isset( $v['type'] ) )		$v['type'] = 'checkbox';
					if ( !isset( $v['default'] ) )	$v['default'] = null;
					if ( !isset( $v['initial_options'] ) ) $v['initial_options'] = $v['default'];
					if ( $v['type'] == 'custom' && ( !isset( $v['nowrap'] ) ) ) $v['nowrap'] = true;
					elseif ( !isset( $v['nowrap'] ) ) $v['nowrap'] = null;
					if ( isset( $v['condshow'] ) ) {
						if ( !is_array( $this->script_data ) ) $this->script_data = Array();
						if ( !isset( $this->script_data['condshow'] ) ) $this->script_data['condshow'] = Array();
						$this->script_data['condshow'][$prefix . $k] = $v['condshow'];
					}
					if ( $v['type'] == 'submit' ) {
						if ( !isset($v['save'] ) )  $v['save']  = false;
						if ( !isset($v['label'] ) ) $v['label'] = 'none';
						if ( !isset($v['prefix'] ) ) $v['prefix'] = false;
					} else {
						if ( !isset($v['label'] ) ) $v['label'] = null;
					}
					if ( $v['type'] == 'hidden' ) {
						if ( !isset($v['label']) ) $v['label'] = 'none';
						if ( !isset($v['prefix']) ) $v['prefix'] = false;
					}
					if  ( ( $v['type'] == 'text' ) && ( !isset( $v['size'] ) ) ) $v['size'] = 57;
					if ( $v['type'] == 'textarea' ) {
						if ( !isset($v['cols'])) $v['cols'] = 57;
						if ( !isset($v['rows'])) $v['rows'] = 2;
					}
					if ( !isset($v['save']) ) $v['save'] = true;
					if ( !isset($v['prefix']) ) $v['prefix'] = true;
					if ( $v['prefix'] )
						$opts[$prefix . $k] = $v;
					else
						$opts[$k] = $v;
				}
			return $opts;
		}

		/** Generates just the default option names and values */
		function default_options( $location = null, $defaults = null ) {
			$options = $this->setting_options( $location, $defaults );
			$opts = Array();
			foreach ( $options as $k => $v ) if ( $v['save'] ) $opts[$k] = $v['default'];
			return $opts;
		}
		
		/** Gets the current options stored for a given location. */
		function get_current_options( $opts = Array(), $location = null, $defaults = null, $post = null ) {
			$prefix = $this->get_prefix( $location );
			$get_opts = '';
			if ( empty( $location ) )
				$type = 'settings';
			else
				$type = $this->locations[$location]['type'];
			if ( $type === 'settings' ) {
				$get_opts = $this->get_class_option();
			} elseif ( $type == 'metabox' ) {
				if ( $post == null ) {
					global $post;
				}
				
				if ( ( isset( $_GET['taxonomy'] ) && isset( $_GET['tag_ID'] ) ) || is_category() || is_tag() || is_tax() ) {

					if ( AIOSEOPPRO ) {
						$get_opts = AIO_ProGeneral::getprotax( $get_opts );
					}
					
				} elseif ( isset( $post ) ) {
					$get_opts = get_post_meta( $post->ID, '_' . $prefix . $location, true );
				}
			}
			$defs = $this->default_options( $location, $defaults );
			if ($get_opts == '')
				$get_opts = $defs;
			else
				$get_opts = wp_parse_args( $get_opts, $defs );
			$opts = wp_parse_args( $opts, $get_opts );
			return $opts;
		}

		/** Updates the options array in the module; loads saved settings with get_option() or uses defaults */
		function update_options( $opts = Array(), $location = null, $defaults = null ) {
			if ($location === null )
				$type = 'settings';
			else
				$type = $this->locations[$location][$type];
			if ( $type === 'settings' ) {
				$get_opts = $this->get_class_option();
			}
			if ($get_opts === FALSE)
				$get_opts = $this->default_options( $location, $defaults );
			else
				$this->setting_options( $location, $defaults ); // hack -- make sure this runs anyhow, for now -- pdb
			$this->options = wp_parse_args( $opts, $get_opts );
		}
	}
}
