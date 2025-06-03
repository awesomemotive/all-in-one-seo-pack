<template>
	<div class="aioseo-tools-database-tools-lite">
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
				:cta-link="links.getPricingUrl('network-tools', 'database-tools')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('network-tools', 'database-tools', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans :core-feature="[ 'tools', 'network-tools-database' ]" />
					{{ strings.networkDatabaseToolsDescription }}
				</template>
			</cta>
		</core-card>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import links from '@/vue/utils/links'
import CoreCard from '@/vue/components/common/core/Card'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreResetSettings from '@/vue/components/common/core/ResetSettings'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		RequiredPlans,
		CoreBlur,
		CoreCard,
		CoreResetSettings,
		CoreSettingsRow,
		Cta
	},
	data () {
		return {
			links,
			strings : {
				selectSite           : __('Select Site', td),
				resetRestoreSettings : __('Reset / Restore Settings', td),
				logs                 : __('Logs', td),
				cleared              : __('Cleared', td),
				logs404              : __('404 Logs', td),
				clear404Logs         : __('Clear 404 Logs', td),
				redirectLogs         : __('Redirect Logs', td),
				clearRedirectLogs    : __('Clear Redirect Logs', td),
				logsTooltip          : __('Log sizes may fluctuate and not always be 100% accurate since the results can be cached. Also after clearing a log, it may not show as "0" since database tables also include additional information such as indexes that we don\'t clear.', td),
				ctaHeader            : sprintf(
					// Translators: 1 - "PRO".
					__('Network Tools is a %1$s Feature', td),
					'PRO'
				),
				ctaButtonText                   : __('Unlock Network Tools', td),
				networkDatabaseToolsDescription : __('Unlock network-level tools to manage all your sites from one easy-to-use location. Migrate data or create backups without the need to visit each dashboard.', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-database-tools-lite {
	.aioseo-card .content {
		padding-bottom: 120px;
	}

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