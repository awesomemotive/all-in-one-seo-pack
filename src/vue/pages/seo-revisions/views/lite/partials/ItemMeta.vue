<template>
	<div class="aioseo-seo-revisions-comparison__item-meta">
		<div class="aioseo-seo-revisions-comparison__item-meta__col">
			<div class="aioseo-seo-revisions-comparison__item-meta__author-avatar">
				<util-user-avatar
					:src="currentUser.avatar.url"
					:size="currentUser.avatar.size"
				/>
			</div>
		</div>

		<div class="aioseo-seo-revisions-comparison__item-meta__col">
			<div class="aioseo-wrapper">
				<div class="aioseo-seo-revisions-comparison__item-meta__author">
					<span>
						Revision by
					</span>

					<span>
						{{ currentUser.display_name }}
					</span>
				</div>

				<div class="aioseo-seo-revisions-comparison__item-meta__note">
					SEO Revision note
				</div>

				<button class="aioseo-seo-revisions-comparison__item-meta__btn-edit-note">
					Edit Note
				</button>
			</div>

			<div class="aioseo-seo-revisions-comparison__item-meta__date">
				1 week ago (2 Dec, 2022 @ 18:28:57)
			</div>
		</div>

		<div class="aioseo-seo-revisions-comparison__item-meta__col">
			<slot name="btn-restore"/>
		</div>
	</div>
</template>

<script setup>
import {
	useSeoRevisionsStore
} from '@/vue/stores'

import { storeToRefs } from 'pinia'

import UtilUserAvatar from '@/vue/components/common/util/UserAvatar'

const { currentUser } = storeToRefs(useSeoRevisionsStore())
</script>

<style lang="scss" scoped>
.aioseo-seo-revisions-comparison__item-meta {
	align-items: center;
	color: $black;
	display: grid;
	font-size: 12px;
	font-style: normal;
	gap: 6px;
	grid-template-columns: 30px minmax(0, 1fr);
	grid-auto-flow: column;
	padding: 6px;

	* {
		line-height: normal;
	}

	&__col {
		.aioseo-wrapper {
			display: flex;
		}
	}

	&__author-avatar {
		height: 30px;
		width: 30px;
	}

	&__title {
		font-weight: 700;
		text-align: right;
	}

	&__author {
		flex: 0 0 auto;
	}

	&__note,
	&__btn-edit-note {
		margin-left: 18px;
		position: relative;

		&:before {
			background-color: $placeholder-color;
			content: '';
			height: 10px;
			right: calc(100% + 9px);
			position: absolute;
			top: 50%;
			transform: translate(0, -50%);
			width: 2px;
		}
	}

	&__note {
		display: flex;
		font-weight: 700;
		flex: 0 1 auto;
		min-width: 0;
	}

	&__btn-edit-note {
		background: none;
		border: none;
		box-shadow: none;
		color: $blue;
		font-style: normal;
		font-weight: 400;
		height: auto;
		min-height: auto;
		outline-offset: 1px;
		outline: transparent solid 0;
		padding: 0;
		white-space: nowrap;
	}

	&__date {
		color: $gray3;
		margin-top: 4px;
	}
}
</style>