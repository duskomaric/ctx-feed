<?php
/**
 * Content Settings Table
 *
 * @package WooFeed
 * @subpackage Editor
 * @version 1.0.0
 * @since WooFeed 3.2.6
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright 2019 WebAppick <support@webappick.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silence
}
/**
 * @global array $feedRules
 * @global Woo_Feed_Dropdown_Pro $wooFeedDropDown
 * @global Woo_Feed_Merchant_Pro $merchant
 */
global $feedRules, $wooFeedDropDown, $merchant;
?>
<table class="widefat fixed">
	<thead>
		<tr>
			<th colspan="2" class="woo-feed-table-heading">
                <span class="woo-feed-table-heading-title"><?php esc_html_e( 'Content Settings', 'woo-feed' ); ?></span>
                <?php woo_feed_clear_cache_button(); ?>
            </th>
		</tr>
	</thead>
	<tbody>
        <tr>
            <th><label for="feed_country"><?php esc_html_e( 'Country', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
            <td>
                <select wftitle="<?php esc_attr_e( 'Select a country', 'woo-feed' ); ?>" name="feed_country" id="feed_country" class="generalInput wfmasterTooltip" required>
                    <?php
                    $shop_country = WC()->countries->get_base_country();
					$default_country = ! empty( $feedRules['feed_country'] ) ? $feedRules['feed_country'] : $shop_country;
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $wooFeedDropDown->countriesDropdown( $default_country );
                    ?>
                </select>
            </td>
        </tr>
		<tr>
			<th><label for="provider"><?php esc_html_e( 'Template', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select wftitle="<?php esc_attr_e( 'Select a template', 'woo-feed' ); ?>" name="provider" id="provider" class="generalInput wfmasterTooltip" required>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $wooFeedDropDown->merchantsDropdown( $feedRules['provider'] );
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="filename"><?php esc_html_e( 'File Name', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="filename" value="<?php echo isset( $feedRules['filename'] ) ? esc_attr( $feedRules['filename'] ) : ''; ?>" type="text" id="filename" class="generalInput wfmasterTooltip" wftitle="<?php esc_attr_e( 'Filename should be unique. Otherwise it will override the existing filename.', 'woo-feed' ); ?>" required>
			</td>
		</tr>
		<tr>
			<th><label for="feedType"><?php esc_html_e( 'File Type', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="feedType" id="feedType" class="generalInput" required>
					<option value=""></option>
					<?php
					foreach ( woo_feed_get_file_types() as $file_type => $label ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $file_type ), esc_html( $label ), selected( $feedRules['feedType'], $file_type, false ) );
					}
					?>
				</select>
				<span class="spinner" style="float: none; margin: 0;"></span>
			</td>
		</tr>
		<tr id="woo_feed_is_variation">
			<th><label for="is_variations"><?php esc_html_e( 'Include Variations?', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="is_variations" id="is_variations" class="WFisVariations generalInput">
					<?php
					foreach ( woo_feed_get_variable_visibility_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['is_variations'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
        <?php
            $variable_dependency='style=display:none;';
            if('both'==$feedRules['is_variations'] || 'n'==$feedRules['is_variations']){
                $variable_dependency='';
            }
        ?>
		<tr class="WFVariablePriceTR" <?php echo esc_attr( $variable_dependency );?> >
			<th><label for="variable_price"><?php esc_html_e( 'Variable Product Price', 'woo-feed' ); ?></label></th>
			<td>
				<select name="variable_price" id="woo_feed_variable_price" class="generalInput">
					<?php
					foreach ( woo_feed_get_variable_price_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['variable_price'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="WFVariablePriceTR" <?php echo esc_attr( $variable_dependency );?>>
			<th><label for="variable_quantity"><?php esc_html_e( 'Variable Product Quantity', 'woo-feed' ); ?></label></th>
			<td>
				<select name="variable_quantity" id="variable_quantity" class="generalInput">
					<?php
					foreach ( woo_feed_get_variable_quantity_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['variable_quantity'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<?php
		$languages = $wooFeedDropDown->getActiveLanguages( $feedRules['feedLanguage'] );
		if ( ! empty( $languages ) ) {
			?>
			<tr>
				<th><label for="feedLanguage"><?php esc_html_e( 'Language', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
				<td>
					<select name="feedLanguage" id="feedLanguage" class="generalInput" required>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $languages;
						?>
					</select>
				</td>
			</tr>
		<?php } ?>
		<?php
		$currencies = $wooFeedDropDown->getActiveCurrencies( $feedRules['feedCurrency'] );
		if ( ! empty( $currencies ) ) {
			?>
			<tr>
				<th><label for="feedCurrency"><?php esc_html_e( 'Currency', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
				<td>
					<select name="feedCurrency" id="feedCurrency" class="generalInput" required>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $currencies;
						?>
					</select>
				</td>
			</tr>
		<?php } ?>

        <?php
        $isItemWrapperHide = 'table-row';
        if( isset($feedRules['provider']) && 'custom' !== $feedRules['provider'] ) {
            $isItemWrapperHide = 'none';
        } elseif(isset($feedRules['feedType']) && 'xml' !== $feedRules['feedType']) {
            if(isset($feedRules['provider']) && 'custom' === $feedRules['provider'] ) {
                $isItemWrapperHide = 'none';
            }
        }
        ?>
        <tr class="itemWrapper" style="display: <?php echo esc_attr($isItemWrapperHide); ?>;">
            <th><label for="itemsWrapper"><?php esc_html_e( 'Items Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
            <td>
                <input name="itemsWrapper" id="itemsWrapper" type="text" value="<?php echo esc_attr( wp_unslash($feedRules['itemsWrapper']) ); ?>" class="generalInput" required="required">
            </td>
        </tr>

        <tr class="itemWrapper" style="display: <?php echo esc_attr($isItemWrapperHide); ?>;">
            <th><label for="itemWrapper"><?php esc_html_e( 'Single Item Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
            <td>
                <input name="itemWrapper" id="itemWrapper" type="text" value="<?php echo esc_attr( wp_unslash($feedRules['itemWrapper'] ) ); ?>" class="generalInput" required="required">
            </td>
        </tr>

		<?php
		/*
		<tr class="itemWrapper" style="display: none;">
			<th><label for="extraHeader"><?php esc_html_e( 'Extra Header', 'woo-feed' ); ?> </label></th>
			<td>
				<textarea name="extraHeader" id="extraHeader"  style="width: 100%" placeholder="<?php esc_html_e( 'Insert Extra Header value. Press enter at the end of each line.', 'woo-feed' ); ?>" rows="3"><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo isset( $feedRules['extraHeader'] ) ? $feedRules['extraHeader'] : '';
				?></textarea>
			</td>
		</tr>
		 */
		?>

        <?php
        $isDelimiterHide = 'table-row';
        if( isset( $feedRules['feedType'] ) ) {
            if( empty($feedRules['feedType']) || 'xml' === $feedRules['feedType'] || 'json' === $feedRules['feedType'] ) {
                $isDelimiterHide = 'none';
            }
        }
        ?>

		<tr class="wf_csvtxt" style="display: <?php echo esc_attr($isDelimiterHide); ?>;">
			<th><label for="delimiter"><?php esc_html_e( 'Delimiter', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="delimiter" id="delimiter" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_delimiters() as $k => $v ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['delimiter'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="wf_csvtxt" style="display: <?php echo esc_attr($isDelimiterHide); ?>;">
			<th><label for="enclosure"><?php esc_html_e( 'Enclosure', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="enclosure" id="enclosure" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_enclosure() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['enclosure'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>

		<?php
		$vendors = $wooFeedDropDown->getAllVendors();
		if ( ! empty( $vendors ) ) {
			?>
			<tr>
				<th><label for="wf_product_vendors"><?php esc_html_e( 'Select Vendors', 'woo-feed' ); ?></label></th>
				<td>
					<select name="vendors[]" id="wf_product_vendors" class="wf_vendors selectize wf_categories generalInput" data-plugins="remove_button" multiple>
						<?php
						foreach ( $vendors as $vendor ) {
							printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $vendor->ID ), esc_html( $vendor->display_name ), selected( in_array( $vendor->ID, $feedRules['vendors'] ), true, false ) );
						}
						?>
					</select>
					<span style="font-size: x-small"><i><?php esc_html_e( 'Keep blank to select all vendors', 'woo-feed' ); ?></i></span>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php
// End of file woo-feed-content-settings.php
