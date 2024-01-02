<template>
	<div>
		<div
			class="aioseo-emoji-picker"
			v-show="show"
			ref="aioseo-emoji-picker"
		/>

		<component
			v-for="(svg, index) in emojiSvgs"
			:is="svg"
			:key="index"
			v-show="false"
			:ref="`aioseo-${svg}`"
		/>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import 'emoji-mart/dist/browser'
import SvgEmojiBasketball from '@/vue/components/common/svg/emoji/Basketball'
import SvgEmojiCar from '@/vue/components/common/svg/emoji/Car'
import SvgEmojiClock from '@/vue/components/common/svg/emoji/Clock'
import SvgEmojiFlag from '@/vue/components/common/svg/emoji/Flag'
import SvgEmojiHamburger from '@/vue/components/common/svg/emoji/Hamburger'
import SvgEmojiLightbulb from '@/vue/components/common/svg/emoji/Lightbulb'
import SvgEmojiPaw from '@/vue/components/common/svg/emoji/Paw'
import SvgEmojiSmile from '@/vue/components/common/svg/emoji/Smile'
import SvgEmojiSymbols from '@/vue/components/common/svg/emoji/Symbols'
import SvgSearch from '@/vue/components/common/svg/Search'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	emits      : [ 'selected-emoji', 'update:show' ],
	components : {
		SvgEmojiBasketball,
		SvgEmojiCar,
		SvgEmojiClock,
		SvgEmojiFlag,
		SvgEmojiHamburger,
		SvgEmojiLightbulb,
		SvgEmojiPaw,
		SvgEmojiSmile,
		SvgEmojiSymbols,
		SvgSearch
	},
	props : {
		show : Boolean
	},
	data () {
		return {
			emojiSvgs : [
				'svg-emoji-basketball',
				'svg-emoji-car',
				'svg-emoji-clock',
				'svg-emoji-flag',
				'svg-emoji-hamburger',
				'svg-emoji-lightbulb',
				'svg-emoji-paw',
				'svg-emoji-smile',
				'svg-emoji-symbols',
				'svg-search'
			]
		}
	},
	watch : {
		show (show) {
			if (show) {
				this.startListening()
				this.positionInModal()

				this.rootStore.setActiveModal(this.modalName || this._uid)

				return
			}
			this.positionInModal()
			this.stopListening()
		}
	},
	computed : {
		pickerOptions () {
			return {
				theme             : 'light',
				emojiButtonColors : [ '#E6EEFC' ],
				emojiButtonSize   : 30,
				emojiSize         : 19,
				maxFrequentRows   : 2,
				categoryIcons     : {
					frequent : { svg: this.$refs['aioseo-svg-emoji-clock'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					people   : { svg: this.$refs['aioseo-svg-emoji-smile'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					nature   : { svg: this.$refs['aioseo-svg-emoji-paw'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					foods    : { svg: this.$refs['aioseo-svg-emoji-hamburger'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					activity : { svg: this.$refs['aioseo-svg-emoji-basketball'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					places   : { svg: this.$refs['aioseo-svg-emoji-car'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					objects  : { svg: this.$refs['aioseo-svg-emoji-lightbulb'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					symbols  : { svg: this.$refs['aioseo-svg-emoji-symbols'][0]?.$el.outerHTML.replace(' style="display: none;"', '') },
					flags    : { svg: this.$refs['aioseo-svg-emoji-flag'][0]?.$el.outerHTML.replace(' style="display: none;"', '') }
				},
				onEmojiSelect : emojiData => {
					this.closePicker()
					this.$emit('selected-emoji', emojiData)
				}
			}
		}
	},
	methods : {
		async startPicker () {
			const picker = new window.EmojiMart.Picker(this.pickerOptions)

			// This picker uses the shadow DOM, so we have to manually apply styles.
			const pickerStyle     = document.createElement('style')
			pickerStyle.innerHTML = `
				.skin-tone-button {
					border-color: #D0D1D7;
					width: 24px !important;
					height: 24px !important;
				}

				.skin-tone {
					width: 12px;
					height: 12px;
				}

				.skin-tone:after {
					border: none;
					box-shadow: none;
				}

				#nav button {
					color: #8C8F9A;
				}

				#nav button:hover {
					color: #141B38;
				}

				#nav button[aria-selected] {
					color: #005AE0;
				}

				#nav .bar {
					background-color: #005AE0;
				}

				#nav[data-position=top]:before {
					height: 3px;
					background: none;
					background-color: #D0D1D7;
					margin: -3px 16px 0 12px;
					border-radius: 3px 3px 0 0;
				}

				.search input[type=search] {
					background-color: #fff;
					border: 1px solid #D0D1D7;
					transition: none;
					border-radius: 3px;
				}

				.search input[type=search]:focus {
					border-color: #005AE0;
					box-shadow: 0 0 0 1px #005AE0;
				}

				.search input[type=search]::placeholder {
					color: #8C8F9A;
				}

				.search .icon {
					color: #8C8F9A;
				}

				#preview .color-c {
					color: #141B38;
					font-size: 16px !important;
				}

				#preview .ellipsis {
					font-size: 16px;
				}

				#preview .ellipsis.color-c {
					display: none;
				}
			`
			picker.shadowRoot.appendChild(pickerStyle)
			this.$refs['aioseo-emoji-picker']?.appendChild(picker)
		},
		closePicker () {
			this.$emit('update:show', false)
			this.rootStore.unsetActiveModal(this.modalName || this._uid)
		},
		documentClick (event) {
			if (!this.show) {
				return
			}

			const target = event && event.target ? event.target : null

			const showEmojiButtons = document.querySelectorAll('.aioseo-show-emoji-button')
			for (let i = 0; i < showEmojiButtons.length; i++) {
				const showEmojiButton = showEmojiButtons[i]
				if (
					showEmojiButton &&
					(showEmojiButton === target || showEmojiButton.contains(target)) &&
					showEmojiButton.parentNode.contains(this.$refs['aioseo-emoji-picker'])
				) {
					return
				}
			}

			if (!this.$refs['aioseo-emoji-picker']) {
				return
			}

			if (this.$refs['aioseo-emoji-picker'] === target || this.$refs['aioseo-emoji-picker'].contains(target)) {
				return
			}

			this.closePicker()
			this.stopListening()
		},
		escapeListener (event) {
			if ('Escape' === event.key && this.show) {
				event.stopPropagation()
				this.closePicker()
				this.stopListening()
			}
		},
		startListening () {
			document.addEventListener('keydown', this.escapeListener, true)
			document.addEventListener('mousedown', this.documentClick)
		},
		stopListening () {
			document.removeEventListener('keydown', this.escapeListener)
			document.removeEventListener('mousedown', this.documentClick)
		},
		getModal () {
			const $modals = document.getElementsByClassName('aioseo-modal')
			if ($modals.length) {
				for (const modal of $modals) {
					if (modal.contains(this.$refs['aioseo-emoji-picker'])) {
						return modal.getElementsByClassName('modal-container')[0]
					}
				}
			}
			return false
		},
		positionInModal () {
			const modal = this.getModal()
			if (!modal) {
				return
			}

			const emojiPicker = this.$refs['aioseo-emoji-picker'].getElementsByTagName('em-emoji-picker')
			if (!emojiPicker.length) {
				return
			}

			// We need a timeout before we can get the dimensions.
			setTimeout(function () {
				const dimensions = emojiPicker[0].getBoundingClientRect()
				const modalDimensions = modal.getBoundingClientRect()
				let left = '0px', top = '4px'

				const emojiX = dimensions.x + dimensions.width + 40
				const modalX = modalDimensions.x + modalDimensions.width
				if (emojiX > modalX) {
					left = (modalX - emojiX) + 'px'
				}

				const emojiY = dimensions.y + dimensions.height
				const modalY = modalDimensions.y + modalDimensions.height
				if (emojiY > modalY) {
					top = (modalY - emojiY) + 'px'
				}

				// Make the emojiPicker fit the modal container.
				emojiPicker[0].style = `
					left: ` + left + ` !important;
					top:  ` + top + ` !important;
				`
			}, 1)
		}
	},
	async mounted () {
		// We need a 1-second wait in order for the emoji browser script to load.
		// If we continue to get complaints on this we will have to find a different way
		// to process the initial load of the emoji script.
		setTimeout(() => {
			this.startPicker()
		}, 1000)
	}
}
</script>

<style lang="scss">
.aioseo-emoji-picker {
	position: relative;
	font-size: 0;

	// We can apply some manual styles to the outer wrapper and use some variables as well.
	em-emoji-picker {
		font-size: initial;
		--rgb-color: 20, 27, 56;
		--font-family: inherit;
		--border-radius: 4px;
		--shadow: 0px 3px 15px rgb(0 0 0 / 10%);
		border: 1px solid #D0D1D7;

		height: 350px;
		min-height: 400px;
		max-height: 400px;
		max-width: 298px;

		position: absolute;
		top: 4px;
		left: 0;
		z-index: 1001;
	}
}
</style>