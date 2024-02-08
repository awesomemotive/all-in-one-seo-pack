<template>
	<div
		class="google-serp-wireframe-wrapper"
		:class="`google-serp-wireframe-wrapper--${device}`"
	>
		<div class="google-serp-wireframe">
			<svg-mobile-device-frame width="260"/>

			<div class="google-serp-wireframe__overflow-y">
				<div class="google-serp-wireframe__header">
					<div class="google-serp-wireframe__bullets">
						<div
							v-for="i in 3"
							:key="`bullet-${i}`"
						/>
					</div>

					<div class="google-serp-wireframe__address-bar">
						<svg-padlock width="25"/>

						<div class="placeholder"/>
					</div>
				</div>

				<div class="google-serp-wireframe__body">
					<div class="svg-logo-google-wrapper">
						<svg-logo-google width="60"/>
					</div>

					<div class="svg-search-wrapper google-serp-wireframe__search-bar">
						<div class="text-truncate">
							{{ searchText }}
						</div>

						<svg-search width="12"/>
					</div>

					<div class="svg-settings-wrapper">
						<svg-icon-settings width="18"/>
					</div>

					<div class="svg-dannie-profile-wrapper">
						<svg-dannie-profile width="26"/>
					</div>
				</div>

				<div class="google-serp-wireframe__footer">
					<div class="google-serp-wireframe__tabs">
						<div class="google-serp-wireframe__tabs__tab google-serp-wireframe__tabs__tab--all">
							<svg-icon-google-search width="18"/>

							{{ strings.all }}
						</div>

						<div
							v-for="i in 5"
							:key="`dot-${i}`"
							class="google-serp-wireframe__tabs__tab google-serp-wireframe__tabs__tab--double-placeholder"
						/>

						<div class="google-serp-wireframe__tabs__tab google-serp-wireframe__tabs__tab--placeholder"/>
					</div>

					<div class="google-serp-wireframe__divider"/>

					<div class="google-serp-wireframe__results">
						{{ strings.results }}
					</div>

					<div
						class="google-serp-wireframe__snippet"
						v-if="$slots['serp-snippet']"
					>
						<slot name="serp-snippet"/>

						<template v-if="'desktop' === device">
							<google-serp-snippet-placeholder
								v-for="i in 3"
								:key="`snippet-placeholder-${i}`"
							/>
						</template>

						<google-serp-snippet-placeholder v-else />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import GoogleSerpSnippetPlaceholder from './GoogleSerpSnippetPlaceholder.vue'
import SvgDannieProfile from '@/vue/components/common/svg/dannie/Profile'
import SvgIconGoogleSearch from '@/vue/components/common/svg/icon/GoogleSearch'
import SvgIconSettings from '@/vue/components/common/svg/Settings'
import SvgLogoGoogle from '@/vue/components/common/svg/logo/Google'
import SvgMobileDeviceFrame from '@/vue/components/common/svg/seo-preview/MobileDeviceFrame'
import SvgPadlock from '@/vue/components/common/svg/Padlock'
import SvgSearch from '@/vue/components/common/svg/Search'

export default {
	components : {
		GoogleSerpSnippetPlaceholder,
		SvgDannieProfile,
		SvgIconGoogleSearch,
		SvgIconSettings,
		SvgLogoGoogle,
		SvgMobileDeviceFrame,
		SvgPadlock,
		SvgSearch
	},
	props : {
		device : {
			type    : String,
			default : 'desktop'
		},
		searchText : {
			type    : String,
			default : ''
		}
	},
	data () {
		return {
			strings : {
				all     : this.$t.__('All', this.$td),
				results : this.$t.__('About 61,000,000,000 results (0.40 seconds)', this.$td)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.google-serp-wireframe {
	--px: 10px;
	--px-desktop: 20px;

	background-color: #fff;
	border-radius: 8px;
	overflow: hidden;
	position: relative;

	.google-serp-wireframe-wrapper--mobile & {
		border-radius: 40px 40px 0 0;
		margin: 0 auto;
		max-width: 260px;
		width: 100%;

		.aioseo-mobile-device-frame {
			color: $black;
			display: block;
			left: 0;
			object-fit: cover;
			position: absolute;
			width: 100%;
		}

		&__overflow-y {
			--mt: 35px;

			margin: var(--mt) 8px 0;
			height: calc(485px - var(--mt));
			overflow-y: auto;
			position: relative;
		}

		&__body {
			flex-wrap: wrap;
			gap: 8px;
			padding: 4px var(--px) 10px;

			.svg-logo-google-wrapper {
				order: 2;
			}

			.svg-search-wrapper {
				max-width: 100%;
				order: 4;
			}

			.svg-settings-wrapper {
				order: 1;
				visibility: hidden;
			}

			.svg-dannie-profile-wrapper {
				height: 20px;
				margin-left: auto;
				order: 3;
				width: 20px;
			}
		}

		&__search-bar {
			flex-direction: row-reverse;
			justify-content: flex-end;
		}

		&__tabs {
			padding: 0 0 0 var(--px);
		}

		&__snippet {
			padding: 20px var(--px) 0;

			.google-serp-snippet-placeholder {
				border-top: 5px solid $border;
				margin: 20px calc(var(--px) * -1) 0;
				padding-top: 20px;

				:deep(.google-serp-snippet-placeholder__main) {
					padding-left: var(--px);
					padding-right: var(--px);
				}
			}
		}

		&__header,
		&__results,
		.aioseo-icon-google-search {
			display: none;
		}
	}

	.aioseo-mobile-device-frame {
		display: none;
	}

	&__overflow-y {
		background-color: #fff;
		height: 435px;
	}

	&__header {
		align-items: center;
		background-color: $black;
		display: flex;
		gap: 23px;
		padding: 9px 24px;
	}

	&__body {
		align-items: center;
		display: flex;
		gap: 16px;
		padding: 13px var(--px-desktop);

		* {
			line-height: normal;
		}

		.svg-logo-google-wrapper {
			flex: 0 1 auto;
		}

		.svg-search-wrapper {
			flex: 1 1 100%;
			max-width: 400px;
		}

		.svg-settings-wrapper {
			flex: 0 1 auto;
			margin-left: auto;

			.aioseo-settings {
				color: $placeholder-color;
			}
		}

		.svg-dannie-profile-wrapper {
			border-radius: 50%;
			border: 1px solid $blue3;
			flex: 0 1 auto;
			height: 24px;
			overflow: hidden;
			width: 24px;

			.aioseo-dannie-profile {
				height: 100%;
				width: 100%;
			}
		}
	}

	&__bullets {
		display: flex;
		flex: 0 1 auto;
		gap: 10px;

		div {
			border-radius: 50%;
			height: 12px;
			width: 12px;

			&:nth-child(1) {
				background-color: $red3;
			}

			&:nth-child(2) {
				background-color: $orange;
			}

			&:nth-child(3) {
				background-color: $green2;
			}
		}
	}

	&__address-bar {
		align-items: center;
		background-color: #fff;
		border-radius: 120px;
		display: flex;
		flex: 1;
		gap: 14px;
		height: 35px;
		line-height: 35px;
		padding: 0 10px;

		.aioseo-padlock {
			color: $input-border;
		}

		.placeholder {
			background-color: $input-border;
			border-radius: 140px;
			height: 12px;
			max-width: 230px;
			width: 100%;
		}
	}

	&__search-bar {
		align-items: center;
		border-radius: 120px;
		box-shadow: 0 2px 8px #403C4333;
		color: $black;
		display: flex;
		font-size: 12px;
		gap: 8px;
		height: 30px;
		justify-content: space-between;
		line-height: 30px;
		padding: 0 14px;

		.aioseo-search {
			color: #4F86EC;
			min-width: 12px;
		}
	}

	&__tabs,
	&__results {
		margin: 0 auto;
		max-width: 660px;
		padding: 0 215px 0 var(--px-desktop);
		width: 100%;
	}

	&__tabs {
		align-items: center;
		display: flex;
		gap: 12px;
		overflow: hidden;

		&__tab {
			display: inline-flex;
			padding: 0 1px 5px;

			&--all {
				align-items: center;
				border-bottom: 2px solid #3676E0;
				color: #000;
				font-size: 12px;
				gap: 2px;
				position: relative;
			}

			&--double-placeholder,
			&--placeholder {
				&::before,
				&::after {
					background-color: $input-border;
					content: '';
					display: inline-block;
					height: 12px;
				}

				&::after {
					border-radius: 170px;
					width: 24px;
				}
			}

			&--double-placeholder {
				&::before {
					border-radius: 50%;
					margin-right: 3px;
					width: 12px;
				}
			}

			&--placeholder {
				margin-left: auto;
			}
		}
	}

	&__results {
		color: $placeholder-color;
		font-size: 12px;
		margin-top: 12px;
	}

	&__snippet {
		margin: 0 auto;
		max-width: 660px;
		padding: 20px var(--px-desktop) 0;
		width: 100%;

		.google-serp-snippet-placeholder {
			margin-top: 20px;
		}
	}

	&__divider {
		border-top: 1px solid $gray;
	}
}
</style>