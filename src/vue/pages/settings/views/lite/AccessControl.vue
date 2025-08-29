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
				:cta-link="links.getPricingUrl('access-control', 'access-control-upsell', null, 'liteUpgrade')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('access-control', null, 'liteUpgrade')"
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

<script setup>
import { computed } from 'vue'

import links from '@/vue/utils/links'
import { merge } from 'lodash-es'
import { useAccessControl } from '@/vue/composables/AccessControl'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	getRoles,
	strings : composableStrings
} = useAccessControl()

const strings = merge(composableStrings, {
	wpRoles         : __('WP Roles (Editor, Author)', td),
	seoManagerRole  : __('SEO Manager Role', td),
	seoEditorRole   : __('SEO Editor Role', td),
	defaultSettings : __('Default settings that just work', td),
	granularControl : __('Granular controls per role', td),
	ctaButtonText   : __('Unlock Access Control', td),
	ctaHeader       : sprintf(
		// Translators: 1 - "PRO".
		__('Access Control is a %1$s Feature', td),
		'PRO'
	)
})

const getLiteRoles = computed(() => {
	const roles = getRoles.value

	let roleNumber = 1
	while (8 > roles.length) {
		roles.push({
			label : __('Custom Role', td) + ' ' + roleNumber,
			name  : 'customRole' + roleNumber
		})
		roleNumber++
	}

	return roles
})
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