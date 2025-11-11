import { defineStore } from 'pinia'

export const identifiersDefaults = {
	sku  : '',
	gtin : '',
	mpn  : '',
	isbn : ''
}

export const offerDefaults = {
	price        : '',
	currency     : '',
	availability : '',
	validUntil   : ''
}

export const audienceDefaults = {
	gender     : 'Unisex',
	minimumAge : '',
	maximumAge : ''
}

export const ratingDefauls = {
	minimum : 1,
	maximum : 5
}

export const reviewDefaults  = {
	headline : '',
	content  : '',
	author   : ''
}

export const attributeDefaults  = {
	material     : '',
	color        : '',
	pattern      : '',
	size         : '',
	energyRating : ''
}

export const shippingDestinationDefaults = {
	type         : 'country',
	country      : '',
	postalCodes  : [],
	states       : [],
	rate         : 0,
	deliveryTime : {
		handlingTime : {
			minValue : '',
			maxValue : ''
		},
		transitTime : {
			minValue : '',
			maxValue : ''
		}
	}
}

export const useProductStore = defineStore('ProductStore', {
	state : () => ({
		blockClientId        : null,
		isSelected           : false,
		autogenerate         : true,
		canRemove            : true,
		name                 : '#post_title',
		description          : '',
		brand                : '',
		image                : '',
		identifiers          : JSON.stringify(identifiersDefaults),
		offer                : JSON.stringify(offerDefaults),
		attributes           : JSON.stringify(attributeDefaults),
		audience             : JSON.stringify(audienceDefaults),
		rating               : JSON.stringify(ratingDefauls),
		reviews              : JSON.stringify([ reviewDefaults ]),
		shippingDestinations : JSON.stringify([ shippingDestinationDefaults ])
	})
})