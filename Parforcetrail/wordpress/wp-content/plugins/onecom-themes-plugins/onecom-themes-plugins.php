<?php 
/**
* Plugin Name: 	One.com themes and plugins
* Version: 		0.1.5
* Text Domain: 	onecom-wp
* Domain Path: 	/languages
* Description: 	Personalize your website with custom made themes and plugins exclusive to One.com customers. You can also find a curated list of plugins that we recommend.
* Network: true
* Author: 		One.com
* Author URI: 	https://one.com/
* License:     	GPL v2 or later

Copyright 2017 One.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/
defined( 'ABSPATH' ) or die( 'Cheating Huh!' ); // Security

if( ! defined( 'ONECOM_WP_VERSION' ) ) {
	define( 'ONECOM_WP_VERSION', '0.1.5' );
}
if( ! defined( 'ONECOM_WP_PATH' ) ) {
	define( 'ONECOM_WP_PATH', plugin_dir_path( __FILE__ ) );
}
if( ! defined( 'ONECOM_WP_URL' ) ) {
	define( 'ONECOM_WP_URL', plugin_dir_url( __FILE__ ) );
}
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
	define( 'MIDDLEWARE_URL', $ONECOM_WP_ADDONS_API.'/api/'.$api_version );
}
if( !defined( 'WP_API_URL' ) ) {
	$api_version = '1.0';
	define( 'WP_API_URL', 'https://api.wordpress.org/plugins/info/'.$api_version.'/' );
}
if( !defined( 'ONECOM_WP_CORE_VERSION' ) ) {
	global $wp_version;
	define( 'ONECOM_WP_CORE_VERSION' , $wp_version );
}
if( !defined( 'ONECOM_PHP_VERSION' ) ) {
	define( 'ONECOM_PHP_VERSION' , phpversion() );
}

add_action( 'admin_init', 'onecom_check_for_get_request', -1 );
if( ! function_exists( 'onecom_check_for_get_request' ) ) {
	function onecom_check_for_get_request() {
		if( isset( $_GET[ 'request' ] ) && $_GET[ 'request' ] != '' ) {
			if( wp_verify_nonce($_GET['_wpnonce'], '_wpnonce') ) {
				delete_site_transient( 'onecom_'.$_GET[ 'request' ] );
				wp_safe_redirect( wp_get_referer() );
				die();
			}
			return;
		}
		return;
	}
}

add_action( 'plugins_loaded', 'onecom_wp_load_textdomain', -1);
if( ! function_exists( 'onecom_wp_load_textdomain' ) ) {
	function onecom_wp_load_textdomain() {
		//echo basename( dirname( __FILE__ ) ) . '/languages'; die();
		load_plugin_textdomain( 'onecom-wp', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
}

/**
* Limit load of resources on only specific admin pages to optimize loading time
*/
/* Add hook to following array where you want to enquque your resources */
global $load_onecom_wp_resources_slugs;
$load_onecom_wp_resources_slugs = array(
	'toplevel_page_onecom-wp',
	'one-com_page_onecom-wp-themes',
	'one-com_page_onecom-wp-plugins',
	'one-com_page_onecom-wp-recommended-plugins',
	'admin_page_onecom-wp-recommended-plugins'
	//'one-com_page_onecom-wp-images',
);
$load_onecom_wp_resources_slugs = apply_filters( 'load_onecom_wp_resources_slugs', $load_onecom_wp_resources_slugs ); 

add_action( 'limit_enqueue_resources', 'limit_enqueue_resources_callback', 10, 3 );
if( ! function_exists( 'limit_enqueue_resources_callback' ) ) {
	function limit_enqueue_resources_callback( $handle, $hook, $type ) {
		global $load_onecom_wp_resources_slugs;
		if( in_array( $hook, $load_onecom_wp_resources_slugs) ) { // checking hook with provided array to be allowed
			if( $type == 'style' ) {
				wp_enqueue_style( $handle ); // if allowed, enqueue the style
			} else if( $type == 'script' ) {
				wp_enqueue_script( $handle ); // if allowed, enqueue the script
			}
		}
	}
}

add_action( 'admin_enqueue_scripts', 'register_one_core_resources' );
if( ! function_exists( 'register_one_core_resources' ) ) {
	function register_one_core_resources( $hook ) {
		$resource_extension = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : '.min'; // Adding .min extension if SCRIPT_DEBUG is enabled
		$resource_min_dir = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : 'min-'; // Adding min- as a minified directory of resources if SCRIPT_DEBUG is enabled

		wp_register_style( 
			$handle = 'one-font-icon', 
			$src = ONECOM_WP_URL.'assets/fonts/onecom/style.css', 
			$deps = null, 
			$ver = ONECOM_WP_VERSION, 
			$media = 'all'
		);
		wp_enqueue_style( 'one-font-icon' );
		wp_register_style( 
			$handle = 'onecom-wp', 
			$src = ONECOM_WP_URL.'assets/'.$resource_min_dir.'css/style'.$resource_extension.'.css', 
			$deps = null, 
			$ver = ONECOM_WP_VERSION, 
			$media = 'all'
		);

		wp_register_script( 
			$handle = 'onecom-wp', 
			$src = ONECOM_WP_URL.'assets/'.$resource_min_dir.'js/script'.$resource_extension.'.js', 
			$deps = array( 'jquery', 'thickbox' ), 
			$ver = ONECOM_WP_VERSION
		);
		wp_localize_script( 'onecom-wp', 'onecom_vars', 
			array(
		    	'network' => ( is_network_admin() && is_multisite() ) ? true : false
		    )
		);

		wp_register_style( 
			$handle = 'onecom-promo', 
			$src = ONECOM_WP_URL.'assets/'.$resource_min_dir.'css/promo'.$resource_extension.'.css', 
			$deps = null, 
			$ver = ONECOM_WP_VERSION, 
			$media = 'all'
		);

		wp_register_script( 
			$handle = 'onecom-promo', 
			$src = ONECOM_WP_URL.'assets/'.$resource_min_dir.'js/promo'.$resource_extension.'.js', 
			$deps = array( 'jquery' ), 
			$ver = ONECOM_WP_VERSION
		);

		/**
		* Hooking resource into limit utilization
		**/
		do_action( 'limit_enqueue_resources', $handle = 'onecom-wp', $hook, $type = 'style' );
		do_action( 'limit_enqueue_resources', $handle = 'onecom-wp', $hook, $type = 'script' );

		/* Google fonts */
		wp_register_style( 
			$handle = 'onecom-wp-google-fonts', 
			$src = '//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800', 
			$deps = null, 
			$ver = null, 
			$media = 'all'
		);
		do_action( 'limit_enqueue_resources', $handle = 'onecom-wp-google-fonts', $hook, $type = 'style' );
	}
}

add_action( 'admin_menu', 'one_core_admin', -1 );
add_action( 'network_admin_menu', 'one_core_admin', -1 );
if( ! function_exists( 'one_core_admin' ) ) {
	function one_core_admin() {
		if( ! is_network_admin() && is_multisite() ) {
			return false;
		}
		$position = onecom_get_free_menu_position( '2.1' );
		
		// save for other One.com plugins and themes
		global $onecom_generic_menu_position;
		$onecom_generic_menu_position = $position;
		
		add_menu_page( 
			$page_title = __( 'One.com', 'onecom-wp' ),
			$menu_title = __( 'One.com', 'onecom-wp' ),
			$capability = 'manage_options',
			$menu_slug = 'onecom-wp',
			$function = 'one_core_admin_callback',
			$icon_url = 'dashicons-admin-generic',
			$position
		);
		add_submenu_page( 
			$parent_slug = 'onecom-wp', 
			$page_title = __( 'Themes', 'onecom-wp' ), 
			$menu_title = __( 'Themes', 'onecom-wp' ), 
			$capability = 'manage_options', 
			$menu_slug = 'onecom-wp-themes', 
			$function = 'one_core_theme_listing_callback' 
		);
		add_submenu_page( 
			$parent_slug = 'onecom-wp', 
			$page_title = __( 'Plugins', 'onecom-wp' ), 
			$menu_title = __( 'Plugins', 'onecom-wp' ), 
			$capability = 'manage_options', 
			$menu_slug = 'onecom-wp-plugins', 
			$function = 'one_core_plugin_listing_callback' 
		);
		add_submenu_page( 
			$parent_slug = null, // adding null to hide from submenu
			$page_title = __( 'Plugins', 'onecom-wp' ), 
			$menu_title = __( 'Plugins', 'onecom-wp' ), 
			$capability = 'manage_options', 
			$menu_slug = 'onecom-wp-recommended-plugins', 
			$function = 'one_core_recommended_plugin_listing_callback' 
		);
		remove_submenu_page('onecom-wp','onecom-wp'); // remove admin duplicate menu item 
	}
}

add_action( 'admin_bar_menu', 'add_one_bar_items', 100, 100 );
if( ! function_exists( 'add_one_bar_items' ) ) {
	function add_one_bar_items( $admin_bar ) {
		if( ! is_network_admin() && is_multisite() ) {
			return false;
		}
		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$args = array(
		    'id'    => 'onecom-wp',
		    //'parent' => 'top-secondary',
		    'title' => __( 'One.com', 'onecom-wp' ),
		    'href'  => ( is_multisite() && is_network_admin() ) ?  network_admin_url( 'admin.php?page=onecom-wp' ) : admin_url( 'admin.php?page=onecom-wp' ),
		    'meta'  => array(
		        'title' => __( 'One.com', 'onecom-wp' ),
		        'class' => 'onecom-wp-admin-bar-item'
		    ),
		);
		$admin_bar->add_menu( $args );

		$args = array(
		    'id'    => 'onecom-wp-themes',
		    'parent' => 'onecom-wp',
		    'title' => __( 'Themes', 'onecom-wp' ),
		    'href'  => ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-themes' ) : admin_url( 'admin.php?page=onecom-wp-themes' ),
		    'meta'  => array(
		        'title' => __( 'Themes', 'onecom-wp' ),
		    ),
		);
		$admin_bar->add_menu( $args );

		$args = array(
		    'id'    => 'onecom-wp-plugins',
		    'parent' => 'onecom-wp',
		    'title' => __( 'Plugins', 'onecom-wp' ),
		    'href'  => ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-plugins' ) : admin_url( 'admin.php?page=onecom-wp-plugins' ),
		    'meta'  => array(
		        'title' => __( 'Plugins', 'onecom-wp' ),
		    ),
		);
		$admin_bar->add_menu( $args );

		/*
		* Account link to Control Panel
		*/
		$args = array(
		    'id'    => 'one-cp',
		    'parent' => 'onecom-wp',
		    'title' => __( 'One.com Control Panel', 'onecom-wp' ),
		    'href'  => 'https://www.one.com/admin/wp-overview.do',
		    'meta'  => array(
		        'title' => __( 'Go to Control Panel at One.com', 'onecom-wp' ),
		        'target' => '_blank'
		    ),
		);
		$admin_bar->add_menu( $args );

		/*
		* WordPress support
		*/
		$locale = get_locale();
		$code = explode( '_', $locale );
		if( isset( $code[0] ) && $code[0] != '' ) {
			$lang = $code[0];
		} else {
			$lang = 'en';
		}


		$args = array(
		    'id'    => 'one-wp-support',
		    'parent' => 'onecom-wp',
		    'title' => __( 'One.com Guides & FAQ', 'onecom-wp' ),
		    'href'  => 'https://www.one.com/'.$lang.'/support/guides-faq#1clickwordpress',
		    'meta'  => array(
		        'title' => __( 'Go to Guides & FAQ at One.com', 'onecom-wp' ),
		        'target' => '_blank'
		    ),
		);
		$admin_bar->add_menu( $args );
	}
}

if( ! function_exists( 'one_core_admin_callback' ) ) {
	function one_core_admin_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/'.$network.'theme-listing.php';
	}
}

if( ! function_exists( 'one_core_theme_listing_callback' ) ) {
	function one_core_theme_listing_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/'.$network.'theme-listing.php';
	}
}

if( ! function_exists( 'one_core_plugin_listing_callback' ) ) {
	function one_core_plugin_listing_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/'.$network.'plugin-listing.php';
	}
}

if( ! function_exists( 'one_core_recommended_plugin_listing_callback' ) ) {
	function one_core_recommended_plugin_listing_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/'.$network.'recommended-plugin-listing.php';
	}
}

/**
* Function to get free position for menu 
**/
if( ! function_exists( 'onecom_get_free_menu_position' ) ) {
	function onecom_get_free_menu_position($start, $increment = 0.3) {
	    foreach ($GLOBALS['menu'] as $key => $menu) {
	        $menus_positions[] = $key;
	    }

	    if (!in_array($start, $menus_positions)) return $start;

	    /* the position is already reserved find the closet one */
	    while (in_array($start, $menus_positions)) {
	        $start += $increment;
	    }

	    return (string) $start;
	}
}

/**
* One.com updater
**/
if( ! class_exists( 'ONECOM_UPDATER' ) ) {
    require_once plugin_dir_path( __FILE__ ).'/inc/update.php';
}

/**
* General functions
**/
add_action( 'admin_init', 'onecom_admin_init_callback' );
if( ! function_exists( 'onecom_admin_init_callback' ) ) {
	function onecom_admin_init_callback() {
		require_once plugin_dir_path( __FILE__ ).'/inc/functions.php';
	}
}