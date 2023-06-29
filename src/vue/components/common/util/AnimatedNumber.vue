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
			animatedNumber : 0
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
			const promise = numbers.animateNumbers(this.fromNumber, this.number, value => (this.animatedNumber = value))

			window.addEventListener('blur', () => {
				promise.cancel()
				this.animatedNumber = this.number
			})
		}
	},
	mounted () {
		this.animateNumber()
	}
}
</script>