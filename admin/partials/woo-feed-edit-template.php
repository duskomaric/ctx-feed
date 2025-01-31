<?php
/**
 * Common Feed Editing Template
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
/** @define "WOO_FEED_PRO_ADMIN_PATH" "./../" */
/**
 * globals
 *
 * @global array $feedRules
 * @global Woo_Feed_Dropdown_Pro $wooFeedDropDown
 * @global Woo_Feed_Merchant_Pro $merchant
 * @global string $feedName
 * @global int $feedId
 * @global string $provider
 * @global array $wp_meta_boxes
 */
global $feedRules, $wooFeedDropDown, $merchant, $feedName, $feedId, $provider, $wp_meta_boxes;
$wf_current_screen = get_current_screen();
$wf_page           = $wf_current_screen->id;
$wooFeedDropDown   = new Woo_Feed_Dropdown_Pro();
// Condition is for those merchants which support another merchant feed requirements.
$feedRules = woo_feed_parse_feed_rules( $feedRules );
woo_feed_register_and_do_woo_feed_meta_boxes( $wf_current_screen, $feedRules );
?>
<!--suppress SpellCheckingInspection, HtmlFormInputWithoutLabel, HtmlDeprecatedAttribute -->
<div class="wrap wapk-admin" id="Feed">
	<div class="wapk-section">
		<h2><?php esc_html_e( 'Edit WooCommerce Product Feed', 'woo-feed' ); ?></h2>
	</div>
	<div class="wapk-section"><?php WPFFWMessage()->displayMessages(); ?></div>
	<div class="wapk-section">
		<form action="" name="feed" id="updatefeed" class="generateFeed" method="post" autocomplete="off">
			<input type="hidden" name="feed_option_name" value="<?php echo esc_attr( str_replace( array( 'wf_feed_', 'wf_config' ), '', $feedName ) ); ?>">
			<input type="hidden" name="feed_id" value="<?php echo esc_attr( $feedId ); ?>">
			<?php
			wp_nonce_field( 'wf_edit_feed' );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
					<div id="post-body-content">
						<?php require_once WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-content-settings.php'; ?>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( get_current_screen(), 'side', $feedRules ); ?>
					</div>
				</div>
				<div class="clear"></div>
				<?php require_once WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-edit-tabs.php'; ?>
			</div>
		</form>
	</div>
</div>
