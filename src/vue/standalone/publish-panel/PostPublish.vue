<template>
	<div
		class="aioseo-post-publish"
		v-if="tagsStore.liveTags.permalink"
	>
		<h2 class="title">{{ strings.title }}</h2>
		<p class="description">{{ strings.description }}</p>
		<div class="links">
			<a
				class="link"
				target="_blank"
				v-for="(network, index) in socialNetworks"
				:key="index"
				:href="network.link"
			>
				<component :is="network.icon" />
			</a>
		</div>
	</div>
</template>

<script>
import {
	useTagsStore
} from '@/vue/stores'

import SvgFacebookRounded from '@/vue/components/common/svg/facebook/Rounded'
import SvgLinkedinRounded from '@/vue/components/common/svg/LinkedinRounded'
import SvgPinterestRounded from '@/vue/components/common/svg/PinterestRounded'
import SvgIconTwitter from '@/vue/components/common/svg/icon/Twitter'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			tagsStore : useTagsStore()
		}
	},
	components : {
		SvgFacebookRounded,
		SvgLinkedinRounded,
		SvgPinterestRounded,
		SvgIconTwitter
	},
	data () {
		return {
			strings : {
				title       : __('Get out the word!', td),
				description : __('Share your content on your favorite social media platforms to drive engagement and increase your SEO.', td)
			}
		}
	},
	computed : {
		socialNetworks () {
			return [
				{
					icon : 'svg-icon-twitter',
					link : 'https://x.com/share?url='
				},
				{
					icon : 'svg-facebook-rounded',
					link : 'https://www.facebook.com/sharer/sharer.php?u='
				},
				{
					icon : 'svg-pinterest-rounded',
					link : 'https://pinterest.com/pin/create/button/?url='
				},
				{
					icon : 'svg-linkedin-rounded',
					link : 'https://www.linkedin.com/shareArticle?url='
				}
			].map(network => {
				return { ...network, link: network.link + this.tagsStore.liveTags.permalink }
			})
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-publish {
	.title {
		font-weight: bold;
		font-size: 13px;
		line-height: 130%;
		color: $black2;
	}

	.description {
		font-size: 13px;
		line-height: 130%;
		color: $black2;
		margin-bottom: 16px;
	}

	.links {
		.link {
			display: inline-block;
			margin-right: 10px;

			&:hover {
				opacity: 0.8;
			}
		}
	}

	.aioseo-icon-twitter {
		width: 32px;
		height: 32px;
		color: $black;
	}

	.aioseo-facebook-rounded {
		color: #1877F2;
	}

	.aioseo-pinterest-rounded {
		color: #E60023;
	}

	.aioseo-linkedin-rounded {
		color: #2867B2;
	}
}
</style>