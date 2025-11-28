<template>
	<div>
		<!-- Show upsell for Lite users -->
		<div v-if="licenseStore.isUnlicensed">
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

		<!-- Show actual functionality for Basic+ users -->
		<div v-if="!licenseStore.isUnlicensed">
			<div class="aioseo-tab-content aioseo-redirects metabox" v-if="'metabox' === $root.$data.screenContext">
				<core-add-redirection
					v-if="!hasRedirects"
					:key="postEditorStore.currentPost.permalinkPath"
					:disable-source="true"
					:post-id="postId"
					:post-status="postEditorStore.currentPost.postStatus"
					:url="getUrl"
					@added-redirect="reload"
				/>
				<div v-if="hasRedirects">
					<redirects-table
						:disable-source="true"
						:exclude-columns="['group']"
						:show-bulk-actions="false"
						:show-header="false"
						:show-table-footer="false"
					/>
				</div>
			</div>

			<core-modal
				:show="postEditorStore.currentPost.redirects.modalOpen && 'sidebar' === $root.$data.screenContext"
				:classes="[ 'aioseo-redirects' ]"
				@close="postEditorStore.currentPost.redirects.modalOpen = false"
				allow-overflow
			>
				<template #headerTitle>
					{{ strings.modalHeader }}
				</template>

				<template #body>
					<div class="bd">
						<core-add-redirection
							v-if="!hasRedirects"
							:key="postEditorStore.currentPost.permalinkPath"
							:disable-source="true"
							:post-id="postId"
							:post-status="postEditorStore.currentPost.postStatus"
							:url="getUrl"
							@added-redirect="reload"
						/>
						<div v-if="hasRedirects">
							<redirects-table
								:disable-source="true"
								:exclude-columns="['group']"
								:show-bulk-actions="false"
								:show-header="false"
								:show-table-footer="false"
							/>
						</div>
					</div>
				</template>
			</core-modal>

			<redirects-side-bar v-if="'modal' !== parentComponentContext" />
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRedirectsStore,
	useLicenseStore
} from '@/vue/stores'

import CoreAddRedirection from '@/vue/components/common/core/add-redirection/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import RedirectsSideBar from './../../RedirectsSideBar'
import RedirectsLite from '@/vue/pages/redirects/views/lite/redirects/Redirects'
import RedirectsTable from '@/vue/pages/redirects/views/lite/redirects/Table'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			postEditorStore : usePostEditorStore(),
			redirectsStore  : useRedirectsStore()
		}
	},
	components : {
		CoreAddRedirection,
		CoreModal,
		RedirectsSideBar,
		RedirectsLite,
		RedirectsTable
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
	},
	computed : {
		getUrl () {
			return { url: this.postEditorStore.currentPost.permalinkPath, showOptions: true }
		},
		hasRedirects () {
			return this.redirectsStore.rows.length
		},
		postId () {
			return 'post' === this.postEditorStore.currentPost.context ? parseInt(this.postEditorStore.currentPost.id) : 0
		}
	},
	methods : {
		reload () {
			this.redirectsStore.getPostRedirects()
		}
	}
}
</script>

<style lang="scss">
.aioseo-redirects.metabox {
	padding: 0 !important;
	.aioseo-wp-table {
		.wp-table {
			tr.edit-row td {
				padding: 0 20px;
			}
		}
	}

	.aioseo-add-redirection.edit-url {
		margin-bottom: 0;
	}
}
.aioseo-redirects.aioseo-modal {
	.modal-body {
		padding: 20px;
	}
}

.aioseo-alert.permalink {
	margin-bottom: 15px;
}
</style>