<template>
	<core-settings-row
		name="Search"
		key="Search"
	>
		<template #content>
			<div>
				<preview
					:preview-data="getPreview()"
					:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.search.useDefaultTemplate"
				/>

				<grid-row>
					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.search.useDefaultTemplate"
							class="current-item"
						/>
						{{ strings.useDefaultTemplate }}
					</grid-column>
				</grid-row>

				<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.archives.search.useDefaultTemplate">
					<grid-column
						v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length"
					>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.search.showPrefixCrumb"
							class="current-item"
						/>
						{{ strings.showPrefixLabel }}
					</grid-column>

					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.search.showHomeCrumb"
							class="current-item"
						/>
						{{ strings.showHomeLabel }}
					</grid-column>

					<grid-column>
						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.search.template"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-search"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_search_result_format',
								'breadcrumb_search_string',
								'breadcrumb_link'
							]"
						/>
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
				showPrefixLabel    : this.$t.__('Show prefix link', this.$td),
				searchString       : this.$t.__('search term', this.$td)
			}
		}
	},
	methods : {
		getPreview () {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const archiveOptions = this.optionsStore.dynamicOptions.breadcrumbs.archives.search
			const useDefault = archiveOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && archiveOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && archiveOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				this.getTemplate()
			]
		},
		getTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.search.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.search : this.optionsStore.dynamicOptions.breadcrumbs.archives.search.template
			return template.replace(/#breadcrumb_search_result_format/g, this.optionsStore.options.breadcrumbs.searchResultFormat).replace(/#breadcrumb_search_string/g, this.strings.searchString)
		}
	}
}
</script>