<template>
	<div id="aioseo-user-profile-tab">
		<div class="navigation-bar">
			<a
				v-for="(tabObject, index) in tabs"
				:key="index"
				:class="{ active: index === activeTabIndex }"
				href="#"
				@click.prevent="setActiveTab(index)"
			>
				<component
					v-if="tabObject.svg"
					:is="tabObject.svg"
				/>

				{{ tabObject.label }}
			</a>
		</div>

		<CoreCard
			slug="userProfiles"
			:header-text="strings.socialProfiles"
			no-slide
			:toggles="false"
			v-if="'social-profiles' === activeTabObject.slug"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
			</div>

			<CoreSocialProfiles
				:userProfiles="settingsStore.userProfile.profiles"
				@updated="newSocialProfiles => updateHiddenInputField(newSocialProfiles)"
			/>
		</CoreCard>
	</div>
</template>

<script>
import {
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import CoreSocialProfiles from '@/vue/components/common/core/SocialProfiles'
import SvgLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

export default {
	setup () {
		return {
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreCard,
		CoreSocialProfiles,
		SvgLogoGear
	},
	data () {
		return {
			activeTabIndex : 0,
			strings        : {
				socialProfiles : this.$t.__('Social Profiles', this.$td),
				description    : this.$t.__('To let search engines know which profiles are associated with this user, enter them below:', this.$td)
			}
		}
	},
	methods : {
		setActiveTab (index) {
			const originalTabIndex = this.activeTabIndex
			this.activeTabIndex    = index

			switch (this.activeTabObject.slug) {
				case 'personal-options':
					document.getElementById('your-profile').childNodes.forEach(node => {
						if (node.style) {
							node.style.display = 'block'
						}
					})
					break
				case 'social-profiles':
					document.getElementById('your-profile').childNodes.forEach(node => {
						if (
							'aioseo-user-profile-tab' === node.id ||
							'submit' === node.className ||
							!node.style
						) {
							return
						}

						node.style.display = 'none'
					})
					break
				case 'customer-data':
					// Reset back to original tab index so that the screen isn't blank while we're loading the new page.
					this.activeTabIndex = originalTabIndex

					window.location.href = this.rootStore.aioseo.urls.home +
						'/wp-admin/admin.php?page=followup-emails-reports&tab=reportuser_view&email=' + encodeURIComponent(this.settingsStore.userProfile.userData.user_email) +
						'&user_id=' + this.settingsStore.userProfile.userData.ID
					break
				default:
					break
			}
		},
		updateHiddenInputField (newSocialProfiles) {
			document.getElementById('aioseo-user-social-profiles').value = JSON.stringify(newSocialProfiles)
		}
	},
	computed : {
		tabs () {
			const tabs = [
				{
					label : this.$t.__('Personal Options', this.$td),
					slug  : 'personal-options'
				},
				{
					label : this.$t.__('Social Profiles', this.$td),
					slug  : 'social-profiles',
					svg   : 'svg-logo-gear'
				}
			]

			if (this.settingsStore.userProfile.isWooCommerceFollowupEmailsActive) {
				tabs.push({
					label : this.$t.__('Customer Data', this.$td),
					slug  : 'customer-data'
				})
			}

			return tabs
		},
		activeTabObject () {
			return this.tabs[this.activeTabIndex]
		}
	},
	async created () {
		// Set the initial values.
		this.updateHiddenInputField(this.settingsStore.userProfile.profiles)
	},
	mounted () {
		const params = new URLSearchParams(window.location.search)
		if (params && params.get('social-profiles')) {
			this.setActiveTab(1)
		}
	}
}
</script>

<style lang="scss">
// Hide the nav bar from WooCommerce Follow-up Emails.
h2.woo-nav-tab-wrapper {
	display: none !important;
}

#aioseo-user-profile-tab {
	--aioseo-gutter: 20px;

	max-width: 1220px;

	.navigation-bar {
		border-bottom: 1px solid #c3c4c7;
		margin: 0;
		padding-top: 9px;
		padding-bottom: 0;
		line-height: inherit;

		a {
			display: flex;
			align-items: center;

			float: left;
			border: 1px solid #c3c4c7;
			border-bottom: none;
			margin-left: 0.5em;
			padding: 5px 10px;
			font-size: 14px;
			line-height: 1.71428571;
			font-weight: 600;
			background: #dcdcde;
			color: #50575e;
			text-decoration: none;
			white-space: nowrap;

			&:hover {
				background-color: #fff;
				color: #3c434a;
			}

			&.active,
			&.active:hover {
				margin-bottom: -1px;
				color: #3c434a;
				background-color: #f1f1f1;
				border-bottom: 1px solid #f0f0f1;
				border-bottom-color: #f1f1f1;
			}

			svg {
				width: 20px;
				height: 20px;
				margin-right: 8px;
			}
		}

		&:after {
			content: "";
			display: table;
			clear: both;
		}
	}

	.aioseo-description {
		margin: 8px 0 0;
	}
}
</style>