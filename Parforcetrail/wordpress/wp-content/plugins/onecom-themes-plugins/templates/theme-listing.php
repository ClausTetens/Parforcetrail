<?php //include_once ONECOM_WP_PATH.'inc/functions.php' ?>
<div class="wrap">
	<div class="loading-overlay">
		<div class="loader"></div>
	</div><!-- loader -->
	<div class="onecom-notifier"></div>
	<!-- <h2 id="one-com-logo-wrapper" class=""><span id="one-com-icon"></span> one.com</h2> -->
	<h2 class="one-logo"> 
		<div class="textleft">
			<?php //_e( 'Welcome to One.com', 'onecom-wp' ); ?>
			<span>
				<?php _e( 'Exclusive themes specially crafted for One.com customers.', 'onecom-wp' ); ?>
			</span>
		</div>
		<div class="textright">
			<img src="<?php echo ONECOM_WP_URL.'/assets/images/one.com-logo.png' ?>" alt="One.com" srcset="<?php echo ONECOM_WP_URL.'/assets/images/one.com-logo@2x.png 2x' ?>" /> 
		</div>
	</h2>
	<!-- <hr class="one-hr" /> -->
	<div class="wrap_inner inner one_wrap">
		<!-- <div class="nav-tab-wrapper">
		    <a href="#free" class="nav-tab nav-tab-active"><?php //_e( 'Free Themes', 'onecom-wp' ); ?></a>
		    <a href="#premium" class="nav-tab"><?php //_e( 'Premium Themes', 'onecom-wp' ); ?></a>
		</div> -->
		<div id="free" class="tab active-tab">

			<!-- <div class="theme-filters">
				<div class="filter-cats">
					<select>
						<option value=""><?php //_e( 'All', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Business & Services', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Events', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Family & Recreation', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Food & Hospitality', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Music & Art', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Portfolio & CV', 'onecom-wp' ); ?></option>
						<option value=""><?php //_e( 'Webshop', 'onecom-wp' ); ?></option>
					</select>
				</div> --> <!-- filter-cats -->
				<!-- <div class="filter-search">
					<input type="text" name="s" placeholder="<?php //_e( 'Enter keyword', 'onecom-wp' ); ?>" />
					<button name="search-theme-button"><span class="dashicons dashicons-search"></span></button>
				</div> --> <!-- filter-search -->
			<!-- </div> --> <!-- theme-filters -->

			<div class="theme-browser">
				<?php
					$themes = onecom_fetch_themes();

					if( is_wp_error( $themes ) ) :
						echo $themes->get_error_message();
					else :
						$current_theme = get_option( 'template' );
						foreach ($themes as $key => $theme) :
							$is_installed = onecom_is_theme_installed( $theme->slug );

							$tags = $theme->tags;
							$tags = implode( ' ', $tags );
							?>
								<div class="one-theme theme scale-anm <?php echo $tags; ?> all <?php echo ( $is_installed ) ? 'installed' : ''; ?>">
									<div class="theme-screenshot">
										<?php 
											$thumbnail_url = $theme->thumbnail;
											$thumbnail_url = preg_replace( '#^https?:#', '', $thumbnail_url );
										?>
											<img src="<?php echo $thumbnail_url; ?>" alt="<?php echo $theme->name; ?>" />
									</div>
									<div class="theme-overlay">
										<h4>
											<?php echo $theme->name; ?>
											<!-- <span>
												<?php //echo wp_trim_words( $theme->description, 15 ); ?>
												<a href="<?php //echo MIDDLEWARE_URL; ?>/themes/<?php //echo $theme->slug; ?>/info/?TB_iframe=true&amp;width=1200&amp;height=800" title="<?php //echo __( 'More information of', 'onecom-wp' ).' '.$theme->name; ?>" class="thickbox"><?php //_e( 'Read more', 'onecom-wp' ); ?></a>
											</span> -->
										</h4>
										<div class="theme-action">
											<div class="one-preview">
												<a class="preview_link" id="demo-<?php echo $theme->id; ?>" data-id="<?php echo $theme->id; ?>" data-demo-url="<?php echo $theme->preview; ?>">
		                                                <span class="dashicons dashicons-search"></span>
		                                                <span>
		                                                    <?php _e( 'Preview', 'onecom-wp' ); ?>
		                                                </span>
		                                        </a>
											</div>
											<?php $class = ( $is_installed ) ? 'one-installed' : 'one-install'; ?>
											<?php $action = ( $is_installed ) ? 'onecom_activate_theme' : 'onecom_install_theme'; ?>
											<?php
												if( $is_installed & ( $current_theme == $theme->slug ) ) {
													$action = '';
												}
											?>
											<div class="<?php echo $class; ?>" data-theme_slug="<?php echo $theme->slug; ?>" data-name="<?php echo $action ?>" data-redirect="<?php echo $theme->redirect; ?>">
												<span>
													<span class="dashicons dashicons-yes"></span>
													<?php if( $is_installed && ( $current_theme == $theme->slug ) ) : ?>
														<span class="action-text"><?php _e( 'Active', 'onecom-wp' ); ?></span>
													<?php elseif( $is_installed && ( $current_theme != $theme->slug ) ) : ?>
														<?php 
															$activate_url = add_query_arg( array(
																'action'     => 'activate',
																'_wpnonce'   => wp_create_nonce( 'switch-theme_' . $theme->slug ),
																'stylesheet' => $theme->slug,
															), admin_url( 'themes.php' ) ); 
														?>
														<?php if( ( ! isset( $theme->redirect ) ) || $theme->redirect != '' ) : ?>
															<span class="action-text one-activate-theme"><?php _e( 'Activate', 'onecom-wp' ); ?></span>
														<?php else: ?>
															<a href="<?php echo $activate_url ?>"><?php _e( 'Activate', 'onecom-wp' ) ?></a>
														<?php endif; ?>
													<?php else : ?>	
														<span class="action-text"><?php _e( 'Install', 'onecom-wp' ); ?></span>
													<?php endif; ?>
												</span>
											</div>
										</div>
									</div>
								</div>
							<?php
						endforeach;
					endif;
				?>
			</div> <!-- theme-browser -->

		</div> <!-- tab -->

	</div> <!-- wrap_inner -->
</div> <!-- wrap -->

<?php add_thickbox(); ?> 

<div id="thickbox_preview" style="display:none">
   <div id="preview_box">
       <div class="one-theme-listing-bar">
           <span class="dashicons dashicons-wordpress-alt"></span>
       </div>
       <div class="header_btn_bar">
           <div class="left-header">
               <div class="btn button_1 close_btn"><?php _e( 'Back to themes', 'onecom-wp' ); ?></div>
               <div class="btn btn_arrow previous" data-demo-id=""><span class="dashicons dashicons-arrow-left-alt2"></span></div>
               <span data-theme-count="" data-active-demo-id="" class="theme-info hide"></span>
               <div class="btn btn_arrow next" data-demo-id=""><span class="dashicons dashicons-arrow-right-alt2"></span></div>
           </div>
           <div class="right-header">
               <div class="btn button_2 current" id="desktop"> <span class="dashicons dashicons-desktop"></span> <?php _e( 'Desktop', 'onecom-wp' ); ?></div>
               <div class="btn button_2" id="mobile"> <span class="dashicons dashicons-smartphone"></span> <?php _e( 'Mobile', 'onecom-wp' ); ?></div>
         </div>
       </div>
       <!-- <hr class="divider" /> -->
       <span class="divider_shadow" > </span>

       <div class="preview-container">
             <div class="desktop-content text-center preview">
                 <iframe src='#'></iframe>
             </div>
       </div>
   </div>
</div>   