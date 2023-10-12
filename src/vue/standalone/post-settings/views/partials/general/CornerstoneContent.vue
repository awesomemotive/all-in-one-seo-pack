<template>
    <div class="cornerstone-content-panel">
		<p class="cornerstone-content-text">
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

			<span v-html="this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'cornerstoneContent', true)"></span>
		</p>

		<base-toggle
			v-model="postEditorStore.currentPost.pillar_content"
			@update:modelValue="postEditorStore.isDirty = true"
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
import {
	useLicenseStore,
	usePostEditorStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import CoreAlert from '@/vue/components/common/core/alert/Index'

export default {
	setup () {
		return {
			licenseStore       : useLicenseStore(),
			postEditorStore    : usePostEditorStore(),
			hasRequiredFeature : license.hasCoreFeature('general', 'cornerstone-content')
		}
	},
	components : {
		CoreAlert
	},
	emits : [ 'changeTab' ],
	data () {
		return {
			license,
			strings : {
				description            : this.$t.__('Cornerstone content refers to the most  important and informative articles or pages on your website that serve as the foundation for your content strategy. AIOSEO uses cornerstone content for', this.$td),
				linkingRecommendations : this.$t.__('internal linking recommendations in Link Assistant.', this.$td),
				upsell                 : this.$t.sprintf(
					// Translators: 1 - "PRO", "Learn more".
					this.$t.__('Cornerstone Content is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('post-settings-general', this.$constants.GLOBAL_STRINGS.learnMore, 'cornerstone-content', true)
				),
				markAsCornerstone : this.$t.__('Mark as Cornerstone', this.$td)
			}
		}
	}
}
</script>
<style lang="scss">
.cornerstone-content-panel {
	.cornerstone-content-text {
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