<template>
	<div class="checklist-info checklist-info-enable-llms-txt">
		<h4>{{ strings.whatIs }}</h4>
		<p>{{ strings.whatIsDescription }}</p>

		<h4>{{ strings.whyEnable }}</h4>
		<ul>
			<li>
				{{ strings.betterAiDescription }}
			</li>
			<li>
				{{ strings.futureProofDescription }}
			</li>
		</ul>

		<h4>{{ strings.howWorks }}</h4>
		<p>{{ strings.howWorksDescription }}</p>

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
				whatIs                 : __('What is llms.txt?', td),
				whatIsDescription      : __('The llms.txt file is a standardized way to provide information about your website to AI language models (LLMs) like ChatGPT, Claude, and others. It\'s similar to robots.txt, but specifically designed for AI crawlers.', td),
				whyEnable              : __('Why should you enable it?', td),
				betterAiDescription    : __('Helps AI models accurately represent your content when users ask questions about your site or company.', td),
				futureProofDescription : __('As AI-powered search becomes more prevalent, having a well-structured llms.txt can improve your visibility.', td),
				howWorks               : __('How does it work?', td),
				howWorksDescription    : __('When enabled, AIOSEO generates an llms.txt file at the root of your site. This file contains structured information about your site that AI models can read and use to better understand your content.', td),
				learnMore              : __('Learn More', td)
			}
		}
	},
	computed : {
		learnMoreLink () {
			return links.getDocLink(this.strings.learnMore, 'llmsTxt', true)
		}
	}
}
</script>