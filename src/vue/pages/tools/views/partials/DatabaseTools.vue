<template>
	<div class="aioseo-tools-database-tools">
		<core-card
			slug="databaseTools"
			:header-text="strings.resetRestoreSettings"
		>
			<core-settings-row
				v-if="rootStore.aioseo.data.isNetworkAdmin"
				:name="strings.selectSite"
			>
				<template #content>
					<base-select
						size="medium"
						v-model="site"
						:options="sites"
					/>
				</template>
			</core-settings-row>

			<core-reset-settings
				:site="selectedSite"
			/>
		</core-card>

		<core-card
			v-if="showLogs"
			slug="databaseToolsLogs"
			:header-text="strings.logs"
		>
			<template #tooltip>
				{{ strings.logsTooltip }}
			</template>

			<core-settings-row
				v-if="rootStore.aioseo.data.logSizes.logs404"
				:name="strings.logs404"
				align
			>
				<template #content>
					<base-button
						class="clear-log"
						type="gray"
						size="medium"
						:loading="'logs404' === loadingLog"
						:disabled="disabledLog('logs404')"
						@click="processClearLog('logs404')"
					>
						<span
							v-if="disabledLog('logs404')"
						>
							<svg-checkmark />
							{{ strings.cleared }}
						</span>
						<span
							v-if="!disabledLog('logs404')"
						>
							{{ strings.clear404Logs }}
						</span>
					</base-button>

					<div class="log-size">
						<span
							class="size-dot"
							:class="getSizeClass(rootStore.aioseo.data.logSizes.logs404.original)"
						/>
						{{ rootStore.aioseo.data.logSizes.logs404.readable }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="rootStore.aioseo.data.logSizes.redirectLogs"
				:name="strings.redirectLogs"
				align
			>
				<template #content>
					<base-button
						class="clear-log"
						type="gray"
						size="medium"
						:loading="'redirectLogs' === loadingLog"
						:disabled="disabledLog('redirectLogs')"
						@click="processClearLog('redirectLogs')"
					>
						<span
							v-if="disabledLog('redirectLogs')"
						>
							<svg-checkmark />
							{{ strings.cleared }}
						</span>
						<span
							v-if="!disabledLog('redirectLogs')"
						>
							{{ strings.clearRedirectLogs }}
						</span>
					</base-button>

					<div class="log-size">
						<span
							class="size-dot"
							:class="getSizeClass(rootStore.aioseo.data.logSizes.redirectLogs.original)"
						/>
						{{ rootStore.aioseo.data.logSizes.redirectLogs.readable }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="showBadBotBlockerLogs"
				:name="strings.badBotBlockerLogs"
				align
			>
				<template #content>
					<base-button
						class="clear-log"
						type="gray"
						size="medium"
						:loading="'badBotBlockerLog' === loadingLog"
						:disabled="disabledLog('badBotBlockerLog')"
						@click="processClearLog('badBotBlockerLog')"
					>
						<span
							v-if="disabledLog('badBotBlockerLog')"
						>
							<svg-checkmark />
							{{ strings.cleared }}
						</span>
						<span
							v-if="!disabledLog('badBotBlockerLog')"
						>
							{{ strings.clearBadBotBlockerLogs }}
						</span>
					</base-button>

					<div class="log-size">
						<span
							class="size-dot"
							:class="getSizeClass(rootStore.aioseo.data.logSizes.badBotBlockerLog.original)"
						/>
						{{ rootStore.aioseo.data.logSizes.badBotBlockerLog.readable }}
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import { Network } from '@/vue/mixins/Network'
import CoreCard from '@/vue/components/common/core/Card'
import CoreResetSettings from '@/vue/components/common/core/ResetSettings'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgCheckmark from '@/vue/components/common/svg/Checkmark'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			toolsStore   : useToolsStore()
		}
	},
	mixins     : [ Network ],
	components : {
		CoreCard,
		CoreResetSettings,
		CoreSettingsRow,
		SvgCheckmark
	},
	data () {
		return {
			site         : null,
			selectedSite : null,
			clearedLogs  : {
				badBotBlockerLogs : false,
				redirectLogs      : false,
				logs404           : false
			},
			loadingLog : null,
			strings    : {
				selectSite             : this.$t.__('Select Site', this.$td),
				resetRestoreSettings   : this.$t.__('Reset / Restore Settings', this.$td),
				logs                   : this.$t.__('Logs', this.$td),
				badBotBlockerLogs      : this.$t.__('Bad Bot Blocker Logs', this.$td),
				cleared                : this.$t.__('Cleared', this.$td),
				clearBadBotBlockerLogs : this.$t.__('Clear Bad Bot Blocker Logs', this.$td),
				logs404                : this.$t.__('404 Logs', this.$td),
				clear404Logs           : this.$t.__('Clear 404 Logs', this.$td),
				redirectLogs           : this.$t.__('Redirect Logs', this.$td),
				clearRedirectLogs      : this.$t.__('Clear Redirect Logs', this.$td),
				logsTooltip            : this.$t.__('Log sizes may fluctuate and not always be 100% accurate since the results can be cached. Also after clearing a log, it may not show as "0" since database tables also include additional information such as indexes that we don\'t clear.', this.$td)
			}
		}
	},
	watch : {
		site (newVal) {
			this.selectedSite = this.rootStore.aioseo.data.network.sites.sites.find(s => this.getUniqueSiteId(s) === newVal.value)
		}
	},
	computed : {
		canReset () {
			const passed = []
			Object.keys(this.options).forEach(key => {
				passed.push(this.options[key])
			})
			return !passed.some(a => a)
		},
		showLogs () {
			return !this.rootStore.aioseo.data.isNetworkAdmin &&
				(
					this.showBadBotBlockerLogs ||
					this.rootStore.aioseo.data.logSizes.redirectLogs ||
					this.rootStore.aioseo.data.logSizes.logs404
				)
		},
		showBadBotBlockerLogs () {
			return this.optionsStore.internalOptions.internal.deprecatedOptions.includes('badBotBlocker')
		},
		sites () {
			return this.getSites
				.filter(s => !s.parentDomain)
				.map(s => {
					return {
						value : this.getUniqueSiteId(s),
						label : `${s.domain}${s.path}`
					}
				})
		}
	},
	methods : {
		getSizeClass (size) {
			let color = 'green'
			if (262144000 < size) {
				color = 'orange'
			} else if (1073741274 < size) {
				color = 'red'
			}

			return color
		},
		processClearLog (log) {
			this.loadingLog = log
			this.toolsStore.clearLog(log)
				.then(() => {
					this.loadingLog       = null
					this.clearedLogs[log] = true
				})
		},
		disabledLog (log) {
			return !this.rootStore.aioseo.data.logSizes[log].original || this.clearedLogs[log]
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-database-tools {
	.clear-log {
		svg {
			width: 12px;
			height: 12px;
			margin-right: 5px;
		}
	}

	.log-size {
		display: inline-flex;
		margin-left: 20px;
		height: 40px;
		background: $box-background;
		align-items: center;
		justify-content: center;
		padding: 0 15px;
		font-size: 14px;
		font-weight: 600;
		color: $black2;

		.size-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			margin-right: 10px;

			&.green {
				background-color: $green;
			}

			&.orange {
				background-color: $orange;
			}

			&.red {
				background-color: $red;
			}
		}
	}
}
</style>