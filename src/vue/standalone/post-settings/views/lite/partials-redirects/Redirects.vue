<template>
	<div>
		<redirects-lite
			v-if="'metabox' === $root._data.screenContext"
			:noCoreCard="true"
			:parentComponentContext="parentComponentContext"
		/>

		<core-modal-portal
			:classes="[ 'aioseo-redirects', 'modal' ]"
			v-if="postEditorStore.currentPost.redirects.modalOpen && 'sidebar' === $root._data.screenContext"
			@close="postEditorStore.toggleRedirectsModal"
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
		</core-modal-portal>

		<redirects-side-bar v-if="'modal' !== this.parentComponentContext" />
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import CoreModalPortal from '@/vue/components/common/core/modal/Portal'
import RedirectsSideBar from './../../RedirectsSideBar'
import RedirectsLite from '@/vue/pages/redirects/views/lite/redirects/Redirects'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreModalPortal,
		RedirectsSideBar,
		RedirectsLite
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				modalHeader : this.$t.__('Redirects', this.$td)
			}
		}
	}
}
</script>