import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import LocationMapSidebar from './LocationMapSidebar.vue'

import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { observeElement } from '@/vue/utils/helpers'
import { __, sprintf } from '@wordpress/i18n'
import { addQueryArgs } from '@wordpress/url'

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
		height  : 20,
		viewBox : '0 0 20 20',
		xmlns   : 'http://www.w3.org/2000/svg'
	},
	el('path',
		{
			d : 'M11 11.14L9.83 5.2C9.04 4.77 8.5 3.95 8.5 3C8.5 1.62 9.62 0.499999 11 0.499999C12.38 0.499999 13.5 1.62 13.5 3C13.5 3.95 12.96 4.77 12.17 5.2L11 11.14ZM11 1.5C10.17 1.5 9.5 2.17 9.5 3C9.5 3.83 10.17 4.5 11 4.5C11.83 4.5 12.5 3.83 12.5 3C12.5 2.17 11.83 1.5 11 1.5ZM12.72 6.3L11 13.68L9.27 6.38L5 4.97L2.98023e-08 6.97V15.97L5 13.97L11.12 16L16 13.97V4.97L12.72 6.3Z'
		}
	)
)

const vueInitialState = {}
const locationMapSidebarApps = []

export const name = 'aioseo/locationmap'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title      : __('AIOSEO Local - Map', td),
	category   : 'aioseo',
	icon       : icon,
	example    : {},
	attributes : {
		locationId : {
			type    : 'number',
			default : null
		},
		showLabel : {
			type    : 'boolean',
			default : true
		},
		showIcon : {
			type    : 'boolean',
			default : true
		},
		customMarker : {
			type    : 'string',
			default : null
		},
		width : {
			type    : 'string',
			default : '100%'
		},
		height : {
			type    : 'string',
			default : '450px'
		},
		label : {
			type    : 'string',
			default : __('Our location:', td)
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
		const { setAttributes, attributes, className, clientId, isSelected } = props
		let { locations } = props
		const vueAioseoId   = 'aioseo-location-map-' + clientId

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

		const postEditorStore    = usePostEditorStore()
		const isLocationPostType = postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName

		// Force locationId if we're in the local-business post type.
		attributes.locationId = (!attributes.locationId && isLocationPostType) ? postEditorStore.currentPost.id : attributes.locationId
		const location = locations.find(item => item.id === attributes.locationId)
		const locationMap = isLocationPostType ? postEditorStore.currentPost.local_seo.maps : (location ? location.maps : null)

		if (isSelected) {
			// Refresh the initial state object.
			vueInitialState[clientId] = {}
			Object.keys(attributes).forEach(function (key) {
				vueInitialState[clientId][key] = attributes[key]
			})
			vueInitialState[clientId].locations = locations

			observeElement({
				id      : vueAioseoId,
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					const findAppIndex = locationMapSidebarApps.findIndex(item => item._container.id === el.id)
					if (-1 !== findAppIndex) {
						// Prevent Vue from creating multiple instances of the same app.
						locationMapSidebarApps[findAppIndex].unmount()
						locationMapSidebarApps.splice(findAppIndex, 1)
					}

					let app = createApp({
						name : 'Blocks/LocationMap',
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
						render : () => h(LocationMapSidebar)
					})

					app = loadPlugins(app)

					app.mount(el)

					locationMapSidebarApps.push(app)
				}
			})
		}

		if (isLocationPostType) {
			observeElement({
				id      : vueAioseoId + '-watcher',
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					let app = createApp({
						name : 'Blocks/LocationMapWatcher',
						data : function () {
							return postEditorStore.currentPost.local_seo.maps
						},
						watch : {
							$data : {
								handler : function () {
									setAttributes({
										updated : Date.now()
									})
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
				{ title: __('Settings', td), opened: true },
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

		if (multipleLocations) {
			if (!attributes.locationId) {
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
			if (!locationMap) {
				const editLink = addQueryArgs('post.php', {
					post   : attributes.locationId,
					action : 'edit'
				})
				return el(Fragment, {},
					sidebar,
					el(
						'div',
						{
							dangerouslySetInnerHTML : {
								__html : sprintf(
									// Translators: 1 - The title of the location.
									__('Please configure the map for this location: %1$s', td),
									'<a href="' + editLink + '" target="_blank">' + location.title.rendered + '</a>'
								)
							}
						}
					)
				)
			}
		}

		const renderedMapDiv = '#' + vueAioseoId + '-preview .aioseo-local-map'
		observeElement({
			selector : renderedMapDiv,
			parent   : document.querySelector('.block-editor'),
			subtree  : true,
			done     : function () {
				const mapToRender = locationMap || optionsStore.options.localBusiness.maps
				// Debounce functions don't work here.
				setTimeout(function () {
					document.dispatchEvent(new CustomEvent(rootStore.aioseo.localBusiness.mapLoadEvent, {
						detail : {
							element           : renderedMapDiv,
							mapOptions        : mapToRender.mapOptions,
							customMarker      : attributes.customMarker || mapToRender.customMarker || optionsStore.options.localBusiness.maps.customMarker,
							instance          : attributes,
							placeId           : optionsStore.options.localBusiness.maps.mapsEmbedApiEnabled ? mapToRender.placeId : null,
							infoWindowContent : mapToRender.infoWindowContent ? mapToRender.infoWindowContent : null
						}
					}))
				}, 2000)
			}
		})

		return el(Fragment, {},
			sidebar,
			el(
				'div',
				{ className: className, id: vueAioseoId + '-preview' },
				el(
					Disabled,
					null,
					el(
						ServerSideRender,
						{
							block      : 'aioseo/locationmap',
							attributes : {
								locationId   : attributes.locationId ? attributes.locationId : 0,
								showLabel    : attributes.showLabel,
								showIcon     : attributes.showIcon,
								customMarker : attributes.customMarker,
								width        : attributes.width,
								height       : attributes.height,
								label        : attributes.label,
								updated      : attributes.updated,
								dataObject   : isLocationPostType ? JSON.stringify(postEditorStore.currentPost.local_seo.maps) : null
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