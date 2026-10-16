<!DOCTYPE html>
<html <?php language_attributes() ?>>
    <head>
        <meta charset="<?php bloginfo('charset') ?>" />
        <script>document.documentElement.className += ' rec-js';</script>

        <meta name="format-detection" content="telephone=no">

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="HandheldFriendly" content="true" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        
        <link rel="icon" type="image/png" href="<?php echo esc_url( plugins_url('css/images/favicons/favicon-32x32.png', __FILE__) ); ?>" sizes="32x32" />
		<link rel="icon" type="image/png" href="<?php echo esc_url( plugins_url('css/images/favicons/favicon-16x16.png', __FILE__) ); ?>" sizes="16x16" />
        
        <?php do_action( 'wp_head' ); ?>
    </head>
	
<?php 
	echo '<!-- Custom header.php loaded -->';
	$current_post_id = get_the_ID();
    $terms = get_the_terms($current_post_id, 'client');

    if (!empty($terms) && !is_wp_error($terms)) {
        $client_term = reset($terms);
        $client_term_id = $client_term->term_id;

        // Now get the term meta for 'client_logo'
        $client_logo_id = carbon_get_term_meta($client_term_id, 'client_logo'); // Use carbon_get_term_meta for term meta

        $client_logo_url = !empty($client_logo_id) ? wp_get_attachment_url($client_logo_id) : '';

    } else {
        $client_logo_url = '';
    }
    
    $current_post_id = get_the_ID();
    $terms = get_the_terms($current_post_id, 'client');

    if (!empty($terms) && !is_wp_error($terms)) {
        $client_term = reset($terms);
        $client_term_id = $client_term->term_id;
        $client_name = $client_term->name;
    } else {
        $client_name = 'Unknown Client';
    }

    // The client's published landing pages, for the "Services" menu (desktop dropdown and mobile panel).
    $lpm_client_pages = array();
    $lpm_client_id    = $current_post_id ? (int) carbon_get_post_meta( $current_post_id, 'client' ) : 0;
    if ( $lpm_client_id ) {
        $lpm_page_ids = get_posts(
            array(
                'post_type'      => 'landing_page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'client',
                        'field'    => 'term_id',
                        'terms'    => $lpm_client_id,
                    ),
                ),
            )
        );
        foreach ( $lpm_page_ids as $lpm_page_id ) {
            $lpm_client_pages[] = array(
                'id'    => (int) $lpm_page_id,
                'title' => get_the_title( $lpm_page_id ),
                'url'   => get_permalink( $lpm_page_id ),
            );
        }
    }
?>
	
    <body <?php body_class() ?>>
		<?php wp_body_open() ?>
		
        <header class="site-header header-main-layout-1 ast-primary-menu-enabled ast-hide-custom-menu-mobile ast-builder-menu-toggle-icon ast-mobile-header-inline" id="masthead" itemtype="https://schema.org/WPHeader" itemscope="itemscope" itemid="#masthead">
			<div id="ast-desktop-header" data-toggle-type="off-canvas">
				<div class="ast-main-header-wrap main-header-bar-wrap ">
					<div class="ast-primary-header-bar ast-primary-header main-header-bar site-header-focus-item" data-section="section-primary-header-builder">
						<div class="site-primary-header-wrap ast-builder-grid-row-container site-header-focus-item ast-container" data-section="section-primary-header-builder">
							<div class="ast-builder-grid-row ast-builder-grid-row-has-sides ast-builder-grid-row-no-center">
								<div class="site-header-primary-section-left site-header-section ast-flex site-header-section-left">
									<div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="title_tagline">
										<div class="site-branding ast-site-identity" itemtype="https://schema.org/Organization" itemscope="itemscope">
											<span class="site-logo-img">
												<a href="<?php echo esc_url( get_permalink() ); ?>" class="custom-logo-link" rel="home" aria-current="page" aria-expanded="false"><img width="180" height="113" src="<?php echo esc_url( $client_logo_url ); ?>" class="custom-logo" alt="<?php echo esc_attr( $client_name ); ?> Jobs">
												</a>
											</span>				
										</div>
									<!-- .site-branding -->
									</div>
								</div>
								<div class="site-header-primary-section-right site-header-section ast-flex ast-grid-right-section">
									<div class="ast-builder-menu-1 ast-builder-menu ast-flex ast-builder-menu-1-focus-item ast-builder-layout-element site-header-focus-item" data-section="section-hb-menu-1">
									<div class="ast-main-header-bar-alignment">
										<div class="main-header-bar-navigation">
											<nav class="site-navigation ast-flex-grow-1 navigation-accessibility site-header-focus-item" id="primary-site-navigation-desktop" aria-label="Site Navigation" itemtype="https://schema.org/SiteNavigationElement" itemscope="itemscope">
												<div class="main-navigation ast-inline-flex">
													<ul id="ast-hf-menu-1" class="main-header-menu ast-menu-shadow ast-nav-menu ast-flex submenu-with-border stack-on-mobile" aria-expanded="false">
														<?php if ( $lpm_client_pages ) : ?>
														<li id="menu-item-57" class="menu-item menu-item-has-children">
															<a href="#" class="menu-link lpm-submenu-parent" aria-haspopup="true"><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></a>
															<ul class="sub-menu client-landing-pages">
																<?php foreach ( $lpm_client_pages as $lpm_page ) : ?>
																<li class="menu-item page-item-<?php echo esc_attr( $lpm_page['id'] ); ?>"><a href="<?php echo esc_url( $lpm_page['url'] ); ?>" class="menu-link"<?php echo ( $lpm_page['id'] === (int) $current_post_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $lpm_page['title'] ); ?></a></li>
																<?php endforeach; ?>
															</ul>
														</li>
														<?php endif; ?>
														<li id="menu-item-58" class="menu-item"><a href="#about" class="menu-link">About Us</a></li>
														<li id="menu-item-59" class="menu-item"><a href="#benefits" class="menu-link">Benefits</a></li>
														<li id="menu-item-60" class="menu-item"><a href="#more" class="menu-link">More About <?php echo esc_html( $client_name ); ?></a></li>
													</ul>
												</div>
											</nav>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- Main Header Bar Wrap -->
	<div id="ast-mobile-header" class="ast-mobile-header-wrap " data-type="off-canvas">
		<div class="ast-main-header-wrap main-header-bar-wrap">
			<div class="ast-primary-header-bar ast-primary-header main-header-bar site-primary-header-wrap site-header-focus-item ast-builder-grid-row-layout-default ast-builder-grid-row-tablet-layout-default ast-builder-grid-row-mobile-layout-default" data-section="section-primary-header-builder">
				<div class="ast-builder-grid-row ast-builder-grid-row-has-sides ast-builder-grid-row-no-center">
					<div class="site-header-primary-section-left site-header-section ast-flex site-header-section-left">
						<div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="title_tagline">
							<div class="site-branding ast-site-identity" itemtype="https://schema.org/Organization" itemscope="itemscope">
								<span class="site-logo-img">
									<a href="<?php echo esc_url( site_url() ); ?>" class="custom-logo-link" rel="home" aria-current="page"><img width="180" height="113" src="<?php echo esc_url( $client_logo_url ); ?>" class="custom-logo" alt="<?php echo esc_attr( $client_name ); ?> Jobs" ></a>
								</span>				
							</div><!-- .site-branding -->
						</div>
					</div>
					<div class="site-header-primary-section-right site-header-section ast-flex ast-grid-right-section">
						<div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="section-header-mobile-trigger">
							<div class="ast-button-wrap">
								<button type="button" class="menu-toggle main-header-menu-toggle ast-mobile-menu-trigger-minimal" aria-expanded="false" aria-controls="lpm-mobile-nav" data-index="0" style="display: flex;">
									<span class="screen-reader-text">Main Menu</span>
										<span class="mobile-menu-toggle-icon">
											<span class="ahfb-svg-iconset ast-inline-flex svg-baseline">
												<svg class="ast-mobile-svg ast-menu-svg" fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
													<path d="M3 13h18c0.552 0 1-0.448 1-1s-0.448-1-1-1h-18c-0.552 0-1 0.448-1 1s0.448 1 1 1zM3 7h18c0.552 0 1-0.448 1-1s-0.448-1-1-1h-18c-0.552 0-1 0.448-1 1s0.448 1 1 1zM3 19h18c0.552 0 1-0.448 1-1s-0.448-1-1-1h-18c-0.552 0-1 0.448-1 1s0.448 1 1 1z">
													</path>
												</svg>
											</span>
											<span class="ahfb-svg-iconset ast-inline-flex svg-baseline">
												<svg class="ast-mobile-svg ast-close-svg" fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
													<path d="M5.293 6.707l5.293 5.293-5.293 5.293c-0.391 0.391-0.391 1.024 0 1.414s1.024 0.391 1.414 0l5.293-5.293 5.293 5.293c0.391 0.391 1.024 0.391 1.414 0s0.391-1.024 0-1.414l-5.293-5.293 5.293-5.293c0.391-0.391 0.391-1.024 0-1.414s-1.024-0.391-1.414 0l-5.293 5.293-5.293-5.293c-0.391-0.391-1.024-0.391-1.414 0s-0.391 1.024 0 1.414z"></path></svg>
											</span>					
									</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<nav id="lpm-mobile-nav" class="lpm-mobile-nav" aria-label="<?php esc_attr_e( 'Main menu', 'landing-page-manager' ); ?>" hidden>
			<ul>
				<?php if ( $lpm_client_pages ) : ?>
				<li class="lpm-mobile-nav__group">
					<span class="lpm-mobile-nav__label" id="lpm-mobile-nav-services"><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></span>
					<ul aria-labelledby="lpm-mobile-nav-services">
						<?php foreach ( $lpm_client_pages as $lpm_page ) : ?>
						<li><a href="<?php echo esc_url( $lpm_page['url'] ); ?>"<?php echo ( $lpm_page['id'] === (int) $current_post_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $lpm_page['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
				<?php endif; ?>
				<li><a href="#about"><?php esc_html_e( 'About Us', 'landing-page-manager' ); ?></a></li>
				<li><a href="#benefits"><?php esc_html_e( 'Benefits', 'landing-page-manager' ); ?></a></li>
				<li><a href="#more">
					<?php
					/* translators: %s: client name */
					echo esc_html( sprintf( __( 'More About %s', 'landing-page-manager' ), $client_name ) );
					?>
				</a></li>
			</ul>
		</nav>
	</div>
	</header>
    <main class="main-content" id="main-content">