<?php
/**
 * The file that defines the merchants attributes dropdown
 *
 * A class definition that includes attributes dropdown and functions used across the admin area.
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */

class Woo_Feed_Dropdown_Pro extends Woo_Feed_Dropdown {

	public $cats = [];

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Dropdown of Merchant List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function merchantsDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'merchantsDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Merchant_Pro();
			$options = $this->cache_dropdown( 'merchantsDropdown', $attributes->merchants(), $selected );
		}
		return $options;
	}

	/**
	 * Get Active Languages for current site.
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function getActiveLanguages( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'getActiveLanguages', $selected );
		if ( false === $options ) {
			$languages = [];
			if ( class_exists( 'SitePress' ) ) {
				$get_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
				if ( ! empty( $get_languages ) ) {
					foreach ( $get_languages as $key => $language ) {
						$languages[ $key ] = $language['translated_name'];
					}
				}
			}

			// when polylang plugin is activated
			if ( defined( 'POLYLANG_BASENAME' ) || function_exists( 'PLL' ) ) {
				// polylang language names
				$poly_languages_names = pll_languages_list( ['fields' => 'name']);

				// polylang language locales
				$poly_languages_slugs = pll_languages_list( ['fields' => 'slug']);

				// polylang language lists
				$get_languages = array_combine( $poly_languages_slugs, $poly_languages_names );

				if ( ! empty( $get_languages ) ) {
					$languages = [];
					foreach ( $get_languages as $key => $value ) {
						$languages[ $key ] = $value;
					}
				}
			}


			//when translatepress is activated
			if ( is_plugin_active( 'translatepress-multilingual/index.php' ) ) {
				if ( class_exists( 'TRP_Translate_Press' ) ) {
					$tr_press_languages = trp_get_languages( 'default' );

					if ( ! empty( $tr_press_languages ) ) {
						foreach ( $tr_press_languages as $key => $value ) {
							$languages[ $key ] = $value;
						}
					}
				}
			}

			//language dropdown
			$options = $this->cache_dropdown( 'getActiveLanguages', $languages, $selected, __( 'Select Language', 'woo-feed' ) );

		}

		return $options;
	}

    /**
     * Get Active Currency
     * @param string $selected
     *
     * @return false|mixed|string
     * @since 3.3.2
     */
	public function getActiveCurrencies( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'getActiveCurrencies', $selected );
		if ( false === $options ) {
			global $woocommerce_wpml;
			if ( class_exists( 'SitePress' ) && class_exists( 'woocommerce_wpml' ) && wcml_is_multi_currency_on() && isset( $woocommerce_wpml->multi_currency->currencies ) ) {
                $get_currencies = $woocommerce_wpml->multi_currency->currencies;
				if ( ! empty( $get_currencies ) ) {
					$currencies = [];
					foreach ( $get_currencies as $key => $currency ) {
						$currencies[ $key ] = $key;
					}
					$options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
				}
			} elseif ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
				$base_currency  = get_woocommerce_currency();
				$get_currencies = apply_filters( 'wc_aelia_cs_enabled_currencies', $base_currency );

				// Fixed warning with Alia Currency Plugin's initial settings when activated.
				if ( ! empty( $get_currencies ) ) {

					if ( is_array( $get_currencies ) ) {
						$currencies = [];
						foreach ( $get_currencies as $currency ) {
							$currencies[ $currency ] = $currency;
						}
					} elseif ( gettype( $get_currencies ) === 'string' ) {
						$currencies = [
							$get_currencies => $get_currencies,
                        ];
					} else {
						$currencies = [];
					}


					$options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
				}
			} elseif ( class_exists( 'WOOCS' ) ) {
				global $WOOCS;
				$get_currencies = $WOOCS->get_currencies();
				if ( ! empty( $get_currencies ) ) {
					$currencies = [];
					foreach ( $get_currencies as $key => $currency ) {
						$currencies[ $key ] = $key;
					}
					$options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
				}
			} elseif ( is_plugin_active( 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' ) ) {

				if ( function_exists( 'alg_get_enabled_currencies' ) ) {
					$currencies = alg_get_enabled_currencies();
					$currencies = array_combine( $currencies, $currencies );

					$options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
				}
			} elseif ( is_plugin_active( 'woocommerce-multicurrency/woocommerce-multicurrency.php' ) ) {

                if ( class_exists( 'WOOMC\DAO\Factory' ) ) {
                    $currencies = WOOMC\DAO\Factory::getDao()->getEnabledCurrencies();
                    $currencies = array_combine( $currencies, $currencies );

                    $options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
                }
			} elseif ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
                $settings   = get_option( 'woo_multi_currency_params' );
                if ( isset($settings['currency']) ) {
                    $currencies = $settings['currency'];
                    $currencies = array_combine( $currencies, $currencies );
                    $options = $this->cache_dropdown( 'getActiveCurrencies', $currencies, $selected, __( 'Select Currency', 'woo-feed' ) );
                }
            }
		}

		return $options;
	}

	/**
	 * Get All Product Categories
	 *
	 * @param int $parent Category Parent ID.
	 *
	 * @return array
	 */
	public function get_categories( $parent = 0 ) {

		$args = [
			'taxonomy'     => 'product_cat',
			'parent'       => $parent,
			'orderby'      => 'term_group',
			'show_count'   => 1,
			'pad_counts'   => 1,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0,
        ];

		$categories = get_categories( $args );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				$this->cats[ $cat->slug ] = $cat->name;
				$this->get_categories( $cat->term_id );
			}
		}

		return $this->cats;
	}

	/**
	 * Get All Product Category
	 *
	 * @return array
	 */
	public function categories() {
		$categories = woo_feed_get_cached_data( 'woo_feed_dropdown_product_categories' );
		if ( false === $categories ) {
			$categories = $this->get_categories();
			woo_feed_set_cache_data( 'woo_feed_dropdown_product_categories', $categories );
		}

		return (array) $categories;
	}

	/**
	 * Get WooCommerce Vendor List for multi-vendor shop
	 *
	 * @return false|WP_User[]|array
	 */
	public function getAllVendors() {
		$users = woo_feed_get_cached_data( 'woo_feed_dropdown_product_vendors' );
		if ( false === $users ) {
			$users = [];
			$vendor_role = woo_feed_get_multi_vendor_user_role();
			if ( ! empty( $vendor_role ) ) {
				/**
				 * Filter Get Vendor (User) Query Args
				 *
				 * @param array $args
				 */
				$args = apply_filters( 'woo_feed_get_vendors_args', ['role' => $vendor_role]);
				if ( is_array( $args ) && ! empty( $args ) ) {
					$users = get_users( $args );
				}
			}
			woo_feed_set_cache_data( 'woo_feed_dropdown_product_vendors', $users );
		}

		return apply_filters( 'woo_feed_product_vendors', $users );
	}




	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function googleTaxonomy( $selected = '' ) {

	    // Get All Google Taxonomies
		$fileName = plugin_dir_path( __FILE__ )
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . 'libs' . DIRECTORY_SEPARATOR
            . 'webappick-product-feed-for-woocommerce' . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR
            . 'partials'. DIRECTORY_SEPARATOR
            . 'templates' . DIRECTORY_SEPARATOR
            . 'taxonomies' . DIRECTORY_SEPARATOR
            . 'google_taxonomy.txt';

		$customTaxonomyFile = fopen( $fileName, 'r' ); // phpcs:ignore
		$str                = '';
		if ( ! empty( $selected ) ) {
			$selected = trim( $selected );
			if ( ! is_numeric( $selected ) ) {
				$selected = html_entity_decode( $selected );
			} else {
				$selected = (int) $selected;
			}
		}
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile ); // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) { // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$catId = (int) trim( $catId );
				$cat   = trim( $cat );
                $is_selected = selected( $selected, is_numeric( $selected ) ? $catId : $cat, false );
				$str   .= "<option value='$catId' $is_selected>$cat</option>";
			}
		}
		if ( ! empty( $str ) ) {
			$str = '<option></option>' . $str;
		}

		return $str;
	}

	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @return array
	 */
	public function googleTaxonomyArray() {
		// Get All Google Taxonomies
		$fileName           = WOO_FEED_FREE_ADMIN_PATH . '/partials/templates/taxonomies/google_taxonomy.txt';
		$customTaxonomyFile = fopen( $fileName, 'r' );  // phpcs:ignore
		$taxonomy           = [];
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile );  // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) {  // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$taxonomy[] = [
					'value' => absint( trim( $catId ) ),
					'text'  => trim( $cat ),
                ];
			}
		}

		return array_filter( $taxonomy );
	}
}
