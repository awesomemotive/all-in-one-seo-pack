<template>
	<div class="aioseo-search-statistics-redirects">
		<div
			class="redirects redirects-from"
			v-if="redirects?.from?.length"
		>
			<div class="title">{{ redirectsFromString }}:</div>
			<ul>
				<li
					v-for="(redirect, index) in redirects.from"
					:key="index"
				>
					<a :href="redirect" target="_blank">{{ redirect }}</a>
				</li>
			</ul>
		</div>

		<div
			class="redirects redirects-to"
			v-if="redirects?.to"
		>
			<div class="title">{{ redirectsToString }}</div>
			<ul>
				<li>
					<a :href="redirects.to" target="_blank">{{ redirects.to }}</a>
				</li>
			</ul>
		</div>

		<div
			class="aioseo-card-footer"
			v-if="(redirects?.from?.length || redirects.to)"
		>
			<a
				:href="rootStore.aioseo.urls.aio.redirects"
				v-html="strings.manageRedirects"
			/>
		</div>

		<div v-else class="no-redirects">
			<div class="no-redirects-title">{{ strings.weCouldnt }}</div>

			<p class="no-redirects-description">{{ strings.easilyCreate }}</p>

			<base-button
				type="blue"
				tag="a"
				:href="rootStore.aioseo.urls.aio.redirects"
				size="small"
			>
				{{ strings.addRedirects }}
			</base-button>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { __, _n, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	props : {
		redirects : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : {
				weCouldnt       : __('We couldn\'t find any redirects for this post.', td),
				easilyCreate    : __('Easily create and manage redirects for your broken links.', td),
				addRedirects    : __('Add Redirect', td),
				manageRedirects : sprintf(
					// Translators: 1 - Right arrow.
					__('Manage Redirects %1$s', td),
					'<span>&rarr;</span>'
				)
			}
		}
	},
	computed : {
		redirectsFromString () {
			const amount = this.redirects.from.length

			return sprintf(
				// Translators: 1 - The amount of posts.
				_n('%1$d post is redirecting to this post', '%1$d posts are redirecting to this post', amount, td),
				amount
			)
		},
		redirectsToString () {
			return sprintf(
				// Translators: 1 - The redirect status code.
				__('This post has a %1$s redirect to:', td),
				this.redirects.toCode
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-redirects {
	margin-bottom: 24px;

	.title {
		font-weight: 700;
		font-size: 14px;
		color: $black;
		margin-bottom: 8px;
	}

	.redirects {
		margin-bottom: 24px;
	}

	ul {
		list-style: none;
		margin: 0;
		padding-left: 8px;

		li {
			position: relative;
			padding-left: 16px;

			&::before {
				content: "\2022";
				color: $blue;
				position: absolute;
				top: 0;
				left: 0;
			}
		}

		a {
			font-size: 14px;
		}
	}

	.no-redirects {
		text-align: center;

		&-title {
			font-weight: 700;
			font-size: 14px;
			color: $black;
			margin-bottom: 8px;
		}

		&-description {
			font-weight: 400;
			font-size: 14px;
			color: $black;
			margin: 0 0 8px;
		}

		.aioseo-button {
			margin-top: 12px;
		}
	}
}
</style>