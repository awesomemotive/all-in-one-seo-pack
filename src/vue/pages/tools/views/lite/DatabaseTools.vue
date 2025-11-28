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
				:cta-link="links.getPricingUrl('network-tools', 'database-tools', null, 'liteUpgrade')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('network-tools', 'database-tools', 'liteUpgrade')"
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
}
</style>