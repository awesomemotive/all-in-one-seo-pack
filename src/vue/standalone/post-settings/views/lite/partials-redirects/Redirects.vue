<template>
	<div>
		<redirects-lite
			v-if="'metabox' === $root.$data.screenContext"
			:noCoreCard="true"
			:parentComponentContext="parentComponentContext"
		/>

		<core-modal
			:show="postEditorStore.currentPost.redirects.modalOpen && 'sidebar' === $root.$data.screenContext"
			:classes="[ 'aioseo-redirects' ]"
			@close="postEditorStore.currentPost.redirects.modalOpen = false"
		>
			<template #headerTitle>
				{{ strings.modalHeader }}
			</template>

			<template #body>
				<div class="bd">
					<redirects-lite
						:noCoreCard="true"
						:parentComponentContext="parentComponentContext"
					/>
				</div>
			</template>
		</core-modal>

		<redirects-side-bar v-if="'modal' !== parentComponentContext" />
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import CoreModal from '@/vue/components/common/core/modal/Index'
import RedirectsSideBar from './../../RedirectsSideBar'
import RedirectsLite from '@/vue/pages/redirects/views/lite/redirects/Redirects'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreModal,
		RedirectsSideBar,
		RedirectsLite
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				modalHeader : __('Redirects', td)
			}
		}
	}
}
</script>