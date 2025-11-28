<template>
	<div class="aioseo-redirects-blur">
		<core-blur v-if="!noCoreCard">
			<core-card
				slug="addNewRedirection"
				:header-text="strings.addNewRedirection"
				:noSlide="true"
			>
				<upsell-add-redirection />
			</core-card>
		</core-blur>

		<core-blur v-if="noCoreCard">
			<upsell-add-redirection />
		</core-blur>

		<core-blur v-if="!noCoreCard">
			<core-wp-table
				:filters="[]"
				:totals="{
					total : 0,
					pages : 0,
					page  : 1
				}"
				:columns="columns"
				:rows="[]"
				:search-label="strings.searchUrls"
				:additional-filters="additionalFilters"
			/>
		</core-blur>

		<cta
			:cta-link="links.getPricingUrl('redirects', 'redirects-upsell', parentComponentContext ? parentComponentContext : null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			align-top
			:learn-more-link="links.getUpsellUrl('redirects', parentComponentContext ? parentComponentContext : null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="[
				strings.serverRedirects,
				strings.automaticRedirects,
				strings.redirectMonitoring,
				strings.monitoring404,
				strings.fullSiteRedirects,
				strings.siteAliases
			]"
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>

			<template #description>
				{{ strings.redirectsDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import license from '@/vue/utils/license'
import {
	useRedirectsStore,
	useRootStore,
	useLicenseStore
} from '@/vue/stores'

import {
	GLOBAL_STRINGS,
	REDIRECT_GROUPS,
	REDIRECT_QUERY_PARAMS,
	REDIRECT_TYPES
} from '@/vue/plugins/constants'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import UpsellAddRedirection from './UpsellAddRedirection'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			license,
			links,
			redirectsStore : useRedirectsStore(),
			rootStore      : useRootStore(),
			licenseStore   : useLicenseStore()
		}
	},
	components : {
		CoreBlur,
		CoreCard,
		CoreWpTable,
		Cta,
		UpsellAddRedirection
	},
	props : {
		noCoreCard : Boolean
	},
	data () {
		return {
			strings : {
				addNewRedirection    : __('Add New Redirect', td),
				ctaButtonText        : __('Unlock Redirects', td),
				ctaHeader            : __('Redirects is a PRO Feature', td),
				serverRedirects      : __('Fast Server Redirects', td),
				automaticRedirects   : __('Automatic Redirects', td),
				redirectMonitoring   : __('Redirect Monitoring', td),
				monitoring404        : __('404 Monitoring', td),
				fullSiteRedirects    : __('Full Site Redirects', td),
				siteAliases          : __('Site Aliases', td),
				redirectsDescription : __('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', td),
				sourceUrl            : __('Source URL', td),
				sourceUrlDescription : sprintf(
					// Translators: 1 - Oening link tag, 2 - Closing link tag.
					__('Enter a relative URL to redirect from or start by typing in page or post title, slug or ID. You can also use regex (%1$s)', td),
					links.getDocLink(__('what\'s this?', td), 'redirectManagerRegex')
				),
				targetUrl            : __('Target URL', td),
				targetUrlDescription : __('Enter a URL or start by typing a page or post title, slug or ID.', td),
				addUrl               : __('Add URL', td),
				addRedirect          : __('Add Redirect', td),
				advancedSettings     : __('Advanced Settings', td),
				searchUrls           : __('Search URLs', td)
			},
			REDIRECT_TYPES,
			REDIRECT_QUERY_PARAMS
		}
	},
	computed : {
		sourceUrl () {
			return {
				id          : null,
				url         : null,
				regex       : false,
				ignoreSlash : false,
				ignoreCase  : false,
				errors      : [],
				warnings    : [],
				showOptions : true
			}
		},
		columns () {
			const columns = [
				{
					slug  : 'source_url',
					label : __('Source URL', td)
				},
				{
					slug  : 'target_url',
					label : __('Target URL', td)
				},
				{
					slug  : 'hits',
					label : __('Hits', td),
					width : '97px'
				},
				{
					slug  : 'type',
					label : __('Type', td),
					width : '100px'
				},
				{
					slug  : 'group',
					label : __('Group', td),
					width : '150px'
				},
				{
					slug  : 'enabled',
					label : GLOBAL_STRINGS.enabled,
					width : '80px'
				}
			]

			return columns
		},
		additionalFilters () {
			return [
				{
					label   : __('Filter by Group', td),
					name    : 'group',
					options : [ { label: __('All Groups', td), value: 'all' } ].concat(REDIRECT_GROUPS)
				}
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-redirects-blur {
	position: relative;
	min-height: 700px;

	@media (min-width: 1024px) {
		min-height: 550px;
	}

	&[parentcomponentcontext="metabox"] {
		.aioseo-cta.floating {
			top: 0 !important;
		}
	}
}
</style>