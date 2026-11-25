<template>
	<div class="aioseo-attributes">
		<div class="aioseo-description">{{ description }}</div>
		<ul
			class="aioseo-attributes-list"
		>
			<li
				v-for="(attribute, index) in attributes"
				:key="index"
			>
				<div>
					<code>{{ attribute.name }}</code>
				</div>

				<div>
					<span
						class="aioseo-description"
						v-safe-html="attribute.description"
					/>
				</div>
			</li>
		</ul>
	</div>
</template>

<script>
export default {
	props : {
		description : {
			type : String
		},
		attributes : {
			type     : Array,
			required : true
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-attributes {
	ul.aioseo-attributes-list {
		list-style: inside;
		list-style-type: disc;
		margin: 20px 0 0;
		li {
			display: flex;
			margin-bottom: 8px;
			align-items: flex-start;
			div:first-of-type {
				flex: 0 0 140px;
			}
		}
	}
}
</style>