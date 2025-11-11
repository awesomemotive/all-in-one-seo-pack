<template>
	<div class="aioseo-toc-select-mode">
		<div class="aioseo-toc-header-title">
			<div class="aioseo-toc-select-mode__collapsible">
				<div
					class="aioseo-toc-header-collapsible"
					@click="toggleCollapsed"
				>
					<svg v-if="blockAttributes.collapsed" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 8H0V6H6V0H8V6H14V8H8V14H6V8Z" fill="#005AE0" />
					</svg>

					<svg v-else width="14" height="2" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 2V0H14V2H0Z" fill="#005AE0" />
					</svg>
				</div>

				<p>
					<span v-if="blockAttributes.collapsed">{{ strings.showTableOfContents }}</span>
					<span v-else>{{ strings.hideTableOfContents }}</span>
				</p>
			</div>
		</div>

		<div v-if="!blockAttributes.collapsed">
			<p class="aioseo-toc-select-mode__description">{{ strings.description }}</p>

			<div class="aioseo-toc-select-mode__selectors">
				<div class="aioseo-toc-select-mode__selector">
					<div class="aioseo-toc-select-mode__selector-icon">
						<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_171_2017)">
								<rect width="37.3333" height="37.3333" rx="2" fill="#1A82EA" />
								<rect x="7.29712" y="7.81384" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.29712" y="12.5684" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.29712" y="17.3229" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.29712" y="22.0774" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.29736" y="26.8322" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="12.0515" y="7.81384" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="12.0515" y="12.5684" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="12.0515" y="17.3229" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="12.0515" y="22.0774" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="12.0513" y="26.8322" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="19" y="19" width="37.3333" height="37.3333" rx="2" fill="white" />
								<rect x="22" y="22" width="37.3333" height="37.3333" rx="2" fill="#80B2FF" />
								<rect x="29.2971" y="29.814" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="29.2971" y="34.5685" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="29.2971" y="39.3229" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="29.2971" y="44.0774" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="29.2971" y="48.8322" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="34.0518" y="29.814" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="34.0518" y="34.5685" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="34.0518" y="39.3229" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="34.0518" y="44.0774" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="34.0518" y="48.8322" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="39.6667" y="39.6666" width="37.3333" height="37.3333" rx="2" fill="white" />
								<rect x="42.6667" y="42.6666" width="37.3333" height="37.3333" rx="2" fill="#99C2FF" />
								<rect x="49.9641" y="50.4806" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="49.9641" y="55.2351" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="49.9641" y="59.9896" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="49.9641" y="64.7441" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="49.9641" y="69.4985" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="54.7185" y="50.4806" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.7185" y="55.2351" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.7185" y="59.9896" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.7185" y="64.7441" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.7185" y="69.4985" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
							</g>
							<defs>
								<clipPath id="clip0_171_2017">
									<rect width="80" height="80" fill="white" />
								</clipPath>
							</defs>
						</svg>
					</div>

					<h3 class="aioseo-toc-select-mode__selector-title">{{ strings.standaloneTitle }}</h3>

					<p class="aioseo-toc-select-mode__selector-description">{{ strings.standaloneDescription }}</p>
					<base-button type="blue" @click="setMode('standalone')" size="base">
						{{ strings.enableStandalone }}
					</base-button>
				</div>

				<div class="aioseo-toc-select-mode__selector">
					<div class="aioseo-toc-select-mode__selector-icon">
						<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_171_2066)">
								<path
									d="M9.8667 40V13.6C9.8667 11.3908 11.6576 9.59998 13.8667 9.59998H66.6667C68.8758 9.59998 70.6667 11.3908 70.6667 13.6V66.4C70.6667 68.6091 68.8758 70.4 66.6667 70.4H40.2667"
									stroke="#141B38" stroke-linecap="round" stroke-linejoin="round"
									stroke-dasharray="1 2.4" />
								<rect y="24.8063" width="37.0026" height="37.416" rx="2" fill="#1A82EA" />
								<rect x="7.13159" y="32.6615" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.13159" y="37.416" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.13159" y="42.1705" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.13159" y="46.925" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="7.13159" y="51.6797" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="11.8862" y="32.6615" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="11.8862" y="37.416" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="11.8862" y="42.1705" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="11.8862" y="46.925" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="11.8862" y="51.6797" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="42.9331" width="37.0026" height="37.416" rx="2" fill="#80B2FF" />
								<rect x="50.0647" y="7.85522" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="50.0649" y="12.6097" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="50.0649" y="17.3641" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="50.0649" y="22.1187" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="50.0649" y="26.8734" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="54.8193" y="7.85522" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.8191" y="12.6097" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.8191" y="17.3641" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.8191" y="22.1187" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="54.8191" y="26.8734" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="21.2666" y="39.1334" width="37.0667" height="37.8667" rx="2" fill="white" />
								<rect x="28.4304" y="47.214" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="28.4304" y="51.9685" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="28.4304" y="56.7231" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="28.4304" y="61.4777" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="28.4304" y="66.2322" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="33.1851" y="47.214" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="33.1853" y="51.9685" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="33.1853" y="56.7231" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="33.1853" y="61.4777" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="33.1853" y="66.2322" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="24.2666" y="42.1334" width="37.0667" height="37.8667" rx="2" fill="#99C2FF" />
								<rect x="31.4304" y="50.214" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="31.4304" y="54.9685" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="31.4304" y="59.7231" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="31.4304" y="64.4777" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="31.4304" y="69.2322" width="2.68734" height="2.68734" rx="1.34367" fill="white" />
								<rect x="36.1851" y="50.214" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="36.1853" y="54.9685" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="36.1853" y="59.7231" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="36.1853" y="64.4777" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<rect x="36.1853" y="69.2322" width="17.9845" height="2.68734" rx="0.413437" fill="white" />
								<circle cx="26" cy="10" r="7" fill="#00AA63" />
								<path
									d="M22.6667 13.3333V12.5H23.8126L23.6459 12.3541C23.2848 12.0347 23.0313 11.6701 22.8855 11.2604C22.7397 10.8507 22.6667 10.4375 22.6667 10.0208C22.6667 9.24996 22.8977 8.5642 23.3595 7.9635C23.8213 7.36281 24.4237 6.96524 25.1667 6.77079V7.64579C24.6667 7.82635 24.264 8.13364 23.9584 8.56767C23.6529 9.0017 23.5001 9.48607 23.5001 10.0208C23.5001 10.3333 23.5591 10.6371 23.6772 10.9323C23.7952 11.2274 23.9792 11.5 24.2292 11.75L24.3334 11.8541V10.8333H25.1667V13.3333H22.6667ZM26.8334 13.2291V12.3541C27.3334 12.1736 27.7362 11.8663 28.0417 11.4323C28.3473 10.9982 28.5001 10.5138 28.5001 9.97913C28.5001 9.66663 28.4411 9.36281 28.323 9.06767C28.2049 8.77253 28.0209 8.49996 27.7709 8.24996L27.6667 8.14579V9.16663H26.8334V6.66663H29.3334V7.49996H28.1876L28.3542 7.64579C28.6945 7.98607 28.9428 8.35586 29.099 8.75517C29.2553 9.15447 29.3334 9.56246 29.3334 9.97913C29.3334 10.75 29.1025 11.4357 28.6407 12.0364C28.1789 12.6371 27.5765 13.0347 26.8334 13.2291Z"
									fill="white" />
								<circle cx="71" cy="55" r="7" fill="#00AA63" />
								<path
									d="M67.6667 58.3333V57.5H68.8126L68.6459 57.3541C68.2848 57.0347 68.0313 56.6701 67.8855 56.2604C67.7397 55.8507 67.6667 55.4375 67.6667 55.0208C67.6667 54.25 67.8977 53.5642 68.3595 52.9635C68.8213 52.3628 69.4237 51.9652 70.1667 51.7708V52.6458C69.6667 52.8263 69.264 53.1336 68.9584 53.5677C68.6529 54.0017 68.5001 54.4861 68.5001 55.0208C68.5001 55.3333 68.5591 55.6371 68.6772 55.9323C68.7952 56.2274 68.9792 56.5 69.2292 56.75L69.3334 56.8541V55.8333H70.1667V58.3333H67.6667ZM71.8334 58.2291V57.3541C72.3334 57.1736 72.7362 56.8663 73.0417 56.4323C73.3473 55.9982 73.5001 55.5138 73.5001 54.9791C73.5001 54.6666 73.4411 54.3628 73.323 54.0677C73.2049 53.7725 73.0209 53.5 72.7709 53.25L72.6667 53.1458V54.1666H71.8334V51.6666H74.3334V52.5H73.1876L73.3542 52.6458C73.6945 52.9861 73.9428 53.3559 74.099 53.7552C74.2553 54.1545 74.3334 54.5625 74.3334 54.9791C74.3334 55.75 74.1025 56.4357 73.6407 57.0364C73.1789 57.6371 72.5765 58.0347 71.8334 58.2291Z"
									fill="white" />
							</g>
							<defs>
								<clipPath id="clip0_171_2066">
									<rect width="80" height="80" fill="white" />
								</clipPath>
							</defs>
						</svg>
					</div>

					<h3 class="aioseo-toc-select-mode__selector-title">{{ strings.syncedTitle }}</h3>

					<p class="aioseo-toc-select-mode__selector-description">{{ strings.syncedDescription }}</p>

					<base-button type="blue" @click="setMode('synced')" size="base">
						{{ strings.enableSynced }}
					</base-button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import BaseButton from '@/vue/components/common/base/Button'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	clientId : {
		type     : String,
		required : true
	}
})

const blockAttributes = ref(window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId))

const strings = {
	showTableOfContents   : __('Show Table of Contents', td),
	hideTableOfContents   : __('Hide Table of Contents', td),
	description           : __('Choose whether you want this table of contents to be standalone or synced. You can change these block settings later from the block panel.', td),
	standaloneTitle       : __('Standalone', td),
	standaloneDescription : __('Each ToC block acts independently, showing the same headings across all blocks for a unified navigation.', td),
	syncedTitle           : __('Synced', td),
	syncedDescription     : __('Multiple ToC blocks working together, pointing to different sections of your content with their own headings.', td),
	enableStandalone      : __('Enable Standalone', td),
	enableSynced          : __('Enable Synced', td)
}

const setMode = (mode) => {
	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
		mode : mode
	})
}

const toggleCollapsed = () => {
	const newCollapsedState = !blockAttributes.value.collapsed
	blockAttributes.value.collapsed = newCollapsedState

	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
		collapsed : newCollapsedState
	})
}

onMounted(() => {
	nextTick(() => {
		window.aioseoBus.$on('updateToc' + props.clientId, () => {
			blockAttributes.value = window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId)
		})
	})
})
</script>