<template>
	<core-alert
		class="no-access"
		v-if="!rootStore.aioseo.user.unfilteredHtml"
		type="red"
		v-html="strings.unfilteredHtmlError"
	/>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useRootStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		CoreAlert
	},
	data () {
		return {
			strings : {
				unfilteredHtmlError : sprintf(
					// Translators: 1 - Learn more link.
					__('Your user account role does not have access to edit this field. %1$s', td),
					links.getDocLink(GLOBAL_STRINGS.learnMore, 'unfilteredHtml', true)
				)
			}
		}
	}
}
</script>