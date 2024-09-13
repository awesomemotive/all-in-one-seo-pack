import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import LocationsSidebar from './LocationsSidebar'

import { observeElement } from '@/vue/utils/helpers'
import { __, sprintf } from '@/vue/plugins/translations'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

const wp = window.wp
const el = wp.element.createElement
const Fragment = wp.element.Fragment
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const PanelBody = wp.components.PanelBody
const Disabled = wp.components.Disabled
const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender
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

const vueInitialState = {}
const locationsSidebarApps = []

export const name = 'aioseo/locations'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title      : __('AIOSEO Local - Locations', td),
	category   : 'aioseo',
	icon       : icon,
	example    : {},
	attributes : {
		categoryId : {
			type    : 'number',
			default : null
		}
	},
	edit : withSelect(function (select) {
		const rootStore  = useRootStore()
		const categories = select('core').getEntityRecords('taxonomy', rootStore.aioseo.localBusiness.taxonomyName)
		return {
			categories : categories
		}
	})(function (props) {
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { setAttributes, attributes, className, clientId, isSelected, toggleSelection } = props
		let { categories } = props
		const vueAioseoId   = 'aioseo-' + clientId

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

		const rootStore = useRootStore()
		if (0 === categories.length) {
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

		const observeElementArgs = {
			id      : vueAioseoId,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			loop    : false,
			done    : function (node) {
				maybeDeleteBlockVueApp(clientId, locationsSidebarApps)

				let app = createApp({
					name : 'Blocks/Locations',
					data : function () {
						return vueInitialState[clientId]
					},
					watch : {
						$data : {
							handler : function (val) {
								setAttributes(val)
							},
							deep : true
						}
					},
					render : () => h(LocationsSidebar)
				})

				app = loadPlugins(app)

				app.mount(node)

				locationsSidebarApps.push({ clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			vueInitialState[clientId] = {}
			Object.keys(attributes).forEach((key) => {
				vueInitialState[clientId][key] = attributes[key]
			})
			vueInitialState[clientId].categories = categories

			observeElement(observeElementArgs)
		}

		const generalSidebarName = wp.data.useSelect(
			select => select('core/edit-post').getActiveGeneralSidebarName()
		)
		if ('edit-post/block' === generalSidebarName) {
			'function' !== typeof toggleSelection || toggleSelection(true)
		}

		const sidebar = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{
					title       : rootStore.aioseo.localBusiness.postTypePluralLabel,
					initialOpen : true,
					onToggle    : () => {
						observeElement(observeElementArgs)
					}
				},
				el(
					'div',
					{},
					el(
						'div',
						{ id: vueAioseoId },
						null
					)
				)
			)
		)

		if (null !== categories && 0 === categories.length) {
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

		if (!attributes.categoryId) {
			return el(Fragment, {},
				sidebar,
				el(
					'div',
					{},
					sprintf(
						// Translators: 1 - The singular label of the custom post type.
						__('Select a %1$s', td),
						rootStore.aioseo.localBusiness.taxonomySingleLabel
					)
				)
			)
		}

		return el(Fragment, {},
			sidebar,
			el(
				'div',
				{ className: className },
				el(
					Disabled,
					null,
					el(
						ServerSideRender,
						{
							block      : name,
							attributes : {
								categoryId : attributes.categoryId
							}
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