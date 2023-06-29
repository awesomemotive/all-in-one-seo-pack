import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@wordpress/i18n'

const wp = window.wp
const el = wp.element.createElement
const Fragment = wp.element.Fragment
const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender
const Disabled = wp.components.Disabled
const withSelect = wp.data.withSelect
const td = import.meta.env.VITE_TEXTDOMAIN
const icon = el('svg',
	{
		width   : 20,
		height  : 19,
		viewBox : '0 0 20 19',
		xmlns   : 'http://www.w3.org/2000/svg'
	},
	el('path',
		{
			d : 'M17.2001 7.2L19.0361 9.036L17.7641 10.308L10.0001 2.544L2.23611 10.308L0.964111 9.036L10.0001 0L14.8001 4.8V2.4H17.2001V7.2ZM10.0001 4.248L17.2001 11.436V18.6H2.80011V11.436L10.0001 4.248ZM12.4001 17.4V11.4H7.60011V17.4H12.4001Z'
		}
	)
)

export const name = 'aioseo/locationcategories'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title    : __('AIOSEO Local - Location Categories', td),
	category : 'aioseo',
	icon     : icon,
	example  : {},
	edit     : withSelect(function (select) {
		const rootStore  = useRootStore()
		const categories = select('core').getEntityRecords('taxonomy', rootStore.aioseo.localBusiness.taxonomyName)
		return {
			categories : categories
		}
	})(function (props) {
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { className } = props
		let { categories } = props

		if (multipleLocations && null === categories) {
			return el(Fragment, {},
				el(
					'div',
					{},
					__('Loading...', td)
				)
			)
		}

		categories = null === categories ? [] : categories

		if (!multipleLocations) {
			return el(Fragment, {},
				el(
					'div',
					{},
					__('Please enable multiple locations before using this block.', td)
				)
			)
		}

		if (0 === categories.length) {
			const rootStore = useRootStore()
			return el(Fragment, {},
				el(
					'div',
					{},
					sprintf(
						// Translators: 1 - The plural label of the custom post type.
						__('No %1$s found', td),
						rootStore.aioseo.localBusiness.taxonomyPluralLabel
					)
				)
			)
		}

		return el(Fragment, {},
			el(
				'div',
				{ className: className },
				el(
					Disabled,
					null,
					el(
						ServerSideRender,
						{
							block : name
						}
					)
				)
			)
		)
	}),
	save : function () {
		return null
	}
}