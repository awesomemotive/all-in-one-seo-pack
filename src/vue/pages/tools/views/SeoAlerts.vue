<template>
	<div class="aioseo-alerts">
		<core-card
			slug="seoAlerts"
			:header-text="strings.seoAlerts"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.alertsDescription }}
			</div>

			<core-settings-row
				:name="strings.enableSeoAlerts"
			>
				<template #content>
					<base-toggle
						:modelValue="seoAlertsStore.enabled"
						@update:modelValue="seoAlertsStore.toggleEnabled($event)"
					/>
				</template>
			</core-settings-row>

			<template v-if="seoAlertsStore.enabled">
				<core-settings-row
					v-for="(alertType, index) in alertTypes"
					:key="index"
					:name="alertType.title"
				>
					<template #content>
						<base-checkbox
							:modelValue="seoAlertsStore.alerts[alertType.id]"
							@update:modelValue="seoAlertsStore.toggleAlert(alertType.id, $event)"
						>
							{{ alertType.description }}
						</base-checkbox>
					</template>
				</core-settings-row>
			</template>
		</core-card>

		<core-card
			v-if="seoAlertsStore.enabled"
			slug="seoAlertsDelivery"
			:header-text="strings.seoAlertsDelivery"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.seoAlertsDeliveryDescription }}
			</div>

			<core-settings-row
				:name="strings.slackWebhookUrl"
			>
				<template #content>
					<div class="aioseo-slack-webhook-row-inner">
						<base-input
							class="aioseo-slack-webhook-input"
							size="medium"
							:modelValue="webhookFieldValue"
							@update:modelValue="seoAlertsStore.updateSlackWebhookUrl($event)"
							:placeholder="strings.slackWebhookPlaceholder"
							:disabled="seoAlertsStore.hasSlackWebhookUrl"
							:readonly="seoAlertsStore.hasSlackWebhookUrl"
						>
							<template #append-icon>
								<core-tooltip
									type="action"
								>
									<base-button
										class="btn-send-test-message"
										@click.exact="sendTestSlackMessage"
										type="gray"
										size="medium"
										:loading="slackTestLoading"
										:disabled="slackTestLoading || slackWebhookInvalid || (!seoAlertsStore.slackWebhookUrl && !seoAlertsStore.hasSlackWebhookUrl)"
									>
										<svg-outgoing-mail/>
									</base-button>

									<template #tooltip>
										{{ strings.sendTestSlackMessage }}
									</template>
								</core-tooltip>
							</template>
						</base-input>

						<core-tooltip
							type="action"
						>
							<a
								@click.prevent.exact="removeSlackWebhook"
								href="#"
								class="btn-delete-webhook"
								:class="{ 'aioseo-disabled': !seoAlertsStore.hasSlackWebhookUrl }"
							>
								<svg-trash width="20"/>
							</a>

							<template #tooltip>
								{{ strings.removeSlackWebhook }}
							</template>
						</core-tooltip>
					</div>

					<core-alert
						v-if="slackWebhookInvalid"
						class="aioseo-seo-alerts-webhook-error"
						type="red"
					>
						{{ strings.slackWebhookInvalid }}
					</core-alert>

					<core-alert
						v-if="slackTestMessage?.show"
						class="aioseo-seo-alerts-webhook-test-message"
						:type="slackTestMessage?.type"
					>
						{{ slackTestMessage?.message }}
					</core-alert>

					<div class="aioseo-description">
						{{ strings.slackDescription }}
						<a :href="strings.slackWebhookUrlLink" target="_blank" rel="noopener noreferrer">
							{{ strings.slackWebhookUrlLinkText }}
							<svg-external
								width="14"
								height="14"
							/>
						</a>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="seoAlertsStore.slackWebhookUrl || seoAlertsStore.hasSlackWebhookUrl"
				:name="strings.slackMemberIds"
			>
				<template #content>
					<div v-if="seoAlertsStore.slackMemberIds.length > 0">
						<div
							v-for="(member, index) in seoAlertsStore.slackMemberIds"
							:key="`member-${index}`"
							class="aioseo-slack-username-row"
						>
							<div class="aioseo-slack-username-row-inner">
								<base-input
									class="aioseo-username-input"
									size="medium"
									@update:modelValue="value => updateSlackMemberId(index, value)"
									:modelValue="member.memberId"
									:placeholder="strings.slackUsernamePlaceholder"
								/>

								<a
									@click.prevent.exact="seoAlertsStore.deleteSlackMemberId(index)"
									href="#"
									class="btn-delete-username"
									v-if="seoAlertsStore.slackMemberIds.length > 1"
								>
									<svg-trash width="20"/>
								</a>
							</div>
						</div>
					</div>

					<base-button
						class="btn-add-username"
						@click.exact="seoAlertsStore.addSlackMemberId()"
						type="blue"
						size="small"
					>
						{{ strings.addSlackMemberId }}
					</base-button>

					<div class="aioseo-description">
						{{ strings.slackMemberIdsDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.recipients"
			>
				<template #content>
					<div v-if="seoAlertsStore.recipients.length > 0">
						<div
							v-for="(recipient, index) in seoAlertsStore.recipients"
							:key="`recipient-${index}`"
							class="aioseo-email-input-row"
						>
							<div class="aioseo-email-input-row-inner">
								<base-input
									class="aioseo-email-input"
									type="email"
									size="medium"
									:validation="recipient.email ? 'email' : null"
									@update:modelValue="value => updateRecipient(index, value)"
									:modelValue="recipient.email"
									:placeholder="strings.emailRecipientsPlaceholder"
								>
									<template #append-icon>
										<core-tooltip
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

								<a
									@click.prevent.exact="seoAlertsStore.deleteRecipient(index)"
									href="#"
									class="btn-delete-recipient"
									v-if="seoAlertsStore.recipients.length > 1"
								>
									<svg-trash width="20"/>
								</a>
							</div>

							<div>
								<core-alert
									v-if="alertSendTestMessage[index]?.show"
									class="alert-send-test-message"
									:type="alertSendTestMessage[index]?.type"
									size="medium"
								>
									{{ alertSendTestMessage[index]?.message }}
								</core-alert>
							</div>
						</div>
					</div>

					<base-button
						class="btn-add-recipient"
						@click.exact="seoAlertsStore.addRecipient()"
						type="blue"
						size="small"
					>
						{{ strings.addEmailAddress }}
					</base-button>

					<div class="aioseo-description">
						{{ strings.emailRecipientsDescription }}
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseInput from '@/vue/components/common/base/Input'
import BaseToggle from '@/vue/components/common/base/Toggle'
import BaseButton from '@/vue/components/common/base/Button'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgOutgoingMail from '@/vue/components/common/svg/OutgoingMail'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { useSeoAlertsStore } from '@/vue/stores/SeoAlertsStore'
import { __ } from '@/vue/plugins/translations'
import { debounce } from '@/vue/utils/debounce'
import { isEmail } from '@/vue/utils/formatting'
import links from '@/vue/utils/links'

const td = import.meta.env.VITE_TEXTDOMAIN
const seoAlertsStore = useSeoAlertsStore()

// Slack webhooks must be https://hooks.slack.com/... — flag anything else to prevent SSRF.
const slackWebhookInvalid = computed(() => {
	const url = (seoAlertsStore.slackWebhookUrl || '').trim()
	if (!url) {
		return false
	}

	try {
		const parsed = new URL(url)

		return 'https:' !== parsed.protocol || 'hooks.slack.com' !== parsed.hostname
	} catch (e) {
		return true
	}
})

// The webhook is write-only; when one is saved, show a masked value in the (disabled) field.
const maskedWebhook = 'https://hooks.slack.com/services/*********/*********/************************'
const webhookFieldValue = computed(() => {
	return seoAlertsStore.hasSlackWebhookUrl && !seoAlertsStore.slackWebhookUrl
		? maskedWebhook
		: seoAlertsStore.slackWebhookUrl
})

const removeSlackWebhook = () => {
	seoAlertsStore.removeSlackWebhook()
}

// Alert types with localized strings
const alertTypes = [
	{
		id          : 'noindexHomepage',
		title       : __('Homepage is noindexed', td),
		description : __('Receive an alert if your homepage is set to noindex.', td)
	},
	{
		id          : 'robotsTxtError',
		title       : __('Robots.txt fails to load', td),
		description : __('Receive an alert if your robots.txt returns an error code.', td)
	},
	{
		id          : 'xmlSitemapError',
		title       : __('XML Sitemaps fail to load', td),
		description : __('Receive an alert if any of your XML sitemaps return an error code.', td)
	}
]

// Alert message state
const alertSendTestMessage = ref([])
const btnSendTestMessageLoading = ref([])
const btnSendTestMessageDisabled = ref(false)

// Slack test message state
const slackTestMessage = ref(null)
const slackTestLoading = ref(false)

// Email functionality
const updateRecipient = (index, value) => {
	seoAlertsStore.updateRecipient(index, value.trim())
}

// Slack functionality
const updateSlackMemberId = (index, value) => {
	// Trim the value first
	let formattedValue = value.trim()

	// If value is not empty and doesn't start with @, add it
	if (formattedValue && !formattedValue.startsWith('@')) {
		formattedValue = `@${formattedValue}`
	}

	// Check for duplicates (excluding the current index)
	const isDuplicate = seoAlertsStore.slackMemberIds.some((item, idx) =>
		idx !== index && item.memberId === formattedValue
	)

	if (isDuplicate) {
		// Remove the duplicate entry
		seoAlertsStore.deleteSlackMemberId(index)
		return
	}

	seoAlertsStore.updateSlackMemberId(index, formattedValue)
}

const sendTestMessage = (index) => {
	const email = seoAlertsStore.recipients[index].email

	btnSendTestMessageLoading.value[index] = true
	btnSendTestMessageDisabled.value = true

	seoAlertsStore.sendTestEmail(email)
		.then((response) => {
			// Set success or error message based on response
			const type = response.success ? 'green' : 'red'
			const message = response.message
			setAlertSendTestMessage(index, type, message)
		})
		.finally(() => {
			btnSendTestMessageLoading.value = []
			btnSendTestMessageDisabled.value = false
		})
}

const sendTestSlackMessage = () => {
	slackTestLoading.value = true

	seoAlertsStore.sendTestSlack(seoAlertsStore.slackWebhookUrl, seoAlertsStore.slackMemberIds)
		.then((response) => {
			slackTestMessage.value = {
				show    : true,
				type    : response.success ? 'green' : 'red',
				message : response.message
			}

			debounce(() => {
				slackTestMessage.value = null
			}, 7000)
		})
		.finally(() => {
			slackTestLoading.value = false
		})
}

const setAlertSendTestMessage = (index, type, message) => {
	// Close any open alerts
	alertSendTestMessage.value = []

	alertSendTestMessage.value[index] = {
		show    : true,
		type    : type,
		message : message
	}

	debounce(() => {
		alertSendTestMessage.value = []
	}, 7000)
}

// Initialize on component mount
onMounted(() => {
	// Add an empty recipient if none exist
	if (0 === seoAlertsStore.recipients.length) {
		seoAlertsStore.addRecipient()
	}

	// Add an empty Slack username if none exist
	if (0 === seoAlertsStore.slackMemberIds.length) {
		seoAlertsStore.addSlackMemberId()
	}
})

// Strings
const strings = {
	seoAlerts                    : __('SEO Alerts', td),
	enableSeoAlerts              : __('Enable SEO Alerts', td),
	alertsDescription            : __('Receive alerts about important SEO issues on your site before it\'s too late. We check major problems every hour.', td),
	seoAlertsDelivery            : __('SEO Alerts Delivery', td),
	seoAlertsDeliveryDescription : __('Configure how you would like to receive SEO alerts.', td),
	recipients                   : __('Email Addresses', td),
	emailRecipientsPlaceholder   : __('Enter email address', td),
	addEmailAddress              : __('Add Email Address', td),
	sendTestEmail                : __('Send Test Email', td),
	sendTestSlackMessage         : __('Send Test Message', td),
	removeSlackWebhook           : __('Remove webhook URL', td),
	slackWebhookUrl              : __('Slack Webhook URL', td),
	slackWebhookPlaceholder      : __('Enter Slack webhook URL', td),
	slackDescription             : __('Receive notifications to your Slack workspace.', td),
	slackWebhookUrlLink          : links.getDocUrl('seoAlertsSlackWebhook'),
	slackWebhookUrlLinkText      : __('How to find my Slack Webhook URL?', td),
	slackWebhookInvalid          : __('Enter a valid Slack webhook URL. It must start with https://hooks.slack.com/.', td),
	emailRecipientsDescription   : __('Receive SEO alerts by email. Add multiple addresses if needed.', td),
	slackMemberIds               : __('Slack Member IDs', td),
	slackUsernamePlaceholder     : __('Enter Slack member ID', td),
	addSlackMemberId             : __('Add Member ID', td),
	slackMemberIdsDescription    : __('Add Slack member IDs to mention users in notifications. You can find a user\'s member ID in their Slack profile.', td)
}
</script>

<style lang="scss">
.aioseo-alerts {
	.aioseo-seo-alerts-webhook-error,
	.aioseo-seo-alerts-webhook-test-message {
		margin-top: 10px;
	}

	.aioseo-description {
		margin-top: 5px;
		font-size: 12px;

		a {
			color: $blue;
			text-decoration: none;

			&:hover {
				text-decoration: underline;
			}

			svg {
				margin-left: 2px;
				fill: currentColor;
			}
		}
	}

	// Common styles for both email and Slack username rows
	.aioseo-email-input-row,
	.aioseo-slack-username-row {
		max-width: 450px;
		margin-bottom: 10px;
		position: relative;

		&-inner {
			display: flex;
			align-items: center;
			gap: 6px;

			.aioseo-tooltip {
				margin-left: 0;
			}
		}
	}

	// Common styles for both email and username inputs
	.aioseo-email-input,
	.aioseo-username-input {
		flex: 1;
	}

	// The webhook input + its actions (test, remove) sit in a row.
	.aioseo-slack-webhook-row-inner {
		display: flex;
		align-items: center;
		gap: 6px;
		max-width: 600px;

		.aioseo-tooltip {
			margin-left: 0;
		}
	}

	.aioseo-slack-webhook-input {
		flex: 1;
	}

	// The email and webhook inputs host the send-test button inside the field on the right.
	.aioseo-email-input,
	.aioseo-slack-webhook-input {
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

	.btn-delete-webhook {
		height: 20px;
		width: 20px;

		svg {
			color: $placeholder-color;

			&:hover {
				color: $red;
			}
		}

		&.aioseo-disabled {
			cursor: default;
			opacity: 0.4;
			pointer-events: none;
		}
	}

	// Common styles for delete buttons
	.btn-delete-recipient,
	.btn-delete-username {
		height: 20px;
		width: 20px;

		svg {
			color: $placeholder-color;

			&:hover {
				color: $red;
			}
		}
	}

	// The test button sits inside the field on the right.
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

	.alert-send-test-message {
		margin-top: 5px;
		width: 100%;
	}

	// Specific styles for add buttons
	.btn-add-recipient,
	.btn-add-username {
		margin-top: 5px;
	}
}
</style>