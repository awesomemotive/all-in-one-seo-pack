<?php
/**
 * Class Test_Canonical_Urls
 *
 * @package 
 */

/**
 * Canonnical URLs test cases.
 */

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

class Test_Canonical_Urls extends AIOSEOP_Test_Base {

	public function setUp() {
		$this->init( true );
	}

	/**
	 * Checks if a non-paginated post specifies the same URL on every page even with "No Pagination for Canonical URLs" unchecked.
	 * Checks if a paginated taxonomy archive DOES NOT specify the same URL on every page.
	 */
	public function test_ignore_pagination() {
		wp_set_current_user( 1 );

		global $aioseop_options;
		$aioseop_options['aiosp_can'] = 1;
		update_option( 'aioseop_options', $aioseop_options );

		$id = $this->factory->post->create( array( 'post_type' => 'post', 'post_content' => 'one two three' ) );
		$link_page = get_permalink( $id );
		$pages[] = $link_page;
		$pages[] = add_query_arg( 'page', 2, $link_page );
		$pages[] = add_query_arg( 'page', 3, $link_page );

		foreach ( $pages as $page ) {
			$links = $this->parse_html( $page, array( 'link' ) );
			//error_log("getting $page " . print_r($links,true));
			$canonical_url = null;
			foreach ( $links as $link ) {
				if ( 'canonical' === $link['rel'] ) {
					$canonical_url = $link['href'];
					break;
				}
			}
			$this->assertEquals( $link_page, $canonical_url );
		}

		// test taxonomy archive pages.
		$this->factory->post->create_many( 100 );
		$cat_id = get_cat_ID( 'Uncategorized' );
		$link_page = get_category_link( $cat_id );
		$pages = array();
		$pages[] = $link_page;
		$pages[] = add_query_arg( 'page', 2, $link_page );
		$pages[] = add_query_arg( 'page', 3, $link_page );

		$canonical_urls = array();
		foreach ( $pages as $page ) {
			$links = $this->parse_html( $page, array( 'link' ) );
			foreach ( $links as $link ) {
				if ( 'canonical' === $link['rel'] ) {
					$canonical_urls[] = $link['href'];
					break;
				}
			}
		}

		// all canonical urls should be different.
		$this->assertEquals( count( $canonical_urls ), count( array_unique( $canonical_urls ) ) );
	}
}
