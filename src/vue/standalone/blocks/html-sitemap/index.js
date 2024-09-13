import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import { observeElement } from '@/vue/utils/helpers'
import { __ } from '@/vue/plugins/translations'

import { maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import HtmlSitemapSidebar from './HtmlSitemapSidebar'

const wp = window.wp
const el = wp.element.createElement
const Fragment = wp.element.Fragment
const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const PanelBody = wp.components.PanelBody
const Disabled = wp.components.Disabled
const td = import.meta.env.VITE_TEXTDOMAIN
const icon = el('svg',
	{
		width   : 25,
		height  : 25,
		viewBox : '0 0 25 25',
		xmlns   : 'http://www.w3.org/2000/svg'
	},
	el('path',
		{
			d        : 'M4.125 4.5H20.125V20.5H4.125V4.5ZM9.71875 6.89062H17.7188V8.49062H9.71875V6.89062ZM17.7188 10.0781H9.71875V11.6781H17.7188V10.0781ZM9.71875 13.2661H17.7188V14.8661H9.71875V13.2661ZM17.7188 16.5H9.71875V18.1H17.7188V16.5ZM6.51562 6.89062H8.11562V8.49062H6.51562V6.89062ZM8.11562 10.0781H6.51562V11.6781H8.11562V10.0781ZM6.51562 13.2661H8.11562V14.8661H6.51562V13.2661ZM8.11562 16.5H6.51562V18.1H8.11562V16.5Z',
			fillRule : 'evenodd',
			clipRule : 'evenodd'
		}
	)
)
const vueInitialState = {}
const htmlSitemapSidebarApps = []

export const name = 'aioseo/html-sitemap'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title      : __('AIOSEO - HTML Sitemap', td),
	category   : 'aioseo',
	icon       : icon,
	example    : {},
	attributes : {
		default : {
			type    : 'boolean',
			default : true
		},
		post_types : {
			type    : 'string',
			default : '["post", "page"]'
		},
		post_types_all : {
			type    : 'boolean',
			default : true
		},
		taxonomies : {
			type    : 'string',
			default : '["category", "post_tag"]'
		},
		taxonomies_all : {
			type    : 'boolean',
			default : true
		},
		show_label : {
			type    : 'boolean',
			default : true
		},
		archives : {
			type    : 'boolean',
			default : false
		},
		publication_date : {
			type    : 'boolean',
			default : true
		},
		nofollow_links : {
			type    : 'boolean',
			default : false
		},
		order_by : {
			type    : 'string',
			default : 'publish_date'
		},
		order : {
			type    : 'string',
			default : 'asc'
		},
		excluded_posts : {
			type    : 'string',
			default : '[]'
		},
		excluded_terms : {
			type    : 'string',
			default : '[]'
		},
		is_admin : {
			type    : 'boolean',
			default : window.location && window.location.pathname.startsWith('/wp-admin/')
		}
	},
	edit : function (props) {
		const { setAttributes, attributes, className, clientId, isSelected, toggleSelection } = props
		const vueAioseoHtmlSitemapID = 'aioseo-' + clientId
		const observeElementArgs = {
			id      : vueAioseoHtmlSitemapID,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			loop    : false,
			done    : function (node) {
				maybeDeleteBlockVueApp(clientId, htmlSitemapSidebarApps)

				let app = createApp({
					name : 'Blocks/HtmlSitemap',
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
					render : () => h(HtmlSitemapSidebar)
				})

				app = loadPlugins(app)
				app = loadComponents(app)
				app = loadVersionedComponents(app)

				loadPiniaStores(app)

				app.mount(node)

				htmlSitemapSidebarApps.push({ clientId, app })
			}
		}

		if (isSelected) {
			// Refresh the initial state object.
			vueInitialState[clientId] = {}
			Object.keys(attributes).forEach((key) => {
				vueInitialState[clientId][key] = attributes[key]
			})

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
						{ id: vueAioseoHtmlSitemapID },
						null
					)
				)
			)
		)

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
								...attributes
							}
						}
					)
				)
			)
		)
	},
	save : function () {
		return null
	}
}