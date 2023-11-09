<template>
	<div class="aioseo-core-reset-settings">
		<core-settings-row
			:name="strings.selectSettings"
			class="no-border"
		>
			<template #content>
				<core-alert
					class="reset-success"
					v-if="showSuccess"
					type="green"
				>
					{{ strings.resetSuccess }}
				</core-alert>

				<div class="reset-settings">
					{{ strings.selectSettingsToReset }}

					<br>
					<br>

					<grid-row
						class="settings"
					>
						<grid-column>
							<base-checkbox
								size="medium"
								v-model="options.all"
								:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
							>
								{{ strings.allSettings }}
							</base-checkbox>
						</grid-column>
						<grid-column
							v-for="(setting, index) in toolsSettings"
							:key="index"
							xl="3"
							md="4"
							sm="6"
						>
							<base-checkbox
								v-if="!options.all"
								size="medium"
								v-model="options[setting.value]"
								:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
							>
								{{ setting.label }}
							</base-checkbox>

							<base-checkbox
								v-if="'all' !== setting.value && options.all"
								size="medium"
								:modelValue="true"
								disabled
							>
								{{ setting.label }}
							</base-checkbox>
						</grid-column>
					</grid-row>

					<base-button
						type="gray"
						size="medium"
						@click="showModal = true"
						:disabled="canReset"
					>
						{{ strings.resetSelectedSettings }}
					</base-button>
				</div>
			</template>
		</core-settings-row>

		<core-modal
			v-if="showModal"
			no-header
			@close="showModal = false"
		>
			<template #body >
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="showModal = false"
					>
						<svg-close @click="showModal = false" />
					</button>

					<h3>{{ strings.areYouSureReset }}</h3>
					<div class="reset-description"
						v-html="strings.actionCannotBeUndone"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="processResetSettings"
						:loading="loading"
					>
						{{ strings.yesIHaveBackup }}
					</base-button>

					<base-button
						type="gray"
						size="medium"
						@click="showModal = false"
					>
						{{ strings.noBackup }}
					</base-button>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import { ToolsSettings } from '@/vue/mixins/ToolsSettings'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	setup () {
		return {
			rootStore  : useRootStore(),
			toolsStore : useToolsStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreAlert,
		CoreModal,
		CoreSettingsRow,
		GridColumn,
		GridRow,
		SvgClose
	},
	mixins : [ ToolsSettings ],
	props  : {
		site : Object
	},
	data () {
		return {
			showSuccess : false,
			showModal   : false,
			loading     : false,
			options     : {},
			strings     : {
				selectSettings        : this.$t.__('Select Settings', this.$td),
				selectSettingsToReset : this.$t.__('Select settings that you would like to reset:', this.$td),
				resetSelectedSettings : this.$t.__('Reset Selected Settings to Default', this.$td),
				resetSuccess          : this.$t.__('Your settings have been reset successfully!', this.$td),
				areYouSureReset       : this.$t.__('Are you sure you want to reset the selected settings to default?', this.$td),
				actionCannotBeUndone  : this.$t.sprintf(
					// Translators: 1 - Opening bold tag, 2 - Closing bold tag.
					this.$t.__('This action cannot be undone. Before taking this action, we recommend that you make a %1$sfull website backup first%2$s.', this.$td),
					'<strong>',
					'</strong>'
				),
				yesIHaveBackup : this.$t.__('Yes, I have a backup and want to reset the settings', this.$td),
				noBackup       : this.$t.__('No, I need to make a backup', this.$td),
				allSettings    : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					this.$t.__('All %1$s Settings', this.$td),
					import.meta.env.VITE_SHORT_NAME
				)
			}
		}
	},
	computed : {
		canReset () {
			if (this.rootStore.aioseo.data.isNetworkAdmin && !this.site) {
				return false
			}

			const passed = []
			Object.keys(this.options).forEach(key => {
				passed.push(this.options[key])
			})

			return !passed.some(a => a)
		}
	},
	methods : {
		processResetSettings () {
			const payload = []
			if (this.options.all) {
				this.toolsSettings
					.filter(setting => 'all' !== setting.value)
					.forEach(setting => {
						payload.push(setting.value)
					})
			} else {
				Object.keys(this.options).forEach(key => {
					if (this.options[key]) {
						payload.push(key)
					}
				})
			}

			this.loading = true
			this.toolsStore.resetSettings({
				payload,
				siteId : this.site ? this.site.blog_id : null
			})
				.then(() => {
					this.showModal   = false
					this.loading     = false
					this.showSuccess = true
					this.options     = {}
					setTimeout(() => {
						this.showSuccess = false
					}, 5000)
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-core-reset-settings {
	.reset-success {
		margin-bottom: 16px;
	}

	.reset-settings {
		margin-top: 0;
		font-size: 16px;
		color: $black;

		> .settings {

			> .aioseo-col:first-child {
				font-weight: $font-bold;
				padding-bottom: 12px;
				border-bottom: 1px solid $border;
			}
		}

		.aioseo-button {
			margin-top: 16px;
		}
	}

	.aioseo-modal-body {
		padding: 20px 50px 50px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		.aioseo-description {
			max-width: 510px;
			text-align: center;
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>