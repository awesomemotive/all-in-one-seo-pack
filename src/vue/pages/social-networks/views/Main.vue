<template>
	<core-main
		:page-name="strings.pageName"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import CoreMain from '@/vue/components/common/core/main/Index'
import Facebook from './Facebook'
import Pinterest from './Pinterest'
import SocialProfiles from './SocialProfiles'
import Twitter from './Twitter'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreMain,
		Facebook,
		Pinterest,
		SocialProfiles,
		Twitter
	},
	data () {
		return {
			strings : {
				pageName : __('Social Networks', td)
			}
		}
	}
}
</script>