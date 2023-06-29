<template>
	<div class="aioseo-wizard-additional-information">
		<wizard-header />
		<wizard-container>

			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.additionalSiteInformation }}
				</div>

				<div class="person-or-organization aioseo-settings-row no-border no-margin">
					<div class="settings-name">
						<div class="name small-margin">{{ strings.personOrOrganization }}</div>
					</div>
					<base-radio-toggle
						v-model="setupWizardStore.additionalInformation.siteRepresents"
						name="siteRepresents"
						:options="[
							{ label: strings.person, value: 'person' },
							{ label: strings.organization, value: 'organization' }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.personOrOrganizationDescription }}
					</div>
				</div>

				<div
					v-if="'person' === setupWizardStore.additionalInformation.siteRepresents"
					class="aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.choosePerson }}</div>
					</div>
					<base-select
						class="person-chooser"
						:options="users"
						:modelValue="getPersonOptions(setupWizardStore.additionalInformation.person)"
						@update:modelValue="value => setupWizardStore.additionalInformation.person = value.value"
					>
						<template #singleLabel="{ option }">
							<div class="person-label">
								<div
									v-if="option.gravatar"
									class="person-avatar"
								>
									<img
										alt="User Gravatar"
										:src="option.gravatar"
									/>
								</div>
								<div class="person-name">
									{{ option.label }}
								</div>
							</div>
						</template>
						<template #option="{ option }">
							<div class="person-label">
								<div
									v-if="option.gravatar"
									class="person-avatar"
								>
									<img
										alt="User Gravatar"
										:src="option.gravatar"
									/>
								</div>
								<div class="person-name">
									{{ option.label }}
								</div>
							</div>
						</template>
					</base-select>
				</div>

				<div
					v-if="'organization' === setupWizardStore.additionalInformation.siteRepresents"
					class="schema-graph-name aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.organizationName }}</div>
					</div>
					<base-input
						size="medium"
						v-model="setupWizardStore.additionalInformation.organizationName"
					/>
				</div>

				<div
					v-if="'organization' !== setupWizardStore.additionalInformation.siteRepresents && 'manual' === setupWizardStore.additionalInformation.person"
					class="schema-graph-name aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.name }}</div>
					</div>
					<base-input
						size="medium"
						v-model="setupWizardStore.additionalInformation.personName"
					/>
				</div>

				<div
					v-if="'organization' === setupWizardStore.additionalInformation.siteRepresents"
					class="schema-graph-phone aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.phone }}</div>
					</div>
					<base-phone
						v-model="setupWizardStore.additionalInformation.phone"
					/>
				</div>

				<div
					v-if="'organization' === setupWizardStore.additionalInformation.siteRepresents"
					class="schema-graph-contact-type aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.contactType }}</div>
					</div>
					<base-select
						size="medium"
						:options="$constants.CONTACT_TYPES"
						:placeholder="strings.chooseContactType"
						:modelValue="getContactTypeOptions(setupWizardStore.additionalInformation.contactType)"
						@update:modelValue="value => setupWizardStore.additionalInformation.contactType = value.value"
					/>
					<div class="aioseo-description">
						{{ strings.contactTypeDescription }}
					</div>
				</div>

				<div
					v-if="'organization' === setupWizardStore.additionalInformation.siteRepresents && 'manual' === setupWizardStore.additionalInformation.contactType"
					class="schema-graph-contact-type-manual aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.contactType }}</div>
					</div>
					<base-input
						size="medium"
						v-model="setupWizardStore.additionalInformation.contactTypeManual"
					/>
				</div>

				<div
					v-if="'organization' === setupWizardStore.additionalInformation.siteRepresents"
					class="schema-graph-image aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.logo }}</div>
					</div>

					<core-image-uploader
						v-model="setupWizardStore.additionalInformation.organizationLogo"
					/>
				</div>

				<div
					v-if="'organization' !== setupWizardStore.additionalInformation.siteRepresents && 'manual' === setupWizardStore.additionalInformation.person"
					class="schema-graph-image aioseo-settings-row no-border no-margin"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.logo }}</div>
					</div>

					<core-image-uploader
						v-model="setupWizardStore.additionalInformation.personLogo"
					/>
				</div>

				<div
					class="schema-graph-image aioseo-settings-row"
				>
					<div class="settings-name">
						<div class="name small-margin">{{ strings.defaultSocialShareImage }}</div>
					</div>

					<core-image-uploader
						v-model="setupWizardStore.additionalInformation.socialShareImage"
					/>
				</div>

				<div class="header social">
					{{ strings.yourSocialProfiles }}
				</div>

				<div v-if="loaded" class="social-profiles">
					<core-social-profiles
						:options="setupWizardStore.additionalInformation"
						leftSize="4"
						rightSize="8"
						sameUsernameWidth="4"
						hideAdditionalProfiles
					/>
				</div>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndContinue"
					>{{ strings.saveAndContinue }} &rarr;</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useWizard } from '@/vue/composables'
import { MaxCounts, Wizard } from '@/vue/mixins'
import BasePhone from '@/vue/components/common/base/Phone'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSocialProfiles from '@/vue/components/common/core/SocialProfiles'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'
export default {
	setup () {
		const { strings } = useWizard()

		return {
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore(),
			composableStrings : strings
		}
	},
	components : {
		BasePhone,
		BaseRadioToggle,
		CoreImageUploader,
		CoreSocialProfiles,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	mixins : [ MaxCounts, Wizard ],
	data () {
		return {
			loaded  : false,
			loading : false,
			stage   : 'additional-information',
			strings : merge(this.composableStrings, {
				additionalSiteInformation       : this.$t.__('Additional Site Information', this.$td),
				personOrOrganization            : this.$t.__('Person or Organization', this.$td),
				choosePerson                    : this.$t.__('Choose a Person', this.$td),
				person                          : this.$t.__('Person', this.$td),
				organization                    : this.$t.__('Organization', this.$td),
				personOrOrganizationDescription : this.$t.__('Choose whether the site represents a person or an organization.', this.$td),
				name                            : this.$t.__('Name', this.$td),
				organizationName                : this.$t.__('Organization Name', this.$td),
				phone                           : this.$t.__('Phone Number', this.$td),
				chooseContactType               : this.$t.__('Choose a Contact Type', this.$td),
				contactType                     : this.$t.__('Contact Type', this.$td),
				contactTypeDescription          : this.$t.__('Select which team or department the phone number belongs to.', this.$td),
				logo                            : this.$t.__('Logo', this.$td),
				defaultSocialShareImage         : this.$t.__('Default Social Share Image', this.$td),
				yourSocialProfiles              : this.$t.__('Your Social Profiles', this.$td)
			})
		}
	},
	watch : {
		'optionsStore.options.social.profiles' : {
			deep : true,
			handler (newValue) {
				this.setupWizardStore.additionalInformation.social.profiles = newValue
			}
		}
	},
	computed : {
		users () {
			return [ {
				label : this.$t.__('Manually Enter Person', this.$td),
				value : 'manual'
			} ].concat(this.rootStore.aioseo.users.map(u => ({
				label    : `${u.displayName} (${u.email})`,
				gravatar : u.gravatar,
				value    : u.id
			})))
		}
	},
	methods : {
		getPersonOptions (option) {
			return this.users.find(u => u.value === option)
		},
		getContactTypeOptions (option) {
			return this.$constants.CONTACT_TYPES.find(t => t.value === option)
		},
		saveAndContinue () {
			this.loading = true
			this.setupWizardStore.saveWizard('additionalInformation')
				.then(() => {
					this.$router.push(this.setupWizardStore.getNextLink)
				})
		}
	},
	mounted () {
		this.$nextTick(() => {
			const searchAppearance = JSON.parse(JSON.stringify(this.optionsStore.options.searchAppearance))
			const social           = JSON.parse(JSON.stringify(this.optionsStore.options.social))

			this.setupWizardStore.additionalInformation.social.profiles   = JSON.parse(JSON.stringify(social.profiles))
			this.setupWizardStore.additionalInformation.socialShareImage  = social.facebook.general.defaultImagePosts
			this.setupWizardStore.additionalInformation.siteRepresents    = searchAppearance.global.schema.siteRepresents
			this.setupWizardStore.additionalInformation.person            = searchAppearance.global.schema.person
			this.setupWizardStore.additionalInformation.organizationName  = searchAppearance.global.schema.organizationName
			this.setupWizardStore.additionalInformation.organizationLogo  = searchAppearance.global.schema.organizationLogo
			this.setupWizardStore.additionalInformation.personName        = searchAppearance.global.schema.personName
			this.setupWizardStore.additionalInformation.personLogo        = searchAppearance.global.schema.personLogo
			this.setupWizardStore.additionalInformation.phone             = searchAppearance.global.schema.phone
			this.setupWizardStore.additionalInformation.contactType       = searchAppearance.global.schema.contactType
			this.setupWizardStore.additionalInformation.contactTypeManual = searchAppearance.global.schema.contactTypeManual
			this.loaded = true
		})
	}
}
</script>

<style lang="scss">
.aioseo-wizard-additional-information {

	.schema-graph-name,
	.schema-graph-contact-type-manual {

		.aioseo-input {
			max-width: 600px;
		}
	}

	.schema-graph-contact-type {

		.aioseo-select {
			max-width: 300px;
		}
	}

	.person-chooser {
		max-width: 600px;

		.person-label {
			display: flex;
			align-items: center;

			.person-avatar {
				margin-right: 16px;
				height: 30px;

				img {
					height: 30px;
					width: 30px;
					border-radius: 50%;
				}
			}
		}
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}
</style>