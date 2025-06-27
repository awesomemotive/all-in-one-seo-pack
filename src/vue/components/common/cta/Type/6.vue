<template>
	<div class="type-6">
    <div class="featured-image">
			<slot name="featured-image" />
		</div>

		<div class="header-text">
			<slot name="header-text">
				{{ strings.ctaHeader }}
			</slot>
		</div>

		<div class="description">
			<slot name="description">
				{{ strings.ctaDescription }}
			</slot>
		</div>

		<grid-row
			v-if="featureList"
			class="feature-list"
		>
			<grid-column
				md="6"
				v-for="(feature, index) in featureList"
				:key="index"
			>
				<svg-circle-check />
				{{ feature }}
			</grid-column>
		</grid-row>

		<core-alert
			v-if="!ctaButtonVisible && ctaButtonVisibleWarning"
			type="yellow"
			v-html="ctaButtonVisibleWarning"
		/>

		<core-alert
			v-if="!hideBonus"
			class="bonus-alert"
			type="yellow"
		>
			🎁 <span v-html="bonus" />
		</core-alert>
	</div>
</template>

<script setup>
import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import { __, sprintf } from '@/vue/plugins/translations'
import * as constants from '@/vue/plugins/constants'

const td           = import.meta.env.VITE_TEXTDOMAIN
const bonus = sprintf(
	// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - "Pro", 4 - Opening bold tag, 5 - A discount percentage (e.g. "50%"), 6 - Closing bold tag.
	__('%1$sBonus:%2$s You can upgrade to the %3$s plan today and %4$ssave %5$s off%6$s (discount auto-applied).', td),
	'<strong>',
	'</strong>',
	'Pro',
	'<strong>',
	constants.DISCOUNT_PERCENTAGE,
	'</strong>'
)

defineProps({
	strings                 : Object,
	featureList             : Array,
	ctaButtonVisible        : Boolean,
	ctaButtonLoading        : Boolean,
	ctaSecondButtonLoading  : Boolean,
	ctaSecondButtonVisible  : Boolean,
	ctaButtonVisibleWarning : String,
	ctaLink                 : String,
	ctaSecondLink           : String,
	target                  : String,
	buttonText              : String,
	secondButtonText        : String,
	ctaButtonClick          : Function,
	ctaSecondButtonClick    : Function,
	ctaSecondButtonNewBadge : Boolean,
	showLink                : Boolean,
	learnMoreLink           : String,
	hideBonus               : Boolean
})
</script>

<style lang="scss">
.aioseo-app,
.aioseo-blc-app,
.aioseo-duplicator-app,
#aioseo-user-profile-tab {
	.aioseo-cta {
		.aioseo-cta-background {
			.type-6 {
				display: flex;
				flex-direction: column;
				align-items: center;

				.description {
					color: $black2;
					font-size: 14px;
					line-height: 22px;
				}

				.bonus-alert {
					margin-top: 20px;
				}
			}
		}
	}
}
</style>