import {
	useRootStore
} from '@/vue/stores'

import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleInfo from '@/vue/components/common/svg/circle/Information'
// If DOMPurify is available in your project, import it. Otherwise, use a simple fallback.
// import DOMPurify from 'dompurify'
import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const rootStore = useRootStore()

export const CHART_COLORS = {
	good     : '#00AA63',
	warnings : '#F18200',
	issues   : '#DF2A4A'
}

export const TAB_TYPES = {
	error   : 'error',
	warning : 'warning',
	passed  : 'passed'
}

function getChartParts (auditType, hasLink = true) {
	const tab = 'homepage' === auditType ? 'seo-audit-checklist' : 'seo-site-audit'

	return {
		good : {
			name  : __('Good', td),
			color : CHART_COLORS.good,
			link  : hasLink ? `${rootStore.aioseo.urls.aio.seoAnalysis}#/${tab}?tab=passed` : null
		},
		warnings : {
			name  : __('Warnings', td),
			color : CHART_COLORS.warnings,
			link  : hasLink ? `${rootStore.aioseo.urls.aio.seoAnalysis}#/${tab}?tab=warning` : null
		},
		issues : {
			name  : __('Issues', td),
			color : CHART_COLORS.issues,
			link  : hasLink ? `${rootStore.aioseo.urls.aio.seoAnalysis}#/${tab}?tab=error` : null
		}
	}
}

export function getSortedParts (counts, auditType = 'site', hasLink = true) {
	if (!counts || 'object' !== typeof counts) {
		return []
	}

	const chartParts = getChartParts(auditType, hasLink)
	const keys = Object.keys(chartParts)
	const charts = []
	keys.forEach((key, index) => {
		const count = parseInt(counts[key]) || 0
		const total = parseInt(counts.total) || 0
		const part = {
			...chartParts[key],
			count,
			ratio : 0 === index ? 100 : 0 < total ? (count / total) * 100 : 0
		}
		if (0 < count) {
			charts.push(part)
		}
	})
	// Calculate cumulative ratios
	charts.forEach((chart, index) => {
		if (0 === index) return chart
		charts.forEach((chart2, index2) => {
			if (index < index2) {
				chart.ratio += chart2.ratio
			}
		})
		return chart
	})
	return charts
}

export function getAuditTabs (counts) {
	return [
		{
			slug    : TAB_TYPES.error,
			label   : __('Issues', td),
			name    : __('Issues', td),
			icon    : SvgCircleClose,
			analyze : {
				classColor : 'red',
				count      : counts.error
			}
		},
		{
			slug    : TAB_TYPES.warning,
			label   : __('Warnings', td),
			name    : __('Warnings', td),
			icon    : SvgCircleInfo,
			analyze : {
				classColor : 'orange',
				count      : counts.warning
			}
		},
		{
			slug    : TAB_TYPES.passed,
			label   : __('Good', td),
			name    : __('Good', td),
			icon    : SvgCircleCheck,
			analyze : {
				classColor : 'green',
				count      : counts.good
			}
		}
	]
}

export function safeParseJSON (json) {
	try {
		return JSON.parse(json)
	} catch (e) {
		console.error('JSON parse error:', e)
		return null
	}
}