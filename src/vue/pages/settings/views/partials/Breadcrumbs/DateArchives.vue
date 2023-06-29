<template>
	<core-settings-row
		name="Date"
		key="Date"
	>
		<template #content>
			<div>
				<div class="previews-box">
					<preview
						:preview-data="getPreview('day')"
						:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate"
					/>

					<preview
						:preview-data="getPreview('month')"
						:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate"
					/>

					<preview
						:preview-data="getPreview('')"
						:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate"
					/>
				</div>

				<grid-row>
					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate"
							class="current-item"
						/>
						{{ strings.useDefaultTemplate }}
					</grid-column>
				</grid-row>

				<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate">
					<grid-column
						v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length"
					>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.showPrefixCrumb"
							class="current-item"
						/>
						{{ strings.showPrefixLabel }}
					</grid-column>

					<grid-column>
						<base-toggle
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.showHomeCrumb"
							class="current-item"
						/>
						{{ strings.showHomeLabel }}
					</grid-column>

					<grid-column>
						<strong>{{ strings.year }}</strong>

						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.template.year"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-date-archive-year"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_date_archive_year',
								'breadcrumb_link'
							]"
						/>

						<strong>{{ strings.month }}</strong>

						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.template.month"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-date-archive-month"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_date_archive_month',
								'breadcrumb_link'
							]"
						/>

						<strong>{{ strings.day }}</strong>

						<core-html-tags-editor
							v-model="optionsStore.dynamicOptions.breadcrumbs.archives.date.template.day"
							:line-numbers="true"
							checkUnfilteredHtml
							tags-context="breadcrumbs-date-archive-day"
							:minimum-line-numbers="3"
							:default-tags="[
								'breadcrumb_date_archive_day',
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
				year               : this.$t.__('Year', this.$td),
				month              : this.$t.__('Month', this.$td),
				day                : this.$t.__('Day', this.$td)
			}
		}
	},
	methods : {
		getPreview (type) {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const archiveOptions = this.optionsStore.dynamicOptions.breadcrumbs.archives.date
			const useDefault = archiveOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && archiveOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && archiveOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				breadcrumbOptions.showBlogHome && this.rootStore.aioseo.data.staticBlogPage ? 'Blog Home' : '',
				this.getYearTemplate(),
				('day' === type || 'month' === type) ? this.getMonthTemplate() : '',
				('day' === type) ? this.getDayTemplate() : ''
			]
		},
		getYearTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.date.year : this.optionsStore.dynamicOptions.breadcrumbs.archives.date.template.year
			return template.replace(/#breadcrumb_date_archive_year/g, this.strings.year)
		},
		getMonthTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.date.month : this.optionsStore.dynamicOptions.breadcrumbs.archives.date.template.month
			return template.replace(/#breadcrumb_date_archive_month/g, this.strings.month)
		},
		getDayTemplate () {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.archives.date.useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.archives.date.day : this.optionsStore.dynamicOptions.breadcrumbs.archives.date.template.day
			return template.replace(/#breadcrumb_date_archive_day/g, this.strings.day)
		}
	}
}
</script>

<style scoped>
.aioseo-html-tags-editor{
	margin-bottom: 16px;
}
</style>