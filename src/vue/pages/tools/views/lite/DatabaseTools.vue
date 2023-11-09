<template>
	<div class="aioseo-tools-database-tools">
		<core-card
			slug="databaseTools"
			:header-text="strings.resetRestoreSettings"
		>
			<core-blur>
				<core-settings-row
					:name="strings.selectSite"
				>
					<template #content>
						<base-select
							size="medium"
							:modelValue="{ value: '', label: '' }"
							:options="[]"
						/>
					</template>

				</core-settings-row>

				<core-reset-settings />
			</core-blur>

			<cta
				:cta-link="$links.getPricingUrl('network-tools', 'database-tools')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="$links.getUpsellUrl('network-tools', 'database-tools', $isPro ? 'pricing' : 'liteUpgrade')"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<!-- <required-plans addon="aioseo-redirects" /> -->
					{{ strings.networkDatabaseToolsDescription }}
				</template>
			</cta>
		</core-card>
	</div>
</template>

<script>
import { Network } from '@/vue/mixins/Network'
import CoreCard from '@/vue/components/common/core/Card'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreResetSettings from '@/vue/components/common/core/ResetSettings'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'
export default {
	mixins     : [ Network ],
	components : {
		CoreBlur,
		CoreCard,
		CoreResetSettings,
		CoreSettingsRow,
		Cta
	},
	data () {
		return {
			strings : {
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
				logsTooltip            : this.$t.__('Log sizes may fluctuate and not always be 100% accurate since the results can be cached. Also after clearing a log, it may not show as "0" since database tables also include additional information such as indexes that we don\'t clear.', this.$td),
				ctaHeader              : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('This feature is not available in your current plan.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaButtonText                   : this.$t.__('Upgrade Your Plan and Unlock Network Tools', this.$td),
				networkDatabaseToolsDescription : this.$t.__('Unlock network-level tools to manage all your sites from one easy-to-use location. Migrate data or create backups without the need to visit each dashboard.', this.$td)
			}
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