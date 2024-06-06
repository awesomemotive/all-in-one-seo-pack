<template>
	<div class="aioseo-tab-content aioseo-localseo-info">
		<core-settings-row
			:name="strings.name"
			class="info-name-row"
			align
		>
			<template #content>
				<local-business-name />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessType"
			class="info-business-type"
			align
		>
			<template #content>
				<local-business-business-type />
      </template>
    </core-settings-row>

    <core-settings-row
			class="info-business-image"
			:name="strings.image"
			align
		>
			<template #content>
				<local-business-image />
			</template>
		</core-settings-row>

		<!--core-settings-row
			:name="strings.urls"
			class="info-urls-row"
			align
		>
			<template #content>
				<local-business-urls />
			</template>
		</core-settings-row-->

		<core-settings-row
			:name="strings.businessAddress"
			class="info-businessaddress-row"
			align
		>
			<template #content>
				<local-business-business-address />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.map"
			align
		>
			<template #content>
				<local-business-map />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessContact"
			class="info-businesscontact-row"
			align
		>
			<template #content>
				<local-business-business-contact />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessIDs"
			class="info-businessids-row"
			align
		>
			<template #content>
				<local-business-business-ids />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.paymentInfo"
			class="info-paymentinfo-row"
			align
		>
			<template #content>
				<local-business-payment-info />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.areaServed"
			class="info-area-row"
			align
		>
			<template #content>
				<local-business-area-served />
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import LocalBusinessAreaServed from '@/vue/components/AIOSEO_VERSION/local-business/AreaServed'
import LocalBusinessBusinessAddress from '@/vue/components/AIOSEO_VERSION/local-business/business/Address'
import LocalBusinessBusinessContact from '@/vue/components/AIOSEO_VERSION/local-business/business/Contact'
import LocalBusinessBusinessIds from '@/vue/components/AIOSEO_VERSION/local-business/business/Ids'
import LocalBusinessBusinessType from '@/vue/components/AIOSEO_VERSION/local-business/business/Type'
import LocalBusinessImage from '@/vue/components/AIOSEO_VERSION/local-business/Image'
import LocalBusinessMap from '@/vue/components/AIOSEO_VERSION/local-business/map/Index'
import LocalBusinessName from '@/vue/components/AIOSEO_VERSION/local-business/Name'
import LocalBusinessPaymentInfo from '@/vue/components/AIOSEO_VERSION/local-business/PaymentInfo'
// import LocalBusinessUrls from '@/vue/components/AIOSEO_VERSION/local-business/Urls'
export default {
	components : {
		CoreSettingsRow,
		LocalBusinessAreaServed,
		LocalBusinessBusinessAddress,
		LocalBusinessBusinessContact,
		LocalBusinessBusinessIds,
		LocalBusinessBusinessType,
		LocalBusinessImage,
		LocalBusinessMap,
		LocalBusinessName,
		LocalBusinessPaymentInfo
		// LocalBusinessUrls
	},
	data () {
		return {
			strings : {
				pageName        : this.$t.__('Business Info', this.$td),
				name            : this.$t.__('Name', this.$td),
				businessType    : this.$t.__('Type', this.$td),
				image           : this.$t.__('Image', this.$td),
				urls            : this.$t.__('URLs', this.$td),
				websiteDesc     : this.$t.__('Website URL', this.$td),
				aboutDesc       : this.$t.__('About Page URL', this.$td),
				contactDesc     : this.$t.__('Contact Page URL', this.$td),
				businessAddress : this.$t.__('Address', this.$td),
				businessContact : this.$t.__('Contact Info', this.$td),
				businessIDs     : this.$t.__('IDs', this.$td),
				paymentInfo     : this.$t.__('Payment Info', this.$td),
				areaServed      : this.$t.__('Area Served', this.$td),
				map             : this.$t.__('Map', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-localseo-info {
	.aioseo-input,
	.aioseo-select,
	.aioseo-multiselect {
		max-width: 480px;
	}

	.field-description {
		font-size: 14px;
	}

	.info-businessaddress-row .columns {
		display: flex;
		flex: 0 1 auto;
		flex-direction: row;
		flex-wrap: wrap;
	}

	&.aioseo-tab-content{
		svg {
			&.aioseo-circle-question-mark {
				width: 17px;
				height: 17px;
				color: #8C8F9A;
				transition: background-color 0.2s ease;
			}
		}
	}
}
</style>