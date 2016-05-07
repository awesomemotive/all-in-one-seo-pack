<?php

/**
 * @package All-in-One-SEO-Pack
 */

class aiosp_common {
	
	function __construct(){
		//construct
	}
	
	
	static function get_blog_page( $p = null ) {
		static $blog_page = '';
		static $page_for_posts = '';
		if ( $p === null ) {
			global $post;
		} else {
			$post = $p;
		}
		if ( $blog_page === '' ) {
			if ( $page_for_posts === '' ) $page_for_posts = get_option( 'page_for_posts' );
			if ( $page_for_posts && is_home() && ( !is_object( $post ) || ( $page_for_posts != $post->ID ) ) )
				$blog_page = get_post( $page_for_posts );
		}
		return $blog_page;
	}
	
	static function get_upgrade_hyperlink( $location = '', $title = '', $anchor = '', $target = '', $class = '', $id = ''  ){
		
		$affiliate_id = '';
		
		//call during plugins_loaded
		$affiliate_id = apply_filters( 'aiosp_aff_id' , $affiliate_id );


		//build URL
		$url = 'http://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/';
		if( $location ) $url .= '?loc=' . $location;
		if( $affiliate_id ) $url .= "?ap_id=$affiliate_id";
		
		
		//build hyperlink		
		$hyperlink = '<a ';
		if( $target ) $hyperlink .= "target=\"$target\" ";
		if( $title ) $hyperlink .= "title=\"$title\" ";
		$hyperlink .= "href=\"$url\">$anchor</a>";

		return $hyperlink;
	}
	
	static function get_upgrade_url(){
		//put build URL stuff in here
	}
	
}