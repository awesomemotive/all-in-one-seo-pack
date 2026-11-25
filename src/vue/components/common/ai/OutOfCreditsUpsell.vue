<template>
	<div class="aioseo-out-of-credits">
		<div class="aioseo-out-of-credits__feature">
			<div class="aioseo-out-of-credits__visual">
				<component :is="feature.icon" />
			</div>

			<div class="aioseo-out-of-credits__feature-text">
				<span class="aioseo-out-of-credits__feature-name">{{ feature.title }}</span>
				<p>{{ feature.description }}</p>
			</div>
		</div>

		<div class="aioseo-out-of-credits__meter">
			<template v-if="cost">
				<span v-safe-html="costLabel" />
				<span class="aioseo-out-of-credits__meter-divider">&middot;</span>
			</template>

			<span v-safe-html="remainingLabel" />
		</div>

		<ul class="aioseo-out-of-credits__benefits">
			<li
				v-for="(benefit, index) in benefits"
				:key="index"
			>
				<svg-circle-check />
				<span v-safe-html="benefit" />
			</li>
		</ul>

		<div class="aioseo-out-of-credits__actions">
			<base-button
				tag="a"
				:type="isElite ? 'blue' : 'green'"
				size="medium"
				:href="primaryUrl"
				target="_blank"
			>
				{{ primaryLabel }}
			</base-button>

			<a
				v-if="!isElite"
				class="aioseo-out-of-credits__secondary"
				v-safe-href="creditsUrl"
				target="_blank"
			>
				{{ strings.buyBundle }}
			</a>

		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useAiContent } from '@/vue/composables/AiContent'
import dateFormat from '@/vue/utils/dateFormat'
import dayjs from '@/vue/utils/dayjs'
import links from '@/vue/utils/links'

import BaseButton from '@/vue/components/common/base/Button'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	// { slug, title, description, icon, costKey or cost, costIsMinimum }
	feature : {
		type     : Object,
		required : true
	}
})

const licenseStore = useLicenseStore()
const optionsStore = useOptionsStore()
const rootStore    = useRootStore()

const { getFeatureCost } = useAiContent()

const strings = {
	buyBundle : __('or buy a credit bundle', td)
}

// Optimize's price depends on whether the post needs spelling work, so it passes a
// resolved cost; everything else looks its own up. A feature with neither has no price to
// advertise, and the cost half of the meter is dropped rather than given a fallback.
const cost      = computed(() => {
	if (undefined !== props.feature.cost) {
		return props.feature.cost
	}

	return props.feature.costKey ? getFeatureCost(props.feature.costKey) : 0
})
const remaining = computed(() => optionsStore.internalOptions.internal.ai.credits.remaining || 0)

const isLicensed = computed(() => rootStore.isPro && !licenseStore.isUnlicensed)
const isElite    = computed(() => {
	return isLicensed.value &&
		'elite' === (optionsStore.internalOptions.internal.license?.level || '').toLowerCase()
})

const renewalDate = computed(() => {
	const expires = optionsStore.internalOptions.internal.license?.expires
	if (!expires) {
		return ''
	}

	return dateFormat(dayjs(expires * 1000).toDate(), rootStore.aioseo.data.dateFormat)
})

const costLabel = computed(() => props.feature.costIsMinimum
	? sprintf(
		// Translators: 1 - Opening strong tag, 2 - Number of credits, 3 - Closing strong tag.
		__('This action costs min. %1$s%2$s credits%3$s', td),
		'<strong>',
		cost.value.toLocaleString(),
		'</strong>'
	)
	: sprintf(
		// Translators: 1 - Opening strong tag, 2 - Number of credits, 3 - Closing strong tag.
		__('This action costs %1$s%2$s credits%3$s', td),
		'<strong>',
		cost.value.toLocaleString(),
		'</strong>'
	)
)

const remainingLabel = computed(() => sprintf(
	// Translators: 1 - Opening strong tag, 2 - Number of credits remaining, 3 - Closing strong tag.
	__('You have %1$s%2$s%3$s remaining', td),
	'<strong class="aioseo-out-of-credits__short">',
	remaining.value.toLocaleString(),
	'</strong>'
))

// Upgrading is the emphasis wherever an upgrade exists, because it renews; a bundle is
// a one-off. Elite has no higher tier, so there the bundle becomes the only offer.
const primaryLabel = computed(() => {
	if (!isLicensed.value) {
		return __('Upgrade to Pro', td)
	}

	return isElite.value ? __('Buy Credits', td) : __('Upgrade your plan', td)
})

const primaryUrl = computed(() => {
	if (!isLicensed.value) {
		return links.getUpsellUrl(props.feature.slug, 'out-of-credits', 'liteUpgrade')
	}

	return isElite.value ? creditsUrl.value : links.getUpsellUrl(props.feature.slug, 'out-of-credits', 'pricing')
})

const creditsUrl = computed(() => links.getUpsellUrl(props.feature.slug, 'out-of-credits', 'aiCredits'))

const benefits = computed(() => {
	if (!isLicensed.value) {
		return [
			sprintf(
				// Translators: 1 - Opening strong tag, 2 - Closing strong tag.
				__('%1$sA full year of AI credits%2$s included with every Pro license.', td),
				'<strong>',
				'</strong>'
			),
			__('Unlimited keywords, Local SEO, Search Statistics and 30+ more features.', td)
		]
	}

	const resetNote = renewalDate.value
		? sprintf(
			// Translators: 1 - Opening strong tag, 2 - Renewal date, 3 - Closing strong tag.
			__('Your credits reset when your license renews on %1$s%2$s%3$s.', td),
			'<strong>',
			renewalDate.value,
			'</strong>'
		)
		: __('Your credits reset when your license renews.', td)

	if (isElite.value) {
		return [
			resetNote,
			__('A bundle tops you up straight away and doesn\'t affect your renewal.', td)
		]
	}

	return [
		sprintf(
			// Translators: 1 - Opening strong tag, 2 - Closing strong tag.
			__('%1$sHigher plans include a larger annual allowance%2$s for running AI across every post.', td),
			'<strong>',
			'</strong>'
		),
		resetNote
	]
})
</script>

<style lang="scss">
.aioseo-out-of-credits {
	&__feature {
		display: flex;
		align-items: flex-start;
		gap: 14px;
	}

	&__visual {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 74px;
		height: 74px;
		border: 1px solid $border;
		border-radius: 6px;
		background: linear-gradient(150deg, $blue4, #E4EDFF);

		svg {
			width: 34px;
			height: 34px;
			color: $blue;
		}
	}

	&__feature-name {
		display: block;
		margin-bottom: 4px;
		font-size: 14.5px;
		font-weight: 700;
		color: $black;
	}

	&__feature-text p {
		margin: 0;
		font-size: 13px;
		line-height: 1.5;
		color: $black2;
	}

	&__meter {
		display: flex;
		align-items: baseline;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 16px;
		padding: 10px 12px;
		border-radius: 5px;
		background: $background;
		font-size: 12.5px;
		color: $black2;

		strong {
			color: $black;
			font-variant-numeric: tabular-nums;
		}
	}

	&__short {
		color: $red !important;
	}

	&__meter-divider {
		color: $placeholder-color;
	}

	&__benefits {
		list-style: none;
		margin: 16px 0 0;
		padding: 0;

		li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			padding: 3px 0;
			font-size: 13px;
			color: $black2;
		}

		svg {
			flex-shrink: 0;
			width: 14px;
			height: 14px;
			margin-top: 3px;
			color: $green;
		}

		strong {
			color: $black;
		}
	}

	&__actions {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 14px;
		margin-top: 20px;
	}

	&__secondary {
		font-size: 13px;
		font-weight: 600;
		color: $blue;
		text-decoration: none;

		&:hover {
			text-decoration: underline;
		}
	}

}
</style>