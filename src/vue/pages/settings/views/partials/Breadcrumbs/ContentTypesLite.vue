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
					<preview :preview-data="getPreview(postType)" />

					<grid-row>
						<grid-column>
							<base-toggle
								class="current-item"
							/>
							{{ strings.useDefaultTemplate }}
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

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import Preview from './Preview'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreSettingsRow,
		GridColumn,
		GridRow,
		Preview
	},
	data () {
		return {
			strings : {
				useDefaultTemplate : __('Use a default template', td)
			}
		}
	},
	computed : {
		postTypes () {
			return this.rootStore.aioseo.postData.postTypes
		}
	},
	methods : {
		getPreview (postType) {
			const breadcrumbOptions = this.optionsStore.options.breadcrumbs
			return [
				breadcrumbOptions.breadcrumbPrefix ? breadcrumbOptions.breadcrumbPrefix : '',
				breadcrumbOptions.homepageLink ? (breadcrumbOptions.homepageLabel ? breadcrumbOptions.homepageLabel : 'Home') : '',
				breadcrumbOptions.showBlogHome && 'post' === postType.name ? 'Blog Home' : '',
				!([ 'post', 'page', 'attachment' ].includes(postType.name)) ? postType.label : '',
				this.getPostTypeTaxonomy(postType).singular,
				this.postTypeIsHierarchical(postType) ? 'Post Parent' : '',
				this.getPostTypeTemplate(postType)
			]
		},
		postTypeIsHierarchical (postType) {
			return 0 < this.rootStore.aioseo.postData.postTypes.filter(postTypeData => postTypeData.name === postType.name && postTypeData.hierarchical).length
		},
		postTypeHasArchive (postType) {
			return 0 < this.rootStore.aioseo.postData.postTypes.filter(postTypeData => postTypeData.name === postType.name && postTypeData.hasArchive).length
		},
		getPostTypeTemplate (postType, replace = true) {
			let template = this.rootStore.aioseo.breadcrumbs.defaultTemplate
			if (replace) {
				template = template.replace(new RegExp('#breadcrumb_label', 'g'), postType.singular)
			} else {
				template = template.replace(new RegExp('#breadcrumb_label', 'g'), '#breadcrumb_post_title_' + postType.name)
			}
			return template
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
			const taxonomies = this.rootStore.aioseo.postData.taxonomies.filter(taxData => postType.taxonomies.includes(taxData.name))
			return 0 < taxonomies.length ? taxonomies[0] : []
		}
	}
}
</script>