<template>
	<div class="aioseo-search-statistics-link-assistant">
		<blur />

		<cta
			:type="4"
			hideBonus
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				{{ strings.ctaDescription }}

				<br /><br />

				<base-button
					type="blue"
					tag="a"
					:href="rootStore.aioseo.urls.aio.linkAssistant"
					size="small"
				>
					{{ strings.enableLinkAssistant }}
				</base-button>
			</template>
		</cta>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		Blur,
		Cta
	},
	data () {
		return {
			strings : {
				ctaHeader           : this.$t.__('Enable Link Assistant on Your Site', this.$td),
				ctaDescription      : this.$t.__('Get internal link suggestions for all your content including orphaned posts that have no internal links.', this.$td),
				enableLinkAssistant : this.$t.__('Enable Link Assistant', this.$td)
			}
		}
	}
}
</script>