<template>
	<core-settings-row
		name="Blog"
		key="Blog"
		v-if="rootStore.aioseo.data.staticBlogPage"
	>
		<template #content>
			<div>
				<preview
					:preview-data="getPreview()"
					:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.blog.useDefaultTemplate"
				/>

				<grid-row>
					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.blog.useDefaultTemplate"
							class="current-item"
						/>
						{{ strings.useDefaultTemplate }}
					</grid-column>
				</grid-row>

				<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.archives.blog.useDefaultTemplate">
					<grid-column
						v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length"
					>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.blog.showPrefixCrumb"
							class="current-item"
						/>
						{{ strings.showPrefixLabel }}
					</grid-column>

					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.blog.showHomeCrumb"
							class="current-item"
						/>
						{{ strings.showHomeLabel }}
					</grid-column>

					<grid-column>
						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.blog.template"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-blog-archive"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_blog_page_title',
								'breadcrumb_link'
							]"
						>
						</core-html-tags-editor>
					</grid-column>
				</grid-row>
			</div>
		</template>
	</core-settings-row>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import Preview from './Preview'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreHtmlTagsEditor,
		CoreSettingsRow,
		GridColumn,
		GridRow,
		Preview
	},
	data () {
		return {
			strings : {
				useDefaultTemplate : this.$t.__('Use a default template', this.$td),
				showHomeLabel      : this.$t.__('Show homepage link', this.$td),
				showPrefixLabel    : this.$t.__('Show prefix link', this.$td)
			}
		}
	},
	methods : {
		getPreview () {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const archiveOptions    = this.optionsStore.dynamicOptions.breadcrumbs.archives.blog
			const useDefault        = archiveOptions.useDefaultTemplate

			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && archiveOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && archiveOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				this.getTemplate()
			]
		},
		getTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.blog.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.blog : this.optionsStore.dynamicOptions.breadcrumbs.archives.blog.template
			return template.replace(/#breadcrumb_blog_page_title/g, this.rootStore.aioseo.data.staticBlogPageTitle)
		}
	}
}
</script>