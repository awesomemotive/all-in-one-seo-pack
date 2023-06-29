<template>
	<core-alert
		class="no-access"
		v-if="!rootStore.aioseo.user.unfilteredHtml"
		type="red"
		v-html="strings.unfilteredHtmlError"
	/>
</template>
<script>
import {
	useRootStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
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
				unfilteredHtmlError : this.$t.sprintf(
					// Translators: 1 - Learn more link.
					this.$t.__('Your user account role does not have access to edit this field. %1$s', this.$td),
					this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'unfilteredHtml', true)
				)
			}
		}
	}
}
</script>