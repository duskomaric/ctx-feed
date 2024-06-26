<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnusedParameterInspection */
/**
 * Pro Helper Functions
 *
 * @package    WooFeed
 * @subpackage WooFeed_Notice_Helper_Functions
 * @since      WooFeed 7.0.16
 * @version    7.0.17
 * @author     Nashir Uddin <nashirbabu@gmail.com>
 * @copyright  WebAppick
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
/** @define "WOO_FEED_PRO_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

if ( ! function_exists( 'woo_feed_christmas_lifetime_notice' ) ) {
	/**
	 * CTX Feed Halloween Notice
	 *
	 * @since 7.2.5
	 * @author Nashir Uddin
	 */
	function woo_feed_christmas_lifetime_notice() {
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'woo_feed_christmas_lifetime_notice_2023_dismissed' ) ) {
			ob_start();
			?>
			<script type="text/javascript">
				(function ($) {
					$(document).on('click', '.woo-feed-ctx-startup-notice button.notice-dismiss', function (e) {
						e.preventDefault();
						let nonce = $('#woo_feed_to_ctx_feed_christmas_lifetime_nonce').val();

						//woo feed halloween cancel callback
						wp.ajax.post('woo_feed_save_christmas_lifetime_notice', {
							_wp_ajax_nonce: nonce,
							clicked: true,
						}).then(response => {
							console.log(response);
						}).fail(error => {
							console.log(error);
						});
					});
				})(jQuery)
			</script>
			<a target="_blank" href="https://webappick.com/plugin/woocommerce-product-feed-pro/?utm_source=Christmass_23&utm_medium=Pro_to_Lifetime&utm_campaign=Christmass23&utm_id=23"
			   class="notice woo-feed-ctx-startup-notice is-dismissible"
			   style="background: url(<?php echo WOO_FEED_PRO_ADMIN_URL . 'images/woo-feed-christmas-lifetime-notice.png'; ?>) no-repeat top center;">
				<input type="hidden" id="woo_feed_to_ctx_feed_christmas_lifetime_nonce"
					   value="<?php echo wp_create_nonce( 'woo-feed-to-ctx-feed-christmas-lifetime-nonce' ); ?>">
			</a>
			<?php
			$image = ob_get_contents();
		}
	}
}

if ( ! function_exists( 'woo_feed_save_christmas_lifetime_notice' ) ) {
	/**
	 * Update user meta to work ctx startup notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since 7.2.5
	 * @author Nashir Uddin
	 */
	function woo_feed_save_christmas_lifetime_notice() {
		if ( isset( $_REQUEST['_wp_ajax_nonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wp_ajax_nonce'] ), 'woo-feed-to-ctx-feed-christmas-lifetime-nonce' ) ) { //phpcs:ignore
			$user_id = get_current_user_id();
			if ( isset( $_REQUEST['clicked'] ) ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_feed_christmas_lifetime_notice_2023_dismissed', 'true', true );

				if ( $updated_user_meta ) {
					wp_send_json_success( esc_html__( 'User meta updated successfully.', 'woo-feed' ) );
				} else {
					wp_send_json_error( esc_html__( 'Something is wrong.', 'woo-feed' ) );
				}
			}
		} else {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		}
		wp_die();
	}
}
add_action( 'wp_ajax_woo_feed_save_christmas_lifetime_notice', 'woo_feed_save_christmas_lifetime_notice' );

if ( ! function_exists( 'woo_feed_halloween_lifetime_notice' ) ) {
	/**
	 * CTX Feed Halloween Notice
	 *
	 * @since 4.5.3
	 * @author Nashir Uddin
	 */
	function woo_feed_halloween_lifetime_notice() {
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'woo_feed_halloween_lifetime_notice_2023_dismissed' ) ) {
			ob_start();
			?>
			<script type="text/javascript">
				(function ($) {
					$(document).on('click', '.woo-feed-ctx-halloween-notice button.notice-dismiss', function (e) {
						e.preventDefault();
						let nonce = $('#woo_feed_to_ctx_feed_halloween_lifetime_nonce').val();

						//woo feed halloween cancel callback
						wp.ajax.post('woo_feed_save_halloween_lifetime_notice', {
							_wp_ajax_nonce: nonce,
							clicked: true,
						}).then(response => {
							console.log(response);
						}).fail(error => {
							console.log(error);
						});
					});
				})(jQuery)
			</script>
			<a target="_blank" href="https://webappick.com/plugin/woocommerce-product-feed-pro/?utm_source=HW_Banner_1b&utm_medium=HW_Banner_pro_to_lifetime&utm_campaign=HWbanner23&utm_id=1"
			   class="notice woo-feed-ctx-halloween-notice is-dismissible"
			   style="background: url(<?php echo WOO_FEED_PRO_ADMIN_URL . 'images/woo-feed-halloween-lifetime-notice.png'; ?>) no-repeat top center;">
				<input type="hidden" id="woo_feed_to_ctx_feed_halloween_lifetime_nonce"
				       value="<?php echo wp_create_nonce( 'woo-feed-to-ctx-feed-halloween-lifetime-nonce' ); ?>">
			</a>
			<?php
			$image = ob_get_contents();
		}
	}
}

if ( ! function_exists( 'woo_feed_save_halloween_lifetime_notice' ) ) {
	/**
	 * Update user meta to work ctx startup notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since 4.5.3
	 * @author Nashir Uddin
	 */
	function woo_feed_save_halloween_lifetime_notice() {
		if ( isset( $_REQUEST['_wp_ajax_nonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wp_ajax_nonce'] ), 'woo-feed-to-ctx-feed-halloween-lifetime-nonce' ) ) { //phpcs:ignore
			$user_id = get_current_user_id();
			if ( isset( $_REQUEST['clicked'] ) ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_feed_halloween_lifetime_notice_2023_dismissed', 'true', true );

				if ( $updated_user_meta ) {
					wp_send_json_success( esc_html__( 'User meta updated successfully.', 'woo-feed' ) );
				} else {
					wp_send_json_error( esc_html__( 'Something is wrong.', 'woo-feed' ) );
				}
			}
		} else {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		}
		wp_die();
	}
}
add_action( 'wp_ajax_woo_feed_save_halloween_lifetime_notice', 'woo_feed_save_halloween_lifetime_notice' );

if ( ! function_exists( 'woo_feed_pro_black_friday_notice' ) ) {
	/**
	 * CTX Feed Pro Black Friday Notice
	 *
	 * @since  5.2.102
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_pro_black_friday_notice() {
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'woo_feed_pro_black_friday_notice_2023_dismissed' ) ) {
			ob_start();
			?>
			<script type="text/javascript">
				(function ($) {
					$(document).on('click', '.woo-feed-pro-black-friday-notice button.notice-dismiss', function (e) {
						e.preventDefault();
						let nonce = $('#woo_feed_pro_notice_nonce').val();

						//woo feed pro black friday notice cancel callback
						wp.ajax.post('woo_feed_pro_save_black_friday_notice_2023', {
							_wp_ajax_nonce: nonce,
							clicked: true,
						}).then(response => {
							console.log(response);
						}).fail(error => {
							console.log(error);
						});
					});
				})(jQuery);
			</script>
			<a target="_blank" href="https://webappick.com/plugin/woocommerce-product-feed-pro/?utm_source=BFCM_banner&utm_medium=BFCM_Banner_pro_to_lifetime&utm_campaign=BFCM23&utm_id=1"
			   class="notice woo-feed-pro-black-friday-notice is-dismissible"
			   style="background: url(<?php echo WOO_FEED_PRO_ADMIN_URL . 'images/ctx-feed-pro-black-friday-banner-2023.png'; ?>) no-repeat top center;">
				<input type="hidden" id="woo_feed_pro_notice_nonce"
					   value="<?php echo wp_create_nonce( 'woo-feed-pro-notice-nonce' ); ?>">
			</a>
			<?php
			$image = ob_get_contents();
		}
	}
}

if ( ! function_exists( 'woo_feed_pro_save_black_friday_notice_2023' ) ) {
	/**
	 * Update user meta to work ctx pro startup notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since  5.2.102
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_pro_save_black_friday_notice_2023() {
		if ( isset( $_REQUEST['_wp_ajax_nonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wp_ajax_nonce'] ), 'woo-feed-pro-notice-nonce' ) ) { //phpcs:ignore
			$user_id = get_current_user_id();
			if ( isset( $_REQUEST['clicked'] ) ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_feed_pro_black_friday_notice_2023_dismissed', 'true', true );

				if ( $updated_user_meta ) {
					wp_send_json_success( esc_html__( 'User meta updated successfully.', 'woo-feed' ) );
				} else {
					wp_send_json_error( esc_html__( 'Something is wrong.', 'woo-feed' ) );
				}
			}
		} else {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		}
		wp_die();
	}
}
add_action( 'wp_ajax_woo_feed_pro_save_black_friday_notice_2023', 'woo_feed_pro_save_black_friday_notice_2023' );
