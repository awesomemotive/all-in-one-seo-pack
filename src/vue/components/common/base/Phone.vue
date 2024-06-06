<template>
	<maz-phone-number-input
		class="aioseo-phone-number"
		:class="{ invalidNumber: isInvalidNumber, validNumber: isValidNumber }"
		v-model="phoneNumber"
		show-code-on-list
		:ignored-countries="['KP']"
		:preferred-countries="['US', 'GB', 'CA', 'AU', 'JP', 'RU', 'IN', 'TR', 'BR', 'MX']"
		:translations="translations"
		@update="emit"
	/>
</template>

<script>
import { DateTime } from 'luxon'
import MazPhoneNumberInput from 'maz-ui/components/MazPhoneNumberInput'
export default {
	components : { MazPhoneNumberInput },
	emits      : [ 'inputFormatted', 'update:modelValue' ],
	props      : {
		modelValue : String
	},
	data () {
		return {
			phoneNumber     : this.modelValue,
			isInvalidNumber : false,
			isValidNumber   : false,
			translations    : {
				countrySelector : {
					placeholder : this.$t.__('Country code', this.$td),
					error       : this.$t.__('Choose country', this.$td)
				},
				phoneInput : {
					placeholder : this.$t.__('Phone number', this.$td),
					example     : this.$t.__('Example', this.$td) + ':'
				}
			}
		}
	},
	methods : {
		emit (phoneNumber) {
			this.isInvalidNumber = false
			this.isValidNumber   = false
			if (!phoneNumber.isValid) {
				if (phoneNumber.e164) {
					this.isInvalidNumber = true
				}
				return this.$emit('update:modelValue', '')
			}
			this.isValidNumber = true

			// The formatted value is used for display inside our Business Info block/widget/shortcode.
			this.$emit('inputFormatted', '+' + phoneNumber.countryCallingCode + ' ' + phoneNumber.formatNational)
			return this.$emit('update:modelValue', phoneNumber.e164)
		}
	},
	mounted () {
		// Prevent the browser from autofilling in a phone number.
		const timestamp = DateTime.now().toFormat('x')
		const value     = 'aioseo-phone-number-' + timestamp.toString()
		document.querySelectorAll('.aioseo-phone-number input').forEach((node) => {
			node.setAttribute('autocomplete', value)
		})
	}
}
</script>

<style lang="scss">

.aioseo-phone-number {
	// Copied from 'maz-ui/css/main.css'
	// All instances of :root have been changed to &:where(.m-phone-number-input) to avoid adding styles globally.
	// :where() is being used to reduce specificity so we can override the styles.
	*,:after,:before{border:0 solid;-webkit-box-sizing:border-box;box-sizing:border-box}button{background-color:initial}button,input,optgroup,select,textarea{font-family:inherit;font-size:100%;line-height:1.15;margin:0}&:where(.m-phone-number-input){--maz-color-primary-50:#e6f2ff;--maz-color-primary-100:#bddeff;--maz-color-primary-200:#94c9ff;--maz-color-primary-300:#6bb5ff;--maz-color-primary-400:#47a3ff;--maz-color-primary:#1f8fff;--maz-color-primary-600:#1975d2;--maz-color-primary-700:#145ca3;--maz-color-primary-800:#0e4277;--maz-color-primary-900:#092b4e;--maz-color-primary-contrast:#fff;--maz-color-primary-alpha:#1f8fff99;--maz-color-primary-alpha-20:#1f8fff33;--maz-color-secondary-50:#e2f9f3;--maz-color-secondary-100:#bbf1e3;--maz-color-secondary-200:#95e9d3;--maz-color-secondary-300:#6be1c1;--maz-color-secondary-400:#45d9b1;--maz-color-secondary:#1cce9f;--maz-color-secondary-600:#17ab83;--maz-color-secondary-700:#128768;--maz-color-secondary-800:#0d634c;--maz-color-secondary-900:#083f31;--maz-color-secondary-contrast:#fff;--maz-color-secondary-alpha:#1cce9f99;--maz-color-secondary-alpha-20:#1cce9f33;--maz-color-info-50:#e4f4f7;--maz-color-info-100:#b8e3ea;--maz-color-info-200:#92d3dd;--maz-color-info-300:#67c2d0;--maz-color-info-400:#40b3c4;--maz-color-info:#17a4ba;--maz-color-info-600:#13889a;--maz-color-info-700:#0f6876;--maz-color-info-800:#0b4c56;--maz-color-info-900:#073036;--maz-color-info-contrast:#fff;--maz-color-info-alpha:#17a4ba99;--maz-color-info-alpha-20:#17a4ba33;--maz-color-success-50:#f3f9e6;--maz-color-success-100:#e1f0c1;--maz-color-success-200:#cee79d;--maz-color-success-300:#bedf7c;--maz-color-success-400:#acd657;--maz-color-success:#99cd32;--maz-color-success-600:#7ea829;--maz-color-success-700:#658722;--maz-color-success-800:#496218;--maz-color-success-900:#2e3e0f;--maz-color-success-contrast:#fff;--maz-color-success-alpha:#99cd3299;--maz-color-success-alpha-20:#99cd3233;--maz-color-warning-50:#fff7e6;--maz-color-warning-100:#feeac3;--maz-color-warning-200:#fede9f;--maz-color-warning-300:#fdd077;--maz-color-warning-400:#fdc553;--maz-color-warning:#fcb831;--maz-color-warning-600:#d29928;--maz-color-warning-700:#a27720;--maz-color-warning-800:#785817;--maz-color-warning-900:#4d380f;--maz-color-warning-contrast:#384252;--maz-color-warning-alpha:#fcb83199;--maz-color-warning-alpha-20:#fcb83133;--maz-color-danger-50:#ffebeb;--maz-color-danger-100:#ffd2d1;--maz-color-danger-200:#ffbab8;--maz-color-danger-300:#ffa09e;--maz-color-danger-400:#ff8785;--maz-color-danger:#ff6e6b;--maz-color-danger-600:#d25856;--maz-color-danger-700:#a54745;--maz-color-danger-800:#7b3332;--maz-color-danger-900:#4c2020;--maz-color-danger-contrast:#fff;--maz-color-danger-alpha:#ff6e6b99;--maz-color-danger-alpha-20:#ff6e6b33;--maz-color-white:#fff;--maz-color-white-contrast:#000;--maz-color-black:#000;--maz-color-black-contrast:#fff;--maz-color-text-light:#d9d9d9;--maz-color-muted-light:#0000008a;--maz-color-text-dark:#212427;--maz-color-muted-dark:#e3e3e38a;--maz-bg-overlay:#00000080;--maz-bg-color-light:#fff;--maz-bg-color-light-light:#f0f0f0;--maz-bg-color-light-lighter:#f2f2f2;--maz-bg-color-dark:#20212c;--maz-bg-color-dark-light:#313245;--maz-bg-color-dark-lighter:#3b3c54;--maz-border-width:0.125rem;--maz-border-radius:0.5rem;--maz-font-family:system-ui,-apple-system,blinkmacsystemfont,"Segoe UI",roboto,oxygen,ubuntu,cantarell,"Fira Sans","Droid Sans","Helvetica Neue",sans-serif;--maz-color-text:var(--maz-color-text-dark);--maz-color-muted:var(--maz-color-muted-light);--maz-color-bg:var(--maz-bg-color-light);--maz-color-bg-light:var(--maz-bg-color-light-light);--maz-color-bg-lighter:var(--maz-bg-color-light-lighter)}.dark{--maz-color-text:var(--maz-color-text-light);--maz-color-muted:var(--maz-color-muted-dark);--maz-color-bg:var(--maz-bg-color-dark);--maz-color-bg-light:var(--maz-bg-color-dark-light);--maz-color-bg-lighter:var(--maz-bg-color-dark-lighter)}&:where(.m-phone-number-input){--maz-container-padding-mobile:1rem;--maz-container-padding-tablet:2rem;--maz-container-padding-desktop:2.5rem;--maz-container-padding-value:var(--maz-container-padding-mobile);--maz-container-width:calc(100% - var(--maz-container-padding-value)*2);--maz-container-padding:calc(50% - var(--maz-container-width)/2);--maz-container-max-width:var(--maz-screen-lap-xl)}@media only screen and (min-width:1520px){&:where(.m-phone-number-input){--maz-container-width:var(--maz-container-max-width)}}@media (min-width:576px){&:where(.m-phone-number-input){--maz-container-padding-value:var(--maz-container-padding-tablet)}}@media (min-width:1024px){&:where(.m-phone-number-input){--maz-container-padding-value:var(--maz-container-padding-desktop)}}.maz-scale-enter-active,.maz-scale-leave-active{opacity:1;z-index:1;-webkit-transition:all .3s cubic-bezier(.4,.52,.26,.9);transition:all .3s cubic-bezier(.4,.52,.26,.9)}.maz-scale-enter-from,.maz-scale-leave-to{opacity:.4;z-index:1;-webkit-transform:scale(0);transform:scale(0)}.maz-scale-fade-enter-active,.maz-scale-fade-leave-active{opacity:1;z-index:1;-webkit-transition:all .2s cubic-bezier(.4,.52,.26,.9);transition:all .2s cubic-bezier(.4,.52,.26,.9)}.maz-scale-fade-enter-from,.maz-scale-fade-leave-to{opacity:0;z-index:1;-webkit-transform:scale(.5);transform:scale(.5)}.maz-scale-loading-enter-active,.maz-scale-loading-enter-active .app-loader__content,.maz-scale-loading-leave-active,.maz-scale-loading-leave-active .app-loader__content{-webkit-transition:all .5s cubic-bezier(.4,.52,.26,.9);transition:all .5s cubic-bezier(.4,.52,.26,.9);opacity:1}.maz-scale-loading-enter-active .app-loader__content,.maz-scale-loading-leave-active .app-loader__content{z-index:1;-webkit-transform:scale(1);transform:scale(1)}.maz-scale-loading-enter-from .app-loader__content,.maz-scale-loading-leave-to .app-loader__content{opacity:.4;z-index:1;-webkit-transform:scale(0);transform:scale(0)}.maz-scale-loading-enter-from,.maz-scale-loading-leave-to{-webkit-transition:all .5s cubic-bezier(.4,.52,.26,.9);transition:all .5s cubic-bezier(.4,.52,.26,.9);opacity:0}.maz-slidevnext-enter-active,.maz-slidevnext-leave-active,.maz-slidevprev-enter-active,.maz-slidevprev-leave-active{position:absolute;-webkit-transition:all .3s;transition:all .3s}.maz-slidevnext-enter-from,.maz-slidevprev-leave-to{-webkit-transform:translateY(100%);transform:translateY(100%);opacity:0}.maz-slidevnext-leave-to,.maz-slidevprev-enter-from{-webkit-transform:translateY(-100%);transform:translateY(-100%);opacity:0}.maz-slide-enter-active,.maz-slide-leave-active{opacity:1;z-index:998;-webkit-transition:all .3s ease-in-out;transition:all .3s ease-in-out;-webkit-transform:translateY(0);transform:translateY(0)}.maz-slide-enter-from,.maz-slide-leave-to{opacity:0;z-index:998;-webkit-transform:translateY(-20px);transform:translateY(-20px)}.maz-slideinvert-enter-active,.maz-slideinvert-leave-active{opacity:1;z-index:998;-webkit-transition:all .3s ease-in-out;transition:all .3s ease-in-out;-webkit-transform:translateY(0);transform:translateY(0)}.maz-slideinvert-enter-from,.maz-slideinvert-leave-to{opacity:0;z-index:998;-webkit-transform:translateY(40px);transform:translateY(40px)}.maz-tags-enter-active,.maz-tags-leave-active{opacity:1;-webkit-transition:opacity .3s,-webkit-transform .3s;transition:opacity .3s,-webkit-transform .3s;transition:transform .3s,opacity .3s;transition:transform .3s,opacity .3s,-webkit-transform .3s;-webkit-transform:translateX(0);transform:translateX(0)}.maz-tags-enter-from,.maz-tags-leave-to{opacity:0;-webkit-transform:translateX(-100%);transform:translateX(-100%)}.maz-tags-invert-enter-active,.maz-tags-invert-leave-active{opacity:1;-webkit-transition:opacity .3s,-webkit-transform .3s;transition:opacity .3s,-webkit-transform .3s;transition:transform .3s,opacity .3s;transition:transform .3s,opacity .3s,-webkit-transform .3s;-webkit-transform:translateX(0);transform:translateX(0)}.maz-tags-invert-enter-from,.maz-tags-invert-leave-to{opacity:0;-webkit-transform:translateX(100%);transform:translateX(100%)}.maz-expand-enter-active,.maz-expand-leave-active{-webkit-transition:all .3s ease-in-out;transition:all .3s ease-in-out;-webkit-transition-property:opacity,height;transition-property:opacity,height;overflow:hidden}.maz-expand-enter-from,.maz-expand-leave-to{height:0;opacity:0}.maz-tab-transition-enter-from{-webkit-transform:translate(100%);transform:translate(100%)}.maz-tab-transition-leave,.maz-tab-transition-leave-active{position:absolute!important;top:0}.maz-tab-transition-leave-to{position:absolute!important}.maz-tab-reverse-transition-enter-from,.maz-tab-transition-leave-to{-webkit-transform:translate(-100%);transform:translate(-100%)}.maz-tab-reverse-transition-leave,.maz-tab-reverse-transition-leave-to{top:0;position:absolute!important;-webkit-transform:translate(100%);transform:translate(100%)}*,:after,:before{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:#3b82f680;--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::-webkit-backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:#3b82f680;--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:#3b82f680;--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }&:where(.m-phone-number-input){--maz-screen-sm:640px;--maz-screen-md:768px;--maz-screen-lg:1024px;--maz-screen-xl:1280px;--maz-screen-2xl:1536px;--maz-screen-mob-s:320px;--maz-screen-mob-m:425px;--maz-screen-mob-l:576px;--maz-screen-tab-s:640px;--maz-screen-tab-m:768px;--maz-screen-tab-l:992px;--maz-screen-lap-s:1024px;--maz-screen-lap-m:1280px;--maz-screen-lap-l:1366px;--maz-screen-lap-xl:1440px;--maz-screen-lap-2xl:1680px;--maz-screen-lap-3xl:1920px}.maz-relative{position:relative}.\!maz-my-0{margin-top:0!important;margin-bottom:0!important}.maz-my-0{margin-top:0;margin-bottom:0}.maz-my-2{margin-top:.5rem;margin-bottom:.5rem}.maz-ml-1{margin-left:.25rem}.maz-ml-2{margin-left:.5rem}.maz-mr-2{margin-right:.5rem}.maz-flex{display:-webkit-box;display:-ms-flexbox;display:flex}.maz-hidden{display:none}.maz-h-5{height:1.25rem}.maz-h-6{height:1.5rem}.maz-w-10{width:2.5rem}.maz-w-5{width:1.25rem}.maz-w-6{width:1.5rem}.maz-w-full{width:100%}.maz-min-w-0{min-width:0}.maz-max-w-full{max-width:100%}.maz-flex-1{-webkit-box-flex:1;-ms-flex:1 1 0%;flex:1 1 0%}.maz-flex-none{-webkit-box-flex:0;-ms-flex:none;flex:none}.maz-flex-row{-webkit-box-orient:horizontal;-ms-flex-direction:row;flex-direction:row}.maz-flex-col,.maz-flex-row{-webkit-box-direction:normal}.maz-flex-col{-webkit-box-orient:vertical;-ms-flex-direction:column;flex-direction:column}.maz-items-center{-webkit-box-align:center;-ms-flex-align:center;align-items:center}.maz-space-x-2>:not([hidden])~:not([hidden]){--tw-space-x-reverse:0;margin-right:calc(.5rem*var(--tw-space-x-reverse));margin-left:calc(.5rem*(1 - var(--tw-space-x-reverse)))}.maz-overflow-hidden{overflow:hidden}.maz-overflow-x-auto{overflow-x:auto}.maz-truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.maz-rounded{border-radius:var(--maz-border-radius)}.maz-rounded-xl{border-radius:.75rem}.maz-rounded-t-xl{border-top-left-radius:.75rem;border-top-right-radius:.75rem}.maz-border-2{border-width:2px}.maz-border-b-2{border-bottom-width:2px}.maz-border-t-2{border-top-width:2px}.maz-border-solid{border-style:solid}.maz-border-black{border-color:var(--maz-color-black)}.maz-border-color-lighter{border-color:var(--maz-color-bg-lighter)}.maz-border-danger{border-color:var(--maz-color-danger)}.maz-border-info{border-color:var(--maz-color-info)}.maz-border-primary{border-color:var(--maz-color-primary)}.maz-border-secondary{border-color:var(--maz-color-secondary)}.maz-border-success{border-color:var(--maz-color-success)}.maz-border-transparent{border-color:#0000}.maz-border-warning{border-color:var(--maz-color-warning)}.maz-border-white{border-color:var(--maz-color-white)}.maz-bg-color-light{background-color:var(--maz-color-bg-light)}.maz-bg-overlay{background-color:var(--maz-bg-overlay)}.maz-p-2{padding:.5rem}.maz-p-3{padding:.75rem}.maz-p-4{padding:1rem}.maz-py-6{padding-top:1.5rem;padding-bottom:1.5rem}.maz-text-left{text-align:left}.maz-text-right{text-align:right}.maz-text-2xl{font-size:1.5rem;line-height:2rem}.maz-text-sm{font-size:.875rem;line-height:1.25rem}.maz-text-xl{font-size:1.25rem;line-height:1.75rem}.maz-font-semibold{font-weight:600}.maz-text-danger-600{color:var(--maz-color-danger-600)}.maz-text-muted{color:var(--maz-color-muted)}.maz-text-normal{color:var(--maz-color-text)}.maz-text-success-600{color:var(--maz-color-success-600)}.maz-text-warning-600{color:var(--maz-color-warning-600)}.maz-text-white{color:var(--maz-color-white)}.maz-padded-container{width:100%;padding-left:var(--maz-container-padding);padding-right:var(--maz-container-padding)}.maz-elevation{-webkit-box-shadow:0 5px 10px 0 #0000000d;box-shadow:0 5px 10px 0 #0000000d}.maz-flex-center{-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}
}

.aioseo-app .aioseo-phone-number.m-phone-number-input {
	--maz-color-text: #141B38;
	--maz-color-muted: #8C8F9A;
	--maz-color-success-600: #8C8F9A;
	--maz-border-radius: 3px;
	--maz-color-bg: #fff;
	--maz-border-width: 1px;

	max-width: 600px;

	input {
		font-size: 14px;
	}

	.m-input-wrapper {
		border: 1px solid $input-border;
		border-radius: 3px;

		.m-input-label {
			font-size: 14px;
		}
	}

	> div:first-of-type {
		input:focus {
			border: none;
			box-shadow: none;
		}

		.m-input-wrapper {
			border-top: 1px solid $input-border;
			border-bottom: 1px solid $input-border;
			border-left: 1px solid $input-border;
			border-right: none;
			border-radius: 3px;

			.m-input-label {
				font-size: 14px;
			}
		}
	}

	> div:last-of-type.--is-focused {
		z-index: 10;

		> div {
			border: 2px solid $blue;
			border-radius: 3px;
		}

		input {
			border: none;
			box-shadow: none;
		}
	}

	.m-select-list {
		background: #fff !important;
		box-shadow: 0px 4px 26px rgba(44, 50, 76, 0.12);
		border: 1px solid $input-border;
		margin-top: 3px;
		min-width: 275px;
	}

	.m-select {
		.m-phone-number-input__select__item {
			display: flex;
			align-items: center;
			gap: 6px;
				&.--is-selected.--primary {
				background-color: $blue;
				color: $white;
			}
		}

		.m-select-list__search-wrapper {
			padding: 8px;
		}

		.m-select-chevron {
			width: 18px;
			height: 18px;
			stroke: none;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'%3E%3Cpath fill='%23141B38' d='M16.59 8.295 12 12.875l-4.59-4.58L6 9.705l6 6 6-6-1.41-1.41Z'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-size: cover;
		}
	}

	.maz-border-primary {
		border-color: $blue;
	}

	.maz-border-danger {
		border-color: $input-border;
	}

	.--focused {
		.maz-border-danger {
			border-color: $red;
		}
	}
}
</style>