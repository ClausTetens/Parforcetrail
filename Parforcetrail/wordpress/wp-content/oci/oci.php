<?php
if( !defined( 'MIDDLEWARE_URL' ) ) {
	$api_version = 'v1.0';
	if( isset( $_SERVER[ 'ONECOM_WP_ADDONS_API' ] ) && $_SERVER[ 'ONECOM_WP_ADDONS_API' ] != '' ) {
		$ONECOM_WP_ADDONS_API = $_SERVER[ 'ONECOM_WP_ADDONS_API' ];
	} elseif( defined( 'ONECOM_WP_ADDONS_API' ) && ONECOM_WP_ADDONS_API != '' && ONECOM_WP_ADDONS_API != false ) {
		$ONECOM_WP_ADDONS_API = ONECOM_WP_ADDONS_API;
	} else {
		$ONECOM_WP_ADDONS_API = 'http://wpapi.one.com/';
	}
	$ONECOM_WP_ADDONS_API = rtrim( $ONECOM_WP_ADDONS_API, '/' );
	define( 'MIDDLEWARE_URL', $ONECOM_WP_ADDONS_API.'/api/'.$api_version.'/themes' );
}
/**
* Function to tell URL for localhost
**/
if( ! function_exists( 'localhost_oci_path' ) ) {
	function localhost_oci_path() {
		$url = $_SERVER['REQUEST_URI']; //returns the current URL

		$parts = explode('/',$url);
		$dir = $_SERVER['SERVER_NAME'];
		for ($i = 0; $i <= 1; $i++) {
		 $dir .= $parts[$i] . "/";
		}
		$dir = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" ) ? 'https://'.$dir : 'http://'.$dir;
		$oci_url =  $dir.'wp-content/oci/';
		return $oci_url;
	}
}
if( !defined( 'OCI_URL' ) ) {
	if ( $_SERVER["SERVER_ADDR"] == '127.0.0.1' || $_SERVER["SERVER_ADDR"] == 'localhost' || $_SERVER["SERVER_ADDR"] == '::1' ) {
		define( 'OCI_URL', localhost_oci_path() );
	} else {
		define( 'OCI_URL', WP_CONTENT_URL.'/oci/' );
	}
}
if( !defined( 'OCI_DIR' ) ) {
	define( 'OCI_DIR', realpath( WP_CONTENT_DIR.'/oci/' ) );
}
if( !defined( 'ONECOM_WP_CORE_VERSION' ) ) {
	global $wp_version;
	define( 'ONECOM_WP_CORE_VERSION' , $wp_version );
}
if( !defined( 'ONECOM_PHP_VERSION' ) ) {
	define( 'ONECOM_PHP_VERSION' , phpversion() );
}
/**
* Function to query update
**/
if( ! function_exists( 'onecom_query_check' ) ) {
	function onecom_query_check( $url ) {
		$url = add_query_arg(
			array(
				'wp' => ONECOM_WP_CORE_VERSION,
				'php' => ONECOM_PHP_VERSION
			), $url
		);
		return $url;
	}
}
/**
* Load OCI ajax handler
**/
//echo WP_CONTENT_DIR;
//require_once( WP_CONTENT_DIR.'/oci/ajax.php' );

/** Register OCI assets */
wp_register_style('one-wp-style', OCI_URL . "assets/css/style.css", null, null );
wp_register_script('one-wp-script', OCI_URL . "assets/js/script.js", array( 'jquery', 'thickbox', 'user-profile' ), null );

wp_localize_script( 'one-wp-script', 'oci', array( 'ajaxurl' => OCI_URL.'ajax.php' ) );

$scripts_to_print = array(
	'jquery',
	'one-wp-script',
    'thickbox'
);

/**
* Function to fetch themes
**/
if( ! function_exists( 'oci_fetch_themes' ) ) {
	function oci_fetch_themes() {
		$option_key = 'oci_themes';
		$themes = array();

		//$themes = get_option( $option_key );
		//delete_site_transient( $option_key );
		//$themes = get_site_transient( $option_key );

		$url = onecom_query_check( MIDDLEWARE_URL );

		if( empty( $themes ) || $themes == false ) {
			global $wp_version;
			$args = array(
			    'timeout'     => 5,
			    'httpversion' => '1.0',
			    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			    'body'        => null,
			    'compress'    => false,
			    'decompress'  => true,
			    'sslverify'   => true,
			    'stream'      => false,
			); 
			$response = wp_remote_get( $url, $args );
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );

			$themes = array();

			if( $body->success ) {
				$themes = $body->data->collection;
			}

			//update_option( $option_key, $themes );
			//set_site_transient( $option_key, $themes, 24 * HOUR_IN_SECONDS );
		}
		//$themes = array();

		return $themes;
	}
}

/**
* Function to get categories
**/
/*if( ! function_exists( 'oci_get_categories' ) ) {
	function oci_get_categories() {
		$categories = array(
			'all' => __( 'All', 'oci' ),
			'business-services' => __( 'Business & Services', 'oci' ),
			'portfolio-cv' => __( 'Portfolio & CV', 'oci' ),
			'family-recreation' => __( 'Family & Recreation', 'oci' ),
			'food-hospitality' => __( 'Food & Hospitality', 'oci' ),
			'events' => __( 'Events', 'oci' ),
			'music-art' => __( 'Music & Art', 'oci' ),
			'webshop' => __( 'Webshop', 'oci' )
		);
		asort( $categories );
		return $categories;
	}
}*/
/**
* Function to check if URL has 404 page not found error
**/
if( ! function_exists( 'oci_check_is_404' ) ) {
	function oci_check_is_404( $url ) {
		$url_headers = @get_headers( $url );
		if(!$url_headers || $url_headers[0] == 'HTTP/1.1 404 Not Found') {
		    $exists = true;
		}
		else {
		    $exists = false;
		}
		return $exists;
	}
}