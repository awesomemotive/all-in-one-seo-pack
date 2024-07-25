<template>
    <div class="aioseo-headline-analyzer-words-block">
        <h5>{{ title }}</h5>
        <div class="aioseo-headline-analyzer-words-block-data">
            <span
                class='aioseo-headline-analyzer-words-block-percentage'
                :class="classOnScore"
            >
                {{ value }}%
            </span>
            <span class='aioseo-headline-analyzer-words-block-percentage-right-column'
                :class="classOnScore"
            >
                <span class="aioseo-headline-analyzer-words-block-goal">
                    {{ textGoal }} {{ goalValue }}
                </span>
                <span class="aioseo-headline-analyzer-words-block-progressbar">
                    <span class="aioseo-headline-analyzer-progressbar-bg"></span>
                    <span
                        class='aioseo-headline-analyzer-progressbar-part'
                        :class="classOnScoreBg"
                        :style="progressBarStyle"
                    ></span>
                </span>
            </span>
        </div>
        <ul class="aioseo-headline-analyzer-words-tag-list">
            <li
                v-for="(word, index) in words"
                :key="index"
            >
                {{ word }}
            </li>
        </ul>
        <p class="aioseo-headline-analyzer-words-guideline">
            {{ guideLine }}
        </p>
    </div>
</template>

<script>
export default {
	data () {
		return {
			textGoal : this.$t.__('Goal:', this.$td)
		}
	},
	props : {
		title : {
			type     : String,
			default  : '',
			required : true
		},
		value : {
			type     : Number,
			required : true
		},
		classOnScore : {
			type     : String,
			default  : '',
			required : true
		},
		classOnScoreBg : {
			type     : String,
			default  : '',
			required : true
		},
		goalValue : {
			type     : String,
			default  : '',
			required : true
		},
		words : {
			type    : Array,
			default : () => []
		},
		guideLine : {
			type    : String,
			default : ''
		}
	},
	computed : {
		progressBarStyle () {
			return `width: ${this.value}%`
		}
	}
}
</script>