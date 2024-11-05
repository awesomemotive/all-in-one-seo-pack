<template>
	<div
		class="aioseo-writing-assistant__keyword-examples"
		v-if="keyword.phrases && competitors"
	>
		<div
			class="aioseo-writing-assistant__keyword-examples-competitor"
			v-for="(competitorPhrases, competitorUrlHash) in keyword.phrases"
			:key="competitorUrlHash"
		>
			<div class="aioseo-writing-assistant__keyword-examples-competitor-info">
				<div class="aioseo-writing-assistant__keyword-examples-competitor-info-title">
					<a :href="competitors[competitorUrlHash]['url']" target="_blank" rel="noopener">
						{{ getCompetitorTitle(competitors[competitorUrlHash]) }}
					</a>
				</div>
				<div class="aioseo-writing-assistant__keyword-examples-competitor-info-meta">
					<div
						class="aioseo-writing-assistant__keyword-examples-competitor-info-meta-position"
						v-if="competitors[competitorUrlHash]['googlePosition']"
					>
						<span class="aioseo-writing-assistant__keyword-examples-competitor-info-meta-position-value">
							#{{ competitors[competitorUrlHash]['googlePosition'] }} {{ strings.desktop }}
						</span>
					</div>
					<div class="aioseo-writing-assistant__keyword-examples-competitor-info-meta-content">
						<div
							class="aioseo-writing-assistant__keyword-examples-competitor-info-meta-content-wordCount"
							v-if="competitors[competitorUrlHash]['wordCount']"
						>
							{{ competitors[competitorUrlHash]['wordCount'] }} {{ strings.words }}
						</div>
						<div v-if="competitors[competitorUrlHash]['grade']">
							<grade-round :grade="competitors[competitorUrlHash]['grade']" />
						</div>
					</div>
				</div>
			</div>
			<div
				class="aioseo-writing-assistant__keyword-examples-competitor-phrases"
				v-if="competitorPhrases"
			>
				<div
					class="aioseo-writing-assistant__keyword-examples-competitor-phrases-phrase"
					v-for="(phrase, key) in competitorPhrases"
					:key="key"
					v-html="phrase"
				/>
			</div>
		</div>
	</div>
</template>

<script>
import { decodeSpecialChars } from '@/vue/utils/helpers.js'
import { useWritingAssistantStore } from '@/vue/stores'
import GradeRound from '../GradeRound'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		GradeRound
	},
	props : {
		keyword : Object
	},
	data () {
		return {
			strings : {
				words   : __('words', td),
				desktop : __('desktop', td)
			},
			competitors : {}
		}
	},
	methods : {
		getCompetitorTitle (competitor) {
			return decodeSpecialChars(competitor.title)
		}
	},
	beforeMount () {
		for (const index in this.keyword.phrases) {
			this.competitors[index] = this.writingAssistantStore.getCompetitors[index]
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant {
	&__keyword-examples {
		padding: 12px;

		&-competitor {
			padding-bottom: 30px;

			&-info {
				display: flex;
				flex-direction: column;
				padding: 10px;
				background-color: $box-background;
				border-radius: 3px;

				&-title {
					font-weight: 700;

					a {
						text-decoration: none;

						&:hover {
							text-decoration: underline;
						}
					}
				}

				&-meta {
					display: flex;
					flex-direction: row;

					&-position {
						flex-grow: 1;
						align-self: center;

						&-value {
							font-weight: 700;
							font-size: 11px;
							padding: 2px 6px;
							background-color: $input-border;
							border-radius: 10px;
						}
					}

					&-content {
						display: flex;

						> * {
							align-self: center;
						}

						&-wordCount {
							margin-right: 6px;
							font-style: italic;
						}
					}
				}
			}

			&-phrases {
				padding: 10px 10px 0 30px;

				&-phrase {
					padding-left: 20px;
					border-left: 3px solid $border;
					margin-bottom: 15px;

					.kp {
						background-color: $yellow-highlight;
					}
				}
			}
		}
	}
}
</style>