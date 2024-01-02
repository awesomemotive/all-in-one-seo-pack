<template>
	<div class="aioseo-link-assistant-sidebar">
		<core-settings-row
			v-if="'sidebar' === $root._data.screenContext"
			class="snippet-description-row open-link-assistant-copy"
		>
			<template #content>
				<div class="aioseo-sidebar-content-title">{{ strings.title }}</div>
				<p>{{ strings.description }}</p>
				<base-button
					class="open-link-assistant-modal gray small"
					@click="postEditorStore.currentPost.linkAssistant.modalOpen = true"
				>
					<svg-right-arrow-short />
					{{ strings.button }}
				</base-button>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgRightArrowShort from '@/vue/components/common/svg/right-arrow/Short'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreSettingsRow,
		SvgRightArrowShort
	},
	data () {
		return {
			strings : {
				title       : this.$t.__('Link Assistant', this.$td),
				description : this.$t.__('Here you can view an overview of your existing links as well as find suggestions for new internal links. Click on the button below to view the Link Assistant panel.', this.$td),
				button      : this.$t.__('Open Link Assistant', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
	.aioseo-link-assistant-sidebar {
		padding: 20px 16px;

		.open-link-assistant-modal {
			margin-top: 12px;
			border: 1px solid $gray;
			align-items: center;

			svg {
				margin-right: 8px;
				width: 10px;
				height: 10px;
			}
		}
		.open-link-assistant-copy > .col-md-3 {
			display: none;
		}
	}
</style>