<template>
	<grid-row class="aioseo-separators">
		<grid-column
			xs="2"
			sm="1"
			v-if="!showMoreSeparators && hiddenSeparator"
		>
			<div class="active separator">
				{{ decodedCustomSeparator }}
			</div>
		</grid-column>

		<grid-column
			xs="2"
			sm="1"
			v-for="(separator, index) in decodedSeparators"
			:key="index"
		>
			<div
				@click="setSeparator(separator)"
				class="separator"
				:class="{ active: optionsSeparator === separator }"
			>
				{{ separator }}
			</div>
		</grid-column>

		<template
			v-if="showMoreSeparators"
		>
			<grid-column
				xs="2"
				sm="1"
				v-for="(separator, index) in decodedMoreSeparators"
				:key="`m_${index}`"
			>
				<div
					@click="setSeparator(separator)"
					class="separator"
					:class="{ active: optionsSeparator === separator }"
				>
					{{ separator }}
				</div>
			</grid-column>
		</template>

		<grid-column
			:xs="hiddenSeparator ? '3' : '4'"
			v-if="!showMoreSeparators"
		>
			<div class="show-more">
				<a
					href="#"
					@click.prevent="showMoreSeparators = true"
				>
					{{ strings.showMore }}&hellip;
				</a>
			</div>
		</grid-column>

		<grid-column
			v-if="showMoreSeparators"
			class="custom-separator-col"
		>
			<div class="custom-separator">
				{{ strings.custom }}

				<base-input
					:spellcheck="false"
					size="medium"
					v-model="customSeparator"
				/>
			</div>
		</grid-column>

		<grid-column
			xs="2"
			v-if="showMoreSeparators"
		>
			<div class="show-more">
				<a
					href="#"
					@click.prevent="showMoreSeparators = false"
				>
					{{ strings.showLess }}&hellip;
				</a>
			</div>
		</grid-column>
	</grid-row>
</template>

<script>
import {
	useSettingsStore
} from '@/vue/stores'

import { sanitizeString } from '@/vue/utils/strings'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	emits      : [ 'update:separator' ],
	components : {
		GridColumn,
		GridRow
	},
	props : {
		optionsSeparator : {
			type     : String,
			required : true
		},
		showMoreSlug : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			strings : {
				custom   : this.$t.__('Custom separator:', this.$td),
				showMore : this.$t.__('Show More', this.$td),
				showLess : this.$t.__('Show Less', this.$td)
			},
			showMoreSeparators : false,
			showMoreInitial    : true,
			customSeparator    : null,
			separators         : [
				'&ndash;',
				'&raquo;',
				'&rsaquo;',
				'&#x2023;',
				'&rarr;',
				'&bull;',
				'&#47;',
				'&#124;'
			],
			moreSeparators : [
				'&#45;',
				'&mdash;',
				'&laquo;',
				'&larr;',
				'&gt;',
				'&ge;',
				'&#92;',
				'&#43;',
				'&#9658;'
			]
		}
	},
	watch : {
		showMoreSeparators (newValue) {
			if (this.showMoreInitial) {
				this.showMoreInitial = false
				return
			}
			this.settingsStore.toggleRadio({ slug: this.showMoreSlug, value: newValue })
		},
		customSeparator (newVal) {
			if (null === newVal) {
				return
			}

			this.$emit('update:separator', sanitizeString(newVal))

			if (this.separators.concat(this.moreSeparators).concat(this.decodedSeparators).concat(this.decodedMoreSeparators).includes(newVal)) {
				this.customSeparator = null
			}
		}
	},
	computed : {
		hiddenSeparator () {
			return this.optionsSeparator === this.customSeparator || this.decodedMoreSeparators.includes(this.optionsSeparator)
				? this.optionsSeparator
				: null
		},
		decodedSeparators () {
			return this.separators.map(separator => sanitizeString(separator))
		},
		decodedMoreSeparators () {
			return this.moreSeparators.map(separator => sanitizeString(separator))
		},
		decodedCustomSeparator () {
			return sanitizeString(this.hiddenSeparator)
		}
	},
	methods : {
		setSeparator (separator) {
			this.customSeparator = null
			this.$emit('update:separator', separator)
		}
	},
	mounted () {
		this.showMoreSeparators = this.settingsStore.settings.toggledRadio[this.showMoreSlug]
		this.customSeparator    = !this.decodedSeparators.concat(this.decodedMoreSeparators).includes(this.optionsSeparator)
			? this.optionsSeparator
			: null
	}
}
</script>

<style lang="scss">
.aioseo-separators {
	--aioseo-gutter: 8px;

	position: relative;
	margin-top: 0;
	padding-right: 100px;

	.aioseo-col {

		&.col-sm-1 {
			flex: 0 0 40px;
			max-width: none;
		}

		.separator {
			background-color: $background;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 40px;
			font-weight: 600;
			border: 1px solid $input-border;
			border-radius: 3px;
			font-size: 20px;
			cursor: pointer;

			&:hover {
				background-color: darken($background, 5%);
			}

			&.active {
				background-color: $blue;
				border-color: $blue;
				color: #fff;

				&:hover {
					background-color: $blue;
				}
			}
		}

		.show-more {
			height: 100%;
			display: flex;
			align-items: center;

			a {
				color: $placeholder-color;
			}
		}

		.custom-separator {
			font-weight: $font-bold;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			gap: 4px;
			margin-top: 8px;

			.aioseo-input-container {
				max-width: 200px;
			}
		}
	}
}
</style>