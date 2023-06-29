<template>
	<div class="aioseo-tools-system-status-info">
		<core-card
			slug="systemStatusInfo"
			:header-text="strings.systemStatusInfo"
		>
			<div class="actions">
				<grid-row>
					<grid-column
						sm="6"
						class="left"
					>
						<base-button
							type="blue"
							size="small"
							@click="downloadSystemStatusInfo"
						>
							<svg-download />
							{{ strings.downloadSystemInfoFile }}
						</base-button>

						<base-button
							type="blue"
							size="small"
							v-clipboard:copy="copySystemStatusInfo"
							v-clipboard:success="onCopy"
							v-clipboard:error="onError"
						>
							<template
								v-if="!copied"
							>
								<svg-copy />
								{{ strings.copyToClipboard }}
							</template>

							<template
								v-if="copied"
							>
								<svg-checkmark />
								{{ strings.copied }}
							</template>
						</base-button>
					</grid-column>
					<grid-column
						sm="6"
						class="right"
					>
						<base-input
							size="small"
							:placeholder="strings.emailDebugInformation"
							v-model="emailAddress"
							:class="{ 'aioseo-error': emailError }"
						/>

						<base-button
							type="blue"
							size="small"
							@click="processEmailDebugInfo"
							:loading="sendingEmail"
						>
							{{ strings.submit }}
						</base-button>
					</grid-column>
				</grid-row>
			</div>

			<div class="aioseo-settings-row">
				<template
					v-for="(group, i) in rootStore.aioseo.data.status"
					:key="i"
				>
					<div
						v-if="group.results.length"
						class="settings-group"
					>
						<div class="settings-name">
							<div class="name">{{ group.label }}</div>
						</div>

						<div class="settings-content">
							<div class="system-status-table">
								<table-row
									v-for="(row, index) in group.results"
									:key="index"
									:class="{ even: 0 === index % 2 }"
								>
									<table-column
										class="system-status-header"
									>
										{{ row.header }}
									</table-column>
									<table-column>
										{{ row.value }}
									</table-column>
								</table-row>
							</div>
						</div>
					</div>
				</template>
			</div>
		</core-card>
	</div>
</template>

<script>
import {
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import { DateTime } from 'luxon'
import CoreCard from '@/vue/components/common/core/Card'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCheckmark from '@/vue/components/common/svg/Checkmark'
import SvgCopy from '@/vue/components/common/svg/Copy'
import SvgDownload from '@/vue/components/common/svg/Download'
import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'
export default {
	setup () {
		return {
			rootStore  : useRootStore(),
			toolsStore : useToolsStore()
		}
	},
	components : {
		CoreCard,
		GridColumn,
		GridRow,
		SvgCheckmark,
		SvgCopy,
		SvgDownload,
		TableColumn,
		TableRow
	},
	data () {
		return {
			copied       : false,
			emailError   : null,
			emailAddress : null,
			sendingEmail : false,
			strings      : {
				systemStatusInfo       : this.$t.__('System Status Info', this.$td),
				downloadSystemInfoFile : this.$t.__('Download System Info File', this.$td),
				copyToClipboard        : this.$t.__('Copy to Clipboard', this.$td),
				emailDebugInformation  : this.$t.__('Email Debug Information', this.$td),
				submit                 : this.$t.__('Submit', this.$td),
				wordPress              : this.$t.__('WordPress', this.$td),
				serverInfo             : this.$t.__('Server Info', this.$td),
				activeTheme            : this.$t.__('Active Theme', this.$td),
				muPlugins              : this.$t.__('Must-Use Plugins', this.$td),
				activePlugins          : this.$t.__('Active Plugins', this.$td),
				inactivePlugins        : this.$t.__('Inactive Plugins', this.$td),
				copied                 : this.$t.__('Copied!', this.$td)
			}
		}
	},
	computed : {
		copySystemStatusInfo () {
			return JSON.stringify(this.rootStore.aioseo.data.status)
		}
	},
	methods : {
		onCopy () {
			this.copied = true
			setTimeout(() => {
				this.copied = false
			}, 2000)
		},
		onError () {},
		downloadSystemStatusInfo () {
			const blob    = new Blob([ JSON.stringify(this.rootStore.aioseo.data.status) ], { type: 'application/json' })
			const link    = document.createElement('a')
			link.href     = URL.createObjectURL(blob)
			link.download = `aioseo-system-status-${DateTime.now().toFormat('yyyy-MM-dd')}.json`
			link.click()
			URL.revokeObjectURL(link.href)
		},
		processEmailDebugInfo () {
			this.emailError = false
			if (!this.emailAddress || !/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(this.emailAddress)) {
				this.emailError = true
				return
			}

			this.sendingEmail = true
			this.toolsStore.emailDebugInfo(this.emailAddress)
				.then(() => {
					this.emailAddress = null
					this.sendingEmail = false
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-system-status-info {

	.actions {
		margin-bottom: var(--aioseo-gutter);

		.left {
			.aioseo-button {
				margin-right: 16px;

				svg {
					width: 14px;
					height: 14px;
					margin-right: 5px;
				}
			}
		}

		.right {
			display: flex;
			justify-content: flex-end;

			.aioseo-input {
				display: flex;
				justify-content: flex-end;

				input {
					max-width: 230px;
					margin-right: 10px;
				}
			}
		}
	}

	.settings-name .name {
		font-size: 16px;
		line-height: 24px;
	}

	.system-status-table {
		border: 1px solid $input-border;
		font-size: 14px;
		border-radius: 3px;

		.aioseo-table-row {
			min-height: 38px;
			padding: 0 15px;

			&:first-child {
				border-radius: 3px 3px 0 0;
			}

			&:last-child {
				border-radius: 0 0 3px 3px;
			}

			&.even {
				background-color: $box-background;
			}

			.aioseo-table-column {
				&.system-status-header {
					margin-right: 30px;
					max-width: 250px;
					font-weight: 600;
					text-align: right;
				}
			}
		}
	}

	.aioseo-settings-row {
		.settings-content {
			margin-top: 16px;
		}

		.settings-group:not(:first-child) {
			margin-top: var(--aioseo-gutter);
		}
	}
}
</style>