<template>
	<core-main
		:page-name="strings.pageName"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import CoreMain from '@/vue/components/common/core/main/Index'
import Locations from './Locations'
import Maps from './Maps'
import OpeningHours from './OpeningHours'
import Import from './Import'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreMain,
		Locations,
		Maps,
		OpeningHours,
		Import
	},
	data () {
		return {
			strings : {
				pageName : __('Local SEO', td)
			}
		}
	}
}
</script>