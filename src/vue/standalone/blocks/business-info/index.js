import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { observeElement } from '@/vue/utils/helpers'
import { __, sprintf } from '@wordpress/i18n'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import BusinessInfoSidebar from './BusinessInfoSidebar'

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
const businessInfoSidebarApps = []

export const name = 'aioseo/businessinfo'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title      : __('AIOSEO Local - Business Info', td),
	category   : 'aioseo',
	icon       : icon,
	example    : {},
	attributes : {
		locationId : {
			type    : 'number',
			default : null
		},
		showLabels : {
			type    : 'boolean',
			default : true
		},
		addressLabel : {
			type    : 'string',
			default : __('Address:', td)
		},
		vatIdLabel : {
			type    : 'string',
			default : __('VAT ID:', td)
		},
		taxIdLabel : {
			type    : 'string',
			default : __('Tax ID:', td)
		},
		phoneLabel : {
			type    : 'string',
			default : __('Phone:', td)
		},
		faxLabel : {
			type    : 'string',
			default : __('Fax:', td)
		},
		emailLabel : {
			type    : 'string',
			default : __('Email:', td)
		},
		showIcons : {
			type    : 'boolean',
			default : true
		},
		showName : {
			type    : 'boolean',
			default : true
		},
		showAddress : {
			type    : 'boolean',
			default : true
		},
		showPhone : {
			type    : 'boolean',
			default : true
		},
		showFax : {
			type    : 'boolean',
			default : true
		},
		showCountryCode : {
			type    : 'boolean',
			default : true
		},
		showEmail : {
			type    : 'boolean',
			default : true
		},
		showVat : {
			type    : 'boolean',
			default : true
		},
		showTax : {
			type    : 'boolean',
			default : true
		},
		dataObject : {
			type    : 'string',
			default : null
		},
		updated : {
			type    : 'string',
			default : Date.now()
		}
	},
	edit : withSelect(function (select) {
		const rootStore = useRootStore()
		const locations = select('core').getEntityRecords('postType', rootStore.aioseo.localBusiness.postTypeName, { per_page: 100 })
		return {
			locations : locations
		}
	})(function (props) {
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { setAttributes, attributes, className, clientId, isSelected, toggleSelection } = props
		let { locations } = props
		const vueAioseoId   = 'aioseo-' + clientId

		if (multipleLocations && null === locations) {
			return el(Fragment, {},
				el(
					'div',
					{},
					__('Loading...', td)
				)
			)
		}

		locations = null === locations ? [] : locations

		if (!multipleLocations && attributes.locationId) {
			return el(Fragment, {},
				el(
					'div',
					{},
					__('Please enable multiple locations before using this block.', td)
				)
			)
		}

		const rootStore = useRootStore()
		if (multipleLocations && 0 === locations.length) {
			return el(Fragment, {},
				el(
					'div',
					{},
					sprintf(
						// Translators: 1 - The plural label of the custom post type.
						__('No %1$s found', td),
						rootStore.aioseo.localBusiness.postTypePluralLabel
					)
				)
			)
		}

		// Force locationId if we're in the local-business post type.
		const postEditorStore = usePostEditorStore()
		attributes.locationId = (!attributes.locationId && postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName) ? postEditorStore.currentPost.id : attributes.locationId

		const observeElementArgs = {
			id      : vueAioseoId,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			loop    : false,
			done    : function (node) {
				maybeDeleteBlockVueApp(clientId, businessInfoSidebarApps)

				let app = createApp({
					name : 'Blocks/BusinessInfo',
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
					render : () => h(BusinessInfoSidebar)
				})

				app = loadPlugins(app)

				app.mount(node)

				businessInfoSidebarApps.push({ clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			vueInitialState[clientId] = {}
			Object.keys(attributes).forEach((key) => {
				vueInitialState[clientId][key] = attributes[key]
			})
			vueInitialState[clientId].locations = locations

			observeElement(observeElementArgs)
		}

		const generalSidebarName = wp.data.useSelect(
			select => select('core/edit-post').getActiveGeneralSidebarName()
		)
		if ('edit-post/block' === generalSidebarName) {
			'function' !== typeof toggleSelection || toggleSelection(true)
		}

		if (postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName) {
			observeElement({
				id      : vueAioseoId + '-watcher',
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					let app = createApp({
						name : 'Blocks/BusinessInfoWatcher',
						data : function () {
							return postEditorStore.currentPost.local_seo.locations.business
						},
						watch : {
							$data : {
								handler : function () {
									setAttributes({ updated: Date.now() })
								},
								deep : true
							}
						},
						render : () => h('div') // This stops the watcher from rendering multiple times.
					})

					app = loadPlugins(app)

					app.mount(el)
				}
			})
		}

		const sidebar = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{
					title       : __('Display Settings', td),
					initialOpen : true,
					onToggle    : () => {
						observeElement(observeElementArgs)
					}
				},
				el(
					'div',
					null,
					el(
						'div',
						{ id: vueAioseoId },
						null
					)
				)
			)
		)

		if (multipleLocations && !attributes.locationId) {
			return el(Fragment, {},
				sidebar,
				el(
					'div',
					{},
					sprintf(
						// Translators: 1 - The singular label of the custom post type.
						__('Select a %1$s', td),
						rootStore.aioseo.localBusiness.postTypeSingleLabel
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
								locationId      : attributes.locationId ? attributes.locationId : 0,
								layout          : attributes.layout,
								showLabels      : attributes.showLabels,
								showIcons       : attributes.showIcons,
								showName        : attributes.showName,
								showAddress     : attributes.showAddress,
								showPhone       : attributes.showPhone,
								showFax         : attributes.showFax,
								showCountryCode : attributes.showCountryCode,
								showEmail       : attributes.showEmail,
								showVat         : attributes.showVat,
								showTax         : attributes.showTax,
								addressLabel    : attributes.addressLabel,
								vatIdLabel      : attributes.vatIdLabel,
								taxIdLabel      : attributes.taxIdLabel,
								phoneLabel      : attributes.phoneLabel,
								faxLabel        : attributes.faxLabel,
								emailLabel      : attributes.emailLabel,
								updated         : attributes.updated,
								dataObject      : postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName ? JSON.stringify(postEditorStore.currentPost.local_seo.locations.business) : null
							}
						}
					)
				),
				el(
					'div',
					{},
					el(
						'div',
						{ id: vueAioseoId + '-watcher' },
						null
					)
				)
			)
		)
	}),
	save : function () {
		return null
	}
}