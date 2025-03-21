<template>
	<core-main
		:page-name="strings.pageName"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import Advanced from './Advanced'
import Archives from './Archives'
import AuthorSeo from './AuthorSeo'
import ContentTypes from './ContentTypes'
import CoreMain from '@/vue/components/common/core/main/Index'
import GlobalSettings from './GlobalSettings'
import Media from './Media'
import Taxonomies from './Taxonomies'

import { __ } from '@/vue/plugins/translations'
import { useRobotsTxt } from '@/vue/composables/RobotsTxt'

useRobotsTxt()

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Advanced,
		Archives,
		AuthorSeo,
		ContentTypes,
		CoreMain,
		GlobalSettings,
		Media,
		Taxonomies
	},
	data () {
		return {
			strings : {
				pageName : __('Search Appearance', td)
			}
		}
	}
}
</script>