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
					:cta-link="$links.getPricingUrl('network-tools', 'network-sites-activation')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="$links.getUpsellUrl('network-tools', 'network-sites-activation', $isPro ? 'pricing' : 'liteUpgrade')"
				>
					<template #header-text>
						{{ strings.ctaHeader }}
					</template>
					<template #description>
						<!-- <required-plans addon="aioseo-redirects" /> -->
						{{ strings.networkDatabaseToolsDescription }}
					</template>
				</cta>
			</template>
		</core-wp-table>
	</div>
</template>

<script>
import { Network } from '@/vue/mixins'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
export default {
	mixins     : [ Network ],
	components : {
		CoreWpTable,
		Cta,
		SvgCircleCheckSolid
	},
	data () {
		return {
			bulkOptions : [
				{ label: this.$t.__('Activate License', this.$td), value: 'activate-license' },
				{ label: this.$t.__('Deactivate License', this.$td), value: 'deactivate-license' }
			],
			strings : {
				activate   : this.$t.__('Activate', this.$td),
				deactivate : this.$t.__('Deactivate', this.$td),
				visitSite  : this.$t.__('Visit Site', this.$td),
				dashboard  : this.$t.__('Dashboard', this.$td),
				ctaHeader  : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('This feature is not available in your current plan.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaButtonText                   : this.$t.__('Upgrade Your Plan and Unlock Network Tools', this.$td),
				networkDatabaseToolsDescription : this.$t.__('Unlock network-level tools to manage all your sites from one easy-to-use location. Manage your license key activations for each individual domain.', this.$td)
			}
		}
	},
	computed : {
		columns () {
			return [
				{
					slug  : 'domain',
					label : this.$t.__('Domain', this.$td)
				},
				{
					slug  : 'path',
					label : this.$t.__('Path', this.$td)
				},
				{
					slug  : 'primary_domain',
					label : this.$t.__('Alias Of', this.$td)
				},
				{
					slug  : 'activated',
					label : this.$t.__('Activated', this.$td),
					width : '90px'
				}
			]
		},
		rows () {
			return [
				{ blog_id: 1, path: '/', domain: 'aioseo.com' },
				{ blog_id: 2, path: '/', domain: 'wpbeginner.com' },
				{ blog_id: 3, path: '/', domain: 'wpforms.com' },
				{ blog_id: 4, path: '/', domain: 'optinmonster.com' },
				{ blog_id: 5, path: '/', domain: 'monsterinsights.com' },
				{ blog_id: 8, path: '/', domain: 'seedprod.com' },
				{ blog_id: 10, path: '/', domain: 'easydigitaldownloads.com' }
			]
		},
		totals () {
			return {
				total : this.rows.length,
				pages : 1,
				page  : 1
			}
		},
		filters () {
			return [
				{
					slug : 'all',
					name : this.$t.__('All', this.$td)
				},
				{
					slug : 'activated',
					name : this.$t.__('Activated', this.$td)
				},
				{
					slug : 'deactivated',
					name : this.$t.__('Deactivated', this.$td)
				}
			]
		}
	}
}
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