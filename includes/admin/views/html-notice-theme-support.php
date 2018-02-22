<?php
/**
 * Admin View: Notice - Theme Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated geodirectory-message gd-connect">
	<a class="geodirectory-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'theme_support' ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'geodirectory' ); ?></a>

	<p><?php printf( __( '<strong>Your theme does not declare GeoDirectory support</strong> &#8211; Please read our <a href="%1$s" target="_blank">integration</a> guide or check out our <a href="%2$s" target="_blank">Storefront</a> theme which is totally free to download and designed specifically for use with GeoDirectory.', 'geodirectory' ), esc_url( apply_filters( 'geodir_docs_url', 'https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/', 'theme-compatibility' ) ), esc_url( admin_url( 'theme-install.php?theme=storefront' ) ) ); ?></p>
	<p class="submit">
		<a href="https://woocommerce.com/storefront/?utm_source=notice&amp;utm_medium=product&amp;utm_content=storefront&amp;utm_campaign=woocommerceplugin" class="button-primary" target="_blank"><?php _e( 'Read more about Storefront', 'geodirectory' ); ?></a>
		<a href="<?php echo esc_url( apply_filters( 'geodir_docs_url', 'http://docs.woocommerce.com/document/third-party-custom-theme-compatibility/?utm_source=notice&utm_medium=product&utm_content=themecompatibility&utm_campaign=woocommerceplugin' ) ); ?>" class="button-secondary" target="_blank"><?php _e( 'Theme integration guide', 'geodirectory' ); ?></a>
	</p>
</div>
