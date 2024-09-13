<template>
	<div id="aioseo-admin-bar-noindex-warning">
		<div class="wrapper">
			<svg
				width="14"
				height="14"
				viewBox="0 0 14 14"
				fill="currentColor"
				xmlns="http://www.w3.org/2000/svg"
			>
				<path
					d="M7.00001 13.9C6.04445 13.9 5.14723 13.7195 4.30834 13.3583C3.46945 12.9972 2.73889 12.5028 2.11667 11.875C1.49445 11.2472 1.00278 10.5139 0.641673 9.67501C0.280562 8.83612 0.100006 7.9389 0.100006 6.98334C0.100006 6.0389 0.280562 5.14723 0.641673 4.30834C1.00278 3.46945 1.49445 2.7389 2.11667 2.11668C2.73889 1.49445 3.46945 1.00001 4.30834 0.633344C5.14723 0.266677 6.04445 0.0833435 7.00001 0.0833435C7.95556 0.0833435 8.85278 0.266677 9.69167 0.633344C10.5306 1.00001 11.2611 1.49445 11.8833 2.11668C12.5056 2.7389 13 3.46945 13.3667 4.30834C13.7333 5.14723 13.9167 6.0389 13.9167 6.98334C13.9167 7.9389 13.7361 8.8389 13.375 9.68334C13.0139 10.5278 12.5195 11.2611 11.8917 11.8833C11.2639 12.5056 10.5306 12.9972 9.69167 13.3583C8.85278 13.7195 7.95556 13.9 7.00001 13.9ZM7.00001 12.5833C8.55556 12.5833 9.87501 12.0417 10.9583 10.9583C12.0417 9.87501 12.5833 8.55001 12.5833 6.98334C12.5833 6.35001 12.4778 5.73612 12.2667 5.14168C12.0556 4.54723 11.7556 4.01112 11.3667 3.53334L3.53334 11.35C4.01112 11.7611 4.55001 12.0695 5.15001 12.275C5.75001 12.4806 6.36667 12.5833 7.00001 12.5833ZM2.65001 10.45L10.45 2.63334C9.96112 2.23334 9.42223 1.93057 8.83334 1.72501C8.24445 1.51945 7.63334 1.41668 7.00001 1.41668C5.44445 1.41668 4.12501 1.95557 3.04167 3.03334C1.95834 4.11112 1.41667 5.42779 1.41667 6.98334C1.41667 7.61668 1.52778 8.23057 1.75001 8.82501C1.97223 9.41945 2.27223 9.96112 2.65001 10.45Z"
					fill="currentColor"
				/>
			</svg>

			<span>{{ strings.blockingSearchEngines }}</span>

			<div class="noindex-warning-tooltip">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="24"
					height="14"
					viewBox="0 0 24 14"
					fill="none"
				>
					<path
						d="M1.20711 11.5L10.9393 1.76777C11.5251 1.18198 12.4749 1.18198 13.0607 1.76777L22.7929 11.5H1.20711Z"
						fill="white"
						stroke="white"
					/>
					<path
						d="M10.5858 2.91421L0 13.5H24L13.4142 2.91421C12.6332 2.13317 11.3668 2.13317 10.5858 2.91421Z"
						fill="white"
					/>
				</svg>

				<p v-html="strings.description" />
			</div>
		</div>
	</div>
</template>

<script setup>
import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	blockingSearchEngines : __('Search Engines Blocked!', td),
	description           : sprintf(
		// Translators: 1 - The short plugin name ("AIOSEO"), 2 - Opening HTML anchor tag, 3 - Closing HTML anchor tag.
		__('%1$s has detected that you are blocking access to search engines, which will prevent your site from ranking in search results. You can re-enable indexing under %2$sSettings > Reading →%3$s', td),
		import.meta.env.VITE_SHORT_NAME,
		sprintf(
			'<a href="%1$s" target="_blank">',
			window.aioseoAdminBarNoindexWarning.optionsReadingUrl
		),
		'</a>'
	)
}
</script>

<style lang="scss">
#aioseo-admin-bar-noindex-warning {
	display: flex;

	margin: 4px 0;
	padding: 0 4px;
	border-radius: 4px;

	background-color: #FEE2E2;

	.wrapper {
		display: inline-flex;
		align-items: center;
		max-height: 24px;

		svg {
			margin-right: 6px;
			color: #DF2A4A;
		}

		span {
			font-size: 13px;
			font-weight: 600;
			letter-spacing: -0.4px;
			color: #DF2A4A;
		}

		.noindex-warning-tooltip {
			display: none;
		}

		&:hover .noindex-warning-tooltip {
			display: block;
			position: absolute;
			top: 35px;
			left: -10px;

			width: 185px;
			padding: 12px;

			text-align: left;
			border: 1px solid white;
			border-radius: 5px;
			background-color: white;
			box-shadow: 0px 8px 36px 0px rgba(44, 50, 76, 0.16);

			cursor: default;
			z-index: 1;

			p {
				font-size: 12px;
				font-style: normal;
				font-weight: 400;
				line-height: 18px;
				color: $black;

				a {
					display: contents;
					font-size: 12px;
					font-style: normal;
					font-weight: 400;
					line-height: 18px;
					text-decoration: underline;
					color: $blue;

					&:hover {
						text-decoration: none;
					}
				}
			}
		}

		.noindex-warning-tooltip > svg {
			position: absolute;
			top: -12px;
			left: 92.5px;
		}

		.noindex-warning-tooltip > svg * {
			fill: white;
		}
	}
}
</style>