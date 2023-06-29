<template>
	<div v-if="rootStore.aioseo.rssFeed" class="aioseo-blog">
		<ul class="aioseo-blog-list">
			<li
				v-for="(post, index) in rootStore.aioseo.rssFeed"
				:key="index"
				class="aioseo-blog-list-item"
			>
				<a
					:href="post.url"
					target="_blank"
				>{{ post.title }}</a>
				<span>{{ post.date }}</span>
			</li>
		</ul>
		<a
			:href="$links.utmUrl(medium, null, 'blog/')"
			class="aioseo-blog-read-more"
			target="_blank"
		>
			{{ strings.readMore }}<svg-external />
		</a>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import SvgExternal from '@/vue/components/common/svg/External'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		SvgExternal
	},
	props : {
		medium : {
			type : String,
			default () {
				return 'aioseo-dashboard-overview'
			}
		}
	},
	data () {
		return {
			strings : {
				readMore : this.$t.__('Read more like this on our SEO blog', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-blog {
	position: relative;

	&-list {
		margin-bottom: 24px;
		border-bottom: 2px solid $border;

		&-item {
			display: flex;
			justify-content: space-between;
			margin-bottom: 16px;
			color: $black2;

			a {
				text-decoration: none;
				text-overflow: ellipsis;
				white-space: nowrap;
				overflow: hidden;
				font-size: 14px;

				&:hover {
					text-decoration: underline !important;
				}
			}

			span {
				white-space: nowrap;
				padding-left: 10px;
			}
		}
	}

	&-read-more {
		font-weight: 700;
		font-size: 14px;

		svg {
			display: inline-block;
			width: 16px;
			height: 16px;
			vertical-align: sub;
			margin-left: 8px;
		}
	}

	&:before {
		position: absolute;
		top: 0;
		left: 0;
	}
}
</style>