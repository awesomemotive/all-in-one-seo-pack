<template>
	<span>{{ formattedNumber }}</span>
</template>

<script>
import numbers from '@/vue/utils/numbers'
export default {
	props : {
		number     : Number,
		fromNumber : {
			type : Number,
			default () {
				return 0
			}
		},
		formatNumber : {
			type : Boolean,
			default () {
				return true
			}
		}
	},
	data () {
		return {
			animatedNumber : 0,
			animation      : null
		}
	},
	watch : {
		number () {
			this.animateNumber()
		}
	},
	computed : {
		formattedNumber () {
			return this.formatNumber ? numbers.numberFormat(this.animatedNumber) : this.animatedNumber
		}
	},
	methods : {
		animateNumber () {
			// A number that changes again mid-tween would otherwise leave the first one running.
			this.cancelAnimation()

			this.animation = numbers.animateNumbers(this.fromNumber, this.number, value => (this.animatedNumber = value))
		},
		cancelAnimation () {
			if (this.animation) {
				this.animation.cancel()
				this.animation = null
			}
		},
		// Leaving the tab pauses requestAnimationFrame, so settle on the target rather than resuming a
		// tween whose elapsed time has run far past its duration.
		onBlur () {
			this.cancelAnimation()

			this.animatedNumber = this.number
		}
	},
	mounted () {
		// Bound once: this was adding a listener per animation, so a long scan left one per tick.
		window.addEventListener('blur', this.onBlur)

		this.animateNumber()
	},
	beforeUnmount () {
		window.removeEventListener('blur', this.onBlur)

		this.cancelAnimation()
	}
}
</script>