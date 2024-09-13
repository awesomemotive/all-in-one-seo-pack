import { computed } from 'vue'

import { OG_TYPE_OPTIONS } from '@/vue/plugins/constants'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const usePostSocial = () => {
	const objectTypeOptions = computed(() => {
		return [ {
			groupLabel : __('Default', td),
			options    : [ {
				label : __('Default Object Type (Set in Social Networks)', td),
				value : 'default'
			} ]
		} ].concat(OG_TYPE_OPTIONS)
	})

	const twitterCardOptions = [
		{ label: __('Default (Set under Social Networks)', td), value: 'default' },
		{ label: __('Summary', td), value: 'summary' },
		{ label: __('Summary with Large Image', td), value: 'summary_large_image' }
	]

	const getObjectTypeLabelByOption = (option) => {
		let label = ''
		objectTypeOptions.value.forEach(group => {
			const wantedOption = group.options.find(o => o.value === option)
			if (wantedOption) {
				label = wantedOption?.label || ''
			}
		})

		return label
	}

	const getTwitterCardLabelByOption = (option) => {
		return twitterCardOptions.find(t => t.value === option)?.label || ''
	}

	return {
		getObjectTypeLabelByOption,
		getTwitterCardLabelByOption,
		objectTypeOptions,
		twitterCardOptions
	}
}