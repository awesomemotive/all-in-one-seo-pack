import { defineStore } from 'pinia'
import { useOptionsStore, useSensitiveOptionsStore } from '@/vue/stores'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useSeoAlertsStore = defineStore('seoAlerts', {
	state : () => {
		const optionsStore = useOptionsStore()
		const sensitiveOptionsStore = useSensitiveOptionsStore()
		const options = optionsStore.options.tools.seoAlerts

		return {
			enabled            : options.enable,
			alerts             : options.alerts,
			recipients         : options.recipients.length ? options.recipients : [],
			// The webhook URL is stored in sensitive options and never sent to the browser, so we
			// keep the input value local, track whether one is saved, and only include it in the
			// save payload once the user edits it (so an untouched save doesn't clear it).
			slackWebhookUrl    : '',
			hasSlackWebhookUrl : sensitiveOptionsStore.hasSeoAlertsSlackWebhookUrl,
			slackWebhookDirty  : false,
			slackMemberIds     : options.slackMemberIds?.length ? options.slackMemberIds : []
		}
	},
	actions : {
		toggleAlert (id, value) {
			if (id in this.alerts) {
				this.alerts[id] = value
				this.saveSettings()
			}
		},
		addRecipient (email = '') {
			this.recipients.push({
				email : email
			})
		},
		updateRecipient (index, email) {
			if (this.recipients[index]) {
				this.recipients[index].email = email
			}
		},
		deleteRecipient (index) {
			this.recipients.splice(index, 1)
			this.saveSettings()
		},
		addSlackMemberId (memberId = '') {
			this.slackMemberIds.push({
				memberId : memberId
			})
		},
		updateSlackMemberId (index, memberId) {
			if (this.slackMemberIds[index]) {
				this.slackMemberIds[index].memberId = memberId
				this.saveSettings()
			}
		},
		deleteSlackMemberId (index) {
			this.slackMemberIds.splice(index, 1)
			this.saveSettings()
		},
		updateSlackWebhookUrl (value) {
			this.slackWebhookUrl = value
			this.slackWebhookDirty = true
			this.saveSettings()
		},
		removeSlackWebhook () {
			if (!this.hasSlackWebhookUrl) {
				return
			}

			// Stage the removal; the cleared value persists to the DB on the next settings save.
			this.slackWebhookUrl = ''
			this.hasSlackWebhookUrl = false
			this.slackWebhookDirty = true
			this.saveSettings()
		},
		toggleEnabled (value) {
			this.enabled = value
			this.saveSettings()
		},
		saveSettings () {
			const optionsStore = useOptionsStore()

			// Update options directly in the options store
			const seoAlerts = {
				enable         : this.enabled,
				alerts         : this.alerts,
				recipients     : this.recipients,
				slackMemberIds : this.slackMemberIds
			}

			// The webhook is routed to sensitive options server-side. Only send it once the user
			// has edited it, so an untouched save doesn't overwrite the stored value.
			if (this.slackWebhookDirty) {
				seoAlerts.slackWebhookUrl = this.slackWebhookUrl
			}

			optionsStore.options.tools.seoAlerts = seoAlerts
		},
		sendTestEmail (email) {
			return http.post(links.restUrl('seo-alerts/test-email'))
				.send({ email })
				.then((response) => {
					if (!response.body.success) {
						console.error(response.body.message)
						return {
							success : false,
							message : response.body.message || 'Failed to send test email.'
						}
					}

					return {
						success : true,
						message : response.body.message || 'Test email sent successfully.'
					}
				})
				.catch((e) => {
					console.error(e)
					return {
						success : false,
						message : 'An error occurred while sending the test email.'
					}
				})
		},
		sendTestSlack (webhookUrl, memberIds) {
			return http.post(links.restUrl('seo-alerts/test-slack'))
				.send({ webhookUrl, memberIds })
				.then((response) => {
					if (!response.body.success) {
						console.error(response.body.message)
						return {
							success : false,
							message : response.body.message || 'Failed to send test message.'
						}
					}

					return {
						success : true,
						message : response.body.message || 'Test message sent successfully.'
					}
				})
				.catch((e) => {
					console.error(e)
					return {
						success : false,
						message : 'An error occurred while sending the test message.'
					}
				})
		}
	}
})