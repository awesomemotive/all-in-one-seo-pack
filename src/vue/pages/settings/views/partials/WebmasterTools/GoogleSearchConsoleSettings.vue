<template>
	<grid-column class="tool-settings tool-settings-google-search-console">
		<div
			v-for="(setting, index) in tool.settings"
			:key="index"
		>
			<core-settings-row
				noHorizontalMargin
				align-small
			>
				<template #name>
					{{ setting.label }}
				</template>

				<template #content>
					<div class="d-flex">
						<base-input
							size="small"
							@blur="maybeUpdateId(setting.option)"
							v-model="optionsStore.options.webmasterTools[setting.option]"
						/>
					</div>

					<p class="aioseo-description" v-html="setting.description" />

					<core-alert
						class="inline-upsell"
						type="blue"
						v-if="licenseStore.isUnlicensed"
					>
						<div v-html="strings.liteUpsell" />
					</core-alert>

					<core-alert
						class="inline-upsell"
						type="blue"
						v-if="!licenseStore.isUnlicensed && !license.hasCoreFeature('search-statistics', 'seo-statistics')"
					>
						<div v-html="requiredPlansString" />
					</core-alert>

					<core-alert
						class="inline-upsell"
						type="blue"
						v-if="!licenseStore.isUnlicensed && license.hasCoreFeature('search-statistics', 'seo-statistics') && !searchStatisticsStore.isConnected"
					>
						<div v-html="strings.notConnected" />
					</core-alert>
				</template>
			</core-settings-row>
		</div>
	</grid-column>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { merge } from 'lodash-es'
import { useWebmasterTools } from '@/vue/composables'
import { WebmasterTools } from '@/vue/pages/settings/mixins'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
export default {
	setup () {
		const { strings } = useWebmasterTools()

		return {
			licenseStore          : useLicenseStore(),
			optionsStore          : useOptionsStore(),
			rootStore             : useRootStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			composableStrings     : strings
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		GridColumn
	},
	mixins : [ WebmasterTools ],
	data () {
		return {
			license,
			strings : merge(this.composableStrings, {
				thisFeatureRequires         : this.$t.__('Advanced tracking and actionable reports require one of the following plans:', this.$td),
				thisFeatureRequiresSingular : this.$t.__('Advanced tracking and actionable reports require the following plan:', this.$td),
				liteUpsell                  : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('Advanced tracking and actionable reports is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('search-statistics', this.$constants.GLOBAL_STRINGS.learnMore, 'webmaster-tools', true)
				),
				notConnected : this.$t.sprintf(
					// Translators: 1 - The opening anchor tag, 2 - The closing anchor tag, 3 - "Learn more".
					this.$t.__('Connect your site with Google Search Console in %1$sSearch Statistics%2$s to track how your site is performing in search rankings and generate actionable reports. %3$s', this.$td),
					`<a href='${this.rootStore.aioseo.urls.aio.searchStatistics}#/settings'>`,
					'</a>',
					this.$links.getPlainLink(
						this.$constants.GLOBAL_STRINGS.learnMore,
						`${this.rootStore.aioseo.urls.aio.searchStatistics}#/settings`,
						true,
						false
					)
				)
			})
		}
	},
	computed : {
		requiredPlans () {
			return license.getPlansForFeature('search-statistics', 'seo-statistics')
		},
		requiredPlansString () {
			let string = 1 < this.requiredPlans
				? this.strings.thisFeatureRequires + ' ' + this.requiredPlans.join(', ')
				: this.strings.thisFeatureRequiresSingular + ' ' + this.requiredPlans.join(', ')

			string += '<br />'
			string += this.$links.getUpsellLink('search-statistics', this.$constants.GLOBAL_STRINGS.learnMore, 'webmaster-tools', true)

			return string
		}
	}
}
</script>