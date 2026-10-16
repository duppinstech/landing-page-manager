<!DOCTYPE html>
	<html <?php language_attributes() ?>>

	<head>
		<meta charset="<?php bloginfo('charset') ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="format-detection" content="telephone=no">

		<?php
		$favicon_dir = LP_MANAGER_PLUGIN_PATH . 'includes/templates/whiterail-ai/css/images/favicons/';
		if ( file_exists( $favicon_dir . 'favicon-32x32.png' ) ) :
			$favicon_url = LP_MANAGER_PLUGIN_URL . 'includes/templates/whiterail-ai/css/images/favicons/';
		?>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( $favicon_url . 'favicon-32x32.png' ); ?>" />
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( $favicon_url . 'favicon-16x16.png' ); ?>" />
		<?php endif; ?>

		<?php do_action('wp_head'); ?>
	</head>

	<body <?php body_class() ?>>
	<?php wp_body_open() ?>

	<?php
  use LPManager\core\Google_Maps_Helper;
  
	$client_term_id = 0;
	$client_logo_id = 0;
	$client_logo_url = '';
	$client_primary_color = '#007BFF';
	$client_secondary_color = '#FF4136';
	$client_address = '';
	$client_place_id = '';
	$gmb_url = '';
	$client_domain = '';
	// The page's own number wins, then the client's default.
	$client_phone = lpmanager_get_landing_phone( get_the_ID() );
	$social_links = [];
	$contrast_text_color = '#FFFFFF';

	$current_post_id = get_the_ID();
	$client_terms = get_the_terms( $current_post_id, 'client' );
	if ( ! empty( $client_terms ) && ! is_wp_error( $client_terms ) ) {
		$client_term = $client_terms[0];
		$client_term_id = $client_term->term_id;
		$client_logo_id = carbon_get_term_meta( $client_term_id, 'client_logo' );
		$client_logo_url = $client_logo_id ? wp_get_attachment_image_url( $client_logo_id, 'full' ) : '';
		$client_primary_color = sanitize_hex_color( (string) carbon_get_term_meta( $client_term_id, 'client_primary_color' ) ) ?: '#007BFF';
		$client_secondary_color = sanitize_hex_color( (string) carbon_get_term_meta( $client_term_id, 'client_secondary_color' ) ) ?: '#FF4136';
		$client_address = carbon_get_term_meta( $client_term_id, 'client_address' ) ?: '';
		$client_place_id = carbon_get_term_meta( $client_term_id, 'client_place_id' ) ?: '';
		$gmb_url = $client_place_id ? Google_Maps_Helper::get_gmb_url( $client_place_id ) : '';
		$client_domain = carbon_get_term_meta( $client_term_id, 'client_domain' ) ?: '';
		$social_links = carbon_get_term_meta( $client_term_id, 'social_links' ) ?: [];
		$contrast_text_color = \LPManager\assets\Assets_Manager::get_contrast_text_color( $client_primary_color );
	}

	// Helper: Font Awesome icon class map by platform name (adjust as needed)
	$social_icons = [
		'facebook' => 'fab fa-facebook-f',
		'google' => 'fab fa-google',
		'yelp' => 'fab fa-yelp',
		'linkedin' => 'fab fa-linkedin-in',
		'twitter' => 'fab fa-twitter',
		// add more as needed
	];
	?>

<div class="header-bar" style="background-color: <?php echo esc_attr( $client_primary_color ); ?>; color: <?php echo esc_attr( $contrast_text_color ); ?>;">
    <div class="header-bar__section header-bar__section--address">
        <?php if (!empty($client_address)) : ?>
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>

            <?php if ($gmb_url) : ?>
                <a href="<?php echo esc_url($gmb_url); ?>" target="_blank" rel="noopener noreferrer" class="header-bar__text" style="color: inherit!important;">
                    <?php echo esc_html($client_address); ?>
                </a>
            <?php else : ?>
                <span class="header-bar__text"><?php echo esc_html($client_address); ?></span>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="header-bar__section header-bar__section--help">
        <?php if (!empty($client_phone)) : ?>
            <span class="header-bar__text">
                Need Help Now? 
                <a href="tel:<?php echo esc_attr( preg_replace('/[^0-9+]/', '', $client_phone) ); ?>" style="color: <?php echo esc_attr($contrast_text_color); ?>">
                    <i class="fas fa-phone-alt" aria-hidden="true"></i> <span style="color: inherit!important;">Call <?php echo esc_html($client_phone);?></span>
                </a>
            </span>
        <?php endif; ?>
    </div>

    <div class="header-bar__section header-bar__section--social">
      <?php if (!empty($social_links) && is_array($social_links)) : ?>
        <nav class="social-links" aria-label="Social Media Links">
          <ul class="social-links__list">
            <?php foreach ($social_links as $link) : 
              $icon_class = trim($link['platform'] ?? '');
              $url        = trim($link['url'] ?? '');
              if ( empty( $url ) || empty( $icon_class ) ) {
                continue;
              }
            ?>
              <li class="social-links__item">
                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                  <i class="<?php echo esc_attr($icon_class); ?>" style="color: <?php echo esc_attr($contrast_text_color); ?>" aria-hidden="true"></i>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
      <?php else: ?>
        <div class="header-bar__empty-social"></div>
      <?php endif; ?>
    </div>
</div>
<?php
// Prepare menus and client domain like before
if (!$client_domain) $client_domain = home_url('/');

$menu_left_items = [
//    'About' => '/about/',
//    'Towing' => '/services/emergency-towing-service/',
//    'Roadside Assistance' => '/services/roadside-assistance/',
];

$menu_right_items = [
//    'Heavy Duty Towing' => '/services/heavy-duty-towing/',
//    'Truck Repair' => '/services/truck-repair/',
//    'Contact' => '/contact/',
];
?>

<?php
function lpmanager_is_valid_external_url($url) {
    $response = wp_remote_head($url, ['timeout' => 2]);
    if (is_wp_error($response)) {
        return false;
    }
    $status = wp_remote_retrieve_response_code($response);
    return $status < 400;
}
?>

<!-- Desktop Header -->
<header class="site-header site-header--desktop" aria-label="Primary Site Header">
  <div class="header-container">
    <!-- Desktop Header Navigation -->
    <nav class="nav nav--left" aria-label="Primary navigation left">
      <ul>
        <?php foreach ($menu_left_items as $label => $slug): 
            $url = 'https://' . rtrim($client_domain, '/') . $slug;
            if (lpmanager_is_valid_external_url($url)): ?>
          <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
        <?php endif; endforeach; ?>
      </ul>
    </nav>

    <div class="logo">
      <a href="<?php echo esc_url($client_domain); ?>" aria-label="Homepage">
        <img src="<?php echo esc_url($client_logo_url); ?>" alt="Site Logo" width="300" height="83" />
      </a>
    </div>

    <nav class="nav nav--right" aria-label="Primary navigation right">
      <ul>
        <?php foreach ($menu_right_items as $label => $slug): 
            $url = 'https://' . rtrim($client_domain, '/') . $slug;
            if (lpmanager_is_valid_external_url($url)): ?>
          <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
        <?php endif; endforeach; ?>
      </ul>
    </nav>

  </div>
</header>

<!-- Mobile Header -->
<?php $mobile_menu_enabled = carbon_get_term_meta($client_term_id, 'lpmanager_enable_mobile_menu'); ?>

<div class="site-header site-header--mobile" aria-label="Mobile Site Header">
  <div class="header-container">
    <div class="logo" style="<?php echo empty($mobile_menu_enabled) ? 'margin: 0 auto;' : ''; ?>">
      <a href="<?php echo esc_url($client_domain); ?>" aria-label="Homepage">
        <img src="<?php echo esc_url($client_logo_url); ?>" alt="Site Logo" width="150" height="42" />
      </a>
    </div>

    <?php if ($mobile_menu_enabled) : ?>
    <div class="mobile-menu-wrapper">
      <button id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
      </button>

      <div id="mobile-menu-overlay" class="mobile-menu-overlay" hidden></div>

      <nav id="mobile-menu" class="mobile-menu" aria-label="Mobile navigation" hidden
        style="background-color: <?php echo esc_attr($client_secondary_color); ?>; color: <?php echo esc_attr($contrast_text_color); ?>;">
        <div class="logo">
          <a href="<?php echo esc_url($client_domain); ?>" aria-label="Homepage">
            <img src="<?php echo esc_url($client_logo_url); ?>" alt="Site Logo" width="150" height="42" />
          </a>
        </div>
        <button id="mobile-menu-close" class="mobile-menu-close" aria-label="Close menu">
          <i class="fas fa-times"></i>
        </button>
        <ul>
          <li><a href="<?php echo esc_url('https://' . rtrim($client_domain, '/')); ?>" style="color: <?php echo esc_attr($contrast_text_color); ?>;">Home</a></li>

          <?php foreach (array_merge($menu_left_items, $menu_right_items) as $label => $slug) : 
            $url = 'https://' . rtrim($client_domain, '/') . $slug;
            if (lpmanager_is_valid_external_url($url)): ?>
            <li><a href="<?php echo esc_url($url); ?>" style="color: <?php echo esc_attr($contrast_text_color); ?>;"><?php echo esc_html($label); ?></a></li>
          <?php endif; endforeach; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>

  </div>
</div>

<main class="main-content" id="main-content">