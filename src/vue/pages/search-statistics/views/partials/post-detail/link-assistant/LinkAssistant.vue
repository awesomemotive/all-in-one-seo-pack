<template>
	<div class="aioseo-search-statistics-link-assistant">
		<div class="links">
			<div>
				<span class="total">{{ links.inboundInternal || 0 }}</span>
				<svg-link-internal-inbound />
				<span class="name">{{ strings.inbound }}</span>
			</div>

			<div>
				<span class="total">{{ links.outboundInternal || 0 }}</span>
				<svg-link-internal-outbound />
				<span class="name">{{ strings.outbound }}</span>
			</div>

			<div>
				<span class="total">{{ links.external || 0 }}</span>
				<svg-link-external />
				<span class="name">{{ strings.external }}</span>
			</div>

			<div>
				<span class="total">{{ links.affiliate || 0 }}</span>
				<svg-link-affiliate />
				<span class="name">{{ strings.affiliate }}</span>
			</div>

			<div>
				<span class="total">{{ links.linkSuggestions || 0 }}</span>
				<svg-link-suggestion />
				<span class="name">{{ strings.suggestions }}</span>
			</div>
		</div>

		<div class="aioseo-card-footer">
			<a
				:href="rootStore.aioseo.urls.aio.linkAssistant"
				v-html="strings.linkOpportunities"
			/>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import SvgLinkAffiliate from '@/vue/components/common/svg/link/Affiliate'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkInternalInbound from '@/vue/components/common/svg/link/InternalInbound'
import SvgLinkInternalOutbound from '@/vue/components/common/svg/link/InternalOutbound'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		SvgLinkAffiliate,
		SvgLinkExternal,
		SvgLinkInternalInbound,
		SvgLinkInternalOutbound,
		SvgLinkSuggestion
	},
	props : {
		links : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : {
				inbound           : __('Inbound Links', td),
				outbound          : __('Outbound Links', td),
				affiliate         : __('Affiliate', td),
				external          : __('External', td),
				suggestions       : __('Link Suggestions', td),
				linkOpportunities : sprintf(
					// Translators: 1 - Right arrow.
					__('Linking Opportunities %1$s', td),
					'<span>&rarr;</span>'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-link-assistant {
	display: flex;
	flex-direction: column;

	.links {
		flex: 1;
		display: grid;
		grid-gap: 16px 8px;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		margin-bottom: 24px;

		.total {
			display: inline-flex;
			font-weight: 700;
			font-size: 16px;
			color: $black;
			margin-right: 17.75px;
		}

		.title {
			font-weight: 400;
			font-size: 14px;
		}

		svg {
			height: 10.5px;
			width: 12.5px;
			margin-right: 9.33px;

			&.aioseo-link-internal-inbound,
			&.aioseo-link-internal-outbound {
				color: $green;
			}

			&.aioseo-link-external {
				color: $blue;
			}

			&.aioseo-link-affiliate {
				color: $orange;
				width: 12px;
				height: 12px;
				margin-top: 2px;
			}

			&.aioseo-link-suggestion {
				color: $black2-hover;
				width: 13.33px;
				height: 6.67px;
			}
		}
	}
}
</style>