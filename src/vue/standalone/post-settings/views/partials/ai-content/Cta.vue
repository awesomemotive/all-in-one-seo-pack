<template>
	<cta
		v-if="aiStore.isFreeAndOutOfCredits"
		:type="6"
		cta-button-action
		cta-second-button-visible
		cta-second-button-action
		:show-link="false"
		hide-bonus
	>
		<template #featured-image>
			<img
				alt="AIOSEO AI Content Generation"
				:src="getAssetUrl(aiContentLiteImg)"
			/>
		</template>

		<template #header-text>
		{{ strings.ctaHeader }}
		</template>

		<template #description>
			<p>{{ strings.aiContentDescription }}</p>

			<buy-or-connect-actions />

			<a
				class="aioseo-pro-upgrade-link"
				:href="links.getPricingUrl('metabox', 'ai-content', null, 'liteUpgrade')"
				target="_blank"
				rel="noopener noreferrer"
			>
				{{ strings.proUpgradeLink }}
			</a>
		</template>
	</cta>
</template>

<script setup>
import {
	useAiStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import links from '@/vue/utils/links'

import BuyOrConnectActions from '@/vue/components/common/ai/BuyOrConnectButtons'
import Cta from '@/vue/components/common/cta/Index'
import aiContentLiteImg from '@/vue/assets/images/upsells/ai-content.png'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	ctaHeader            : __('You Ran Out of Trial Credits!', td),
	aiContentDescription : __('To continue using awesome AI Content features such as FAQs, Key Points, and Social Posts, you must purchase additional credits or connect to an existing account.', td),
	proUpgradeLink       : __('Or upgrade to Pro to unlock additional AI credits.', td)
}

const aiStore   = useAiStore()
</script>

<style lang="scss">
.aioseo-ai-content-settings {
	.aioseo-cta {
		.description {
			margin-block: 0;
		}
	}
}
.aioseo-app.aioseo-post-settings,
.aioseo-ai-content-settings {
	.aioseo-cta {
		.aioseo-buy-or-connect {
			margin: 12px auto 24px auto;
		}

		a.aioseo-pro-upgrade-link {
			display: block;
			margin: 0 auto;
			font-style: italic;
			color: $placeholder-color;
			text-wrap: auto;
		}
	}
}

.aioseo-post-settings-sidebar {
	.aioseo-cta {
		.aioseo-cta-background {
			padding: 24px 12px !important;
		}

		.aioseo-loading-spinner {
			margin-top: 0 !important;
		}

		.aioseo-buy-or-connect {
			margin-top: 16px;

			.aioseo-buy-or-connect__actions {
				flex-direction: column;

				a {
					font-size: 14px;
					width: 180px;
					text-wrap: auto;
					height: 50px;
				}
			}
		}
	}
}
</style>