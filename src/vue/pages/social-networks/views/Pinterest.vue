<template>
	<div class="aioseo-pinterest">
		<core-card
			slug="pinterest"
			:header-text="strings.pinterest"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}

				<span
					v-html="$links.getDocLink(strings.learnHowToGetPinterestTag, 'pinterestSiteVerification', true)"
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
import {
	useOptionsStore
} from '@/vue/stores'

import { MetaTag } from '@/vue/mixins'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		CoreCard,
		CoreSettingsRow
	},
	mixins : [ MetaTag ],
	data () {
		return {
			pagePostOptions : [],
			strings         : {
				pinterest                 : this.$t.__('Pinterest', this.$td),
				description               : this.$t.__('Pinterest uses Open Graph metadata just like Facebook, so be sure to keep Open Graph enabled on the Facebook tab checked if you want to optimize your site for Pinterest.', this.$td),
				learnHowToGetPinterestTag : this.$t.__('Learn how to get your Pinterest Verification Code', this.$td),
				skipStep                  : this.$t.__('If you have already confirmed your website with Pinterest, you can skip the step below.', this.$td),
				pinterestVerificationCode : this.$t.__('Pinterest Verification Code', this.$td)
			}
		}
	}
}
</script>