<template>
	<div
		v-if="errors.length"
		class="robots-editor-rule-error-alert"
	>
		<template
			v-for="(errorByType, type) in errorsByType"
			:key="type + '-errors'"
		>
			<core-alert
				:type="type"
				size="small"
			>
				<div
					v-for="(error, index) in errorByType"
					:key="index"
				>
					<b>{{ getErrorMessage(error) }}</b>
				</div>
			</core-alert>
		</template>
	</div>
</template>

<script>
import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : { CoreAlert },
	props      : {
		errors : {
			type     : Array,
			required : true
		}
	},
	data () {
		return {
			strings : {
				allowTakesPrecedence       : __('The "Allow" rule takes precedence.', td),
				fromTheNetwork             : __('from the network level', td),
				invalidCleanParam          : __('Clean-param must start with at least one param which is optionally followed by one path.', td),
				invalidCrawlDelay          : __('Crawl-delay must be a number starting from 1.', td),
				conflictingCrawlDelay      : __('For some crawlers, encountering conflicting "Crawl-delay" might lead to unpredictable behavior.', td),
				networkRuleTakesPrecedence : __('The network rule takes precedence.', td)
			}
		}
	},
	computed : {
		errorsByType () {
			return this.errors.reduce((errorsByType, error) => {
				if (!errorsByType[error.type]) {
					errorsByType[error.type] = []
				}

				errorsByType[error.type].push(error)

				return errorsByType
			}, {})
		}
	},
	methods : {
		getErrorMessage (error) {
			if ('duplicateRule' === error.message) {
				return sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					__('This rule is a duplicate of rule #%1$s%2$s.', td),
					error.duplicateIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			if ('equivalentPath' === error.message) {
				return sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					__('Equivalent to rule #%1$s%2$s. The trailing wildcard is ignored.', td),
					error.equivalentIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			if ('conflictingPath' === error.message) {
				return sprintf(
					// Translators: 1 - The table row index, 2 - Warn this index is on the network level, 3 - Additional warnings.
					__('This rule conflicts with rule #%1$s%2$s.%3$s', td),
					error.conflictingIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : '',
					error.isNetworkIndex ? ` ${this.strings.networkRuleTakesPrecedence}` : ` ${this.strings.allowTakesPrecedence}`
				)
			}

			if ('defaultRuleOverride' === error.message) {
				return sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					__('This rule overrides the default rule #%1$s%2$s.', td),
					error.overriddenIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			if ('conflictingCrawlDelay' === error.message) {
				return sprintf(
					// Translators: 1 - The table row index, 2 - Warn this index is on the network level, 3 - Additional warnings.
					__('This rule conflicts with rule #%1$s%2$s.%3$s', td),
					error.conflictingIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : '',
					` ${this.strings.conflictingCrawlDelay}`
				)
			}

			return this.strings[error.message]
		}
	}
}
</script>