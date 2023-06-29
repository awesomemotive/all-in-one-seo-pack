<template>
	<core-settings-row
		name="Author"
		key="Author"
	>
		<template #content>
			<div>
				<preview
					:preview-data="getPreview()"
					:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.author.useDefaultTemplate"
				/>

				<grid-row>
					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.author.useDefaultTemplate"
							class="current-item"
						/>
						{{ strings.useDefaultTemplate }}
					</grid-column>
				</grid-row>

				<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.archives.author.useDefaultTemplate">
					<grid-column
						v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length">
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.author.showPrefixCrumb"
							class="current-item"
						/>
						{{ strings.showPrefixLabel }}
					</grid-column>

					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.author.showHomeCrumb"
							class="current-item"
						/>
						{{ strings.showHomeLabel }}
					</grid-column>

					<grid-column>
						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.author.template"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-author"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_author_display_name',
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
				authorName         : this.$t.__('Author name', this.$td),
				authorNameFirst    : this.$t.__('First name', this.$td),
				authorNameLast     : this.$t.__('Last name', this.$td)
			}
		}
	},
	methods : {
		getPreview () {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const archiveOptions = this.optionsStore.dynamicOptions.breadcrumbs.archives.author
			const useDefault = archiveOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && archiveOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && archiveOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				this.getTemplate()
			]
		},
		getTemplate () {
			let template = this.optionsStore.dynamicOptions.breadcrumbs.archives.author.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.author : this.optionsStore.dynamicOptions.breadcrumbs.archives.author.template
			template = template.replace(/#breadcrumb_author_display_name/g, this.strings.authorName)
			template = template.replace(/#breadcrumb_author_first_name/g, this.strings.authorNameFirst)
			template = template.replace(/#breadcrumb_author_last_name/g, this.strings.authorNameLast)
			return template
		}
	}
}
</script>