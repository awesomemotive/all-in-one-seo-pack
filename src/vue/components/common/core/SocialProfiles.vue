<template>
	<div class="aioseo-social-profiles">
		<div class="same-username">
			<div class="use-same">
				<base-checkbox
					size="medium"
					v-model="profileData.sameUsername.enable"
				>
					<span>{{ strings.useSameUsername }}</span>
				</base-checkbox>

				<core-settings-row
					:name="strings.yourUsername"
					v-if="profileData.sameUsername.enable"
					align
				>
					<template #content>
						<base-input
							size="medium"
							v-model="profileData.sameUsername.username"
						/>

						<grid-row
							class="profiles"
						>
							<grid-column
								v-for="(profile, index) in profiles"
								:key="index"
								:md="sameUsernameWidth"
								sm="6"
								:class="profile.key"
							>
								<base-checkbox
									size="medium"
									:modelValue="getValue(profile)"
									@update:modelValue="checked => updateValue(checked, profile)"
								>
									{{ profile.name }}
								</base-checkbox>
							</grid-column>
						</grid-row>
					</template>
				</core-settings-row>
			</div>
		</div>

		<div class="aioseo-social-profile-list">
			<core-settings-row
				align
				class="social-profile"
				v-for="(profile, index) in profiles"
				:key="profile.key + errorsKey"
				:class="profile.key"
				:leftSize="leftSize"
				:rightSize="rightSize"
			>
				<template #name>
					<component
						class="logo-svg"
						:is="profile.svg"
					/> {{ profile.label }}
				</template>

				<template #content>
					<base-input
						v-if="!profileData.sameUsername.enable || !profileData.sameUsername.included.includes(profile.key)"
						size="medium"
						:modelValue="profileData.urls[profile.key]"
						@blur="validateUrl($event, profile)"
						:tabindex="index"
					/>

					<core-alert
						class="profile-error"
						v-if="errors[profile.key] && (!profileData.sameUsername.enable || !profileData.sameUsername.included.includes(profile.key))"
						type="red"
					>
						<strong>{{ errors[profile.key] }}</strong>
					</core-alert>

					<base-input
						v-if="profileData.sameUsername.enable && profileData.sameUsername.included.includes(profile.key)"
						size="medium"
						disabled
						:modelValue="getUrl(profile)"
					/>
				</template>
			</core-settings-row>
		</div>

		<core-settings-row
			class="additional-social-profiles"
			:name="strings.additionalProfiles"
			v-if="!hideAdditionalProfiles"
		>
			<template #content>
				<base-textarea
					:minHeight="100"
					v-model="profileData.additionalUrls"
				/>

				<div class="aioseo-description">
					{{ strings.additionalProfilesDescription }}
				</div>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgIconFacebook from '@/vue/components/common/svg/icon/Facebook'
import SvgIconInstagram from '@/vue/components/common/svg/icon/Instagram'
import SvgIconTiktok from '@/vue/components/common/svg/icon/Tiktok'
import SvgIconLinkedin from '@/vue/components/common/svg/icon/Linkedin'
import SvgIconMyspace from '@/vue/components/common/svg/icon/Myspace'
import SvgIconPinterest from '@/vue/components/common/svg/icon/Pinterest'
import SvgIconSoundCloud from '@/vue/components/common/svg/icon/SoundCloud'
import SvgIconTumblr from '@/vue/components/common/svg/icon/Tumblr'
import SvgIconTwitter from '@/vue/components/common/svg/icon/Twitter'
import SvgIconWikipedia from '@/vue/components/common/svg/icon/Wikipedia'
import SvgIconWordPress from '@/vue/components/common/svg/icon/WordPress'
import SvgIconYelp from '@/vue/components/common/svg/icon/Yelp'
import SvgIconYoutube from '@/vue/components/common/svg/icon/Youtube'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'updated' ],
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		BaseCheckbox,
		BaseTextarea,
		CoreAlert,
		CoreSettingsRow,
		GridColumn,
		GridRow,
		SvgIconFacebook,
		SvgIconInstagram,
		SvgIconTiktok,
		SvgIconLinkedin,
		SvgIconMyspace,
		SvgIconPinterest,
		SvgIconSoundCloud,
		SvgIconTumblr,
		SvgIconTwitter,
		SvgIconWikipedia,
		SvgIconWordPress,
		SvgIconYelp,
		SvgIconYoutube
	},
	props : {
		leftSize : {
			type : String,
			default () {
				return '3'
			}
		},
		rightSize : {
			type : String,
			default () {
				return '9'
			}
		},
		sameUsernameWidth : {
			type : String,
			default () {
				return '3'
			}
		},
		userProfiles : {
			type     : Object,
			required : false
		},
		hideAdditionalProfiles : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			pagePostOptions : [],
			errors          : {},
			errorsKey       : 0,
			strings         : {
				useSameUsername               : __('Use the same username for multiple social networks', td),
				yourUsername                  : __('Your Username:', td),
				additionalProfiles            : __('Additional Profiles', td),
				additionalProfilesDescription : __('You can add additional social profile URLs here, separated by a new line.', td)
			}
		}
	},
	watch : {
		userProfiles : {
			deep : true,
			handler () {
				this.$emit('updated', this.userProfiles)
			}
		}
	},
	computed : {
		profileData () {
			return this.userProfiles || this.optionsStore.options.social.profiles
		},
		profiles () {
			// We use a set of alternative keys for the User Profile Tab as we otherwise would need to migrate old, existing links for Facebook and X (Twitter).
			return [
				{
					key        : 'facebookPageUrl',
					name       : 'Facebook',
					label      : this.userProfiles ? 'Facebook URL' : 'Facebook Page URL',
					url        : 'https://facebook.com',
					svg        : 'svg-icon-facebook',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?facebook\.[a-z.]+\/.*$/.test(v) || __('Your Facebook URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'twitterUrl',
					name       : 'X (Twitter)',
					label      : 'X (Twitter) URL',
					url        : 'https://x.com',
					svg        : 'svg-icon-twitter',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?(twitter|x)\.[a-z.]+\/.*$/.test(v) || __('Your X (Twitter) URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'instagramUrl',
					name       : 'Instagram',
					label      : 'Instagram URL',
					url        : 'https://instagram.com',
					svg        : 'svg-icon-instagram',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?instagram\.[a-z.]+\/.*$/.test(v) || __('Your Instagram URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'tiktokUrl',
					name       : 'TikTok',
					label      : 'TikTok URL',
					url        : 'https://tiktok.com/@',
					svg        : 'svg-icon-tiktok',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?tiktok\.[a-z.]+\/@.*$/.test(v) || __('Your TikTok URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'linkedinUrl',
					name       : 'LinkedIn',
					label      : 'LinkedIn URL',
					url        : 'https://linkedin.com/in',
					svg        : 'svg-icon-linkedin',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?linkedin\.[a-z.]+\/(?:in|company|school|groups|showcase)\/.*$/.test(v) || __('Your LinkedIn URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'pinterestUrl',
					name       : 'Pinterest',
					label      : 'Pinterest URL',
					url        : 'https://pinterest.com',
					svg        : 'svg-icon-pinterest',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?pinterest\.[a-z.]+\/.*$/.test(v) || __('Your Pinterest URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'youtubeUrl',
					name       : 'YouTube',
					label      : 'YouTube URL',
					url        : 'https://youtube.com',
					svg        : 'svg-icon-youtube',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?youtube\.[a-z.]+\/.*$/.test(v) || __('Your YouTube URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'tumblrUrl',
					name       : 'Tumblr',
					label      : 'Tumblr URL',
					url        : 'https://{profile}.tumblr.com',
					svg        : 'svg-icon-tumblr',
					validation : [
						v => /^https:\/\/([^/]+)\.tumblr\.[a-z.]+.*$/.test(v) || __('Your Tumblr URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'yelpPageUrl',
					name       : 'Yelp',
					label      : 'Yelp Page URL',
					url        : 'https://yelp.com/biz',
					svg        : 'svg-icon-yelp',
					validation : [
						v => /^https:\/\/(?:www\.)?yelp\.[a-z.]+\/biz\/.*$/.test(v) || __('Your Yelp URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'soundCloudUrl',
					name       : 'SoundCloud',
					label      : 'SoundCloud URL',
					url        : 'https://soundcloud.com',
					svg        : 'svg-icon-sound-cloud',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?soundcloud\.[a-z.]+\/.*$/.test(v) || __('Your SoundCloud URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'wikipediaUrl',
					name       : 'Wikipedia',
					label      : 'Wikipedia URL',
					url        : 'https://en.wikipedia.org/wiki',
					svg        : 'svg-icon-wikipedia',
					validation : [
						v => /^https:\/\/([a-z-]+)\.wikipedia\.org\/wiki\/.*$/.test(v) || __('Your Wikipedia URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'myspaceUrl',
					name       : 'MySpace',
					label      : 'MySpace URL',
					url        : 'https://myspace.com',
					svg        : 'svg-icon-myspace',
					validation : [
						v => /^https:\/\/(?:www\.)?(?:[a-zA-Z0-9]+.)?myspace\.[a-z.]+\/.*$/.test(v) || __('Your MySpace URL is invalid. Please check the format and try again.', td)
					]
				},
				{
					key        : 'wordPressUrl',
					name       : 'WordPress',
					label      : 'WordPress URL',
					url        : 'https://profiles.wordpress.org',
					svg        : 'svg-icon-word-press',
					validation : [
						v => /^https:\/\/profiles\.wordpress\.org\/[a-zA-Z0-9-]+\/?$/.test(v) || __('Your WordPress URL is invalid. Please check the format and try again.', td)
					]
				}
			]
		}
	},
	methods : {
		updateValue (checked, profile) {
			if (checked) {
				this.profileData.sameUsername.included.push(profile.key)

				return
			}

			const index = this.profileData.sameUsername.included.findIndex(t => t === profile.key)
			if (-1 !== index) {
				this.profileData.sameUsername.included.splice(index, 1)
			}
		},
		getValue (profile) {
			return this.profileData.sameUsername.included.includes(profile.key)
		},
		getUrl (profile) {
			const username = this.profileData.sameUsername.username || ''
			if ('tumblrUrl' === profile.key) {
				return profile.url.replace('{profile}', username)
			}
			if ('tiktokUrl' === profile.key) {
				return profile.url + username
			}
			return profile.url + '/' + username
		},
		validateUrl (value, profile) {
			if (value.startsWith('http://')) {
				value = value.replace('http://', 'https://')
			}

			if (value && !value.startsWith('https://')) {
				value = `https://${value}`
			}

			this.profileData.urls[profile.key] = value

			if (!profile.validation || !value) {
				this.errors[profile.key] = ''
				this.errorsKey++

				return
			}

			const errors = []
			profile.validation.forEach(validator => {
				const result = validator(value)
				if (true !== result) {
					errors.push(result)
				}
			})

			if (errors.length) {
				this.errors[profile.key] = errors.join(', ')
			} else {
				this.errors[profile.key] = ''
			}

			this.errorsKey++
		},
		getKey (profileObject) {
			return this.userProfiles ? profileObject.name.toLowerCase() : profileObject.value
		}
	},
	mounted () {
		// Validate the profiles on load.
		this.profiles.forEach(profile => {
			const errors = []
			profile.validation.forEach(validator => {
				if (!this.profileData.urls[profile.key]) {
					return
				}

				const result = validator(this.profileData.urls[profile.key])
				if (true !== result) {
					errors.push(result)
				}
			})

			if (errors.length) {
				this.errors[profile.key] = errors.join(', ')
			} else {
				this.errors[profile.key] = ''
			}
		})

		this.errorsKey++
	}
}
</script>

<style lang="scss">
.aioseo-social-profiles {
	--aioseo-gutter: 20px;

	.same-username {

		.use-same {
			padding: 16px;
			background: $box-background;
			border-radius: 4px;

			> label {
				align-items: flex-start;

				.form-checkbox-wrapper {
					margin-top: 2px;
				}
			}

			> .aioseo-settings-row {
				margin: 16px 0 0 24px;
				flex-direction: column;
				max-width: 720px;
				gap: 4px;
				flex-wrap: nowrap;

				.aioseo-input-container {
					max-width: 470px;
				}
			}

			.profiles {
				margin-top: 12px;

				--aioseo-gutter: 12px;

				@include aioseoGrid(5, 130px);

				.aioseo-col {
					max-width: none;
				}
			}
		}
	}

	.aioseo-social-profile-list {
		margin-top: 20px;
		display: flex;
		flex-direction: column;
		gap: 12px;

		.social-profile {
			margin-bottom: 0;
			padding-bottom: 0;
			border-bottom: none;
			gap: 8px;

			> .col-md-9:last-child {
				max-width: none;
			}

			.logo-svg {
				margin-right: 12px;
			}

			.profile-error {
				margin-top: 10px;
			}

			.name {
				margin-bottom: 0;
			}
		}
	}

	.additional-social-profiles {
		margin-top: var(--aioseo-gutter);
		padding-top: var(--aioseo-gutter);
		border-top: 1px solid $border;
		gap: 8px;

		> .col-md-9:last-child {
			max-width: none;
		}
	}
}
</style>