<template>
	<div class="aioseo-seo-revision-list-item">
		<div class="aioseo-seo-revision-list-item__author">
			<span class="aioseo-seo-revision-list-item__author__avatar">
				<util-user-avatar
					:src="currentUser.avatar.url"
					:size="currentUser.avatar.size"
				/>
			</span>

			<span class="aioseo-seo-revision-list-item__author__name">
				{{ truncate(currentUser.display_name, 30) }}
			</span>
		</div>

		<div class="aioseo-seo-revision-list-item__date">
			1 week ago (2 Dec, 2022 @ 18:28:57)
		</div>

		<div class="aioseo-seo-revision-list-item__action">
			<span class="aioseo-seo-revision-list-item__action__compare">
				Compare
			</span>

			<span class="aioseo-seo-revision-list-item__action__view">
				<svg-eye width="18"/>
			</span>

			<span class="aioseo-seo-revision-list-item__action__delete">
				<svg-trash width="18"/>
			</span>
		</div>
	</div>
</template>

<script setup>
import {
	useSeoRevisionsStore
} from '@/vue/stores'

import { truncate } from '@/vue/utils/html'
import { storeToRefs } from 'pinia'

import SvgEye from '@/vue/components/common/svg/Eye'
import SvgTrash from '@/vue/components/common/svg/Trash'
import UtilUserAvatar from '@/vue/components/common/util/UserAvatar'

const { currentUser } = storeToRefs(useSeoRevisionsStore())
</script>

<style lang="scss" scoped>
.aioseo-seo-revision-list-item {
	align-items: center;
	background-color: $blue4;
	display: flex;
	flex-wrap: wrap;
	font-size: 14px;
	gap: 8px;
	position: relative;

	* {
		line-height: normal;
	}

	+ #{&} {
		background-color: $background;
		margin: 8px 0 0;
	}

	&__author {
		align-items: center;
		display: flex;
		gap: 8px;

		&__avatar {
			flex: 0 0 16px;
			height: 16px;
			width: 16px;
		}

		&__name {
			color: $black;
			font-weight: 400;
		}
	}

	&__date {
		color: $placeholder-color;
		font-weight: 400;
	}

	&__action {
		align-items: center;
		display: flex;
		flex: 1 1 auto;
		font-size: 12px;
		gap: 12px;
		justify-content: end;

		&__compare {
			color: $blue;
		}

		&__delete,
		&__view {
			height: 18px;
			width: 18px;

			svg {
				color: $placeholder-color;
			}
		}
	}
}
</style>