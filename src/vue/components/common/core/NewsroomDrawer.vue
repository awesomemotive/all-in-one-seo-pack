<template>
	<div class="aioseo-newsroom-drawer">
		<transition name="newsroom-slide">
			<div
				v-if="show"
				class="newsroom-menu"
			>
				<div class="newsroom-header">
					<strong>{{ strings.title }}</strong>

					<button
						type="button"
						class="newsroom-close"
						:aria-label="strings.close"
						@click="$emit('close')"
					>
						&#10005;
					</button>
				</div>

				<div class="newsroom-items">
					<article
						v-for="item in items"
						:key="item.id"
						class="newsroom-item"
					>
						<div class="newsroom-item__meta">
							<span
								v-if="item.label"
								class="newsroom-item__badge"
								:class="'newsroom-item__badge--' + item.badge"
							>{{ item.label }}</span>

							<span
								v-if="item.version"
								class="newsroom-item__version"
							>v{{ item.version }}</span>

							<span class="newsroom-item__date">{{ item.dateFormatted }}</span>
						</div>

						<a
							class="newsroom-item__title"
							:href="item.url"
							target="_blank"
							rel="noopener noreferrer"
						>{{ item.title }}</a>

						<p
							v-if="item.excerpt"
							class="newsroom-item__excerpt"
						>{{ item.excerpt }}</p>

						<span class="newsroom-item__more">
							{{ strings.readFull }}
							<span class="newsroom-item__more-arrow">&rarr;</span>
						</span>
					</article>
				</div>

				<a
					class="newsroom-see-all"
					:href="archiveUrl"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span>
						<strong>{{ strings.seeAll }}</strong>
						<span>{{ strings.seeAllSub }}</span>
					</span>

					<span class="newsroom-see-all__arrow">&rarr;</span>
				</a>
			</div>
		</transition>

		<div
			v-if="show"
			class="newsroom-overlay"
			@click="$emit('close')"
		/>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

defineEmits([ 'close' ])

// Presentational on purpose: the feed lives on a different root store in each plugin, and
// reaching for AIOSEO's from here would pull its store into builds that can't construct it.
const props = defineProps({
	show : {
		type     : Boolean,
		required : true
	},
	newsroom : {
		type : Object,
		default () {
			return {}
		}
	}
})

const items      = computed(() => props.newsroom?.items || [])
const archiveUrl = computed(() => props.newsroom?.archiveUrl || '')

const strings = computed(() => ({
	title     : __('What\'s New', td),
	readFull  : __('Read the full update', td),
	seeAll    : __('View all updates', td),
	seeAllSub : __('Every release and announcement in the Newsroom', td),
	close     : __('Close', td)
}))
</script>

<style lang="scss">
.aioseo-newsroom-drawer {
	.newsroom-menu {
		position: fixed;
		// The admin bar would otherwise clip the header. WP sets this custom property;
		// the fallback covers older versions.
		top: var(--wp-admin--admin-bar--height, 32px);
		right: 0;
		bottom: 0;
		z-index: 100100;
		display: flex;
		flex-direction: column;
		width: 400px;
		max-width: 100vw;
		background: #fff;
		box-shadow: -8px 0 28px rgba($black, 0.18);
	}

	.newsroom-header {
		height: 64px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 0 20px;
		color: #fff;
		background-color: $blue;

		strong {
			font-size: 18px;
			font-weight: 600;
		}
	}

	.newsroom-close {
		padding: 0;
		border: 0;
		background: none;
		color: #fff;
		font-size: 16px;
		line-height: 1;
		cursor: pointer;
	}

	.newsroom-items {
		flex: 1 1 auto;
		overflow-y: auto;
		padding: 0 20px;
	}

	.newsroom-item {
		position: relative;
		padding: 20px 0;
		border-bottom: 1px solid $border;

		&:hover .newsroom-item__more {
			text-decoration: underline;
		}

		&:last-child {
			border-bottom: 0;
		}

		&__meta {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 6px;
			margin-bottom: 5px;
		}

		&__badge {
			padding: 4px 8px;
			border-radius: 3px;
			background: #eceff5;
			color: $black2;
			font-size: 10px;
			font-weight: 700;
			letter-spacing: 0.05em;
			line-height: 1.4;
			text-transform: uppercase;

			&--aioseo {
				background: #e5efff;
				color: #0046b0;
			}

			&--blc {
				background: #efe9fd;
				color: #5326bd;
			}
		}

		&__version {
			padding: 1px 5px;
			border: 1px solid $border;
			border-radius: 3px;
			color: $black2;
			font-size: 10px;
			font-variant-numeric: tabular-nums;
		}

		&__date {
			// Pushed to the far edge so the dates form a column down the list.
			margin-left: auto;
			color: $placeholder-color;
			font-size: 11px;
			white-space: nowrap;
		}

		&__title {
			display: block;
			font-size: 13px;
			font-weight: 600;
			line-height: 1.35;
			text-decoration: none;
		}

		&__more {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			margin-top: 8px;
			color: $blue;
			font-size: 12px;
			font-weight: 600;
		}

		&__excerpt {
			margin: 4px 0 0;
			color: $black2;
			font-size: 12px;
			line-height: 1.45;
			// Clamped rather than truncated server-side so translations aren't cut mid-word.
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
	}

	a.newsroom-item__title {
		color: $black;

		&::after {
			content: "";
			position: absolute;
			inset: 0;
		}

		&:hover {
			color: $blue;
		}
	}

	.newsroom-see-all {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		padding: 14px 20px;
		border-top: 1px solid $border;
		background: $box-background;
		text-decoration: none;

		strong {
			display: block;
			color: $blue;
			font-size: 13px;
		}

		span span {
			color: $placeholder-color;
			font-size: 11px;
		}

		&__arrow {
			color: $blue;
			font-weight: 700;
		}
	}

	.newsroom-overlay {
		position: fixed;
		inset: 0;
		z-index: 100099;
		background: rgba($black, 0.28);
	}
}

.newsroom-slide-enter-active,
.newsroom-slide-leave-active {
	transition: transform 0.25s ease;
}

.newsroom-slide-enter-from,
.newsroom-slide-leave-to {
	transform: translateX(100%);
}
</style>