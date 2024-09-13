<template>
	<div class="aioseo-wizard-import">
		<wizard-header />
		<wizard-container>

			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.importData }}
				</div>

				<div class="description">{{ strings.weHaveDetected }}</div>

				<div class="plugins">
					<grid-row>
						<grid-column
							v-for="(plugin, index) in getPlugins"
							:key="index"
							md="6"
						>
							<base-highlight-toggle
								type="checkbox"
								size="medium"
								round
								:active="isActive(plugin)"
								:name="plugin.name"
								:modelValue="getValue(plugin)"
								@update:modelValue="checked => updateValue(checked, plugin)"
							>
								<img
									:alt="plugin.name + ' Plugin Icon'"
									v-if="pluginImages[plugin.slug]"
									:src="pluginImages[plugin.slug]"
									class="icon"
									:class="plugin.slug"
								/>
								<span
									v-if="!pluginImages[plugin.slug]"
									class="icon dashicons dashicons-admin-plugins"
								/>
								{{ plugin.name }}
							</base-highlight-toggle>
						</grid-column>
					</grid-row>
				</div>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="gray"
						@click="skipStep"
					>{{ strings.skipThisStep }}</base-button>
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndContinue"
					>{{ strings.importDataAndContinue }} &rarr;</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import {
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import yoastSeoImg from '@/vue/assets/images/plugins/yoast-logo-small.png'
import rankMathSeoImg from '@/vue/assets/images/plugins/rank-math-seo-logo-small.png'
import seopressImg from '@/vue/assets/images/plugins/seopress-free-logo-small.svg'
import seopressProImg from '@/vue/assets/images/plugins/seopress-pro-logo-small.svg'
import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useWizard({
			stage : 'import'
		})

		return {
			composableStrings : strings,
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore()
		}
	},
	components : {
		BaseHighlightToggle,
		GridColumn,
		GridRow,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			loading : false,
			strings : merge(this.composableStrings, {
				importData     : __('Import data from your current plugins', td),
				weHaveDetected : sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					__('We have detected other SEO plugins installed on your website. Select which plugins you would like to import data to %1$s.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				importDataAndContinue : __('Import Data and Continue', td)
			}),
			pluginImages : {
				'yoast-seo'         : getAssetUrl(yoastSeoImg),
				'yoast-seo-premium' : getAssetUrl(yoastSeoImg),
				'rank-math-seo'     : getAssetUrl(rankMathSeoImg),
				seopress            : getAssetUrl(seopressImg),
				'seopress-pro'      : getAssetUrl(seopressProImg)
			},
			selected : []
		}
	},
	watch : {
		selected (newVal) {
			this.setupWizardStore.importers = newVal.map(v => v.slug)
		}
	},
	computed : {
		getPlugins () {
			return this.rootStore.aioseo.importers
				.filter(plugin => plugin.canImport)
		}
	},
	methods : {
		updateValue (checked, plugin) {
			if (checked) {
				this.selected.push(plugin)
				return
			}

			const index = this.selected.findIndex(p => p.value === plugin.value)
			if (-1 !== index) {
				this.selected.splice(index, 1)
			}
		},
		getValue (plugin) {
			return this.selected.includes(plugin)
		},
		isActive (plugin) {
			const index = this.selected.findIndex(p => p.slug === plugin.slug)
			if (-1 !== index) {
				return true
			}

			return false
		},
		saveAndContinue () {
			this.loading = true
			this.setupWizardStore.saveWizard('importers')
				.then(() => {
					this.$router.push(this.setupWizardStore.getNextLink)
				})
		},
		skipStep () {
			this.setupWizardStore.saveWizard()
			this.$router.push(this.setupWizardStore.getNextLink)
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-import {
	font-size: 14px;

	.plugins {
		--aioseo-gutter: 16px;

		img {
			width: 25px;
			height: auto;

			&.seopress,
			&.seopress-pro {
				width: 20px;
				margin: 0 5px 0 5px;
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