<template>
	<div class="cornerstone-content-panel">
		<div class="cornerstone-content-text">
			<span>
				{{ strings.description }}
			</span>

			<a
				href="#"
				@click.stop.prevent="$emit('changeTab', {
					main : 'linkAssistant',
					sub  : 'link-suggestions'
				})"
			>
				{{ strings.linkingRecommendations }}
			</a>

			<span v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'cornerstoneContent', true)"></span>
		</div>

		<base-toggle
			v-model="postEditorStore.currentPost.pillar_content"
			:disabled="!hasRequiredFeature"
		>
			{{ strings.markAsCornerstone }}
		</base-toggle>

		<core-alert
			class="inline-upsell"
			v-if="licenseStore.isUnlicensed || !hasRequiredFeature"
			type="blue"
		>
			<div v-html="strings.upsell" />
		</core-alert>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	usePostEditorStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'changeTab' ],
	setup () {
		return {
			licenseStore       : useLicenseStore(),
			postEditorStore    : usePostEditorStore(),
			hasRequiredFeature : license.hasCoreFeature('general', 'cornerstone-content'),
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		CoreAlert
	},
	data () {
		return {
			license,
			strings : {
				description            : __('Cornerstone content refers to the most  important and informative articles or pages on your website that serve as the foundation for your content strategy. AIOSEO uses cornerstone content for', td),
				linkingRecommendations : __('internal linking recommendations in Link Assistant.', td),
				upsell                 : sprintf(
					// Translators: 1 - "PRO", "Learn more".
					__('Cornerstone Content is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('post-settings-general', 'cornerstone-content', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
				),
				markAsCornerstone : __('Mark as Cornerstone', td)
			}
		}
	}
}
</script>
<style lang="scss">
.cornerstone-content-panel {
	.cornerstone-content-text {
		margin-bottom: 14px;

		> a {
			padding-inline: 4px;
		}
	}

	.aioseo-alert.inline-upsell {
		order: 2
	}
}

.aioseo-sidebar-card {
	.cornerstone-content-panel {
		.aioseo-alert.inline-upsell {
			margin-top: 10px;
		}
	}
}
</style>