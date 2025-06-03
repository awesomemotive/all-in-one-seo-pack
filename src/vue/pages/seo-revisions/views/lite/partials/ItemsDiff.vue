<template>
	<div class="aioseo-seo-revisions-item-diff">
		<h3>SEO Title</h3>
		<div>
			<table class="diff is-split-view">
				<tbody>
				<tr>
					<td class="diff-deletedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-minus"
						/>
						<span class="tag">Post Title</span>
						<span class="tag">Separator</span>
						<del>
							<span class="tag">Site Title</span>
						</del>
					</td>
					<td class="diff-addedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-plus"
						/>
						<span class="tag">Post Title</span>
						<span class="tag">Separator</span>
						<ins>
							<span class="tag">Tagline</span>
						</ins>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<h3>SEO Description</h3>
		<div>
			<table class="diff is-split-view">
				<tbody>
				<tr>
					<td class="diff-deletedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-minus"
						/>
						<del>
							<span>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</span>
						</del>
					</td>
					<td class="diff-addedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-plus"
						/>
						<ins>
							<span>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</span>
						</ins>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<h3>Focus Keyword</h3>
		<div>
			<table class="diff is-split-view">
				<tbody>
				<tr>
					<td class="diff-deletedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-minus"
						/>
						<del>
							<span>Apple Pie</span>
						</del>
					</td>
					<td class="diff-addedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-plus"
						/>
						<ins>
							<span>Apple Pie</span>
						</ins>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<h3>Additional Keywords</h3>
		<div>
			<table class="diff is-split-view">
				<tbody>
				<tr>
					<td class="diff-deletedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-minus"
						/>
						<del>
							<span>Assistance</span>
						</del>
						<br>
						<del>
							<span>Explanation</span>
						</del>
						<br>
						<del>
							<span>Inspector</span>
						</del>
						<br>
						<del>
							<span>Championship</span>
						</del>
						<br>
						<span>Smile</span>
					</td>
					<td class="diff-addedline">
						<span
							aria-hidden="true"
							class="dashicons dashicons-plus"
						/>
						<ins>
							<span>Perspective</span>
						</ins>
						<br>
						<ins>
							<span>Family</span>
						</ins>
						<br>
						<ins>
							<span>Priority</span>
						</ins>
						<br>
						<ins>
							<span>Independence</span>
						</ins>
						<br>
						<span>Smile</span>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<style lang="scss" scoped>
.aioseo-seo-revisions-item-diff {
	font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", sans-serif;

	h3 {
		margin: 0 0 15px 0;

		~ h3 {
			border-top: 1px solid $border;
			margin-top: 20px;
			padding-top: 40px;
		}
	}

	.diff td {
		font-family: inherit;

		.tag {
			background-color: $background;
			border-radius: 4px;
			border: 1px solid $input-border;
			color: $black;
			display: inline-block;
			font-size: 12px;
			font-weight: 700;
			line-height: normal;
			margin-right: 5px;
			padding: 3px 6px;
		}

		del {
			background-color: #FECACA;

			> .tag {
				background-color: #FECACA;
				border: 1px solid #F87171;
			}
		}

		ins {
			background-color: #A7F3D0;

			> .tag {
				background-color: #A7F3D0;
				border: 1px solid #34D399;
			}
		}

		del,
		ins {
			&:has(.tag) {
				background-color: transparent;
			}
		}
	}
}
</style>