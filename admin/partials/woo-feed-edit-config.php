<?php

use CTXFeed\V5\Common\DropDownOptions;
use CTXFeed\V5\Merchant\MerchantAttributesFactory;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>
<?php if ( in_array( $provider, woo_feed_get_custom2_merchant() ) ) { ?>
    <table class="table tree widefat fixed mtable" style="width: 100%" id="table-1">
        <tr>
            <td colspan="3">
                <script>
                    let el = document.getElementById("editor");
                    const editor = CodeMirror.fromTextArea(el, {
                        lineNumbers: true,
                        mode: "xml",
                        matchBrackets: true
                    });
                    editor.setSize(null, 620);
                </script>
                <textarea name="feed_config_custom2" id="editor" rows="40"><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $feedRules['feed_config_custom2'];
					?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label for="custom2_attribute"><?php esc_html_e( 'Product Attributes', 'woo-feed' ); ?></label>
            </td>
            <td>
                <select id="custom2_attribute" class="wf_validate_attr wf_attr wf_attributes">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo woo_feed_get_product_attributes();
					?>
                </select>
            </td>
            <td>
                <b><input type="text" class="regular-text" onclick="this.select();" readonly
                          id="custom2_attribute_value"></b>
            </td>
        </tr>
    </table>
<?php } else { ?>
    <table class="table tree widefat fixed sorted_table mtable" style="width: 100%" id="table-1">
        <thead>
        <tr>
            <th></th>
            <th><?php echo esc_html( ucfirst( $provider ) ); ?><?php esc_html_e( 'Attributes', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Prefix', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Type', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Value', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Suffix', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Output Type', 'woo-feed' ); ?></th>
            <th><?php esc_html_e( 'Command', 'woo-feed' ); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
		<?php
		if ( isset( $feedRules['mattributes'] ) && count( $feedRules['mattributes'] ) > 0 ) {
			$mAttributes   = array_values( $feedRules['mattributes'] );
			$wooAttributes = array_values( $feedRules['attributes'] );
			$attr_type     = array_values( $feedRules['type'] );
			$default       = array_values( $feedRules['default'] );
			$prefix        = array_values( $feedRules['prefix'] );
			$suffix        = array_values( $feedRules['suffix'] );
			$outputType    = array_values( $feedRules['output_type'] );
			$limit         = array_values( $feedRules['limit'] );
			$counter       = 0;
			foreach ( $mAttributes as $k => $mAttribute ) {
				?>
                <tr>
                    <td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
                    <td>
						<?php
						$merchantAttributes = MerchantAttributesFactory::get( $feedRules['provider'] );


						if ( $merchantAttributes ) { ?>
                            <select name="mattributes[<?php echo esc_attr( $k ); ?>]" class="wf_mattributes">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo DropDownOptions::merchant_attributes( $merchantAttributes, $mAttribute)
								?>
                            </select>
						<?php } else { ?>
                            <input type="text" name="mattributes[<?php echo esc_attr( $k ); ?>]"
                                   value="<?php echo esc_attr( $mAttribute ); ?>" required class="wf_mattributes">
						<?php } ?>
                    </td>
                    <td>
                        <input type="text" name="prefix[<?php echo esc_attr( $k ); ?>]"
                               value="<?php echo esc_attr( stripslashes( $prefix[ $k ] ) ); ?>" autocomplete="off"
                               class="wf_ps"/>
                    </td>
                    <td>
                        <select name="type[<?php echo esc_attr( $k ); ?>]" class="attr_type wfnoempty">
                            <option
								<?php echo ( 'attribute' == $attr_type[ $k ] ) ? 'selected="selected" ' : ''; ?>value="attribute"><?php esc_html_e( 'Attribute', 'woo-feed' ); ?></option>
                            <option <?php echo ( 'pattern' == $attr_type[ $k ] ) ? 'selected="selected" ' : ''; ?>
                                    value="pattern"><?php esc_html_e( 'Pattern (Static Value)', 'woo-feed' ); ?></option>
                        </select>
                    </td>
                    <td>
                        <select
							<?php echo ( 'attribute' == $attr_type[ $k ] ) ? '' : 'style=" display: none;" '; ?>name="attributes[<?php echo esc_attr( $k ); ?>]"
                            class="wf_attr wf_attributes">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo woo_feed_get_product_attributes( esc_attr( $wooAttributes[ $k ] ) );
							?>
                        </select>
						<?php if ( woo_feed_merchant_require_google_category( $feedRules['provider'], $mAttribute ) ) { ?>
                            <span
								<?php echo ( 'pattern' == $attr_type[ $k ] ) ? '' : 'style=" display: none;" '; ?>class="wf_default wf_attributes">
							<select name="default[<?php echo esc_attr( $k ); ?>]" class="selectize"
                                    data-placeholder="<?php esc_attr_e( 'Select A Category', 'woo-feed' ); ?>">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->googleTaxonomy( esc_attr( $default[ $k ] ) );
								?>
							</select>
						</span>
                            <span style="font-size:x-small;"><a style="color: red"
                                                                href="https://webappick.com/docs/woo-feed/feed-configuration/how-to-map-store-category-with-merchant-category/"
                                                                target="_blank">Learn More..</a></span>
						<?php } else { ?>
                            <input
								<?php echo ( 'pattern' == $attr_type[ $k ] ) ? '' : 'style=" display: none;"'; ?>autocomplete="off"
                                class="wf_default wf_attributes " type="text"
                                name="default[<?php echo esc_attr( $k ); ?>]"
                                value="<?php echo esc_attr( $default[ $k ] ); ?>"/>
						<?php } ?>
                    </td>
                    <td>
                        <input type="text" name="suffix[<?php echo esc_attr( $k ); ?>]"
                               value="<?php echo esc_attr( stripslashes( $suffix[ $k ] ) ); ?>" autocomplete="off"
                               class="wf_ps"/>
                    </td>
                    <td>
                        <select name="output_type[<?php echo esc_attr( $k ); ?>][]" class="outputType wfnoempty"
                                data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
							<?php

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							if ( isset( $outputType[ $k ] ) ) {
								echo $wooFeedDropDown->outputTypes( $outputType[ $k ] );
							} else {
								echo $wooFeedDropDown->outputTypes( 1 );
							}

							?>
                        </select>
                    </td>
                    <td>
						<input type="text" name="limit[<?php echo esc_attr( $k ); ?>]"
							   value="<?php echo esc_attr( stripslashes( $limit[ $k ] ) ); ?>" autocomplete="off" class="wf_ps"/>
                    </td>
                    <td>
                        <i class="delRow dashicons dashicons-trash"></i>
                    </td>
                </tr>
				<?php
			}
		}
		?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3">
                <script type="text/template" id="feed_config_template">
                    <tr>
                        <td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
                        <td>
							<?php if ( method_exists( $wooFeedDropDown, $feedRules['provider'] . 'AttributesDropdown' ) ) { ?>
                                <select name="mattributes[__idx__]" class="wf_mattributes">
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $wooFeedDropDown->{$feedRules['provider'] . 'AttributesDropdown'}();
									?>
                                </select>
							<?php } else { ?>
                                <input type="text" name="mattributes[__idx__]" autocomplete="off" value="" required
                                       class="wf_validate_attr wf_mattributes">
							<?php } ?>
                        </td>
                        <td>
                            <input type="text" name="prefix[__idx__]" autocomplete="off" value="" class="wf_ps">
                        </td>
                        <td>
                            <select name="type[__idx__]" class="attr_type wfnoempty">
                                <option value="attribute"><?php esc_html_e( 'Attribute', 'woo-feed' ); ?></option>
                                <option value="pattern"><?php esc_html_e( 'Pattern (Static Value)', 'woo-feed' ); ?></option>
                            </select>
                        </td>
                        <td>
                            <select name="attributes[__idx__]" required="required"
                                    class="wf_validate_attr wf_attr wf_attributes">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo woo_feed_get_product_attributes();
								?>
                            </select>
                            <input value="" type="text" name="default[]" autocomplete="off"
                                   class="wf_default wf_attributes" style="display:none;">
                        </td>
                        <td>
                            <input type="text" name="suffix[__idx__]" autocomplete="off" value="" class="wf_ps">
                        </td>
                        <td>
                            <select name="output_type[__idx__][]" class="outputType wfnoempty"
                                    data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>"
                                    multiple>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->outputTypes();
								?>
                            </select>
                        </td>
                        <td>
                            <input type="text" value="" name="limit[__idx__]" class="wf_ps">
                        </td>
                        <td>
                            <i class="delRow dashicons dashicons-trash"></i>
                        </td>
                    </tr>
                </script>
                <button type="button" class="button-small button-primary woo-feed-btn-bg-gradient-blue"
                        id="wf_newRow"><?php esc_html_e( 'Add New Attribute', 'woo-feed' ); ?></button>
            </td>
            <td colspan="6"></td>
        </tr>
        </tfoot>
    </table>
<?php } ?>
