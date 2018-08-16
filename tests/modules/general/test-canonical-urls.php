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
	 * Checks if a paginated post specifies the correct canonical URL on every page i.e. the canonical URL takes into account the page number.
	 */
	public function test_pagination() {
		wp_set_current_user( 1 );

		global $aioseop_options;
		$aioseop_options['aiosp_can'] = 1;
		update_option( 'aioseop_options', $aioseop_options );

		$id = $this->factory->post->create( array( 'post_type' => 'post', 'post_content' => 'one <!--nextpage--> two <!--nextpage--> three <!--nextpage-->' ) );

		$link_page = get_permalink( $id );
		$pages[] = $link_page;
		$pages[] = add_query_arg( 'page', 2, $link_page );
		$pages[] = add_query_arg( 'page', 3, $link_page );

		foreach ( $pages as $page ) {
			$links = $this->parse_html( $page, array( 'link' ) );
			$canonical_url = null;
			foreach ( $links as $link ) {
				if ( 'canonical' === $link['rel'] ) {
					$canonical_url = $link['href'];
					break;
				}
			}
			$this->assertEquals( $page, $canonical_url );
		}
	}

	/**
	 * Checks if a paginated post specifies the same URL on every page i.e. the canonical URL ignores the page number.
	 */
	public function test_ignore_pagination() {
		wp_set_current_user( 1 );

		global $aioseop_options;
		$aioseop_options['aiosp_can'] = 1;
		$aioseop_options['aiosp_no_paged_canonical_links'] = 1;
		update_option( 'aioseop_options', $aioseop_options );

		$id = $this->factory->post->create( array( 'post_type' => 'post', 'post_content' => 'one <!--nextpage--> two <!--nextpage--> three <!--nextpage-->' ) );
		$link_page = get_permalink( $id );
		$pages[] = $link_page;
		$pages[] = add_query_arg( 'page', 2, $link_page );
		$pages[] = add_query_arg( 'page', 3, $link_page );

		foreach ( $pages as $page ) {
			$links = $this->parse_html( $page, array( 'link' ) );
			$canonical_url = null;
			foreach ( $links as $link ) {
				if ( 'canonical' === $link['rel'] ) {
					$canonical_url = $link['href'];
					break;
				}
			}
			$this->assertEquals( $link_page, $canonical_url );
		}
	}
}
