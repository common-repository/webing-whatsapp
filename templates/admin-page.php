<div class="wrap webing-whatsapp-wrapper">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors( \Webing\WhatsApp\Core\Admin::PAGE_SLUG . '_notices' ); ?>

	<form action="options.php" method="post">

		<?php settings_fields( \Webing\WhatsApp\Core\Admin::PAGE_SLUG ); ?>

		<?php do_settings_sections( \Webing\WhatsApp\Core\Admin::PAGE_SLUG ); ?>

		<?php submit_button( __( 'Save Settings', 'webging-whatsapp' ) ); ?>

	</form>
</div>