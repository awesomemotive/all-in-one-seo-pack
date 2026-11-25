<template>
	<div
		class="aioseo-post-publish"
		v-if="permalink"
	>
		<div class="aioseo-post-publish__head">
			<p class="aioseo-post-publish__title">{{ strings.title }}</p>
			<p class="aioseo-post-publish__description">{{ strings.description }}</p>
		</div>

		<div class="aioseo-post-publish__url">
			<span class="aioseo-post-publish__url-text">{{ displayUrl }}</span>

			<button
				type="button"
				class="aioseo-post-publish__copy"
				:class="{ 'aioseo-post-publish__copy--copied': copied }"
				@click="copyLink"
			>
				<svg-circle-check v-if="copied" />
				<svg-copy v-else />

				{{ copied ? strings.copied : strings.copy }}
			</button>
		</div>

		<div class="aioseo-post-publish__label">{{ strings.preview }}</div>

		<core-facebook-preview
			class="aioseo-post-publish__preview"
			:title="previewTitle"
			:description="previewDescription"
			:image="socialImage"
		/>

		<div class="aioseo-post-publish__label">{{ strings.shareIt }}</div>

		<div class="aioseo-post-publish__networks">
			<a
				v-for="network in socialNetworks"
				:key="network.slug"
				class="aioseo-post-publish__network"
				target="_blank"
				rel="noopener noreferrer"
				v-safe-href="network.link"
				:aria-label="network.label"
			>
				<component :is="network.icon" />
			</a>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import { useImage } from '@/vue/composables/Image'
import { useTags } from '@/vue/composables/Tags'

import CoreFacebookPreview from '@/vue/components/common/core/FacebookPreview'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCopy from '@/vue/components/common/svg/Copy'
import SvgIconTwitter from '@/vue/components/common/svg/icon/Twitter'
import SvgOutgoingMail from '@/vue/components/common/svg/OutgoingMail'
import SvgSocialFacebook from '@/vue/components/common/svg/social/Facebook'
import SvgSocialLinkedin from '@/vue/components/common/svg/social/LinkedIn'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const postEditorStore = usePostEditorStore()
const tagsStore       = useTagsStore()

const { imageUrl, setImageUrl } = useImage()
const { parseTags }             = useTags({ separator: undefined })

const strings = {
	title       : __('You\'re live! 🎉', td),
	description : __('Your post is published. Share it to start driving traffic and engagement.', td),
	preview     : __('Preview', td),
	shareIt     : __('Share it', td),
	copy        : __('Copy', td),
	copied      : __('Copied!', td)
}

const permalink = computed(() => tagsStore.liveTags.permalink)

const displayUrl = computed(() => (permalink.value || '').replace(/^https?:\/\//, '').replace(/\/$/, ''))

const previewTitle = computed(() => parseTags(
	postEditorStore.currentPost.og_title ||
	postEditorStore.currentPost.title ||
	postEditorStore.currentPost.tags?.title ||
	'#post_title #separator_sa #site_title'
))

const previewDescription = computed(() => parseTags(
	postEditorStore.currentPost.og_description ||
	postEditorStore.currentPost.description ||
	postEditorStore.currentPost.tags?.description ||
	'#post_content'
))

const socialImage = ref('')

const copied    = ref(false)
let   copyTimer = null

// The async Clipboard API only exists in secure contexts (HTTPS/localhost), so
// on a plain-HTTP site navigator.clipboard is undefined. Fall back to the legacy
// execCommand path there so the button works regardless of context.
const copyToClipboard = async (text) => {
	if (navigator.clipboard?.writeText) {
		try {
			await navigator.clipboard.writeText(text)

			return true
		} catch (error) {
			// Fall through to the legacy path below.
		}
	}

	try {
		const textarea = document.createElement('textarea')
		textarea.value = text
		textarea.setAttribute('readonly', '')
		textarea.style.position = 'fixed'
		textarea.style.left      = '-9999px'
		document.body.appendChild(textarea)
		textarea.select()

		const succeeded = document.execCommand('copy')
		document.body.removeChild(textarea)

		return succeeded
	} catch (error) {
		return false
	}
}

const copyLink = async () => {
	if (!await copyToClipboard(permalink.value)) {
		return
	}

	copied.value = true

	if (copyTimer) {
		clearTimeout(copyTimer)
	}

	copyTimer = setTimeout(() => {
		copied.value = false
	}, 2000)
}

const socialNetworks = computed(() => {
	const url  = encodeURIComponent(permalink.value || '')
	const text = encodeURIComponent(previewTitle.value || postEditorStore.currentPost.title || '')

	return [
		{
			slug  : 'twitter',
			icon  : SvgIconTwitter,
			label : __('Share on X', td),
			link  : `https://x.com/intent/tweet?url=${url}&text=${text}`
		},
		{
			slug  : 'facebook',
			icon  : SvgSocialFacebook,
			label : __('Share on Facebook', td),
			link  : `https://www.facebook.com/sharer/sharer.php?u=${url}`
		},
		{
			slug  : 'linkedin',
			icon  : SvgSocialLinkedin,
			label : __('Share on LinkedIn', td),
			link  : `https://www.linkedin.com/sharing/share-offsite/?url=${url}`
		},
		{
			slug  : 'email',
			icon  : SvgOutgoingMail,
			label : __('Share via email', td),
			link  : `mailto:?subject=${text}&body=${url}`
		}
	]
})

onMounted(async () => {
	await setImageUrl('facebook')
	socialImage.value = imageUrl.value
})
</script>

<style lang="scss">
.aioseo-post-publish {
	&__head {
		margin-bottom: 14px;
	}

	&__title {
		margin: 0 0 3px;
		font-size: 14px;
		font-weight: 700;
		color: $black;
	}

	&__description {
		margin: 0;
		font-size: 13px;
		line-height: 1.4;
		color: $black2;
	}

	&__url {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 5px 5px 5px 10px;
		margin-bottom: 16px;
		background: #f3f4f5;
		border: 1px solid $border;
		border-radius: 6px;
	}

	&__url-text {
		flex: 1;
		min-width: 0;
		font-size: 12px;
		color: $black2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	&__copy {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		flex-shrink: 0;
		padding: 4px 9px;
		font-size: 12px;
		font-weight: 600;
		color: $blue;
		background: #fff;
		border: 1px solid $border;
		border-radius: 5px;
		cursor: pointer;

		svg {
			width: 13px;
			height: 13px;
		}

		&--copied {
			color: $green;
		}
	}

	&__label {
		margin-bottom: 8px;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: $placeholder-color;
	}

	&__preview {
		margin-bottom: 18px;
	}

	&__networks {
		display: flex;
		gap: 8px;
	}

	&__network {
		display: grid;
		place-items: center;
		width: 34px;
		height: 34px;
		color: $black2;
		background: #fff;
		border: 1px solid $border;
		border-radius: 8px;
		transition: border-color 0.15s ease, color 0.15s ease;

		svg {
			width: 17px;
			height: 17px;
		}

		&:hover {
			color: $blue;
			border-color: $blue;
		}
	}
}
</style>