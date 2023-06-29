<template>
	<div class="aioseo-primary-term-cta" v-if="canShowUpsell">
		<svg-circle-information
			width="15"
			height="15"
		/>

		<p v-html="strings.didYouKnow" />
		<p v-html="strings.learnMoreLink" />

		<svg-close @click.native.stop="postEditorStore.disablePrimaryTermEducation" />
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import { getSelectedTerms } from '@/vue/standalone/primary-term/helpers'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
import SvgClose from '@/vue/components/common/svg/Close'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		SvgCircleInformation,
		SvgClose
	},
	data () {
		return {
			selectedTerms : [],
			strings       : {
				didYouKnow : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Opening strong tag, 3 - Closing strong tag.
					this.$t.__('Did you know that %1$s Pro allows you to choose a %2$sprimary category%3$s for your posts? This feature works hand in hand with our powerful Breadcrumbs template to give you full navigational control to help improve your search rankings!', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'<strong>',
					'</strong>'
				),
				learnMoreLink : this.$t.sprintf(
					// Translators: 1 - "Learn More" link.
					'<a href="%1$s" target="_blank" rel="noreferrer nofollow">%2$s<span class="link-right-arrow">&nbsp;&rarr;</span></a>',
					this.$links.getDocUrl('primaryTerm'),
					this.$t.__('Learn more', this.$td)
				)
			}
		}
	},
	props : {
		taxonomy : String
	},
	methods : {
		updateSelectedTerms () {
			this.selectedTerms = getSelectedTerms(this.taxonomy)
		}
	},
	computed : {
		canShowUpsell () {
			const { options }               = this.postEditorStore.currentPost
			const productEducationDismissed = options.primaryTerm.productEducationDismissed

			return !productEducationDismissed && 1 < this.selectedTerms.length
		}
	},
	mounted () {
		this.updateSelectedTerms()

		window.aioseoBus.$on('updateSelectedTerms', this.updateSelectedTerms)
	},
	beforeDestroy () {
		window.aioseoBus.$off('updateSelectedTerms', this.updateSelectedTerms)
	}
}
</script>

<style lang="scss">
.aioseo-primary-term-cta {
	position: relative;
	padding: 0 25px;

	.aioseo-circle-information {
		position: absolute;
		top: 0;
		left: 0;
		color: $orange;
	}

	p {
		font-weight: 400;
		font-size: 14px;
		line-height: 125%;
		color: $black2-hover;

		&:last-of-type {
			margin-bottom: 0;
		}
	}

	.aioseo-close {
		position: absolute;
		top: 2px;
		right: 0;
		cursor: pointer;
		color: $black2;
		width: 10px;
		height: 10px;
	}
}
</style>