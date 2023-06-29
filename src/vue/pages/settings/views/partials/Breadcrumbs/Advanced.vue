<template>
	<div class="content">
		<core-settings-row
			:name="strings.showPaged"
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.breadcrumbs.advanced.showPaged"
					name="showPaged"
					:options="[
								{ label: $constants.GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
								{ label: $constants.GLOBAL_STRINGS.on, value: true }
							]"
				/>
				<div class="aioseo-description">
					{{ strings.showPagedDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.pagedFormat" v-if="optionsStore.options.breadcrumbs.advanced.showPaged"
		>
			<template #content>
				<core-html-tags-editor
					v-model="optionsStore.options.breadcrumbs.advanced.pagedFormat"
					:line-numbers="false"
					single
					checkUnfilteredHtml
					tags-context="breadcrumbs-format-paged"
					:minimum-line-numbers="3"
					:showAllTagsLink="false"
					:default-tags="['breadcrumb_format_page_number']"
					tags-description=""
				/>

				<div class="aioseo-description">
					{{ strings.pagedFormatDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.taxonomySkipUnselected"
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.breadcrumbs.advanced.taxonomySkipUnselected"
					name="taxonomySkipUnselected"
					:options="[
								{ label: $constants.GLOBAL_STRINGS.include, value: false },
								{ label: $constants.GLOBAL_STRINGS.remove, value: true }
							]"
				/>
				<div class="aioseo-description">
					{{ strings.taxonomySkipUnselectedDescription }}
				</div>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreHtmlTagsEditor,
		CoreSettingsRow
	},
	data () {
		return {
			strings : {
				showPaged                         : this.$t.__('Show Paged Breadcrumb', this.$td),
				showPagedDescription              : this.$t.__('Show a breadcrumb for the current page.', this.$td),
				pagedFormat                       : this.$t.__('Paged Format', this.$td),
				pagedFormatDescription            : this.$t.__('Format the label used for the page link.', this.$td),
				taxonomySkipUnselected            : this.$t.__('Unselected Taxonomy Terms', this.$td),
				taxonomySkipUnselectedDescription : this.$t.__('Show/hide parent terms that are not explicitly selected in your post.', this.$td)
			}
		}
	}
}
</script>