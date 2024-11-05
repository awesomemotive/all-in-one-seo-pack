<template>
	<span :class="[
		'aioseo-writing-assistant__grade-value',
		grade ? 'grade-' + gradeFirstLetter : ''
		]">
		{{ getGrade }}
	</span>
</template>

<script>
export default {
	props : {
		grade : null
	},
	computed : {
		getGrade () {
			return this.grade ? this.grade : '--'
		},
		gradeFirstLetter () {
			return this.grade ? this.grade.toString().charAt(0).toLowerCase() : ''
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__grade-value {
	font-size: 14px;
	line-height: 100%;
	font-weight: 700;
	color: #fff;
	background-color: $gray3;
	padding: 5px 0;
	border-radius: 30px;
	display: inline-block;
	min-width: 33px;
	text-align: center;
	width: auto;
	margin: 0 auto;

	&.grade-a {
		background-color: $green;
	}

	&.grade-b {
		background-color: #a1c628;
	}

	&.grade-c {
		background-color: #cece15;
	}

	&.grade-d {
		background-color: #939362;
	}

	&.grade-e {
		background-color: #6e6e5f;
	}
}
</style>