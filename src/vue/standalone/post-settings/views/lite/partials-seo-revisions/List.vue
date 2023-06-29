<template>
	<div class="aioseo-seo-revisions-list">
		<seo-revision-list-item
			v-for="index in 13"
			:key="'seo-revision-list-item-'+index"
		/>
	</div>
</template>

<script>
import SeoRevisionListItem from './ListItem'

export default {
	components : {
		SeoRevisionListItem
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-revisions-list {
	--border-size: 2px;
	--bullet-ms: 12px;
	--bullet-size: 12px;
	--item-p: 10px;

	position: relative;

	&:before {
		background-color: $input-border;
		content: '';
		height: calc(100% - var(--item-p));
		left: calc(var(--border-size) / 2);
		position: absolute;
		top: var(--item-p);
		width: var(--border-size);
	}

	.aioseo-seo-revision-list-item {
		margin-left: calc(var(--bullet-ms) * 2);
		padding: var(--item-p);

		&:before {
			background-color: $input-border;
			border-radius: 50%;
			content: '';
			height: var(--bullet-size);
			left: calc((var(--bullet-ms) + var(--border-size)) * -2);
			position: absolute;
			top: var(--item-p);
			width: var(--bullet-size);
		}

		&:first-of-type {
			&:before {
				background-color: $blue;
			}
		}

		&:last-of-type {
			&:after {
				background-color: #fff;
				content: '';
				height: calc(100% - (var(--item-p) + var(--bullet-size)));
				left: calc(var(--bullet-ms) * -2 + var(--border-size) / 2);
				position: absolute;
				top: calc(var(--item-p) + var(--bullet-size));
				width: var(--border-size);
			}
		}
	}
}
</style>