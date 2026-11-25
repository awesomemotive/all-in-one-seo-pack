import { registerBlock, useBlockProps } from '../utils'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import { h, createApp, watch } from 'vue'
import { observeElement } from '@/vue/utils/helpers'

import icon from './icon'
import metadata from './block.json'
import { addQueryArgs } from '@wordpress/url'

import { getBlockLocation, maybeDeleteBlockVueApp, panelBodyMountTarget, useBlockSettingsRemount } from '@/vue/standalone/blocks/utils'

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
	attributes,
	usesContext
} = metadata
export { metadata, name }

const wp                = window.wp
const { useEffect }     = wp.element
const ServerSideRender  = wp.serverSideRender || wp.components.ServerSideRender
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const PanelBody         = wp.components.PanelBody
const Disabled          = wp.components.Disabled
const withSelect        = wp.data.withSelect

const initialBlockState = {}
const locationMapSidebarApps = []

export const settings = {
	title,
	description,
	category,
	supports,
	attributes,
	icon,
	usesContext,
	edit : withSelect(function (select) {
		const rootStore = useRootStore()
		const locations = select('core').getEntityRecords('postType', rootStore.aioseo.localBusiness.postTypeName, { per_page: 100 })
		return {
			locations : locations
		}
	})(function (props) {
		const blockProps = useBlockProps()
		const rootStore         = useRootStore()
		const postEditorStore   = usePostEditorStore()
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { setAttributes, attributes, context, clientId, isSelected, toggleSelection } = props
		let { locations } = props
		useBlockSettingsRemount(toggleSelection)

		const vueAioseoId   = 'aioseo-location-map-' + clientId
		const isLocationPostType = postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName

		// Default dynamic attribute
		attributes.label      = attributes.label || __('Our location:', td)
		attributes.updated    = attributes.updated || Date.now()

		// A block shows its own Location, or the one it renders for. Derived, not saved: the server falls back the same way.
		const { locationId, isCurrentLocation } = getBlockLocation(attributes, context)

		// Snapshot of the location's current settings, so the preview reflects unsaved changes.
		// Deliberately kept out of `attributes` — persisting it would freeze the front-end output.
		const dataObject = isCurrentLocation ? JSON.stringify(postEditorStore.currentPost.local_seo.maps) : null

		// This is a React component, so it is blind to the Pinia mutations the metabox makes. Without
		// a nudge the preview keeps rendering the snapshot taken when the block last rendered.
		const [ , refreshPreview ] = React.useState(0)
		React.useEffect(() => {
			if (!isLocationPostType) {
				return
			}

			return watch(
				() => postEditorStore.currentPost.local_seo.maps,
				() => refreshPreview(count => count + 1),
				{ deep: true }
			)
		}, [isLocationPostType])

		if (multipleLocations && null === locations) {
			return (
				<div {...blockProps}>{ __('Loading...', td) }</div>
			)
		}

		locations = null === locations ? [] : locations

		if (!multipleLocations && attributes.locationId) {
			return (
				<div {...blockProps}>{ __('Please enable multiple locations before using this block.', td) }</div>
			)
		}

		if (multipleLocations && 0 === locations.length) {
			return (
				<div {...blockProps}>{ sprintf(
					// Translators: 1 - The plural label of the custom post type.
					__('No %1$s found', td),
					rootStore.aioseo.localBusiness.postTypePluralLabel
				) }</div>
			)
		}

		const location = locations.find(item => item.id === locationId)
		const locationMap = isCurrentLocation ? postEditorStore.currentPost.local_seo.maps : (location ? location.maps : null)
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
			// A Location the block follows without one saved, e.g. its Query Loop row's.
			initialBlockState[clientId].derivedLocationId = attributes.locationId ? 0 : locationId

			observeElement(observeElementArgs)
		}

		if (multipleLocations) {
			if (!locationId) {
				return (
					<>
						<InspectorControls>
							<PanelBody title={__('Block Settings', td)} initialOpen={true}>
								{panelBodyMountTarget(vueAioseoId)}
							</PanelBody>
						</InspectorControls>
						<div {...blockProps}>{ sprintf(
							// Translators: 1 - The singular label of the custom post type.
							__('Select a %1$s', td),
							rootStore.aioseo.localBusiness.postTypeSingleLabel
						) }</div>
					</>
				)
			}

			if (!locationMap) {
				const editLink = addQueryArgs('post.php', {
					post   : locationId,
					action : 'edit'
				})

				return (
					<>
						<InspectorControls>
							<PanelBody title={__('Block Settings', td)} initialOpen={true}>
								{panelBodyMountTarget(vueAioseoId)}
							</PanelBody>
						</InspectorControls>
						<div {...blockProps} dangerouslySetInnerHTML={{
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
					<PanelBody title={__('Block Settings', td)} initialOpen={true}>
						{panelBodyMountTarget(vueAioseoId)}
					</PanelBody>
				</InspectorControls>
				<div {...blockProps}>
					<div id={`${vueAioseoId}-preview`}>
						<Disabled>
							<ServerSideRender
								block={name}
								attributes={{ ...attributes, locationId, dataObject }}
							/>
						</Disabled>
					</div>
				</div>
			</>
		)
	}),
	save : function () {
		return null
	}
}

if (allowed('aioseo_page_local_seo_settings')) {
	registerBlock({
		name,
		settings
	})
}