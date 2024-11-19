import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgFaceNegative from '@/vue/components/common/svg/face/Negative'
import SvgFaceNeutral from '@/vue/components/common/svg/face/Neutral'
import SvgFaceSmile from '@/vue/components/common/svg/face/Smile'
import SvgList from '@/vue/components/common/svg/List'
export const HeadlineResult = {
	components : {
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation,
		SvgFaceNegative,
		SvgFaceNeutral,
		SvgFaceSmile,
		SvgList
	},
	computed : {
		wordBalance () {
			const result = this.result

			const words = {
				common : {
					title   : this.$t.__('Common Words', this.$td),
					help    : this.$t.__('Headlines with 20-30% common words are more likely to get clicks.', this.$td),
					class   : 0 === result.commonWordsPercentage ? 'red' : 0.2 > result.commonWordsPercentage ? 'orange' : 'green',
					words   : result.commonWords,
					percent : Math.round(result.commonWordsPercentage * 100),
					bar     : (100 * result.commonWordsPercentage) / 0.3,
					goal    : this.$t.__('20-30%', this.$td)
				},
				uncommon : {
					title   : this.$t.__('Uncommon Words', this.$td),
					help    : this.$t.__('Your headline would be more likely to get clicks if it had more uncommon words.', this.$td),
					class   : 0 === result.uncommonWordsPercentage ? 'red' : 0.1 > result.uncommonWordsPercentage ? 'orange' : 'green',
					words   : result.uncommonWords,
					percent : Math.round(result.uncommonWordsPercentage * 100),
					bar     : (100 * result.uncommonWordsPercentage) / 0.2,
					goal    : this.$t.__('10-20%', this.$td)
				},
				emotional : {
					title   : this.$t.__('Emotional Words', this.$td),
					help    : this.$t.__('Emotionally triggered headlines are likely to drive more clicks.', this.$td),
					class   : 0 === result.emotionalWordsPercentage ? 'red' : 0.1 > result.emotionalWordsPercentage ? 'orange' : 'green',
					words   : result.emotionWords,
					percent : Math.round(result.emotionalWordsPercentage * 100),
					bar     : (100 * result.emotionalWordsPercentage) / 0.15,
					goal    : this.$t.__('10-15%', this.$td)
				},
				power : {
					title   : this.$t.__('Power Words', this.$td),
					help    : this.$t.__('Headlines with power words are more likely to get clicks.', this.$td),
					class   : 0 === result.powerWordsPercentage ? 'red' : 'green',
					words   : result.powerWords,
					percent : Math.round(result.powerWordsPercentage * 100),
					bar     : (100 * result.powerWordsPercentage) / 0.1,
					goal    : this.$t.__('at least one', this.$td)
				}
			}

			const allGood = Object.values(words).every(word => 'green' === word.class)

			return {
				result : allGood ? this.$t.__('All good', this.$td) : this.$t.__('Needs improvement', this.$td),
				icon   : allGood ? 'svg-circle-check' : 'svg-circle-exclamation',
				class  : allGood ? 'green' : 'orange',
				words
			}
		},
		characterCount () {
			let color, result, icon, guideline
			const length  = this.result.length
			const percent = Math.round((100 * length) / 65) // 65 is the limit for a good grade.

			switch (true) {
				case 19 >= length:
				case 80 <= length:
					color = 'red'
					icon  = 'svg-circle-close'
					break
				case 20 <= length && 34 >= length:
				case 67 <= length && 79 >= length:
					color = 'orange'
					icon  = 'svg-circle-exclamation'
					break
				case 35 <= length && 66 >= length:
					color = 'green'
					icon  = 'svg-circle-check'
					break
			}

			switch (true) {
				case 34 >= length:
					result = this.$t.__('Too Short', this.$td)
					guideline = this.$t.__('You have space to add more keywords and power words to boost your rankings and click-through rate.', this.$td)
					break
				case 35 <= length && 66 >= length:
					result = this.$t.__('Good', this.$td)
					guideline = this.$t.__('Headlines that are about 55 characters long will display fully in search results and tend to get more clicks.', this.$td)
					break
				case 67 <= length:
					result = this.$t.__('Too Long', this.$td)
					guideline = this.$t.__('At this length, it will get cut off in search results. Try reducing it to about 55 characters.', this.$td)
					break
			}

			return {
				result,
				icon,
				percent,
				guideline,
				class : color
			}
		},
		wordCount () {
			const wordCount = this.result.wordCount
			const percent   = Math.round((100 * wordCount) / 9) // 9 is the limit for a good grade.

			switch (true) {
				case 4 >= wordCount:
					return {
						result    : this.$t.__('Not Enough Words', this.$td),
						percent   : percent,
						class     : 'red',
						icon      : 'svg-circle-close',
						guideline : this.$t.__('Your headline doesn’t use enough words. You have more space to add keywords and power words to improve your SEO and get more engagement.', this.$td)
					}
				case 5 <= wordCount && 9 >= wordCount:
					return {
						result    : this.$t.__('Good', this.$td),
						percent   : percent,
						class     : 'green',
						icon      : 'svg-circle-check',
						guideline : this.$t.__('Your headline has the right amount of words. Headlines are more likely to be clicked on in search results if they have about 6 words.', this.$td)
					}
				case 10 <= wordCount && 11 >= wordCount:
					return {
						result    : this.$t.__('Reduce Word Count', this.$td),
						percent   : percent,
						class     : 'orange',
						icon      : 'svg-circle-exclamation',
						guideline : this.$t.__('Headlines are more likely to be clicked on in search results if they have about 6 words.', this.$td)
					}
				default:
					return {
						result    : this.$t.__('Too Many Words', this.$td),
						percent   : percent,
						class     : 'red',
						icon      : 'svg-circle-close',
						guideline : this.$t.__('Your headline has too many words. Long headlines will get cut off in search results and won’t get as many clicks.', this.$td)
					}
			}
		},
		sentiment () {
			switch (this.result.sentiment) {
				case 'neu':
					return {
						result    : this.$t.__('Neutral', this.$td),
						icon      : 'svg-face-neutral',
						class     : 'orange',
						headline  : this.$t.__('Your headline has a neutral sentiment.', this.$td),
						guideline : this.$t.__('Headlines that are strongly positive or negative tend to get more engagement than neutral ones.', this.$td)
					}
				case 'pos':
					return {
						result    : this.$t.__('Positive', this.$td),
						icon      : 'svg-face-smile',
						class     : 'green',
						headline  : this.$t.__('Your headline has a positive sentiment.', this.$td),
						guideline : this.$t.__('Positive headlines tend to get better engagement than neutral or negative ones.', this.$td)
					}
				default:
					return {
						result    : this.$t.__('Negative', this.$td),
						icon      : 'svg-face-negative',
						class     : 'red',
						headline  : this.$t.__('Your headline has a negative sentiment.', this.$td),
						guideline : this.$t.__('Negative headlines are attention-grabbing and tend to perform better than neutral ones.', this.$td)
					}
			}
		},
		headlineTypes () {
			return {
				result    : this.result.headlineTypes.join(', '),
				icon      : 'svg-list',
				class     : 'blue',
				guideline : this.$t.sprintf(
					// Translators: 1 - HTML line break tag, 2 - Opening HTML link tag, 3 - Closing HTML link tag.
					this.$t.__('Headlines that are lists and how-to get more engagement on average than other types of headlines. %1$s%2$sLearn More%3$s →', this.$td),
					'<br/><br/>',
					'<a href="https://optinmonster.com/why-these-21-headlines-went-viral-and-how-you-can-copy-their-success/" target="_blank" className="aioseo-headline-analyzer-link"><span>',
					'</span></a>'
				)
			}
		}
	}
}