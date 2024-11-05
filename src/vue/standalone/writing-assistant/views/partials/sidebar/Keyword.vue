<template>
	<div
		class="aioseo-writing-assistant__sidebar-keyword"
		:class="{
			open : showDetails
		}"
	>
		<div class="aioseo-writing-assistant__sidebar-keyword-usage">
			<svg-check v-if="usageInsideBounds" />
			<svg-check-empty v-if="!usageInsideBounds" />
		</div>
		<div class="aioseo-writing-assistant__sidebar-keyword-element">
			<div
				class="aioseo-writing-assistant__sidebar-keyword-element-upper"
				@click="showDetails = !showDetails"
			>
				<div class="aioseo-writing-assistant__sidebar-keyword-element-upper-text">
					{{ keyword.text }}
				</div>
				<div class="aioseo-writing-assistant__sidebar-keyword-element-upper-open-details">
					<svg-right-arrow-simple />
				</div>
			</div>
			<div
				class="aioseo-writing-assistant__sidebar-keyword-element-bottom"
				:class="{'aioseo-writing-assistant__sidebar-keyword-element-bottom-small': 100 <= keyword.higherCount && 100 <= keyword.contentCount}"
			>
				<div style="display: flex; align-items: center">
					<span class="aioseo-writing-assistant__sidebar-keyword-element-bottom-label">{{ strings.uses }}:</span>
					<keyword-uses :keyword="keyword" />
				</div>
				<keyword-heading-presence
					:keyword="keyword"
					:heading-presence-string="strings.heading"
					v-show="!showDetails"
				/>
			</div>
			<div
				class="aioseo-writing-assistant__sidebar-keyword-element-bottom-details"
				v-if="showDetails"
			>
				<div>
					<span class="aioseo-writing-assistant__sidebar-keyword-element-bottom-details-label">{{ strings.importance }}:</span>
					<keyword-importance :keyword="keyword" />
				</div>
				<div>
					<span class="aioseo-writing-assistant__sidebar-keyword-element-bottom-details-label">{{ strings.headingPresence }}:</span>
					<keyword-heading-presence :keyword="keyword" />
				</div>
				<div class="aioseo-writing-assistant__sidebar-keyword-separator"></div>
				<div
					class="aioseo-writing-assistant__sidebar-keyword-similar"
					v-if="keyword.similar"
				>
					{{ keyword.similar.join(', ') }}
				</div>
				<div
					class="aioseo-writing-assistant__sidebar-keyword-click-examples"
					v-if="keyword.phrases"
					@click="showExamples = !showExamples"
				>
					<svg-examples /> {{ strings.seeExamples }}
				</div>

				<core-modal
					:show="showExamples"
					@close="showExamples = false"
				>
					<template #headerTitle>
						"{{ keyword.text }}" {{ strings.usageExamples }}
					</template>
					<template #body>
						<keyword-examples :keyword="keyword" />
					</template>
				</core-modal>
			</div>
		</div>
		<div
			class="aioseo-writing-assistant__sidebar-keyword-usage-bar"
			:style="{width: usageWidth+'%'}"
			v-show="!showDetails"
		></div>
	</div>
</template>

<script>
import SvgCheck from '@/vue/components/common/svg/circle/Check'
import SvgCheckEmpty from '@/vue/components/common/svg/circle/Empty'
import SvgRightArrowSimple from '@/vue/components/common/svg/right-arrow/Simple'
import SvgExamples from '@/vue/components/common/svg/link/External'
import KeywordUses from '@/vue/standalone/writing-assistant/views/partials/keyword/Uses'
import KeywordHeadingPresence from '@/vue/standalone/writing-assistant/views/partials/keyword/HeadingPresence'
import KeywordImportance from '@/vue/standalone/writing-assistant/views/partials/keyword/Importance'
import KeywordExamples from '@/vue/standalone/writing-assistant/views/partials/keyword/Examples'
import CoreModal from '@/vue/components/common/core/modal/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		SvgCheck,
		SvgCheckEmpty,
		SvgRightArrowSimple,
		SvgExamples,
		KeywordUses,
		KeywordHeadingPresence,
		KeywordImportance,
		KeywordExamples,
		CoreModal
	},
	props : {
		keyword : Object
	},
	data () {
		return {
			strings : {
				uses            : __('Uses', td),
				heading         : __('Heading', td),
				importance      : __('Importance', td),
				headingPresence : __('Heading Presence', td),
				seeExamples     : __('See Examples', td),
				usageExamples   : __('Usage Examples', td)
			},
			showDetails  : false,
			showExamples : false
		}
	},
	computed : {
		usageInsideBounds () {
			if (this.keyword?.contentCount && 0 < this.keyword.contentCount) {
				return this.keyword.contentCount >= this.keyword.lowerCount
			}

			return false
		},
		usageWidth () {
			if (this.keyword?.contentCount) {
				if (this.keyword?.lowerCount) {
					return Math.min(100, this.keyword?.contentCount * 100 / this.keyword?.lowerCount)
				}
			}

			return 0
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__sidebar {
	&-keyword {
		--letter-spacing: 3px;
		background: $white;
		border-radius: 3px;
		display: flex;
		position: relative;
		padding: 10px 6px;
		gap: 8px;

		&.open {
			.aioseo-writing-assistant__sidebar-keyword-element-upper-open-details {
				svg {
					transform: rotate(90deg);
				}
			}
		}

		&-element {
			flex-grow: 1;

			&-upper {
				display: flex;
				padding-bottom: 2px;
				cursor: pointer;

				&-text {
					font-weight: 700;
					font-size: 14px;
					flex-grow: 1;
				}

				&-open-details {
					justify-self: flex-end;
					padding: 0 0 0 20px;

					.aioseo-right-arrow-simple {
						width: 7px;
						height: 10px;
						color: $placeholder-color;
					}
				}
			}

			&-bottom, &-uses {
				display: flex;
				font-size: 13px;
				flex-grow: 1;
				justify-content: space-between;
				align-items: center;

				&-label {
					margin-right: var(--letter-spacing);
				}

				&-details {
					font-size: 13px;

					&-label {
						margin-right: var(--letter-spacing);
					}
				}

				&-small {
					font-size: 12px;
				}
			}
		}

		&-usage {
			padding-top: 2px;

			svg {
				width: 14px;
				height: 14px;
				color: $gray2;

				&.aioseo-circle-check {
					color: $green;
				}
			}
		}

		&-usage-bar {
			position: absolute;
			bottom: 0;
			left: 0;
			height: 2px;
			background-color: $green;
			border-radius: 0 0 3px 3px;
		}

		&-separator {
			margin: 10px 0;
			height: 1px;
			background-color: $input-border;
		}

		&-similar {
			font-size: 14px;
			padding: 0 0 8px;
		}

		&-click-examples {
			display: flex;
			align-items: center;
			color: $blue;
			cursor: pointer;

			svg {
				width: 12px;
				height: 12px;
				margin-right: 9px;
			}
		}
	}
}
</style>