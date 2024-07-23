(function($){
	$(document).ready(function(){
		// check hash if in URL
		/*var hash = window.location.hash;
		if( $( hash ).length > 0 ) { // if hash exists on document
			$( '.nav-tab' ).each( function( key, nav_tab ){
				var href = $( nav_tab ).attr( 'href' );
				if( href == hash ) {
					$( '.one_wrap .tab' ).removeClass( 'active-tab' );
					$( '.one_wrap .nav-tab' ).removeClass( 'nav-tab-active' );

					$( href ).addClass( 'active-tab' );
					$( nav_tab ).addClass( 'nav-tab-active' );
				}
			} );			
		}

		$( '.nav-tab' ).click(function(e){
			e.preventDefault();
			var href = $(this).attr( 'href' );
			
			$( '.one_wrap .tab' ).removeClass( 'active-tab' );
			$( '.one_wrap .nav-tab' ).removeClass( 'nav-tab-active' );

			$( href ).addClass( 'active-tab' );
			$( this ).addClass( 'nav-tab-active' );
			history.pushState(null, null, href);
		});*/

		screenshotPreview();

		/**
		* Handles theme installation
		**/
		$( '.one-install, .one-installed' ).click( function(){
			var button = $( this );
			var theme_wrapper = $( this ).parents( '.one-theme:first' );
			
			var name = $(this).attr( 'data-name' );
            var theme_slug = $( this ).attr( 'data-theme_slug' );
            var redirect = $( this ).attr( 'data-redirect' );
            var network = onecom_vars.network;

            if( typeof name == 'undefined' || name == '' ) {
                return;
            }

            if( typeof network == 'undefined' ) {
                network = false;
            }

            var data = {
            	'action' : name,
            	'theme_slug' : theme_slug,
                'redirect' : redirect,
                'network' : network
            }

			$( theme_wrapper ).addClass( 'active' );
			$( '.loading-overlay' ).addClass( 'show' );

			$.post(ajaxurl, data, function( response ) {
				var result = $.parseJSON( response );

				console.log(result);
				
				if( typeof result.type != 'undefined' && result.type == 'redirect' ) { 
					window.location = result.url;
				} else {
                    $( '.onecom-notifier' ).html( result.message ).attr( 'type', result.type ).addClass( 'show' );
                    var time_to_show_message = 5000;
                    if( result.type == 'success' ) {
                    	$( theme_wrapper ).addClass( 'installed' );
                    	$( button ).removeClass( 'one-install' ).addClass( 'one-installed' );
                    	$( button ).find( '.action-text' ).remove();
                        $( button ).find( '> span' ).append( result.button_html );
                        $( button ).attr( 'data-name', 'onecom_activate_theme' );
                        time_to_show_message = 1500;
                    }
                    setTimeout( function(){
                        $( '.onecom-notifier' ).removeClass( 'show' );
                        $( '.loading-overlay' ).removeClass( 'show' );
                    }, time_to_show_message );
                }

			} );
		});

		/**
		* Handles plugin installation
		**/
		$( document ).on( 'click', '.install-now, .activate-plugin-ajax', function(event){ 
			event.preventDefault();
			var button = $( this );
			var plugin_card = $( this ).parents( '.one-plugin-card:first' );

			var download_url = $( this ).attr( 'data-download_url' );
            var plugin_slug = $( this ).attr( 'data-slug' );
            var plugin_name = $( this ).attr( 'data-name' );
            var action = $( this ).attr( 'data-action' );
            var redirect = $( this ).attr( 'data-redirect' );
            var plugin_type = ( typeof( $( this ).attr( 'data-plugin_type' ) ) != 'undefined' ) ? $( this ).attr( 'data-plugin_type' ) : '';

            $( '.loading-overlay' ).addClass( 'show' );

            var data = {
            	action : action,
            	plugin_slug : plugin_slug,
            	plugin_name : plugin_name,
            	download_url : download_url,
            	plugin_type : plugin_type,
                redirect : redirect
            }

            $.post(ajaxurl, data, function( response ) {
				var result = $.parseJSON( response );

				console.log(result);

				if( typeof result.type != 'undefined' && result.type == 'redirect' ) { 
					window.location = result.url;
				} else {
                    $( '.onecom-notifier' ).html( result.message ).attr( 'type', result.type ).addClass( 'show' );
                    var time_to_show_message = 5000;
                    if( result.type == 'success' ) {
                        //if( typeof result.status.activateUrl != 'undefined' && result.status.activateUrl != '' ) {
                            $( plugin_card ).addClass( 'activate' );
                            $( button ).after( result.button_html );
                            $( button ).remove();
                        //} 
                        /*else {
                            $( plugin_card ).addClass( 'installed' );
                            $( button )
                                .text( 'Installed' )
                                .removeClass( 'install-now' )
                                .addClass( 'installed-plugin' )
                                .attr( 'disabled', true );
                        }*/
                    	time_to_show_message = 1500;
                    }
                    setTimeout( function(){
                        $( '.onecom-notifier' ).removeClass( 'show' );
                        $( '.loading-overlay' ).removeClass( 'show' );
                    }, time_to_show_message );
                }
			});

		});
	});

	this.screenshotPreview = function(){	
		/* CONFIG */
			
			xOffset = 10;
			yOffset = 30;
			
			// these 2 variable determine popup's distance from the cursor
			// you might want to adjust to get the right result
			
		/* END CONFIG */
		$(".one-screenshot").hover(function(e){
			this.t = this.title;
			this.title = "";	
			var c = (this.t != "") ? "<br/>" + this.t : "";
			$("body").append("<p id='one-screenshot'><img src='"+ $(this).attr('data-preview') +"' alt='url preview' />"+ c +"</p>");								 
			$("#screenshot")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px")
				.fadeIn("fast");						
	    },
		function(){
			this.title = this.t;	
			$("#one-screenshot").remove();
	    });	
		$(".one-screenshot").mousemove(function(e){
			$("#one-screenshot")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px");
		});			
	};

	/**
	* Snippet to handle thickbox full size 
	**/
	function onecom_resize_thickbox(){
		TB_WIDTH = ( ( $( window ).width() * 75 ) / 100 );
		TB_HEIGHT = ( ( $( window ).height() * 85 ) / 100 );
		$("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
		$("#TB_window").css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px', height: TB_HEIGHT + 'px'});
	}

	$(window).on( 'resize', onecom_resize_thickbox );
	
	$( document ).on( 'thickbox:iframe:loaded', function( e ) {
		onecom_resize_thickbox();
        // Small Snippet to hide install button
        $('#TB_iframeContent').contents().find( 'head' ).append( $("<style type='text/css'> .plugin-install-php #plugin-information-footer {display:none !important;} </style>") );
	} );
        
    /* ==============  Theme preview JS with next/previous button events ==================== */
    // $( ".theme-screenshot .theme-overlay" ).ready(function() {
    $(document).on("click", ".preview_link", function(){       
        var theme_count = $(".theme-browser > div.one-theme").length;
        // Set current theme demo url in iframe
        var url = $(this).attr("data-demo-url");
        $('iframe').attr('src', url);
        
        var current_demo_id = $(this).attr('data-id');
        // Set next demo url id attribute
        var next_id = $(this).closest('.one-theme').next('.one-theme').find('.preview_link').attr("data-id");
        $('.header_btn_bar .next').attr('data-demo-id', next_id);
        // Set previous demo url id attribute
        var prev_id = $(this).closest('.one-theme').prev('.one-theme').find('.preview_link').attr("data-id");
        $('.header_btn_bar .previous').attr('data-demo-id', prev_id);
        // Check theme count to manage previous/next action
        $('.header_btn_bar .theme-info').attr('data-theme-count', theme_count);
        // Set current theme id in data attribute
        $('.header_btn_bar .theme-info').attr('data-active-demo-id', current_demo_id);
        // Reset Previous/Next Button Style
        $('.header_btn_bar .next').removeAttr('style');
        $('.header_btn_bar .previous').removeAttr('style');
        // If no (0) previous theme preview div available, disable previous button
        var demo_id = $(this).attr('data-id');
        var prev_theme_num = $('#demo-'+demo_id).closest('.one-theme').prev('.one-theme').length;
        if (prev_theme_num === 0) {
            $('.header_btn_bar .previous').css( { 'opacity' : '0.5', 'cursor' : 'initial'  } );
            $('.header_btn_bar .previous').attr('data-demo-id', '0');
        }
        // If no (0) next theme preview div available, disable next button
        var demo_id = $(this).attr('data-id');
        var next_theme_num = $('#demo-'+demo_id).closest('.one-theme').next('.one-theme').length;
        if (next_theme_num === 0) {
            $('.header_btn_bar .next').css( { 'opacity' : '0.5', 'cursor' : 'initial' } );
            $('.header_btn_bar .next').attr('data-demo-id', '0');
        }
        
        tb_show("Preview Popup","#TB_inline?width=full&height=full&inlineId=thickbox_preview&modal=true&class=thickbox",null);
      
        // Add preview page specific class to set page width/height to full page
        $('body').addClass("preview_page");
    });
        
    $(document).on("click", ".close_btn", function(){
        // remove thickbox overlay
        tb_remove();
        // remove preview page specific class
        setTimeout( function(){
            $('body').removeClass("preview_page");
        }, 500 );
    });
        
    $(document).on("click", "#desktop", function(){
        $(".preview-container .phone-content").removeClass("phone-content").addClass("desktop-content");
        $(".preview-container .preview span").remove( ".screen-rotate" );
        $(".preview-container").removeClass( "scroll" );
        $(".preview-container iframe").removeClass( "horizontal" );
        $(".desktop-content").removeClass( "horizontal" );
        $("#desktop").addClass( "current" );
        $("#mobile").removeClass( "current" );
    });
    
    $(document).on("click", "#mobile", function(){
        $('.preview-container .desktop-content').removeClass("desktop-content").addClass("phone-content");
        $(".preview-container").addClass( "scroll" );
        $(".preview-container .preview").append('<span class="screen-rotate"></span>');
        $("#desktop").removeClass( "current" );
        $("#mobile").addClass( "current" );
    });
        
    $(document).on("click", ".screen-rotate", function(){
        $(".preview-container iframe").toggleClass( "horizontal" );
        $(".phone-content").toggleClass( "horizontal" );
    });
        
    $(document).on("click", ".header_btn_bar .next", function(){
        // Check if current preview theme is first, disable previous button
        var demo_id = $(this).attr('data-demo-id');
        var active_demo_id = $('#preview_box .theme-info').attr('data-active-demo-id');
        var next_theme_num = $('#demo-'+demo_id).closest('.one-theme').next('.one-theme').length;

        if (demo_id === '0') {
            // demo_id 0 means, you are already on last theme. No action needed
            event.stopPropagation();    
        } else if (next_theme_num === 0) {
            // next_theme_num 0 means, next theme is last theme. Disable next button
            $(this).css( { 'opacity' : '0.5', 'cursor' : 'initial' } );
            $(this).attr('data-demo-id', 0);
            $('.header_btn_bar .previous').attr('data-demo-id', active_demo_id);
            var url = $('#demo-'+demo_id).attr('data-demo-url');
            $('iframe').attr('src', url);
            $('.header_btn_bar .theme-info').attr('data-active-demo-id', demo_id);
        } else {
            // Common action for rest of the themes
            $('.header_btn_bar .previous').removeAttr('style');
            var url = $('#demo-'+demo_id).attr("data-demo-url");
            $('iframe').attr('src', url);
            var next_id = $('#demo-'+demo_id).closest('.one-theme').next('.one-theme').find('.preview_link').attr("data-id");
            $(this).attr('data-demo-id', next_id);
            $('.header_btn_bar .previous').attr('data-demo-id', active_demo_id);
            $('.header_btn_bar .theme-info').attr('data-active-demo-id', demo_id);
        }
    });
        
    $(document).on("click", ".header_btn_bar .previous", function(){
        // Check if current preview theme is first, disable previous button
        var demo_id = $(this).attr('data-demo-id');
        var active_demo_id = $('#preview_box .theme-info').attr('data-active-demo-id');
        var prev_theme_num = $('#demo-'+demo_id).closest('.one-theme').prev('.one-theme').length;

        if (demo_id === '0') {
            // demo_id 0 means, no previous theme demo available
            event.stopPropagation();
        } else if (prev_theme_num === 0) {
            // prev_theme_num 0 means, it will switch to first theme and disable previous button
            $(this).css( { 'opacity' : '0.5', 'cursor' : 'initial' } );
            $(this).attr('data-demo-id', 0);
            $('.header_btn_bar .next').attr('data-demo-id', active_demo_id);
            var url = $('#demo-'+demo_id).attr('data-demo-url');
            $('iframe').attr('src', url);
            // Assign previous demo id 0, as this is first theme
            $('.header_btn_bar .theme-info').attr('data-active-demo-id', demo_id);
        } else {
            $('.header_btn_bar .next').removeAttr('style');
            var url = $('#demo-'+demo_id).attr("data-demo-url");
            $('iframe').attr('src', url);
            var prev_id = $('#demo-'+demo_id).closest('.one-theme').prev('.one-theme').find('.preview_link').attr("data-id");
            $(this).attr('data-demo-id', prev_id);
            $('.header_btn_bar .next').attr('data-demo-id', active_demo_id);
            $('.header_btn_bar .theme-info').attr('data-active-demo-id', demo_id);
        }
    });

})( jQuery );