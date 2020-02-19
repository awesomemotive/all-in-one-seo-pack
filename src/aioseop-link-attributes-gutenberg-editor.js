// jshint ignore: start
/* eslint-disable */

/**
 * Internal dependencies
 */
import Edit from './components/edit';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerFormatType, applyFormat, isCollapsed } = wp.richText;
const { decodeEntities } = wp.htmlEntities;
const { isURL } = wp.url;


/**
 * Block constants
 */
const name = 'aioseop/link';

export const link = {
	name,
	title: __( 'Link', 'all-in-one-seo-pack' ),
	tagName: 'a',
	className: 'aioseop-link',
	attributes: {
		url: 'href',
		target: 'target',
		rel: 'rel',
	},
	__unstablePasteRule( value, { html, plainText } ) {
		if ( isCollapsed( value ) ) {
			return value;
		}

		const pastedText = ( html || plainText ).replace( /<[^>]+>/g, '' ).trim();

		if ( ! isURL( pastedText ) ) {
			return value;
		}

		return applyFormat( value, {
			type: name,
			attributes: {
				url: decodeEntities( pastedText ),
			},
		} );
	},
	edit: Edit,
};

function registerFormats() {
	[
		link,
	].forEach( ( { name, ...settings } ) => {
		if ( name ) {
			registerFormatType( name, settings );
		}
	} );
}

registerFormats();