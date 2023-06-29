<template>
	<div class="content">
		<core-settings-row
			class="aioseo-breadcrumbs-post-type"
			:name="postType.label"
			:key="postType.name"
			v-for="postType in postTypes"
		>
			<template #content>
				<div>
					<preview
						:preview-data="getPreview(postType)"
						:useDefaultTemplate="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].useDefaultTemplate"
					/>

					<grid-row>
						<grid-column>
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].useDefaultTemplate"
								class="current-item"
							/>
							{{ strings.useDefaultTemplate }}
						</grid-column>
					</grid-row>

					<grid-row v-if="!optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].useDefaultTemplate">
						<grid-column
							v-if="optionsStore.options.breadcrumbs.breadcrumbPrefix && optionsStore.options.breadcrumbs.breadcrumbPrefix.length">
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showPrefixCrumb"
								class="current-item"
							/>
							{{ strings.showPrefixLabel }}
						</grid-column>

						<grid-column>
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showHomeCrumb"
								class="current-item"
							/>
							{{ strings.showHomeLabel }}
						</grid-column>

						<grid-column v-if="postTypeHasArchive(postType)">
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showArchiveCrumb"
								class="current-item"
							/>
							{{ strings.showPostTypeArchiveLabel }}
						</grid-column>

						<grid-column v-if="getPostTaxonomyOptions(postType).length">
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showTaxonomyCrumbs"
								class="current-item"
							/>
							{{ strings.showTaxonomyLabel }}
						</grid-column>

						<grid-column v-if="postTypeIsHierarchical(postType)">
							<base-toggle
								v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showParentCrumbs"
								class="current-item"
							/>
							{{ strings.showParentLabel }}
						</grid-column>

						<grid-column>
							<core-settings-row
								:name="postTypeIsHierarchical(postType) && optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showParentCrumbs ? strings.singleTemplateLabel : ''"
								leftSize="12"
								rightSize="12"
							>
								<template #content>
									<core-html-tags-editor
										v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].template"
										:line-numbers="true"
										:tags-context="'breadcrumbs-post-type-'+postType.name"
										:minimum-line-numbers="3"
										checkUnfilteredHtml
										:default-tags="[
											'breadcrumb_post_title',
											'breadcrumb_link'
										]"
									/>
								</template>
							</core-settings-row>
						</grid-column>

						<grid-column
							v-if="postTypeIsHierarchical(postType) && optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showParentCrumbs">
							<core-settings-row
								:name="strings.parentTemplateLabel"
								leftSize="12"
								rightSize="12"
							>
								<template #content>
									<core-html-tags-editor
										v-model="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].parentTemplate"
										:line-numbers="true"
										:tags-context="'breadcrumbs-post-type-'+postType.name"
										:minimum-line-numbers="3"
										checkUnfilteredHtml
										:default-tags="[
											'breadcrumb_post_title',
											'breadcrumb_link'
										]"
									/>
								</template>
							</core-settings-row>
						</grid-column>

						<grid-column
							v-if="optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].showTaxonomyCrumbs && getPostTaxonomyOptions(postType).length">
							<grid-column>
								<div class="taxonomy-select">
									{{ strings.selectTaxonomyLabel }}
									<base-select
										size="medium"
										:options="getPostTaxonomyOptions(postType)"
										:placeholder="strings.selectTaxonomy"
										:modelValue="getPostTaxonomyOptions(postType).find(t => t.value === getPostTypeTaxonomy(postType).name)"
										@update:modelValue="value => optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].taxonomy = value.value"
									/>
								</div>
							</grid-column>

							<grid-column class="aioseo-description">
								{{ strings.selectTaxonomyDescription }}
							</grid-column>
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
				useDefaultTemplate        : this.$t.__('Use a default template', this.$td),
				selectTaxonomyLabel       : this.$t.__('Taxonomy priority:', this.$td),
				selectTaxonomy            : this.$t.__('Select a taxonomy', this.$td),
				selectTaxonomyDescription : this.$t.__('Choose taxonomy that should have a priority for this post type.', this.$td),
				showPostTypeArchiveLabel  : this.$t.__('Show post type archive link', this.$td),
				showTaxonomyLabel         : this.$t.__('Show taxonomy link', this.$td),
				showHomeLabel             : this.$t.__('Show homepage link', this.$td),
				showPrefixLabel           : this.$t.__('Show prefix link', this.$td),
				showParentLabel           : this.$t.__('Show parent item link', this.$td),
				singleTemplateLabel       : this.$t.__('Single item template', this.$td),
				parentTemplateLabel       : this.$t.__('Parent item template', this.$td)
			}
		}
	},
	computed : {
		postTypes () {
			return this.rootStore.aioseo.postData.postTypes || []
		}
	},
	methods : {
		getPreview (postType) {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			const postTypeOptions = this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name]
			const useDefault = postTypeOptions.useDefaultTemplate
			return [
				(useDefault && breadcrumbOptions.breadcrumbPrefix) || (!useDefault && postTypeOptions.showPrefixCrumb) ? breadcrumbOptions.breadcrumbPrefix : '',
				(useDefault && breadcrumbOptions.homepageLink) || (!useDefault && postTypeOptions.showHomeCrumb) ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				breadcrumbOptions.showBlogHome && this.rootStore.aioseo.data.staticBlogPage && 'post' === postType.name ? 'Blog Home' : '',
				this.postTypeHasArchive(postType) && (useDefault || (!useDefault && postTypeOptions.showArchiveCrumb)) ? postType.label : '',
				useDefault || (!useDefault && postTypeOptions.showTaxonomyCrumbs) ? this.getPostTypeTaxonomyTemplate(postType) : '',
				this.postTypeIsHierarchical(postType) && (useDefault || (!useDefault && postTypeOptions.showParentCrumbs)) ? this.getPostTypeParentTemplate(postType) : '',
				this.getPostTypeTemplate(postType)
			]
		},
		postTypeIsHierarchical (postType) {
			return 0 < this.rootStore.aioseo.postData.postTypes.filter(postTypeData => postTypeData.name === postType.name && postTypeData.hierarchical).length
		},
		postTypeHasArchive (postType) {
			return 0 < this.rootStore.aioseo.postData.postTypes.filter(postTypeData => postTypeData.name === postType.name && postTypeData.hasArchive).length
		},
		getPostTypeTaxonomyTemplate (postType) {
			const taxonomy = this.getPostTypeTaxonomy(postType)
			if (0 >= taxonomy.length) {
				return
			}

			const parentTemplate = this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].parentTemplate || this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].template

			const template = this.optionsStore.dynamicOptions.breadcrumbs.taxonomies[taxonomy.name].useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.taxonomies[taxonomy.name] : parentTemplate
			return template.replace(new RegExp('#breadcrumb_taxonomy_title', 'g'), taxonomy.singular + ' Parent')
		},
		getPostTypeParentTemplate (postType) {
			const template = this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.postTypes[postType.name] : this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].parentTemplate
			return template.replace(new RegExp('#breadcrumb_post_title', 'g'), postType.singular + ' Parent')
		},
		getPostTypeTemplate (postType) {
			let template = this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].useDefaultTemplate ? this.rootStore.aioseo.breadcrumbs.defaultTemplates.postTypes[postType.name] : this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].template
			if ('product' === postType.name) {
				template = template.replace(new RegExp('#breadcrumb_wc_product_sku', 'g'), 'SKU')
				template = template.replace(new RegExp('#breadcrumb_wc_product_price', 'g'), '$10.00')
				template = template.replace(new RegExp('#breadcrumb_wc_product_brand', 'g'), 'Brand')
			}
			return template.replace(new RegExp('#breadcrumb_post_title', 'g'), postType.singular)
		},
		getPostTaxonomyOptions (postType) {
			return this.rootStore.aioseo.postData.taxonomies.filter(tax => postType.taxonomies.includes(tax.name)).map(tax => {
				return {
					value : tax.name,
					label : tax.label
				}
			})
		},
		getPostTypeTaxonomy (postType) {
			let taxonomies = this.rootStore.aioseo.postData.taxonomies.filter(taxData => taxData.name === this.optionsStore.dynamicOptions.breadcrumbs.postTypes[postType.name].taxonomy)
			if (0 === taxonomies.length && 0 < postType.taxonomies.length) {
				taxonomies = this.rootStore.aioseo.postData.taxonomies.filter(taxData => postType.taxonomies.includes(taxData.name))
			}

			return 0 < taxonomies.length ? taxonomies[0] : []
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-breadcrumbs-post-type {
	.taxonomy-select {
		display: flex;
		flex-wrap: wrap;
		align-items: center;

		.aioseo-select {
			max-width: 200px;
			margin-left: 10px;
		}
	}
}
</style>