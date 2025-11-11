import { registerBlock } from '../utils'

import { h, createApp } from 'vue'
import { observeElement } from '@/vue/utils/helpers'

import icon from './icon'
import metadata from './block.json'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import loadPlugins from '@/vue/plugins'
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
const locationsSidebarApps = []

export const settings = {
	title,
	description,
	category,
	supports,
	attributes,
	icon,
	edit : withSelect(function (select) {
		const categories = select('core').getEntityRecords('taxonomy', window.aioseo.localBusiness.taxonomyName)
		return {
			categories : categories
		}
	})(function (props) {
		const multipleLocations = window?.aioseo?.options?.localBusiness?.locations?.general?.multiple
		const { setAttributes, attributes, clientId, isSelected, toggleSelection } = props
		let { categories } = props
		const vueAioseoId   = 'aioseo-' + clientId

		const generalSidebarName = wp.data.useSelect(
			select => select('core/edit-post').getActiveGeneralSidebarName()
		)

		if ('edit-post/block' === generalSidebarName) {
			'function' !== typeof toggleSelection || toggleSelection(true)
		}

		if (multipleLocations && null === categories) {
			return (
				<div>{ __('Loading...', td) }</div>
			)
		}

		categories = null === categories ? [] : categories

		if (!multipleLocations) {
			return (
				<div>{ __('Please enable multiple locations before using this block.', td) }</div>
			)
		}

		if (0 === categories.length) {
			return (
				<div>{ sprintf(
					// Translators: 1 - The plural label of the custom post type.
					__('No %1$s found', td),
					window.aioseo.localBusiness.taxonomyPluralLabel
				) }</div>
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

				locationsSidebarApps.push({ id: clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			initialBlockState[clientId] = {}
			Object.keys(attributes).forEach((key) => {
				initialBlockState[clientId][key] = attributes[key]
			})
			initialBlockState[clientId].categories = categories

			observeElement(observeElementArgs)
		}

		if (null !== categories && 0 === categories.length) {
			return (
				<div>{ sprintf(
					// Translators: 1 - The plural label of the custom post type.
					__('No %1$s found', td),
					window.aioseo.localBusiness.taxonomyPluralLabel
				) }</div>
			)
		}

		if (!attributes.categoryId) {
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
						window.aioseo.localBusiness.taxonomySingleLabel
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