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

export default {
	components : { CoreAlert },
	computed   : {
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
				return this.$t.sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					this.$t.__('This rule is a duplicate of rule #%1$s%2$s.', this.$td),
					error.duplicateIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			if ('equivalentPath' === error.message) {
				return this.$t.sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					this.$t.__('Equivalent to rule #%1$s%2$s. The trailing wildcard is ignored.', this.$td),
					error.equivalentIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			if ('conflictingPath' === error.message) {
				return this.$t.sprintf(
					// Translators: 1 - The table row index, 2 - Warn this index is on the network level, 3 - Warn about precedence.
					this.$t.__('This rule conflicts with rule #%1$s%2$s.%3$s', this.$td),
					error.conflictingIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : '',
					error.isNetworkIndex ? ` ${this.strings.networkRuleTakesPrecedence}` : ` ${this.strings.allowTakesPrecedence}`
				)
			}

			if ('defaultRuleOverride' === error.message) {
				return this.$t.sprintf(
					// Translators: 1 - The table row index, 2 - A message telling this index comes is on the network level.
					this.$t.__('This rule overrides the default rule #%1$s%2$s.', this.$td),
					error.overriddenIndex,
					error.isNetworkIndex ? ` (${this.strings.fromTheNetwork})` : ''
				)
			}

			return this.strings[error.message]
		}
	},
	props : {
		errors : {
			type     : Array,
			required : true
		}
	},
	data () {
		return {
			strings : {
				allowTakesPrecedence       : this.$t.__('The "Allow" rule takes precedence.', this.$td),
				fromTheNetwork             : this.$t.__('from the network level', this.$td),
				invalidCleanParam          : this.$t.__('Clean-param must start with at least one param which is optionally followed by one path.', this.$td),
				invalidCrawlDelay          : this.$t.__('Crawl-delay must be a number greater than 0.', this.$td),
				networkRuleTakesPrecedence : this.$t.__('The network rule takes precedence.', this.$td)
			}
		}
	}
}
</script>