<template>
	<div>
		<core-card
			slug="analyzeNewCompetitor"
			hide-header
			no-slide
			:toggles="false"
		>
			<component
				:is="!optionsStore.internalOptions.internal.siteAnalysis.connectToken ? 'core-blur' : 'div'"
			>
				<slot />
			</component>

			<div
				v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
				class="aioseo-seo-site-score-cta"
			>
				<a
					href="#"
					@click.prevent="openPopup(rootStore.aioseo.urls.connect)"
				>{{ connectWithAioseo }}</a> {{ strings.toAnalyzeCompetitorSite }}
			</div>
		</core-card>

		<template
			v-if="optionsStore.internalOptions.internal.siteAnalysis.connectToken"
		>
			<slot name="competitor-results" />
		</template>
	</div>
</template>

<script>
import {
	useConnectStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { useSeoSiteScore } from '@/vue/composables'
import { SeoSiteScore } from '@/vue/mixins/SeoSiteScore'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
export default {
	setup () {
		const { strings } = useSeoSiteScore()

		return {
			connectStore : useConnectStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			strings
		}
	},
	components : {
		CoreBlur,
		CoreCard
	},
	mixins : [ SeoSiteScore ],
	data () {
		return {
			score : 0
		}
	},
	methods : {
		openPopup (url) {
			popup(
				url,
				this.connectWithAioseo,
				600,
				630,
				true,
				[ 'token' ],
				this.completedCallback,
				() => {}
			)
		},
		completedCallback (payload) {
			return this.connectStore.saveConnectToken(payload.token)
		}
	}
}
</script>