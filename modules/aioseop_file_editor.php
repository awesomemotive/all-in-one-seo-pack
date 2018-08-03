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
			$this->current_tab = 'htaccess';
			if ( isset( $_REQUEST['tab'] ) ) {
				$this->current_tab = $_REQUEST['tab'];
			}

			$this->default_options = array(
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

			$this->tabs = array(
				'htaccess' => array( 'name' => __( '.htaccess' ) ),
			);

			$this->layout = array(
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
			if ( 'htaccess' === $this->current_tab ) {
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
			if ( 'htaccess' === $this->current_tab ) {
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
			if ( 'htaccess' === $this->current_tab && isset( $_POST['Submit_htaccess'] ) && $_POST['Submit_htaccess'] ) {
				$this->save_files( array( 'htaccfile' => '.htaccess' ), $prefix );
			}
		}
	}
}
