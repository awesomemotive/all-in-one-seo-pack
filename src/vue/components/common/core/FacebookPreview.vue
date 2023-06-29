<template>
	<div class="aioseo-facebook-preview">
		<div class="facebook-post">
			<div class="facebook-header">
				<div class="profile-photo">
					<svg-dannie-profile />
				</div>
				<div class="poster">
					<div class="poster-name">
						{{ appName }}
					</div>
					<div class="poster-date">
						{{ date }}
					</div>
				</div>
				<div class="ellipsis">
					<div></div>
					<div></div>
					<div></div>
				</div>
			</div>
			<div
				class="facebook-content"
				:class="{ vertical: isVerticalImage }"
			>
				<base-img
					:debounce="false"
					:src="image"
					@images="results => images = results"
					class="facebook-content__image"
				/>

				<div v-if="loading" class="loader">
					<core-loader />
				</div>

				<div class="facebook-site-description">
					<div class="site-domain">
						<slot name="site-url">
							{{ rootStore.aioseo.urls.domain }}
						</slot>
					</div>

					<div class="site-title">
						{{ truncate(title, 60) }}
					</div>

					<div class="site-description">
						{{ truncate(description, 110) }}
					</div>
				</div>
			</div>

			<div class="facebook-footer"></div>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { truncate } from '@/vue/utils/html'
import BaseImg from '@/vue/components/common/base/Img'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgDannieProfile from '@/vue/components/common/svg/dannie/Profile'

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		BaseImg,
		CoreLoader,
		SvgDannieProfile
	},
	props : {
		description : {
			type     : String,
			required : true
		},
		image   : String,
		loading : {
			type    : Boolean,
			default : false
		},
		title : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			images : {}
		}
	},
	computed : {
		appName () {
			return import.meta.env.VITE_NAME
		},
		date () {
			const today = new Date()
			return today.toLocaleString('default', { month: 'long' }) + ' ' + today.getDate()
		},
		isVerticalImage () {
			const image = this.images[this.image]

			if (!image) {
				return false
			}

			return image.vertical
		}
	},
	methods : {
		truncate
	}
}
</script>

<style lang="scss" scoped>
.aioseo-facebook-preview {
	--primary-text: #050505;
	--secondary-text: #65676B;

	background-color: $white2;
	padding: 20px;
	display: flex;
	align-items: center;
	justify-content: center;

	.facebook-post {
		background-color: #fff;
		border-radius: 8px;
		border: 1px solid $border;
		max-width: 525px;
		width: 100%;

		.facebook-header {
			height: 65px;
			padding: 0 12px;
			display: flex;
			align-items: center;

			.profile-photo {
				overflow: hidden;
				width: 40px;
				height: 40px;
				border: 1px solid $border;
				border-radius: 50%;

				svg {
					height: 100%;
					width: 100%;
				}
			}

			.poster {
				margin-left: 10px;
				flex: 1 0 auto;

				.poster-name {
					font-size: 15px;
					color: var(--primary-text);
					font-weight: 500;
				}

				.poster-date {
					color: var(--secondary-text);
					font-size: 13px;
				}
			}

			.ellipsis {
				display: inline-flex;
				gap: 4px;

				div {
					background-color: #5E666F;
					width: 4px;
					height: 4px;
					border-radius: 50%;
				}
			}
		}

		.facebook-content {
			align-items: center;
			background-color: $white2;
			display: flex;
			flex-direction: row;

			.facebook-content__image {
				flex: 0 1 139px;
				max-height: 210px;
				min-width: 139px;
				object-fit: cover;
				width: 100%;
			}

			.loader {
				max-width: 158px;
				width: 100%;
				align-self: stretch;
				background-color: $white3;
				position: relative;

				:deep() {
					.aioseo-loading-spinner {
						top: 0;
						bottom: 0;
						left: 0;
						right: 0;
						margin: auto;
					}
				}
			}

			&.vertical {
				display: block;

				.facebook-content__image {
					max-height: 260px;
				}

				.loader {
					max-width: 100%;
					height: 100px;
				}
			}

			.facebook-site-description {
				display: flex;
				flex-direction: column;
				flex: 1 1 auto;
				gap: 2px;
				justify-content: center;
				padding: 10px 12px;

				.site-domain,
				.site-title,
				.site-description {
					font-family: $font-family;
					font-style: normal;
					line-height: 1.4;
					letter-spacing: normal;
					margin: 0;
					padding: 0;
					text-transform: none;
				}

				.site-domain {
					color: var(--secondary-text);
					font-size: 13px;
					font-weight: 400;
					overflow: hidden;
					text-overflow: ellipsis;
					text-transform: uppercase;
					white-space: nowrap;
				}

				.site-title {
					color: var(--primary-text);
					font-size: 17px;
					font-weight: 600;
				}

				.site-description {
					color: var(--secondary-text);
					font-size: 14px;
					font-weight: 400;
				}
			}
		}

		.facebook-footer {
			height: 24px;
		}
	}
}
</style>