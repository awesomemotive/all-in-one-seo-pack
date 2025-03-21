<template>
	<button
		type="button"
		v-if="truSeoHighlighterStore.enabled"
		class="tru-seo-toggle-highlighter"
		:disabled="!truSeoHighlighterStore.allowHighlighting"
		@click.stop.exact="onClickBtnToggleHighlighter()"
	>
		<core-tooltip
			v-if="truSeoHighlighterStore.highlightAnalyzer !== analyzer"
			:offset="'sidebar' === $root.$data.screenContext ? '-200px,0' : ''"
			type="action"
		>
			<svg-eye
				width="16"
				height="16"
			/>

			<template #tooltip>
				{{ truSeoHighlighterStore.allowHighlighting ? strings.highlightSections : strings.highlightingIsDisabled }}
			</template>
		</core-tooltip>

		<template v-else>
			<svg-eye-filled
				width="16"
				height="16"
			/>
		</template>
	</button>
</template>

<script>
import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgEye from '@/vue/components/common/svg/Eye'
import SvgEyeFilled from '@/vue/components/common/svg/EyeFilled'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			truSeoHighlighterStore : useTruSeoHighlighterStore()
		}
	},
	components : {
		CoreTooltip,
		SvgEye,
		SvgEyeFilled
	},
	props : {
		analyzer : String
	},
	data () {
		return {
			strings : {
				highlightSections      : __('Highlight sections in the Editor', td),
				highlightingIsDisabled : __('Highlighting is disabled for current view', td)
			}
		}
	},
	methods : {
		onClickBtnToggleHighlighter () {
			this.truSeoHighlighterStore.toggleHighlightAnalyzer(this.analyzer)
		}
	}
}
</script>

<style lang="scss">
.tru-seo-toggle-highlighter {
	background: transparent;
	border: none;
	box-shadow: none;
	cursor: pointer;
	opacity: 1;
	outline-color: $blue;
	outline-offset: 1px;
	outline-width: 1px;
	padding: 0;

	&:disabled {
		cursor: not-allowed;

		svg {
			opacity: 0.5;
		}
	}
}

mark.annotation-text.annotation-text-aioseo-tru-seo-highlighter,
span.annotation-text.annotation-text-aioseo-tru-seo-highlighter {
	background-color: #cce0ff;
	color: inherit;
	display: inline;
	font-size: inherit;
	font-weight: inherit;
	letter-spacing: inherit;
	line-height: inherit;
	position: static;
}

.is-annotated-by-aioseo-tru-seo-highlighter {
	background-color: #cce0ff;
	outline: 2px solid #cce0ff;
}
</style>