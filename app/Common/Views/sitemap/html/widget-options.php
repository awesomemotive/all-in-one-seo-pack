<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="aioseo-html-sitemap">
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="aioseo-title">
			<?php esc_html_e( 'Title', 'all-in-one-seo-pack' ); ?>
		</label>
		<input
			type="text"
			id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			value="<?php echo esc_attr( $instance['title'] ); ?>"
			class="widefat"
		/>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'archives' ) ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $this->get_field_id( 'archives' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'archives' ) ); ?>"
				<?php
				if ( 'on' === $instance['archives'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
			<?php esc_html_e( 'Compact Archives', 'all-in-one-seo-pack' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'show_label' ) ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $this->get_field_id( 'show_label' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'show_label' ) ); ?>"
				<?php
				if ( 'on' === $instance['show_label'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
			<?php esc_html_e( 'Show Labels', 'all-in-one-seo-pack' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'publication_date' ) ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $this->get_field_id( 'publication_date' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'publication_date' ) ); ?>"
				<?php
				if ( 'on' === $instance['publication_date'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
			<?php esc_html_e( 'Show Publication Date', 'all-in-one-seo-pack' ); ?>
		</label>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'post_types' ) ); ?>" class="aioseo-title">
		<?php esc_html_e( 'Post Types', 'all-in-one-seo-pack' ); ?>
		</label>

		<div class="aioseo-columns">
			<?php foreach ( $postTypeObjects as $i => $postTypeObject ) : ?>
			<div>
				<label>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $this->get_field_name( 'post_types' ) ); ?>[]"
						id="<?php echo esc_attr( $this->get_field_id( 'post_types' . $i ) ); ?>"
						<?php checked( in_array( $postTypeObject['name'], $instance['post_types'], true ) ); ?>
						value="<?php echo esc_html( $i ); ?>"
					/>
					<?php echo esc_html( $postTypeObject['label'] ); ?>
				</label>
			</div>
			<?php endforeach ?>
		</div>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomies' ) ); ?>" class="aioseo-title">
		<?php esc_html_e( 'Taxonomies', 'all-in-one-seo-pack' ); ?>
		</label>

		<div class="aioseo-columns">
			<?php foreach ( $taxonomyObjects as $i => $taxonomyObject ) : ?>
			<div>
				<label>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $this->get_field_name( 'taxonomies' ) ); ?>[]"
						id="<?php echo esc_attr( $this->get_field_id( 'taxonomies' . $i ) ); ?>"
						<?php checked( in_array( $taxonomyObject['name'], $instance['taxonomies'], true ) ); ?>
						value="<?php echo esc_html( $i ); ?>"
					/>
					<?php echo esc_html( $taxonomyObject['label'] ); ?>
				</label>
			</div>
			<?php endforeach ?>
		</div>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>" class="aioseo-title">
			<?php esc_html_e( 'Sort Order', 'all-in-one-seo-pack' ); ?>
		</label>
		<select name="<?php echo esc_attr( $this->get_field_name( 'order_by' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>" class="widefat">
			<option value="publish_date"<?php selected( 'publish_date', $instance['order_by'], true ); ?>>
				<?php esc_html_e( 'Publish Date', 'all-in-one-seo-pack' ); ?>
			</option>
			<option value="last_updated"<?php selected( 'last_updated', $instance['order_by'], true ); ?>>
				<?php esc_html_e( 'Last Updated', 'all-in-one-seo-pack' ); ?>
			</option>
			<option value="alphabetical"<?php selected( 'alphabetical', $instance['order_by'], true ); ?>>
				<?php esc_html_e( 'Alphabetical', 'all-in-one-seo-pack' ); ?>
			</option>
			<option value="id"<?php selected( 'id', $instance['order_by'], true ); ?>>
				<?php esc_html_e( 'ID', 'all-in-one-seo-pack' ); ?>
			</option>
		</select>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" class="aioseo-title">
			<?php esc_html_e( 'Sort Direction', 'all-in-one-seo-pack' ); ?>
		</label>
		<select name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" class="widefat">
			<option value="asc"<?php echo ( 'asc' === $instance['order'] ) ? ' selected="selected"' : '' ?>><?php esc_html_e( 'Ascending', 'all-in-one-seo-pack' ); ?></option>
			<option value="desc"<?php echo ( 'desc' === $instance['order'] ) ? ' selected="selected"' : '' ?>"><?php esc_html_e( 'Descending', 'all-in-one-seo-pack' ); ?></option>
		</select>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'excluded_posts' ) ); ?>" class="aioseo-title">
			<?php esc_html_e( 'Exclude Posts / Pages', 'all-in-one-seo-pack' ); ?>
		</label>
		<input
			type="text"
			value="<?php echo esc_attr( $instance['excluded_posts'] ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'excluded_posts' ) ); ?>"
			id="<?php echo esc_attr( $this->get_field_id( 'excluded_posts' ) ); ?>"
			class="widefat"
		/>
		<p class="aioseo-description"><?php esc_html_e( 'Enter a comma-separated list of post IDs.', 'all-in-one-seo-pack' ); ?></p>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'excluded_terms' ) ); ?>" class="aioseo-title">
			<?php esc_html_e( 'Exclude Terms', 'all-in-one-seo-pack' ); ?>
		</label>
		<input
			type="text"
			value="<?php echo esc_attr( $instance['excluded_terms'] ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'excluded_terms' ) ); ?>"
			id="<?php echo esc_attr( $this->get_field_id( 'excluded_terms' ) ); ?>"
			class="widefat"
		/>
		<p class="aioseo-description"><?php esc_html_e( 'Enter a comma-separated list of term IDs.', 'all-in-one-seo-pack' ); ?></p>
	</p>
</div>

<style>
	.aioseo-html-sitemap label.aioseo-title,
	.aioseo-html-sitemap label.aioseo-title select {
		color: #141B38 !important;
		font-weight: bold !important;
	}
	.aioseo-html-sitemap .aioseo-description {
		margin-top: -5px;
		font-style: italic;
		font-size: 13px;
	}
	.aioseo-html-sitemap select, .aioseo-html-sitemap input[type=text] {
		margin-top: 8px;
	}
	.aioseo-html-sitemap .aioseo-columns {
		display: flex;
		flex-wrap: wrap;
	}
	.aioseo-html-sitemap .aioseo-columns div {
		flex: 0 0 50%;
	}
</style>