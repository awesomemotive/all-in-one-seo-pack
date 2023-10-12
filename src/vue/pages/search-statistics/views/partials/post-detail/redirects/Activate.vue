<template>
	<div class="aioseo-search-statistics-redirects">
		<blur />

		<cta :type="4">
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				{{ strings.ctaDescription }}

				<br /><br />

				<base-button
					type="blue"
					tag="a"
					:href="rootStore.aioseo.urls.aio.redirects"
					size="small"
				>
					{{ strings.enableRedirects }}
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
				ctaHeader       : this.$t.__('Enable Redirects on Your Site', this.$td),
				ctaDescription  : this.$t.__('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', this.$td),
				enableRedirects : this.$t.__('Enable Redirects', this.$td)
			}
		}
	}
}
</script>