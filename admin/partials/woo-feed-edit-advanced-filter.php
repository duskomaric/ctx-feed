<?php

use CTXFeed\V5\Common\DropDownOptions;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
$filterConditions = woo_feed_get_conditions();
if ( isset( $filterConditions['between'] ) ) {
	unset( $filterConditions['between'] );
}

?>
<table class="table tree widefat fixed sorted_table mtable" style="width: 100%;" id="table-advanced-filter">
    <thead>
    <tr>
        <th></th>
        <th><?php esc_html_e( 'Attributes', 'woo-feed' ); ?></th>
        <th><?php esc_html_e( 'Condition', 'woo-feed' ); ?></th>
        <th><?php esc_html_e( 'Value', 'woo-feed' ); ?></th>
        <th><?php esc_html_e( 'Operator', 'woo-feed' ); ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <tr style="display:none;" class="daRow">
        <td>
            <i class="wf_sortedtable dashicons dashicons-menu"></i>
        </td>
        <td>
            <select name="fattribute[]" disabled required class="fsrow">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo DropDownOptions::product_attributes();
				?>
            </select>
        </td>
        <td>

            <select name="condition[]" disabled class="fsrow">
				<?php echo DropDownOptions::conditions(); ?>
            </select>
        </td>
        <td>
            <input type="text" name="filterCompare[]" disabled autocomplete="off" class="fsrow"/>
        </td>
        <td>
            <select name="concatType[]" disabled class="fsrow wf_concat_advance">
                <option value="AND"><?php esc_html_e( 'AND', 'woo-feed' ); ?></option>
                <option selected value="OR"><?php esc_html_e( 'OR', 'woo-feed' ); ?></option>
            </select>
        </td>
        <td>
            <i class="delRow dashicons dashicons-trash"></i>
        </td>
    </tr>
	<?php
	
	if ( isset( $feedRules['fattribute'] ) && count( $feedRules['fattribute'] ) ) {
		foreach ( $feedRules['fattribute'] as $fkey => $fvalue ) {
			if ( ! empty( $fvalue ) ) {
				$condition     = $feedRules['condition'];
				$filterCompare = $feedRules['filterCompare'];
				$concatType    = isset( $feedRules['concatType'] ) && ! empty( $feedRules['concatType'] ) ? $feedRules['concatType'] : [];
				?>
                <tr class="daRow">
                    <td>
                        <i class="wf_sortedtable dashicons dashicons-menu"></i>
                    </td>
                    <td>
                        <select name="fattribute[]" required class="fsrow">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo DropDownOptions::product_attributes( $fvalue );
							?>
                        </select>
                    </td>
                    <td>
                       
                        <select name="condition[]" class=''>
							<?php echo DropDownOptions::conditions( (string) $condition[ $fkey ] ) ?>
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               value="<?php echo isset( $filterCompare[ $fkey ] ) ? esc_attr( stripslashes( $filterCompare[ $fkey ] ) ) : ''; ?>"
                               name="filterCompare[]" autocomplete="off" class="fsrow"/>
                    </td>
                    <td>
						
						<?php
						
						// Backward compatibility for <= v5.2.25
						$defaultConcatType = 'OR';
						
						if ( isset( $feedRules['filterType'] ) && ! empty( $feedRules['filterType'] ) ) {
							$feedRules['filterType'] = $feedRules['filterType'] == 1 || $feedRules['filterType'] == 'OR' ? 'OR' : 'AND';
						} else {
							$feedRules['filterType'] = $defaultConcatType;
						}
						
						if ( ! isset( $concatType[ $fkey ] ) || empty( $concatType[ $fkey ] ) ) {
							$concatType[ $fkey ] = $feedRules['filterType'];
						}
						
						?>

                        <select name="concatType[<?php echo $fkey; ?>]"
                                value="<?php esc_attr( $concatType[ $fkey ] ); ?>">
                            <option value="AND" <?php if ( $concatType[ $fkey ] == 'AND' ) {
								echo "selected";
							} ?> ><?php esc_html_e( 'AND', 'woo-feed' ); ?></option>
                            <option value="OR" <?php if ( $concatType[ $fkey ] == 'OR' ) {
								echo "selected";
							} ?> ><?php esc_html_e( 'OR', 'woo-feed' ); ?></option>
                        </select>
                    </td>
                    <td>
                        <i class="delRow dashicons dashicons-trash"></i>
                    </td>
                </tr>
				<?php
			}
		}
	}
	?>
    </tbody>
    <tfoot>
    <tr>
        <td>
            <button type="button" class="button-small button-primary woo-feed-btn-bg-gradient-blue"
                    id="wf_newFilter"><?php esc_html_e( 'Add New Condition', 'woo-feed' ); ?></button>
        </td>
        <td colspan="5"></td>
    </tr>
    </tfoot>
</table>
