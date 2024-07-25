import {
	useRootStore
} from '@/vue/stores'

import { shouldShowMetaBox } from '@/vue/utils/metabox'
import { isBlockEditor } from '@/vue/utils/context'
import { __ } from '@wordpress/i18n'

export default function registerHeadlineAnalyzer () {
	const td = import.meta.env.VITE_TEXTDOMAIN
	if (!isBlockEditor() || !shouldShowMetaBox()) {
		return
	}

	const rootStore = useRootStore()
	if (rootStore.aioseo.registerHeadlineAnalyzerSet) {
		return
	}

	rootStore.aioseo.registerHeadlineAnalyzerSet = true

	const registerPlugin            = window.wp.plugins.registerPlugin
	const PluginSidebarMoreMenuItem = window.wp.editPost.PluginSidebarMoreMenuItem
	const PluginSidebar             = window.wp.editPost.PluginSidebar
	const Fragment                  = window.wp.element.Fragment
	const el                        = window.wp.element.createElement
	const useEffect                 = window.wp.element.useEffect
	// const useState                  = window.wp.element.useState

	const HeadlineAnalyzerIcon = el('svg',
		{
			width   : 24,
			height  : 24,
			fill    : 'none',
			viewBox : '0 0 24 24',
			xmlns   : 'http://www.w3.org/2000/svg'
		},
		el('path',
			{
				d        : 'M15.817 2H22v.038l-6.183 4.226V2.001zm-7.62 9.473V2H2.016v4.683-.267l6.126 5.094.057-.038zm-6.182 5.061l6.183 4.213v1.252H2.015v-5.465zm13.802-.857L22 11.559v10.437h-6.183v-6.32z',
				fillRule : 'evenodd',
				clipRule : 'evenodd',
				fill     : '#F18200'
			}
		),
		el('path',
			{
				d        : 'M8.198 11.435l-.057.039L2.016 6.38v.265-4.644h6.182v9.434zm0 9.226L2.015 16.45v5.55h6.183v-1.337zm7.62-5.07L22 11.474v10.522h-6.183v-6.405zM22 2.001l-6.183 4.226V2H22z',
				fillRule : 'evenodd',
				clipRule : 'evenodd',
				fill     : '#F18200'
			}
		),
		el('path',
			{
				d    : 'M8.141 13.537L22 4.064v5.432l-13.8 9.19L2 14.46l.016-6.018 6.125 5.094z',
				fill : '#F18200'
			}
		)
	)
	const HeadlineAnalyzerButton = el('div',
		{ id: 'aioseo-headline-analyzer-sidebar-button' },
		HeadlineAnalyzerIcon,
		el('span',
			{ id: 'aioseo-headline-analyzer-sidebar-button-score' },
			'/100'
		)
	)

	const user = rootStore.aioseo.user

	registerPlugin('aioseo-headline-analyzer', {
		render : () => {
			if (
				!user.capabilities.aioseo_page_analysis &&
				!user.capabilities.aioseo_page_general_settings &&
				!user.capabilities.aioseo_page_social_settings &&
				!user.capabilities.aioseo_page_schema_settings &&
				!user.capabilities.aioseo_page_advanced_settings
			) {
				return null
			}

			useEffect(() => {

			})

			return el(Fragment, {},
				el(PluginSidebarMoreMenuItem,
					{
						target : 'aioseo-headline-analyzer',
						icon   : HeadlineAnalyzerIcon
					},
					__('SEO Headline Analyzer', td)
				),
				el(PluginSidebar,
					{
						name      : 'aioseo-headline-analyzer',
						icon      : HeadlineAnalyzerButton,
						title     : __('SEO Headline Analyzer', td),
						className : 'aioseo-headline-analyzer-wrapper'
					},
					el('div',
						{ id: 'aioseo-headline-analyzer-sidebar-vue', className: 'aioseo-headline-analyzer-sidebar-vue' }
					)
				)
			)
		}
	})
}