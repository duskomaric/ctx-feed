<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedMethodInspection, PhpUnused, PhpUnusedPrivateFieldInspection, PhpUnusedLocalVariableInspection, DuplicatedCode, PhpUnusedParameterInspection, PhpForeachNestedOuterKeyValueVariablesConflictInspection, RegExpRedundantEscape */

/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 12/9/19
 * Time: 12:42 PM
 */
class Woo_Feed_Custom_XML {
	/**
	 * This variable is responsible for holding all product attributes and their values
	 *
	 * @since   1.0.0
	 * @var     Woo_Feed_Products_v3_Pro $products Contains all the product attributes to generate feed
	 * @access  public
	 */
	private $productEngine;

	/**
	 * This variable is responsible for holding feed configuration form values
	 *
	 * @since   1.0.0
	 * @var     array $rules Contains feed configuration form values
	 * @access  public
	 */
	private $config;

	private $feedHeader;
	/**
	 * This variable is responsible for holding feed string
	 *
	 * @since   1.0.0
	 * @var     string $feedString Contains feed information
	 * @access  public
	 */
	private $feedString;
	private $feedFooter;
	private $Elements;
	private $variationElementsStart;
	private $shippingElementsStart;
	private $taxElementsStart;
	private $s = - 1;
	private $forSubLoop = '';

	protected $subLoopsStart = [
		'ifVariationAvailable' => '{{if variation available}}',
		'ifProductTaxable'     => '{{if product taxable}}',
		'ifShippingAvailable'  => '{{if shipping available}}',
		'ifImageAvailable'     => '{{if image available}}',
		'ifCategoryAvailable'  => '{{if category available}}',
		'variation'            => '{{each variation start}}',
		'images'               => '{{each image start}}',
		'shipping'             => '{{each shipping start}}',
		'tax'                  => '{{each tax start}}',
		'categories'           => '{{each category start}}',
		'crossSale'            => '{{each crossSale start}}',
		'upSale'               => '{{each upSale start}}',
		'relatedProducts'      => '{{each relatedProducts start}}',
		'associatedProduct'    => '{{each associatedProduct start}}'
	];

	protected $subLoopsEnd = [
		'ifVariationAvailableEnd' => '{{endif variation}}',
		'ifProductTaxableEnd'     => '{{endif tax}}',
		'ifShippingAvailableEnd'  => '{{endif shipping}}',
		'ifImageAvailableEnd'     => '{{endif image}}',
		'ifCategoryAvailableEnd'  => '{{endif category}}',
//		'variationEnd'            => '{{each variation end}}',
//		'imagesEnd'               => '{{each image end}}',
//		'shippingEnd'             => '{{each shipping end}}',
//		'taxEnd'                  => '{{each tax end}}',
//		'categoryEnd'             => '{{each category end}}',
//		'crossSaleEnd'            => '{{each crossSale end}}',
//		'upSaleEnd'               => '{{each upSale end}}',
//		'relatedProductsEnd'      => '{{each relatedProducts end}}',
//		'associatedProductEnd'    => '{{each associatedProduct end}}'
	];

	/**
	 * @throws Exception
	 */
	public function __construct( $feedRule ) {
		$this->feedHeader    = '';
		$this->feedString    = '';
		$this->feedFooter    = '';
		$this->config        = $feedRule;
		$this->productEngine = new Woo_Feed_Products_v3_Pro( $feedRule );

		// When update via cron job then set productIds
		if ( ! isset( $feedRule['productIds'] ) ) {
			$feedRule['productIds'] = $this->productEngine->query_products();
		}


		$xml            = $this->config['feed_config_custom2'];
		$this->Elements = $this->getXMLElements( $xml );
//
//		echo "<pre>";
//		print_r( $this->shippingElementsStart );
//		print_r( $this->taxElementsStart );
//		print_r( $this->variationElementsStart );
//		print_r( $this->Elements );
//		die();

		// Make Header
		$this->make_xml_header( $xml );

		add_filter( 'woo_feed_allowed_shipping_countries', 'woo_feed_allowed_shipping_countries_callback', 10, 2 );

		// Make Body
		$this->process_xml( $feedRule['productIds'] );
		// Make Footer
		$this->make_xml_footer( $xml );
	}

	/** Get XML node information for each product
	 *
	 * @param $xml
	 * @param string $type
	 *
	 * @return array
	 */
	public function getXMLElements( $xml, $type = '' ) {

//		$xml = trim( preg_replace( '/\+/', '', $xml ) );
		$xml = trim( str_replace( "+", '', $xml ) );

		// Get XML nodes for each product
		$getFeedBody = woo_feed_get_string_between( $xml, '{{each product start}}', '{{each product end}}' );
		if ( empty( $getFeedBody ) ) {
			$getFeedBody = woo_feed_get_string_between( $xml, '{each product start}', '{each product end}' );
		}
		// Explode each element by new line
		$getElements = explode( "\n", $getFeedBody );

		$Elements = array();
		$i        = 1;

		if ( empty( $getElements ) ) {
			return $Elements;
		}

		foreach ( $getElements as $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			if ( in_array( trim( $value ), $this->subLoopsStart ) ) {
				$this->setSubElementStartingIndex( trim( $value ), $i );
				continue;
			}

			if ( in_array( trim( $value ), $this->subLoopsEnd ) ) {
				$loopKey = array_search( trim( $value ), $this->subLoopsEnd, false );
				$keys    = [
					'ifVariationAvailableEnd',
					'ifProductTaxableEnd',
					'ifShippingAvailableEnd',
					'ifImageAvailableEnd',
					'ifCategoryAvailableEnd'
				];

				if ( ! isset( $Elements[ $i - 1 ]['attr_code'] ) && ! isset( $Elements[ $i - 2 ]['attr_code'] ) && ! isset( $Elements[ $i - 2 ]['to_return'] ) && in_array( $loopKey, $keys, true ) ) {
					$Elements[ $i - 1 ]['for'] = substr( $loopKey, 0, - 3 );
				}

				$this->forSubLoop = "";
				continue;
			}

			// Get Element info
			$element = woo_feed_get_string_between( $value, '<', '>' );
			if ( empty( $element ) ) {
				continue;
			}

			// Set Element for
			$Elements[ $i ]['for'] = $this->forSubLoop;
			// Get starting element
			$Elements[ $i ]['start'] = $this->removeQuotation( $element );
			// Get ending element
			$Elements[ $i ]['end'] = woo_feed_get_string_between( $value, '</', '>' );

			// Set CDATA status and remove CDATA
			$elementTextInfo                 = woo_feed_get_string_between( $value, '>', '</' );
			$Elements[ $i ]['include_cdata'] = 'no';
			if ( stripos( $elementTextInfo, 'CDATA' ) !== false ) {
				$Elements[ $i ]['include_cdata'] = 'yes';
				$elementTextInfo                 = $this->removeCDATA( $elementTextInfo );
			}
			// Get Pattern of the xml node
			$Elements[ $i ]['elementTextInfo'] = $elementTextInfo;

			if ( ! empty( $Elements[ $i ]['elementTextInfo'] ) ) {
				// Get type of the attribute pattern
				if ( strpos( $elementTextInfo, '{' ) === false && strpos( $elementTextInfo, '}' ) === false ) {
					$Elements[ $i ]['attr_type']  = 'text';
					$Elements[ $i ]['attr_value'] = $elementTextInfo;
				} elseif ( strpos( $elementTextInfo, 'return' ) !== false ) {
					$Elements[ $i ]['attr_type'] = 'return';
					$return                      = woo_feed_get_string_between( $elementTextInfo, '{(', ')}' );
					$Elements[ $i ]['to_return'] = $return;
				} elseif ( strpos( $elementTextInfo, 'php ' ) !== false ) {
					$Elements[ $i ]['attr_type'] = 'php';
					$php                         = woo_feed_get_string_between( $elementTextInfo, '{(', ')}' );
					$Elements[ $i ]['to_return'] = str_replace( 'php', '', $php );
				} else {
					$Elements[ $i ] = array_merge( $Elements[ $i ], $this->setAttributeTypeElement( $elementTextInfo, $value ) );
				}

				$Elements[ $i ] = array_merge( $Elements[ $i ], $this->setElementPrefixSuffix( $Elements[ $i ] ) );

			}

			$Elements[ $i ] = array_merge( $Elements[ $i ], $this->setElementStartCodes( $element ) );


			$i ++;
		}

		return $Elements;
	}

//	public function skipElement( $value,$i , $Elements) {
//
//	}

	public function setElementStartCodes( $element ) {
		preg_match_all( '/{(.*?)}/', $element, $matches );
		$startCodes             = ( isset( $matches[0] ) ? $matches[0] : '' );
		$Elements['start_code'] = array_filter( $startCodes );

		return $Elements;
	}

	public function setElementPrefixSuffix( $element ) {
		// Get prefix of the attribute node value
		$elementTextInfo    = $element['elementTextInfo'];
		$Elements['prefix'] = '';
		if ( 'text' !== $element['attr_type'] && strpos( trim( $elementTextInfo ), '{' ) !== 0 ) {
			$getPrefix          = explode( '{', $elementTextInfo );
			$Elements['prefix'] = ( count( $getPrefix ) > 1 ) ? $getPrefix[0] : '';
		}
		// Get suffix of the attribute node value
		$Elements['suffix'] = '';
		if ( 'text' !== $element['attr_type'] && strpos( trim( $elementTextInfo ), '}' ) !== 0 ) {
			$getSuffix          = explode( '}', $elementTextInfo );
			$Elements['suffix'] = ( count( $getSuffix ) > 1 ) ? $getSuffix[1] : '';
		}

		return $Elements;
	}

	public function setAttributeTypeElement( $elementTextInfo, $value ) {
		$Elements['attr_type'] = 'attribute';
		$attribute             = woo_feed_get_string_between( $elementTextInfo, '{', '}' );
		$getAttrBaseFormat     = explode( ',', $attribute );

		$attrInfo = $getAttrBaseFormat[0];
		if ( count( $getAttrBaseFormat ) > 1 ) {
			$j = 0;
			foreach ( $getAttrBaseFormat as $_value ) {
				if ( $value !== "" ) {
					$formatters = woo_feed_get_string_between( $_value, '[', ']' );
					if ( ! empty( $formatters ) ) {
						$Elements['formatter'][ $j ] = $formatters;
						$j ++;
					}
				}
			}
		}

		$getAttrCodes          = explode( '|', $attrInfo );
		$Elements['attr_code'] = $getAttrCodes[0];
		$Elements['id_type']   = isset( $getAttrCodes[1] ) ? $getAttrCodes[1] : '';

		return $Elements;
	}

	public
	function setSubElementStartingIndex(
		$value, $index
	) {
		$this->forSubLoop = array_search( trim( $value ), $this->subLoopsStart, false );
		if ( $this->forSubLoop === 'variation' ) {
			$this->variationElementsStart = $index;
		} elseif ( $this->forSubLoop === 'shipping' ) {
			$this->shippingElementsStart = $index;
		} elseif ( $this->forSubLoop === 'tax' ) {
			$this->taxElementsStart = $index;
		}
	}

	public
	function setSubElementEndingIndex(
		$value, $Elements, $i
	) {
		$loopKey = array_search( trim( $value ), $this->subLoopsEnd, false );
		$keys    = [
			'ifVariationAvailableEnd',
			'ifProductTaxableEnd',
			'ifShippingAvailableEnd',
			'ifImageAvailableEnd',
			'ifCategoryAvailableEnd'
		];

		if ( ! isset( $Elements[ $i - 1 ]['attr_code'] ) && in_array( $loopKey, $keys, true ) ) {
			return substr( $loopKey, 0, - 3 );
		}

		return $loopKey;
	}

	/**
	 * Return Feed
	 *
	 * @return array
	 */
	public
	function returnFinalProduct() {
		return array(
			'header' => $this->feedHeader,
			'body'   => $this->feedString,
			'footer' => $this->feedFooter,
		);
	}

	/**
	 * @throws Exception
	 */
	private
	function process_xml(
		$ids
	) {

		// Get XML Elements from feed config
		$Elements = $this->Elements;

		if ( ! empty( $Elements ) && ! empty( $ids ) ) {
			foreach ( $ids as $pid ) {
				$product = wc_get_product( $pid );

				if ( $this->skipProducts( $product ) ) {
					continue;
				}

				// Start making XML Elements
				foreach ( $Elements as $each => $element ) {

					// Variations Loop Start Here
					$this->processVariationElements( $Elements, $each, $product );

					// Shipping Loop Start Here
					$this->processShippingElements( $Elements, $each, $product );

					// Tax Loop Start Here
					$this->processTaxElements( $Elements, $each, $product );

					// Image
					$this->processImageElements( $element, $product );

					// Category
					$this->processCategoryElements( $element, $product );

					// Exclude Elements if needed or skip duplicate sub elements.
					$excludeElement = $this->excludeElements( $element, $product );
					if ( $excludeElement ) {
						continue;
					}

					$this->feedString .= $this->make_xml_element( $element, $product );
				}

			}
		}
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 * @throws Exception
	 */
	public
	function skipProducts(
		$product
	) {
		// Skip for invalid products
		if ( ! is_object( $product ) ) {
			return true;
		}

		$skipStatus = false;
		// For WP_Query check available product types
		if ( 'wp' === $this->productEngine->get_query_type() && ! in_array( $product->get_type(), $this->productEngine->get_product_types(), true ) ) {
			$skipStatus = true;
		}

		if ( $product->is_type( 'variation' ) ) {
			if ( in_array( $product->get_id(), $this->productEngine->get_not_ids_in(), true ) ) {
				$skipStatus = false;
			}

			if ( ! empty( $this->productEngine->get_ids_in() ) && ! in_array( $product->get_id(), $this->productEngine->get_ids_in(), true ) ) {
				$skipStatus = true;
			}

			if ( isset( $this->config['is_outOfStock'] ) && 'y' === $this->config['is_outOfStock'] && $product->get_stock_status() === 'outofstock' ) {
				$skipStatus = true;
			}
		}

		if ( $product->is_type( 'variable' ) && $this->process_variation( $product ) ) {
			$skipStatus = true;
		}

		if ( ! $product->is_visible() && '0' === $this->config['product_visibility'] ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is not visible.' );
			$skipStatus = true;
		}

		// Filter products by Condition
		if ( ! $this->productEngine->filter_product( $product ) ) {
			$skipStatus = true;
		}

		//remove empty image product
		if ( isset( $this->config['is_emptyImage'] ) && 'y' === $this->config['is_emptyImage'] && ! $product->get_image_id() ) {
			$skipStatus = true;
		}

		//remove empty description product
		if ( isset( $this->config['is_emptyDescription'] ) && 'y' === $this->config['is_emptyDescription'] && ! $product->get_description() ) {
			$skipStatus = true;
		}

		//remove empty price product
		if ( isset( $this->config['is_emptyPrice'] ) && 'y' === $this->config['is_emptyPrice'] && ! $product->get_price() ) {
			$skipStatus = true;
		}

		//remove out of stock product
		if ( isset( $this->config['product_visibility'] ) && '0' === $this->config['product_visibility'] && ( 'hidden' === $product->get_catalog_visibility() ) ) {
			$skipStatus = true;
		}

		return $skipStatus;
	}

	/**
	 * Process product variations
	 *
	 * @param WC_Product|WC_Product_Variable $product
	 *
	 * @return bool
	 * @throws Exception
	 * @since 3.3.9
	 */
	protected
	function process_variation(
		$product
	) {
		// Apply variable and variation settings
		if ( ! $product->has_child() ) {
			return true;
		}

		// Include Product Variations if configured
		$variation_query = woo_feed_get_options( 'variation_query_type' );
		$variations      = $product->get_visible_children();
		$variation       = '';

		if ( 'variable' === $variation_query && in_array( $this->config['is_variations'], [ 'y', 'both' ] ) ) {
			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting Variation Product(s) :: %s', implode( ', ', $variations ) ) );
			$this->process_xml( $variations );
		}

		if ( 'default' === $this->config['is_variations'] ) {
			$variation = woo_feed_default_product_variation( $product );
		}

		if ( in_array( $this->config['is_variations'], [ 'first', 'last' ] ) ) {
			$variation = ( 'first' === $this->config['is_variations'] ) ? reset( $variations ) : end( $variations );
		}

		if ( 'cheap' === $this->config['is_variations'] ) {
			$variations_price = $product->get_variation_prices();
			$variation        = array_keys( $variations_price['price'], min( $variations_price['price'] ) );
		}

		if ( ! empty( $variation ) ) {
			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting %s variation :: %s', $this->config['is_variations'], $variation ) );
			$this->process_xml( array( $variation ) );
		}

		// Skip variable products if only variations are configured
		if ( in_array( $this->config['is_variations'], [ 'y', 'default', 'first', 'last', 'cheap' ], true ) ) {
			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Include only variations' );

			return true;
		}

		return false;
	}

	public
	function excludeElements(
		$element, $product
	) {

		$continue = false;

		if ( in_array( $element['for'], [ 'variation', 'shipping', 'tax', 'categories', 'images' ] ) ) {
			$continue = true;
		}

		if ( $element['for'] === 'ifVariationAvailable' && $product->get_type() !== 'variable' ) {
			$continue = true;
		}

		if ( $element['for'] === 'ifProductTaxable' && $product->get_tax_status() !== 'taxable' ) {
			$continue = true;
		}

		if ( $element['for'] === 'ifImageAvailable' && empty( $product->get_gallery_image_ids() ) ) {
			$continue = true;
		}

		if ( $element['for'] === 'ifCategoryAvailable' && empty( $product->get_categories() ) ) {
			$continue = true;
		}

		if ( $element['for'] === 'tax' && $product->get_tax_status() === 'taxable' ) {
			$taxClass = $product->get_tax_class();
			$rates    = WC_Tax::get_rates_for_tax_class( $taxClass );
			if ( empty( $rates ) ) {
				$continue = true;
			}
		}
		//TODO Remove shipping elements if product tax not available.
//		if ( isset( $shippingStatus ) && $shippingStatus === false ) {
//			$continue = true;
//		}

		if ( $element['for'] === 'ifShippingAvailable' ) {
			$shippingCheck = $this->productEngine->custom_xml_shipping( $product );
			if ( is_array( $shippingCheck ) && count( $shippingCheck ) === 0 ) {
				$continue = true;
			}
		}

		return $continue;
	}

	/**
	 *  Process Image Elements according to config.
	 *
	 * @param array $element
	 * @param WC_Product $product
	 */
	public
	function processImageElements(
		$element, $product
	) {
		if ( $element['for'] === 'images' && isset( $element['attr_code'] ) ) {
			$images = $this->productEngine->custom_xml_images( $product );
//			echo "<pre>";print_r($images);die();
			if ( count( $images ) > 0 ) {
				foreach ( $images as $image ) {
					if ( ! empty( $image ) ) {
						$element['elementTextInfo'] = $image;
						$element['attr_type']       = 'text';
						$element['attr_value']      = $image;
						unset( $element['attr_code'] );
						$this->feedString .= $this->make_xml_element( $element, $product );
					}
				}
			}
		}
	}

	/**
	 *  Process Category Elements according to config.
	 *
	 * @param array $element
	 * @param WC_Product $product
	 */
	public
	function processCategoryElements(
		$element, $product
	) {
		if ( $element['for'] === 'categories' && isset( $element['attr_code'] ) ) {
			$categories = $this->productEngine->custom_xml_categories( $product );
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$element['elementTextInfo'] = $category;
					$element['attr_type']       = 'text';
					$element['attr_value']      = $category;
					unset( $element['attr_code'] );
					$this->feedString .= $this->make_xml_element( $element, $product );
				}
			}
		}
	}

	/**
	 *  Process Shipping Elements according to config.
	 *
	 * @param array $Elements
	 * @param int $each Element Key
	 * @param WC_Product $product
	 */
	public
	function processShippingElements(
		$Elements, $each, $product
	) {
		if ( $each === $this->shippingElementsStart ) {
			$shippings = $this->productEngine->custom_xml_shipping( $product );
			foreach ( $shippings as $shipping ) {
				if ( ! isset( $shipping['region'] ) ) {
					$shipping['region'] = "";
				}
				if ( ! isset( $shipping['postcode'] ) ) {
					$shipping['postcode'] = "";
				}
				foreach ( $Elements as $shippingElement ) {
					if ( $shippingElement['for'] === 'shipping' ) {
						if ( isset( $shippingElement['attr_code'] ) ) {
							$attr_value                         = str_replace( 'shipping_', '', $shippingElement['attr_code'] );
							$attr_value                         = isset( $shipping[ $attr_value ] ) ? $shipping[ $attr_value ] : $this->getAttributeTypeAndValue( $shippingElement['attr_code'], $product );
							$shippingElement['elementTextInfo'] = $attr_value;
							$shippingElement['attr_value']      = $attr_value;
							$shippingElement['attr_type']       = 'text';
						}

						$this->feedString .= $this->make_xml_element( $shippingElement, $product );
					}
				}
			}
		}
	}

	/**
	 * Process Tax Elements according to config.
	 *
	 * @param array $Elements
	 * @param int $each Element Key
	 * @param WC_Product $product
	 *
	 * @return void
	 */
	public
	function processTaxElements(
		$Elements, $each, $product
	) {
		if ( $each === $this->taxElementsStart ) {
			$taxes = $this->productEngine->custom_xml_tax( $product );
			foreach ( $taxes as $tax ) {
				foreach ( $Elements as $taxElement ) {
					if ( $taxElement['for'] === 'tax' ) {
						if ( isset( $taxElement['attr_code'] ) ) {
							$attr_value                    = str_replace( 'tax_', '', $taxElement['attr_code'] );
							$attr_value                    = isset( $tax[ $attr_value ] ) ? $tax[ $attr_value ] : $this->getAttributeTypeAndValue( $taxElement['attr_code'], $product );
							$taxElement['elementTextInfo'] = $attr_value;
							$taxElement['attr_value']      = $attr_value;
							$taxElement['attr_type']       = 'text';
						}

						$this->feedString .= $this->make_xml_element( $taxElement, $product );
					}
				}
			}
		}
	}

	/**
	 * Process variation elements.
	 *
	 * @param array $Elements
	 * @param int $each Element Key
	 * @param WC_Product $product
	 */
	public
	function processVariationElements(
		$Elements, $each, $product
	) {
		if ( $each === $this->variationElementsStart && $product->is_type( 'variable' ) && $product->has_child() ) {
			$variations = $product->get_children();
			foreach ( $variations as $variation ) {
				$variation = wc_get_product( $variation );
				foreach ( $Elements as $variationElement ) {
					if ( $variationElement['for'] === 'variation' ) {
						$this->feedString .= $this->make_xml_element( $variationElement, $variation );
					}
				}
			}
		}
	}

	/**
	 * Make XML Element according to config.
	 *
	 * @param $element
	 * @param $product
	 *
	 * @return string
	 */
	public
	function make_xml_element(
		$element, $product
	) {
		$p      = false;
		$string = '';
		$start  = '';
		$end    = '';
		$output = '';

		// Start XML Element
		if (
			empty( $element['elementTextInfo'] ) && // Get the root element.
			empty( $element['end'] ) &&
			6 === count( $element )
		) {

			// Start XML Element
			$elementStart = $this->processStartingElement( $element, $product );

			$end    .= '<' . $elementStart . '>';
			$string .= $end . "\n";
			$p      = true;
		} elseif ( ! empty( $element['start'] ) ) {
			$elementStart = $this->processStartingElement( $element, $product );
			$start        .= '<' . $elementStart . '>';
		}

		// Make XML Element Text
		if ( ! empty( $element['elementTextInfo'] ) ) {

			$output = $this->getElementValueByConfig( $element, $output, $product );

			$pluginAttribute = null;
			if ( 'attribute' === $element['attr_type'] ) {
				$pluginAttribute = $element['attr_code'];
			}

			// Format output according to commands
			if ( array_key_exists( 'formatter', $element ) ) {
				$output = $this->productEngine->process_commands( $output, $element, $product, $pluginAttribute );
			}
			$p = false;
		}

		// End XML Element
		if ( '/' . $element['end'] === $element['start'] && empty( $element['elementTextInfo'] ) && 6 === count( $element ) ) {
			if ( ! empty( $element['end'] ) ) {
				$end .= '<' . $element['start'] . '>';
			}
			$string .= $end . "\n";
			$p      = true;
		} else if ( ! empty( $element['end'] ) ) {
			$end .= '</' . $element['end'] . ">\n";
		}

		if ( ! $p ) {

			// Add Prefix and Suffix.
			$output = $this->addPrefixSuffix( $element, $output );
			// Add CDATA if needed.
			$output = $this->addCDATA( $element['include_cdata'], $output );

			// Complete a line.
			$string .= $start . $output . $end;
			$p      = false;
		}

		return $string;
	}


	/**
	 * Get xml element value by its config.
	 *
	 * @param $element
	 * @param $output
	 * @param $product
	 *
	 * @return string
	 */
	public
	function getElementValueByConfig(
		$element, $output, $product
	) {

		switch ( $element['attr_type'] ) {
			case "attribute":
				$output = $this->getAttributeTypeAndValue( $element['attr_code'], $product );
				$output = $this->productEngine->str_replace( $output, $element['attr_code'] );
				break;
			case "return":
				if ( preg_match("/\bround\b/", $element['to_return'] ) ) {
					$to_return = preg_replace("/round\(|\)/", "", $element['to_return'] );
					$element['to_return'] = $to_return;
					$output =  round( $this->getReturnTypeValue( $element, $product ) );
				} else {
					$output =  $this->getReturnTypeValue( $element, $product );
				}
				break;
			case "php":
				if ( isset( $element['to_return'] ) && ! empty( $element['to_return'] ) ) {
					$output = $this->returnPHPFunction( $element['to_return'] );
				}
				break;
			case "text":
				$output = ( isset( $element['attr_value'] ) && ! empty( $element['attr_value'] ) ) ? $element['attr_value'] : '';
				break;
			default:
				break;
		}

		return $output;
	}


	/**
	 * Call Product class and return value by attribute type.
	 *
	 * @param $attribute
	 * @param $product
	 *
	 * @return mixed|string
	 */
	public
	function getAttributeTypeAndValue(
		$attribute, $product
	) {

		return $this->productEngine->getAttributeValueByType( $product, $attribute );

	}

	/**
	 * Add Quotation mark to store code value.
	 *
	 * @return string
	 */
	public
	function addQuotation(
		$string
	) {
		return "'" . str_replace( array( "'", "\"", "&quot;" ), "", htmlspecialchars( $string ) ) . "'";
	}

	/**
	 * Remove Quotation mark from xml element.
	 *
	 * @return string
	 */
	public
	function removeQuotation(
		$string
	) {
		return str_replace( array( "'", "\"", "&quot;" ), "", $string );
	}

	/**
	 * Extract Start Code attributes value and replace.
	 *
	 * @param $element
	 * @param $product
	 *
	 * @return array|string
	 */
	public function processStartingElement( $element, $product ) {
		$elementStart = stripslashes( $element['start'] );
		if ( ! empty( $element['start_code'] ) ) {
			$start_attr_codes = array();
			foreach ( $element['start_code'] as $attrValue ) {
				if ( strpos( $attrValue, 'return' ) !== false ) {
					$start_attr_code                                = woo_feed_get_string_between( $attrValue, '{(', ')}' );
					$tempAttribute                                  = array(
						'to_return' => $start_attr_code,
						'attr'      => $attrValue,
					);
					$start_attr_code                                = $this->getReturnTypeValue( $tempAttribute, $product );
					$start_attr_codes[ stripslashes( $attrValue ) ] = $this->addQuotation( $start_attr_code );

				} else {
					$start_attr_code                = woo_feed_get_string_between( $attrValue, '{', '}' );
					$start_attr_code                = $this->getAttributeTypeAndValue( $start_attr_code, $product );
					$start_attr_codes[ $attrValue ] = $this->addQuotation( $start_attr_code );

				}
			}
			$elementStart = str_replace( array_keys( $start_attr_codes ), array_values( $start_attr_codes ), $elementStart );
		}

		return $elementStart;
	}


	/**
	 * Make feed Header
	 *
	 * @param $xml
	 */
	public
	function make_xml_header(
		$xml
	) {
		$getHeader = explode( '{{each product start}}', $xml );
		$header    = $getHeader[0];
		$getNodes  = explode( "\n", $header );

		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $value ) {
				// Add header info to feed file
				$value = preg_replace( '/\\\\/', '', $value );
				if ( strpos( $value, 'return' ) !== false ) {
					$return       = woo_feed_get_string_between( $value, '{(', ')}' );
					$return_value = $this->process_eval( $return );
					$value        = preg_replace( '/\{\(.*?\)\}/', $return_value, $value );
				}
				$this->feedHeader .= $value;
				$this->s          += 2;
			}
		} else {
			$this->feedHeader .= '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
		}
	}

	/**
	 * Make Feed Footer
	 *
	 * @param $xml
	 */
	public
	function make_xml_footer(
		$xml
	) {
		$getFooter = explode( '{{each product end}}', $xml );
		$getNodes  = explode( "\n", $getFooter[1] );
		if ( ! empty( $getNodes ) ) {
			foreach ( $getNodes as $value ) {
				$this->s -= 2;
				// Add header info to feed file
				$this->feedFooter .= $value;
			}
		}
	}

	/**
	 * Process eval attribute settings
	 *
	 * @param $attribute
	 *
	 * @return mixed
	 */
	public function process_eval( $attribute ) {
		$return = preg_replace( '/\\\\/', '', $attribute );

		return eval( $return );
	}

	public function getReturnTypeValue( $attribute, $product ) {
		$variables = array();
		if ( ! empty( $attribute ) && strpos( $attribute['to_return'], '$' ) !== false ) {
			$pattern = '/\$\S+/';
			preg_match_all( $pattern, $attribute['to_return'], $matches, PREG_SET_ORDER );
			$matches = array_column( $matches, 0 );
			foreach ( $matches as $variable ) {
				if ( strpos( $variable, '$' ) !== false ) {
					$variable                             = str_replace( array( '$', ';' ), '', $variable );
					$attribute['attr_code']               = $variable;
					$variables[ $attribute['attr_code'] ] = $this->getAttributeTypeAndValue( $attribute['attr_code'], $product );
				}
			}
		}

		extract( $variables, EXTR_OVERWRITE ); // phpcs:ignore
		$return = $attribute['to_return'];
		$return = preg_replace( '/\\\\/', '', $return );
		$return = eval( $return );

		return $return;
	}

	/** Return the php function of the attribute
	 *
	 * @param $function
	 *
	 * @return mixed
	 */
	public
	function returnPHPFunction(
		$function
	) {
		return $function;
	}

	/**
	 * Add Prefix & Suffix to the Output
	 *
	 * @param $element
	 * @param $output
	 *
	 * @return mixed|string|null
	 */
	public function addPrefixSuffix( $element, $output ) {
		if ( ! empty( $output ) ) {
			$prefix = isset( $element['prefix'] ) ? preg_replace( '!\s+!', ' ', $element['prefix'] ) : '';
			$suffix = isset( $element['suffix'] ) ? preg_replace( '!\s+!', ' ', $element['suffix'] ) : '';
			$output = $this->productEngine->process_prefix_suffix( $output, $prefix, $suffix, isset( $element['attr_code'] ) ? $element['attr_code'] : '' );
		}

		return $output;
	}

	/** Add CDATA to String
	 *
	 * @param string $status
	 * @param string $output
	 *
	 * @return string
	 */
	public
	function addCDATA(
		$status, $output
	) {
		if ( 'yes' === $status && $output !== '' ) {
			$output = $this->removeCDATA( $output );

			return '<![CDATA[' . $output . ']]>';
		}

		return $output;
	}

	/** Remove CDATA from String
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public
	function removeCDATA(
		$output
	) {
		return str_replace( [ "<![CDATA[", "]]>" ], "", $output );
	}
}
