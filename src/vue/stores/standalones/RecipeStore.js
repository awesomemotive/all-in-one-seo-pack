import { defineStore } from 'pinia'

export const nutritionDefaults = {
	servings : 1,
	calories : ''
}

export const timeRequiredDefaults = {
	preparation : '',
	cooking     : ''
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

export const useRecipeStore = defineStore('RecipeStore', {
	state : () => ({
		blockClientId : null,
		isSelected    : false,
		canRemove     : true,
		name          : '#post_title',
		description   : '',
		author        : '#author_name',
		ingredients   : '',
		dishType      : '',
		cuisineType   : '',
		keywords      : '',
		image         : '',
		nutrition     : JSON.stringify(nutritionDefaults),
		timeRequired  : JSON.stringify(timeRequiredDefaults),
		rating        : JSON.stringify(ratingDefauls),
		reviews       : JSON.stringify([ reviewDefaults ]),
		instructions  : []
	})
})