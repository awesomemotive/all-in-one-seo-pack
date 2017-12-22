/**
 * Admin Footer for AIOSEOP.
 *
 * @summary  Handles the AJAX Actions with AIOSEOP_Notices
 *
 * @since    2.4.2
 */

(function($) {
    /**
     * @summary Sets up the Action Button listeners inside the footers.
     *
     * @since   2.4.2
     * @access  public
     *
     * @global  string $aioseop_notice_data.footer_nonce
     * @listens .aioseop-footer-{footer_slug} > #aioseop-footer-action-{action_index}
     *
     * @param   string footerSlug
     * @param   string action_index
     */
    function aioseop_footer_ajax_action( footerSlug, actionIndex ) {
        var footerNonce = aioseop_footer_data.footer_nonce;
        var footerContainer = ".aioseop-footer-" + footerSlug;
        var footerActionElem = "#aioseop-footer-action-" + actionIndex;

        $( footerContainer ).on( "click", footerActionElem, function( event ) {
            var elem_href = $( this ).attr( "href" );
            if ( "#" === elem_href || "" === elem_href ) {
                // Stops automatic actions.
                event.stopPropagation();
                event.preventDefault();
            }

            var formData = new FormData();
            formData.append( "footer_slug", footerSlug );
            formData.append( "action_index", actionIndex );

            formData.append( "action", "aioseop_footer" );
            formData.append( "_ajax_nonce", footerNonce );
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: formData,
                cache: false,
                dataType: "json",
                processData: false,
                contentType: false,

                success: function( data, textStatus, jqXHR ){
                    if ( true === data.dismissed ) {
                        var footerContainer = ".aioseop-footer-" + footerSlug;
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
    var footers = aioseop_footer_data.footers;

    $.each( footers, function( k1FooterSlug, v1FooterArr ) {
        $.each( v1FooterArr, function( k2I, v2ActionIndex ) {
            aioseop_footer_ajax_action( k1FooterSlug, v2ActionIndex );
        });
    });
})(jQuery);
