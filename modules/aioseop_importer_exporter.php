<?php

if ( ! class_exists( 'All_in_One_SEO_Pack_Importer_Exporter' ) ) {


	/**
	 * Class All_in_One_SEO_Pack_Importer_Exporter
	 */
	class All_in_One_SEO_Pack_Importer_Exporter extends All_in_One_SEO_Pack_Module {


		/**
		 * All_in_One_SEO_Pack_Importer_Exporter constructor.
		 */
		function __construct() {
			$this->name   = __( 'Importer & Exporter', 'all-in-one-seo-pack' );    // Human-readable name of the module
			$this->prefix = 'aiosp_importer_exporter_'; // option prefix
			$this->file   = __FILE__;
			parent::__construct();
			$help_text             = Array(
				'import_submit'     => __(
					"Select a valid All in One SEO Pack ini file and click 'Import' to import options from a previous state or install of All in One SEO Pack.<br /><a href='https://semperplugins.com/documentation/importer-exporter-module/' target='_blank'>Click here for documentation on this setting</a>",
					'all-in-one-seo-pack'
				),
				'export_choices'    => __(
					"You may choose to export settings from active modules, and content from post data.<br /><a href='https://semperplugins.com/documentation/importer-exporter-module/' target='_blank'>Click here for documentation on this setting</a>",
					'all-in-one-seo-pack'
				),
				'export_post_types' => __(
					"Select which Post Types you want to export your All in One SEO Pack meta data for.<br /><a href='https://semperplugins.com/documentation/importer-exporter-module/' target='_blank'>Click here for documentation on this setting</a>",
					'all-in-one-seo-pack'
				),
			);
			$this->warnings        = Array();
			$this->default_options = array(
				'import_submit'      => Array(
					'name'    => __( 'Import', 'all-in-one-seo-pack' ),
					'default' => '',
					'type'    => 'file',
					'save'    => false,
				),
				'export_choices'     => Array(
					'name'            => __( 'Export Settings', 'all-in-one-seo-pack' ),
					'type'            => 'multicheckbox',
					'initial_options' => Array(
						1 => __( 'General Settings', 'all-in-one-seo-pack' ),
 +						2 => __( 'Post Data', 'all-in-one-seo-pack' ),
					),
				),
				'export_post_types'  => Array(
					'name'            => __( 'Export Post Types:', 'all-in-one-seo-pack' ),
					'default'         => Array(
						'post' => 'post',
						'page' => 'page',
					),
					'type'            => 'multicheckbox',
					'initial_options' => $this->get_post_type_titles(
						Array( '_builtin' => false )
					),
				),
				'import_export_help' => Array(
					'type'    => 'html',
					'label'   => 'none',
					'default' => __(
						             'Note: If General Settings is checked, the
								General Settings, the Feature Manager settings,
								and the following currently active modules will
								have their settings data exported:',
						             'all-in-one-seo-pack'
					             ) . '<br />',
				),
			);
			if ( ! empty( $help_text ) ) {
				foreach ( $help_text as $k => $v ) {
					$this->default_options[ $k ]['help_text'] = $v;
				}
			}
			$this->layout = Array(
				'default' => Array(
					'name'      => $this->name,
					'help_link' => 'https://semperplugins.com/documentation/importer-exporter-module/',
					'options'   => array_keys( $this->default_options ),
				),
			);

			// load initial options / set defaults
			add_action( 'admin_init', Array( $this, 'debug_post_types' ), 5 );
		}


		function settings_page_init() {
			add_filter(
				$this->prefix . 'submit_options',
				Array( $this, 'filter_submit' )
			);
		}


		/**
		 * @param $submit
		 *
		 * @return array
		 */
		function filter_submit( $submit ) {
			$submit['Submit']['value'] = __(
				                             'Import',
				                             'all-in-one-seo-pack'
			                             )
			                             . ' &raquo;';

			return Array(
				       'export_submit' => Array(
					       'type'  => 'submit',
					       'class' => 'button-primary',
					       'value' => __( 'Export', 'all-in-one-seo-pack' ) . ' &raquo;',
				       ),
			       ) + $submit;
		}


		function debug_post_types() {
			$post_types                                                    = $this->get_post_type_titles();
			$rempost                                                       = array(
				'attachment'    => 1,
				'revision'      => 1,
				'nav_menu_item' => 1,
			);
			$this->default_options['export_post_types']['initial_options'] = array_diff_key(
				$post_types,
				$rempost
			);
			global $aioseop_modules;
			if ( ! empty( $aioseop_modules ) ) {
				$modules = $aioseop_modules->get_loaded_module_list();
				if ( ! empty( $modules ) && ! empty( $modules['feature_manager'] ) ) {
					unset( $modules['feature_manager'] );
				}
				if ( ! empty( $modules ) ) {
					$this->default_options['import_export_help']['default'] .= "<ul>\n";
					foreach ( $modules as $m ) {
						$module = $aioseop_modules->return_module( $m );
						$this->default_options['import_export_help']['default'] .=
							"\t<li>" . $module->name . "</li>\n";
					}
					$this->default_options['import_export_help']['default'] .= "\n</ul>\n";
				} else {
					$this->default_options['import_export_help']['default'] .= '<br />'
					                                                           . __(
						                                                           'There are no other modules currently loaded!',
						                                                           'all-in-one-seo-pack'
					                                                           );
				}
			}
			$this->default_options['import_export_help']['default'] .= '<br />'
			                                                           . __(
				                                                           'You may change this by activating or deactivating
						modules in the Feature Manager.',
				                                                           'all-in-one-seo-pack'
			                                                           );
			$this->update_options();
			if ( ! empty( $_REQUEST['export_submit'] ) ) {
				$this->do_importer_exporter();
			} else {
				add_action(
					$this->prefix . 'settings_update',
					Array( $this, 'do_importer_exporter' )
				);
			}
		}


		/**
		 * @param $args
		 *
		 * @return string
		 */
		function importer_exporter_export( $args ) {

			// Adds all settings to settings file
			$name = $this->get_option_name();
			$buf  = '[' . $this->get_option_name() . "]\n";
			if ( ! empty( $this->options ) ) {
				foreach ( $this->options as $key => $value ) {
					$buf .= "$key = '" . str_replace(
							"'",
							"\'",
							trim( serialize( $value ) )
						) . "'\n";
				}
			}

			return $buf;
		}


		function show_import_warnings() {

			echo '<div class="error fade" style="width:52%">';

			if ( is_array( $this->warnings ) ) {
				foreach ( $this->warnings as $warning ) {
					echo "<p>" . esc_html( $warning ) . "</p>";
				}
			}
			echo '</div>';
		}


		/**
		 * @param $array
		 *
		 * @return array
		 */
		function parse_ini_helper( $array ) {
			$returnArray = array();
			if ( is_array( $array ) ) {
				foreach ( $array as $key => $value ) {
					$e = explode( ':', $key );
					if ( ! empty( $e[1] ) ) {
						$x = array();
						foreach ( $e as $tk => $tv ) {
							$x[ $tk ] = trim( $tv );
						}
						$x = array_reverse( $x, true );
						foreach ( $x as $k => $v ) {
							$c = $x[0];
							if ( empty( $returnArray[ $c ] ) ) {
								$returnArray[ $c ] = array();
							}
							if ( isset( $returnArray[ $x[1] ] ) ) {
								$returnArray[ $c ] = array_merge(
									$returnArray[ $c ], $returnArray[ $x[1] ]
								);
							}
							if ( $k === 0 ) {
								$returnArray[ $c ] = array_merge(
									$returnArray[ $c ], $array[ $key ]
								);
							}
						}
					} else {
						$returnArray[ $key ] = $array[ $key ];
					}
				}
			}

			return $returnArray;
		}


		/**
		 * @param $array
		 *
		 * @return array
		 */
		function recursive_parse( $array ) {
			$returnArray = array();
			if ( is_array( $array ) ) {
				foreach ( $array as $key => $value ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = $this->recursive_parse( $value );
					}
					$x = explode( '.', $key );
					if ( ! empty( $x[1] ) ) {
						$x = array_reverse( $x, true );
						if ( isset( $returnArray[ $key ] ) ) {
							unset( $returnArray[ $key ] );
						}
						if ( ! isset( $returnArray[ $x[0] ] ) ) {
							$returnArray[ $x[0] ] = array();
						}
						$first = true;
						foreach ( $x as $k => $v ) {
							if ( $first === true ) {
								$b     = $array[ $key ];
								$first = false;
							}
							$b = array( $v => $b );
						}
						$returnArray[ $x[0] ] = array_merge_recursive(
							$returnArray[ $x[0] ], $b[ $x[0] ]
						);
					} else {
						$returnArray[ $key ] = $array[ $key ];
					}
				}
			}

			return $returnArray;
		}


		/**
		 * @param      $assoc_arr
		 * @param bool $has_sections
		 *
		 * @return string
		 */
		function get_ini_file( $assoc_arr, $has_sections = true ) {
			$content = '';
			if ( $has_sections ) {
				foreach ( $assoc_arr as $key => $elem ) {
					$content .= '[' . $key . "]\n";
					foreach ( $elem as $key2 => $elem2 ) {
						if ( is_array( $elem2 ) ) {
							for ( $i = 0; $i < count( $elem2 ); $i ++ ) {
								$content .= $key2 . "[] = \"" . $elem2[ $i ] . "\"\n";
							}
						} else if ( $elem2 == '' ) {
							$content .= $key2 . " = \n";
						} else {
							$content .= $key2 . " = \"" . $elem2 . "\"\n";
						}
					}
				}
			} else {
				foreach ( $assoc_arr as $key => $elem ) {
					if ( is_array( $elem ) ) {
						for ( $i = 0; $i < count( $elem ); $i ++ ) {
							$content .= $key2 . "[] = \"" . $elem[ $i ] . "\"\n";
						}
					} else if ( $elem == '' ) {
						$content .= $key2 . " = \n";
					} else {
						$content .= $key2 . " = \"" . $elem . "\"\n";
					}
				}
			}

			return $content;
		}


		/**
		 * @param $string
		 *
		 * @return array
		 */
		function parse_ini_advanced( $string ) {
			return $this->recursive_parse(
				$this->parse_ini_helper(
					parse_ini_string( $string, true )
				)
			);
		}


		function do_importer_exporter() {
			$submit       = null;
			$count        = 0;
			$post_exists  = null;
			$post_warning = null;
			global $aioseop_options, $aiosp, $aioseop_module_list;
			if ( isset( $_REQUEST['nonce-aioseop'] ) ) {
				$nonce = $_REQUEST['nonce-aioseop'];
			}
			$post_fields = Array(
				'keywords',
				'description',
				'title',
				'meta',
				'disable',
				'disable',
				'disable_analytics',
				'togglekeywords',
			);
			if ( ! empty( $_FILES['aiosp_importer_exporter_import_submit']['tmp_name'] ) ) {
				$submit = 'Import';
			}
			if ( ! empty( $_REQUEST['export_submit'] ) ) {
				$submit = 'Export';
			}
			if ( ( $submit != null ) && wp_verify_nonce( $nonce, 'aioseop-nonce' ) ) {
				switch ( $submit ) {
					case 'Import':
						try {
							// Parses export file
							$file          = $this->get_sanitized_file(
								$_FILES['aiosp_importer_exporter_import_submit']['tmp_name']
							);
							$section       = Array();
							$section_label = null;
							foreach ( $file as $line_number => $line ) {
								$line    = trim( $line );
								$matches = Array();
								if ( empty( $line ) ) {
									continue;
								}
								if ( $line[0] == ';' ) {
									continue;
								}
								if ( preg_match( "/^\[(\S+)\]$/", $line, $label ) ) {
									$section_label = strval( $label[1] );
									if ( $section_label == 'post_data' ) {
										$count ++;
									}
									if ( ! isset( $section[ $section_label ] ) ) {
										$section[ $section_label ] = Array();
									}
								} elseif ( preg_match( "/^(\S+)\s*=\s*'(.*)'$/", $line, $matches ) ) {
									if ( $section_label == 'post_data' ) {
										$section[ $section_label ][ $count ][ $matches[1] ] = $matches[2];
									} else {
										$section[ $section_label ][ $matches[1] ] = $matches[2];
									}
								} elseif ( preg_match( "/^(\S+)\s*=\s*NULL$/", $line, $matches ) ) {
									if ( $section_label == 'post_data' ) {
										$section[ $section_label ][ $count ][ $matches[1] ] = null;
									} else {
										$section[ $section_label ][ $matches[1] ] = null;
									}
								} else {
									$this->warnings[] = sprintf(
										__(
											'<b>Warning:</b> Line not matched: <b>"%s"</b>, On Line: <b>%s</b>',
											'all-in-one-seo-pack'
										),
										$line,
										$line_number
									);
								}
							}

							// Updates Plugin Settings
							if ( is_array( $section ) ) {
								foreach ( $section as $label => $module_options ) {
									if ( is_array( $module_options ) ) {
										foreach ( $module_options as $key => $value ) {

											// Updates Post Data
											if ( $label == 'post_data' ) {
												$post_exists = post_exists(
													$module_options[ $key ]['post_title'],
													'',
													$module_options[ $key ]['post_date']
												);
												$target      = get_post( $post_exists );
												if ( ( ! empty( $module_options[ $key ]['post_type'] ) )
												     && $post_exists != null
												) {
													if ( is_array( $value ) ) {
														foreach ( $value as $field_name => $field_value ) {
															if ( substr( $field_name, 1, 7 ) == 'aioseop' ) {
																if ( $value ) {
																	update_post_meta(
																		$target->ID,
																		$field_name,
																		maybe_unserialize( $field_value )
																	);
																} else {
																	delete_post_meta(
																		$target->ID,
																		$field_name
																	);
																}
															}
														}
													}
													$post_exists = null;
												} else {
													$target_title = $module_options[ $key ]['post_title'];
													$post_warning = sprintf(
														__(
															'<b>Warning:</b> This following post could not be found: <b>"%s"</b>',
															'all-in-one-seo-pack'
														),
														$target_title
													);
												}
												if ( $post_warning != null ) {
													$this->warnings[] = $post_warning;
													$post_warning     = null;
												}

												// Updates Module Settings
											} else {
												$module_options[ $key ] = str_replace(
													Array( "\'", '\n', '\r' ),
													Array( "'", "\n", "\r" ),
													maybe_unserialize( $value )
												);
											}
										}

										// Updates Module Settings
										$this->update_class_option(
											$module_options,
											$label
										);
									}
								}
							}
						} catch ( Exception $e ) {
							$this->warnings[] = $e->getMessage();
						}

						// Shows all errors found
						if ( ! empty( $this->warnings ) ) {
							add_action(
								$this->prefix . 'settings_header',
								Array( $this, 'show_import_warnings' ),
								5
							);
						}

						break;
					case 'Export':

						// Creates Files Contents
						$settings_file = 'settings_aioseop.ini';
						$buf           = '; ' . __(
								'Settings export file for All in One SEO Pack', '
							all-in-one-seo-pack'
							) . "\n";

						// Adds all settings to settings file
						$buf = $aiosp->settings_export( $buf );
						$buf = apply_filters( 'aioseop_export_settings', $buf );

						// Sends File to browser
						$strlength = strlen( $buf );
						header( 'Content-type: application/ini' );
						header( "Content-Disposition: attachment; filename=$settings_file" );
						header( 'Content-Length: ' . $strlength );
						echo $buf;
						die();
						break;
				}
			}
		}


		function settings_update() {
		}

		/**
		 * Returns sanitized imported file.
		 *
		 * @since
		 *
		 * @param string $filename Path to where the uploaded file is located.
		 *
		 * @return array Sanitized file as array.
		 * @throws Exception
		 */
		private function get_sanitized_file( $filename ) {
			$file = file( $filename );
			for ( $i = count( $file ) - 1; $i >= 0; -- $i ) {
				// Remove insecured lines
				if ( preg_match( '/\<(\?php|script)/', $file[ $i ] ) ) {
					throw new Exception( __(
						'<b>Security warning:</b> Your file looks compromised. Please check the file for any script-injection.',
						'all-in-one-seo-pack'
					) );
				}
				// Apply security filters
				$file[ $i ] = strip_tags( trim( $file[ $i ] ) );
				// Remove empty lines
				if ( empty( $file[ $i ] ) ) {
					unset( $file[ $i ] );
				}
			}

			return $file;
		}
	}
}
