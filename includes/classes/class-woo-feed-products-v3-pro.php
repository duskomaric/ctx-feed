<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUnused, PhpUnusedLocalVariableInspection, DuplicatedCode */
/**
 * Product V3 Pro
 *
 * @author    Kudratullah <mhamudul.hk@gmail.com>
 * @copyright 2020 WebAppick
 * @package   WooFeed/Pro
 * @version   1.0.1
 * @since     WooFeed 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Woo_Feed_Products_v3_Pro
 */
class Woo_Feed_Products_v3_Pro extends Woo_Feed_Products_v3 {

	/**
	 * Advance Custom Field (ACF) Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ACF_FIELDS = 'acf_fields_';


	/**
	 * Categories to include or exclude [Filter]
	 *
	 * @var array
	 */
	protected $categories = array();
	/**
	 * Vendors to include [Filter]
	 *
	 * @var array
	 */
	protected $vendors = array();
	/**
	 * Product Ids to include [Filter]
	 *
	 * @var array
	 */
	protected $ids_in = array();
	/**
	 * Product Ids to exclude [Filter]
	 *
	 * @var array
	 */
	protected $ids_not_in = array();

	/**
	 * Post meta prefix for dropdown item
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const POST_META_PREFIX = 'wf_cattr_';
	/**
	 * Product Attribute (taxonomy & local) Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ATTRIBUTE_PREFIX = 'wf_attr_';
	/**
	 * Product Taxonomy Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_TAXONOMY_PREFIX = 'wf_taxo_';
	/**
	 * Product Category Mapping Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_CATEGORY_MAPPING_PREFIX = 'wf_cmapping_';
	/**
	 * Product Dynamic Attribute Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX = 'wf_dattribute_';
	/**
	 * WordPress Option Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const WP_OPTION_PREFIX = 'wf_option_';

	/**
	 * Extra Attribute Prefix
	 *
	 * @since 3.2.20
	 */
	const PRODUCT_EXTRA_ATTRIBUTE_PREFIX = 'wf_extra_';

	/**
	 * Product Attribute Mappings Prefix
	 *
	 * @since 3.3.2*
	 */
	const PRODUCT_ATTRIBUTE_MAPPING_PREFIX = 'wp_attr_mapping_';

	/**
	 * Stores currently processing attribute key
	 *
	 * @since 5.2.91
	 */
	public $attribute_key;

	/**
	 * Woo_Feed_Products_v3_Pro constructor.
	 *
	 * @param $config
	 *
	 * @return void
	 */
	public function __construct( $config ) {
		parent::__construct( $config );
		$this->config    = woo_feed_parse_feed_rules( $config );
		$this->queryType = woo_feed_get_options( 'product_query_type' );
		$this->process_xml_wrapper();

		$this->inExProducts();
		// TODO move this sanitization[s] to config save function.
		if ( ! empty( $this->config['categories'] ) ) {
			//$this->categories = array_map( 'sanitize_text_field', (array) $this->config['categories'] );
			$this->categories = (array) $this->config['categories'];
		}
		if ( ! empty( $this->config['vendors'] ) ) {
			$this->vendors = array_map( 'sanitize_text_field', (array) $this->config['vendors'] );
		}

		if ( ! empty( $this->config['post_status'] ) ) {
			$this->config['post_status'] = array_map( 'sanitize_text_field', (array) $this->config['post_status'] );
			$statuses                    = array_keys( woo_feed_get_post_statuses() );
			// check if status is allowed.
			if ( count( array_diff( $this->config['post_status'], $statuses ) ) === 0 ) {
				if ( 'include' === $this->config['filter_mode']['post_status'] ) {
					$this->post_status = $this->config['post_status'];
				} else {
					$this->post_status = array_diff( $statuses, $this->config['post_status'] );
				}
			}
		}

		woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Current Query Type is %s', $this->queryType ) );
	}

	/**
	 *  Get Ids to Exclude or Include from product list
	 *
	 * @since 2.2.0
	 * @for WC 3.1+
	 */
	public function inExProducts() {
		// Argument for Product search by ID
		if ( isset( $this->config['fattribute'] ) && is_array( $this->config['fattribute'] ) ) {
			if ( count( $this->config['fattribute'] ) ) {
				$condition = $this->config['condition'];
				$compare   = $this->config['filterCompare'];
				foreach ( $this->config['fattribute'] as $key => $rule ) {
					if ( 'id' == $rule && in_array( $condition[ $key ], array( '==', 'contain' ) ) ) {

						unset( $this->config['fattribute'][ $key ], $this->config['condition'][ $key ], $this->config['filterCompare'][ $key ] );

						if ( false !== strpos( $compare[ $key ], ',' ) ) {
							foreach ( explode( ',', $compare[ $key ] ) as $k => $id ) {
								array_push( $this->ids_in, $id );
							}
						} else {
							array_push( $this->ids_in, $compare[ $key ] );
						}
					} elseif ( 'id' == $rule && in_array( $condition[ $key ], array( '!=', 'nContains' ) ) ) {
						unset( $this->config['fattribute'][ $key ] );
						unset( $this->config['condition'][ $key ] );
						unset( $this->config['filterCompare'][ $key ] );
						if ( false !== strpos( $compare[ $key ], ',' ) ) {
							foreach ( explode( ',', $compare[ $key ] ) as $k => $id ) {
								array_push( $this->ids_not_in, $id );
							}
						} else {
							array_push( $this->ids_not_in, $compare[ $key ] );
						}
					}
				}
			}
		}
		if ( ! empty( $this->config['product_ids'] ) ) {
			$this->config['product_ids'] = explode( ',', $this->config['product_ids'] );
			$this->config['product_ids'] = array_map( 'absint', $this->config['product_ids'] );
			$this->config['product_ids'] = array_filter( $this->config['product_ids'] );
			if ( ! empty( $this->config['product_ids'] ) ) {
				if ( 'include' === $this->config['filter_mode']['product_ids'] ) {
					$this->ids_in = array_merge( $this->ids_in, $this->config['product_ids'] );
				} else {
					$this->ids_not_in = array_merge( $this->ids_not_in, $this->config['product_ids'] );
				}
			}
		}

		if ( ! empty( $this->ids_in ) ) {
			$this->ids_in = array_unique( $this->ids_in );
		}
		if ( ! empty( $this->ids_not_in ) ) {
			$this->ids_not_in = array_unique( $this->ids_not_in );
		}
	}

	/**
	 * Generate Query Args For WP/WC query class
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	protected function get_query_args( $type = 'wc' ) {

		$args = array();

		// Include Product Variations with db query if configured
		$variation_query = woo_feed_get_options( 'variation_query_type' );
		if ( 'wc' === $type ) {
			$product_types = $this->product_types;
			if ( 'variable' === $variation_query ) {
				$skip_variations = true;
			} else {
				if ( isset( $this->config['is_variations'] ) && ! in_array( $this->config['is_variations'], array( 'both', 'y' ) ) ) {
					$skip_variations = true;
				} else {
					$skip_variations = false;
				}
			}

			//unset all variations on product types
			if ( $skip_variations ) {
				$variation = array_search( 'variation', $product_types, true );
				if ( $variation ) {
					unset( $product_types[ $variation ] );
				}
			}

			$args = array(
				'limit'            => - 1,
				'offset'           => 0,
				'status'           => $this->post_status,
				'type'             => $product_types,
				'exclude'          => $this->ids_not_in,
				'include'          => $this->ids_in,
				'author'           => implode( ',', $this->vendors ),
				'orderby'          => 'date',
				'order'            => 'DESC',
				'return'           => 'ids',
				'suppress_filters' => false,
			);

			// Remove Out Of Stock Products
			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				$args['stock_status'] = array( 'instock', 'onbackorder' );
			}

			// Remove Backorder Products
			if ( isset( $this->config['is_backorder'] ) && 'y' == $this->config['is_backorder'] ) {
				$args['stock_status'] = array( 'instock', 'outofstock' );
			}

			// Remove both Out Of Stock and Backorder Products
			if ( isset( $this->config['is_backorder'], $this->config['is_outOfStock'] ) && 'y' == $this->config['is_backorder'] && 'y' == $this->config['is_outOfStock'] ) {
				$args['stock_status'] = array( 'instock' );
			}

			// Get Products for Specific Categories
			if ( is_array( $this->categories ) && ! empty( $this->categories ) ) {
				if ( 'include' == $this->config['filter_mode']['categories'] ) {
					$args['category'] = $this->categories;
				} else {
					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $this->categories,
						'operator' => 'NOT IN',
					);
				}
			}
		}
		if ( 'wp' === $type ) {

			$post_type = 'product';
			// Include Product Variations with db query if configured
			$variation_query = woo_feed_get_options( 'variation_query_type' );
			if ( 'individual' == $variation_query &&
				( 'y' == $this->config['is_variations'] || 'both' == $this->config['is_variations'] ) ) {
				$post_type = array( 'product', 'product_variation' );
			}

			$args = array(
				'posts_per_page'         => - 1,
				'post_type'              => $post_type,
				'post_status'            => $this->post_status,
				'post__in'               => $this->ids_in,
				'post__not_in'           => $this->ids_not_in,
				'author__in'             => implode( ',', $this->vendors ),
				'order'                  => 'DESC',
				'fields'                 => 'ids',
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
			);

			// Remove Out Of Stock Products
			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => '!=',
					),
				);
			}

			// Remove Backorder Products
			if ( isset( $this->config['is_backorder'] ) && 'y' == $this->config['is_backorder'] ) {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_stock_status',
						'value'   => 'onbackorder',
						'compare' => '!=',
					),
				);
			}

			// Get Products for Specific Categories
			if ( is_array( $this->categories ) && ! empty( $this->categories ) ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						// This is optional, as it defaults to 'term_id'
						'terms'    => $this->categories,
						'operator' => 'include' == $this->config['filter_mode']['categories'] ? 'IN' : 'NOT IN', // Possible values are 'IN', 'NOT IN', 'AND'.
					),
				);
			}
		}

		return $args;
	}

	/**
	 * Process product variations
	 *
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return bool
	 * @throws Exception
	 * @since 3.3.9
	 */
	protected function process_variation( $product ) {
		// Apply variable and variation settings
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			// Include Product Variations if configured
			$variation_query = woo_feed_get_options( 'variation_query_type' );

			if ( 'variable' === $variation_query && ( 'y' === $this->config['is_variations'] || 'both' === $this->config['is_variations'] ) ) {
				$this->pi ++;
				$variations = $product->get_visible_children();
				if ( is_array( $variations ) && ( count( $variations ) > 0 ) ) {
					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting Variation Product(s) :: %s', implode( ', ', $variations ) ) );
					}
					$this->get_products( $variations );
				}
			}

			if ( 'default' === $this->config['is_variations'] ) {
				$variations = $product->get_visible_children();
				if ( is_array( $variations ) && ( count( $variations ) > 0 ) ) {

					$default_variation = woo_feed_default_product_variation( $product );

					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting default variation :: %s', $default_variation ) );
					}

					if ( ! $default_variation ) {
						return false;
					}

					$this->get_products( array( $default_variation ) );
				}
			}

			if ( 'first' === $this->config['is_variations'] ) {
				$variations = $product->get_visible_children();
				if ( is_array( $variations ) && ( count( $variations ) > 0 ) ) {

					$first_variation = reset( $variations );

					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting first variation :: %s', $first_variation ) );
					}

					$this->get_products( array( $first_variation ) );
				}
			}

			if ( 'last' === $this->config['is_variations'] ) {
				$variations = $product->get_visible_children();
				if ( is_array( $variations ) && ( count( $variations ) > 0 ) ) {

					$last_variation = end( $variations );

					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting first variation :: %s', $last_variation ) );
					}

					$this->get_products( array( $last_variation ) );
				}
			}

			if ( 'cheap' === $this->config['is_variations'] ) {
				$variations       = $product->get_visible_children();
				$variations_price = $product->get_variation_prices();
				if ( isset( $variations_price['price'] ) ) {
					$min_variation = array_keys( $variations_price['price'], min( $variations_price['price'] ) );

					if ( woo_feed_is_debugging_enabled() ) {
						woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting cheapest variation :: %s', $min_variation ) );
					}

					$this->get_products( $min_variation );
				}
			}

			// Skip variable products if only variations are configured
			if ( in_array( $this->config['is_variations'], array( 'y', 'default', 'first', 'last', 'cheap' ), true ) ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Include only variations' );
				return true;
			}
		}
		return false;
	}

	private function getCustomeMattributes( $mattributes ) {
		// Convert every value to uppercase, and remove duplicate values
		//      $withoutDuplicates = array_unique(array_map("strtoupper",$mattributes));
		//      print_r($withoutDuplicates);
		//      // The difference in the original array, and the $withoutDuplicates array
		//      // will be the duplicate values
		//      $duplicates = array_diff($mattributes, $withoutDuplicates);
		//      print_r($duplicates);
	}


		/**
	 * Process The Attributes and assign value to merchant attribute
	 *
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return void
	 * @since 3.3.9
	 */
	protected function process_attributes( $product ) {
		// Get Product Attribute values by type and assign to product array
		$merchant = $this->config['provider'];
		if ( $merchant == 'custom' ) {
			$custom_same_attributes = array();
			$mattributes            = array_count_values( $this->config['mattributes'] );

			foreach ( $mattributes as $keys => $attributes ) {
				if ( $attributes > 1 ) {
					for ( $i = 1;$i <= $attributes;$i++ ) {
						if ( ( $key = array_search( $keys, $this->config['mattributes'] ) ) !== false ) {
							unset( $this->config['mattributes'][ $key ] );
							$this->config['mattributes'][ $key ]          = $keys . '___' . $i;
							$custom_same_attributes[ $keys . '___' . $i ] = $keys;
							update_option( 'woo_feed_custom_template_1', $custom_same_attributes, false );
						}
					}
				}
			}
		}
		$custom_same_attributes = get_option( 'woo_feed_custom_template_1' );

		foreach ( $this->config['attributes'] as $attr_key => $attribute ) {
			//print_r($custom_same_attributes);
			$this->attribute_key = $attr_key;

			$merchant_attribute = $this->config['mattributes'][ $attr_key ];

			$feedType = $this->config['feedType'];

			if ( $this->exclude_current_attribute( $product, $merchant_attribute, $attribute ) ) {
				continue;
			}

			// Add Prefix and Suffix into Output
			$prefix   = $this->config['prefix'][ $attr_key ];
			$suffix   = $this->config['suffix'][ $attr_key ];
			$merchant = $this->config['provider'];

			if ( 'pattern' == $this->config['type'][ $attr_key ] ) {
				$attributeValue = $this->config['default'][ $attr_key ];
			} else { // Get Pattern value
				$attributeValue = $this->getAttributeValueByType( $product, $attribute, $merchant_attribute );
			}

			// Format Output according to Output Type config.
			if ( isset( $this->config['output_type'][ $attr_key ] ) ) {
				$outputType = $this->config['output_type'][ $attr_key ];
				if ( 'default' != $outputType ) {
					$attributeValue = $this->format_output( $attributeValue, $outputType, $product, $attribute, $merchant_attribute );
				}
			}

			// Format Output according to command
			$commands = $this->config['limit'][ $attr_key ];
			if ( false !== strpos( $commands, '[' ) ) {
				$attributeValue = $this->process_commands( $attributeValue, $commands, $product, $attribute );
			}

			// Add Prefix and Suffix into Output
			$prefix = $this->config['prefix'][ $attr_key ];
			$suffix = $this->config['suffix'][ $attr_key ];

			$attributeValue = $this->process_prefix_suffix( $attributeValue, $prefix, $suffix, $attribute );
			$attributeValue = $this->str_replace( $attributeValue, $attribute );
			// Replace XML Nodes according to merchant requirement
			$getReplacedAttribute = woo_feed_replace_to_merchant_attribute( $merchant_attribute, $merchant, $feedType );

			if ( $merchant == 'custom' && is_array($custom_same_attributes)) {
				foreach ( $custom_same_attributes as $key => $value ) {
					if ( $getReplacedAttribute == $key ) {
						$getReplacedAttribute = $value;
					}
				}
			}

			if ( 'xml' == $feedType ) {

				// XML does not support space in element. So replace Space with Underscore
				$getReplacedAttribute = str_replace( ' ', '_', $getReplacedAttribute );

				// Trim XML Element text & Encode for UTF-8
				if ( ! empty( $attributeValue ) ) {
					$attributeValue = trim( $attributeValue );
					if ( 'custom' === $this->config['provider'] && strpos( $attributeValue, '<![CDATA[' ) === false ) {
						$attributeValue = htmlentities( $attributeValue, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
					}
				}

				// Add closing XML node if value is empty
				if ( '' !== $attributeValue ) {

					//translate with translatepress
					$attributeValue = woo_feed_tp_translate( $attribute, $attributeValue, $product, $this->config, $attr_key );

					// Add CDATA wrapper for XML feed to prevent XML error.
					$attributeValue = woo_feed_add_cdata( $merchant_attribute, $attributeValue, $merchant, $this->config['feedType'] );

					// Strip slash from output
					$attributeValue  = stripslashes( $attributeValue );
					$this->feedBody .= '<' . $getReplacedAttribute . '>' . "$attributeValue" . '</' . $getReplacedAttribute . '>';
					$this->feedBody .= "\n";
				} else {
					$this->feedBody .= '<' . $getReplacedAttribute . '/>';
					$this->feedBody .= "\n";
				}
			} elseif ( 'csv' == $feedType || 'tsv' == $feedType || 'xls' == $feedType || 'xlsx' == $feedType ) {
				$merchant_attribute = $this->processStringForCSV( $getReplacedAttribute );

				//translate with translatepress
				$attributeValue = woo_feed_tp_translate( $attribute, $attributeValue, $product, $this->config, $attr_key );

				if ( 'shipping' === $merchant_attribute && 'bing' == $this->config['provider'] ) {
					$merchant_attribute = 'shipping(country:service:price)';
				} elseif ( 'shipping' === $merchant_attribute ) {
					$merchant_attribute = 'shipping(country:region:service:price)';
				}

				if ( 'tax' === $merchant_attribute ) {
					$merchant_attribute = 'tax(country:region:rate:tax_ship)';
				}

				$attributeValue = $this->processStringForCSV( $attributeValue );
			} elseif ( 'txt' == $feedType ) {
				$merchant_attribute = $this->processStringForTXT( $getReplacedAttribute );

				if ( 'shipping' === $merchant_attribute && 'bing' == $this->config['provider'] ) {
					$merchant_attribute = 'shipping(country:service:price)';
				} elseif ( 'shipping' === $merchant_attribute ) {
					$merchant_attribute = 'shipping(country:region:service:price)';
				}

				if ( 'tax' === $merchant_attribute ) {
					$merchant_attribute = 'tax(country:region:rate:tax_ship)';
				}

				$attributeValue = $this->processStringForTXT( $attributeValue );
			}

			$mAttributes[ $attr_key ]                           = $this->config['mattributes'][ $attr_key ];
			$this->products[ $this->pi ][ $merchant_attribute ] = $attributeValue;

		}
	}

	/**
	 * Check if current product should be processed for feed
	 * This should be using by Woo_Feed_Products_v3::get_products()
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 * @since 3.3.9
	 */
	protected function exclude_from_loop( $product ) {

		// Skip for invalid products
		if ( ! is_object( $product ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product data is not a valid WC_Product object.' );

			return true;
		}

		// For WP_Query check available product types
		if ( 'wp' == $this->queryType ) {
			if ( ! in_array( $product->get_type(), $this->product_types ) ) {
				woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Skipping Product :: Invalid Post/Product Type : %s.', $product->get_type() ) );

				return true;
			}
		}

		// Skip orphaned variation
		if ( $product->is_type( 'variation' ) && ! $product->get_parent_id() ) {
			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Skipping Product :: Orphaned variation product [id: %s] skipped.', $product->get_id() ) );
			return true;
		}

		// Skip for invisible products if $this->config['product_visibility'] set to 0
		if ( 0 == $this->config['product_visibility'] && ! $product->is_visible() ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is not visible.' );

			return true;
		}

		// Skip for empty description products
		if ( 'y' == $this->config['is_emptyDescription'] && empty( $this->description( $product ) ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product description is empty.' );

			return true;
		}

		// Skip for empty image products
		if ( 'y' == $this->config['is_emptyImage'] && empty( $this->image( $product ) ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product image is empty.' );

			return true;
		}

		// Skip for empty price products
		if ( 'y' == $this->config['is_emptyPrice'] && empty( $this->price( $product ) ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product price is empty.' );

			return true;
		}

		// Execute Product id and stock filter for product variations
		if ( $product->is_type( 'variation' ) ) {
			if ( ! empty( $this->ids_not_in ) && in_array( $product->get_id(), $this->ids_not_in ) ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product found in ids_not_in array' );

				return true;
			}

			if ( ! empty( $this->ids_in ) && ! in_array( $product->get_id(), $this->ids_in ) ) {
				woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product found in ids_in array' );

				return true;
			}

			if ( isset( $this->config['is_outOfStock'] ) && 'y' == $this->config['is_outOfStock'] ) {
				if ( $product->get_stock_status() == 'outofstock' ) {
					woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is out of stock' );

					return true;
				}
			}

			// Category filtering for `Variation Query Type : `individual`
			if ( isset( $this->categories ) && ! empty( $this->categories ) ) {

				if ( 'include' == $this->config['filter_mode']['categories'] ) {

					if ( ! has_term( $this->categories, 'product_cat', $product->get_parent_id() ) ) {
						return true;
					}
				} else {

					if ( has_term( $this->categories, 'product_cat', $product->get_parent_id() ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get Product Shipping
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 5.3.7
	 * @author Nazrul Islam Nayan
	 */
	protected function shipping( $product ) {
		$feedBody = '';
		$data     = $this->data;

		if ( isset( $data['shipping_zones'] ) && ! empty( $data['shipping_zones'] ) ) {
			$zones = $data['shipping_zones'];

			if ( in_array( $this->config['provider'], array( 'google', 'facebook', 'pinterest', 'bing', 'snapchat' ) ) ) {
				$get_shipping = new Woo_Feed_Shipping_Pro( $this->config );
				$feedBody    .= $get_shipping->set_product( $product )->set_shipping_zone( $zones )->get_google_shipping();
			}
		}

		return apply_filters( 'woo_feed_filter_product_shipping', $feedBody, $product, $this->config );

	}

	/**
	 * Get Product Shipping Cost
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 5.3.7
	 * @author Nazrul Islam Nayan
	 */
	protected function shipping_cost( $product ) {
		$shipping_obj = new Woo_Feed_Shipping_Pro( $this->config );
		$shipping_obj = $shipping_obj->set_product( $product );

		return apply_filters( 'woo_feed_filter_product_shipping_cost', $shipping_obj->get_lowest_shipping_price(), $product, $this->config );
	}

	/**
	 * Get Product Tax
	 *
	 * @param WC_Product $product Product object.
	 * @return mixed
	 * @since 5.3.7
	 * @author Nazrul Islam Nayan
	 */
	protected function tax( $product ) {
		$feedBody = '';
		if ( in_array( $this->config['provider'], array( 'google', 'facebook', 'pinterest', 'bing', 'snapchat' ) ) ) {
			$shipping_obj = new Woo_Feed_Shipping_Pro( $this->config );
			$feedBody    .= $shipping_obj->set_product( $product )->get_google_tax();
		}

		return apply_filters( 'woo_feed_filter_product_tax', $feedBody, $product, $this->config );
	}

	/**
	 * Get ACF Field values
	 *
	 * @param WC_Product $product
	 * @param string $field_key ACF Filed Key with prefix "acf_fields_"
	 *
	 * @return mixed|string
	 */
	private function getACFField( $product, $field_key ) {
		$field = str_replace( 'acf_fields_', '', $field_key );
		if ( class_exists( 'ACF' ) ) {
			return get_field( $field, $product->get_id() );
		}

		return '';
	}

	/**
	 * Get Product Attribute Value by Type
	 *
	 * @param WC_Product $product Product Object.
	 * @param string $attribute
	 * @param string $merchant_attribute
	 *
	 * @return mixed|string
	 *
	 * @since 3.2.0
	 */
	public function getAttributeValueByType( $product, $attribute, $merchant_attribute = '' ) {

		if ( method_exists( $this, $attribute ) ) {
			$output = $this->$attribute( $product );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX, '', $attribute );

			/**
			 * Filter output for extra attribute, which can be added via 3rd party plugins.
			 *
			 * @param string $output the output
			 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
			 * @param array feed config/rule
			 *
			 * @since 3.3.5
			 */
			return apply_filters( "woo_feed_get_extra_{$attribute}_attribute", '', $product, $this->config );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ACF_FIELDS ) ) {
			$output = $this->getACFField( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_CUSTOM_IDENTIFIER ) || woo_feed_strpos_array(
			array(
				'_identifier_gtin',
				'_identifier_ean',
				'_identifier_mpn',
			),
			$attribute
		) ) {
			$output = $this->getCustomField( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ATTRIBUTE_MAPPING_PREFIX ) ) {
			return $this->get_mapped_attribute_value( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_ATTRIBUTE_PREFIX, '', $attribute );
			$output    = $this->getProductAttribute( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::POST_META_PREFIX ) ) {
			$attribute = str_replace( self::POST_META_PREFIX, '', $attribute );
			$output    = $this->getProductMeta( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_TAXONOMY_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_TAXONOMY_PREFIX, '', $attribute );
			$output    = $this->getProductTaxonomy( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) ) {
			return $this->get_dynamic_attribute_value( $product, $attribute, $merchant_attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_CATEGORY_MAPPING_PREFIX ) ) {
			$id     = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			$output = woo_feed_get_category_mapping_value( $attribute, $id );
			$output = apply_filters( 'woo_feed_filter_category_mapping_value', $output, $attribute, $id );
		} elseif ( false !== strpos( $attribute, self::WP_OPTION_PREFIX ) ) {
			$optionName = str_replace( self::WP_OPTION_PREFIX, '', $attribute );
			$output     = get_option( $optionName );
		} elseif ( 'image_' === substr( $attribute, 0, 6 ) ) {
			// For additional image method images() will be used with extra parameter - image number
			$imageKey = explode( '_', $attribute );
			if ( ! isset( $imageKey[1] ) || ( isset( $imageKey[1] ) && ( empty( $imageKey[1] ) || ! is_numeric( $imageKey[1] ) ) ) ) {
				$imageKey[1] = '';
			}
			$output = call_user_func_array( array( $this, 'images' ), array( $product, $imageKey[1] ) );
		} else {
			// return the attribute so multiple attribute can be join with separator to make custom attribute.
			$output = $attribute;
		}

		// Json encode if value is an array
		if ( is_array( $output ) ) {
			$output = wp_json_encode( $output );
		}

		/**
		 * Filter attribute value
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.4.3
		 */
		$output = apply_filters( 'woo_feed_get_attribute', $output, $product, $this->config, $merchant_attribute );

		/**
		 * Filter attribute value before return based on merchant and attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.7
		 */
		$output = apply_filters( "woo_feed_get_{$this->config['provider']}_{$attribute}_attribute", $output, $product, $this->config );

		/**
		 * Filter attribute value before return based on attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.5
		 */
		return apply_filters( "woo_feed_get_{$attribute}_attribute", $output, $product, $this->config );
	}

	/**
	 * Get the value of a dynamic attribute
	 *
	 * @param WC_Product $product
	 * @param $attributeName
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 */
	private function get_dynamic_attribute_value( $product, $attributeName, $merchant_attribute ) {
		$getValue         = maybe_unserialize( get_option( $attributeName ) );
		$wfDAttributeCode = isset( $getValue['wfDAttributeCode'] ) ? $getValue['wfDAttributeCode'] : '';
		$attribute        = isset( $getValue['attribute'] ) ? (array) $getValue['attribute'] : array();
		$condition        = isset( $getValue['condition'] ) ? (array) $getValue['condition'] : array();
		$compare          = isset( $getValue['compare'] ) ? (array) $getValue['compare'] : array();
		$type             = isset( $getValue['type'] ) ? (array) $getValue['type'] : array();

		$prefix = isset( $getValue['prefix'] ) ? (array) $getValue['prefix'] : array();
		$suffix = isset( $getValue['suffix'] ) ? (array) $getValue['suffix'] : array();

		$value_attribute = isset( $getValue['value_attribute'] ) ? (array) $getValue['value_attribute'] : array();
		$value_pattern   = isset( $getValue['value_pattern'] ) ? (array) $getValue['value_pattern'] : array();

		$default_type            = isset( $getValue['default_type'] ) ? $getValue['default_type'] : 'attribute';
		$default_value_attribute = isset( $getValue['default_value_attribute'] ) ? $getValue['default_value_attribute'] : '';
		$default_value_pattern   = isset( $getValue['default_value_pattern'] ) ? $getValue['default_value_pattern'] : '';

		$result = '';

		// Check If Attribute Code exist
		if ( $wfDAttributeCode ) {
			if ( count( $attribute ) ) {
				foreach ( $attribute as $key => $name ) {
					if ( ! empty( $name ) ) {
						$conditionName = $this->getAttributeValueByType( $product, $name );
						if ( 'weight' === $name ) {
							$unit = ' ' . get_option( 'woocommerce_weight_unit' );
							if ( isset( $unit ) && ! empty( $unit ) ) {
								$conditionName = (float) str_replace( $unit, '', $conditionName );
							}
						}

						$conditionCompare  = $compare[ $key ];
						$conditionOperator = $condition[ $key ];

						if ( ! empty( $conditionCompare ) ) {
							$conditionCompare = trim( $conditionCompare );
						}
						$conditionValue = '';
						if ( 'pattern' == $type[ $key ] ) {
							$conditionValue = $value_pattern[ $key ];
						} elseif ( 'attribute' == $type[ $key ] ) {
							$conditionValue = $this->getAttributeValueByType( $product, $value_attribute[ $key ] );
						} elseif ( 'remove' == $type[ $key ] ) {
							$conditionValue = '';
						}

						switch ( $conditionOperator ) {
							case '==':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName == $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '!=':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName != $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>=':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName >= $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '<=':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName <= $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '>':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName > $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' !== $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case '<':
								if ( $this->validateDate( $conditionName ) && $this->validateDate( $conditionCompare ) ) {
									$conditionName    = strtotime( $conditionName );
									$conditionCompare = strtotime( $conditionCompare );
								}
								if ( $conditionName < $conditionCompare ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'contains':
								if ( false !== strpos(
									mb_strtolower( $conditionName ),
									mb_strtolower( $conditionCompare )
								) ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'nContains':
								if ( strpos(
									mb_strtolower( $conditionName ),
									mb_strtolower( $conditionCompare )
								) === false ) {
									$result = $this->price_format( $name, $conditionName, $conditionValue );
									if ( '' != $result ) {
										$result = $prefix[ $key ] . $result . $suffix[ $key ];
									}
								}
								break;
							case 'between':
								$compare_items = explode( ',', $conditionCompare );

								if ( isset( $compare_items[1] ) && is_numeric( $compare_items[0] ) && is_numeric( $compare_items[1] ) ) {
									if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
										$result = $this->price_format( $name, $conditionName, $conditionValue );
										if ( '' != $result ) {
											$result = $prefix[ $key ] . $result . $suffix[ $key ];
										}
									}
								} elseif ( isset( $compare_items[1] ) && $this->validateDate( $compare_items[0] ) && $this->validateDate( $compare_items[1] ) ) {
									if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
										$result = $this->price_format( $name, $conditionName, $conditionValue );
										if ( '' != $result ) {
											$result = $prefix[ $key ] . $result . $suffix[ $key ];
										}
									}
								} else {
									$result = '';
								}
								break;
							default:
								break;
						}
					}
				}
			}
		}

		if ( '' === $result ) {
			if ( 'pattern' == $default_type ) {
				$result = $default_value_pattern;
			} elseif ( 'attribute' == $default_type ) {
				if ( ! empty( $default_value_attribute ) ) {
					$result = $this->getAttributeValueByType( $product, $default_value_attribute );
				}
			} elseif ( 'remove' == $default_type ) {
				$result = '';
			}
		}

		return apply_filters( 'woo_feed_after_dynamic_attribute_value', $result, $product, $attributeName, $merchant_attribute, $this->config );
	}

	/**
	 * Get dat is Validate
	 *
	 * @param date $date Date
	 * @param string $format Date Formate
	 *
	 * @return boolean
	 */

	private function validateDate( $date, $format = 'Y-m-d' ) {
		$d = DateTime::createFromFormat( $format, $date );
		return $d && $d->format( $format ) == $date;
	}

	/**
	 * Format price value
	 *
	 * @param string $name Attribute Name
	 * @param int $conditionName condition
	 * @param int $result price
	 *
	 * @return mixed
	 * @since 3.2.0
	 */
	protected function price_format( $name, $conditionName, $result ) {
		// calc and return the output.
		if ( false !== strpos( $name, 'price' ) || false !== strpos( $name, 'weight' ) ) {
			if ( false !== strpos( $result, '+' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '+', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName + ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '-' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '-', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '*' ) && false !== strpos( $result, '%' ) ) {
				$result = str_replace_trim( '*', '', $result );
				$result = str_replace_trim( '%', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( false !== strpos( $result, '+' ) ) {
				$result = str_replace_trim( '+', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName + $result );
				}
			} elseif ( false !== strpos( $result, '-' ) ) {
				$result = str_replace_trim( '-', '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - $result;
				}
			} elseif ( false !== strpos( $result, '*' ) ) {
				$result = str_replace_trim( '*', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName * $result );
				}
			} elseif ( false !== strpos( $result, '/' ) ) {
				$result = str_replace_trim( '/', '', $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName / $result );
				}
			}
		}

		return $result;
	}

	/**
	 * Get Parent Product Title
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 5.1.8
	 */
	protected function parent_title( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			/*$id    = $product->get_parent_id();
			$title = get_the_title( $id );*/
			$product = wc_get_product( $product->get_parent_id() );
			$title   = woo_feed_strip_all_tags( $this->remove_short_codes( $product->get_name() ) );
		} else {
			$title = $product->get_name();
		}

		$title = woo_feed_strip_all_tags( $this->remove_short_codes( $title ) );

		return apply_filters( 'woo_feed_filter_product_parent_title', $title, $product, $this->config );
	}

	/**
	 * Get formatted product title
	 *
	 * @param WC_Product $product
	 *
	 * @return bool|string
	 * @since 3.2.0
	 */
	protected function get_extended_title( $product ) {
		$attributes = explode( '|', $this->config['ptitle_show'] );
		$attributes = array_filter( $attributes );
		$title      = $product->get_name();
		if ( ! empty( $attributes ) ) {
			$output = '';
			if ( $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				$title  = $parent->get_name();
			}
			foreach ( $attributes as $attribute ) {
				$attribute = trim( $attribute );
				if ( empty( $attribute ) ) {
					continue;
				}
				if ( 'title' == $attribute ) {
					$output .= ' ' . $title;
				} else {
					$get_attr_value = $this->getAttributeValueByType( $product, $attribute );
					if ( '' === $get_attr_value && $product->is_type( 'variation' ) ) {
						$get_attr_value = $this->getAttributeValueByType( $parent, $attribute );
					}
					$output .= ' ' . $get_attr_value;
				}
			}
			$output = ! empty( $output ) ? trim( $output ) : '';

			return preg_replace( '!\s+!', ' ', $output );
		} else {
			return $title;
		}
	}

	/**
	 * @param WC_Product $product
	 * @param string $attribute_map_option_name
	 *
	 * @return string
	 */
	protected function get_mapped_attribute_value( $product, $attribute_map_option_name ) {
		$attributes = get_option( $attribute_map_option_name );
		$glue       = ! empty( $attributes['glue'] ) ? $attributes['glue'] : ' ';
		$output     = '';

		if ( isset( $attributes['mapping'] ) ) {
			foreach ( $attributes['mapping'] as $map ) {
				$get_value = $this->getAttributeValueByType( $product, $map );
				// get product id
				//                if ( empty($get_value) && $product->is_type('variation') ) {
				//                  $product = wc_get_product($product->get_parent_id());
				//                  $get_value = $this->getAttributeValueByType( $product, $map );
				//                }

				if ( ! empty( $get_value ) ) {
					$output .= $glue . $get_value;
				}
			}
		}

		//trim extra glue
		$output = trim( $output, $glue );

		// remove extra whitespace
		$output = preg_replace( '!\s\s+!', ' ', $output );

		return $output;
	}

	/**
	 * Get Meta
	 *
	 * @param WC_Product $product
	 * @param string $meta post meta key
	 *
	 * @return mixed|string
	 * @since 2.2.3
	 */
	protected function getProductMeta( $product, $meta ) {
		// user can decide to get parent meta value with output type dropdown.
		$value = get_post_meta( $product->get_id(), $meta, true );

		// if empty meta value of parent post
		if ( '' === $value && $product->get_parent_id() ) {
			$value = get_post_meta( $product->get_parent_id(), $meta, true );
		}

		// Get ACF Field value if a acf field
		if ( '' === $value && class_exists( 'ACF' ) ) {
			$field_value = get_field( $meta, $product->get_id() );
			// if empty, then get field value of parent post
			if ( '' === $field_value && $product->get_parent_id() ) {
				$field_value = get_field( $meta, $product->get_parent_id() );
			}

			if ( '' !== $field_value ) {
				$value = $field_value;
			}
		}

		return apply_filters( 'woo_feed_filter_product_meta', $value, $product, $this->config );
	}

	/**
	 * Filter Products by Conditions
	 *
	 * @param WC_Product $product
	 *
	 * @return bool|array
	 * @since 3.2.0
	 */
	public function filter_product( $product ) {

		if ( isset( $this->config['fattribute'] ) && ! empty( $this->config['fattribute'] ) ) {

			// Filtering Variable
			$fAttributes   = $this->config['fattribute'];
			$conditions    = $this->config['condition'];
			$filterCompare = $this->config['filterCompare'];
			$concatType    = isset( $this->config['concatType'] ) ? $this->config['concatType'] : array();

			// Backward compatibility for <= v5.2.25
			$filterType = isset( $this->config['filterType'] ) && ! empty( $this->config['filterType'] )
				? $this->config['filterType']
				: 2;

			$filterType = $filterType == 1
				? 'OR'
				: 'AND';

			// Tracking Variables
			$matched          = 0;
			$totalOr          = 0;
			$effectiveOrCount = 0;

			foreach ( $fAttributes as $key => $check ) {

				$flag = false;

				// Backward compatibility for <= v5.2.25
				$concatOperator = isset( $concatType[ $key ] ) && ! empty( $concatType[ $key ] )
					? $concatType[ $key ]
					: $filterType;

				if ( $concatOperator == 'OR' ) {
					$totalOr ++;
				}

				$conditionName    = $this->getAttributeValueByType( $product, $check );
				$condition        = $conditions[ $key ];
				$conditionCompare = stripslashes( $filterCompare[ $key ] );
				// DEBUG HERE
				// echo "Product Name: ".$product->get_name() .''.$product->get_id();   echo "<br>";
				// echo "Name: ".$conditionName;   echo "<br>";
				// echo "Condition: ".$condition;   echo "<br>";
				// echo "Compare: ".$conditionCompare;  echo "<br>";   echo "<br>";

				switch ( $condition ) {

					case '==':
						if ( mb_strtolower( $conditionName ) == mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case '!=':
						if ( mb_strtolower( $conditionName ) != mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case '>=':
						if ( mb_strtolower( $conditionName ) >= mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case '<=':
						if ( mb_strtolower( $conditionName ) <= mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case '>':
						if ( mb_strtolower( $conditionName ) > mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case '<':
						if ( mb_strtolower( $conditionName ) < mb_strtolower( $conditionCompare ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case 'contains':
						if ( false !== strpos( mb_strtolower( $conditionName ), mb_strtolower( $conditionCompare ) ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case 'nContains':
						if ( false === strpos( mb_strtolower( $conditionName ), mb_strtolower( $conditionCompare ) ) ) {
							$matched ++;
							$flag = true;
						}
						break;
					case 'between':
						$compare_items = explode( '-', $conditionCompare );
						if ( $conditionName >= $compare_items[0] && $conditionName <= $compare_items[1] ) {
							$matched ++;
							$flag = true;
						}
						break;
					default:
						break;
				}

				if ( $concatOperator == 'OR' && $flag ) {
					$effectiveOrCount ++;
				}

				if ( $concatOperator == 'AND' && ! $flag ) {
					return false;
				}
			}

			if ( $totalOr > 0 && $effectiveOrCount == 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Format output According to Output Type config
	 *
	 * @param string $output
	 * @param array $outputTypes
	 * @param WC_Product $product
	 * @param string $productAttribute
	 * @param string $merchant_attribute
	 *
	 * @return float|int|string
	 * @since 3.2.0
	 */
	protected function format_output( $output, $outputTypes, $product, $productAttribute, $merchant_attribute ) {
		if ( ! empty( $outputTypes ) && is_array( $outputTypes ) ) {

			// Format Output According to output type
			if ( in_array( 2, $outputTypes ) ) { // Strip Tags
				$output = woo_feed_strip_all_tags( html_entity_decode( $output ) );
			}

			if ( in_array( 3, $outputTypes ) ) { // UTF-8 Encode
				$output = utf8_encode( $output );
			}

			if ( in_array( 4, $outputTypes ) ) { // htmlentities
				$output = htmlentities( $output, ENT_QUOTES, 'UTF-8' );
			}

			if ( in_array( 5, $outputTypes ) ) { // Integer
				$output = intval( $output );
			}

			if ( in_array( 6, $outputTypes ) ) { // Format Price
				if ( ! empty( $output ) && $output > 0 ) {
					$decimals           = $this->config['decimals'];
					$decimal_separator  = $this->config['decimal_separator'];
					$thousand_separator = $this->config['thousand_separator'];
					$output             = (float) $output;

					if ( 'idealo' === $this->config['provider'] ) {
						$output = number_format( $output, 2, '.', '' );
					} else {
						$output = number_format( $output, $decimals, wp_specialchars_decode( stripslashes( $decimal_separator ) ), wp_specialchars_decode( stripslashes( $thousand_separator ) ) );
					}
				}
			}

			if ( in_array( 7, $outputTypes ) ) { // Rounded Price
				if ( ! empty( $output ) && $output > 0 ) {
					$output = round( $output );
					$output = number_format( $output, 2, '.', '' );
				}
			}

			if ( in_array( 8, $outputTypes ) ) { // Delete Space
				$output = htmlentities( $output, null, 'utf-8' );
				$output = str_replace( '&nbsp;', ' ', $output );
				$output = html_entity_decode( $output );
				$output = preg_replace( '/\\s+/', ' ', $output );
			}

			if ( in_array( 10, $outputTypes ) ) { // Remove Invalid Character
				$output = woo_feed_stripInvalidXml( $output );
			}

			if ( in_array( 11, $outputTypes ) ) {  // Remove ShortCodes
				$output = $this->remove_short_codes( $output );
			}

			if ( in_array( 12, $outputTypes ) ) {
				//                $output = ucwords(mb_strtolower($output));
				$output = mb_convert_case( $output, MB_CASE_TITLE );
			}

			if ( in_array( 13, $outputTypes ) ) {
				//                $output = ucfirst(strtolower($output));
				$output = mb_strtoupper( mb_substr( $output, 0, 1 ) ) . mb_substr( $output, 1 );
			}

			if ( in_array( 14, $outputTypes ) ) {
				$output = mb_strtoupper( mb_strtolower( $output ) );
			}

			if ( in_array( 15, $outputTypes ) ) {
				$output = mb_strtolower( $output );
			}

			if ( in_array( 16, $outputTypes ) ) {
				if ( 'http' == substr( $output, 0, 4 ) ) {
					$output = str_replace( 'http://', 'https://', $output );
				}
			}

			if ( in_array( 17, $outputTypes ) ) {
				if ( 'http' == substr( $output, 0, 4 ) ) {
					$output = str_replace( 'https://', 'http://', $output );
				}
			}

			if ( in_array( 18, $outputTypes ) ) { // only parent
				if ( $product->is_type( 'variation' ) ) {
					$id            = $product->get_parent_id();
					$parentProduct = wc_get_product( $id );
					$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute, $merchant_attribute );
				}
			}

			if ( in_array( 19, $outputTypes ) ) { // child if parent empty
				if ( $product->is_type( 'variation' ) ) {
					$id            = $product->get_parent_id();
					$parentProduct = wc_get_product( $id );
					$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute, $merchant_attribute );
					if ( empty( $output ) ) {
						$output = $this->getAttributeValueByType( $product, $productAttribute, $merchant_attribute );
					}
				}
			}

			if ( in_array( 20, $outputTypes ) ) { // parent if child empty
				if ( $product->is_type( 'variation' ) ) {
					$output = $this->getAttributeValueByType( $product, $productAttribute, $merchant_attribute );
					if ( empty( $output ) ) {
						$id            = $product->get_parent_id();
						$parentProduct = wc_get_product( $id );
						$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute, $merchant_attribute );
					}
				}
			}

			if ( in_array( 9, $outputTypes ) && ! empty( $output ) && 'xml' === $this->config['feedType'] ) { // Add CDATA
				$output = '<![CDATA[' . $output . ']]>';
			}

			if ( in_array( 23, $outputTypes ) || in_array( 24, $outputTypes ) ) { // parent lang if child empty
				$id = $product->get_id();

				//check if the format type is `parent` or `parent_lang_if_empty`
				if ( in_array( 23, $outputTypes ) ) {
					$force_parent = true;
				} elseif ( in_array( 24, $outputTypes ) ) {
					$force_parent = empty( $output );
				}

				/**
				 * when format type is `parent` then force getting parent value
				 * when format type is `parent_lang_if_empty` then get the parent value on current empty value
				 */
				if ( $force_parent ) {
					//when wpml plugin is activated, get parent language post id
					if ( class_exists( 'SitePress', false ) ) {
						$parent_id = woo_feed_wpml_get_original_post_id( $id );

						//remove wpml term filter
						global $sitepress;
						remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
						remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ), 1 );
					}

					// when polylang plugin is activated, get parent language post id
					if ( defined( 'POLYLANG_BASENAME' ) || function_exists( 'PLL' ) ) {
						$parent_id = woo_feed_pll_get_original_post_id( $id );
					}

					//get attribute value of parent language post id
					if ( ! empty( $parent_id ) ) {
						$parentProduct = wc_get_product( $parent_id );
						$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute, $merchant_attribute );
					}
				}
			}
		}

		return $output;
	}

	/**
	 * @param string $output
	 * @param string $productAttribute
	 *
	 * @return string
	 */
	public function str_replace( $output, $productAttribute ) {
		// str_replace array can contain duplicate subjects, so better loop through...
		foreach ( $this->config['str_replace'] as $str_replace ) {
			if ( empty( $str_replace['subject'] ) || $productAttribute !== $str_replace['subject'] ) {
				continue;
			}

			if ( strpos( $str_replace['search'], '/' ) === false ) {
				$output = preg_replace( stripslashes( '/' . $str_replace['search'] . '/mi' ), $str_replace['replace'], $output );
			} else {
				$output = str_replace( $str_replace['search'], $str_replace['replace'], $output );
			}
		}

		return $output;
	}

	/**
	 * Process command to format output
	 *
	 * @param $output
	 * @param $commands
	 * @param WC_Product $product
	 * @param NULL|string $productAttribute
	 *
	 * @return bool|string
	 * @since 3.2.0
	 */
	public function process_commands( $output, $commands, $product, $productAttribute ) {
		// Custom Template 2 return commands as array
		if ( ! is_array( $commands ) ) {
			$commands = woo_feed_get_functions_from_command( $commands );
		}

		if ( ! empty( $commands['formatter'] ) ) {
			$output = trim( $output );
			foreach ( $commands['formatter'] as $key => $command ) {
				if ( ! empty( $command ) ) {
					if ( false !== strpos( $command, 'only_parent' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$id            = $product->get_parent_id();
							$parentProduct = wc_get_product( $id );
							$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
						}
					} elseif ( false !== strpos( $command, 'parent_if_empty' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$output = $this->getAttributeValueByType( $product, $productAttribute );
							if ( empty( $output ) ) {
								$id            = $product->get_parent_id();
								$parentProduct = wc_get_product( $id );
								$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
							}
						}
					} elseif ( false !== strpos( $command, 'parent' ) ) {
						if ( $product->is_type( 'variation' ) ) {
							$id            = $product->get_parent_id();
							$parentProduct = wc_get_product( $id );
							$output        = $this->getAttributeValueByType( $parentProduct, $productAttribute );
							if ( empty( $output ) ) {
								$output = $this->getAttributeValueByType( $product, $productAttribute );
							}
						}
					} elseif ( false !== strpos( $command, 'substr' ) ) {
						$args   = preg_split( '/\s+/', $command );
						$output = woo_feed_strip_all_tags( $output );
						$output = substr( $output, $args[1], $args[2] );
					} elseif ( false !== strpos( $command, 'strip_tags' ) ) {
						$output = woo_feed_strip_all_tags( $output );
					} elseif ( false !== strpos( $command, 'htmlentities' ) ) {
						$output = htmlentities( $output );
					} elseif ( false !== strpos( $command, 'clear' ) ) {
						$output = woo_feed_stripInvalidXml( $output );
					} elseif ( false !== strpos( $command, 'ucwords' ) ) {
						$output = ucwords( mb_strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'ucfirst' ) ) {
						$output = ucfirst( mb_strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'strtoupper' ) ) {
						$output = mb_strtoupper( mb_strtolower( $output ) );
					} elseif ( false !== strpos( $command, 'strtolower' ) ) {
						$output = mb_strtolower( $output );
					} elseif ( false !== strpos( $command, 'convert' ) ) {
						// Skip convert command if API Key not found
						//if ( ! empty( get_option( 'woo_feed_currency_api_code' ) ) ) {
						$args              = preg_split( '/\s+/', $command );
						$number            = explode( ' ', $output );
						$convertedCurrency = woo_feed_convert_currency( $number[0], $args[1], $args[2] );

						if ( $convertedCurrency ) {
							$currencyCode = ( isset( $number[1] ) && ! empty( $number[1] ) ) ? ' ' . trim( $number[1] ) : '';
							$output       = $convertedCurrency . $currencyCode;
						}
						//}
					} elseif ( false !== strpos( $command, 'number_format' ) ) {
						if ( ! empty( $output ) ) {
							$args      = explode( ' ', $command, 3 );
							$arguments = array( 0 => '' );

							if ( isset( $args[1] ) ) {
								$arguments[1] = $args[1];
							}

							if ( isset( $args[2] ) && 'point' == $args[2] ) {
								$arguments[2] = '.';
							} elseif ( isset( $args[2] ) && 'comma' == $args[2] ) {
								$arguments[2] = ',';
							} elseif ( isset( $args[2] ) && 'space' == $args[2] ) {
								$arguments[2] = ' ';
							}

							if ( isset( $args[3] ) && 'point' == $args[3] ) {
								$arguments[3] = '.';
							} elseif ( isset( $args[3] ) && 'comma' == $args[3] ) {
								$arguments[3] = ',';
							} elseif ( isset( $args[3] ) && 'space' == $args[3] ) {
								$arguments[3] = ' ';
							} else {
								$arguments[3] = '';
							}

							if ( isset( $arguments[1] ) && isset( $arguments[2] ) && isset( $arguments[3] ) ) {
								$output = number_format( $output, $arguments[1], $arguments[2], $arguments[3] );
							} elseif ( isset( $arguments[1] ) && isset( $arguments[2] ) ) {
								$output = number_format( $output, $arguments[1], $arguments[2], $arguments[3] );
							} elseif ( isset( $arguments[1] ) ) {
								$output = number_format( $output, $arguments[1] );
							} else {
								$output = number_format( $output );
							}
						}
					} elseif ( false !== strpos( strtolower( $command ), 'urltounsecure' ) ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'https://', 'http://', $output );
						}
					} elseif ( false !== strpos( strtolower( $command ), 'urltosecure' ) ) {
						if ( 'http' == substr( $output, 0, 4 ) ) {
							$output = str_replace( 'http://', 'https://', $output );
						}
					} elseif ( false !== strpos( $command, 'str_replace' ) ) {
						$args = explode( '=>', $command, 3 );
						if ( array_key_exists( 1, $args ) && array_key_exists( 2, $args ) ) {

							$argument1 = $args[1];
							$argument2 = $args[2];

							if ( false !== strpos( $args[1], 'comma' ) ) {
								$argument1 = str_replace( 'comma', ',', $args[1] );
							}

							if ( false !== strpos( $args[2], 'comma' ) ) {
								$argument2 = str_replace( 'comma', ',', $args[2] );
							}

							$output = str_replace( "$argument1", "$argument2", $output );
						}
					} elseif ( false !== strpos( $command, 'strip_shortcodes' ) ) {
						$output = $this->remove_short_codes( $output );
					} elseif ( false !== strpos( $command, 'preg_replace' ) ) {
						$args = explode( ' ', $command, 3 );

						$argument1 = $args[1];
						$argument2 = $args[2];

						if ( false !== strpos( $args[1], 'comma' ) ) {
							$argument1 = str_replace( 'comma', ',', $args[1] );
						}

						if ( false !== strpos( $args[2], 'comma' ) ) {
							$argument2 = str_replace( 'comma', ',', $args[2] );
						}

						$output = preg_replace( stripslashes( $argument1 ), $argument2, $output );
					}
				}
			}
		}

		if ( in_array( $this->config['provider'], woo_feed_get_custom2_merchant() ) ) {
			$output = $this->str_replace( $output, $productAttribute );
		}

		return "$output";
	}

	public function get_not_ids_in() {
		return $this->ids_not_in;
	}

	public function get_ids_in() {
		return $this->ids_in;
	}

	public function vendor_store_name( $product ) {

		if ( class_exists( 'WeDevs_Dokan' ) ) {
			$seller = get_post_field( 'post_author', $product->get_id() );
			$author = get_user_by( 'id', $seller );
			$vendor = dokan()->vendor->get( $seller );

			return $vendor->get_shop_name();
		} elseif ( class_exists( 'WC_Vendors' ) ) {
			$vendor_id = get_post_field( 'post_author', $product->get_id() );

			return WCV_Vendors::get_vendor_shop_name( stripslashes( $vendor_id ) );
		} elseif ( class_exists( 'YITH_Vendor' ) ) {
			$store_name = get_the_terms( $product->get_id(), 'yith_shop_vendor' );

			return isset( $store_name[0]->name ) ? $store_name[0]->name : '';
		} elseif ( class_exists( 'WCMp' ) ) {
			$vendor     = get_wcmp_product_vendors( $product->get_id() );
			$store_name = get_user_meta( $vendor->user_data->ID, '_vendor_page_title', true );

			return $store_name;
		} elseif ( class_exists( 'WCFMmp' ) ) {
			global $WCFM, $WCFMmp;
			$store_id   = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $product->get_id() );
			$store_name = wcfmmp_get_store( $store_id )->get_store_name();

			return $store_name;
		}

		return false;
	}

	/**
	 * Get Product URL from TranslatePress
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 * @since 5.2.44
	 */
	protected function link( $product ) {
		$link = $product->get_permalink();

		if ( isset( $this->attribute_key ) && isset( $this->config['output_type'][ $this->attribute_key ] ) ) {
			$output_type = $this->config['output_type'][ $this->attribute_key ];

			// When translatepress plugin is active
			if ( is_plugin_active( 'translatepress-multilingual/index.php' ) && ! in_array( 23, $output_type ) ) {

				$feed_language = $this->config['feedLanguage'];

				$settings = get_option( 'trp_settings', false );
				$url_slug = $settings['url-slugs'][ $feed_language ];

				$link = $product->get_permalink();

				$trp           = TRP_Translate_Press::get_trp_instance();
				$url_converter = $trp->get_component( 'url_converter' );
				$link          = $url_converter->get_url_for_language( $feed_language, $link );

				if ( $settings['default-language'] != $feed_language ) {
					$link = str_replace( home_url() . '/', home_url() . '/' . $url_slug . '/', $link );
				}
			}
		}

		// UTM Parameter addition
		$link = $this->add_utm_tracker( $link );

		return apply_filters( 'woo_feed_filter_product_link', $link, $product, $this->config );
	}

	/**
	 * Get Parent Product Id for WPML/Polylang plugin
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 */
	protected function parent_id( $product ) {
		$id = $product->get_id();

		// when wpml plugin is activated
		if ( class_exists( 'SitePress' ) ) {
			$default_lang = apply_filters( 'wpml_default_language', null );
			$id           = apply_filters( 'wpml_object_id', $product->get_id(), 'post', true, $default_lang );
		}

		return apply_filters( 'woo_feed_filter_parent_product_id', $id, $product, $this->config );
	}

	/**
	 * Get Product Quantity
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 */
	protected function quantity( $product ) {
		$quantity = $product->get_stock_quantity();
		$status   = $product->get_stock_status();

		//when product is outofstock and it's quantity is empty, set quantity to 0
		if ( 'outofstock' == $status && empty( $quantity ) ) {
			$quantity = 0;
		}

		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$visible_children = $product->get_visible_children();
			$qty              = array();
			foreach ( $visible_children as $key => $child ) {
				$childQty = get_post_meta( $child, '_stock', true );
				$qty[]    = (int) $childQty + 0;
			}

			if ( isset( $this->config['variable_quantity'] ) ) {
				$vaQty = $this->config['variable_quantity'];
				if ( 'max' === $vaQty ) {
					$quantity = max( $qty );
				} elseif ( 'min' === $vaQty ) {
					$quantity = min( $qty );
				} elseif ( 'sum' === $vaQty ) {
					$quantity = array_sum( $qty );
				} elseif ( 'first' === $vaQty ) {
					$quantity = ( (int) $qty[0] );
				}
			}
		}

		return apply_filters( 'woo_feed_filter_product_quantity', $quantity, $product, $this->config );
	}

	/**
	 * Custom Template 2 images loop
	 *
	 * @param $product
	 *
	 * @return array
	 */
	public function custom_xml_images( $product ) {

		return explode( ',', $this->images( $product ) );
	}

	/**
	 * Custom Template 2 attributes loop
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function custom_xml_attributes( $product ) {
		$getAttributes = $product->get_attributes();
		$attributes    = array();
		if ( ! empty( $getAttributes ) ) {
			foreach ( $getAttributes as $key => $attribute ) {
				$attributes[ $key ]['name']  = wc_attribute_label( $key );
				$attributes[ $key ]['value'] = $product->get_attribute( wc_attribute_label( $key ) );
			}
		}

		return $attributes;
	}

	/**
	 * Custom Template 2 attributes loop
	 *
	 * @return array
	 */
	public function custom_xml_shipping( $product ) {
		$shipping = new Woo_Feed_Shipping( $this->config );
		$zones    = WC_Shipping_Zones::get_zones();

		return $shipping->set_product( $product )->set_shipping_zone( $zones )->get_shipping();
	}

	/**
	 * Custom Template 2 attributes loop
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function custom_xml_tax( $product ) {
		$taxRates = array();
		if ( $product->is_taxable() ) {
			$taxClass = $product->get_tax_class();
			$rates    = WC_Tax::get_rates_for_tax_class( $taxClass );
			foreach ( $rates as $rKey => $rate ) {
				$settings             = woo_feed_get_options( 'allow_all_shipping' );
				$all_country_shipping = apply_filters( 'woo_feed_allowed_tax_countries', $settings, $this->config );
				if ( 'no' === $all_country_shipping && $rate->tax_rate_country !== $this->config['feed_country'] ) {
					continue;
				}
				$taxRates[ $rKey ]['country']  = $rate->tax_rate_country;
				$taxRates[ $rKey ]['state']    = $rate->tax_rate_state;
				$taxRates[ $rKey ]['postcode'] = is_array( $rate->postcode ) ? implode( ',', $rate->postcode ) : $rate->postcode;
				$taxRates[ $rKey ]['city']     = is_array( $rate->city ) ? implode( ',', $rate->city ) : $rate->city;
				$taxRates[ $rKey ]['rate']     = wc_admin_number_format( $rate->tax_rate );
				$taxRates[ $rKey ]['label']    = $rate->tax_rate_name;
			}
		}

		return $taxRates;
	}

	/**
	 * Custom Template 2 attributes loop
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function custom_xml_categories( $product ) {
		$output   = array(); // Initialising
		$taxonomy = 'product_cat'; // Taxonomy for product category

		// Get the product categories terms ids in the product:
		$terms_ids = wp_get_post_terms( $product->get_id(), $taxonomy, array( 'fields' => 'ids' ) );

		// Loop though terms ids (product categories)
		foreach ( $terms_ids as $term_id ) {
			$term_names = array(); // Initialising category array

			// Loop through product category ancestors
			foreach ( get_ancestors( $term_id, $taxonomy ) as $ancestor_id ) {
				// Add the ancestors term names to the category array
				$term_names[] = get_term( $ancestor_id, $taxonomy )->name;
			}
			// Add the product category term name to the category array
			$term_names[] = get_term( $term_id, $taxonomy )->name;

			// Get category separator
			$separator = apply_filters( 'woo_feed_filter_category_separator', ' > ', $product, $this->config );

			// Add the formatted ancestors with the product category to main array
			$output[] = implode( $separator, $term_names );
		}

		return $output;
	}
}
