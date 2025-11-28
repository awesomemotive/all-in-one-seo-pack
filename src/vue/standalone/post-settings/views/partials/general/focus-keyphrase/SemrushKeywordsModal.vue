<template>
	<core-modal
		:show="show"
		@close="handleClose"
		:classes="[ 'aioseo-focus-keyphrase-panel-modal' ]"
	>
		<template #headerTitle>
			{{ strings.modalTitle }}
		</template>

		<template #body>
			<div class="aioseo-modal-content has-padding">
				<core-alert
					v-if="isUnlicensed"
					type="blue"
					v-html="strings.upsell"
				/>

				<div class="aioseo-settings-row">
					<div class="settings-name">
						<div class="name">
							{{ strings.showResultsFor }}
						</div>
					</div>
					<base-select
						class="semrush-country-selector"
						size="medium"
						:options="countryOptions"
						:placeholder="strings.selectPriceIndicator"
						v-model="selectedCountry"
					/>
				</div>

				<semrush-keywords-table
					:keywords="keywords"
					:loading="loading"
					:error="error"
					:focus-keyphrase="focusKeyphrase"
					:additional-keyphrases="additionalKeyphrases"
					:adding-index="addingIndex"
					:removing-index="removingIndex"
					:max-additional-keyphrases="maxAdditionalKeyphrases"
					:is-unlicensed="isUnlicensed"
					@add="handleAdd"
					@remove="handleRemove"
					@navigate="handleNavigate"
				/>
			</div>
		</template>
	</core-modal>
</template>

<script>
import links from '@/vue/utils/links'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import { getSemrushDatabaseOptions } from '@/vue/utils/semrushKeywords'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SemrushKeywordsTable from './SemrushKeywordsTable'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'close', 'add', 'remove', 'navigate', 'country-changed' ],
	components : {
		CoreAlert,
		CoreModal,
		SemrushKeywordsTable
	},
	props : {
		show : {
			type    : Boolean,
			default : false
		},
		keywords : {
			type    : Array,
			default : () => []
		},
		loading : {
			type    : Boolean,
			default : false
		},
		error : {
			type    : String,
			default : ''
		},
		focusKeyphrase : {
			type     : String,
			required : true
		},
		additionalKeyphrases : {
			type    : Array,
			default : () => []
		},
		addingIndex : {
			type    : [ Number, Boolean ],
			default : false
		},
		removingIndex : {
			type    : [ Number, Boolean ],
			default : false
		},
		maxAdditionalKeyphrases : {
			type     : Number,
			required : true
		},
		isUnlicensed : {
			type    : Boolean,
			default : false
		},
		countryValue : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			selectedCountry : this.countryValue,
			strings         : {
				modalTitle : sprintf(
					// Translators: 1 - Semrush.
					__('Additional Keywords by %1$s', td),
					'Semrush'
				),
				showResultsFor       : __('Show Results For:', td),
				selectPriceIndicator : __('Select a country', td),
				upsell               : sprintf(
					// Translators: 1 - Plugin short name + Pro "AIOSEO Pro", 2 - Semrush, 3 - Link to learn more.
					__('Analyzing your content with %1$s keywords is only available to licensed %2$s users. %3$s', td),
					'Semrush',
					`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
					links.getUpsellLink('post-settings', 'semrush-keywords', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
				)
			}
		}
	},
	watch : {
		countryValue : {
			deep : true,
			handler (newValue) {
				this.selectedCountry = newValue
			}
		},
		selectedCountry : {
			deep : true,
			handler (newValue) {
				this.$emit('country-changed', newValue)
			}
		}
	},
	computed : {
		countryOptions () {
			return getSemrushDatabaseOptions()
		}
	},
	methods : {
		handleClose () {
			this.$emit('close')
		},
		handleAdd (keyphrase, index) {
			this.$emit('add', keyphrase, index)
		},
		handleRemove (keyphrase, index) {
			this.$emit('remove', keyphrase, index)
		},
		handleNavigate (keyphrase) {
			this.$emit('navigate', keyphrase)
		}
	}
}
</script>

<style lang="scss">
.aioseo-focus-keyphrase-panel-modal {
	.modal-body {
		max-height: calc(90vh - 70px);
		overflow: auto;

		.aioseo-modal-content {
			.aioseo-alert {
				margin-bottom: 20px;
			}
		}
	}

	.semrush-country-selector {
		max-width: 350px;
	}
}
</style>