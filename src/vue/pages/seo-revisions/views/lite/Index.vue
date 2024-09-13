<template>
	<div class="aioseo-seo-revisions-comparison">
		<h1 class="long-header">
			{{ strings.compareRevisionsOf }}

			&#8220;

			<a
				href="#"
				@click.prevent
			>
				8 Link Building Strategies You Need to Know
			</a>

			&#8221;
		</h1>

		<a
			href="#"
			@click.prevent
		>
			&larr; {{ strings.goToEditor }}
		</a>

		<controls/>

		<core-blur>
			<div class="aioseo-seo-revisions-comparison__item-meta-wrapper">
				<item-meta>
					<template #btn-restore>
						<base-button
							size="small-table"
							:disabled="true"
						>
							Restore This Revision
						</base-button>
					</template>
				</item-meta>
			</div>

			<div class="aioseo-seo-revisions-comparison__diff">
				<items-diff/>
			</div>
		</core-blur>

		<seo-revisions-upsell :parent-component-context="'comparison-ui'"/>
	</div>
</template>

<script>
import Controls from './partials/Controls'
import CoreBlur from '@/vue/components/common/core/Blur'
import ItemMeta from './partials/ItemMeta'
import ItemsDiff from './partials/ItemsDiff'
import SeoRevisionsUpsell from '@/vue/components/common/seo-revisions/Upsell'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Controls,
		CoreBlur,
		ItemMeta,
		ItemsDiff,
		SeoRevisionsUpsell
	},
	data () {
		return {
			strings : {
				compareRevisionsOf : __('Compare Revisions of', td),
				goToEditor         : __('Go to editor', td)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-seo-revisions-comparison {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", sans-serif;
	letter-spacing: normal;

	h1 {
		align-items: center;
		display: flex;
	}

	&__item-meta-wrapper {
		background-color: #fff;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		margin: 20px 0 20px;
	}

	&__diff {
		background-color: #fff;
		box-shadow: 0 1px 3px rgb(0 0 0 / 10%);
		margin: 20px 0 0;
		padding: 15px;
	}
}
</style>