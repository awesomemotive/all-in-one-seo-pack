<template>
	<div class="additional-keyphrases-upsell">
		<div class="aioseo-description additional-keyphrases-description">
			{{ strings.keyphraseDocumentation }}
		</div>

		<core-alert
			class="inline-upsell"
			type="blue"
		>
			<div v-html="strings.upsell" />
		</core-alert>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreAlert
	},
	data () {
		return {
			strings : {
				keyphraseDocumentation : __('Improve your SEO rankings with additional keywords.', td),
				upsell                 : sprintf(
					// Translators: 1 - "Pro" string, 2 - "Learn more link".
					__('Additional Keywords are a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('post-settings', 'additional-keywords', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
				)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.additional-keyphrases-upsell {
	.additional-keyphrases-description {
		margin: 0 0 12px;
	}
}
</style>