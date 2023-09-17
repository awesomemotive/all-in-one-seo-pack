<template>
	<ul class="aioseo-analysis-detail">
		<template
			v-for="(keyphrase, index) in analysisItems"
			:key="index"
		>
			<li
				v-if="keyphrase.title"
			>
				<p
					class="title"
					:class="0 === keyphrase.error ? 'toggled' : ''"
				>
					<svg-circle-check width="16" v-if="0 === keyphrase.error" />

					<svg-circle-close width="16" v-if="1 === keyphrase.error" />

					<span class="title__text">{{ keyphrase.title }}</span>

					<tru-seo-toggle-highlighter
						v-if="keyphrase?.highlightSentences?.length"
						:analyzer="index"
					/>

					<svg-caret
						width="16"
						role="button"
						@click.stop="toggleDescriptionEv"
					/>
				</p>
				<p class="description">{{ keyphrase.description }}</p>
			</li>
		</template>
	</ul>
</template>

<script>
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import TruSeoToggleHighlighter from './tru-seo/ToggleHighlighter'

export default {
	components : {
		SvgCaret,
		SvgCircleCheck,
		SvgCircleClose,
		TruSeoToggleHighlighter
	},
	props : {
		analysisItems : {
			type : Object
		}
	},
	methods : {
		toggleDescriptionEv (ev) {
			ev.target.parentElement.classList.toggle('toggled')
		}
	}
}
</script>

<style lang="scss">
.aioseo-analysis-detail {
	margin: 0 0 16px;
	padding: 0;
	list-style: none;

	li {
		padding-left: 24px;
		position: relative;
		margin-bottom: 0;
		font-size: 14px;
		line-height: 22px;

		+ li {
			margin-top: 16px;
		}

		p {
			font-size: inherit;
			line-height: inherit;
			margin: 0;
			padding: 0;
		}

		svg {
			&.aioseo-circle-check {
				color: $green;
			}
			&.aioseo-circle-close {
				color: $red;
			}
			&.aioseo-circle-check,
			&.aioseo-circle-close {
				position: absolute;
				left: 0;
				top: 2px;
			}
			&.aioseo-caret {
				cursor: pointer;
				transform: rotate(-180deg);
				transition: transform 0.3s;
			}
		}

		.title {
			align-items: center;
			display: flex;
			gap: 4px;

			&__text {
				font-weight: 700;
				margin-right: 6px;

				.edit-post-sidebar & {
					flex: 1;
				}
			}

			&.toggled {

				.aioseo-caret {
					transform: rotate(-90deg)
				}

				&+ .description {
					opacity: 0;
					height: 0;
					margin: 0;
				}
			}

			.tru-seo-toggle-highlighter {
				color: $black2;
				height: 16px;
				width: 16px;

				.aioseo-tooltip {
					display: block;
					margin: 0;

					:has(svg) {
						&, * {
							height: 16px;
							width: 16px;
						}
					}
				}
			}
		}

		.description {
			color: $black;
			font-style: normal;
			font-weight: 400;
			opacity: 1;
			height: auto;
			margin-top: 5px;
			transition: all 0.3s;
		}
	}
}
</style>