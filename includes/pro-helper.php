<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnusedParameterInspection */
/**
 * Pro Helper Functions
 *
 * @package    WooFeed
 * @subpackage WooFeed_Helper_Functions
 * @since      WooFeed 3.3.0
 * @version    1.0.0
 * @author     KD <mhamudul.hk@gmail.com>
 * @copyright  WebAppick
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
/** @define "WOO_FEED_PRO_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

// Pages.
if ( ! function_exists( 'woo_feed_manage_attribute' ) ) {
	/**
	 * Dynamic Attribute
	 */
	function woo_feed_manage_attribute() {
		// Manage action for category mapping.
		if ( isset( $_GET['action'], $_GET['dattribute'] ) && 'edit-attribute' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['wfDAttributeCode'], $_POST['edit-attribute'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'woo-feed-dynamic-attribute' );
				$condition        = sanitize_text_field( $_POST['wfDAttributeCode'] );
				$dAttributeOption = 'wf_dattribute_attribute_' . $condition;
				if ( $dAttributeOption != $_GET['dattribute'] ) {
					delete_option( sanitize_text_field( $_GET['dattribute'] ) );
				}
				$_data   = woo_feed_sanitize_form_fields( $_POST );
				$oldData = maybe_unserialize( get_option( $dAttributeOption, array() ) );

				// Delete product attribute drop-down cache
				delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes' );

				if ( $oldData === $_data ) {
					update_option( 'wpf_message', esc_html__( 'Attribute Not Changed', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=warning' ) );
					die();
				}
				$update = update_option( $dAttributeOption, serialize( $_data ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				if ( $update ) {
					update_option( 'wpf_message', esc_html__( 'Attribute Updated Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Updated Attribute', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=error' ) );
					die();
				}
			} else {
				require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute.php';
			}
		} elseif ( isset( $_GET['action'] ) && 'add-attribute' == $_GET['action'] ) {
			check_admin_referer( 'woo-feed-dynamic-attribute' );
			if ( count( $_POST ) && isset( $_POST['wfDAttributeCode'], $_POST['add-attribute'] ) ) {
				$condition             = sanitize_text_field( $_POST['wfDAttributeCode'] );
				$_data                 = woo_feed_sanitize_form_fields( $_POST );
				$wf_attribute_opt_name = 'wf_dattribute_' . $condition;

				// Delete product attribute drop-down cache
				delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes' );

				if ( false !== get_option( 'wf_dattribute_' . $condition, false ) ) {
					update_option( 'wpf_message', esc_html__( 'Another attribute with the same name already exists.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=warning' ) );
					die();
				}
				if ( false === get_option( $wf_attribute_opt_name ) ) {
					$update = update_option( $wf_attribute_opt_name, serialize( $_data ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					if ( $update ) {
						update_option( 'wpf_message', esc_html__( 'Attribute Added Successfully', 'woo-feed' ), false );
						wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=success' ) );
						die();
					} else {
						update_option( 'wpf_message', esc_html__( 'Failed To Add Attribute', 'woo-feed' ), false );
						wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-feed-attribute&wpf_message=error' ) );
						die();
					}
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-dynamic-attribute-list.php';
		}

	}
}
if ( ! function_exists( 'woo_feed_manage_attribute_mapping' ) ) {
	/**
	 * Attribute Mapping
	 */
	function woo_feed_manage_attribute_mapping() {
		// page actions
		if ( isset( $_GET['action'] ) && ( 'edit-mapping' == $_GET['action'] || 'add-mapping' == $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-attribute-mapping.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-attribute-mapping-list.php';
		}
	}
}
if ( ! function_exists( 'woo_feed_category_mapping' ) ) {
	/**
	 * Category Mapping
	 */
	function woo_feed_category_mapping() {

		// Manage action for category mapping.
		if ( isset( $_GET['action'], $_GET['cmapping'] ) && 'edit-mapping' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['edit-mapping'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'category-mapping' );

				$mappingOption = sanitize_text_field( $_POST['mappingname'] );
				$mappingOption = 'wf_cmapping_' . sanitize_title( $mappingOption );
				$mappingData   = woo_feed_array_sanitize( $_POST );
				$oldMapping    = maybe_unserialize( get_option( $mappingOption, array() ) );

				// Delete product attribute drop-down cache
				delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes' );

				if ( $oldMapping === $mappingData ) {
					update_option( 'wpf_message', esc_html__( 'Mapping Not Changed', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
					die();
				}

				if ( update_option( $mappingOption, serialize( $mappingData ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					update_option( 'wpf_message', esc_html__( 'Mapping Updated Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Updated Mapping', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
		} elseif ( isset( $_GET['action'] ) && 'add-mapping' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['add-mapping'] ) ) {
				check_admin_referer( 'category-mapping' );

				$mappingOption = 'wf_cmapping_' . sanitize_text_field( $_POST['mappingname'] );

				// Delete product attribute drop-down cache
				delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes' );

				if ( false !== get_option( $mappingOption, false ) ) {
					update_option( 'wpf_message', esc_html__( 'Another category mapping exists with the same name.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
					die();
				}
				if ( update_option( $mappingOption, serialize( woo_feed_array_sanitize( $_POST ) ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					update_option( 'wpf_message', esc_html__( 'Mapping Added Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Add Mapping', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-category-mapping-list.php';
		}
	}
}
if ( ! function_exists( 'woo_feed_wp_options' ) ) {
	function woo_feed_wp_options() {
		if ( isset( $_GET['action'] ) && 'add-option' == $_GET['action'] ) {
			if ( count( $_POST ) && isset( $_POST['wpfp_option'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				check_admin_referer( 'woo-feed-add-option' );

				$options   = get_option( 'wpfp_option', array() );
				$newOption = sanitize_text_field( $_POST['wpfp_option'] );
				$id        = explode( '-', $newOption );
				if ( false !== array_search( $id[0], array_column( $options, 'option_id' ) ) ) { // found
					update_option( 'wpf_message', esc_html__( 'Option Already Added.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-wp-options&wpf_message=error' ) );
					die();
				} else {
					$options[ $id[0] ] = array(
							'option_id'   => $id[0],
							'option_name' => 'wf_option_' . str_replace( $id[0] . '-', '', $newOption ),
					);
					update_option( 'wpfp_option', $options, false );
					update_option( 'wpf_message', esc_html__( 'Option Successfully Added.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-wp-options&wpf_message=success' ) );
					die();
				}
			}
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-add-option.php';
		} else {
			require WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-option-list.php';
		}
	}
}

// Admin Page Form Actions.
if ( ! function_exists( 'woo_feed_save_attribute_mapping' ) ) {

	/**
	 * Handle Attribute Mapping Form Actions
	 *
	 * @return void
	 */
	function woo_feed_save_attribute_mapping() {
		check_admin_referer( 'wf-attribute-mapping' );

		$slug      = false;
		$data      = array();
		$old_data  = array();
		$is_update = false;
		if ( ! isset( $_POST['mapping_name'] ) || ( isset( $_POST['mapping_name'] ) && empty( $_POST['mapping_name'] ) ) ) {
			wp_die( esc_html__( 'Missing Required Fields.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), array( 'back_link' => true ) );
		} else {
			$data['name'] = sanitize_text_field( $_POST['mapping_name'] );
		}

		if ( ! isset( $_POST['value'] ) || ( isset( $_POST['value'] ) && ( empty( $_POST['value'] ) || ! is_array( $_POST['value'] ) ) ) ) {
			wp_die( esc_html__( 'Missing Required Fields.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), array( 'back_link' => true ) );
		} else {
			foreach ( $_POST['value'] as $item ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( ' ' === $item ) {
					$data['mapping'][] = $item;
				} elseif ( '' !== $item ) {
					$data['mapping'][] = sanitize_text_field( $item );
				}
			}
			$data['mapping'] = array_filter( $data['mapping'] );
		}

		if ( isset( $_POST['mapping_glue'] ) ) {
			$data['glue'] = $_POST['mapping_glue'];
		} else {
			$data['glue'] = '';
		}

		if (
				isset( $_POST['option_name'] ) &&
				! empty( $_POST['option_name'] ) &&
				false !== strpos( $_POST['option_name'], Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			$slug = sanitize_text_field( $_POST['option_name'] );
		} else {
			// generate unique one.
			$slug = woo_feed_unique_option_name( Woo_Feed_Products_v3_Pro::PRODUCT_ATTRIBUTE_MAPPING_PREFIX . sanitize_title( $data['name'] ) );
		}

		if ( $slug ) {
			$old_data  = get_option( $slug );
			$is_update = true;
			if ( ! isset( $old_data['name'], $old_data['mapping'] ) ) {
				$old_data = array();
			}
		}

		if ( empty( $data ) ) {
			wp_die( esc_html__( 'Invalid Data Submitted.', 'woo-feed' ), esc_html__( 'Invalid Request.', 'woo-feed' ), array( 'back_link' => true ) );
		}

		// check for update
		if ( $data !== $old_data ) {
			$update = update_option( $slug, $data, false );
			if ( $update ) {
				$update  = 'success';
				$message = $is_update ? esc_html__( 'Attribute Mapping Data Updated.', 'woo-feed' ) : esc_html__( 'Attribute Mapping Data Saved.', 'woo-feed' );
				update_option( 'wpf_message', $message, false );
			} else {
				$update = 'error';
				update_option( 'wpf_message', esc_html__( 'Unable To Save Attribute Mapping Data.', 'woo-feed' ), false );
			}
		} else {
			$update = 'warning';
			update_option( 'wpf_message', esc_html__( 'Nothing Updated.', 'woo-feed' ), false );
		}

		// Delete product attribute drop-down cache
		delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes' );

		wp_safe_redirect( admin_url( 'admin.php?page=webappick-manage-attribute-mapping&wpf_message=' . $update ) );
		die();
	}
}

// The Editor.
if ( ! function_exists( 'woo_feed_pro_parse_feed_rules' ) ) {
	/**
	 * Parse Feed Config/Rules to make sure that necessary array keys are exists
	 * this will reduce the uses of isset() checking
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	function woo_feed_pro_parse_feed_rules( $rules = array() ) {
		if ( isset( $rules['provider'] ) && in_array( $rules['provider'], woo_feed_get_custom2_merchant() ) ) {
			if ( ! isset( $rules['feed_config_custom2'] ) ) {
				$rules['feed_config_custom2'] = '';
			}
		}
		if ( isset( $rules['feed_config_custom2'] ) ) {
			$rules['feed_config_custom2'] = trim( preg_replace( '/\\\\/', '', $rules['feed_config_custom2'] ) );
		}
		$str_replace = array(
				'subject' => '',
				'search'  => '',
				'replace' => '',
		);
		if ( ! isset( $rules['str_replace'] ) || empty( $rules['str_replace'] ) ) {
			$rules['str_replace'] = array( $str_replace );
		} else {
			for ( $i = 0, $iMax = count( $rules['str_replace'] ); $i < $iMax; $i ++ ) {
				$rules['str_replace'][ $i ] = wp_parse_args( $rules['str_replace'][ $i ], $str_replace );
			}
		}

		return $rules;
	}
}

if ( ! function_exists( 'render_feed_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_feed_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-edit-config.php';
	}
}
if ( ! function_exists( 'render_filter_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_filter_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-edit-filter.php';
	}
}

if ( ! function_exists( 'woo_feed_pro_insert_feed_data_filter' ) ) {
	/**
	 * Filter Feed config before insert
	 *
	 * @param array $config
	 *
	 * @return array
	 * @since 3.3.3
	 *
	 */
	function woo_feed_pro_insert_feed_data_filter( $config ) {
		if ( isset( $config['str_replace'] ) ) {
			$config['str_replace'] = array_filter(
					$config['str_replace'],
					function ( $item ) {
						return isset( $item['subject'], $item['search'], $item['replace'] ) && ! empty( $item['subject'] ) && ! empty( $item['search'] ) ? $item : null;
					}
			);
			$config['str_replace'] = array_filter( $config['str_replace'] );
			$config['str_replace'] = array_values( $config['str_replace'] );
			$config['str_replace'] = array_map(
					function ( $item ) {
						$item['subject'] = wp_unslash( $item['subject'] );
						$item['search']  = wp_unslash( $item['search'] );
						$item['replace'] = wp_unslash( $item['replace'] );

						return $item;
					},
					$config['str_replace']
			);
		}

		return $config;
	}
}
if ( ! function_exists( 'woo_feed_get_variable_visibility_options' ) ) {
	/**
	 * Get Variable visibility options for feed editor
	 *
	 * @return array
	 */
	function woo_feed_get_variable_visibility_options() {
		return apply_filters(
				'woo_feed_variable_visibility_options',
				array(
						'n'       => esc_html__( 'Variable Products (Parent)', 'woo-feed' ),
						'y'       => esc_html__( 'All Variations', 'woo-feed' ),
						'default' => esc_html__( 'Default Variation', 'woo-feed' ),
						'cheap'   => esc_html__( 'Cheapest Variation', 'woo-feed' ),
						'first'   => esc_html__( 'First Variation', 'woo-feed' ),
						'last'    => esc_html__( 'Last Variation', 'woo-feed' ),
						'both'    => esc_html__( 'Variable + Variations', 'woo-feed' ),
				)
		);
	}
}
if ( ! function_exists( 'woo_feed_get_variable_price_options' ) ) {
	/**
	 * Get Variable price options for feed editor
	 *
	 * @return array
	 */
	function woo_feed_get_variable_price_options() {
		return apply_filters(
				'woo_feed_variable_price_options',
				array(
						'first' => esc_html__( 'First Variation Price', 'woo-feed' ),
						'max'   => esc_html__( 'Max Variation Price', 'woo-feed' ),
						'min'   => esc_html__( 'Min Variation Price', 'woo-feed' ),
				)
		);
	}
}
if ( ! function_exists( 'woo_feed_get_variable_quantity_options' ) ) {
	/**
	 * Get Variable quantity options for feed editor
	 *
	 * @return array
	 */
	function woo_feed_get_variable_quantity_options() {
		return apply_filters(
				'woo_feed_variable_quantity_options',
				array(
						'first' => esc_html__( 'First Variation Quantity', 'woo-feed' ),
						'max'   => esc_html__( 'Max Variation Quantity', 'woo-feed' ),
						'min'   => esc_html__( 'Min Variation Quantity', 'woo-feed' ),
						'sum'   => esc_html__( 'Sum of Variation Quantity', 'woo-feed' ),
				)
		);
	}
}
if ( ! function_exists( 'woo_feed_get_post_statuses' ) ) {
	/**
	 * Get Product Statuses
	 *
	 * @return array
	 */
	function woo_feed_get_post_statuses() {
		return (array) apply_filters( 'woo_feed_product_statuses', get_post_statuses() );
	}
}
if ( ! function_exists( 'woo_feed_format_price' ) ) {
	/**
	 * Format Price with specified number format
	 *
	 * @param $price
	 * @param $options
	 *
	 * @return string
	 * @see wc_price()
	 */
	function woo_feed_format_price( $price, $options ) {
		// @TODO include currency and price_format.
		$options           = apply_filters(
				'woo_feed_price_options',
				wp_parse_args(
						$options,
						array(
								'decimal_separator'  => wc_get_price_decimal_separator(),
								'thousand_separator' => wc_get_price_thousand_separator(),
								'decimals'           => wc_get_price_decimals(),
						)
				)
		);
		$unformatted_price = $price;
		$negative          = $price < 0;
		$price             = apply_filters( 'raw_woo_feed_price', floatval( $negative ? $price * - 1 : $price ) );
		$price             = apply_filters(
				'formatted_woo_feed_price',
				number_format(
						$price,
						$options['decimals'],
						$options['decimal_separator'],
						$options['thousand_separator']
				),
				$price,
				$options['decimals'],
				$options['decimal_separator'],
				$options['thousand_separator']
		);
		if ( apply_filters( 'woo_feed_price_trim_zeros', false ) && $options['decimals'] > 0 ) {
			$price = preg_replace( '/' . preg_quote( $options['decimal_separator'], '/' ) . '0++$/', '', $price );
		}

		$price = ( $negative ? '-' : '' ) . $price;

		/**
		 * Filters the string of price markup.
		 *
		 * @param string $price Formatted price.
		 * @param array $options Pass on the args.
		 * @param float $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
		 */
		return apply_filters( 'woo_feed_price', $price, $options, $unformatted_price );
	}
}
if ( ! function_exists( 'woo_feed_get_conditions' ) ) {
	function woo_feed_get_conditions() {
		return array(
				'=='        => __( 'is / equal', 'woo-feed' ),
				'!='        => __( 'is not / not equal', 'woo-feed' ),
				'>='        => __( 'equals or greater than', 'woo-feed' ),
				'>'         => __( 'greater than', 'woo-feed' ),
				'<='        => __( 'equals or less than', 'woo-feed' ),
				'<'         => __( 'less than', 'woo-feed' ),
				'contains'  => __( 'contains', 'woo-feed' ),
				'nContains' => __( 'does not contain', 'woo-feed' ),
				'between'   => __( 'between', 'woo-feed' ),
		);
	}
}

// Editor Tabs.
if ( ! function_exists( 'woo_feed_parse_feed_rules' ) ) {
	/**
	 * Parse Feed Config/Rules to make sure that necessary array keys are exists
	 * this will reduce the uses of isset() checking
	 *
	 * @param array $rules rules to parse.
	 * @param string $context parsing context. useful for filtering, view, save, db, create etc.
	 *
	 * @return array
	 * @since  3.3.5 $context parameter added.
	 *
	 * @uses   wp_parse_args
	 */
	function woo_feed_parse_feed_rules( $rules = array(), $context = 'view' ) {

		if ( empty( $rules ) ) {
			$rules = array();
		}
		$defaults             = array(
				'provider'              => '',
				'feed_country'          => '',
				'filename'              => '',
				'feedType'              => '',
				'ftpenabled'            => 0,
				'ftporsftp'             => 'ftp',
				'ftphost'               => '',
				'ftpport'               => '21',
				'ftpuser'               => '',
				'ftppassword'           => '',
				'ftppath'               => '',
				'ftpmode'               => 'active',
				'is_variations'         => 'y', // Only Variations (All Variations)
				'variable_price'        => 'first',
				'variable_quantity'     => 'first',
				'feedLanguage'          => apply_filters( 'wpml_current_language', null ),
				'feedCurrency'          => get_woocommerce_currency(),
				'itemsWrapper'          => 'products',
				'itemWrapper'           => 'product',
				'delimiter'             => ',',
				'enclosure'             => 'double',
				'extraHeader'           => '',
				'vendors'               => array(),
			// Feed Config
				'mattributes'           => array(), // merchant attributes
				'prefix'                => array(), // prefixes
				'type'                  => array(), // value (attribute) types
				'attributes'            => array(), // product attribute mappings
				'default'               => array(), // default values (patterns) if value type set to pattern
				'suffix'                => array(), // suffixes
				'output_type'           => array(), // output type (output filter)
				'limit'                 => array(), // limit or command
			// filters tab
				'composite_price'       => 'all_product_price',
				'product_ids'           => '',
				'categories'            => array(),
				'post_status'           => array( 'publish' ),
				'filter_mode'           => array(),
				'campaign_parameters'   => array(),
				'is_outOfStock'         => 'n',
				'is_backorder'          => 'n',
				'is_emptyDescription'   => 'n',
				'is_emptyImage'         => 'n',
				'is_emptyPrice'         => 'n',
				'product_visibility'    => 0,
			// include hidden ? 1 yes 0 no
				'outofstock_visibility' => 0,
			// override wc global option for out-of-stock product hidden from catalog? 1 yes 0 no
				'ptitle_show'           => '',
				'decimal_separator'     => wc_get_price_decimal_separator(),
				'thousand_separator'    => wc_get_price_thousand_separator(),
				'decimals'              => wc_get_price_decimals(),
		);
		$rules                = wp_parse_args( $rules, $defaults );
		$rules['filter_mode'] = wp_parse_args(
				$rules['filter_mode'],
				array(
						'product_ids' => 'include',
						'categories'  => 'include',
						'post_status' => 'include',
				)
		);

		$rules['campaign_parameters'] = wp_parse_args(
				$rules['campaign_parameters'],
				array(
						'utm_source'   => '',
						'utm_medium'   => '',
						'utm_campaign' => '',
						'utm_term'     => '',
						'utm_content'  => '',
				)
		);

		if ( ! empty( $rules['provider'] ) && is_string( $rules['provider'] ) ) {
			/**
			 * filter parsed rules for provider
			 *
			 * @param array $rules
			 * @param string $context
			 *
			 * @since 3.3.7
			 */
			$rules = apply_filters( "woo_feed_{$rules['provider']}_parsed_rules", $rules, $context );
		}

		/**
		 * filter parsed rules
		 *
		 * @param array $rules
		 * @param string $context
		 *
		 * @since 3.3.7 $provider parameter removed
		 */
		return apply_filters( 'woo_feed_parsed_rules', $rules, $context );
	}
}
if ( ! function_exists( 'woo_feed_pro_filter_tabs' ) ) {
	function woo_feed_pro_filter_tabs( $tabs ) {
		return array_splice_assoc(
				$tabs,
				'filter',
				'filter',
				array(
						'advanced-filter' => array(
								'label'    => __( 'Advanced Filter', 'woo-feed' ),
								'callback' => 'render_advanced_filter_config',
						),
				)
		);
	}
}
if ( ! function_exists( 'render_advanced_filter_config' ) ) {
	/** @noinspection PhpUnusedParameterInspection */
	/**
	 * @param string $tabId
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_advanced_filter_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_PRO_ADMIN_PATH . 'partials/woo-feed-edit-advanced-filter.php';
	}
}

// File process.
if ( ! function_exists( 'woo_feed_duplicate_feed' ) ) {
	/**
	 * @param string $feed_from Required. Feed name to duplicate from
	 * @param string $new_name Optional. New name for duplicate feed.
	 *                              Default to auto generated slug from the old name prefixed with number.
	 * @param bool $copy_file Optional. Copy the file. Default is true.
	 *
	 * @return bool|WP_Error        WP_Error object on error, true on success.
	 */
	function woo_feed_duplicate_feed( $feed_from, $new_name = '', $copy_file = true ) {

		if ( empty( $feed_from ) ) {
			return new WP_Error( 'invalid_feed_name_top_copy_from', esc_html__( 'Invalid Request.', 'woo-feed' ) );
		}
		// normalize the option name.
		$feed_from = woo_feed_extract_feed_option_name( $feed_from );
		// get the feed data for duplicating.
		$base_feed = maybe_unserialize( get_option( 'wf_feed_' . $feed_from, array() ) );
		// validate the feed data.
		if ( empty( $base_feed ) || ! is_array( $base_feed ) || ! isset( $base_feed['feedrules'] ) || ( isset( $base_feed['feedrules'] ) && empty( $base_feed['feedrules'] ) ) ) {
			return new WP_Error( 'empty_base_feed', esc_html__( 'Feed data is empty. Can\'t duplicate feed.', 'woo-feed' ) );
		}
		$part = '';
		if ( empty( $new_name ) ) {
			// generate a unique slug for duplicate the feed.
			$new_name = generate_unique_feed_file_name( $feed_from, $base_feed['feedrules']['feedType'], $base_feed['feedrules']['provider'] );
			// example-2 or example-2-2-3
			$part = ' ' . str_replace_trim( $feed_from . '-', '', $new_name ); // -2-2-3
		} else {
			$new_name = generate_unique_feed_file_name( $new_name, $base_feed['feedrules']['feedType'], $base_feed['feedrules']['provider'] );
		}
		// new name for the feed with numeric parts from the unique slug.
		$base_feed['feedrules']['filename'] = $base_feed['feedrules']['filename'] . $part;
		// copy feed config data.
		$saved_feed = woo_feed_save_feed_config_data( $base_feed['feedrules'], $new_name, false );
		if ( false === $saved_feed ) {
			return new WP_Error( 'unable_to_save_the_duplicate', esc_html__( 'Unable to save the duplicate feed data.', 'woo-feed' ) );
		}

		if ( true === $copy_file ) {
			// copy the data file.
			$original_file = woo_feed_get_file( $feed_from, $base_feed['feedrules']['provider'], $base_feed['feedrules']['feedType'] );
			$new_file      = woo_feed_get_file( $new_name, $base_feed['feedrules']['provider'], $base_feed['feedrules']['feedType'] );
			if ( copy( $original_file, $new_file ) ) {
				return true;
			} else {
				return new WP_Error( 'unable_to_copy_file', esc_html__( 'Feed Successfully Duplicated, but unable to generate the data file. Please click the "Regenerate Button"', 'woo-feed' ) );
			}
		}

		return true;
	}
}

// Third Party
if ( ! function_exists( 'woo_feed_is_multi_vendor' ) ) {
	/**
	 * Check any multi vendor plugin installed or not
	 * Check if any of following multi vendor plugin class exists
	 *
	 * @link https://wedevs.com/dokan/
	 * @link https://www.wcvendors.com/
	 * @link https://yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/
	 * @link https://wc-marketplace.com/
	 * @link https://wordpress.org/plugins/wc-multivendor-marketplace/
	 * @return bool
	 */
	function woo_feed_is_multi_vendor() {
		return apply_filters(
				'woo_feed_is_multi_vendor',
				(
						class_exists( 'WeDevs_Dokan' ) ||
						class_exists( 'WC_Vendors' ) ||
						class_exists( 'YITH_Vendor' ) ||
						class_exists( 'WCMp' ) ||
						class_exists( 'WCFMmp' )
				)
		);
	}
}
if ( ! function_exists( 'woo_feed_get_multi_vendor_user_role' ) ) {
	/**
	 * Get Sellers User Role Based On Multi Vendor Plugin
	 *
	 * @return string
	 */
	function woo_feed_get_multi_vendor_user_role() {
		$map         = array(
				'WeDevs_Dokan' => 'seller',
				'WC_Vendors'   => 'vendor',
				'YITH_Vendor'  => 'yith_vendor',
				'WCMp'         => 'dc_vendor',
				'WCFMmp'       => 'wcfm_vendor',
		);
		$vendor_role = '';
		foreach ( $map as $class => $role ) {
			if ( class_exists( $class, false ) ) {
				$vendor_role = $role;
				break;
			}
		}

		/**
		 * Filter Vendor User Role
		 *
		 * @param string $vendor_role
		 *
		 * @since 3.4.0
		 */
		return apply_filters( 'woo_feed_multi_vendor_user_role', $vendor_role );
	}
}
if ( ! function_exists( 'woo_feed_has_composite_product_plugin' ) ) {
	function woo_feed_has_composite_product_plugin() {
		return (
				class_exists( 'WC_Product_Composite', false ) ||
				class_exists( 'WC_Product_Yith_Composite', false )
		);
	}
}

if ( ! function_exists( 'woo_feed_sanitize_custom_template2_config_field' ) ) {
	/**
	 * filter callback to allow un-sanitized data for custom template 2 config textarea
	 *
	 * @param bool $status
	 * @param string $key
	 *
	 * @return bool
	 */
	function woo_feed_sanitize_custom_template2_config_field( $status, $key ) {
		if ( 'feed_config_custom2' === $key ) {
			return false;
		}

		return $status;
	}

	add_filter( 'woo_feed_sanitize_form_fields', 'woo_feed_sanitize_custom_template2_config_field', 10, 2 );
}

if ( ! function_exists( 'woo_feed_get_functions_from_command' ) ) {
	/**
	 * Get command function names from string
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	function woo_feed_get_functions_from_command( $string ) {
		$functions = explode( ',', $string );
		$funArray  = array();
		if ( $functions ) {
			foreach ( $functions as $key => $value ) {
				if ( ! empty( $value ) ) {
					$funArray['formatter'][] = woo_feed_get_string_between( $value, '[', ']' );
				}
			}
		}

		return $funArray;
	}
}
if ( ! function_exists( 'woo_feed_get_string_between' ) ) {
	/**  Separate XML header footer and body from feed content
	 *
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 *
	 * @return string
	 */
	function woo_feed_get_string_between( $string, $start, $end ) { // Moved to FeedHelper file.
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( 0 == $ini ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}
}
if ( ! function_exists( 'woo_feed_get_conversion_rate' ) ) {
	/**
	 * Base Currency Convert
	 *
	 * @param string $from
	 * @param string $to
	 * @param int $retry
	 *
	 * @return bool|float
	 */
	function woo_feed_get_conversion_rate( $from, $to, $retry = 0 ) {
		$apiKey  = '62501510ffac07fd8e4849112e91c66d';
		$request = wp_safe_remote_get( 'http://data.fixer.io/api/latest?access_key=' . $apiKey . '&base=' . $from . '&symbols=' . $to );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body   = wp_remote_retrieve_body( $request );
		$result = json_decode( $body, true );

		if ( isset( $result['rates'] ) ) {
			if ( array_key_exists( $to, $result['rates'] ) ) {
				return $result['rates'][ $to ];
			}
		}

		return false;
	}
}
if ( ! function_exists( 'woo_feed_convert_currency' ) ) {
	/**
	 * Currency Convert
	 *
	 * @param int|float|double $amount
	 * @param string $from
	 * @param string $to
	 *
	 * @return float
	 */
	function woo_feed_convert_currency( $amount, $from, $to ) {
		$optionName = strtolower( $from ) . strtolower( $to );

		if ( ! get_option( $optionName ) || get_option( 'wf_convert_' . gmdate( 'Y-m-d' ) != gmdate( 'Y-m-d' ) ) ) {
			$converted = woo_feed_get_conversion_rate( $from, $to );
			update_option( 'wf_convert_' . gmdate( 'Y-m-d' ), gmdate( 'Y-m-d' ), false );
			update_option( $optionName, $converted, false );
			$newAmount = $amount * $converted;

			return round( $newAmount, 2 );
		} else {
			$rate   = get_option( $optionName );
			$amount = (int) $amount * (int) $rate;

			return round( $amount, 2 );
		}
	}
}

// WPML Helper Functions.
if ( ! function_exists( 'wooFeed_is_multilingual' ) ) {
	/**
	 * Check if if site is multilingual
	 *
	 * @TODO add common language handler for all multilingual plugins
	 * @return bool
	 */
	function wooFeed_is_multilingual() {
		/**
		 * filter is multilingual enabled.
		 *
		 * @param bool $status multilingual status
		 */
		return apply_filters( 'woo_feed_is_multilingual', ( class_exists( 'SitePress', false ) ) );
	}
}
if ( ! function_exists( 'woo_feed_switch_language' ) ) {
	/**
	 * Switch Current language.
	 * Switches WPML's query language
	 *
	 * @param array|string $language_code The language code to switch to Or the feed config to get the language code
	 *                                    If set to null it restores the original language
	 *                                    If set to 'all' it will query content from all active languages
	 *                                    Defaults to null
	 * @param bool|string $cookie_lang Optionally also switch the cookie language
	 *                                    to the value given. default is true.
	 *
	 * @return void
	 * @global SitePress $sitepress
	 * @see SitePress::switch_lang()
	 * @see SitePress::get_current_language()
	 */
	function woo_feed_switch_language( $language_code, $cookie_lang = true ) {
		if ( is_array( $language_code ) && isset( $language_code['feedLanguage'] ) ) {
			$language_code = $language_code['feedLanguage'];
		} else {
			if ( isset( $language_code->feed_info ) && is_array( $language_code->feed_info ) && isset( $language_code->feed_info['feedLanguage'] ) ) {
				$language_code = $language_code->feed_info['feedLanguage'];
			}
		}

		if ( ! empty( $language_code ) ) {
			if ( class_exists( 'SitePress', false ) ) {
				// WPML Switch Language.
				global $sitepress;

				if ( $sitepress->get_current_language() !== $language_code ) {
					$sitepress->switch_lang( $language_code, $cookie_lang );
				}
			}

			// when polylang plugin is activated
			if ( defined( 'POLYLANG_BASENAME' ) || function_exists( 'PLL' ) ) {
				if ( pll_current_language() !== $language_code ) {
					PLL()->curlang = PLL()->model->get_language( $language_code );
				}
			}
		}
	}
}
if ( ! function_exists( 'woo_feed_restore_language' ) ) {
	/**
	 * Restore Default Language.
	 * Switches WPML's query language to site's default language
	 *
	 * @return void
	 * @see SitePress::get_default_language()
	 * @see woo_feed_switch_language()
	 * @global SitePress $sitepress
	 */
	function woo_feed_restore_language() {
		$language_code = '';
		if ( class_exists( 'SitePress', false ) ) {
			// WPML restore Language.
			global $sitepress;
			$language_code = $sitepress->get_default_language();
		}
		/**
		 * Filter to hijack Default Language code before restore
		 *
		 * @param string $language_code
		 */
		$language_code = apply_filters( 'woo_feed_restore_language', $language_code );
		if ( ! empty( $language_code ) ) {
			woo_feed_switch_language( $language_code );
		}
	}
}
if ( ! function_exists( 'woo_feed_tp_translate' ) ) {
	/**
	 * Translate with translatepress plugin.
	 * Switches translatepress's query language to expected language
	 *
	 * @param string $attribute product attribute name
	 * @param mixed $attributeValue product attribute value
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product product obj
	 * @param mixed $config feed configuration
	 *
	 * @return mixed
	 * @since  5.2.12
	 *
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_tp_translate( $attribute, $attributeValue, $product, $config, $attr_key='' ) {

		//when translatepress is activated
		$description_key  = array_search('description', $config['mattributes']);
		$title_key  = array_search('title', $config['mattributes']);
		$categories_key  = array_search('categories', $config['mattributes']);

		if ( is_plugin_active( 'translatepress-multilingual/index.php' ) ) {
			if ( isset( $config['feedLanguage'] ) && ! empty( $config['feedLanguage'] ) ) {
				$feed_language = $config['feedLanguage'];

//				if ( 'en_US' === $feed_language ) {
//					return $attributeValue;
//				}

				if ( class_exists( 'TRP_Settings' ) && class_exists( 'TRP_Translation_Render' ) ) {
					$settings   = ( new TRP_Settings() )->get_settings();
					$trp_render = new TRP_Translation_Render( $settings );
					global $TRP_LANGUAGE;
					$default_language = $TRP_LANGUAGE;
					$TRP_LANGUAGE     = $feed_language;


					//For multiple line title and description
					if( $attr_key === $title_key ){
						$attributeValue = $trp_render->translate_page($product->get_title());
						$attributeValue = strip_tags($attributeValue);
					}

					elseif( $attr_key === $description_key ){
						$attributeValue = $trp_render->translate_page($product->get_description());
						$attributeValue = strip_tags($attributeValue);
					}

					elseif( $attr_key === $categories_key ){
						if( $config['feedType'] === 'xml' ) {
							$categories = explode(" &gt; ",$attributeValue);
						} else {
							$categories = explode(" > ",$attributeValue);
						}
						$trp_category = '';
						foreach( $categories as $key => $category ) {
							$attributeValue  = $trp_render->translate_page($category);
							if( $key === 0 ) {
								$first_category = $attributeValue;
							} else {
								$trp_category .= ' > ' . $attributeValue;
							}
						}
						$attributeValue = $first_category . $trp_category;
					}else{
						$attributeValue   = $trp_render->translate_page( $attributeValue ); //@TODO need to make attributeValue as html, description attribute should return html
					}

					//reset trp_language
					$TRP_LANGUAGE = $default_language;
				}
			}
		}

		return $attributeValue;
	}
}
if ( ! function_exists( 'woo_feed_weglot_translate' ) ) {
	/**
	 * Translate with weglot plugin.
	 *
	 * @param string $attribute product attribute name
	 * @param mixed $attributeValue product attribute value
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product product obj
	 * @param mixed $config feed configuration
	 *
	 * @return mixed
	 * @throws Exception
	 * @since  5.2.12
	 *
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_weglot_translate( $attribute, $attributeValue, $product, $config ) {
		$translated_string = $attributeValue;

		if ( is_plugin_active( 'weglot/weglot.php' ) ) {

			if ( function_exists( 'weglot_get_api_key' ) && function_exists( 'weglot_get_current_language' ) && function_exists( 'weglot_get_destination_languages' ) ) {
				$api_key              = weglot_get_api_key();
				$current_language     = weglot_get_current_language();
				$dest_lang_lists      = wp_list_pluck( weglot_get_destination_languages(), 'language_to' );
				$destination_language = reset( $dest_lang_lists );

				//api header
				$header = array(
						'Content-Type' => 'application/json',
				);

				if ( ! empty( $api_key ) && ! empty( $current_language ) && ! empty( $destination_language ) ) {

					//api data
					$data_array = array(
							'l_from'      => $current_language,
							'l_to'        => $destination_language,
							'request_url' => home_url(),
							'bot'         => 0,
							'words'       => array(
									array(
											'w' => $attributeValue,
											't' => 1,
									),
							),
					);

					//api call
					$response = woo_feed_call_api( 'POST', 'https://api.weglot.com/translate?api_key=' . $api_key, $header, json_encode( $data_array ) );
					$response = json_decode( $response, true );

					//search translated string for targeted string
					if ( isset( $response['from_words'] ) && isset( $response['to_words'] ) ) {
						$from_words = $response['from_words'];
						$to_words   = $response['to_words'];

						foreach ( $from_words as $from_word_key => $from_word_value ) {
							if ( isset( $to_words[ $from_word_key ] ) ) {
								$translated_string = $to_words[ $from_word_key ];
							}
						}
					}
				}
			}
		} else {
			return false;
		}

		return $translated_string;
	}
}

if ( ! function_exists( 'woo_feed_call_api' ) ) {
	/**
	 * Woo Feed Call API.
	 *
	 * @param $method
	 * @param $url
	 * @param $header
	 * @param $data
	 *
	 * @return mixed
	 */
	function woo_feed_call_api( $method, $url, $header, $data ) {
		$curl = curl_init();

		switch ( $method ) {

			case 'POST':
				curl_setopt( $curl, CURLOPT_POST, 1 );
				if ( $data ) {
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
				}
				break;

			case 'PUT':
				curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
				if ( $data ) {
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
				}
				break;

			default:
				if ( $data ) {
					$url = sprintf( '%s?%s', $url, http_build_query( $data ) );
				}
		}

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );

		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

		$result = curl_exec( $curl );
		if ( ! $result ) {
			die( 'Connection Failure' );
		}
		curl_close( $curl );

		return $result;
	}
}

if ( ! function_exists( 'woo_feed_wpml_get_original_post_id' ) ) {
	/**
	 * @param int $element_id
	 *
	 * @return int:null
	 * @see wpml_get_default_language()
	 * @see wpml_object_id_filter()
	 */
	function woo_feed_wpml_get_original_post_id( $element_id ) {
		$lang = apply_filters( 'wpml_default_language', '' );

		/**
		 * Get translation of specific language for element id.
		 *
		 * @param int $elementId translated object id
		 * @param string $element_type object type (post type). If set to 'any' wpml will try to detect the object type
		 * @param bool|false $return_original_if_missing return the original if missing.
		 * @param string|null $language_code Language code to get the translation. If set to 'null', wpml will use current language.
		 */
		return apply_filters( 'wpml_object_id', $element_id, 'any', true, $lang );
	}
}
if ( ! function_exists( 'woo_feed_pll_get_original_post_id' ) ) {
	/**
	 * Get parent product id for Polylang Multi Language
	 *
	 * @param int $element_id product id for current language
	 *
	 * @return int parent product id for parent language
	 */
	function woo_feed_pll_get_original_post_id( $element_id ) {
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$polylang_post   = pll_get_post_translations( $element_id );
			$defaultLanguage = pll_default_language();
			if ( isset( $polylang_post[ $defaultLanguage ] ) ) {
				$parent_id = $polylang_post[ $defaultLanguage ];
			}
		}

		return ! empty( $parent_id ) ? $parent_id : $element_id;
	}
}

if ( ! function_exists( 'woo_feed_get_wcml_price' ) ) {
	/**
	 * Get price by product id and currency
	 *
	 * @param int $productId Product ID for price convert
	 * @param string $currency currency to convert the price
	 * @param string $price current/raw price
	 * @param string $type Price type (_price , _regular_price or _sale_price)
	 *
	 * @return float               return current price if type is null
	 */
	function woo_feed_get_wcml_price( $price, $productId, $currency, $type = null ) {

		if ( class_exists( 'woocommerce_wpml' ) && wcml_is_multi_currency_on() && get_woocommerce_currency() !== $currency ) {
			$originalId = woo_feed_wpml_get_original_post_id( $productId );
			global $woocommerce_wpml;
			if ( get_post_meta( $originalId, '_wcml_custom_prices_status', true ) ) {
				$prices = $woocommerce_wpml->multi_currency->custom_prices->get_product_custom_prices( $originalId, $currency );
				if ( ! empty( $prices ) ) {
					if ( is_null( $type ) ) {
						return $prices['_price'];
					}
					if ( array_key_exists( $type, $prices ) ) {
						return $prices[ $type ];
					} else {
						return $prices['_price'];
					}
				}
			} else {
				$currencies = $woocommerce_wpml->multi_currency->currencies;
				if ( array_key_exists( $currency, $currencies ) ) {
					$price = ( (float) $price * (float) $currencies[ $currency ]['rate'] );
					$price = $woocommerce_wpml->multi_currency->prices->apply_rounding_rules( $price, $currency );
				}
			}
		}

		return (float) $price;
	}
}

// Hooks on feed generating process...
if ( ! function_exists( 'woo_feed_pro_apply_hooks_before_product_loop' ) ) {
	/**
	 * Apply Hooks Before Looping through ProductIds
	 *
	 * @param int[] $productIds
	 * @param array $feedConfig
	 */
//	function woo_feed_pro_apply_hooks_before_product_loop( $productIds, $feedConfig ) {
//		// Aelia Currency Switcher support.
//		$currency_code                                        = ( ! empty( $feedConfig['feedCurrency'] ) ) ? $feedConfig['feedCurrency'] : get_woocommerce_currency();
//		$GLOBALS['woo_feed_get_single_feed_default_currency'] = get_woocommerce_currency();
//
//		add_filter(
//				'wc_aelia_cs_selected_currency',
//				function ( $selected_currency ) use ( $currency_code ) {
//					return $currency_code;
//				},
//				999
//		);
//
//		// WooCommerce Currency Switcher by realmag777 support.
//		if ( class_exists( 'woocommerce_wpml' ) && wcml_is_multi_currency_on() ) {
//			//when wpml and woocs both is installed and wpml is enabled, woocs change currency by it's own filter, filter is removed here
//			if ( class_exists( 'WOOCS' ) ) {
//				global $WOOCS;
//				remove_filter( 'woocommerce_currency', array( $WOOCS, 'get_woocommerce_currency' ), 9999 );
//			}
//		} elseif ( class_exists( 'WOOCS' ) ) {
//			global $WOOCS;
//			$currency_code = $WOOCS->default_currency;
//			if ( $currency_code != $feedConfig['feedCurrency'] ) {
//				$WOOCS->set_currency( $feedConfig['feedCurrency'] );
//			} else {
//				$WOOCS->set_currency( $currency_code );
//			}
//		}
//
//		// RightPress dynamic pricing support.
//		add_filter( 'rightpress_product_price_shop_change_prices_in_backend', '__return_true', 999 );
//		add_filter( 'rightpress_product_price_shop_change_prices_before_cart_is_loaded', '__return_true', 999 );
//
//		// WooCommerce Out of Stock visibility override
//		if ( isset( $feedConfig['outofstock_visibility'] ) && '1' == $feedConfig['outofstock_visibility'] ) {
//			// just return false as wc expect the value should be 'yes' with eqeqeq (===) operator.
//			add_filter( 'pre_option_woocommerce_hide_out_of_stock_items', '__return_false', 999 );
//		}
//	}
}
if ( ! function_exists( 'woo_feed_pro_remove_hooks_after_product_loop' ) ) {
	/**
	 * Remove Applied Hooks Looping through ProductIds
	 *
	 * @param int[] $productIds
	 * @param array $feedConfig the feed array.
	 *
	 * @see woo_feed_apply_hooks_before_product_loop
	 */
//	function woo_feed_pro_remove_hooks_after_product_loop( $productIds, $feedConfig ) {
//		// Aelia Currency Switcher support.
//		global $woo_feed_get_single_feed_default_currency; //get previously saved currency (default currency)
//		$currency_code = ( ! empty( $feedConfig['feedCurrency'] ) ) ? $feedConfig['feedCurrency'] : get_woocommerce_currency();
//		$currency_code = $woo_feed_get_single_feed_default_currency;
//
//		add_filter(
//				'wc_aelia_cs_selected_currency',
//				function ( $selected_currency ) use ( $currency_code ) {
//					return $currency_code;
//				},
//				999
//		);
//
//		// WooCommerce Currency Switcher by realmag777 support.
//		if ( class_exists( 'WOOCS' ) ) {
//			global $WOOCS;
//			$currency_code = $WOOCS->default_currency;
//			$WOOCS->set_currency( $currency_code );
//		}
//
//		// RightPress dynamic pricing support.
//		remove_filter( 'rightpress_product_price_shop_change_prices_in_backend', '__return_true', 999 );
//		remove_filter( 'rightpress_product_price_shop_change_prices_before_cart_is_loaded', '__return_true', 999 );
//
//		// WooCommerce Out of Stock visibility override
//		if ( isset( $feedConfig['outofstock_visibility'] ) && '1' == $feedConfig['outofstock_visibility'] ) {
//			remove_filter( 'pre_option_woocommerce_hide_out_of_stock_items', '__return_false', 999 );
//		}
//	}
}

if ( ! function_exists( 'woo_feed_schedule_interval_options_extend' ) ) {
	function woo_feed_schedule_interval_options_extend( $intervals ) {
		if ( ! empty( $intervals ) && is_array( $intervals ) ) {
			$intervals += array(
					30 * MINUTE_IN_SECONDS => esc_html__( '30 Minutes', 'woo-feed' ),
					15 * MINUTE_IN_SECONDS => esc_html__( '15 Minutes', 'woo-feed' ),
					5 * MINUTE_IN_SECONDS  => esc_html__( '5 Minutes', 'woo-feed' ),
			);
		}

		return $intervals;
	}
}

if ( ! function_exists( 'woo_feed_filter_chosen_method_id' ) ) {
	/**
	 * Compatibility for Woocommerce Table Rate plugin by Woocommerce
	 *
	 * @link   https://woocommerce.com/products/table-rate-shipping/
	 * @author Nazrul Islam Nayan
	 * @since  5.2.100
	 *
	 * @param string $chosen_ship_method_id Chosen shipping method id (Example: flat_rate:1)
	 * @param array $shipping shipping data
	 * @param array $method shipping method
	 *
	 * @return string
	 */
	function woo_feed_filter_chosen_method_id( $chosen_ship_method_id, $shipping, $method ) {
		if ( ! isset( $method ) || empty( $method ) ) {
			return $chosen_ship_method_id;
		}

		// When Woocommerce Table Rate plugin by Woocommerce is active
		if ( is_plugin_active( 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php' ) ) {
			if ( isset( $method['id'] ) && $method['instance_id'] && 'table_rate' === $method['id'] ) {
				$chosen_ship_method_id = $method['id'] . ':' . $method['instance_id'] . ':' . $method['table_rate_id'];
			}
		}

		return $chosen_ship_method_id;
	}

	add_filter( 'woo_feed_filter_chosen_method_id', 'woo_feed_filter_chosen_method_id', 10, 3 );
}


// Multi Vendor...
// @TODO Move hooks to hooks.php.
if ( woo_feed_is_multi_vendor() ) {
	if ( ! function_exists( 'woo_feed_add_view_feeds_tab_menu' ) ) {
		/**
		 * Add View Feed Tabs to My Account
		 *
		 * @param $menu_links
		 *
		 * @return array
		 */
		function woo_feed_add_view_feeds_tab_menu( $menu_links ) {
			if ( woo_feed_is_multi_vendor() ) {
				$menu_links = array_slice( $menu_links, 0, 5, true ) + array( 'view-feeds' => 'Product Feed' ) + array_slice( $menu_links, 5, 1, true );
			}

			return $menu_links;
		}

		add_filter( 'woocommerce_account_menu_items', 'woo_feed_add_view_feeds_tab_menu' );
	}

	if ( ! function_exists( 'woo_feed_add_endpoint_for_view_feeds_menu' ) ) {
		function woo_feed_add_endpoint_for_view_feeds_menu() {
			add_rewrite_endpoint( 'view-feeds', EP_PAGES );
		}

		add_action( 'init', 'woo_feed_add_endpoint_for_view_feeds_menu' );
	}
	if ( ! function_exists( 'woo_feed_view_vendor_feeds_endpoint_add_content' ) ) {
		function woo_feed_view_vendor_feeds_endpoint_add_content() {
			$result = woo_feed_get_cached_data( 'woo_feed_get_feeds' );
			if ( false === $result ) {
				global $wpdb;
				$query  = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", 'wf_feed_%' );
				$result = $wpdb->get_results( $query, 'ARRAY_A' ); // phpcs:ignore
				woo_feed_set_cache_data( 'woo_feed_get_feeds', $result );
			}
			$user_id = get_current_user_id();
			?>
			<div>
				<table class="table table-responsive">
					<thead>
					<tr>
						<th style="width: 20%;"><?php esc_html_e( 'Feed Name', 'woo-feed' ); ?></th>
						<th><?php esc_html_e( 'Feed Link', 'woo-feed' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Actions', 'woo-feed' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( empty( $result ) || ! is_array( $result ) ) { ?>
						<tr>
							<td colspan="3"
								style="text-align: center;"><?php esc_html_e( 'No Feed Available', 'woo-feed' ); ?></td>
						</tr>
						<?php
					} else {
						foreach ( $result as $feed ) {
							$info = maybe_unserialize( get_option( $feed['option_name'] ) );
							if ( isset( $info['feedrules']['vendors'] ) ) {
								if ( in_array( $user_id, $info['feedrules']['vendors'] ) ) {
									$fileName = $info['feedrules']['filename'];
									$fileURL  = $info['url'];
									?>
									<tr>
										<td><?php echo esc_html( $fileName ); ?></td>
										<td style="color: rgb(0, 135, 121); font-weight: bold;"><?php echo esc_html( $fileURL ); ?></td>
										<td><a href="<?php echo esc_url( $fileURL ); ?>" class="button button-primary"
											   target="_blank"><?php esc_html_e( 'View/Download', 'woo-feed' ); ?></a>
										</td>
									</tr>
									<?php
								}
							}
						}
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
		}

		add_action( 'woocommerce_account_view-feeds_endpoint', 'woo_feed_view_vendor_feeds_endpoint_add_content' );
	}
	if ( ! function_exists( 'woo_feed_add_rewrite_rules_for_view_feeds_tab' ) ) {
		function woo_feed_add_rewrite_rules_for_view_feeds_tab( $wp_rewrite ) {
			$feed_rules = array(
					'my-account/?$' => 'index.php?account-page=true',
			);

			$wp_rewrite->rules = $wp_rewrite->rules + $feed_rules;

			return $wp_rewrite->rules;
		}

		add_filter( 'generate_rewrite_rules', 'woo_feed_add_rewrite_rules_for_view_feeds_tab' );
	}
}

if ( ! function_exists( 'woo_feed_get_curreny_fixed_price' ) ) {
	function woo_feed_get_curreny_fixed_price( $price, $product, $config, $regular_price, $sale_price , $price_type ){
		if ( $price_type === 'price' && ! empty( $regular_price ) ) {
			$price = $regular_price[ $config['feedCurrency'] ];
		} else if ( $price_type === 'sale_price' && ! empty( $sale_price ) ) {
			$price = $sale_price[ $config['feedCurrency'] ];
		}

		return $price;
	}
}

if ( ! function_exists( 'woo_feed_filter_price_for_currency_switcher' ) ) {
	/**
	 * Currency Convert for Currency Switcher
	 *
	 * @param float $price Product Price
	 * @param WC_Product $product Product Object
	 * @param array $config Feed Config
	 *
	 * @return float|string
	 */
	function woo_feed_filter_price_for_currency_switcher( $price, $product, $config ) {
		$decimal      = isset( $config['decimals'] ) && ! empty( $config['decimals'] ) ? $config['decimals'] : get_option( 'woocommerce_price_num_decimals' );
		$decimal_sep  = isset( $config['decimal_separator'] ) && ! empty( $config['decimal_separator'] ) ? $config['decimal_separator'] : get_option( 'woocommerce_price_decimal_sep' );
		$thousand_sep = isset( $config['thousand_separator'] ) && ! empty( $config['thousand_separator'] ) ? $config['thousand_separator'] : get_option( 'woocommerce_price_thousand_sep' );

		// when currency switcher plugin by wp wham exists
		if ( is_plugin_active( 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' ) ) {
			if ( $config['feedCurrency'] !== get_woocommerce_currency() ) {

				if ( ! empty( $price ) ) {
					$price = alg_get_product_price_by_currency( $price, $config['feedCurrency'] );
					$price = number_format( $price, $decimal, $decimal_sep, $thousand_sep );
				}
			}
		} elseif ( is_plugin_active( 'woocommerce-multicurrency/woocommerce-multicurrency.php' ) ) {
			// compatibility with Woocommerce Multi Currency by TIV.NET INC
			if ( $config['feedCurrency'] !== get_woocommerce_currency() ) {
				$currency = get_woocommerce_currency();

				if ( ! empty( $price ) && class_exists( '\WOOMC\API' ) ) {
					$default_currency = \WOOMC\API::default_currency();
					$price            = \WOOMC\API::convert( $price, $config['feedCurrency'], $currency );
				}
			}
		}

		// when WooCommerce Multi Currency plugin by VillaTheme exists
		if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
			$price = $main_price = wmc_get_price( $price, $config['feedCurrency'] );
			$wmc_currency_params = get_option( 'woo_multi_currency_params' );

			$regular_price = wmc_adjust_fixed_price( json_decode( get_post_meta( $product->get_id(), '_regular_price_wmcp', true ), true ) );
			$sale_price    = wmc_adjust_fixed_price( json_decode( get_post_meta( $product->get_id(), '_sale_price_wmcp', true ), true ) );

			$woocommerce_currency = get_option( 'woocommerce_currency' );

			if( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
				if ( $config['feedCurrency'] !== $woocommerce_currency ) {
					if( isset( $wmc_currency_params['enable_fixed_price'] ) && $wmc_currency_params['enable_fixed_price'] == 1 ) {
						$price = woo_feed_get_curreny_fixed_price( $price, $product, $config , $regular_price , $sale_price, 'price');
						$price = ! $price ? $main_price : $price;
					}
				}
			}

			if( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
				if ( $config['feedCurrency'] !== $woocommerce_currency ) {
					if( isset( $wmc_currency_params['enable_fixed_price'] ) && $wmc_currency_params['enable_fixed_price'] == 1 ) {
						$price = woo_feed_get_curreny_fixed_price( $price, $product, $config , $regular_price , $sale_price, 'price');
						$price = ! $price ? $main_price : $price;
					}
				}
			}
		}

		return $price;
	}
	// Move to CTXFeed\V5\Compatibility\MultiCurrency
	//add_filter( 'woo_feed_filter_product_regular_price', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );
	//add_filter( 'woo_feed_filter_product_price', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );

	//add_filter( 'woo_feed_filter_product_sale_price', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );

	// Move to CTXFeed\V5\Compatibility\MultiCurrency
	//add_filter( 'woo_feed_filter_product_sale_price', 'woo_feed_filter_sale_price_for_currency_switcher', 10, 3 );
	//add_filter( 'woo_feed_filter_product_regular_price_with_tax', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );
	//add_filter( 'woo_feed_filter_product_price_with_tax', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );
	//add_filter( 'woo_feed_filter_product_sale_price_with_tax', 'woo_feed_filter_price_for_currency_switcher', 10, 3 );
}

if ( ! function_exists( 'woo_feed_filter_sale_price_for_currency_switcher' ) ) {
	/**
	 * Currency Convert for Currency Switcher
	 *
	 * @param float $price Product Price
	 * @param WC_Product $product Product Object
	 * @param array $config Feed Config
	 *
	 * @return float|string
	 */
	function woo_feed_filter_sale_price_for_currency_switcher( $price, $product, $config ) {
		$decimal      = isset( $config['decimals'] ) && ! empty( $config['decimals'] ) ? $config['decimals'] : get_option( 'woocommerce_price_num_decimals' );
		$decimal_sep  = isset( $config['decimal_separator'] ) && ! empty( $config['decimal_separator'] ) ? $config['decimal_separator'] : get_option( 'woocommerce_price_decimal_sep' );
		$thousand_sep = isset( $config['thousand_separator'] ) && ! empty( $config['thousand_separator'] ) ? $config['thousand_separator'] : get_option( 'woocommerce_price_thousand_sep' );

		// when currency switcher plugin by wp wham exists
		if ( is_plugin_active( 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' ) ) {
			if ( $config['feedCurrency'] !== get_woocommerce_currency() ) {

				if ( ! empty( $price ) ) {
					$price = alg_get_product_price_by_currency( $price, $config['feedCurrency'] );
					$price = number_format( $price, $decimal, $decimal_sep, $thousand_sep );
				}
			}
		} elseif ( is_plugin_active( 'woocommerce-multicurrency/woocommerce-multicurrency.php' ) ) {
			// compatibility with Woocommerce Multi Currency by TIV.NET INC
			if ( $config['feedCurrency'] !== get_woocommerce_currency() ) {
				$currency = get_woocommerce_currency();

				if ( ! empty( $price ) && class_exists( '\WOOMC\API' ) ) {
					$default_currency = \WOOMC\API::default_currency();
					$price            = \WOOMC\API::convert( $price, $config['feedCurrency'], $currency );
				}
			}
		}

		// when WooCommerce Multi Currency plugin by VillaTheme exists
		if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
			$price = $main_price = wmc_get_price( $price, $config['feedCurrency'] );

			$wmc_currency_params = get_option( 'woo_multi_currency_params' );

			$regular_price = wmc_adjust_fixed_price( json_decode( get_post_meta( $product->get_id(), '_regular_price_wmcp', true ), true ) );
			$sale_price    = wmc_adjust_fixed_price( json_decode( get_post_meta( $product->get_id(), '_sale_price_wmcp', true ), true ) );

			$woocommerce_currency = get_option( 'woocommerce_currency' );

			if( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
				if ( $config['feedCurrency'] !== $woocommerce_currency ) {
					if( isset( $wmc_currency_params['enable_fixed_price'] ) && $wmc_currency_params['enable_fixed_price'] == 1 ) {
						$price = woo_feed_get_curreny_fixed_price( $price, $product, $config , $regular_price , $sale_price, 'sale_price');
						$price = ! $price ? $main_price : $price;
					}
				}
			}

			if( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
				if ( $config['feedCurrency'] !== $woocommerce_currency ) {
					if( isset( $wmc_currency_params['enable_fixed_price'] ) && $wmc_currency_params['enable_fixed_price'] == 1 ) {
						$price = woo_feed_get_curreny_fixed_price( $price, $product, $config , $regular_price , $sale_price, 'sale_price');
						$price = ! $price ? $main_price : $price;
					}
				}
			}

		}

		return ( $price > 0 ) ? $price : '';
	}
	// Move to CTXFeed\V5\Compatibility\MultiCurrency
	//add_filter( 'woo_feed_filter_product_sale_price', 'woo_feed_filter_sale_price_for_currency_switcher', 10, 3 );

}

/**
 * Find default product variation id
 *
 * @param WC_Product $product
 *
 * @return int Matching variation ID or 0.
 * @throws Exception
 */
function woo_feed_default_product_variation( $product ) {

	if ( $product->is_type( 'variable' ) ) {
		$attributes = $product->get_default_attributes();
		foreach ( $attributes as $key => $value ) {
			if ( strpos( $key, 'attribute_' ) === 0 ) {
				continue;
			}

			unset( $attributes[ $key ] );
			$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
		}

		return class_exists( 'WC_Data_Store' ) ? WC_Data_Store::load( 'product' )->find_matching_product_variation( $product, $attributes ) : $product->get_matching_variation( $attributes );

	}

	return false;
}

if ( ! function_exists( 'woo_feed_custom_field_meta_filter' ) ) {
	/**
	 * Identifier meta value filter for old and new version users
	 *
	 * @param            $meta  string Default Meta
	 * @param WC_Product $product
	 * @param            $field string Meta field
	 *
	 * @return string Custom Field Meta.
	 */
	function woo_feed_custom_field_meta_filter( $meta, $product, $field ) {
		$id = $product->get_id();

		//identifier meta value for old and new version users
		if ( false !== strpos( $meta, 'woo_feed_identifier_' ) ) {

			$identifier = str_replace( 'woo_feed_identifier_', '', $meta );
			if ( metadata_exists( 'post', $id, 'woo_feed_' . $identifier ) ) {
				$meta = 'woo_feed_' . $identifier;
			} else {
				$meta = 'woo_feed_identifier_' . $identifier;
			}
		}

		if ( false !== strpos( $meta, Woo_Feed_Products_v3_Pro::POST_META_PREFIX ) ) {
			$meta = str_replace( Woo_Feed_Products_v3_Pro::POST_META_PREFIX, '', $meta );
		}

		return $meta;
	}

	add_filter( 'woo_feed_custom_field_meta', 'woo_feed_custom_field_meta_filter', 3, 10 );
}


if ( ! function_exists( 'woo_feed_pro_output_types' ) ) {
	/**
	 * Pro Output Types for pro filter attribute
	 *
	 * @param array $output_types
	 *
	 * @return array
	 * @since 5.2.84
	 */
	function woo_feed_pro_output_types( $output_types ) {

		//when wpml or polylang plugin is activated
		if (
				( class_exists( 'SitePress', false ) || function_exists( 'PLL' ) ) // When WPML is active
				|| is_plugin_active( 'translatepress-business/index.php' ) // Translatepress
		) {
			array_push( $output_types, 'parent_lang' );
			array_push( $output_types, 'parent_lang_if_empty' );
		}

		return $output_types;
	}

	add_filter( 'woo_feed_output_types', 'woo_feed_pro_output_types' );
}

if ( ! function_exists( 'woo_feed_get_parent_lang_product_id_when_output_empty' ) ) {
	/**
	 * Get parent language product id when output is empty
	 *
	 * @param string $output Attribute value
	 * @param WC_Product $product Product Object
	 * @param string $productAttribute Product Attribute
	 * @param string $merchant_attribute Merchant Attribute
	 *
	 * @return string
	 * @since 5.2.84
	 */
	function woo_feed_get_parent_lang_product_id_when_output_empty( $output, $product, $productAttribute, $merchant_attribute ) {
		$id = $product->get_id();

		//when attribute value is empty
		if ( empty( $output ) ) {
			//when wpml plugin is activated, get parent language post id
			if ( class_exists( 'SitePress', false ) ) {
				$parent_id = woo_feed_wpml_get_original_post_id( $id );
			}

			// when polylang plugin is activated, get parent language post id
			if ( function_exists( 'pll_get_post_translations' ) ) {
				$polylang_post   = pll_get_post_translations( $id );
				$defaultLanguage = pll_default_language();
				if ( isset( $polylang_post[ $defaultLanguage ] ) ) {
					$parent_id = $polylang_post[ $defaultLanguage ];
				}
			}
		}

		return ! empty( $parent_id ) ? $parent_id : $id;
	}
}

if ( ! function_exists( 'woo_feed_insert_after_key' ) ) {

	/**
	 * Inserts a key value pair after a specific key. If the key is not found then it is inserted at the end of the array.
	 *
	 * @param string $after_key
	 * @param Array $array
	 * @param string $key
	 * @param string|mixed|array $value
	 *
	 * @return Array $array
	 * @since 5.2.92
	 */
	function woo_feed_insert_after_key( $after_key, $array, $key, $value ) {

		$new_array = array();

		foreach ( $array as $_key => $_value ) {

			$new_array[ $_key ] = $_value;
			if ( $_key == $after_key ) {
				$new_array[ $key ] = $value;
			}
		}

		return $new_array;

	}
}


if ( ! function_exists( 'woo_feed_pro_dynamic_attribute_ui_review' ) ) {
	/**
	 * CTX Feed Pro UI Dynamic attribute review notice
	 *
	 * @since  5.4.15
	 * @author Azizul Hasan
	 */
	function woo_feed_pro_dynamic_attribute_ui_review() {

//		delete_option('woo_feed_notice_next_show_time');
//		delete_user_meta(get_current_user_id(), 'woo_feed_notice_dismissed');
		$pluginName              = sprintf( '<b>%s</b>', esc_html__( 'CTX Feed Pro', 'woo-feed' ) );
		$has_notice              = false;
		$user_id                 = get_current_user_id();
		$next_timestamp          = get_option( 'woo_feed_notice_next_show_time' );
		$review_notice_dismissed = get_user_meta( $user_id, 'woo_feed_notice_dismissed', true );
		$nonce                   = wp_create_nonce( 'woo_feed_notice_nonce' );
		if ( ! empty( $next_timestamp ) ) {
			if ( ( time() > $next_timestamp ) ) {
				$show_notice = true;
			} else {
				$show_notice = false;
			}
		} else {
			if ( isset( $review_notice_dismissed ) && ! empty( $review_notice_dismissed ) ) {
				$show_notice = false;
			} else {
				$show_notice = true;
			}
		}
		// Review Notice.
		if ( $show_notice ) {
			$has_notice = true;
			?>
			<div class="woo-feed-notice woo-feed-notice-pro notice notice-info is-dismissible" style="line-height:1.5;"
				 data-which="rating" data-nonce="<?php echo esc_attr( $nonce ); ?>">
				<p><?php
					printf(
					/* translators: 1: plugin name,2: Slightly Smiling Face (Emoji), 3: line break 'br' tag */
							esc_html__( '%3$s %2$s Hey, we’ve just updated the UI/UX of the %1$s plugin. If you have any feedback, suggestions, or issue with this, please feel free to let us know..', 'woo-feed' ),
							$pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'<span style="font-size: 16px;">&#128578;</span>',
							'<div class="woo-feed-review-notice-logo"></div>',
							'<br>'
					);
					?></p>
				<p>
					<a class="button button-primary" data-response="given" href="#"
					   target="_blank"><?php esc_html_e( 'Review Now', 'woo-feed' ); ?></a>
					<a class="button button-secondary" data-response="later"
					   href="#"><?php esc_html_e( 'Remind Me Later', 'woo-feed' ); ?></a>
					<a class="button button-secondary" data-response="done" href="#"
					   target="_blank"><?php esc_html_e( 'Already Done!', 'woo-feed' ); ?></a>
					<a class="button button-secondary" data-response="never"
					   href="#"><?php esc_html_e( 'Never Ask Again', 'woo-feed' ); ?></a>
				</p>
			</div>
			<?php
		}

		if ( true === $has_notice ) {
			add_action( 'admin_print_footer_scripts', function () use ( $nonce ) {
				?>
				<script>
					(function ($) {
						"use strict";
						$(document)
							.on('click', '.woo-feed-notice a.button', function (e) {
								e.preventDefault();
								// noinspection ES6ConvertVarToLetConst
								var self = $(this), notice = self.attr('data-response');
								if ('given' === notice) {
									window.open('https://webappick.com/my-account/contact-support/', '_blank');
								}
								self.closest(".woo-feed-notice").slideUp(200, 'linear');
								wp.ajax.post('woo_feed_save_review_notice', {
									_ajax_nonce: '<?php echo esc_attr( $nonce ); ?>',
									notice: notice
								});
							})

							.on('click', '.woo-feed-notice .notice-dismiss', function (e) {
								e.preventDefault();
								// noinspection ES6ConvertVarToLetConst
								var self = $(this), feed_notice = self.closest('.woo-feed-notice'),
									which = feed_notice.attr('data-which');
								wp.ajax.post('woo_feed_hide_notice', {
									_wpnonce: '<?php echo esc_attr( $nonce ); ?>',
									which: which
								});
							});
					})(jQuery)
				</script><?php
			}, 99 );
		}
	}
}


if ( ! function_exists( 'woo_feed_hide_notice' ) ) {
	/**
	 * Update user meta to work ctx pro ui reveiw notice notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since  5.4.15
	 * @author Azizul Hasan
	 */
	function woo_feed_hide_notice() {
		check_ajax_referer( 'woo_feed_notice_nonce' );
		$notices = [ 'rating', ];
		if ( isset( $_REQUEST['which'] ) && ! empty( $_REQUEST['which'] ) && in_array( $_REQUEST['which'], $notices ) ) {
			$user_id = get_current_user_id();

			if ( 'rating' == $_REQUEST['which'] ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_feed_notice_dismissed', true, true );
				update_option( 'woo_feed_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 ) );
			}

			if ( isset( $updated_user_meta ) && $updated_user_meta ) {
				wp_send_json_success( esc_html__( 'Request Successful.', 'woo-feed' ) );
			} else {
				wp_send_json_error( esc_html__( 'Something is wrong.', 'woo-feed' ) );
			}
			wp_die();
		}
		wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		wp_die();
	}
}
if ( ! function_exists( 'woo_feed_save_review_notice' ) ) {
	/**
	 * Update user meta to work ctx pro startup notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since  5.4.15
	 * @author Azizul Hasan
	 */
	function woo_feed_save_review_notice() {
		check_ajax_referer( 'woo_feed_notice_nonce' );
		$user_id = get_current_user_id();
		update_option('review_test', 'review');
		$review_actions = [ 'later', 'never', 'done', 'given' ];
		if ( isset( $_POST['notice'] ) && ! empty( $_POST['notice'] ) && in_array( $_POST['notice'], $review_actions ) ) {
			$value  = [
					'review_notice' => sanitize_text_field( $_POST['notice'] ), //phpcs:ignore
					'updated_at'    => time(),
			];
			if ( 'never' === $_POST['notice'] || 'done' === $_POST['notice'] || 'given' === $_POST['notice'] ) {

				add_user_meta( $user_id, 'woo_feed_notice_dismissed', true, true );

				update_option( 'woo_feed_notice_next_show_time', 0 );

			}elseif ( 'later' == $_POST['notice'] ) {

				add_user_meta( $user_id, 'woo_feed_notice_dismissed', true, true );

				update_option( 'woo_feed_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 ) );
			}
			update_option( 'woo_feed_ui_review_notice', $value );
			wp_send_json_success( $value );
			wp_die();
		}
		wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		wp_die();
	}
}
add_action( 'wp_ajax_woo_feed_hide_notice',  'woo_feed_hide_notice' );
add_action( 'wp_ajax_woo_feed_save_review_notice',  'woo_feed_save_review_notice' );

if ( ! function_exists( 'woo_feed_pro_black_friday_notice' ) ) {
	/**
	 * CTX Feed Pro Black Friday Notice
	 *
	 * @since  5.2.102
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_pro_black_friday_notice() {
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'woo_feed_pro_black_friday_notice_2021_dismissed' ) ) {
			ob_start();
			?>
			<script type="text/javascript">
				(function ($) {
					$(document).on('click', '.woo-feed-pro-black-friday-notice button.notice-dismiss', function (e) {
						e.preventDefault();
						let nonce = $('#woo_feed_pro_notice_nonce').val();

						//woo feed pro black friday notice cancel callback
						wp.ajax.post('woo_feed_pro_save_black_friday_notice_2021', {
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
			<a href="https://webappick.com/plugin/woocommerce-product-feed-pro/?utm_source=proPlugin&utm_medium=go_premium&utm_campaign=black_friday_2021&utm_term=wooFeed"
			   class="notice woo-feed-pro-black-friday-notice is-dismissible"
			   style="background: url(<?php echo WOO_FEED_PRO_ADMIN_URL . 'images/ctx-feed-pro-black-friday-banner-2021.png'; ?>) no-repeat top center;">
				<input type="hidden" id="woo_feed_pro_notice_nonce"
					   value="<?php echo wp_create_nonce( 'woo-feed-pro-notice-nonce' ); ?>">
			</a>
			<?php
			$image = ob_get_contents();
		}
	}
}

if ( ! function_exists( 'woo_feed_pro_save_black_friday_notice_2021' ) ) {
	/**
	 * Update user meta to work ctx pro startup notice once.
	 *
	 * @param int _ajax_nonce nonce number.
	 *
	 * @since  5.2.102
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_pro_save_black_friday_notice_2021() {
		if ( isset( $_REQUEST['_wp_ajax_nonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wp_ajax_nonce'] ), 'woo-feed-pro-notice-nonce' ) ) { //phpcs:ignore
			$user_id = get_current_user_id();
			if ( isset( $_REQUEST['clicked'] ) ) {
				$updated_user_meta = add_user_meta( $user_id, 'woo_feed_pro_black_friday_notice_2021_dismissed', 'true', true );

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
add_action( 'wp_ajax_woo_feed_pro_save_black_friday_notice_2021', 'woo_feed_pro_save_black_friday_notice_2021' );

if ( ! function_exists( 'woo_feed_filter_plugin_pages_slugs' ) ) {
	/**
	 * Filter Woo Feed Plugin Pages Slugs
	 *
	 * @return array
	 * @since  5.2.103
	 * @author Nazrul Islam Nayan
	 */
	function woo_feed_filter_plugin_pages_slugs( $pages ) {
		if ( empty( $pages ) ) {
			return $pages;
		}

		$pro_pages_slugs = array(
				'webappick-manage-feed-attribute',
				'webappick-manage-attribute-mapping',
				'woo-feed-pro-beta-manage-license',
		);
		array_push( $pages, $pro_pages_slugs );

		return $pages;
	}

	add_filter( 'woo_feed_plugin_pages_slugs', 'woo_feed_filter_plugin_pages_slugs' );
}

// WordPress backward compatability and polyfill
if ( ! function_exists( 'readonly' ) ) {

	/**
	 * Outputs the HTML readonly attribute.
	 *
	 * Compares the first two arguments and if identical marks as readonly
	 *
	 * @param mixed $readonly One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 *
	 * @return string HTML attribute or empty string
	 * @since 5.2.97
	 *
	 */
	function readonly( $readonly, $current = true, $echo = true ) {
		return __checked_selected_helper( $readonly, $current, $echo, 'readonly' );
	}
}

if ( ! function_exists( 'woo_feed_make_feed_big_data' ) ) {
	function woo_feed_make_feed_big_data( $data, $ids, $config ) {

		//setup feed shipping data @TODO: need to make a class when another data setup will be added
		if ( isset( $config['attributes'] ) && in_array( 'shipping', $config['attributes'] ) ) {
			if ( class_exists( 'WC_Shipping_Zones' ) ) {
				$data['shipping_zones'] = WC_Shipping_Zones::get_zones();
			}
		}

		return $data;

	}

	add_filter( 'woo_feed_feed_big_data', 'woo_feed_make_feed_big_data', 10, 3 );
}

if ( ! function_exists( 'woo_feed_after_wc_product_structured_data' ) ) {
	function woo_feed_after_wc_product_structured_data( $markup, $product ) {

		if ( is_plugin_active( 'woocommerce-currency-switcher/index.php' ) && class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$woocs_currencies          = $WOOCS->get_currencies();
			$previous_default_currency = $WOOCS->current_currency;
			$i                         = 0;
			foreach ( $woocs_currencies as $woocs_currency ) {
				$currency_name           = $woocs_currency['name'];
				$WOOCS->current_currency = $currency_name;

				$converted_price = $product->get_price();

				$markup['offers'][ $i ]['@type']                               = 'Offer';
				$markup['offers'][ $i ]['price']                               = $converted_price;
				$markup['offers'][ $i ]['priceSpecification']['price']         = $converted_price;
				$markup['offers'][ $i ]['priceSpecification']['priceCurrency'] = $currency_name;
				$markup['offers'][ $i ]['priceCurrency']                       = $currency_name;

				if ( isset( $markup['offers'][0] ) && is_array( $markup['offers'][0] ) ) {
					$markup['offers'][ $i ]['priceValidUntil']                             = $markup['offers'][0]['priceValidUntil'];
					$markup['offers'][ $i ]['availability']                                = $markup['offers'][0]['availability'];
					$markup['offers'][ $i ]['url']                                         = $markup['offers'][0]['url'];
					$markup['offers'][ $i ]['priceSpecification']['valueAddedTaxIncluded'] = $markup['offers'][0]['priceSpecification']['valueAddedTaxIncluded'];
				}

				$i ++;
			}

			$WOOCS->current_currency = $previous_default_currency;
		}

		return $markup;

	}

	add_filter( 'woo_feed_after_wc_product_structured_data', 'woo_feed_after_wc_product_structured_data', 10, 2 );
}

if ( ! function_exists( 'woo_feed_allowed_shipping_countries_callback' ) ) {

	/**
	 * @param $settings
	 * @param $config
	 *
	 * @return mixed|string
	 */
	function woo_feed_allowed_shipping_countries_callback( $settings, $config ) {
		if ( ! isset( $config['shipping_country'] ) ) {
			return $settings;
		}

		if ( empty( $config['shipping_country'] ) ) {
			return $settings;
		}

		if ( $config['shipping_country'] === 'all' ) {
			$settings = 'yes';
		}

		if ( $config['shipping_country'] === 'feed' ) {
			$settings = 'no';
		}

		return $settings;
	}
}

if ( ! function_exists( 'woo_feed_allowed_tax_countries_callback' ) ) {

	/**
	 * @param $settings
	 * @param $config
	 *
	 * @return mixed|string
	 */
	function woo_feed_allowed_tax_countries_callback( $settings, $config ) {

		if ( ! isset( $config['tax_country'] ) ) {
			return $settings;
		}
		if ( empty( $config['tax_country'] ) ) {
			return $settings;
		}

		if ( $config['tax_country'] === 'all' ) {
			$settings = 'yes';
		}

		if ( $config['tax_country'] === 'feed' ) {
			$settings = 'no';
		}

		return $settings;
	}
}
if ( ! function_exists( 'woo_feed_notice_to_include_hidden_products_from_feed' ) ) {

	/**
	 * @param $settings
	 * @param $config
	 *
	 * @return mixed|string
	 */
	function woo_feed_notice_to_include_hidden_products_from_feed() {

		$type = 'include_hidden_products_from_feed';

		$hidden_products = woo_feed_hidden_products_count();

		if ( $hidden_products > 0 ) {
			$notice_data = Woo_Feed_Notices::get_woo_feed_notice_data();
			Woo_Feed_Notices::add_update_woo_feed_notice_data( $type, $notice_data );
		}
		else {
			Woo_Feed_Notices::update_woo_feed_notice_dismiss( $type, true );
		}

	}
}


if( ! function_exists('get_plugin_file')){
	/**
	 * @return false|mixed|string
	 */
	function get_plugin_file() {
		return WOO_FEED_PLUGIN_FILE;
	}
}
// End of file pro-helper.php.
