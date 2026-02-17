<template>
	<div class="checklist-info checklist-info-fill-knowledge-graph">
		<h4>{{ strings.howWorks }}</h4>
		<p>{{ strings.howWorksDescription }}</p>

		<h4>{{ strings.whatIsPanel }}</h4>
		<p>{{ strings.whatIsPanelDescription }}</p>

		<h4>{{ strings.whyImportant }}</h4>
		<ul>
			<li>
				{{ strings.prominentDisplayDescription }}
			</li>
			<li>
				{{ strings.brandAuthorityDescription }}
			</li>
			<li>
				{{ strings.consistentInfoDescription }}
			</li>
		</ul>

		<p v-html="learnMoreLink" />
	</div>
</template>

<script>
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	data () {
		return {
			strings : {
				whatIs                      : __('What is this?', td),
				whatIsDescription           : __('These settings add schema markup to your site with your organization or personal information. Search engines use this structured data to understand your brand identity and display it in the Knowledge Graph.', td),
				whatIsPanel                 : __('What is the Knowledge Graph?', td),
				whatIsPanelDescription      : __('The Knowledge Graph is Google\'s database of entities and facts. When you search for a brand or person, Google often shows a Knowledge Panel on the right side of search results with key information, logo, social profiles, and related links.', td),
				howWorks                    : __('What do these settings do?', td),
				howWorksDescription         : __('When you fill out your organization or person details in AIOSEO, this information is automatically output as schema.org markup that Google and other search engines can read. This helps them populate your Knowledge Panel with accurate information.', td),
				whyImportant                : __('Why is this important?', td),
				prominentDisplayDescription : __('Your Knowledge Panel appears prominently in branded searches, showing your logo, description, and key details at a glance.', td),
				brandAuthorityDescription   : __('A well-populated Knowledge Panel signals legitimacy and helps build trust with potential customers and search engines.', td),
				consistentInfoDescription   : __('Provide Google with accurate data directly from your site instead of relying on third-party sources that may be outdated or incorrect.', td),
				learnMore                   : __('Learn More', td)
			}
		}
	},
	computed : {
		learnMoreLink () {
			return links.getDocLink(this.strings.learnMore, 'configuringSchema', true)
		}
	}
}
</script>