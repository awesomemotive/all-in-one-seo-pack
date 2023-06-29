<template>
	<div class="aioseo-setup-wizard-container">
		<div class="setup-wizard-bg">
			<svg-setup-wizard-bg />
		</div>

		<div class="getting-started-wrapper">
			<div class="text">
				<p class="how-to-get-started">{{ strings.howToGetStarted }}</p>
				<h2>{{ strings.welcomeToAio }}</h2>
				<p class="welcome-text">{{ strings.welcomeText }}</p>

				<grid-row
					class="wizard-actions"
				>
					<grid-column
						lg="7"
						first-md
						text-xs="center"
						text-lg="left"
					>
						<base-button
							type="green"
							tag="a"
							:href="rootStore.aioseo.urls.aio.wizard"
						>
							<svg-rocket /> {{ strings.launchSetupWizard }}&nbsp;&rarr;
						</base-button>
					</grid-column>

					<grid-column
						lg="5"
						last-md
						text-xs="center"
						text-lg="left"
					>
						<svg-book />
						<a
							class="text-white"
							:href="$links.getDocUrl('quickStartGuide')"
							target="_blank"
						>
							{{ strings.readSetupGuide }}
						</a>
					</grid-column>
				</grid-row>
			</div>
			<div class="video">
				<div class="wrapper">
					<iframe
						title="Getting Started Video"
						src="https://www.youtube.com/embed/fYPmIMSR9ik?rel=0"
						frameborder="1"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowfullscreen
						rel="0"
					/>
				</div>
			</div>
		</div>

		<a
			v-if="!disableClose"
			class="close-wizard"
			href="#"
			@click.prevent="settingsStore.hideSetupWizard"
		>
			<svg-close
				@click="settingsStore.hideSetupWizard"
			/>
		</a>
	</div>
</template>

<script>
import {
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgBook from '@/vue/components/common/svg/Book'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgRocket from '@/vue/components/common/svg/Rocket'
import SvgSetupWizardBg from '@/vue/components/common/svg/SetupWizardBg'
export default {
	setup () {
		return {
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		GridColumn,
		GridRow,
		SvgBook,
		SvgClose,
		SvgRocket,
		SvgSetupWizardBg
	},
	props : {
		disableClose : Boolean
	},
	data () {
		return {
			strings : {
				howToGetStarted : this.$t.__('How to Get Started', this.$td),
				welcomeToAio    : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('Welcome to %1$s', this.$td),
					import.meta.env.VITE_NAME
				),
				welcomeText : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('Thank you for choosing the best WordPress SEO plugin. %1$s default settings works great out of the box. We created the setup wizard to guide you through some important configuration settings & custom-tailored SEO best practices for your site to help you improve rankings.', this.$td),
					import.meta.env.VITE_NAME
				),
				launchSetupWizard : this.$t.__('Launch the Setup Wizard', this.$td),
				readSetupGuide    : this.$t.__('Read the Setup Guide', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-setup-wizard-container {
	margin-block: var(--aioseo-gutter);
	padding: calc(var(--aioseo-gutter) * 2);
	color: #fff;
	position: relative;
	background-color: $blue;

	p {
		color: #fff;
	}

	.getting-started-wrapper {
		position: relative;
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 20px;

		.text {
			max-width: 534px;
		}

		.video {
			flex: 0 0 533px;

			.wrapper {
				padding-bottom: 56.25%;
				margin-bottom: 0;
				position: relative;
				height: 0;

				iframe {
					width: 100%;
					height: 100%;
					position: absolute;
					top: 0;
					left: 0;
				}
			}
		}

		@media only screen and (max-width: 1350px) {
			.video {
				flex: 0 0 593px;
				margin: 20px;
				align-self: center;

				.wrapper {
					margin-bottom: 0;
				}
			}

			.wizard-actions {
				.aioseo-col {
					flex-basis: 100%;
					max-width: 100%;
					justify-content: center;
				}
			}
		}

		@media only screen and (max-width: 1300px) {
			flex-direction: row;
			flex-wrap: wrap;

			.video {
				margin: 0;
			}

			.video,
			.text {
				flex-basis: 100%;
				width: 100%;
			}

			.wizard-actions {
				justify-content: center;

				.aioseo-col {
					flex-basis: inherit;
					max-width: inherit;
					justify-content: center;
				}
			}
		}

		@media only screen and (max-width: 782px) {
			.wizard-actions {
				.aioseo-col {
					flex-basis: 100%;
					max-width: 100%;
					justify-content: center;
				}
			}
		}
	}

	.aioseo-row {
		position: relative;
		z-index: 1;
	}

	.wizard-actions {

		.aioseo-col {
			display: flex;
			align-items: center;

			&:first-child {
				flex-basis: auto;
			}
		}

		.aioseo-button {
			height: 40px;
			font-size: 14px;
			padding: 0 16px;

			svg {
				width: 14px;
				height: 14px;
				margin-right: 10px;
			}
		}
	}

	.setup-wizard-bg {
		width: 100%;
		height: 100%;
		overflow: hidden;
		z-index: 0;
		position: absolute;
		top: 0;
		left: 0;

		svg.aioseo-setup-wizard-bg {
			width: auto;
			height: 100%;

			rect {
				width: auto;
				height: 100%;
			}
		}
	}

	.close-wizard {
		color: #fff;
		width: 20px;
		height: 20px;
		position: absolute;
		right: 20px;
		top: 20px;
		z-index: 1;
		display: flex;
		align-items: center;
		justify-content: center;

		&:hover {
			color: darken(#fff, 20%);
		}

		svg.aioseo-close {
			width: 12px;
			height: 12px;
			cursor: pointer;
			color: #fff;

			&:hover {
				color: $gray2;
			}
		}
	}

	p.how-to-get-started {
		font-size: 16px;
		line-height: 24px;
		margin: 0;
	}

	p.welcome-text {
		font-size: 14px;
		line-height: 24px;
		margin: 0 0 32px;
	}

	h2 {
		color: #fff;
		font-size: 28px;
		line-height: 40px;
		margin-bottom: 16px;
	}

	a {
		color: #fff;
	}

	svg.aioseo-book {
		width: 16px;
		height: 16px;
		margin: 0 8px 0 0;
	}

	.getting-started-video {
		padding-right: 20px;
		margin-bottom: -60px;
		position: relative;
		height: 0;
		padding-bottom: 56.25%;

		iframe {
			width: 100%;
			height: 100%;
			position: absolute;
			top: 0;
			left: 0;
		}
	}
}
</style>