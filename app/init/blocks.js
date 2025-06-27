/**
 * Since we dynamically load our blocks, wordpress.org cannot pick them up properly.
 * This file solely exists to let WordPress know what blocks we are currently using.
 *
 * @since 4.2.4
 */

/* eslint-disable no-undef */

registerBlockType('aioseo/breadcrumbs', {
	title : 'AIOSEO - Breadcrumbs'
})
registerBlockType('aioseo/html-sitemap', {
	title : 'AIOSEO - HTML Sitemap'
})
registerBlockType('aioseo/faq', {
	title : 'AIOSEO - FAQ with JSON Schema'
})
registerBlockType('aioseo/table-of-contents', {
	title : 'AIOSEO - Table of Contents'
})
registerBlockType('aioseo/businessinfo', {
	title : 'AIOSEO - Local Business Info'
})
registerBlockType('aioseo/locationcategories', {
	title : 'AIOSEO - Local Business Location Categories'
})
registerBlockType('aioseo/locations', {
	title : 'AIOSEO - Local Business Locations'
})
registerBlockType('aioseo/locationmap', {
	title : 'AIOSEO - Local Business Google Map'
})
registerBlockType('aioseo/openinghours', {
	title : 'AIOSEO - Local Business Opening Hours'
})
registerBlockType('aioseo/openinghours', {
	title : 'AIOSEO - Author Bio (E-E-A-T)'
})
registerBlockType('aioseo/openinghours', {
	title : 'AIOSEO - Author Name (E-E-A-T)'
})
registerBlockType('aioseo/openinghours', {
	title : 'AIOSEO - Reviewer Name (E-E-A-T)'
})
registerBlockType('aioseo/key-points', {
	title : 'AIOSEO - Key Points (TLDR)'
})