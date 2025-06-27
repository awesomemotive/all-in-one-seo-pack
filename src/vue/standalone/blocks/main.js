import * as breadcrumbs from './breadcrumbs'
import * as htmlSitemap from './html-sitemap'
import * as businessinfo from './business-info'
import * as openinghours from './opening-hours'
import * as locations from './locations'
import * as locationcategories from './location-categories'
import * as locationmap from './location-map'
import * as tableOfContents from './table-of-contents'
import * as faq from './faq'
import * as keyPoints from './key-points'
import './schema'

import { canLoadBlocks } from '@/vue/utils/context'

if (canLoadBlocks()) {
	const el = window.wp.element.createElement
	const icon = el('svg',
		{
			width   : 25,
			height  : 25,
			viewBox : '0 0 25 25',
			fill    : 'none',
			xmlns   : 'http://www.w3.org/2000/svg'
		},
		el('path',
			{
				fillRule : 'evenodd',
				clipRule : 'evenodd',
				fill     : '#434960',
				d        : 'M12.434 24c6.627 0 12-5.373 12-12s-5.373-12-12-12c-6.628 0-12 5.373-12 12s5.372 12 12 12zM10.523 4.392a.635.635 0 00-.74-.266 8.07 8.07 0 00-.92.391.659.659 0 00-.337.723l.206 1.041a.746.746 0 01-.264.713 6.293 6.293 0 00-.903.924.717.717 0 01-.698.272l-1.02-.206a.638.638 0 00-.708.346 8.493 8.493 0 00-.38.94.66.66 0 00.263.754l.867.588a.745.745 0 01.307.694 6.54 6.54 0 00.003 1.305.744.744 0 01-.305.695l-.865.591a.661.661 0 00-.26.755c.108.321.236.635.383.94a.638.638 0 00.708.343l1.02-.21a.716.716 0 01.7.27c.267.334.57.643.905.92a.746.746 0 01.266.712L8.55 18.67a.659.659 0 00.34.722 8.045 8.045 0 00.92.388c.458.16 1.097-.413 1.57-.836a1.12 1.12 0 00.379-.82v-1.748c0-.018 0-.036.002-.055-1.397-.34-2.435-1.622-2.435-3.152v-1.85c0-.14.111-.254.25-.254h.864v-1.82c0-.251.2-.455.446-.455a.45.45 0 01.446.455v1.82h2.34v-1.82c0-.251.2-.455.446-.455a.45.45 0 01.446.455v1.82h.865c.138 0 .25.114.25.254v1.85c0 1.578-1.105 2.893-2.569 3.182v1.76c0 .322.149.623.389.832.481.42 1.132.988 1.586.827a8.08 8.08 0 00.921-.391.659.659 0 00.336-.723l-.205-1.042a.746.746 0 01.264-.712c.328-.273.63-.582.902-.924a.717.717 0 01.698-.272l1.02.206a.638.638 0 00.708-.346 8.575 8.575 0 00.38-.94.661.661 0 00-.263-.754l-.867-.588a.745.745 0 01-.307-.694 6.481 6.481 0 00-.003-1.305.745.745 0 01.305-.695l.865-.591a.662.662 0 00.26-.755 8.435 8.435 0 00-.383-.94.638.638 0 00-.708-.343l-1.02.21a.716.716 0 01-.7-.27 6.297 6.297 0 00-.904-.92.747.747 0 01-.267-.712l.202-1.042a.659.659 0 00-.339-.722 8.164 8.164 0 00-.921-.388.635.635 0 00-.74.269l-.575.884a.718.718 0 01-.681.314 6.105 6.105 0 00-1.278.003.718.718 0 01-.682-.311l-.58-.883z'
			}
		)
	)

	const {
		getCategories,
		setCategories,
		registerBlockCollection,
		registerBlockType
	} = window.wp.blocks

	const categories = [ ...getCategories() ]
	if ('function' === typeof registerBlockCollection) {
		// If possible, register blocks as a collection under the aioseo namespace.
		// https://make.wordpress.org/core/2020/02/27/block-collections/
		registerBlockCollection('aioseo', {
			title : 'AIOSEO',
			icon
		})
	} else {
		// If we can't do collections create an aioseo category.
		categories.unshift({
			slug  : 'aioseo',
			title : 'AIOSEO',
			icon
		})
		setCategories(categories)
	}

	const registerBlock = (block) => {
		if (!block) {
			return
		}
		const { name, settings } = block

		if (settings.icon && !settings.icon.foreground) {
			const colorIcon = {
				foreground : '#141B38',
				src        : settings.icon
			}
			settings.icon = colorIcon
		}

		// If we're using a Collection for our blocks, we can use the category for something else.
		if ('function' === typeof registerBlockCollection && 'aioseo' === settings.category) {
			settings.category = 'widgets'
		}

		// If Collections aren't supported, ensure all our blocks have the aioseo category.
		if ('function' !== typeof registerBlockCollection && 'aioseo' !== settings.category) {
			settings.category = 'aioseo'
		}

		registerBlockType(name, settings)
	}

	const registerAIOSEOBlocks = () => {
		[
			breadcrumbs,
			htmlSitemap,
			'post' === window?.aioseo?.screen?.base ? tableOfContents : null,
			faq,
			keyPoints

		].forEach(registerBlock)

		// Register Local Business blocks if the addon is active.
		if (window.aioseo.localBusiness) {
			[
				businessinfo,
				openinghours,
				locations,
				locationcategories,
				locationmap

			].forEach(registerBlock)
		}
	}

	registerAIOSEOBlocks()
}