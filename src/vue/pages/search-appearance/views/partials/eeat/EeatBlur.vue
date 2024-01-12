<template>
	<core-blur id="aioseo-eeat-search-appearance-settings">
		<core-card
			id="aioseo-eeat-search-appearance-settings-card"
			:header-text="strings.eeatSearchAppearanceSettings"
			noSlide
		>
			<div
				class="aioseo-settings-row aioseo-section-description"
				v-html="strings.eeatSearchAppearanceSettingsDescription"
			/>

			<table class="topics-table">
				<thead>
					<tr>
						<td>
							<div class="tooltip-wrapper">
								{{ strings.name }}

								<core-tooltip>
									<svg-circle-question-mark />

									<template #tooltip>
										{{ strings.nameTooltip }}
									</template>
								</core-tooltip>
							</div>
						</td>
						<td>
							<div class="tooltip-wrapper">
								{{ strings.url }}

								<core-tooltip>
									<svg-circle-question-mark />

									<template #tooltip>
										{{ strings.urlTooltip }}
									</template>
								</core-tooltip>
							</div>
						</td>
						<td>
							<div class="tooltip-wrapper">
								{{ strings.sameAsUrls }}

								<core-tooltip>
									<svg-circle-question-mark />

									<template #tooltip>
										{{ strings.sameAsUrlsTooltip }}
									</template>
								</core-tooltip>
							</div>
						</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for="(row, index) in rows"
						:class="{ even: 0 === index % 2 }"
						:key="index"
					>
						<td class="name">
							<base-input
								size="medium"
								v-model="row.name"
								append-icon="circle-close"
								@append-icon-click="row.name = ''"
							/>
						</td>
						<td class="url">
							<base-input
								size="medium"
								v-model="row.url"
								append-icon="circle-close"
								@append-icon-click="row.url = ''"
							/>
						</td>
						<td class="same-as-urls">
							<base-select
								size="medium"
								multiple
								taggable
								:options="getJsonValue(row.sameAsUrls) || []"
								:modelValue="getJsonValue(row.sameAsUrls) || []"
								:placeholder="strings.sameAsUrlsPlaceholder"
							/>
						</td>
						<td class="actions">
							<core-tooltip
								type="action"
							>
								<svg-trash/>

								<template #tooltip>
									{{ strings.delete }}
								</template>
							</core-tooltip>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4">
							<base-button
								size="small-table"
								type="black"
								@click="addRow(null)"
							>
								<svg-circle-plus />
								{{ strings.addItem }}
							</base-button>
						</td>
					</tr>
				</tfoot>
			</table>
		</core-card>
	</core-blur>
</template>

<script setup>
import { useJsonValues } from '@/vue/composables'
import { __, sprintf } from '@wordpress/i18n'

import BaseButton from '@/vue/components/common/base/Button'
import BaseInput from '@/vue/components/common/base/Input'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgTrash from '@/vue/components/common/svg/Trash'

const { getJsonValue } = useJsonValues()

const rows = [
	{
		name       : 'WordPress',
		url        : 'https://wordpress.org',
		sameAsUrls : []
	},
	{
		name       : 'SEO',
		url        : 'https://aioseo.com',
		sameAsUrls : []
	},
	{
		name       : 'Schema Markup',
		url        : 'https://schema.org',
		sameAsUrls : []
	}
]

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	eeatSearchAppearanceSettings            : __('Author Experience Topics (E-E-A-T)', td),
	eeatSearchAppearanceSettingsDescription : sprintf(
		// Translators: 1 - Opening <code> tag, 2 - Closing </code> tag.
		__('The following settings will be added directly to an author\'s schema meta data via the %1$sknowsAbout%2$s property. This property helps with the Experience aspect of Google\'s E-E-A-T guidelines. After setting the global options here, you can add them directly in an authors profile page.', td),
		'<code>',
		'</code>'
	),
	name                  : __('Name', td),
	url                   : __('URL', td),
	sameAsUrls            : __('Same As URLs', td),
	addItem               : __('Add Item', td),
	delete                : __('Delete', td),
	nameTooltip           : __('The name of the item the author knows about (e.g. "Amazon").', td),
	urlTooltip            : __('The URL of the item the author knows about (e.g. "https://amazon.com").', td),
	sameAsUrlsTooltip     : __('Additional URLs to help identify the item (e.g. "https://en.wikipedia.org/wiki/Amazon_(company)").', td),
	sameAsUrlsPlaceholder : __('Enter a URL and press enter', td)
}
</script>

<style lang="scss">
@import '@/vue/assets/scss/redirects/table.scss';

.topics-table {
	.tooltip-wrapper {
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.same-as-urls {
		max-width: 300px;
		min-width: 300px;
	}

	svg.aioseo-circle-plus {
		width: 14px;
		height: 14px;
		margin-right: 8px;
	}
}
</style>