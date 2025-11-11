import { registerBlock } from '../utils'

import { h, createApp } from 'vue'
import { observeElement } from '@/vue/utils/helpers'

import icon from './icon'
import metadata from './block.json'
import { addQueryArgs } from '@wordpress/url'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import loadPlugins from '@/vue/plugins'
import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'
import SidebarOptions from './vue/SidebarOptions'

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
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const PanelBody         = wp.components.PanelBody
const Disabled          = wp.components.Disabled
const withSelect        = wp.data.withSelect

const initialBlockState = {}
const locationMapSidebarApps = []
const locationMapSidebarWatcherApps = []

export const settings = {
	title,
	description,
	category,
	supports,
	attributes,
	icon,
	edit : withSelect(function (select) {
		const rootStore = useRootStore()
		const locations = select('core').getEntityRecords('postType', rootStore.aioseo.localBusiness.postTypeName, { per_page: 100 })
		return {
			locations : locations
		}
	})(function (props) {
		const rootStore         = useRootStore()
		const postEditorStore   = usePostEditorStore()
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { setAttributes, attributes, className, clientId, isSelected, toggleSelection } = props
		let { locations } = props
		const vueAioseoId   = 'aioseo-location-map-' + clientId
		const isLocationPostType = postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName

		// Default dynamic attribute
		attributes.label      = attributes.label || __('Our location:', td)
		attributes.updated    = attributes.updated || Date.now()
		attributes.dataObject = attributes.dataObject || isLocationPostType ? JSON.stringify(postEditorStore.currentPost.local_seo.maps) : null

		if (multipleLocations && null === locations) {
			return (
				<div>{ __('Loading...', td) }</div>
			)
		}

		locations = null === locations ? [] : locations

		if (!multipleLocations && attributes.locationId) {
			return (
				<div>{ __('Please enable multiple locations before using this block.', td) }</div>
			)
		}

		if (multipleLocations && 0 === locations.length) {
			return (
				<div>{ sprintf(
					// Translators: 1 - The plural label of the custom post type.
					__('No %1$s found', td),
					rootStore.aioseo.localBusiness.postTypePluralLabel
				) }</div>
			)
		}

		// Force locationId if we're in the local-business post type.
		attributes.locationId = (!attributes.locationId && isLocationPostType) ? postEditorStore.currentPost.id : attributes.locationId
		const location = locations.find(item => item.id === attributes.locationId)
		const locationMap = isLocationPostType ? postEditorStore.currentPost.local_seo.maps : (location ? location.maps : null)
		const observeElementArgs = {
			id      : vueAioseoId,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			loop    : false,
			done    : function (node) {
				maybeDeleteBlockVueApp(clientId, locationMapSidebarApps)

				let app = createApp({
					name : 'Blocks/LocationMap',
					data : function () {
						return initialBlockState[clientId]
					},
					watch : {
						$data : {
							handler : function (val) {
								setAttributes(val)
							},
							deep : true
						}
					},
					render : () => h(SidebarOptions)
				})

				app = loadPlugins(app)

				app.mount(node)

				locationMapSidebarApps.push({ id: clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			initialBlockState[clientId] = {}
			Object.keys(attributes).forEach(function (key) {
				initialBlockState[clientId][key] = attributes[key]
			})
			initialBlockState[clientId].locations = locations

			observeElement(observeElementArgs)
		}

		const generalSidebarName = wp.data.useSelect(
			select => select('core/edit-post').getActiveGeneralSidebarName()
		)
		if ('edit-post/block' === generalSidebarName) {
			'function' !== typeof toggleSelection || toggleSelection(true)
		}

		if (isLocationPostType) {
			observeElement({
				id      : vueAioseoId + '-watcher',
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					maybeDeleteBlockVueApp(clientId, locationMapSidebarWatcherApps)

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

					locationMapSidebarWatcherApps.push({ id: clientId, app })
				}
			})
		}

		if (multipleLocations) {
			if (!attributes.locationId) {
				return (
					<>
						<InspectorControls>
							<PanelBody title={__('Block Settings', td)} initialOpen={true} onToggle={observeElement(observeElementArgs)}>
								<div className={className} id={vueAioseoId}></div>
							</PanelBody>
						</InspectorControls>
						<div>{ sprintf(
							// Translators: 1 - The singular label of the custom post type.
							__('Select a %1$s', td),
							rootStore.aioseo.localBusiness.postTypeSingleLabel
						) }</div>
					</>
				)
			}

			if (!locationMap) {
				const editLink = addQueryArgs('post.php', {
					post   : attributes.locationId,
					action : 'edit'
				})

				return (
					<>
						<InspectorControls>
							<PanelBody title={__('Block Settings', td)} initialOpen={true} onToggle={observeElement(observeElementArgs)}>
								<div className={className} id={vueAioseoId}></div>
							</PanelBody>
						</InspectorControls>
						<div dangerouslySetInnerHTML={{
							__html : sprintf(
								// Translators: 1 - The title of the location.
								__('Please configure the map for this location: %1$s', td),
								'<a href="' + editLink + '" target="_blank">' + location.title.rendered + '</a>'
							)
						}}></div>
					</>
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

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Block Settings', td)} initialOpen={true} onToggle={observeElement(observeElementArgs)}>
						<div className={className} id={vueAioseoId}></div>
					</PanelBody>
				</InspectorControls>
				<div>
					<div className={className} id={`${vueAioseoId}-preview`}>
						<Disabled>
							<ServerSideRender
								block={name}
								attributes={{ ...attributes }}
							/>
						</Disabled>
					</div>
					<div id={`${vueAioseoId}-watcher`}></div>
				</div>
			</>
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