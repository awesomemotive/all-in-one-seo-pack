import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { shouldShowMetaBox } from '@/vue/utils/metabox'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import { isBlockEditor } from '@/vue/utils/context'
import { __ } from '@wordpress/i18n'

export default function registerScoreToggler () {
	const td = import.meta.env.VITE_TEXTDOMAIN
	if (!isBlockEditor() || !shouldShowMetaBox()) {
		return
	}

	const rootStore = useRootStore()
	if (rootStore.aioseo.registerScoreTogglerSet) {
		return
	}

	rootStore.aioseo.registerScoreTogglerSet = true

	const registerPlugin            = window.wp.plugins.registerPlugin
	const PluginSidebarMoreMenuItem = window.wp.editPost.PluginSidebarMoreMenuItem
	const PluginSidebar             = window.wp.editPost.PluginSidebar
	const Fragment                  = window.wp.element.Fragment
	const el                        = window.wp.element.createElement
	const analysisCapability        = rootStore.aioseo.user.capabilities.aioseo_page_analysis
	const postEditorStore           = usePostEditorStore()
	const score                     = postEditorStore.currentPost.seo_score
	const naString                  = __('N/A', td)
	const scoreClass                = function (score) {
		if (!analysisCapability || !truSeoShouldAnalyze()) {
			return 'score-disabled'
		}
		return 80 < score ? 'score-green' : (50 < score ? 'score-orange' : (1 < score ? 'score-red' : 'score-disabled'))
	}

	const AioseoIcon = el('svg',
		{
			width   : 24,
			height  : 24,
			fill    : 'none',
			viewBox : '0 0 24 24',
			xmlns   : 'http://www.w3.org/2000/svg'
		},
		el('path',
			{
				d        : 'M11.9811 23.7877C18.5428 23.7877 23.8623 18.4684 23.8623 11.9066C23.8623 5.34477 18.5428 0.0253906 11.9811 0.0253906C5.41924 0.0253906 0.0998535 5.34477 0.0998535 11.9066C0.0998535 18.4684 5.41924 23.7877 11.9811 23.7877ZM10.0892 4.37389C9.92824 4.12859 9.6301 4.01391 9.35674 4.11048C9.04535 4.22048 8.74079 4.34987 8.44488 4.49781C8.18513 4.6277 8.05479 4.92439 8.11199 5.21372L8.31571 6.24468C8.36815 6.51003 8.25986 6.77935 8.0543 6.95044C7.72937 7.22084 7.42944 7.52654 7.16069 7.86489C6.99366 8.07521 6.73011 8.18668 6.46987 8.13409L5.45923 7.92995C5.17534 7.87259 4.88492 8.00678 4.75864 8.27251C4.68731 8.42264 4.61997 8.57591 4.55683 8.73224C4.49369 8.88855 4.43564 9.04574 4.38258 9.20355C4.28872 9.4829 4.40211 9.78694 4.64318 9.95035L5.50129 10.5321C5.72226 10.6819 5.8323 10.9505 5.80561 11.2198C5.76265 11.6532 5.76441 12.0857 5.80825 12.5112C5.83598 12.7804 5.72684 13.0494 5.5064 13.2L4.64996 13.785C4.40958 13.9493 4.29718 14.2535 4.3918 14.5324C4.49961 14.8502 4.62641 15.1609 4.7714 15.4629C4.89868 15.728 5.18943 15.8609 5.47301 15.8026L6.48336 15.5947C6.7434 15.5412 7.00735 15.6517 7.17499 15.8615C7.43997 16.193 7.73956 16.499 8.07114 16.7733C8.27723 16.9437 8.38649 17.2127 8.33498 17.4782L8.13487 18.5095C8.07868 18.7992 8.2102 19.0955 8.47059 19.2244C8.61773 19.2971 8.76793 19.3659 8.92112 19.4303C9.07434 19.4947 9.22835 19.5539 9.38302 19.6081C9.83552 19.7664 10.4688 19.1996 10.937 18.7805C11.1679 18.5738 11.3103 18.2813 11.3119 17.9682C11.3119 17.9665 11.3119 17.9648 11.3119 17.9632V16.2386C11.3119 16.2204 11.3125 16.2022 11.3139 16.1843C9.93098 15.847 8.90283 14.5775 8.90283 13.0629V11.2317C8.90283 11.0925 9.01342 10.9797 9.14984 10.9797H10.0064V9.17798C10.0064 8.92921 10.204 8.72754 10.4478 8.72754C10.6916 8.72754 10.8892 8.92921 10.8892 9.17798V10.9797H13.2067V9.17798C13.2067 8.92921 13.4043 8.72754 13.6481 8.72754C13.8919 8.72754 14.0895 8.92921 14.0895 9.17798V10.9797H14.9461C15.0825 10.9797 15.193 11.0925 15.193 11.2317V13.0629C15.193 14.6253 14.0989 15.927 12.6497 16.2135C12.6501 16.2218 12.6502 16.2302 12.6502 16.2386V17.9557C12.6502 18.275 12.7969 18.5727 13.0347 18.7801C13.5113 19.1958 14.1555 19.7576 14.6053 19.5987C14.9167 19.4887 15.2213 19.3593 15.5172 19.2113C15.7769 19.0814 15.9073 18.7848 15.8501 18.4954L15.6464 17.4644C15.5939 17.1991 15.7022 16.9298 15.9078 16.7587C16.2327 16.4883 16.5326 16.1826 16.8013 15.8442C16.9684 15.634 17.2319 15.5225 17.4922 15.575L18.5028 15.7792C18.7867 15.8366 19.0771 15.7024 19.2034 15.4366C19.2747 15.2865 19.3421 15.1333 19.4052 14.9769C19.4683 14.8206 19.5264 14.6634 19.5795 14.5056C19.6733 14.2263 19.5599 13.9222 19.3189 13.7588L18.4607 13.177C18.2398 13.0272 18.1297 12.7586 18.1564 12.4893C18.1994 12.056 18.1976 11.6234 18.1538 11.1979C18.1261 10.9287 18.2352 10.6598 18.4556 10.5092L19.3121 9.92409C19.5525 9.75989 19.6649 9.45566 19.5702 9.17674C19.4624 8.85899 19.3356 8.5482 19.1907 8.24628C19.0634 7.98121 18.7726 7.84823 18.489 7.90657L17.4787 8.11444C17.2187 8.16796 16.9547 8.05746 16.7871 7.84769C16.5221 7.51615 16.2225 7.2101 15.8909 6.93588C15.6848 6.76543 15.5756 6.49649 15.6271 6.23094L15.8272 5.19968C15.8834 4.90999 15.7519 4.61365 15.4914 4.48481C15.3443 4.412 15.1941 4.34331 15.0409 4.27886C14.8877 4.21444 14.7337 4.1552 14.579 4.10107C14.3053 4.00526 14.0073 4.12099 13.8472 4.36697L13.277 5.24259C13.1302 5.46808 12.867 5.58035 12.6031 5.55312C12.1784 5.5093 11.7545 5.51109 11.3375 5.55581C11.0737 5.58411 10.8101 5.47276 10.6625 5.24782L10.0892 4.37389Z',
				fillRule : 'evenodd',
				clipRule : 'evenodd',
				fill     : '#005AE0'
			}
		)
	)
	const AioseoButton = el('div',
		{ id: 'aioseo-post-settings-sidebar-button', className: scoreClass(score) },
		AioseoIcon,
		el('span',
			{ id: 'aioseo-post-score-disabled' },
			naString
		),
		el('span',
			{ id: 'aioseo-post-score' },
			score
		),
		el('span',
			{},
			'/100'
		)
	)
	const user = rootStore.aioseo.user

	registerPlugin('aioseo-post-settings-sidebar', {
		render : function () {
			if (
				!user.capabilities.aioseo_page_analysis &&
				!user.capabilities.aioseo_page_general_settings &&
				!user.capabilities.aioseo_page_social_settings &&
				!user.capabilities.aioseo_page_schema_settings &&
				!user.capabilities.aioseo_page_advanced_settings
			) {
				return null
			}
			return el(Fragment, {},
				el(PluginSidebarMoreMenuItem,
					{
						target : 'aioseo-post-settings-sidebar',
						icon   : AioseoIcon
					},
					'AIOSEO'
				),
				el(PluginSidebar,
					{
						name  : 'aioseo-post-settings-sidebar',
						icon  : AioseoButton,
						title : 'AIOSEO'
					},
					el('div',
						{ id: 'aioseo-post-settings-sidebar', className: 'aioseo-post-settings-sidebar' },
						el('div',
							{ id: 'aioseo-post-settings-sidebar-vue', className: 'aioseo-post-settings-sidebar-vue' },
							el('div',
								{
									className : 'aioseo-loading-spinner dark',
									style     : {
										left   : 0,
										right  : 0,
										margin : '30px auto'
									}
								},
								el('div',
									{ className: 'double-bounce1' },
									null
								),
								el('div',
									{ className: 'double-bounce2' },
									null
								)
							)
						)
					)
				)
			)
		}
	})
}