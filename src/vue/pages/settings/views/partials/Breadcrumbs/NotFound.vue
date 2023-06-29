<template>
	<core-settings-row
		name="404"
		key="404"
	>
		<template #content>
			<div>
				<preview
					:preview-data="getPreview()"
					:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.notFound.useDefaultTemplate"
				/>

				<grid-row>
					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.notFound.useDefaultTemplate"
							class="current-item"
						/>
						{{ strings.useDefaultTemplate }}
					</grid-column>
				</grid-row>

				<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.archives.notFound.useDefaultTemplate">
					<grid-column
						v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length"
					>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.notFound.showPrefixCrumb"
							class="current-item"
						/>
						{{ strings.showPrefixLabel }}
					</grid-column>

					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.notFound.showHomeCrumb"
							class="current-item"
						/>
						{{ strings.showHomeLabel }}
					</grid-column>

					<grid-column>
						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.notFound.template"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-notFound"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_404_error_format',
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
				showPrefixLabel    : this.$t.__('Show prefix link', this.$td)
			}
		}
	},
	methods : {
		getPreview () {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const archiveOptions = this.optionsStore.dynamicOptions.breadcrumbs.archives.notFound
			const useDefault = archiveOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && archiveOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && archiveOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				this.getTemplate()
			]
		},
		getTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.notFound.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.notFound : this.optionsStore.dynamicOptions.breadcrumbs.archives.notFound.template
			return template.replace(/#breadcrumb_404_error_format/g, this.optionsStore.options.breadcrumbs.errorFormat404)
		}
	}
}
</script>