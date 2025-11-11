import { registerBlock } from '../utils'

import { h, createApp } from 'vue'
import { observeElement } from '@/vue/utils/helpers'

import icon from './icon'
import metadata from './block.json'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import loadPlugins from '@/vue/plugins'
import {
	usePostEditorStore,
	useOptionsStore,
	useRootStore,
	loadPiniaStores
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
const withSelect        = wp.data.withSelect

const initialBlockState = {}
const openingHoursSidebarApps = []
const openingHoursSidebarWatcherApps = []

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
	}
	)(function (props) {
		const optionsStore      = useOptionsStore()
		const multipleLocations = optionsStore.options.localBusiness?.locations.general.multiple
		const { setAttributes, attributes, clientId, isSelected, toggleSelection } = props
		let { locations } = props
		const vueAioseoId = `aioseo-${clientId}-settings`

		// Default dynamic attribute
		attributes.label   = attributes.label || __('Our Opening Hours:', td)
		attributes.updated = attributes.updated || Date.now()

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

		const rootStore = useRootStore()
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
		const postEditorStore = usePostEditorStore()
		attributes.locationId = (!attributes.locationId && postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName) ? postEditorStore.currentPost.id : attributes.locationId

		const observeElementArgs = {
			id      : vueAioseoId,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			loop    : false,
			done    : function (node) {
				maybeDeleteBlockVueApp(clientId, openingHoursSidebarApps)

				let app = createApp({
					name : 'Blocks/OpeningHours',
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

				// We need to load the store so we can check the license features.
				loadPiniaStores(app)

				app.mount(node)

				openingHoursSidebarApps.push({ id: clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			initialBlockState[clientId] = {}
			Object.keys(attributes).forEach((key) => {
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

		if (postEditorStore.currentPost.postType === rootStore.aioseo.localBusiness.postTypeName) {
			observeElement({
				id      : vueAioseoId + '-watcher',
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					maybeDeleteBlockVueApp(clientId, openingHoursSidebarWatcherApps)

					let app = createApp({
						name : 'Blocks/OpeningHoursWatcher',
						data : function () {
							return postEditorStore.currentPost.local_seo.openingHours
						},
						watch : {
							$data : {
								handler : function () {
									setAttributes({ updated: Date.now() })
								},
								deep : true
							}
						},
						render : () => h('div')
					})

					app = loadPlugins(app)

					app.mount(el)

					openingHoursSidebarWatcherApps.push({ id: clientId, app })
				}
			})
		}

		if (multipleLocations && !attributes.locationId) {
			return (
				<>
					<InspectorControls>
						<PanelBody title={__('Block Settings', td)} initialOpen={true} onToggle={observeElement(observeElementArgs)}>
							<div id={vueAioseoId}></div>
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

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Block Settings', td)} initialOpen={true} onToggle={observeElement(observeElementArgs)}>
						<div id={vueAioseoId}></div>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender
					block={name}
					attributes={{ ...attributes }}
				/>
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