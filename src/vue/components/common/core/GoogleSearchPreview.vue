<template>
	<div class="aioseo-google-search-preview">
		<div class="google-post">
			<div class="domain">
				<slot name="domain">
					{{ domain }}
				</slot>
			</div>

			<div class="site-title">
				{{ truncate(title, 100) }}
			</div>

			<div class="meta-description">
				{{ truncate(description) }}
			</div>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { truncate } from '@/vue/utils/html'

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	props : {
		title : {
			type     : String,
			required : true
		},
		description : {
			type     : String,
			required : true
		},
		domain : {
			type : String,
			default () {
				const rootStore = useRootStore()
				return rootStore.aioseo.urls.mainSiteUrl
			}
		},
		separator : String
	},
	methods : {
		truncate
	}
}
</script>

<style lang="scss" scoped>
.aioseo-google-search-preview {
	align-items: center;
	display: flex;
	justify-content: center;

	.google-post {
		background-color: #fff;
		border: 1px solid $border;
		display: flex;
		flex-direction: column;
		gap: 8px;
		padding: 16px;
		width: 100%;

		.domain,
		.site-title,
		.meta-description {
			font-family: $font-family;
			font-style: normal;
			font-weight: 400;
			line-height: 1.4;
			letter-spacing: normal;
			margin: 0;
			padding: 0;
			text-transform: none;
		}

		.domain {
			color: $black3;
			font-size: 14px;
			line-height: 22px;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		.site-title {
			color: #1A0DAB;
			font-size: 16px;
			line-height: 24px;
		}

		.meta-description {
			color: #4d5156;
			font-size: 14px;
			line-height: 22px;
			max-width: 600px;
			width: 100%;
			overflow-wrap: break-word;

			&:empty {
				display: none;
			}
		}
	}
}
</style>

<style lang="scss">
#wpwrap {
	.edit-post-sidebar,
	.editor-post-publish-panel {
		.google-post {
			gap: 8px;
			padding: 16px;

			.site-title {
				font-size: 16px;
				line-height: 24px;
			}

			.domain,
			.meta-description {
				font-size: 14px;
				line-height: 22px;
			}
		}
	}

	.aioseo-modal-content {
		.google-post {
			.domain,
			.meta-description {
				font-size: 14px;
			}

			.site-title {
				font-size: 20px;
			}
		}
	}
}
</style>