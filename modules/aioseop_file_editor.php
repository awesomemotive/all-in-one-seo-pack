<?php

/**
 * The File Editor class.
 *
 * @package All-in-One-SEO-Pack
 */
if ( ! class_exists( 'All_in_One_SEO_Pack_File_Editor' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_File_Editor
	 */
	class All_in_One_SEO_Pack_File_Editor extends All_in_One_SEO_Pack_Module {

		/**
		 * All_in_One_SEO_Pack_File_Editor constructor.
		 */
		function __construct() {
			$this->name   = __( 'File Editor', 'all-in-one-seo-pack' );        // Human-readable name of the plugin
			$this->prefix = 'aiosp_file_editor_';                        // option prefix
			$this->file   = __FILE__;                                        // the current file
			parent::__construct();
			$this->current_tab = 'robots';
			if ( isset( $_REQUEST['tab'] ) ) {
				$this->current_tab = $_REQUEST['tab'];
			}

			$help_text             = array(
				'robotfile' => __( 'Robots.txt editor', 'all-in-one-seo-pack' ),
				'htaccfile' => __( '.htaccess editor', 'all-in-one-seo-pack' ),
			);
			$this->default_options = array(
				'robotfile' => array(
					'name'    => __( 'Edit Robots.txt', 'all-in-one-seo-pack' ),
					'save'    => false,
					'default' => '',
					'type'    => 'textarea',
					'cols'    => 70,
					'rows'    => 25,
					'label'   => 'top',
				),
				'htaccfile' => array(
					'name'    => __( 'Edit .htaccess', 'all-in-one-seo-pack' ),
					'save'    => false,
					'default' => '',
					'type'    => 'textarea',
					'cols'    => 70,
					'rows'    => 25,
					'label'   => 'top',
				),
			);

			if ( ! empty( $help_text ) ) {
				foreach ( $help_text as $k => $v ) {
					$this->default_options[ $k ]['help_text'] = $v;
				}
			}
			$this->tabs = array(
				'robots'   => array( 'name' => __( 'robots.txt' ) ),
				'htaccess' => array( 'name' => __( '.htaccess' ) ),
			);

			$this->layout = array(
				'robots'   => array(
					'name'    => __( 'Edit robots.txt', 'all-in-one-seo-pack' ),
					'options' => array( 'robotfile' ),
					'tab'     => 'robots',
				),
				'htaccess' => array(
					'name'    => __( 'Edit .htaccess', 'all-in-one-seo-pack' ),
					'options' => array( 'htaccfile' ),
					'tab'     => 'htaccess',
				),
			);

			$this->update_options();            // load initial options / set defaults
		}

		function settings_page_init() {
			add_filter( $this->prefix . 'display_options', array( $this, 'filter_options' ), 10, 2 );
			add_filter( $this->prefix . 'submit_options', array( $this, 'filter_submit' ), 10, 2 );
		}

		function add_page_hooks() {
			parent::add_page_hooks();
			add_action( $this->prefix . 'settings_update', array( $this, 'do_file_editor' ), 10, 2 );
		}

		/**
		 * @param $submit
		 * @param $location
		 *
		 * @return mixed
		 */
		function filter_submit( $submit, $location ) {
			unset( $submit['Submit_Default'] );
			$submit['Submit']['type'] = 'hidden';
			if ( 'robots' === $this->current_tab ) {
				$submit['Submit_File_Editor'] = array(
					'type'  => 'submit',
					'class' => 'button-primary',
					'value' => __( 'Update robots.txt', 'all-in-one-seo-pack' ) . ' &raquo;',
				);
			} elseif ( 'htaccess' === $this->current_tab ) {
				$submit['Submit_htaccess'] = array(
					'type'  => 'submit',
					'class' => 'button-primary',
					'value' => __( 'Update .htaccess', 'all-in-one-seo-pack' ) . ' &raquo;',
				);
			}

			return $submit;
		}

		/**
		 * @param $options
		 * @param $location
		 *
		 * @return mixed
		 */
		function filter_options( $options, $location ) {
			$prefix = $this->get_prefix( $location );
			if ( 'robots' === $this->current_tab ) {
				$options = $this->load_files( $options, array( 'robotfile' => 'robots.txt' ), $prefix );
			} elseif ( 'htaccess' === $this->current_tab ) {
				$options = $this->load_files( $options, array( 'htaccfile' => '.htaccess' ), $prefix );
			}

			return $options;
		}

		/**
		 * @param $options This seems to be unused.
		 * @param $location
		 */
		function do_file_editor( $options, $location ) {
			$prefix = $this->get_prefix( $location );
			if ( 'robots' === $this->current_tab && isset( $_POST['Submit_File_Editor'] ) && $_POST['Submit_File_Editor'] ) {
				$this->save_files( array( 'robotfile' => 'robots.txt' ), $prefix );
			} elseif ( 'htaccess' === $this->current_tab && isset( $_POST['Submit_htaccess'] ) && $_POST['Submit_htaccess'] ) {
				$this->save_files( array( 'htaccfile' => '.htaccess' ), $prefix );
			}
		}
	}
}
