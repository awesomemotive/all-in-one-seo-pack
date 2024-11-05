<template>
	<div
		class="aioseo-seoboost-login"
		:class="{ 'aioseo-seoboost-login--button-only': props.buttonOnly }"
	>
		<template v-if="!props.buttonOnly">
			<img :src="getAssetUrl(loginImg)"/>
			<div class="aioseo-seoboost-login__title">
				{{ strings.title }}
				<br/>
				{{ strings.title2 }}
			</div>
			<div class="aioseo-seoboost-login__description">
				{{ strings.description }}
			</div>
		</template>
		<base-button
			:type="props.buttonType"
			@click="login"
			size="medium"
		>
			<img
				:src="getAssetUrl(iconLogo)"
				v-if="props.buttonIcons"
			/>
			{{ props.buttonText || strings.login }}
			<svg-caret v-if="props.buttonIcons" />
		</base-button>
	</div>
</template>

<script setup>
import BaseButton from '@/vue/components/common/base/Button'
import loginImg from '@/vue/assets/images/writing-assistant/login.svg'
import iconLogo from '@/vue/assets/images/writing-assistant/icon-logo.svg'
import SvgCaret from '@/vue/components/common/svg/Caret'
import {
	useWritingAssistantStore,
	useWritingAssistantSettingsStore
} from '@/vue/stores'
import { getAssetUrl } from '@/vue/utils/helpers'
import { __ } from '@/vue/plugins/translations'
import { watch } from 'vue'

const td = import.meta.env.VITE_TEXTDOMAIN

const writingAssistantStore = useWritingAssistantStore()
const writingAssistantSettingsStore = useWritingAssistantSettingsStore()

const strings = {
	title       : __('Elevate your SEO with AIOSEO Writing Assistant', td),
	title2      : __('Now Integrated into SEOBoost', td),
	description : __('Experience the power of AI-driven writing assistance seamlessly integrated into SEOBoost. Login to enhance your content creation process and boost your search rankings.', td),
	login       : __('Login to SEOBoost', td)
}

const props = defineProps({
	buttonOnly : {
		type    : Boolean,
		default : false
	},
	buttonText : String,
	buttonType : {
		type    : String,
		default : 'blue'
	},
	buttonIcons : {
		type    : Boolean,
		default : true
	},
	openLogin : {
		type    : Boolean,
		default : false
	}
})

watch(() => props.openLogin, (value) => {
	if (value) {
		login()
	}
})

const login = () => {
	// Open a new small window to authenticate.
	const url = writingAssistantStore.seoBoost.loginUrl || writingAssistantSettingsStore.seoBoost.loginUrl
	const width = 450
	const height = 580
	const left = window.innerWidth / 2 - width / 2
	const top = window.innerHeight / 2 - height / 2
	const windowFeatures = `width=${width},height=${height},resizable=no,scrollbars=no,status=no,location=no,toolbar=no,menubar=no`
	const loginWindow = window.open(url, '_blank', windowFeatures)
	loginWindow.moveBy(left, top)

	window.addEventListener('message', (event) => {
		if (event.origin !== window.location.origin) {
			return
		}

		if ('seoboost-authenticated' === event.data) {
			writingAssistantStore.setUserLoggedIn(true)
			writingAssistantSettingsStore.setUserLoggedIn(true)
		}
	})
}
</script>

<style lang="scss">
.aioseo-seoboost-login {
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;

	.aioseo-writing-assistant-sidebar & {
		text-align: left;
	}

	&__title {
		font-size: 16px;
		font-weight: 700;
		margin-top: 40px;
		line-height: 24px;
	}

	&__description {
		max-width: 600px;
		margin-top: 8px;
		line-height: 22px;
	}

	button {
		margin-top: 20px;

		> img {
			margin-right: 8px;
		}

		> .aioseo-caret {
			width: 16px;
			height: 16px;
			margin: 0 0 0 8px;
			color: #fff;
			fill: #fff;
			transform: rotate(-90deg);

			path {
				fill: #fff !important;
			}
		}
	}

	&--button-only {
		align-items: flex-start;

		> button {
			margin-top: 0;
		}
	}
}
</style>