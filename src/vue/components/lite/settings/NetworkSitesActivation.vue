<template>
	<div class="aioseo-settings-network-sites-activation">
		<core-wp-table
			:columns="columns"
			:rows="rows"
			:totals="totals"
			:filters="filters"
			:bulk-options="bulkOptions"
			blur-rows
			disable-table
		>
			<template #domain="{ row }">
				<span>
					{{ row.domain }}
				</span>

				<div class="row-actions">
					<span>
						<a
							class="activate"
							href="#"
						>
							<span>{{ strings.activate }}</span>
						</a> |
					</span>

					<span>
						<a
							class="view-site"
							href="#"
							target="_blank"
						>
							<span>{{ strings.visitSite }}</span>
						</a> |
					</span>

					<span>
						<a
							class="dashboard"
							href="#"
							target="_blank"
						>
							<span>{{ strings.dashboard }}</span>
						</a>
					</span>
				</div>
			</template>

			<template #activated>
				<span>
					<svg-circle-check-solid />
				</span>
			</template>

			<template #cta>
				<cta
					:cta-link="links.getPricingUrl('network-tools', 'network-sites-activation', null, 'liteUpgrade')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="links.getUpsellUrl('network-tools', 'network-sites-activation', 'liteUpgrade')"
				>
					<template #header-text>
						{{ strings.ctaHeader }}
					</template>
					<template #description>
						<required-plans :core-feature="[ 'tools', 'network-tools-site-activation' ]" />
						{{ strings.networkDatabaseToolsDescription }}
					</template>
				</cta>
			</template>
		</core-wp-table>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import links from '@/vue/utils/links'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const bulkOptions = [
	{ label: __('Activate License', td), value: 'activate-license' },
	{ label: __('Deactivate License', td), value: 'deactivate-license' }
]

const strings = {
	activate   : __('Activate', td),
	deactivate : __('Deactivate', td),
	visitSite  : __('Visit Site', td),
	dashboard  : __('Dashboard', td),
	ctaHeader  : sprintf(
		// Translators: 1 - "Elite".
		__('Domain Activations is an %1$s Feature', td),
		'Elite'
	),
	ctaButtonText                   : __('Unlock Domain Activations', td),
	networkDatabaseToolsDescription : __('Unlock network-level tools to manage all your sites from one easy-to-use location. Manage your license key activations for each individual domain.', td)
}

const columns = computed(() => {
	return [
		{
			slug  : 'domain',
			label : __('Domain', td)
		},
		{
			slug  : 'path',
			label : __('Path', td)
		},
		{
			slug  : 'primary_domain',
			label : __('Alias Of', td)
		},
		{
			slug  : 'activated',
			label : __('Activated', td),
			width : '90px'
		}
	]
})

const rows = computed(() => {
	return [
		{ blog_id: 1, path: '/', domain: 'aioseo.com' },
		{ blog_id: 2, path: '/', domain: 'wpbeginner.com' },
		{ blog_id: 3, path: '/', domain: 'wpforms.com' },
		{ blog_id: 4, path: '/', domain: 'optinmonster.com' },
		{ blog_id: 5, path: '/', domain: 'monsterinsights.com' },
		{ blog_id: 8, path: '/', domain: 'seedprod.com' },
		{ blog_id: 10, path: '/', domain: 'easydigitaldownloads.com' }
	]
})

const totals = computed(() => {
	return {
		total : rows.value.length,
		pages : 1,
		page  : 1
	}
})

const filters = computed(() => {
	return [
		{
			slug : 'all',
			name : __('All', td)
		},
		{
			slug : 'activated',
			name : __('Activated', td)
		},
		{
			slug : 'deactivated',
			name : __('Deactivated', td)
		}
	]
})
</script>

<style lang="scss">
.aioseo-settings-network-sites-activation {
	.aioseo-wp-table {
		.manage-column {
			&.activated {
				text-align: center;
			}
		}

		.row-actions {
			.deactivate {
				color: $red;
			}
		}
	}

	svg.aioseo-circle-check-solid {
		width: 20px;
		height: 20px;
		color: $green;
	}

	svg.aioseo-circle-close-solid {
		width: 20px;
		height: 20px;
		color: $red;
	}
}
</style>