<template>
	<div class="aioseo-redirects-sidebar">
		<core-settings-row
			v-if="'sidebar' === $root.$data.screenContext"
			class="snippet-description-row open-redirects-copy"
		>
			<template #content>
				<div class="aioseo-sidebar-content-title">{{ strings.title }}</div>

				<p>{{ strings.description }}</p>

				<base-button
					class="open-redirects-modal gray small"
					@click="postEditorStore.currentPost.redirects.modalOpen = true"
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

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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
				title       : __('Redirects', td),
				description : __('Here you can add a redirect or view your existing redirects from this page. Click on the button below to view the Redirects panel.', td),
				button      : __('Open Redirects', td)
			}
		}
	}
}
</script>
<style lang="scss" scoped>
.open-redirects-modal {
	margin: 12px 0 12px 0;

	svg {
		margin-right: 8px;
		width: 10px;
		height: 10px;
	}
}
</style>