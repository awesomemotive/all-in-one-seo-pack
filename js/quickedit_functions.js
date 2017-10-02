jQuery(document).on( 'click', '.visibility-notice', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'aioseo_dismiss_visibility_notice'
        }
    })

})

jQuery(document).on( 'click', '.yst_notice', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'aioseo_dismiss_yst_notice'
        }
    })

})

jQuery(document).on( 'click', '.woo-upgrade-notice', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'aioseo_dismiss_woo_upgrade_notice'
        }
    })

})

jQuery(document).on( 'click', '.sitemap_max_urls_notice', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'aioseo_dismiss_sitemap_max_url_notice'
        }
    })

})


function aioseop_ajax_edit_meta_form( post_id, meta, nonce ) {
	var uform = jQuery('#aioseop_'+meta+'_' + post_id);
	var post_title = jQuery('#aioseop_label_' + meta + '_' + post_id).text();
	var element = uform.html(); var input;
	input = '<textarea id="aioseop_new_'+meta+'_' + post_id + '" style="font-size:13px;width:100%;float:left;position:relative;z-index:1;" rows=4 cols=32>'  + post_title + '</textarea>';
	input += '<label style="float:left">';
	input += '<a class="aioseop_mpc_SEO_admin_options_edit" href="javascript:void(0);" id="aioseop_'+meta+'_save_' + post_id + '" >';
	input += '<img src="' + aioseopadmin.imgUrl+'accept.png" border="0" alt="" title="'+meta+'" /></a>';
	input += '<a class="aioseop_mpc_SEO_admin_options_edit" href="javascript:void(0);" id="aioseop_'+meta+'_cancel_' + post_id + '" >';
	input += '<img src="' + aioseopadmin.imgUrl+'delete.png" border="0" alt="" title="'+meta+'" /></a>';
	input += '</label>';
	uform.html( input );
	uform.attr( "class", "aioseop_mpc_admin_meta_options aio_editing" );
	jQuery('#aioseop_'+meta+'_cancel_' + post_id).click(function() {
		uform.html( element );
		uform.attr( "class", "aioseop_mpc_admin_meta_options" );
	});
	jQuery('#aioseop_'+meta+'_save_' + post_id).click(function() {
		var new_meta = jQuery( '#aioseop_new_'+meta+'_' + post_id ).val();
		handle_post_meta( post_id, new_meta, meta, nonce );
	});
}

function handle_post_meta( p, t, m, n ) {
	jQuery("div#aioseop_"+m+"_"+p).fadeOut('fast', function() {
		var loading = '<label class="aioseop_'+m+'_loading">';
		loading += '<img style="width:20px;margin-right:5px;float:left" align="absmiddle" ';
		loading += 'src="'+aioseopadmin.imgUrl+'activity.gif" border="0" alt="" title="'+m+'" /></a>';
		loading += '</label><div style="float:left">Please wait…</div>';
		jQuery("div#aioseop_"+m+"_"+p).fadeIn('fast', function() {
			var aioseop_sack = new sack(aioseopadmin.requestUrl);
			aioseop_sack.execute = 1;
			aioseop_sack.method = 'POST';
			aioseop_sack.setVar( "action", "aioseop_ajax_save_meta");
			aioseop_sack.setVar( "post_id", p );
			aioseop_sack.setVar( "new_meta", t );
			aioseop_sack.setVar( "target_meta", m );
			aioseop_sack.setVar( "_inline_edit", jQuery('input#_inline_edit').val() );
			aioseop_sack.setVar( "_nonce", n );
			aioseop_sack.onError = function() {alert('Ajax error on saving title'); };
			aioseop_sack.runAJAX();
		})
		jQuery("div#aioseop_"+m+"_"+p).html(loading);
		jQuery("div#aioseop_"+m+"_"+p).attr( "class", "aioseop_mpc_admin_meta_options" );

	})
}
