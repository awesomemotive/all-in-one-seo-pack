<?php
/**
 * Default Notice Template.
 *
 * @since 3.0
 *
 * @see AIOSEOP_Notice::display_notice_default();
 * @uses $notice in AIOSEOP_Notice::notices
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

// $notice = $this->notices[ $a_notice_slug ];
$notice_class = 'notice-info';
if ( isset( $notice['class'] ) && ! empty( $notice['class'] ) ) {
	$notice_class = $notice['class'];
}

?>
<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible aioseop-notice-container aioseop-notice-<?php echo esc_attr( $notice['slug'] ); ?>">
	<p><?php echo esc_html( $notice['message'] ); ?></p>
	<p>
		<?php foreach ( $notice['action_options'] as $key => $action_option ) : ?>
			<?php
			$link   = $action_option['link'];
			$id     = 'aioseop-notice-delay-' . $notice['slug'] . '-' . $key;
			$class  = '';
			$class .= 'aioseop-delay-' . $key;
			$class .= ' ' . $action_option['class'];
			?>
			<a href="<?php echo esc_url( $link ); ?>" id="<?php echo esc_attr( $id ); ?>" class="aioseop-notice-delay <?php echo esc_attr( $class ); ?>" target="_blank" rel="noopener"><?php echo esc_textarea( $action_option['text'] ); ?></a>
		<?php endforeach; ?>
	</p>
</div>
