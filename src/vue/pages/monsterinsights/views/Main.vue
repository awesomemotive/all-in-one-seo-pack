<template>
	<core-main
		:page-name="strings.pageName"
		:show-tabs="false"
		:show-save-button="false"
	>
		<monsterinsights />
	</core-main>
</template>

<script>
import CoreMain from '@/vue/components/common/core/main/Index'
import Monsterinsights from './Monsterinsights'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreMain,
		Monsterinsights
	},
	data () {
		return {
			strings : {
				pageName : __('Analytics', td)
			}
		}
	}
}
</script>