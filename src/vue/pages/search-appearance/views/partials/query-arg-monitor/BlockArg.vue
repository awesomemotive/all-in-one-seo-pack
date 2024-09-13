<template>
	<div
		class="card-block-query"
	>
		<core-card
			no-slide
			slug="blockQueryArg"
		>
			<template #header>
				<span>{{ strings.blockQuery }}</span>
			</template>

			<div class="query-arg-logs">
				<div class="query-arg-options">
					<div class="query-arg-key">
						<base-input
							v-if="!regex"
							v-model="key"
							size="medium"
							:placeholder="strings.key"
						/>
						<base-input
							v-if="regex"
							v-model="regexValue"
							size="medium"
							placeholder="^utm_.*$"
						/>
					</div>
					<div
						class="query-arg-value"
						v-if="!regex"
					>
						<base-select
							multiple
							taggable
							:options="[]"
							size="medium"
							:modelValue="valuesList"
							@update:modelValue="values => valuesList = values"
							:placeholder="strings.value"
							:tag-placeholder="strings.createValue"
						/>
					</div>
					<div>
						<base-toggle
							v-model="regex"
						>
							{{ strings.regex }}
						</base-toggle>
					</div>
				</div>
				<div class="query-arg-button">
					<base-button
						size="small"
						type="black"
						:disabled="!allowedAddQuery"
						@click="addQuery()"
					>
						<svg-circle-plus />
						{{ strings.add }}
					</base-button>
				</div>
			</div>
		</core-card>
		<core-alert
			v-if="!isRegexValid"
			class="regex-error"
			type="red"
			size="medium"
		>
			{{ strings.invalidRegex }}
		</core-alert>
		<div
			v-if="regex && !!warnings.length"
		>
			<core-alert
				v-for="(warning, index) in warnings"
				:key="index"
				class="regex-warning"
				type="yellow"
				size="medium"
				v-html="warning"
			/>
		</div>
	</div>
</template>

<script>
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { sanitizeString } from '@/vue/utils/strings'
import { isRegexValid } from '@/vue/utils/regex'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'blockArgAdded' ],
	components : {
		CoreAlert,
		CoreCard,
		SvgCirclePlus
	},
	data () {
		return {
			strings : {
				blockQuery   : __('Block Query Arg', td),
				delete       : __('Delete', td),
				add          : __('Block Query Arg', td),
				regex        : __('Regex', td),
				selectAValue : __('Select a Value or Add a New One', td),
				key          : __('Key', td),
				value        : __('Enter one or multiple values', td),
				createValue  : __('Press enter to create a value', td),
				invalidRegex : __('The regex syntax is invalid.', td)
			},
			key          : null,
			regex        : false,
			regexValue   : null,
			isRegexValid : true,
			valuesList   : [],
			warnings     : []
		}
	},
	watch : {
		regexValue () {
			this.warnings = this.testRegex()
		}
	},
	computed : {
		allowedAddQuery () {
			if (this.regex) {
				return this.regexValue && this.isRegexValid
			}
			return this.key
		}
	},
	methods : {
		getSlug (value) {
			if (this.regex) {
				return this.regexValue
			}

			let slug = '?' + this.key
			if (value) {
				slug += '=' + value
			}
			return slug
		},
		addQuery () {
			const data = []
			if (this.regex) {
				data.push({
					slug  : this.getSlug(),
					key   : null,
					value : null,
					regex : this.regexValue
				})
			} else {
				if (0 !== this.valuesList.length) {
					this.valuesList.forEach(value => {
						data.push({
							slug  : this.getSlug(value.value),
							key   : this.key,
							value : value.value,
							regex : null
						})
					})
				} else {
					data.push({
						slug  : this.getSlug(),
						key   : this.key,
						value : null,
						regex : null
					})
				}
			}

			http.post(links.restUrl('crawl-cleanup/block/'))
				.send(data)
				.then(response => {
					const body = response.body
					if (!this.regex) {
						this.key        = null
						this.valuesList = []
						if (false === body.success && body.exists) {
							body.exists.forEach(e => {
								this.key = e.key
								this.valuesList.push({
									label : e.value,
									value : e.value
								})
							})
						}
					}

					if (this.regex) {
						if (true === body.success) {
							this.regexValue = null
						}
					}
					window.aioseoBus.$emit('blockArgAdded', response)
				})
		},
		testRegex () {
			const warnings = []
			this.isRegexValid = isRegexValid(this.regexValue)
			this.regexValue = '' === this.regexValue ? null : this.regexValue

			if (this.regex && null !== this.regexValue) {
				if (-1 === this.regexValue.indexOf('^') && -1 === this.regexValue.indexOf('$')) {
					warnings.push(sprintf(
						// Translators: 1 - To prevent a greedy regular expression you can use <code>^/</code>, 2 - to anchor it to the start of the Query Arg. For example: <code>^</code>.
						__('To prevent a greedy regular expression you can use %1$s to anchor it to the start of the Query Arg. For example: %2$s', td),
						'<code>^/</code>', '<code>^' + sanitizeString(this.regexValue.replace(/^\//, '')) + '</code>'
					))
				}

				if (0 < this.regexValue.indexOf('^')) {
					warnings.push(sprintf(
						// Translators: 1 - Adds a html tag with an option like: <code>^</code>, 2 - Adds a html tag with an option like: <code>^</code>.
						__('The caret %1$s should be at the start. For example: %2$s', td),
						'<code>^</code>',
						'<code>^' + sanitizeString(this.regexValue.replace('^', '').replace(/^\//, '')) + '</code>'
					))
				}

				if (this.regexValue.length - 1 !== this.regexValue.indexOf('$') && -1 !== this.regexValue.indexOf('$')) {
					warnings.push(sprintf(
						// Translators: 1 - The dollar symbol, 2 - Dollar symbol example.
						__('The dollar symbol %1$s should be at the end. For example: %2$s', td),
						'<code>$</code>',
						'<code>' + sanitizeString(this.regexValue.replace(/\$/g, '')) + '$</code>'
					))
				}
			}

			return warnings
		}
	}
}
</script>

<style lang="scss">
.card-block-query {
	.aioseo-card {
		border-radius: 4px;
		margin: 0;
	}

	.header {
		padding: 16px;
		border-bottom: none;

		.header-title {
			span {
				font-size: 14px;
				font-weight: 400;
			}
		}
	}

	.query-arg-logs {
		width: 100%;

		.query-arg-options {
			padding: 16px;
			display: flex;
			align-items: center;
			gap: 16px;
			background: #F3F4F5;
			border-style: solid;
			border-width: 1px 0;
			border-color: #D0D1D7;

			.query-arg-key {
				flex: 1;
			}

			.query-arg-value {
				flex: 2;
			}
		}

		.query-arg-button {
			padding: 8px 16px;

			svg.aioseo-circle-plus {
				width: 16px;
				height: 16px;
				margin-right: 8px;
			}
		}
	}
}
</style>