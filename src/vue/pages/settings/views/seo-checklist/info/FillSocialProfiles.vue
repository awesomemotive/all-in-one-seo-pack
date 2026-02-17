<template>
	<div class="checklist-info">
		<h4>{{ strings.whyAdd }}</h4>
		<p>{{ strings.description }}</p>
		<ul>
			<li>{{ strings.benefit1 }}</li>
			<li>{{ strings.benefit2 }}</li>
			<li>{{ strings.benefit3 }}</li>
		</ul>
		<p>{{ strings.platforms }}</p>
		<p v-html="learnMoreLink" />
	</div>
</template>

<script>
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'FillSocialProfilesInfo',
	data () {
		return {
			strings : {
				whyAdd      : __('Why add your social profiles?', td),
				description : __('Adding your social media profiles helps search engines and visitors connect with your brand across multiple platforms:', td),
				benefit1    : __('Helps search engines verify your brand identity and appear in Google\'s Knowledge Panel.', td),
				benefit2    : __('Connect all your online properties under one unified presence.', td),
				benefit3    : __('Makes it easier for users to find and follow you on social media.', td),
				learnMore   : __('Learn More', td)
			}
		}
	},
	computed : {
		learnMoreLink () {
			return links.getDocLink(this.strings.learnMore, 'socialProfiles', true)
		}
	}
}
</script>