<?php
/**
 * globals
 *
 * @global array $feedRules
 * @global Woo_Feed_Dropdown_Pro $wooFeedDropDown
 * @global Woo_Feed_Merchant_Pro $merchant
 * @global string $provider
 * @global string $feedName
 * @global int $feedId
 * @global array $wp_meta_boxes
 */
global $feedRules, $wooFeedDropDown, $merchant, $provider;
$editorTabs = array(
	'config' => array(
		'label'    => __( 'Feed Config', 'woo-feed' ),
		'callback' => 'render_feed_config',
	),
	'filter' => array(
		'label'    => __( 'Filter', 'woo-feed' ),
		'callback' => 'render_filter_config',
	)
);

$settings = woo_feed_get_options('all');
if( isset($settings['enable_ftp_upload']) && 'yes' === $settings['enable_ftp_upload'] ) {
    $editorTabs['ftp'] = array(
        'label'    => __( 'FTP/SFTP', 'woo-feed' ),
        'callback' => 'render_ftp_config',
    );
}

$editorTabs = apply_filters( 'woo_feed_editor_tabs', $editorTabs );
$isEdit     = defined( 'WOO_FEED_EDIT_CONFIG' ) && WOO_FEED_EDIT_CONFIG;
$tab_name = '';
?>
<div id="providerPageWrapper">
    <ul class="wf_tabs">
	<?php
	foreach ( $editorTabs as $tabId => $tabConfig ) {
		if ( ! isset( $tabConfig['label'], $tabConfig['callback'] ) ) {
			continue;
		}
		if ( ! is_callable( $tabConfig['callback'] ) ) {
			continue;
		}
		$isActivated = 'config' === $tabId ? 'activate' : '';
		$tab_name .= '<div class="tab-name"><input type="radio" name="wf_tabs" id="tab-' . esc_attr( $tabId ) . '" ' . checked( 'config', $tabId, false ) . '>
                <label class="wf-tab-name ' . $isActivated . '" for="wf-tab-content-' . esc_attr( $tabId ) . '">' . esc_html( $tabConfig['label'] ) . '</label></div>';
		?>
		<li class="<?php echo 'config' === $tabId ? 'active' : ''; ?>">
			<div id="wf-tab-content-<?php echo esc_attr( $tabId ); ?>" class="wf-tab-content">
				<?php
				/**
				 * before tab content
				 *
				 * @param string $tabId
				 * @param array $feedRules
				 */
				do_action( 'woo_feed_editor_tab_before_content', $tabId, $feedRules );
				/**
				 * before tab content
				 *
				 * @param array $feedRules
				 */
				do_action( "woo_feed_editor_tab_before_{$tabId}_content", $feedRules );
				/**
				 * Call the render callback for tab
				 *
				 * @param string $tabId
				 * @param array $feedRules
				 * @param bool  $isEdit
				 */
				call_user_func_array(
					$tabConfig['callback'],
					array(
						$tabId, // tab id
						$feedRules, // feed config/rules
						$isEdit, // is edit mode
					)
				);
				/**
				 * after tab content
				 *
				 * @param string $tabId
				 * @param array $feedRules
				 */
				do_action( 'woo_feed_editor_tab_after_content', $tabId, $feedRules );
				/**
				 * after tab content
				 *
				 * @param array $feedRules
				 */
				do_action( "woo_feed_editor_tab_after_{$tabId}_content", $feedRules );
				?>
				<table class="feed-actions widefat fixed">
					<tr>
						<td class=''>
							<div class="makeFeedResponse"></div>
							<div class="makeFeedComplete"></div>
						</td>
						<td>
							<?php if ( defined( 'WOO_FEED_EDIT_CONFIG' ) && WOO_FEED_EDIT_CONFIG ) { ?>
							<button name="save_feed_config" type="submit" class="wfbtn updatefeed woo-feed-btn-bg-gradient-blue"><?php esc_html_e( 'Save', 'woo-feed' ); ?></button>
							<?php } ?>
							<button name="<?php echo isset( $_GET['action'] ) ? esc_attr( sanitize_text_field( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" type="submit" class="wfbtn updatefeed woo-feed-btn-bg-gradient-blue"><?php esc_html_e( 'Update and Generate Feed', 'woo-feed' ); ?></button>
						</td>
					</tr>
				</table>
			</div>
		</li>
		<?php
	}
	?>
</ul>
    <div id="tabName"><?php echo $tab_name; ?></div>
</div>
