<template>
	<div class="aioseo-access-control-lite">
		<core-card
			slug="accessControl"
		>
			<template #header>
				<span>{{ strings.accessControl }}</span>
				<core-pro-badge />
			</template>

			<template #tooltip>
				{{ strings.tooltip }}
			</template>

			<core-blur>
				<template
					v-for="role in getLiteRoles"
					:key="role.name"
				>
					<core-settings-row
						:name="role.label"
					>
						<template #content>
							<div
								class="toggle"
							>
								<base-toggle
									:disabled="true"
									:modelValue="true"
								>
									{{ strings.useDefaultSettings }}
								</base-toggle>
							</div>
						</template>
					</core-settings-row>
				</template>
			</core-blur>

			<cta
				:feature-list="[
					strings.granularControl,
					strings.wpRoles,
					strings.seoManagerRole,
					strings.seoEditorRole
				]"
				:cta-link="$links.getPricingUrl('access-control', 'access-control-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="$links.getUpsellUrl('access-control', null, $isPro ? 'pricing' : 'liteUpgrade')"
				align-top
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					{{ strings.tooltip }}
				</template>
			</cta>
		</core-card>
	</div>
</template>

<script>
import { merge } from 'lodash-es'
import { useAccessControl } from '@/vue/composables'
import { AccessControl } from '@/vue/pages/settings/mixins'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'
export default {
	setup () {
		const { strings, roles } = useAccessControl()

		return {
			composableStrings : strings,
			roles
		}
	},
	components : {
		CoreBlur,
		CoreCard,
		CoreProBadge,
		CoreSettingsRow,
		Cta
	},
	mixins : [ AccessControl ],
	data () {
		return {
			strings : merge(this.composableStrings, {
				wpRoles         : this.$t.__('WP Roles (Editor, Author)', this.$td),
				seoManagerRole  : this.$t.__('SEO Manager Role', this.$td),
				seoEditorRole   : this.$t.__('SEO Editor Role', this.$td),
				defaultSettings : this.$t.__('Default settings that just work', this.$td),
				granularControl : this.$t.__('Granular controls per role', this.$td),
				ctaButtonText   : this.$t.__('Unlock Access Control', this.$td),
				ctaHeader       : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Access Control is a %1$s Feature', this.$td),
					'PRO'
				)
			})
		}
	},
	computed : {
		getLiteRoles () {
			const roles = this.getRoles

			let roleNumber = 1
			while (8 > roles.length) {
				roles.push({
					label : this.$t.__('Custom Role', this.$td) + ' ' + roleNumber,
					name  : 'customRole' + roleNumber
				})
				roleNumber++
			}

			return roles
		}
	}
}
</script>

<style lang="scss">
.aioseo-access-control-lite {
	.aioseo-card {
		.content {
			position: relative;
			min-height: 600px;
		}
	}
}
</style>