<template>
	<div class="aioseo-email-summary">
		<base-toggle v-model="optionsStore.options.advanced.emailSummary.enable"/>

		<div class="aioseo-description">
			{{ strings.enableEmailReports }}
		</div>

		<div
			class="aioseo-email-summary__table"
			v-if="optionsStore.options.advanced.emailSummary.enable"
		>
			<div
				class="aioseo-email-summary-table__header"
				v-if="parseRecipients.length > 0"
			>
				<grid-row>
					<grid-column
						xs="7"
						sm="6"
					>
						<div class="aioseo-email-summary__label">{{ strings.recipientLabel }}</div>
					</grid-column>

					<grid-column
						xs="5"
						sm="4"
					>
						<div class="aioseo-email-summary__label">{{ strings.frequencyLabel }}</div>
					</grid-column>
				</grid-row>
			</div>

			<div
				class="aioseo-email-summary__table-body"
				v-if="parseRecipients.length > 0"
			>
				<grid-row
					v-for="(recipient, index) in parseRecipients"
					:key="`recipient-${index}`"
					class="aioseo-email-summary__table-row"
				>
					<grid-column
						class="aioseo-email-summary__table-column"
						xs="6"
					>
						<base-input
							class="aioseo-email-summary__input aioseo-email-summary__input--recipient"
							type="email"
							size="medium"
							validation="email"
							@update:modelValue="value => { updateRecipient(recipient, index, 'email', value) }"
							:modelValue="recipient.email"
						>
							<template #append-icon>
								<core-tooltip
									class=""
									type="action"
								>
									<base-button
										class="btn-send-test-message"
										@click.exact="sendTestMessage(index)"
										type="gray"
										size="medium"
										:loading="btnSendTestMessageLoading[index]"
										:disabled="btnSendTestMessageDisabled || !isEmail(recipient.email)"
									>
										<svg-outgoing-mail/>
									</base-button>

									<template #tooltip>
										{{ strings.sendTestEmail }}
									</template>
								</core-tooltip>
							</template>
						</base-input>
					</grid-column>

					<grid-column
						class="aioseo-email-summary__table-column"
						xs="4"
					>
						<base-input
							v-if="licenseStore.isUnlicensed"
							class="aioseo-email-summary__input aioseo-email-summary__input--frequency"
							size="medium"
							:modelValue="frequencyOptions.find(f => f.value === defaultFrequencyValue).label"
							:disabled="true"
						/>

						<base-select
							v-else
							class="aioseo-email-summary__input aioseo-email-summary__input--frequency"
							size="medium"
							:options="frequencyOptions"
							:modelValue="frequencyOptions.find(f => f.value === recipient.frequency)"
							:filterable="null"
							@update:modelValue="updateRecipient(recipient, index, 'frequency', $event.value)"
						/>
					</grid-column>

					<grid-column
						class="aioseo-email-summary__table-column aioseo-email-summary__table-column--actions"
						xs="2"
						v-if="parseRecipients.length > 1"
					>
						<a
							@click.prevent.exact="deleteRecipient(index)"
							href="#"
							class="btn-delete-recipient"
						>
							<svg-trash width="20"/>
						</a>
					</grid-column>

					<grid-column
						v-if="alertSendTestMessage[index]?.show"
						class="aioseo-email-summary__table-column aioseo-email-summary__table-column--alert"
						xs="10"
					>
						<core-alert
							class="alert-send-test-message"
							:type="alertSendTestMessage[index]?.type"
							size="medium"
						>
							{{ alertSendTestMessage[index]?.message }}
						</core-alert>
					</grid-column>
				</grid-row>
			</div>

			<div class="aioseo-email-summary__table-footer">
				<div>
					<base-button
						class="btn-add-recipient"
						@click.exact="addRecipient()"
						type="blue"
						size="small"
						:disabled="licenseStore.isUnlicensed"
					>
						{{ strings.addEmailAddress }}
					</base-button>
				</div>

				<core-alert
					class="inline-upsell"
					v-if="licenseStore.isUnlicensed"
				>
					<div v-html="strings.upsell"/>
				</core-alert>
			</div>
		</div>
	</div>
</template>
<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'
import { isEmail } from '@/vue/utils/formatting'

import { __, sprintf } from '@/vue/plugins/translations'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgOutgoingMail from '@/vue/components/common/svg/OutgoingMail'
import SvgTrash from '@/vue/components/common/svg/Trash'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreTooltip,
		GridColumn,
		GridRow,
		SvgOutgoingMail,
		SvgTrash
	},
	data () {
		return {
			alertSendTestMessage : [
				{
					show    : false,
					type    : '',
					message : ''
				}
			],
			btnSendTestMessageLoading  : [],
			btnSendTestMessageDisabled : false,
			strings                    : {
				enableEmailReports : __('Enable email reports to receive a digest of the most important SEO updates for your site, right in your inbox.', td),
				recipientLabel     : __('Email Address', td),
				frequencyLabel     : __('Frequency', td),
				addEmailAddress    : __('Add Email Address', td),
				sendTestEmail      : __('Send Test Email', td),
				upsell             : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Sending summaries to additional email addresses is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-settings-advanced', GLOBAL_STRINGS.learnMore, 'email-summary', true)
				)
			},
			defaultFrequencyValue : 'monthly',
			frequencyOptions      : [
				{
					label : __('Monthly', td),
					value : 'monthly'
				},
				{
					label : __('Weekly', td),
					value : 'weekly'
				}
			]
		}
	},
	watch : {
		'optionsStore.options.advanced.emailSummary.enable' (value) {
			if (value) {
				this.sanitizeRecipients()

				const length = this.optionsStore.options.advanced.emailSummary.recipients.length
				if (!length) {
					this.addRecipient({ email: this.rootStore.aioseo.data.adminEmail })
				}

				if (1 === length && !this.optionsStore.options.advanced.emailSummary.recipients[0]?.email) {
					this.deleteRecipient(0)
					this.addRecipient({ email: this.rootStore.aioseo.data.adminEmail })
				}
			}
		}
	},
	computed : {
		parseRecipients () {
			const parsed     = []
			const recipients = this.optionsStore.options.advanced.emailSummary.recipients
			Object.values(recipients).forEach(recipient => {
				parsed.push(recipient)
			})

			if (0 === parsed.length) {
				this.addRecipient()
			}

			return parsed
		}
	},
	methods : {
		isEmail,
		stringTestEmailSent (email) {
			return sprintf(
				// Translators: 1 - An email address.
				__('An email was sent out to %1$s.', td),
				email
			)
		},
		stringTestEmailNotSent (email) {
			return sprintf(
				// Translators: 1 - An email address.
				__('Failed to send email to %1$s. Make sure the address is correct and that your site is configured correctly to send emails.', td),
				email
			)
		},
		addRecipient (data = {}) {
			this.optionsStore.options.advanced.emailSummary.recipients.push({
				email     : data?.email ?? '',
				frequency : this.defaultFrequencyValue
			})
		},
		deleteRecipient (index) {
			this.optionsStore.options.advanced.emailSummary.recipients.splice(index, 1)
		},
		sendTestMessage (index) {
			const email     = this.optionsStore.options.advanced.emailSummary.recipients[index].email
			const frequency = this.optionsStore.options.advanced.emailSummary.recipients[index].frequency

			this.btnSendTestMessageLoading[index] = true
			this.btnSendTestMessageDisabled       = true

			return http.post(links.restUrl('email-summary/send'))
				.send({
					to        : email,
					frequency : frequency
				})
				.then((response) => {
					if (!response.body.success) {
						console.error(response.body.message)

						this.setAlertSendTestMessage(index, 'red', this.stringTestEmailNotSent(email))

						return false
					}

					this.setAlertSendTestMessage(index, 'green', this.stringTestEmailSent(email))
				})
				.catch((e) => {
					console.error(e)

					this.setAlertSendTestMessage(index, 'red', this.stringTestEmailNotSent(email))
				})
				.finally(() => {
					this.btnSendTestMessageLoading  = []
					this.btnSendTestMessageDisabled = false
				})
		},
		setAlertSendTestMessage (index, type, message) {
			// Close any open alerts.
			this.alertSendTestMessage = []

			this.alertSendTestMessage[index] = {
				show    : true,
				type    : type,
				message : message
			}

			debounce(() => {
				this.alertSendTestMessage = []
			}, 7000)
		},
		async sanitizeRecipients () {
			await this.$nextTick()

			this.optionsStore.options.advanced.emailSummary.recipients = this.optionsStore.options.advanced.emailSummary.recipients.filter(r => r.email)

			this.optionsStore.options.advanced.emailSummary.recipients.forEach((recipient, index) => {
				// Remove duplicate emails.
				for (let i = index + 1; i < this.optionsStore.options.advanced.emailSummary.recipients.length; i++) {
					if (recipient.email === this.optionsStore.options.advanced.emailSummary.recipients[i]?.email) {
						this.deleteRecipient(i)
					}
				}
			})
		},
		async updateRecipient (recipient, index, key, value) {
			await this.$nextTick()

			recipient[key] = value.trim()

			this.optionsStore.options.advanced.emailSummary.recipients.splice(index, 1, recipient)
		}
	},
	mounted () {
		this.sanitizeRecipients()

		window.aioseoBus.$on('changes-saved', this.sanitizeRecipients)
	},
	beforeUnmount () {
		window.aioseoBus.$off('changes-saved', this.sanitizeRecipients)
	}
}
</script>

<style lang="scss" scoped>
.aioseo-email-summary {
	--column-height: 40px;

	&__label {
		margin-bottom: 4px;
		font-weight: 700;
	}

	&__table {
		margin-top: 20px;
	}

	&__table-body {
		display: grid;
		row-gap: 10px;
	}

	&__table-column {
		&--actions {
			align-items: center;
			display: flex;
			height: var(--column-height);
			line-height: 1;
		}
	}

	&__input {
		&--recipient {
			.aioseo-tooltip {
				display: block;
			}

			:deep(.aioseo-input) {
				position: relative;
			}

			:deep(input) {
				padding-right: 60px;
			}
		}
	}

	.btn-add-recipient {
		margin-top: 12px;
	}

	.btn-delete-recipient {
		height: 20px;
		width: 20px;

		svg {
			color: $placeholder-color;

			&:hover {
				color: $red;
			}
		}
	}

	.btn-send-test-message {
		position: absolute;
		top: 50%;
		right: 2px;
		height: 90%;
		width: 50px;
		transform: translate(0, -50%);

		svg {
			height: 18px;
			margin-top: 1px;
			min-width: 18px;
			width: 18px;
		}
	}
}
</style>