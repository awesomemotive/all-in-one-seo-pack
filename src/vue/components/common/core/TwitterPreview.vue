<template>
	<div class="aioseo-twitter-preview">
		<div class="twitter-post">
			<div class="twitter-header">
				<div class="profile-photo">
					<svg-dannie-profile />
				</div>
				<div class="poster">
					<div class="poster-name">
						{{ appName }}
					</div>
					<div class="poster-username">
						@aioseopack
					</div>
				</div>
			</div>
			<div
				class="twitter-container"
				:class="!image ? 'summary' : getCard"
			>
				<div class="twitter-content">
					<div
						class="twitter-image-preview"
						:style="{
							backgroundImage: 'summary' === getCard && canShowImage ? `url('${image}')` : ''
						}"
					>
						<svg-book
							v-if="!loading && (!image || !canShowImage)"
						/>
						<core-loader
							v-if="loading"
						/>
						<base-img
							v-show="'summary_large_image' === getCard && canShowImage"
							:src="image"
							:debounce="false"
							@can-show="maybeCanShow"
						/>
					</div>

					<div class="twitter-site-description">
						<div class="site-domain">
							<slot name="site-url">
								{{ rootStore.aioseo.urls.domain }}
							</slot>
						</div>

						<div class="site-title">
							{{ truncate(title, 70) }}
						</div>

						<div class="site-description">
							{{ truncate(description, 105) }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { truncate } from '@/vue/utils/html'
import BaseImg from '@/vue/components/common/base/Img'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgBook from '@/vue/components/common/svg/Book'
import SvgDannieProfile from '@/vue/components/common/svg/dannie/Profile'

export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseImg,
		CoreLoader,
		SvgBook,
		SvgDannieProfile
	},
	props : {
		card        : String,
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
			canShowImage : false
		}
	},
	computed : {
		appName () {
			return import.meta.env.VITE_NAME
		},
		getCard () {
			if ('default' === this.card) {
				return this.optionsStore.options.social.twitter.general.defaultCardType
			}

			return this.card
		}
	},
	methods : {
		maybeCanShow (canShow) {
			this.canShowImage = canShow
		},
		truncate
	}
}
</script>

<style lang="scss" scoped>
.aioseo-twitter-preview {
	--primary-text: #0f1419;
	--secondary-text: #536471;
	--tertiary-text: #cfd9de;

	align-items: center;
	background-color: $white2;
	display: flex;
	justify-content: center;
	padding: 20px;

	.twitter-post {
		background-color: #fff;
		border-radius: 16px;
		border: 1px solid $border;
		max-width: 500px;
		width: 100%;

		.twitter-header {
			height: 65px;
			padding: 0 18px;
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
					font-weight: 600;
				}

				.poster-username {
					color: var(--secondary-text);
					font-weight: 500;
					font-size: 13px;
				}
			}
		}

		.twitter-container {
			margin-bottom: 0;
			padding: 0 20px 20px;

			&.summary {
				.twitter-content {
					flex-direction: row;

					.twitter-image-preview {
						align-items: center;
						background-color: #f7f9f9;
						background-position: center center;
						background-repeat: no-repeat;
						background-size: cover;
						border-right: 1px solid var(--tertiary-text);
						display: flex;
						justify-content: center;
						min-height: 127px;
						min-width: 127px;

						svg.aioseo-book {
							width: 50px;
							color: var(--secondary-text);
						}
					}
				}
			}

			&.summary_large_image {
				.twitter-image-preview {
					overflow: hidden;
					padding-top: 56.25%;
					position: relative;

					img {
						height: 100%;
						left: 0;
						object-fit: cover;
						position: absolute;
						top: 0;
						width: 100%;
					}

					svg.aioseo-book {
						display: none;
					}
				}
			}

			.twitter-content {
				border-radius: 16px;
				overflow: hidden;
				display: flex;
				flex-direction: column;
				border: 1px solid var(--tertiary-text);

				.twitter-site-description {
					display: flex;
					flex-direction: column;
					gap: 2px;
					justify-content: center;
					padding: 12px;

					.site-domain,
					.site-title,
					.site-description {
						font-family: $font-family;
						font-style: normal;
						font-weight: 400;
						line-height: 1.4;
						letter-spacing: normal;
						margin: 0;
						padding: 0;
						text-transform: none;
						overflow-wrap: break-word;
						word-break: break-word;
					}

					.site-domain {
						color: var(--secondary-text);
						font-size: 13px;
						overflow: hidden;
						text-overflow: ellipsis;
						white-space: nowrap;
					}

					.site-title {
						color: var(--primary-text);
						font-size: 17px;
					}

					.site-description {
						color: var(--secondary-text);
						font-size: 14px;
					}
				}
			}
		}
	}
}
</style>