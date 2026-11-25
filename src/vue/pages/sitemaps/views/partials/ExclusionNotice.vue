<template>
	<div class="aioseo-sitemap-exclusion-notices">
		<core-alert
			v-for="group in exclusions.groups"
			:key="group.cause"
			:id="getNoticeId(sitemapType, kind, group.cause, names)"
			class="aioseo-sitemap-exclusion-notice"
			type="yellow"
			size="medium"
		>
			{{ strings.causes[group.cause] }}

			<p class="aioseo-sitemap-exclusion-notice-objects">
				<template
					v-for="(object, index) in group.objects"
					:key="object.name"
				>
					<span v-if="index">, </span>

					<em>
						<a
							v-if="group.objectsAreLinks"
							v-safe-href="object.url"
						>{{ object.label }}</a>

						<template v-else>{{ object.label }}</template>
					</em>
				</template>
			</p>

			<p v-if="group.groupUrl">
				<a v-safe-href="group.groupUrl">{{ strings.globalRobotsMetaLink }}</a>
			</p>

			<p v-if="group.escapableByObject">
				{{ strings.perObjectAlternative }}
			</p>

			<p v-if="group.overridable">
				{{ strings.individuallyIndexed }}
			</p>
		</core-alert>
	</div>
</template>

<script>
import {
	exclusionCauses,
	useSitemapExclusions
} from '@/vue/pages/sitemaps/composables/SitemapExclusions'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { getExclusions, getNoticeId } = useSitemapExclusions()

		return {
			getExclusions,
			getNoticeId
		}
	},
	components : {
		CoreAlert
	},
	props : {
		sitemapType : {
			type     : String,
			required : true
		},
		kind : {
			type     : String,
			required : true
		},
		names : {
			type : Array,
			default () {
				return null
			}
		},
		// The same list the tab hands to core-post-type-options, so the notice never names an
		// object this tab does not offer.
		excluded : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			strings : {
				causes : {
					// Each sentence states its cause and the screen that sets it, not the steps to undo it —
					// the links beneath reach the control itself.
					[exclusionCauses.hidden]        : __('The following won\'t be included because Show in Search Results is turned off under Search Appearance:', td),
					[exclusionCauses.objectNoindex] : __('The following won\'t be included because No Index is enabled under Search Appearance:', td),
					[exclusionCauses.globalNoindex] : __('The following won\'t be included because No Index is enabled site-wide in Global Robots Meta:', td)
				},
				globalRobotsMetaLink : __('Change No Index in Global Robots Meta under Search Appearance → Advanced', td),
				perObjectAlternative : __('Any of these that uses its own robots settings under Search Appearance will be included.', td),
				individuallyIndexed  : __('Individual posts and terms that are set to appear in search results are still included.', td)
			}
		}
	},
	computed : {
		exclusions () {
			return this.getExclusions(this.kind, this.names, this.excluded)
		}
	}
}
</script>

<style lang="scss">
.aioseo-sitemap-exclusion-notice {
	margin-top: 16px;

	p {
		margin: 8px 0 0;
	}

	.aioseo-sitemap-exclusion-notice-objects {
		margin-top: 4px;
	}
}
</style>