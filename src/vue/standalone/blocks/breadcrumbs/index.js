import { __ } from '@/vue/plugins/translations'

import { getSelectedTerms } from '@/vue/standalone/primary-term/helpers'
import { useRootStore } from '@/vue/stores'

const wp = window.wp
const el = wp.element.createElement
const Fragment = wp.element.Fragment
const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor.InspectorControls
const PanelBody = wp.components.PanelBody
const Disabled = wp.components.Disabled
const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender
const td = import.meta.env.VITE_TEXTDOMAIN
const icon = el('svg',
	{
		width   : 24,
		height  : 25,
		viewBox : '0 0 24 25',
		xmlns   : 'http://www.w3.org/2000/svg'
	},
	el('path',
		{
			d : 'M1.7002 5.31067H10.8705L17.8705 12.5L10.8705 19.6893H1.7002V5.31067ZM10.2856 12.4999C10.2856 13.3284 9.61396 14.0001 8.7854 14.0001C7.95684 14.0001 7.28516 13.3284 7.28516 12.4999C7.28516 11.6713 7.95684 10.9996 8.7854 10.9996C9.61396 10.9996 10.2856 11.6713 10.2856 12.4999ZM6.1933 12.5001C6.1933 13.195 5.62995 13.7584 4.93503 13.7584C4.2401 13.7584 3.67676 13.195 3.67676 12.5001C3.67676 11.8052 4.2401 11.2418 4.93503 11.2418C5.62995 11.2418 6.1933 11.8052 6.1933 12.5001ZM12.6342 13.7584C13.3292 13.7584 13.8925 13.195 13.8925 12.5001C13.8925 11.8052 13.3292 11.2418 12.6342 11.2418C11.9393 11.2418 11.376 11.8052 11.376 12.5001C11.376 13.195 11.9393 13.7584 12.6342 13.7584ZM15.3002 5.31067H12.5771L19.5771 12.5L12.5771 19.6893H15.3002L22.3002 12.5L15.3002 5.31067Z'
		}
	)
)

const postType = window?.aioseo?.currentPost?.postType ?? ''
let defaultTaxonomy = ''
const findDefaultTaxonomy = window?.aioseo?.postData?.postTypes.find(t => t.name === postType)

if (findDefaultTaxonomy && 0 < findDefaultTaxonomy.taxonomies.length) {
	defaultTaxonomy = findDefaultTaxonomy.taxonomies[0]
}

export const name = 'aioseo/breadcrumbs'
export const settings = {
	// Shortname is static because wp.org reads this for the Blocks description.
	title       : __('AIOSEO - Breadcrumbs', td),
	description : __('Automatically output a breadcrumb trail to help your users and search engines navigate your site.', td),
	category    : 'aioseo',
	icon        : icon,
	example     : {},
	attributes  : {
		primaryTerm : {
			type    : 'string',
			default : null
		},
		breadcrumbSettings : {
			type    : 'object',
			default : {
				default            : window?.aioseo?.currentPost?.breadcrumb_settings?.default ?? true,
				separator          : window?.aioseo?.currentPost?.breadcrumb_settings?.separator ?? '›',
				showHomeCrumb      : window?.aioseo?.currentPost?.breadcrumb_settings?.showHomeCrumb ?? true,
				showTaxonomyCrumbs : window?.aioseo?.currentPost?.breadcrumb_settings?.showTaxonomyCrumbs ?? true,
				showParentCrumbs   : window?.aioseo?.currentPost?.breadcrumb_settings?.showParentCrumbs ?? true,
				template           : window?.aioseo?.currentPost?.breadcrumb_settings?.template ?? 'default',
				parentTemplate     : window?.aioseo?.currentPost?.breadcrumb_settings?.parentTemplate ?? 'default',
				taxonomy           : window?.aioseo?.currentPost?.breadcrumb_settings?.taxonomy || defaultTaxonomy,
				primaryTerm        : window?.aioseo?.currentPost?.breadcrumb_settings?.primaryTerm ?? null
			}
		}
	},
	edit : function (props) {
		const { setAttributes, attributes, clientId } = props
		const rootStore = useRootStore()
		const sidebarSettingsId = `aioseo-${clientId}-settings`

		window.aioseoBus.$on('standalone-update-post', (param) => {
			if (!param?.primary_term && !param?.breadcrumb_settings) {
				return
			}

			if (param?.primary_term) {
				setAttributes({ primaryTerm: JSON.stringify(param.primary_term) })
			}

			if (param?.breadcrumb_settings) {
				setAttributes({ breadcrumbSettings: param.breadcrumb_settings })
			}
		})

		let primaryTerm = attributes.breadcrumbSettings?.primaryTerm
		if (attributes.breadcrumbSettings?.taxonomy) {
			const selectedTerms = getSelectedTerms(attributes.breadcrumbSettings?.taxonomy, true)

			if (selectedTerms[0] && selectedTerms[0] !== primaryTerm) {
				primaryTerm = selectedTerms[0]
			}
		}

		const openBreadcrumbConfig = () => {
			// Open the Advanced tab in the Post Settings metabbox
			window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'advanced', context: 'metabox' })

			// Open the Advanced tab in the Post Settings sidebar
			wp.data.dispatch('core/edit-post').openGeneralSidebar('aioseo-post-settings-sidebar/aioseo-post-settings-sidebar')
			// Wait for the sidebar to open before changing the tab.
			setTimeout(() => {
				window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'advanced', context: 'sidebar' })
			}, 100)
		}

		const sidebar = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{
					title       : __('Breadcrumb Settings', td),
					initialOpen : true
				},
				el(
					'div',
					{},
					el(
						'div',
						{ id: sidebarSettingsId },
						el(
							'p',
							{ className: 'aioseo-breadcrumbs-sidebar-text' },
							[
								__('You can customize your breadcrumb trail under ', td),
								el(
									'a',
									{
										href    : '#',
										onClick : () => openBreadcrumbConfig()
									},
									__('Advanced > Breadcrumbs.', td)
								)
							]
						)
					)
				)
			)
		)

		const user = rootStore.aioseo.user
		return el(Fragment, {},
			user.capabilities.aioseo_page_advanced_settings ? sidebar : null,
			el(
				'div',
				{},
				el(
					window.aioseo.options.breadcrumbs.enable ? Disabled : 'div',
					null,
					el(
						ServerSideRender,
						{
							block      : name,
							attributes : {
								primaryTerm        : attributes.primaryTerm,
								breadcrumbSettings : { ...attributes.breadcrumbSettings, primaryTerm: primaryTerm }
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