<template>
	<div class="semrush-keywords-button-wrapper">
		<!-- Unlicensed Pro version - disabled button with license tooltip -->
		<core-tooltip
			v-if="!loading && hasFocusKeyphrase && isPro && isUnlicensed"
		>
			<div class="disabled-button gray">
				<svg-circle-plus width="14" height="14" />
				{{ strings.getAdditionalKeyphrases }}
			</div>

			<template #tooltip>
				<span v-html="strings.semrushTooltipLicenseKey" />
			</template>
		</core-tooltip>

		<!-- Licensed or Lite version - clickable button with optional tooltip -->
		<core-tooltip
			v-if="!loading && hasFocusKeyphrase && (!isPro || !isUnlicensed)"
			:disabled="!showTooltip || isPro"
			:force-show="showTooltip && !isPro"
		>
			<base-button
				class="add-keyphrase gray medium"
				@click="handleClick"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.getAdditionalKeyphrases }}
			</base-button>

			<template #tooltip>
				<span>{{ strings.semrushTooltip }}</span>
			</template>
		</core-tooltip>
	</div>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'click' ],
	components : {
		CoreTooltip,
		SvgCirclePlus
	},
	props : {
		hasFocusKeyphrase : {
			type    : Boolean,
			default : false
		},
		loading : {
			type    : Boolean,
			default : false
		},
		isPro : {
			type    : Boolean,
			default : false
		},
		isUnlicensed : {
			type    : Boolean,
			default : false
		},
		showTooltip : {
			type    : Boolean,
			default : false
		},
		settingsUrl : {
			type     : String,
			required : true
		}
	},
	computed : {
		strings () {
			return {
				getAdditionalKeyphrases : __('Get Additional Keywords', td),
				semrushTooltip          : sprintf(
					// Translators: 1 - Semrush.
					__('Get Additional Keywords with %1$s!', td),
					'Semrush'
				),
				semrushTooltipLicenseKey : sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag, 3 - Semrush.
					__('%1$sA valid license key is required%2$s in order to connect with %3$s.', td),
					'<a href="' + this.settingsUrl + '">',
					'</a>',
					'Semrush'
				)
			}
		}
	},
	methods : {
		handleClick () {
			this.$emit('click')
		}
	}
}
</script>

<style lang="scss" scoped>
.semrush-keywords-button-wrapper {
	margin-top: 16px;

	.disabled-button {
		display: inline-flex;
		align-items: center;
		padding: 8px 16px;
		border-radius: 3px;
		font-size: 14px;
		opacity: 0.5;
		cursor: not-allowed;

		svg {
			margin-right: 8px;
		}
	}

	.add-keyphrase {
		svg {
			margin-right: 8px;
		}
	}
}
</style>