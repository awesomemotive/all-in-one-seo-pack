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
import SvgTwitterRounded from '@/vue/components/common/svg/TwitterRounded'
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
		SvgTwitterRounded
	},
	data () {
		return {
			strings : {
				title       : this.$t.__('Get out the word!', this.$td),
				description : this.$t.__('Share your content on your favorite social media platforms to drive engagement and increase your SEO.', this.$td)
			}
		}
	},
	computed : {
		socialNetworks () {
			return [
				{
					icon : 'svg-twitter-rounded',
					link : 'https://twitter.com/share?url='
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

	.aioseo-twitter-rounded {
		color: #1DA1F2;
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