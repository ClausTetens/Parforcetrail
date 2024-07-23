<?php
if( ! function_exists( 'onecom_fetch_themes' ) ) {
	function onecom_fetch_themes() {
		$themes = array();

	//	delete_site_transient( 'onecom_themes' );
		$themes = get_site_transient( 'onecom_themes' );

		if ( ( ! $themes ) || empty( $themes ) ) {
			$fetch_themes_url = MIDDLEWARE_URL.'/themes';

			$fetch_themes_url = onecom_query_check( $fetch_themes_url );

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
			$response = wp_remote_get( $fetch_themes_url, $args );

			if( is_wp_error( $response ) ) {
				if( isset( $response->errors[ 'http_request_failed' ] ) ) {
					$errorMessage = __( 'Connection timed out', 'onecom-wp' );
				} else {
					$errorMessage = $response->get_error_message();
				}
			} else {
				if( wp_remote_retrieve_response_code( $response ) != 200 ) {
					$errorMessage = '('.wp_remote_retrieve_response_code( $response ).') '.wp_remote_retrieve_response_message( $response );
				} else {
					$body = wp_remote_retrieve_body( $response );

					$body = json_decode( $body );

					if( ! empty($body) && $body->success ) {
						$themes = $body->data->collection;
					} elseif ( $body->success == false ) {
						if( $body->error == 'RESOURCE NOT FOUND' ) {
							$try_again_url = add_query_arg(
								array(
									'request' => 'themes',
								),
								''
							);
							$try_again_url = wp_nonce_url( $try_again_url, '_wpnonce' );
							$errorMessage = __( 'Sorry, no compatible themes found for your version of WordPress and PHP.', 'onecom-wp' ).'&nbsp;<a href="'.$try_again_url.'">'.__( 'Try again', 'onecom-wp' ).'</a>';
						} else {
							$errorMessage = $body->error;
						}
					}	
				}

				set_site_transient( 'onecom_themes', $themes, 3 * HOUR_IN_SECONDS );
			}
		}

		if( empty( $themes ) ) {		
			$themes = new WP_Error( 'message', $errorMessage );
		}

		return $themes;
	}
}

/**
* Function to handle install a theme
**/
if( ! function_exists( 'onecom_install_theme_callback' ) ) {
	function onecom_install_theme_callback() {
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );

		if ( 
			get_option( 'auto_updater.lock' ) // else if auto updater lock present
			|| get_option( 'core_updater.lock' ) // else if core updater lock present
		) {
			$response[ 'type' ] = 'error';
			$response[ 'message' ] = __( 'WordPress is being upgraded. Please try again later.', 'onecom-wp' );
			echo json_encode( $response );
			wp_die();
		}

		$theme_slug = wp_unslash( $_POST[ 'theme_slug' ] );
		$redirect = ( isset( $_POST[ 'redirect' ] ) ) ? $_POST[ 'redirect' ] : false;
		$network = ( isset( $_POST[ 'network' ] ) ) ? (boolean) $_POST[ 'network' ] : false;

		$theme_info = onecom_get_theme_info( $theme_slug );
		$theme_info->download_link = MIDDLEWARE_URL.'/themes/'.$theme_info->slug.'/download';

		add_filter('http_request_reject_unsafe_urls','__return_false');
		add_filter( 'http_request_host_is_external', '__return_true' );

		$title = sprintf( __( 'Installing theme', 'onecom-wp' ) );
		$nonce = 'theme-install';
		$url = add_query_arg(
			array(
				'package' => basename( $theme_info->download_link ), 
				'action' => 'install',
			), 
			admin_url() 
		);

		$type = 'web'; //Install plugin type, From Web or an Upload.

		$skin     = new WP_Ajax_Upgrader_Skin( compact('type', 'title', 'nonce', 'url') );
		$upgrader = new Theme_Upgrader( $skin );
		$result   = $upgrader->install( $theme_info->download_link );

		$status = array(
			'slug' => $theme_info->slug
		);

		$default_error_message = __( 'Something went wrong. Please contact the support at One.com.', 'onecom-wp' );

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the file system. Please contact the support at One.com.', 'onecom-wp' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}
		}

		$status['themeName'] = wp_get_theme( $theme_slug )->get( 'Name' );

		$response[ 'type' ] = 'error';
		$response[ 'message' ] = ( isset( $status[ 'errorMessage' ] ) ) ? $status[ 'errorMessage' ] : $default_error_message ;

		if( $result == true ) {
			$response[ 'type' ] = 'success';
			$response[ 'message' ] = __( 'Theme installed successfully', 'onecom-wp' );

			if( $redirect != false ) {
				$button_html = '<span class="action-text one-activate-theme">'.__( 'Activate', 'onecom-wp' ).'</span>';
			} else {
				if( $network ) {
					$activate_url = add_query_arg( array(
						'action'     => 'enable',
						'_wpnonce'   => wp_create_nonce( 'enable-theme_' . $theme_slug ),
						'theme' => $theme_slug,
					), network_admin_url( 'themes.php' ) );
				} else {
					$activate_url = add_query_arg( array(
						'action'     => 'activate',
						'_wpnonce'   => wp_create_nonce( 'switch-theme_' . $theme_slug ),
						'stylesheet' => $theme_slug,
					), admin_url( 'themes.php' ) ); 
				}
				$button_html = '<a href="'.$activate_url.'">'.__( 'Activate', 'onecom-wp' ).'</a>';
			}
			
			$response[ 'button_html' ] = $button_html;
		}

		$response[ 'status' ] = $status;

		echo json_encode( $response );

		wp_die();
	}
}
add_action( 'wp_ajax_onecom_install_theme', 'onecom_install_theme_callback' );

if( ! function_exists( 'onecom_fetch_plugins' ) ) {
	function onecom_fetch_plugins( $recommended = false ) {
		$plugins = array();
		
		//delete_site_transient( 'onecom_plugins' );
		//delete_site_transient( 'onecom_recommended_plugins' );

		if( $recommended ) {
			$plugins = get_site_transient( 'onecom_recommended_plugins' );
			$fetch_plugins_url = MIDDLEWARE_URL.'/recommended-plugins';
		} else {
			$plugins = get_site_transient( 'onecom_plugins' );
			$fetch_plugins_url = MIDDLEWARE_URL.'/plugins';
		}

		$fetch_plugins_url = onecom_query_check( $fetch_plugins_url );

		if ( ( ! $plugins ) || empty( $plugins ) ) {		
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
			$response = wp_remote_get( $fetch_plugins_url, $args );

			if( is_wp_error( $response ) ) {
				if( isset( $response->errors[ 'http_request_failed' ] ) ) {
					$errorMessage = __( 'Connection timed out', 'onecom-wp' );
				} else {
					$errorMessage = $response->get_error_message();
				}
			} else {
				if( wp_remote_retrieve_response_code( $response ) != 200 ) {
					$errorMessage = '('.wp_remote_retrieve_response_code( $response ).') '.wp_remote_retrieve_response_message( $response );
				} else {
					$body = wp_remote_retrieve_body( $response );

					$body = json_decode( $body );

					if( ! empty($body) && $body->success ) {
						if( $recommended ) { 
							$plugins = $body->data;
							
						} else {
							$plugins = $body->data->collection;
						}
						
					} elseif ( $body->success == false ) {
						if( $body->error == 'RESOURCE NOT FOUND' ) {
							if( $recommended ) { 
								$args = array(
									'request' => 'recommended_plugins',
								);
							} else {
								$args = array(
									'request' => 'plugins',
								);
							}
							$try_again_url = add_query_arg(
								$args, ''
							);
							$try_again_url = wp_nonce_url( $try_again_url, '_wpnonce' );
							$errorMessage = __( 'Sorry, no compatible plugins found with your version of WordPress and PHP.', 'onecom-wp' ).'&nbsp;<a href="'.$try_again_url.'">'.__( 'Try again', 'onecom-wp' ).'</a>';
						} else {
							echo $body->error;
						}
					}
				}

				if( $recommended ) {
					set_site_transient( 'onecom_recommended_plugins', $plugins, 3 * HOUR_IN_SECONDS );
				} else {
					set_site_transient( 'onecom_plugins', $plugins, 3 * HOUR_IN_SECONDS );
				}
			}		
		}

		if( empty( $plugins ) ) {		
			$plugins = new WP_Error( 'message', $errorMessage );
		}

		return $plugins;
	}
}

/**
* Ajax handler to activate theme 
**/
if( ! function_exists( 'onecom_activate_theme_callback' ) ) {
	function onecom_activate_theme_callback() {
		$theme_slug = wp_unslash( $_POST[ 'theme_slug' ] );
		$redirect = $_POST[ 'redirect' ];
		$is_activate = switch_theme( $theme_slug );
		$response = array();
		//$response[ 'activated' ] = $is_activate;
		$response[ 'type' ] = 'redirect';
		$response[ 'url' ] = $redirect;

		echo json_encode( $response );
		wp_die();

	}
}
add_action( 'wp_ajax_onecom_activate_theme', 'onecom_activate_theme_callback' );

/**
* Function to check if URL has 404 page not found error
**/
if( ! function_exists( 'onecom_check_is_404' ) ) {
	function onecom_check_is_404( $url ) {
		stream_context_set_default(
		    array(
		        'http' => array(
		            'timeout' => 2
		        )
		    )
		);
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

/**
* Function to check valid API request
**/
if( ! function_exists( 'onecome_valid_request' ) ) {
	function onecome_valid_request( $url ) {
		global $wp_version;
		$args = array(
		    'timeout'     => 0.5,
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
		$result = json_decode( $body );
		
		if( isset( $result->success ) ) {
			if( $result->success == false ) {
				return false;
			}
		}
		return true;
	}
}

/**
*	It will return key of array of objects based on search value and search key 
**/
if( ! function_exists( 'onecom_search_key_in_object' ) ) {
	function onecom_search_key_in_object( $search_value, $array, $search_key ) {
	   foreach ( $array as $key => $val ) {
	       if ( $val->$search_key === $search_value ) {
	           return $key;
	       }
	   }
	   return null;
	}
}

/**
* Function to get theme info
**/
if( ! function_exists( 'onecom_get_theme_info' ) ) {
	function onecom_get_theme_info( $slug ) {
		if( $slug == '' ) {
			return new WP_Error( 'message', 'Theme slug should not be empty' );
		}
		$themes = get_site_transient( 'onecom_themes' );
		if( empty( $themes ) ) {
			return new WP_Error( 'message', 'No themes found locally' );
		}
		$key = onecom_search_key_in_object( $slug, $themes, 'slug' );
		return $themes[ $key ];
	}
}

/**
* Function to get theme info
**/
if( ! function_exists( 'onecom_get_plugin_info' ) ) {
	function onecom_get_plugin_info( $slug, $type ) {
		if( $slug == '' ) {
			return new WP_Error( 'message', 'Plugin slug should not be empty' );
		}
		$plugins = ( $type == 'recommended' ) ? get_site_transient( 'onecom_recommended_plugins_meta' ) : get_site_transient( 'onecom_plugins' );
		if( empty( $plugins ) ) {
			return new WP_Error( 'message', 'No plugins found locally' );
		}
		$key = onecom_search_key_in_object( $slug, $plugins, 'slug' );
		return $plugins[ $key ];
	}
}

/**
* Check if theme installed
**/
if( ! function_exists( 'onecom_is_theme_installed' ) ) {
	function onecom_is_theme_installed( $theme_slug ) {
		$path = get_theme_root().'/'.$theme_slug.'/';
		if( file_exists($path) ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
* Function to handle plugin installation
**/
if( ! function_exists( 'onecom_install_plugin_callback' ) ) {
	function onecom_install_plugin_callback() {
		$plugin_type = ( isset( $_POST[ 'plugin_type' ] ) ) ? wp_unslash( $_POST[ 'plugin_type' ] ) : 'normal';
		$download_url = ( isset( $_POST[ 'download_url' ] ) ) ? $_POST[ 'download_url' ] : '';
		$plugin_slug = wp_unslash( $_POST[ 'plugin_slug' ] );
		$plugin_name = wp_unslash( $_POST[ 'plugin_name' ] );
		$redirect = ( isset( $_POST[ 'redirect' ] ) ) ? $_POST[ 'redirect' ] : false;

		if ( 
			get_option( 'auto_updater.lock' ) // else if auto updater lock present
			|| get_option( 'core_updater.lock' ) // else if core updater lock present
		) {
			$response[ 'type' ] = 'error';
			$response[ 'message' ] = __( 'WordPress is being upgraded. Please try again later.', 'onecom-wp' );
			echo json_encode( $response );
			wp_die();
		}

		$plugin_info = onecom_get_plugin_info( $plugin_slug, $plugin_type );
		$plugin_info->slug = $plugin_slug;

		if( $plugin_type == 'recommended' ) {
			$plugin_info->download_link = $download_url;
		} else {
			$plugin_info->download_link = MIDDLEWARE_URL.'/plugins/'.$plugin_info->slug.'/download';
		}
		
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		add_filter('http_request_reject_unsafe_urls','__return_false');
		add_filter( 'http_request_host_is_external', '__return_true' );

		$title = sprintf( __( 'Installing plugin', 'onecom-wp' ) );
		$nonce = 'plugin-install';
		$url = add_query_arg(
			array(
				'package' => basename( $download_url ), 
				'action' => 'install',
				//'page' => 'page',
				//'step' => 'theme'
			), 
			admin_url() 
		);

		$type = 'web'; //Install plugin type, From Web or an Upload.

		$skin     = new WP_Ajax_Upgrader_Skin( compact('type', 'title', 'nonce', 'url') );
		//$skin = new Plugin_Installer_Skin( compact('type', 'title', 'nonce', 'url') );
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $plugin_info->download_link );

		$default_error_message = __( 'Something went wrong. Please contact the support at One.com.', 'onecom-wp' );

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			//wp_send_json_error( $status );
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			//wp_send_json_error( $status );
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
			//wp_send_json_error( $status );
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the file system. Please contact the support at One.com.', 'onecom-wp' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			//wp_send_json_error( $status );
		}
		$response[ 'type' ] = 'error';
		$response[ 'message' ] = ( isset( $status[ 'errorMessage' ] ) ) ? $status[ 'errorMessage' ] : $default_error_message ;

		if( $result == true ) {
			$status = install_plugin_install_status( $plugin_info );
			$response[ 'type' ] = 'success';
			$response[ 'message' ] = __( 'Plugin installed successfully', 'onecom-wp' );
			$admin_url = ( is_multisite() ) ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
			$activateUrl = add_query_arg( array(
				'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
				'action'   => 'activate',
				'plugin'   => $status['file'],
			), $admin_url );
			if( $redirect == false || $redirect == '' || is_multisite() ) {
				$button_html = '<a href="'.$activateUrl.'" class="button button-primary activate-plugin">'.__( 'Activate', 'onecom-wp' ).'</a>';
			} else {
				$button_html = '<a class="activate-plugin activate-plugin-ajax button button-primary" href="javascript:void(0)" data-action="onecom_activate_plugin" data-redirect="'.$redirect.'" data-slug="'.$status['file'].'" data-name="'.$plugin_name.'">'.__( 'Activate', 'onecom-wp' ).'</a>';
			}
			$response[ 'button_html' ] = $button_html;
			$response[ 'info' ] = $plugin_info;
		}

		$response[ 'status' ] = $status;
		
		echo json_encode( $response );

		wp_die();
	}
}
add_action( 'wp_ajax_onecom_install_plugin', 'onecom_install_plugin_callback' );

/**
* Ajax handler to activate theme 
**/
if( ! function_exists( 'onecom_activate_plugin_callback' ) ) {
	function onecom_activate_plugin_callback() {
		$plugin_slug = wp_unslash( $_POST[ 'plugin_slug' ] );
		$redirect = $_POST[ 'redirect' ];
		$is_activate = activate_plugin( $plugin_slug );
		$response = array();

		if( is_wp_error( $is_activate ) ) {
			$response[ 'type' ] = 'error';
			$response[ 'message' ] = $is_activate->get_error_message();
		} else {
			$response[ 'type' ] = 'redirect';
			$response[ 'url' ] = $redirect;
		}

		echo json_encode( $response );
		wp_die();

	}
}
add_action( 'wp_ajax_onecom_activate_plugin', 'onecom_activate_plugin_callback' );

/**
* An alternative for thumbnail
**/
if( ! function_exists( 'onecom_string_acronym' ) ) {
	function onecom_string_acronym( $name ) {
		preg_match_all('/\b\w/', $name, $acronym);
		$str = implode( '', $acronym[0] );
		return substr($str, 0, 3);
	}
}

/**
* Pick random flat color 
**/
if( ! function_exists( 'onecom_random_color' ) ) {
	function onecom_random_color( $key = null ) {
		$array = array(
			'#FFC107', //yellow
			'#3498db', // peter river
			'#2ecc71', // emerald
			'#9b59b6', // Amethyst
			'#f1c40f', // sun flower
			'#e74c3c', // alizarin
			'#1abc9c', // turquoise
			'#00BCD4', // cyan,
			'#E91E63', // pink
			'#34495e', // wet asphalt
			'#CDDC39', // lime
			'#03A9F4', // light blue,
			'#8BC34A', // light green
			'#9C27B0', // purple
			'#3F51B5', // indigo
			'#F44336', // red
			'#009688', // teal
			
		);
		if( $key == null ) {
			$key = array_rand( $array );
		} else {
			$array_keys = array_keys( $array );
			if( ! in_array( $key, $array_keys) ) {
				$key = array_rand( $array );
			}
		}
		
		return $array[ $key ];
	}
}

/**
* Function to query update
**/
if( ! function_exists( 'onecom_query_check' ) ) {
	function onecom_query_check( $url ) {
		//echo ( function_exists( 'add_query_arg' ) ) ? 'EXISTS' : 'NOT EXISTS';
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
* Function which will display admin notices
**/
if( ! function_exists( 'onecom_generic_promo' ) ) {
	function onecom_generic_promo() {
		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wp_version;

		//delete_site_transient( 'onecom_promo' );
		//delete_site_option( 'onecom_local_promo' );

		$is_transient = get_site_transient( 'onecom_promo' );

		if( ! $is_transient ) {
			$url = MIDDLEWARE_URL.'/promo';
			$args = array(
			    'timeout'     => 10,
			    'httpversion' => '1.0',
			    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			    'body'        => null,
			    'compress'    => false,
			    'decompress'  => true,
			    'sslverify'   => true,
			    'stream'      => false,
			); 	

			$x_promo_transient = 48; // default transient value

			$response = wp_remote_get( $url, $args );
			if( ! is_wp_error( $response ) ) {
				$local_promo = array();
				$local_promo = get_site_option( 'onecom_local_promo' );
				$x_promo_check = wp_remote_retrieve_header( $response, $header = 'X-ONECOM-Promo' );
				$x_promo_transient = wp_remote_retrieve_header( $response, $header = 'X-ONECOM-Transient' );
	
				$result = wp_remote_retrieve_body( $response );

				$json = json_decode( $result );
 				if (json_last_error() === 0) {
 					$result = '';
 				}

				if( isset( $local_promo[ 'xpromo' ] ) && $local_promo[ 'xpromo' ] == $x_promo_check ) {
					$local_promo[ 'html' ] = $result;
				} else {
					$local_promo[ 'show' ] = true;
					$local_promo[ 'html' ] = $result;
					$local_promo[ 'xpromo' ] = $x_promo_check;
				}
				update_site_option( 'onecom_local_promo', $local_promo );
			}

			set_site_transient( 'onecom_promo', true, $x_promo_transient * HOUR_IN_SECONDS );
		}

		$local_promo = get_site_option( 'onecom_local_promo' );

		if( ( isset( $local_promo[ 'show' ] ) && $local_promo[ 'show' ] == true ) && ( isset( $local_promo[ 'html' ] ) && $local_promo[ 'html' ] != '' ) ) {
			wp_enqueue_style( 'onecom-promo' );
			wp_enqueue_script( 'onecom-promo' );
			echo $local_promo[ 'html' ]; 
		}
	}
}
add_action( 'admin_notices', 'onecom_generic_promo' );

/**
* Ajax handler for dismissable notice request
**/
if( ! function_exists( 'onecom_dismiss_notice_callback' ) ) {
	function onecom_dismiss_notice_callback() {
		$local_promo = get_site_option( 'onecom_local_promo' );
		$local_promo[ 'show' ] = false;
		$is_update = update_site_option( 'onecom_local_promo', $local_promo );
		if( $is_update ) {
			echo 'Notice dismissed';
		} else {
			echo 'Notice cannot dismissed';
		}
		wp_die();
	}
}
add_action( 'wp_ajax_onecom_dismiss_notice', 'onecom_dismiss_notice_callback' );