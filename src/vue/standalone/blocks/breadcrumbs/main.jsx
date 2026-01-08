import { getSelectedTerms } from '@/vue/standalone/primary-term/helpers'
import { createApp } from 'vue'
import {
	useRootStore,
	usePostEditorStore,
	loadPiniaStores
} from '@/vue/stores'
import { registerBlock } from '../utils'
import { Sidebar } from './Sidebar'

import icon from './icon'
import metadata from './block.json'

const wp = window.wp
const { useEffect, useRef } = wp.element
const { useSelect } = wp.data
const { InspectorControls } = wp.blockEditor || wp.editor
const { Disabled } = wp.components
const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender

const postType = window?.aioseo?.currentPost?.postType ?? ''
let defaultTaxonomy = ''
const findDefaultTaxonomy = window?.aioseo?.postData?.postTypes.find(t => t.name === postType)

if (findDefaultTaxonomy && 0 < findDefaultTaxonomy.taxonomies.length) {
	defaultTaxonomy = findDefaultTaxonomy.taxonomies[0]
}

const {
	name,
	title,
	description,
	category
} = metadata
export { metadata, name }

export const settings = {
	title,
	description,
	category,
	icon,
	attributes : {
		primaryTerm : {
			type    : 'string',
			default : null
		},
		postTitle : {
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
		const postEditorStore = usePostEditorStore()
		const blockRef = useRef(null)
		const hasRenderedRef = useRef(false)

		const postTitle = useSelect((select) =>
			select('core/editor').getEditedPostAttribute('title')
		)

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

		const user = rootStore.aioseo.user

		const syncAttributesWithStore = () => {
			const currentBreadcrumbSettings = postEditorStore?.currentPost?.breadcrumb_settings
			if (currentBreadcrumbSettings) {
				// Deep compare to avoid unnecessary updates
				const currentAttributes = JSON.stringify(attributes.breadcrumbSettings)
				const storeSettings = JSON.stringify(currentBreadcrumbSettings)

				if (currentAttributes !== storeSettings) {
					setAttributes({ breadcrumbSettings: currentBreadcrumbSettings })
				}
			}
		}

		const onBlockFirstRender = () => {
			if (hasRenderedRef.current) {
				return
			}

			hasRenderedRef.current = true
			const breadcrumbElement = blockRef.current?.querySelector('.aioseo-breadcrumbs')
			if (breadcrumbElement) {
				syncAttributesWithStore()
			}
		}

		useEffect(() => {
			setTimeout(() => {
				if (postTitle !== attributes.postTitle) {
					setAttributes({ postTitle })
				}
			}, 1000)
		}, [ postTitle ])

		useEffect(() => {
			// Watch for ServerSideRender content to asppear
			if (!blockRef.current) {
				return
			}

			const observer = new MutationObserver((mutations) => {
				for (const mutation of mutations) {
					// Check if ServerSideRender content was added
					if ('childList' === mutation.type && 0 < mutation.addedNodes.length) {
						const serverRenderElement = blockRef.current?.querySelector('.aioseo-breadcrumbs')

						// Check if content actually exists (not just loading state)
						if (serverRenderElement && serverRenderElement.innerHTML.trim() &&
							!serverRenderElement.innerHTML.includes('Loading...') &&
							!serverRenderElement.querySelector('.components-spinner')) {
							onBlockFirstRender()
							observer.disconnect()
							break
						}
					}
				}
			})

			observer.observe(blockRef.current, {
				childList : true,
				subtree   : true
			})

			// Cleanup observer on unmount
			return () => observer.disconnect()
		}, [])

		return (
			<>
				{user.capabilities.aioseo_page_advanced_settings && (
					<InspectorControls>
						<Sidebar clientId={clientId} />
					</InspectorControls>
				)}
				<div ref={blockRef}>
					{window.aioseo.options.breadcrumbs.enable
						? (
							<Disabled>
								<ServerSideRender
									block={name}
									attributes={{
										primaryTerm        : attributes.primaryTerm,
										postTitle          : attributes.postTitle,
										breadcrumbSettings : { ...attributes.breadcrumbSettings, primaryTerm }
									}}
								/>
							</Disabled>
						)
						: (
							<ServerSideRender
								block={name}
								attributes={{
									primaryTerm        : attributes.primaryTerm,
									postTitle          : attributes.postTitle,
									breadcrumbSettings : { ...attributes.breadcrumbSettings, primaryTerm }
								}}
							/>
						)}
				</div>
			</>
		)
	},
	save : function () {
		return null
	}
}

const app = createApp({})
loadPiniaStores(app)
registerBlock({
	name,
	settings
})