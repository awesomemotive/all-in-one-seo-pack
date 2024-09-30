export const objectType = {
	computed : {
		objectTypeOptions () {
			return [ {
				groupLabel : this.$t.__('Default', this.$td),
				options    : [ {
					label : this.$t.__('Default Object Type (Set in Social Networks)', this.$td),
					value : 'default'
				} ]
			} ].concat(this.$constants.OG_TYPE_OPTIONS)
		}
	},
	methods : {
		getObjectTypeLabelByOption (option) {
			let label = ''
			this.objectTypeOptions.forEach(group => {
				const wantedOption = group.options.find(o => o.value === option)
				if (wantedOption) {
					label = wantedOption?.label || ''
				}
			})

			return label
		}
	}
}

export const twitterCard = {
	computed : {
		twitterCardOptions () {
			return [
				{ label: this.$t.__('Default (Set under Social Networks)', this.$td), value: 'default' },
				{ label: this.$t.__('Summary', this.$td), value: 'summary' },
				{ label: this.$t.__('Summary with Large Image', this.$td), value: 'summary_large_image' }
			]
		}
	},
	methods : {
		getTwitterCardLabelByOption (option) {
			return this.twitterCardOptions.find(t => t.value === option)?.label || ''
		}
	}
}