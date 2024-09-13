<template>
	<div class="aioseo-pinterest">
		<core-card
			slug="pinterest"
			:header-text="strings.pinterest"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}

				<span
					v-html="links.getDocLink(strings.learnHowToGetPinterestTag, 'pinterestSiteVerification', true)"
				/>

				<br>
				<br>

				<strong>{{ strings.skipStep }}</strong>
			</div>

			<core-settings-row
				:name="strings.pinterestVerificationCode"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.webmasterTools.pinterest"
						@blur="maybeUpdateId('pinterest')"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useOptionsStore
} from '@/vue/stores'

import { useMetaTags } from '@/vue/composables/MetaTags'

import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { maybeUpdateId } = useMetaTags()

		return {
			maybeUpdateId,
			optionsStore : useOptionsStore(),
			links
		}
	},
	components : {
		CoreCard,
		CoreSettingsRow
	},
	data () {
		return {
			pagePostOptions : [],
			strings         : {
				pinterest                 : __('Pinterest', td),
				description               : __('Pinterest uses Open Graph metadata just like Facebook, so be sure to keep Open Graph enabled on the Facebook tab checked if you want to optimize your site for Pinterest.', td),
				learnHowToGetPinterestTag : __('Learn how to get your Pinterest Verification Code', td),
				skipStep                  : __('If you have already confirmed your website with Pinterest, you can skip the step below.', td),
				pinterestVerificationCode : __('Pinterest Verification Code', td)
			}
		}
	}
}
</script>