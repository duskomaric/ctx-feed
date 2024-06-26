<?php
/**
 * This class is responsible for all override facebook template.
 *
 * @package CTXFeed\V5\Override
 */

namespace CTXFeed\V5\Override;

/**
 * Class PinterestTemplate
 *
 * @package CTXFeed\V5\Override
 */
class PinterestTemplate {

	/**
	 * PinterestTemplate class constructor
	 */
	public function __construct() {
		add_filter( 'woo_feed_filter_product_title', array( $this, 'woo_feed_filter_product_title_callback' ), 10, 1 );

		add_filter(
			'woo_feed_filter_product_description_with_html',
			array(
				$this,
				'woo_feed_filter_product_description_callback',
			),
			10,
			1
		);

		add_filter(
			'woo_feed_filter_product_description',
			array(
				$this,
				'woo_feed_filter_product_description_callback',
			),
			10,
			1
		);

		add_filter(
			'woo_feed_filter_product_availability_date',
			array(
				$this,
				'woo_feed_filter_product_availability_date_callback',
			),
			10,
			1
		);
	}

	/**
	 * Modify Product Title
	 *
	 * @param string $title Product title.
	 * @return string
	 */
	public function woo_feed_filter_product_title_callback( $title ) {
		// Replace comma with dash.
		$title = str_replace( ',', '-', $title );

		// Limit to 150 characters.
		return substr( $title, 0, 150 );
	}

	/**
	 * Limit Pinterest Product Description to 9999 characters.
	 *
	 * @param string $description Product description.
	 * @return string
	 */
	public function woo_feed_filter_product_description_callback( $description ) {
		return substr( $description, 0, 9999 );
	}

	/**
	 * Modify product availability date.
	 *
	 * @param string $availability_date availability date.
	 * @return string
	 */
	public function woo_feed_filter_product_availability_date_callback( $availability_date ) {
		return gmdate( 'c', strtotime( $availability_date ) );
	}

}
