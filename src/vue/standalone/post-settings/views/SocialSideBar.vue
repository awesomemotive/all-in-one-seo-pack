<template>
	<core-settings-row
		:name="strings.metaDescription"
		v-if="'sidebar' === $root.$data.screenContext"
		class="snippet-description-row open-social-copy"
	>
		<template #content>
			<div class="aioseo-sidebar-content-title">{{ strings.title }}</div>

			<p>{{ strings.sidebarCopy }}</p>

			<base-button
				class="open-social-modal gray small"
				@click="openModalEv"
			>
				<svg-pencil />
				{{ strings.editSnippet }}
			</base-button>
		</template>
	</core-settings-row>
</template>

<script>
import {
	usePostEditorStore,
	useSettingsStore
} from '@/vue/stores'

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgPencil from '@/vue/components/common/svg/Pencil'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			settingsStore   : useSettingsStore()
		}
	},
	components : {
		CoreSettingsRow,
		SvgPencil
	},
	data () {
		return {
			strings : {
				title       : __('Social Media', td),
				sidebarCopy : __('Here you can view and edit the thumbnail, title and description that will be displayed when your site is shared on social media. Click on the button below to view and edit the preview.', td),
				editSnippet : __('Preview & Edit', td)
			}
		}
	},
	methods : {
		openModalEv () {
			this.settingsStore.changeTabSettings({ setting: 'modal', value: 'social' })
			this.postEditorStore.currentPost.modalOpen = true
		}
	}
}
</script>

<style lang="scss">
	.open-social-modal {
		margin: 12px 0 12px 0;
		border: 1px solid $gray;
		svg {
			margin-right: 5px;
		}
	}
	.open-social-copy > .col-md-3 {
		display: none;
	}
</style>