<template>
	<div class="aioseo-search-statistics-redirects">
		<blur />

		<cta :type="4">
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				{{ strings.ctaDescription }}

				<base-button
					type="blue"
					tag="a"
					:href="rootStore.aioseo.urls.aio.redirects"
					size="small"
				>
					{{ strings.updateRedirects }}
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

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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
				ctaHeader       : __('Update Redirects to the latest version', td),
				ctaDescription  : __('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', td),
				updateRedirects : __('Update Redirects', td)
			}
		}
	}
}
</script>