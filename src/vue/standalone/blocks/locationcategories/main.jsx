import { registerBlock } from '../utils'

import icon from './icon'
import metadata from './block.json'

import { useRootStore, useOptionsStore } from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const {
	name,
	title,
	description,
	category,
	supports,
	attributes
} = metadata
export { metadata, name }

const wp                = window.wp
const ServerSideRender  = wp.serverSideRender || wp.components.ServerSideRender
const withSelect        = wp.data.withSelect
const Disabled          = wp.components.Disabled

export const settings = {
	title,
	description,
	category,
	supports,
	attributes,
	icon,
	edit : withSelect(function (select) {
		const rootStore  = useRootStore()
		const categories = select('core').getEntityRecords('taxonomy', rootStore.aioseo.localBusiness.taxonomyName)
		return {
			categories : categories
		}
	})(function (props) {
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		let { categories } = props

		if (multipleLocations && null === categories) {
			return <div>{ __('Loading...', td) }</div>
		}

		categories = null === categories ? [] : categories

		if (!multipleLocations) {
			return <div>{ __('Please enable multiple locations before using this block.', td) }</div>
		}

		if (0 === categories.length) {
			const rootStore = useRootStore()
			return <div>{ sprintf(
				// Translators: 1 - The plural label of the custom post type.
				__('No %1$s found', td),
				rootStore.aioseo.localBusiness.taxonomyPluralLabel
			) }</div>
		}

		return (
			<div>
				<Disabled>
					<ServerSideRender
						block={name}
					/>
				</Disabled>
			</div>
		)
	}),
	save : function () {
		return null
	}
}

registerBlock({
	name,
	settings
})