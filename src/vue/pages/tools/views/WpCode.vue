<template>
	<div class="aioseo-tools-wpcode">
		<div :class="blurClass">
			<grid-row>
				<grid-column
					v-for="(snippet, index) in wpCodeStore.snippets"
					:key="index"
					sm="12"
					md="6"
					lg="4"
				>
					<div class="aioseo-wpcode-snippet">
						<div class="wpcode-snippet-body">
							<div class="snippet-title">
								{{ snippet.title }}
							</div>

							<div class="snippet-description">
								{{ snippet.note }}
							</div>
						</div>

						<div class="wpcode-snippet-footer">
							<base-button
								v-if="snippet.install"
								type="blue"
								size="medium"
								tag="a"
								:href="decodeHTMLEntities(snippet.install)"
								@click="loadingUseSnippet = snippet.install"
								:loading="snippet.install === loadingUseSnippet"
							>
								{{ snippet.installed ? strings.editSnippet : strings.installSnippet }}
							</base-button>

							<base-button type="gray" size="medium" disabled v-else>
								{{ strings.installSnippet }}
							</base-button>
						</div>
					</div>
				</grid-column>
			</grid-row>
		</div>

		<cta
			v-if="!showSnippets && wpCodeStore.snippets.length"
			:button-text="ctaButtonText"
			:learn-more-link="$links.getDocUrl('wpcode')"
			:cta-button-loading="activationLoading"
			@cta-button-click="processUpdateOrActivate"
			same-tab
			align-top
			cta-button-action
		>
			<template #header-text>
				{{ ctaTitle }}
			</template>

			<template #description>
				{{ strings.ctaDescription }}
			</template>

			<template #learn-more-text>
				{{ strings.ctaLearnMoreText }}
			</template>
		</cta>

		<core-alert
			v-if="showSnippets && wpCodeStore.snippets.length === 0"
			type="yellow"
		>
			<div>{{ strings.noSnippets }}</div>
		</core-alert>
	</div>
</template>

<script>
import {
	usePluginsStore,
	useWpCodeStore
} from '@/vue/stores'

import tags from '@/vue/utils/tags'
import Cta from '@/vue/components/common/cta/Index.vue'
import CoreAlert from '@/vue/components/common/core/alert/Index.vue'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
export default {
	setup () {
		return {
			pluginsStore : usePluginsStore(),
			wpCodeStore  : useWpCodeStore()
		}
	},
	components : {
		Cta,
		CoreAlert,
		GridColumn,
		GridRow
	},
	data () {
		return {
			loadingUseSnippet : null,
			failed            : false,
			activationLoading : false,
			strings           : {
				codesnippets      : this.$t.__('Code Snippets', this.$td),
				installSnippet    : this.$t.__('Use Snippet', this.$td),
				editSnippet       : this.$t.__('Edit Snippet', this.$td),
				ctaDescription    : this.$t.__('Using WPCode you can install AIOSEO code snippets with 1-click directly from this page or the WPCode library inside the WordPress admin.', this.$td),
				ctaLearnMoreText  : this.$t.__('Learn More about WPCode Snippets', this.$td),
				noSnippets        : this.$t.__('We encountered an error loading the code snippets, please try again later.', this.$td),
				activateError     : this.$t.__('An error occurred while activating the addon. Please upload it manually or contact support for more information.', this.$td),
				permissionWarning : this.$t.__('You currently don\'t have permission to activate this addon. Please ask a site administrator to activate first.', this.$td)
			}
		}
	},
	computed : {
		showSnippets () {
			return this.wpCodeStore.pluginInstalled && this.wpCodeStore.pluginActive && !this.wpCodeStore.pluginNeedsUpdate
		},
		blurClass () {
			return this.showSnippets ? '' : 'aioseo-blur'
		},
		ctaTitle () {
			if (this.wpCodeStore.pluginNeedsUpdate) {
				return this.$t.__('Please Update WPCode to load the AIOSEO Snippet Library', this.$td)
			} else if (!this.wpCodeStore.pluginInstalled) {
				return this.$t.__('Please Install WPCode to load the AIOSEO Snippet Library', this.$td)
			} else if (!this.wpCodeStore.pluginActive) {
				return this.$t.__('Please Activate WPCode to load the AIOSEO Snippet Library', this.$td)
			}

			return this.$t.__('Please Install WPCode to load the AIOSEO Snippet Library', this.$td)
		},
		ctaButtonText () {
			if (this.wpCodeStore.pluginNeedsUpdate) {
				return this.$t.__('Update WPCode', this.$td)
			} else if (!this.wpCodeStore.pluginInstalled) {
				return this.$t.__('Install WPCode', this.$td)
			} else if (!this.wpCodeStore.pluginActive) {
				return this.$t.__('Activate WPCode', this.$td)
			}

			return this.$t.__('Install WPCode', this.$td)
		}
	},
	methods : {
		processUpdateOrActivate () {
			this.activateOrUpdate(this.wpCodeStore.pluginNeedsUpdate)
		},
		activateOrUpdate (update = false) {
			this.failed            = false
			this.activationLoading = true

			const action     = update ? 'upgradePlugins' : 'installPlugins'
			const wpcodePro  = this.pluginsStore.plugins.wpcodePro
			const pluginName = wpcodePro.installed ? 'wpcodePro' : 'wpcode'

			this.pluginsStore[action]([
				{
					plugin : pluginName,
					type   : 'plugin'
				}
			]).then((response) => {
				if (response.body.failed.length) {
					this.activationLoading = false
					this.failed            = true
					return
				}

				const promises = [
					this.wpCodeStore.loadSnippets()
				]

				Promise.all(promises)
					.then(() => {
						this.activationLoading  = false
					})
			})
				.catch(error => {
					this.activationLoading = false
					this.failed            = true
					console.error(`Unable to install ${pluginName}: ${error}`)
				})
		},
		decodeHTMLEntities (url) {
			return tags.decodeHTMLEntities(url)
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-wpcode {
	position: relative;
}

.aioseo-wpcode-snippet {
	height: 100%;
	border: 1px solid #E8E8EB;
	background: #fff;
	box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
	color: #141B38;
	display: flex;
	flex-direction: column;

	.wpcode-snippet-body {
		padding: 20px 20px 10px;
		flex: 1;
		line-height: 22px;
	}

	.wpcode-snippet-footer {
		padding: 15px;
		justify-content: flex-end;
		display: flex;
	}

	.snippet-title {
		display: flex;
		align-items: center;
		font-size: 16px;
		font-weight: 600;
		margin-bottom: 16px;
		line-height: 1.4;
	}
}
</style>