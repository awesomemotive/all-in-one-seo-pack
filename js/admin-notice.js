/**
 * Admin Notices for AIOSEOP.
 *
 * @summary  Handles the AJAX Actions with AIOSEOP_Notices
 *
 * @since    2.4.2
 */

(function($) {

	/**
	 * @summary Sets up the Delay Button listeners
	 *
	 * @since 2.4.2
	 * @access public
	 *
	 * @global string $aioseop_notice_data.notice_nonce
	 * @listens aioseop-notice-delay-{notice_slug}-{delay_index}:click
	 * 
	 * @param string notice_slug
	 * @param string delay_index
	 */
	function aioseop_notice_delay_ajax_action( notice_slug, delay_index ) {
		var notice_nonce = aioseop_notice_data.notice_nonce;
		var noticeDelayID = '#aioseop-notice-delay-' + notice_slug + '-' + delay_index;
		$( noticeDelayID ).on( 'click', function( event ) {
			if ( '#' === this.href || '' === this.href ) {
				// Stops automatic actions.
				event.stopPropagation();
				event.preventDefault();
			}

			var formData = new FormData();
			formData.append( 'notice_slug', notice_slug );
			formData.append( 'delay_index', delay_index );

			formData.append( 'action', 'aioseop_notice' );
			formData.append( '_ajax_nonce', notice_nonce );
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,

				success: function( data, textStatus, jqXHR ){
					var noticeContainer = '.aioseop-notice-' + notice_slug;
					$( noticeContainer ).remove();
				}
			});
		});
	}

	/**
	 * @summary
	 *
	 * @since 2.4.2
	 * @access public
	 *
	 * @global string $aioseop_notice_data.notice_nonce
	 * @listens aioseop-notice-delay-{notice_slug}-{delay_index}:click
	 *
	 * @param string notice_slug
	 */
	function aioseop_notice_delay_wp_default_dismiss_ajax_action( notice_slug ) {
		var notice_nonce = aioseop_notice_data.notice_nonce;
		var noticeContainer = '.aioseop-notice-' + notice_slug;
		$( noticeContainer ).on( 'click', 'button.notice-dismiss ', function( event ) {
			// Prevents any unwanted actions.
			event.stopPropagation();
			event.preventDefault();

			var formData = new FormData();
			formData.append( 'notice_slug', notice_slug );
			formData.append( 'delay_index', 'default' );

			formData.append( 'action', 'aioseop_notice' );
			formData.append( '_ajax_nonce', notice_nonce );
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false
			});
		});
	}

	/**
	 * @summary Sets up the Action Button listeners inside the footers.
	 *
	 * @since   2.4.2
	 * @access  public
	 *
	 * @global  string $aioseop_notice_data.footer_nonce
	 * @listens .aioseop-footer-{footer_slug} > #aioseop-footer-action-{action_index} 
	 *
	 * @param   string footer_slug
	 * @param   string action_index
	 */
	function aioseop_footer_ajax_action( footer_slug, action_index ) {
		var footer_nonce = aioseop_notice_data.footer_nonce;
		var footerContainer = '.aioseop-footer-' + footer_slug;
		var footerActionElem = '#aioseop-footer-action-' + action_index;
		
		$( footerContainer ).on( 'click', footerActionElem, function( event ) {
			var elem_href = $( this ).attr( 'href' );
			if ( '#' === elem_href || '' === elem_href ) {
				// Stops automatic actions.
				event.stopPropagation();
				event.preventDefault();
			}

			var formData = new FormData();
			formData.append( 'footer_slug', footer_slug );
			formData.append( 'action_index', action_index );

			formData.append( 'action', 'aioseop_footer' );
			formData.append( '_ajax_nonce', footer_nonce );
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,

				success: function( data, textStatus, jqXHR ){
					if ( true === data.dismissed ) {
						var footerContainer = '.aioseop-footer-' + footer_slug;
						$( footerContainer ).remove();
					}
				}
			});
		});
	}

	/**
	 * INITIALIZE NOTICE JS
	 *
	 * Constructs the actions the user may perform.
	 */
	var notice_delays = aioseop_notice_data.notice_delays;

	$.each( notice_delays, function ( k1_notice_slug, v_delay_arr ) {
		$.each( v_delay_arr, function ( k2_i, v_delay_index ) {
			aioseop_notice_delay_ajax_action( k1_notice_slug, v_delay_index );
		});

		// Default WP action for Dismiss Button on Upper-Right.
		aioseop_notice_delay_wp_default_dismiss_ajax_action( k1_notice_slug );
	});

	var footers = aioseop_notice_data.footers;

	$.each( footers, function( k1_footer_slug, v1_footer_arr ) {
		$.each( v1_footer_arr, function( k2_i, v2_action_index ) {
			aioseop_footer_ajax_action( k1_footer_slug, v2_action_index );
		});
	});
})(jQuery);
