<template>
	<div class="aioseo-footer">
		<div>{{strings.madeBy}}</div>

		<div>
			<support-link /><span>/</span>
			<a :href="getUtmLink('https://aioseo.com/docs/')" target="_blank" rel="noreferrer noopener">{{strings.docs}}</a><span>/</span>
			<a :href="rootStore.aioseo.urls.aio.about" rel="noreferrer noopener">{{strings.freePlugins}}</a>
		</div>

		<div>
			<a :href="getUtmLink('https://aioseo.com/plugin/facebook')" target="_blank" rel="noreferrer noopener"><facebook-svg /></a>
			<a :href="getUtmLink('https://aioseo.com/plugin/linkedin')" target="_blank" rel="noreferrer noopener"><linked-in-svg /></a>
			<a :href="getUtmLink('https://aioseo.com/plugin/twitter')"  target="_blank" rel="noreferrer noopener"><twitter-svg /></a>
			<a :href="getUtmLink('https://aioseo.com/plugin/youtube')" target="_blank" rel="noreferrer noopener"><you-tube-svg /></a>
		</div>
	</div>
</template>

<script>
import { useRootStore } from '@/vue/stores'
import SupportLink from './AIOSEO_VERSION/SupportLink'

import FacebookSvg from '@/vue/components/common/svg/social/Facebook'
import LinkedInSvg from '@/vue/components/common/svg/social/LinkedIn'
import TwitterSvg from '@/vue/components/common/svg/social/Twitter'
import YouTubeSvg from '@/vue/components/common/svg/social/YouTube'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		FacebookSvg,
		LinkedInSvg,
		TwitterSvg,
		YouTubeSvg,
		SupportLink
	},
	data () {
		return {
			strings : {
				madeBy      : this.$t.__('Made with ♥ by the AIOSEO Team', this.$td),
				support     : this.$t.__('Support', this.$td),
				docs        : this.$t.__('Docs', this.$td),
				freePlugins : this.$t.__('Free Plugins', this.$td)
			}
		}
	},
	methods : {
		getUtmLink (url) {
			return this.$links.utmUrl('footer', this.rootStore.aioseo.page, url)
		}
	}
}
</script>

<style lang="scss">
#wpbody-content {
	padding-bottom: 173px;
}

.aioseo-footer {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 20px 0 30px 0;

	div {
		margin-top: 12px;
		color: #787c82;

		&:first-of-type {
			font-weight: 600;
		}

		&:last-of-type {
			display: flex;
			gap: 12px;
		}

		a {
			color: $blue;

			&:hover {
				text-decoration: none;
			}
		}

		span {
			padding: 0 6px;
		}

		svg {
			color: #A7AAAD;

			&.aioseo-facebook,
			&.aioseo-linkedin {
				width: 16px;
				height: 16px;
			}

			&.aioseo-twitter,
			&.aioseo-youtube {
				width: 17px;
				height: 16px;
			}

			&:hover {
				color: $blue;
			}
		}
	}
}
</style>