<?php
/*
 * Autoloading file & classes
 *
 * @since 4.7
 *
 * */

defined( 'ABSPATH' ) || die();

spl_autoload_register( 'woo_feed_pro_autoloader' );

function woo_feed_pro_autoloader($class){

    if( strpos( $class, 'WebAppick' ) !== false ){

        $file_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR
            . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

        $file_path_free = dirname( __FILE__ ) . DIRECTORY_SEPARATOR
            . 'webappick-product-feed-for-woocommerce' . DIRECTORY_SEPARATOR
            . 'libs' . DIRECTORY_SEPARATOR
            . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

        $file_path_free_secondary = dirname( __FILE__ ) . DIRECTORY_SEPARATOR
            . 'webappick-product-feed-for-woocommerce' . DIRECTORY_SEPARATOR
            . 'includes' . DIRECTORY_SEPARATOR
            . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

        $file_path_free_secondary = str_replace( 'WebAppick' . DIRECTORY_SEPARATOR . 'Feed', '', $file_path_free_secondary );

        if( file_exists( $file_path ) ) {
            require_once $file_path;
        } elseif( file_exists( $file_path_free ) ) {
            require_once $file_path_free;
        } elseif( file_exists( $file_path_free_secondary ) ){
            require_once( $file_path_free_secondary );
        }


    }

}