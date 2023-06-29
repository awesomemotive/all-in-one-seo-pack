<template>
	<div class="content">
		<core-settings-row
			:name="taxonomy.label"
			:key="taxonomy.name"
			v-for="taxonomy in taxonomies"
		>
			<template #content>
				<div>
					<preview
						:preview-data="getPreview(taxonomy)"
						:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate"
					/>

					<grid-row>
						<grid-column>
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate"
								class="current-item"
							/>
							{{ strings.useDefaultTemplate }}
						</grid-column>
					</grid-row>

					<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate">
						<grid-column
							v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length"
						>
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].showPrefixCrumb"
								class="current-item"
							/>
							{{ strings.showPrefixLabel }}
						</grid-column>

						<grid-column>
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].showHomeCrumb"
								class="current-item"
							/>
							{{ strings.showHomeLabel }}
						</grid-column>

						<grid-column v-if="taxonomy.hierarchical">
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].showParentCrumbs"
								class="current-item"
							/>
							{{ strings.showParentLabel }}
						</grid-column>

						<grid-column>
							<core-settings-row
								:name="taxonomy.hierarchical && optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].showParentCrumbs ? strings.singleTemplateLabel : ''"
								leftSize="12"
								rightSize="12"
							>
								<template #content>
									<core-html-tags-editor
										v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].template"
										:line-numbers="true"
										checkUnfilteredHtml
										:tags-context="'breadcrumbs-taxonomy-'+taxonomy.name"
										:minimum-line-numbers="3"
										:default-tags="[
											'breadcrumb_taxonomy_title',
											'breadcrumb_link'
										]"
									/>
								</template>
							</core-settings-row>
						</grid-column>
						<grid-column
							v-if="taxonomy.hierarchical && optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].showParentCrumbs">
							<core-settings-row
								:name="strings.parentTemplateLabel"
								leftSize="12"
								rightSize="12"
							>
								<template #content>
									<core-html-tags-editor
										v-model="optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].parentTemplate"
										:line-numbers="true"
										checkUnfilteredHtml
										:tags-context="'breadcrumbs-taxonomy-'+taxonomy.name"
										:minimum-line-numbers="3"
										:default-tags="[
							'breadcrumb_taxonomy_title',
							'breadcrumb_link'
						]"
									>
									</core-html-tags-editor>
								</template>
							</core-settings-row>
						</grid-column>
					</grid-row>
				</div>
			</template>
		</core-settings-row>
	</div>
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
				useDefaultTemplate  : this.$t.__('Use a default template', this.$td),
				showHomeLabel       : this.$t.__('Show homepage link', this.$td),
				showPrefixLabel     : this.$t.__('Show prefix link', this.$td),
				showParentLabel     : this.$t.__('Show parent item link', this.$td),
				singleTemplateLabel : this.$t.__('Single item template', this.$td),
				parentTemplateLabel : this.$t.__('Parent item template', this.$td)
			}
		}
	},
	methods : {
		getPreview (taxonomy) {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const taxOptions = this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name]
			const useDefault = taxOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && taxOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && taxOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				([ 'category', 'post_tag' ].includes(taxonomy.name) && breadcrumbOptions.showBlogHome && this.rootStore.aioseo.data.staticBlogPage) ? 'Blog Home' : '',
				this.taxonomyHasArchive(taxonomy) ? taxonomy.label : '',
				taxonomy.hierarchical && (useDefault || (!useDefault && taxOptions.showParentCrumbs)) ? this.getTaxonomyParentTemplate(taxonomy) : '',
				this.getTaxonomyTemplate(taxonomy)
			]
		},
		taxonomyHasArchive (taxonomy) {
			return 0 < this.rootStore.aioseo.postData.taxonomies.filter(taxonomyData => taxonomyData.name === taxonomy.name && taxonomyData.hasArchive).length
		},
		getTaxonomyParentTemplate (taxonomy) {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.taxonomies[taxonomy.name] : this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].parentTemplate
			return template.replace(new RegExp('#breadcrumb_taxonomy_title', 'g'), taxonomy.singular + ' Parent')
		},
		getTaxonomyTemplate (taxonomy) {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.taxonomies[taxonomy.name] : this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].template
			return template.replace(new RegExp('#breadcrumb_taxonomy_title', 'g'), taxonomy.singular)
		}
	},
	computed : {
		taxonomies () {
			return this.rootStore.aioseo.postData.taxonomies
		}
	}
}
</script>