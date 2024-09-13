<template>
	<div
		v-if="canShowUpsell"
		class="aioseo-link-assistant-did-you-know"
	>
		<svg-circle-information />

		<span
			@click.stop="disabled = true"
			v-html="strings.upsell"
		/>

		<svg-close
			@click.native.stop="postEditorStore.disableLinkAssistantEducation"
		/>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import urlMethods from 'url'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
import SvgClose from '@/vue/components/common/svg/Close'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore()
		}
	},
	components : {
		SvgCircleInformation,
		SvgClose
	},
	data () {
		return {
			linkFormatValue : {},
			disabled        : false,
			url             : null,
			strings         : {
				upsell : sprintf(
					// Translators: 1 - Learn more link.
					__('Did you know you can automatically add internal links using Link Assistant? %1$s', td),
					links.getPlainLink(GLOBAL_STRINGS.learnMore, this.rootStore.aioseo.urls.aio.linkAssistant, true)
				)
			}
		}
	},
	computed : {
		canShowUpsell () {
			const addon                  = addons.getAddon('aioseo-link-assistant')
			const { options }            = this.postEditorStore.currentPost
			const internalLinkCount      = options.linkFormat.internalLinkCount
			const linkAssistantDismissed = options.linkFormat.linkAssistantDismissed

			return (
				this.licenseStore.isUnlicensed ||
				(
					!addon ||
					!addon.isActive ||
					addon.requiresUpgrade
				)
			) &&
			2 < internalLinkCount &&
			!linkAssistantDismissed &&
			!this.disabled &&
			this.linkFormatValue.url &&
			this.isInternalLink(this.linkFormatValue.url)
		}
	},
	methods : {
		async linkAdded (context) {
			await this.$nextTick()

			// Return early if the process is disabled or has already run.
			const { options }            = this.postEditorStore.currentPost
			const internalLinkCount      = options.linkFormat.internalLinkCount
			const linkAssistantDismissed = options.linkFormat.linkAssistantDismissed
			if (
				2 < internalLinkCount ||
				linkAssistantDismissed
			) {
				return
			}

			if (this.isInternalLink(context.url || context.suggestion?.url || null)) {
				this.postEditorStore.incrementInternalLinkCount()
			}
		},
		async setLinkFormatValue () {
			await this.$nextTick()
			const linkFormatValue = document.querySelector('#aioseo-link-assistant-education input')
			if (!this.linkFormatValue.url && linkFormatValue?.value) {
				this.linkFormatValue = JSON.parse(linkFormatValue.value)
			}
		},
		isInternalLink (url) {
			const parsedUrl = urlMethods.parse(url, false, true) // eslint-disable-line
			// Check if the URL starts with a single slash.
			if (-1 === url.indexOf('//') && 0 === url.indexOf('/')) {
				return true
			}

			// Check if the URL starts with a # indicating a fragment.
			if (0 === url.indexOf('#')) {
				return false
			}

			// No host indicates an internal link.
			if (!parsedUrl.host) {
				return true
			}

			return parsedUrl.host === this.rootStore.aioseo.urls.domain
		}
	},
	created () {
		this.setLinkFormatValue()

		const { addAction, hasAction } = window.wp.hooks
		if (!hasAction('aioseo-link-format-link-added', 'aioseo')) {
			addAction('aioseo-link-format-link-added', 'aioseo', this.linkAdded)
		}
	}
}
</script>

<style lang="scss">
.aioseo-link-assistant-did-you-know {
	padding: 16px 12px 24px;
	border-top: 1px solid #ddd;
	display: flex;

	svg.aioseo-circle-information {
		width: 16px;
		min-width: 16px;
		max-width: 16px;
		height: 16px;
		margin-right: 5px;
		margin-top: 2px;
	}

	span {
		flex: 1;
	}

	svg.aioseo-close {
		margin-top: 2px;
		cursor: pointer;
		color: $black2;
		min-width: 10px;
		max-width: 10px;
		width: 10px;
		height: 10px;
	}
}
</style>