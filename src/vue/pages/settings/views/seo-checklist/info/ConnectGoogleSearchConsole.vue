<template>
	<div class="checklist-info checklist-info-connect-google-search-console">
		<h4>{{ strings.whatIs }}</h4>
		<p>{{ strings.whatIsDescription }}</p>

		<h4>{{ strings.benefits }}</h4>
		<ul>
			<li>
				{{ strings.sitemapDescription }}
			</li>
			<li>
				{{ strings.errorsDescription }}
			</li>
			<li>
				{{ strings.performanceDescription }}
			</li>
			<li>
				{{ strings.keywordsDescription }}
			</li>
			<li>
				{{ strings.securityDescription }}
			</li>
		</ul>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'ConnectGoogleSearchConsoleInfo',
	data () {
		return {
			strings : {
				whatIs                 : __('What is Google Search Console?', td),
				whatIsDescription      : __('Google Search Console is a free tool that provides valuable insights into how Google sees and ranks your site. Connecting takes just a few minutes and provides essential data for improving your SEO strategy.', td),
				benefits               : __('Why should you connect your site?', td),
				performanceDescription : __('Track your site\'s search performance, rankings, and click-through rates to identify areas for improvement.', td),
				sitemapDescription     : __('Submit and verify your XML sitemaps with Google to ensure your content is indexed quickly.', td),
				errorsDescription      : __('Identify and fix crawling errors that prevent pages from being indexed.', td),
				keywordsDescription    : __('See which keywords drive traffic to your site and identify new opportunities.', td),
				securityDescription    : __('Get notified of issues and penalties before they impact your rankings.', td)
			}
		}
	}
}
</script>