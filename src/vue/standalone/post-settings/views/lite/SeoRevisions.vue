<template>
	<div
		v-if="'metabox' === $root._data.screenContext"
		class="aioseo-seo-revisions-metabox"
	>
		<core-blur>
			<seo-revisions-list/>
		</core-blur>

		<seo-revisions-upsell :parent-component-context="'metabox'"/>
	</div>

	<div
		v-else-if="'sidebar' === $root._data.screenContext"
		class="aioseo-seo-revisions-sidebar"
	>
		<core-modal-portal
			:classes="[]"
			v-if="modalOpenSidebar"
			@close="modalOpenSidebar = false"
		>
			<template #headerTitle>
				{{ strings.seoRevisions }}
			</template>

			<template #body>
				<div class="aioseo-seo-revisions-sidebar__modal-body">
					<core-blur>
						<seo-revisions-list/>
					</core-blur>

					<seo-revisions-upsell :parent-component-context="'modal'"/>
				</div>
			</template>
		</core-modal-portal>

		<core-settings-row align>
			<template #name>
				{{ strings.seoRevisions }}
			</template>

			<template #description>
				<p class="description">{{ strings.description }}</p>
			</template>

			<template #content>
				<base-button
					class="gray small"
					@click.stop.exact="modalOpenSidebar = true"
				>
					<svg-right-arrow-short width="10"/>

					&nbsp;

					{{ strings.button }}
				</base-button>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreModalPortal from '@/vue/components/common/core/modal/Portal'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SeoRevisionsList from './partials-seo-revisions/List'
import SeoRevisionsUpsell from '@/vue/components/common/seo-revisions/Upsell'
import SvgRightArrowShort from '@/vue/components/common/svg/right-arrow/Short'

export default {
	components : {
		CoreBlur,
		CoreModalPortal,
		CoreSettingsRow,
		SeoRevisionsList,
		SeoRevisionsUpsell,
		SvgRightArrowShort
	},
	data () {
		return {
			modalOpenSidebar : false,
			strings          : {
				button       : this.$t.__('Open SEO Revisions', this.$td),
				description  : this.$t.__('With our powerful revisions feature for all your SEO data, never lose the exact SEO title or description (and more!) that helped you rank higher in search results and restore it back in a single click.', this.$td),
				seoRevisions : this.$t.__('SEO Revisions', this.$td)
			}
		}
	}
}
</script>
<style lang="scss" scoped>
.aioseo-seo-revisions-sidebar {
	&__modal-body {
		padding: 20px;

		div.aioseo-cta {
			padding: 40px;
		}
	}

	.aioseo-description {
		margin: 0;
	}
}
</style>