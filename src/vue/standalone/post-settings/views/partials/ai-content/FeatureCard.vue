<template>
	<div
		class="aioseo-ai-feature-slot"
		:class="{ 'aioseo-ai-feature-slot--hero': 'hero' === variant }"
	>
		<feature-body
			v-if="'hero' === variant"
			:feature="feature"
			variant="hero"
			:disabled-reason="disabledReason"
			:parent-component-context="parentComponentContext"
			:cost="resolvedCost"
			@activate="onClick"
		/>

		<core-tooltip
			v-else-if="disabledReason"
			type="action"
			placement="top"
			offset="25px, 0"
		>
			<div class="aioseo-ai-feature-slot__target aioseo-ai-feature-slot__target--disabled">
				<feature-body
					:feature="feature"
					:variant="variant"
				/>
			</div>

			<template #tooltip>
				{{ disabledReason }}
			</template>
		</core-tooltip>

		<button
			v-else
			type="button"
			class="aioseo-ai-feature-slot__target"
			@click="onClick"
		>
			<feature-body
				:feature="feature"
				:variant="variant"
			/>
		</button>

		<component
			v-if="!feature.clickCallback"
			:is="`${feature.slug}-modal`"
			:feature="feature"
			:show="aiStore.isModalOpened === feature.slug"
			@closeModal="aiStore.isModalOpened = null"
			:modal-name="`ai-content-${feature.slug}-modal`"
		/>

		<out-of-credits-modal
			:show="showOutOfCreditsModal"
			:feature="upsellFeature"
			@close="showOutOfCreditsModal = false"
		/>
	</div>
</template>

<script>
import { toRef } from 'vue'

import { useAiContent } from '@/vue/composables/AiContent'
import { useAiFeatureGate } from '@/vue/composables/AiFeatureGate'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import {
	useAiAssistantStore,
	useAiStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import FaqsModal from './FaqsModal'
import ImageGeneratorModal from './ImageGeneratorModal'
import KeyPointsModal from './KeyPointsModal'
import MetaDescriptionModal from './MetaDescriptionModal'
import MetaTitleModal from './MetaTitleModal'
import FeatureBody from './FeatureBody'
import OutOfCreditsModal from '@/vue/components/common/ai/OutOfCreditsModal'
import SocialPostsModal from './SocialPostsModal'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const { shouldUpsell, upsellFeature } = useAiFeatureGate(toRef(props, 'feature'))

		return {
			aiAssistantStore       : useAiAssistantStore(),
			aiContent              : useAiContent(),
			aiStore                : useAiStore(),
			truSeoHighlighterStore : useTruSeoHighlighterStore(),
			shouldUpsell,
			upsellFeature
		}
	},
	components : {
		CoreTooltip,
		FaqsModal,
		FeatureBody,
		ImageGeneratorModal,
		KeyPointsModal,
		MetaDescriptionModal,
		MetaTitleModal,
		OutOfCreditsModal,
		SocialPostsModal
	},
	props : {
		parentComponentContext : String,
		feature                : {
			type     : Object,
			required : true
		},
		buttonDisabled : {
			type     : Boolean,
			required : false
		},
		variant : {
			type    : String,
			default : 'row'
		}
	},
	data () {
		return {
			showOutOfCreditsModal : false,
			strings               : {
				// Translators: 1 - The word "Preferences" from WordPress core translations.
				blockHiddenWarning : sprintf(
					// Translators: 1 - The word "Preferences" from WordPress core translations.
					__('Block hidden in %1$s.', td),
					__('Preferences', td)
				),
				noPermission : __('You don\'t have permission to use this feature.', td)
			}
		}
	},
	computed : {
		// One reason replaces three near-identical disabled branches; the first that applies
		// is the one shown, and an empty result means the feature is actionable.
		disabledReason () {
			if ('ai-assistant' === this.feature.slug && this.aiAssistantStore.isBlockHiddenByUser) {
				return this.strings.blockHiddenWarning
			}

			if (
				(this.feature.permission && !allowed(this.feature.permission)) ||
				(this.feature.isAllowed && !this.feature.isAllowed())
			) {
				return this.strings.noPermission
			}

			if (this.buttonDisabled) {
				return this.aiContent.strings.noContentWarning
			}

			return ''
		},
		// Auto-Optimize's price moves with whether the post needs spelling work, so the hero
		// resolves it rather than letting FeatureBody's static costKey lookup answer. Left
		// undefined elsewhere so the Image Generator still quotes nothing on its card.
		resolvedCost () {
			return 'auto-optimize' === this.feature.slug ? this.truSeoHighlighterStore.optimizeCreditCost : undefined
		}
	},
	methods : {
		onClick () {
			if (this.shouldUpsell) {
				this.showOutOfCreditsModal = true

				return
			}

			if (this.feature?.clickCallback) {
				this.feature.clickCallback()

				return
			}

			this.aiStore.isModalOpened = this.feature.slug
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-feature-slot {
	&__target {
		display: block;
		width: 100%;
		padding: 0;
		border: 0;
		background: none;
		text-align: left;
		font: inherit;
		color: inherit;
		cursor: pointer;

		&--disabled {
			cursor: default;

			.aioseo-ai-feature {
				opacity: .55;
			}
		}
	}

	// Tooltip.vue ships a 12px left margin for inline use; here the wrapper is the card, so
	// it would indent every disabled feature.
	.aioseo-tooltip {
		display: block;
		margin-left: 0;
	}
}

.aioseo-ai-feature {
	display: flex;
	align-items: center;
	gap: 12px;
	background: #fff;
	border: 1px solid $border;
	border-radius: 6px;

	&__icon {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 6px;
		background: $blue4;

		svg {
			color: $blue;
		}
	}

	&__text {
		flex: 1;
		min-width: 0;
	}

	&__name {
		display: flex;
		align-items: center;
		gap: 7px;
		font-weight: $font-bold;
		color: $black;
	}

	&__hint,
	&__desc {
		display: block;
		color: $black2;
	}

	// BaseBadge's defaults are sized for a page heading, not a row title.
	.aioseo-badge {
		padding: 2px 8px;
		font-size: 11.5px;
		line-height: 1.45;
		font-weight: $font-bold;
	}

	&__cost {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		gap: 3px;
		font-size: 11px;
		font-weight: $font-bold;
		font-variant-numeric: tabular-nums;
		color: $placeholder-color;

		svg {
			width: 11px;
			height: 11px;
		}
	}

	&__caret {
		flex-shrink: 0;
		width: 14px;
		height: 14px;
		color: $blue;
		transform: rotate(-90deg);
	}

	// ---- row ----
	&--row {
		padding: 10px 11px;

		.aioseo-ai-feature__icon {
			width: 28px;
			height: 28px;

			svg {
				width: 15px;
				height: 15px;
			}
		}

		.aioseo-ai-feature__name {
			font-size: 13px;
			line-height: 1.3;
		}

		.aioseo-ai-feature__hint {
			font-size: 12px;
			line-height: 1.35;
		}
	}

	// ---- hero ----
	// Two rows: name and balance on the first, description and button on the second, so the
	// credits you have sit in the same column as the credits the action costs.
	&--hero {
		display: grid;
		grid-template-columns: 44px minmax(0, 1fr) auto;
		align-items: start;
		gap: 4px 16px;
		padding: 16px 18px;
		border-color: rgba($blue, 0.28);
		background: linear-gradient(115deg, $blue4, #E4EDFF 65%, #D9E6FF);

		.aioseo-ai-feature__icon {
			grid-row: span 2;
			align-self: center;
			width: 44px;
			height: 44px;
			background: #fff;
			border-radius: 9px;
			box-shadow: 0 1px 2px rgba($black, 0.1);

			svg {
				width: 23px;
				height: 23px;
			}
		}

		.aioseo-ai-feature__name {
			font-size: 15px;
		}

		.aioseo-ai-feature__counter {
			justify-self: end;
		}

		.aioseo-ai-feature__desc {
			grid-column: 2;
			font-size: 13px;
			line-height: 1.45;
		}

		.aioseo-ai-feature__action {
			grid-column: 3;
			justify-self: end;
			margin-top: 6px;
		}
	}

}

// One column at sidebar width: a screen media query can't see this, since the sidebar is
// ~280px on a 1280px screen. The icon tile goes too — at this width it costs more room than
// it earns, and every child left-aligns against the same edge.
.aioseo-ai-content-main--sidebar .aioseo-ai-feature--hero,
.aioseo-ai-feature-slot--narrow .aioseo-ai-feature--hero {
	grid-template-columns: minmax(0, 1fr);

	.aioseo-ai-feature__icon {
		display: none;
	}

	.aioseo-ai-feature__name,
	.aioseo-ai-feature__counter,
	.aioseo-ai-feature__desc,
	.aioseo-ai-feature__action {
		grid-column: 1;
		justify-self: start;
	}

	// Stacked, the balance sat between the name and its description and broke them apart.
	// It goes last instead, behind a rule, so the hero reads name → what it does → do it,
	// and the balance is a footnote to the price on the button.
	.aioseo-ai-feature__name {
		order: 1;
	}

	.aioseo-ai-feature__desc {
		order: 2;
	}

	.aioseo-ai-feature__action {
		order: 3;
	}

	.aioseo-ai-feature__counter {
		order: 4;
		width: 100%;
		margin-top: 12px;
		padding-top: 10px;
		border-top: 1px solid rgba($blue, 0.22);
	}
}

.aioseo-ai-feature-slot__target:hover .aioseo-ai-feature--row {
	border-color: rgba($blue, 0.45);
	box-shadow: 0 1px 3px rgba($black, 0.09);
}

// Chrome for the feature modals this component mounts. Kept here because that's where
// it has always lived; it isn't card styling.
.aioseo-modal.aioseo-ai-content-feature-modal {
	color: $font-color;

	.modal-wrapper .modal-container {
		max-width: 900px;
	}

	.modal-header {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: flex-start;

		.header-left {
			display: flex;
			flex-direction: row;
			align-items: center;

			svg {
				&.aioseo-arrow-back {
					width: 26px;
					height: 26px;
					padding-right: 10px;
					border-right: 1px solid $border;
					margin-right: 10px;
					cursor: pointer;
				}

				&.aioseo-ai-content-feature-modal-icon {
					width: 25px;
					height: 25px;
					margin-right: 10px;
					color: $blue;
				}
			}
		}
	}

	.aioseo-ai-content-feature-modal-body {
		.aioseo-ai-content-feature-modal-body-main {
			padding: 20px;
		}
	}

	.modal-container__footer {
		display: flex;
		flex-direction: row;
		padding: 12px 20px;
		justify-content: space-between;
		align-items: center;

		.button-icon {
			width: 20px;
			height: 20px;
			margin-right: 8px;
		}

		.footer-left {
			display: flex;
			flex-direction: row;
			gap: 12px;

			.aioseo-ai-credit-counter {
				display: flex;
				align-items: center;

				.counter-container {
					display: flex;
					flex-direction: row;
					align-items: center;
				}
			}

			.rephrase-button {
				svg.aioseo-ai-rephrase {
					width: 20px;
					height: 20px;
					margin-right: 4px;
				}
			}
		}

		.footer-right {
			display: flex;
			flex-direction: row;
			gap: 12px;

			.copy-button {
				svg {
					width: 20px;
					height: 20px;
					margin-right: 4px;
				}
			}
		}
	}
}
</style>