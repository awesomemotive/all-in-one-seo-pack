<template>
	<div class="aioseo-tools-htaccess-editor">
		<core-card
			slug="htaccessEditor"
			:header-text="strings.htaccessEditor"
		>
			<div class="aioseo-settings-row aioseo-section-description"
				v-html="strings.description"
			/>

			<core-settings-row
				:name="strings.editHtaccess"
				align
			>
				<template #content>
					<core-alert
						v-if="optionsStore.htaccessError"
						type="red"
					>
						{{ optionsStore.htaccessError }}
					</core-alert>
					<base-editor
						class="htaccess-editor"
						:disabled="!rootStore.aioseo.user.unfilteredHtml"
						v-model="rootStore.aioseo.data.htaccess"
						line-numbers
						monospace
						preserve-whitespace
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import BaseEditor from '@/vue/components/common/base/Editor'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseEditor,
		CoreAlert,
		CoreCard,
		CoreSettingsRow
	},
	data () {
		return {
			strings : {
				htaccessEditor : this.$t.__('.htaccess Editor', this.$td),
				editHtaccess   : this.$t.__('Edit .htaccess', this.$td),
				description    : this.$t.sprintf(
					// Translators: 1 - Opening bold tag, 2 - Closing bold tag.
					this.$t.__('This allows you to edit the .htaccess file for your site. All WordPress sites on an Apache server have a .htaccess file and we have provided you with a convenient way of editing it. Care should always be taken when editing important files from within WordPress as an incorrect change could cause WordPress to become inaccessible. %1$sBe sure to make a backup before making changes and ensure that you have FTP access to your web server and know how to access and edit files via FTP.%2$s', this.$td),
					'<strong>',
					'</strong>'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-htaccess-editor {
	.aioseo-alert {
		margin-bottom: 20px;
	}

	.htaccess-editor {
		margin-top: 10px;
	}
}
</style>