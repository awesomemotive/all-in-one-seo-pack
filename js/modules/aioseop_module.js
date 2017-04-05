/**
 * Controls all the styling of the plugin.
 *
 * AIOSEOP Updates class.
 * @author Michael Torbert.
 * @author Semper Fi Web Design.
 * @copyright https://semperplugins.com
 * @version 1.0.0
 */
if ( typeof aiosp_data != 'undefined' ) {

	/**
	 * @since 1.0.0
	 * @param int $index.
	 * @param $value
	 */
	jQuery.each( aiosp_data, function( index, value ) {
//		aiosp_data[index] = value.json.replace(/&quot;/g, '"');
//		aiosp_data[index] = jQuery.parseJSON( value );
		if ( index == 0 ) {
			if ( typeof value.condshow == 'undefined' ) {
				aiosp_data[ index ].condshow = [];
			}
		} else {
			if ( typeof value.condshow != 'undefined' ) {
				aiosp_data[ 0 ].condshow =
					jQuery.merge( aiosp_data[0].condshow, value.condshow );
			}
		}
	});
	aiosp_data = aiosp_data[0];
}

/**
 * @summary Changes visibility.
 *
 * @since 1.0.0
 * @param int $id.
 */
function toggleVisibility( id ) {
	var e = document.getElementById( id );
	if ( e.style.display == 'block' )
		e.style.display = 'none';
	else
		e.style.display = 'block';
}

/**
 * @summary Counts characters.
 *
 * @since 1.0.0
 * @param String $field.
 * @param Int $cntfield.
 * @return Mixed.
 */
function countChars( field, cntfield ) {
	var extra = 0;
	var field_size;
	if ( ( field.name == 'aiosp_title' )
		&& ( typeof aiosp_title_extra !== 'undefined' ) ) {
		extra = aiosp_title_extra;
	}
	cntfield.value = field.value.length + extra;
	if ( typeof field.size != 'undefined' ) {
		field_size = field.size;
	} else {
		field_size = field.rows * field.cols;
	}
	if ( field_size < 10 )
		return;
	if ( cntfield.value > field_size ) {
		cntfield.style.color = "#fff";
		cntfield.style.backgroundColor = "#f00";
	} else {
		if ( cntfield.value > ( field_size - 6 ) ) {
			cntfield.style.color = "#515151";
			cntfield.style.backgroundColor = "#ff0";
		} else {
			cntfield.style.color = "#515151";
			cntfield.style.backgroundColor = "#eee";
		}
	}
}

/**
 * @summary Returns the fields value.
 *
 * @since 1.0.0
 * @param String $field.
 * @return Mixed.
 */
function aioseop_get_field_value( field ) {
	if ( field.length == 0 )
		return field;
	cur = jQuery('[name=' + field + ']');
	if ( cur.length == 0 )
		return field;
	type = cur.attr('type');
	if ( type == "checkbox" || type == "radio" )
		cur = jQuery('input[name=' + field + ']:checked');
	return cur.val();
}

/**
 * @summary Returns the fields value.
 *
 * @since 1.0.0
 * @param String $field.
 * @return Mixed.
 */
function aioseop_get_field_values( field ) {
	arr = [];
	cur = jQuery( '[name=' + field + ']' );
	if ( cur.length == 0 )
		return field;
	type = cur.attr('type');
	if ( type == "checkbox" || type == "radio" )
		jQuery( 'input[name=' + field + ']:checked' ).each(function() {
			arr.push(jQuery(this).val());
		});
	if ( arr.length <= 0 )
		arr.push(cur.val());
	return arr;
}

/**
 * @summary Evaluates condshow logic.
 *
 * @since 1.0.0
 * @param String $statement.
 * @return Mixed.
 */
function aioseop_eval_condshow_logic( statement ) {
	var lhs, rhs;
	if ( ( typeof statement ) == 'object' ) {
		lhs = statement['lhs'];
		rhs = statement['rhs'];
		if ( lhs !== null && ( ( typeof lhs ) == 'object' ) )
			lhs = aioseop_eval_condshow_logic( statement['lhs'] );
		if ( rhs !== null && ( typeof rhs ) == 'object' )
			rhs = aioseop_eval_condshow_logic( statement['rhs'] );
		lhs = aioseop_get_field_value( lhs );
		rhs = aioseop_get_field_value( rhs );
		switch ( statement['op'] ) {
			case 'NOT':
				return ( ! lhs );
			case 'AND':
				return ( lhs && rhs );
			case 'OR' :
				return ( lhs || rhs );
			case '==' :
				return ( lhs == rhs );
			case '!=' :
				return ( lhs != rhs );
			default   :
				return null;
		}
	}
	return statement;
}

/**
 * @summary Evaluates condshow logic.
 *
 * @since 1.0.0
 * @param String $index.
 * @param $value.
 * @return Mixed.
 */
function aioseop_do_condshow_match( index, value ) {
	if ( typeof value != 'undefined' ) {
		matches = true;
		jQuery.each( value, function(subopt, setting) {
			var statement;
			if ( ( typeof setting ) == 'object' ) {
				statement = aioseop_eval_condshow_logic( setting );
				if ( ! statement ) {
					matches = false;
				}
			} else {
				if ( subopt.match( /\\\[\\\]/ ) ) { // special case for these -- pdb
					cur = aioseop_get_field_values( subopt );
					if ( jQuery.inArray( setting, cur, 0 ) < 0 ) {
						matches = false;
					}
				} else {
					cur = aioseop_get_field_value( subopt );
					if ( cur != setting ) {
						matches = false;
					}
				}
			}
		});
		if ( matches ) {
			jQuery( '#' + index + '_wrapper' ).show();
		} else {
			jQuery( '#' + index + '_wrapper' ).hide();
		}
		return matches;
	}
	return false;
}

/**
 * @summary Adds condshow handlers.
 *
 * @since 1.0.0
 * @param String $index.
 * @param $value.
 */
function aioseop_add_condshow_handlers( index, value ) {
	if ( typeof value != 'undefined' ) {
		jQuery.each(value, function(subopt, setting) {
			jQuery('[name=' + subopt + ']').bind( "change keyup", function() {
				aioseop_do_condshow_match( index, value );
			});
		});
	}
}

/**
 * @summary Does condshow.
 *
 * @since 1.0.0
 * @param $condshow.
 */
function aioseop_do_condshow( condshow ) {
	if ( typeof aiosp_data.condshow != 'undefined' ) {
		jQuery.each( aiosp_data.condshow, function( index, value ) {
			aioseop_do_condshow_match( index, value );
			aioseop_add_condshow_handlers( index, value );
		});
	}
}

/**
 * @since 1.0.0
 */
jQuery( document ).ready(function() {
	if ( typeof aiosp_data != 'undefined' ) {
		if ( typeof aiosp_data.condshow != 'undefined' ) {
			aioseop_do_condshow( aiosp_data.condshow );
		}
	}
});

/**
 * @summary Custom jQuery plugin that enables image uploader in wordpress.
 * 
 * @since 2.3.13
 * @see http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
 */
jQuery.fn.aioseopImageUploader = function() {
    // Keep reference to this.
    var self = this;

    // Set input target when to update image url value
    self.target = jQuery( self ).next();

    // Uploader per image button
    // * Having only one uploader was causing problems when multiple image buttons where in place
    self.uploader = wp.media({
        title: 'Choose Image',
        button: {
            text: 'Choose Image'
        },
        multiple: false
    });

    /**
     * Event handler that will be called when an image is selected from media uploader.
     */
    self.onSelect = function() {
        if ( self.target.length >= 0 )
            jQuery( self.target ).val(
                self.uploader.state().get( 'selection' ).first().toJSON().url
            );
    }

    /**
     * Click event handler.
     * @param object e Click event.
     */
    self.onClick = function( e ) {
        e.preventDefault();
        self.uploader.open();
    }

    //Set uploader select handler
    self.uploader.on( 'select', self.onSelect );

    // Set click handler
    jQuery( self ).click( self.onClick );
};

/**
 * @summary Javascript for using WP media uploader. Indentifies which DOM should use custom uploader plugin.
 *
 * @see http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
 * @since ?
 * @since 2.3.11.2 Use WP 3.5 new media uploader
 * @since 2.3.13 Fixed issue #[740](https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/740)
 *
 */
jQuery(document).ready(function($){

    jQuery( '.aioseop_upload_image_button' ).each(function() {
        jQuery( this ).aioseopImageUploader();
    });

});

/**
 * @summary workaround for bug that causes radio inputs to lose settings when meta box is dragged.
 *
 * props to commentluv for this fix
 * @author commentluv.
 * @link https://core.trac.wordpress.org/ticket/16972
 * @since 1.0.0
 */
jQuery( document ).ready(function() {

	// listen for drag drop of metaboxes , bind mousedown to .hndle so it only fires when starting to drag
    jQuery( '.hndle' ).mousedown(function() {

		// set live event listener for mouse up on the content .wrap and wait a tick to give the dragged div time to settle before firing the reclick function
        jQuery( '.wrap').mouseup(function() {
			aiosp_store_radio();
			setTimeout( 'aiosp_reclick_radio();', 50 );
		});
    })
});

/**
 * @summary Stores object of all radio buttons that are checked for entire form.
 *
 * @since 1.0.0
 */
function aiosp_store_radio() {
    var radioshack = {};
    jQuery( 'input[type="radio"]' ).each(function() {
        if( jQuery( this ).is( ':checked' ) ) {
            radioshack[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
        }
        jQuery( document ).data( 'radioshack', radioshack );
    });
}

/**
 * @summary Detects mouseup and restore all radio buttons that were checked.
 *
 * @since 1.0.0
 */
function aiosp_reclick_radio() {

	// gets the object of checked radio button names and values
    var radios = jQuery( document ).data( 'radioshack' );

	//steps thru each object element and trigger a click on it's corresponding radio button
    for( key in radios ) {
        jQuery( 'input[name="' + key + '"]' )
			.filter( '[value="' + radios[ key ]+ '"]' )
			.trigger( 'click' );
    }
    // unbinds the event listener on .wrap  (prevents clicks on inputs from triggering function)
    jQuery( '.wrap' ).unbind( 'mouseup' );
}

/**
 * @summary Handdles ajax call.
 *
 * @since 1.0.0
 * @param $action.
 * @param $setting.
 * @param $options.
 * @param $success.
 */
function aioseop_handle_ajax_call( action, settings, options, success ) {
	var aioseop_sack = new sack( ajaxurl );
	aioseop_sack.execute = 1;
	aioseop_sack.method = 'POST';
	aioseop_sack.setVar( "action", action );
	aioseop_sack.setVar( "settings", settings );
	aioseop_sack.setVar( "options", options );
	if ( typeof success != 'undefined' ) {
		aioseop_sack.onCompletion = success;
	}
	aioseop_sack.setVar(
			"nonce-aioseop",
			jQuery( 'input[name="nonce-aioseop"]' ).val()
		);
	aioseop_sack.setVar(
		"nonce-aioseop-edit",
		jQuery( 'input[name="nonce-aioseop-edit"]' ).val()
	);
	aioseop_sack.onError = function() {
		alert( 'Ajax error on saving.' );
	};
	aioseop_sack.runAJAX();
}

/**
 * @summary Handdles posts URL.
 *
 * @since 1.0.0
 * @param $action.
 * @param $setting.
 * @param $options.
 * @param $success.
 */
function aioseop_handle_post_url( action, settings, options, success) {
	jQuery("div#aiosp_"+settings).fadeOut('fast', function() {
		var loading = '<label class="aioseop_loading aioseop_' + settings + '_loading"></label> Please wait...';
		jQuery( "div#aiosp_"+ settings ).fadeIn( 'fast', function() {
			aioseop_handle_ajax_call( action, settings, options, success);
		});
		jQuery( "div#aiosp_" + settings ).html( loading );
	})
};

/**
 * @summary Handles when AIOSEOP is overflowed.
 *
 * @since 1.0.0
 * @param $element.
 * @return mixed.
 */
function aioseop_is_overflowed( element ) {
    return element.scrollHeight > element.clientHeight || element.scrollWidth > element.clientWidth;
}

/**
 * @summary Handles when overflowed border.
 *
 * @since 1.0.0
 * @param $el.
 */
function aioseop_overflow_border( el ) {
	if ( aioseop_is_overflowed( el ) ) {
		el.className = 'aioseop_option_div aioseop_overflowed';
	} else {
		el.className = 'aioseop_option_div';
	}
}

/**
 * @since 1.0.0
 * @return mixed.
 */
jQuery( document ).ready(function() {
	jQuery( "#poststuff .aioseop_radio_type input[type='radio']" ).on( 'click', function() {
		var previousValue = jQuery( this ).attr( 'previousValue' );
		var name = jQuery( this ).attr( 'name' );
		if ( typeof previousValue == 'undefined' ) {
			if ( jQuery( this ).prop( "checked" ) ) {
				jQuery( this ).prop( 'checked', true );
				jQuery( this ).attr( 'previousValue', 'checked' );
			} else {
				jQuery( this ).prop( 'checked', false );
				jQuery( this ).attr( 'previousValue', false );
			}
			return;
		}
		if ( previousValue == 'checked' ) {
			jQuery( this ).prop( 'checked', false );
			jQuery( this ).attr( 'previousValue', false );
		} else {
			jQuery( "input[name=" + name + "]:radio" )
				.attr( 'previousValue', false );
			jQuery( this ).attr( 'previousValue', 'checked' );
		}
	});
	if ( typeof aiosp_data.pointers != 'undefined' ) {

		/**
		 * @since 1.0.0
		 * @param $index.
		 * @param $value.
		 * @return mixed.
		 */
		jQuery.each( aiosp_data.pointers, function( index, value ) {
			if ( value != 'undefined' && value.pointer_text != '' ) {
				aioseop_show_pointer( index, value );
			}
		});
	}

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( ".all-in-one-seo_page_all-in-one-seo-pack-modules-aioseop_feature_manager #aiosp_settings_form .aioseop_settings_left" )
		.delegate("input[name='Submit']", "click", function( e ) {
		e.preventDefault();
		return false;
	});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( ".all-in-one-seo_page_all-in-one-seo-pack-modules-aioseop_feature_manager #aiosp_settings_form" )
		.delegate( "input[name='Submit']", "click", function( e ) {
		e.preventDefault();
		aioseop_handle_post_url(
			'aioseop_ajax_save_settings',
			'ajax_settings_message',
			jQuery( 'form#aiosp_settings_form' ).serialize(),
			function() {
				jQuery( '.wp-has-current-submenu' ).fadeIn( 'fast', function() {
					aioseop_handle_ajax_call(
						'aioseop_ajax_get_menu_links',
						'ajax_settings_message',
						jQuery.param( {target: '.wp-has-current-submenu > ul'} )
					);
				});
			} );
		return false;
	});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( ".all-in-one-seo_page_all-in-one-seo-pack-pro-modules-aioseop_feature_manager #aiosp_settings_form .aioseop_settings_left" )
		.delegate("input[name='Submit']", "click", function( e ) {
			e.preventDefault();
			return false;
		});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( ".all-in-one-seo_page_all-in-one-seo-pack-pro-modules-aioseop_feature_manager #aiosp_settings_form" )
		.delegate( "input[name='Submit']", "click", function( e ) {
			e.preventDefault();
			aioseop_handle_post_url(
				'aioseop_ajax_save_settings',
				'ajax_settings_message',
				jQuery( 'form#aiosp_settings_form' ).serialize(),
				function() {
					jQuery( '.wp-has-current-submenu' ).fadeIn( 'fast', function() {
						aioseop_handle_ajax_call(
							'aioseop_ajax_get_menu_links',
							'ajax_settings_message',
							jQuery.param( {target: '.wp-has-current-submenu > ul'} )
						);
					});
				} );
			return false;
		});

	var selectors =
		"div.aioseop_multicheckbox_type div.aioseop_option_div, #aiosp_sitemap_debug div.aioseop_option_div, #aiosp_performance_status div.aioseop_option_div";

	/**
	 * @since 1.0.0
	 * @return boolean.
	 */
	jQuery( "div#aiosp_sitemap_addl_pages_metabox" )
		.delegate( "input[name='Submit']", "click", function() {
		aioseop_handle_post_url(
			'aioseop_ajax_save_url',
			'sitemap_addl_pages',
			jQuery( 'div#aiosp_sitemap_addl_pages_metabox input, div#aiosp_sitemap_addl_pages_metabox select' )
				.serialize()
		);
		return false;
	});

	/**
	 * @since 1.0.0
	 * @return boolean.
	 */
	jQuery(	"div#aiosp_video_sitemap_addl_pages_metabox" )
		.delegate( "input[name='Submit']", "click", function() {
		aioseop_handle_post_url(
			'aioseop_ajax_save_url',
			'video_sitemap_addl_pages',
			jQuery( 'div#aiosp_video_sitemap_addl_pages_metabox input, div#aiosp_video_sitemap_addl_pages_metabox select')
				.serialize()
		);
		return false;
	});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( "div#aiosp_sitemap_addl_pages_metabox" )
		.delegate("a.aiosp_delete_url", "click", function( e ) {
		e.preventDefault();
		aioseop_handle_post_url(
			'aioseop_ajax_delete_url',
			'sitemap_addl_pages',
			jQuery( this ).attr( "title" )
		);
		return false;
	});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery( "div#aiosp_video_sitemap_addl_pages_metabox" )
		.delegate( "a.aiosp_delete_url", "click", function( e ) {
		e.preventDefault();
		aioseop_handle_post_url(
			'aioseop_ajax_delete_url',
			'video_sitemap_addl_pages',
			jQuery( this ).attr( "title" )
		);
		return false;
	});

	/**
	 * @since 1.0.0
	 * @param $e.
	 * @return boolean.
	 */
	jQuery(	"div#aiosp_opengraph_scan_header" )
		.delegate( "input[name='aiosp_opengraph_scan_header']", "click", function( e ) {
		e.preventDefault();
		aioseop_handle_post_url(
			'aioseop_ajax_scan_header',
			'opengraph_scan_header',
			jQuery( 'div#aiosp_opengraph_scan_header' ).serialize()
		);
		return false;
	});

	/**
	 * @since 1.0.0
	 */
	jQuery(	'input[name="aiosp_sitemap_posttypes[]"][value="all"], input[name="aiosp_video_sitemap_posttypes[]"][value="all"], input[name="aiosp_sitemap_taxonomies[]"][value="all"], input[name="aiosp_video_sitemap_taxonomies[]"][value="all"]' )
		.click(function() {
		jQuery( this )
			.parents( 'div:eq(0)' )
			.find( ':checkbox' )
			.prop( 'checked', this.checked );
    });

	/**
	 * @since 1.0.0
	 */
	jQuery( 'input[name="aiosp_sitemap_posttypes[]"][value!="all"], input[name="aiosp_video_sitemap_posttypes[]"][value!="all"], input[name="aiosp_sitemap_taxonomies[]"][value!="all"], input[name="aiosp_video_sitemap_taxonomies[]"][value!="all"]' )
		.click(function () {
		if ( ! this.checked )
			jQuery( this )
				.parents( 'div:eq(0)' )
				.find( 'input[value="all"]:checkbox' )
				.prop( 'checked', this.checked );
    });

	/**
	 * @since 1.0.0
	 */
	jQuery( ".aioseop_tab:not(:first)" ).hide();

	/**
	 * @since 1.0.0
	 */
    jQuery( ".aioseop_tab:first" ).show();

	/**
	 * @since 1.0.0
	 * @return boolean.
	 */
	jQuery( "a.aioseop_header_tab" ).click(function() {
            var stringref = jQuery( this ).attr( "href" ).split( '#' )[1];
            jQuery( '.aioseop_tab:not(#' + stringref + ')' ).hide( 'slow' );
            jQuery( '.aioseop_tab#' + stringref ).show( 'slow' );
            jQuery( '.aioseop_header_tab[href!="#'+ stringref +'"]' ).removeClass( 'active' );
            jQuery( '.aioseop_header_tab[href="#' + stringref +'"]' ).addClass( 'active' );
            return false;
    });
});
