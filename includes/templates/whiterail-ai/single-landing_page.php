<?php
/*
Template Name: Whiterail AI
Description: A simple landing page template for Whiterail AI.
*/
use \LPManager\assets\Assets_Manager;
use LPManager\core\Google_Maps_Helper;
?>

<?php include \LPManager\templates\Template_Manager::get_template_path('header.php'); ?>

<?php
// The page's own number wins, then the client's default (one number for display and tel: links).
$client_tel = lpmanager_get_landing_phone( get_the_ID() );
// Ensure client/keyword/phone available for body (header may have set them from term)
if ( empty( $client_term_id ) ) {
    $client_terms = get_the_terms( get_the_ID(), 'client' );
    $client_term_id = ( $client_terms && ! is_wp_error( $client_terms ) && ! empty( $client_terms ) ) ? $client_terms[0]->term_id : 0;
}
$client_name_for_alt = $client_term_id ? get_term_field( 'name', $client_term_id, 'client' ) : '';
$keyword_terms = get_the_terms( get_the_ID(), 'keyword' );
$keyword_name_for_alt = ( $keyword_terms && ! is_wp_error( $keyword_terms ) && ! empty( $keyword_terms ) ) ? $keyword_terms[0]->name : '';
$client_phone = $client_tel;
$client_place_id = $client_term_id ? carbon_get_term_meta( $client_term_id, 'client_place_id' ) : '';
$client_google_search_url = $client_term_id ? carbon_get_term_meta($client_term_id, 'client_google_search_url') : '';
if ($client_place_id) {
    $gmb_url = Google_Maps_Helper::get_gmb_url($client_place_id);
    $gmb_reviews_url = Google_Maps_Helper::get_gmb_reviews_url($client_place_id);
} else {
    $gmb_url = $client_google_search_url;
    $gmb_reviews_url = $client_google_search_url;
}

// Colours land in style attributes, so only accept a real hex value; anything else uses the fallback.
$client_primary_color = sanitize_hex_color( $client_term_id ? (string) carbon_get_term_meta($client_term_id, 'client_primary_color') : '' ) ?: '#007BFF'; // fallback color: web-safe blue
$client_secondary_color = sanitize_hex_color( $client_term_id ? (string) carbon_get_term_meta($client_term_id, 'client_secondary_color') : '' ) ?: '#FF4136'; // fallback color: web-safe red
$contrast_text_color = Assets_Manager::get_contrast_text_color($client_primary_color);
$contrast_text_color_alt = Assets_Manager::get_contrast_text_color($client_secondary_color);

$hero_section_h1 = carbon_get_the_post_meta('hero_section_h1');
$hero_section_h2 = carbon_get_the_post_meta('hero_section_h2');
$hero_section_bg_id = carbon_get_the_post_meta('hero_section_bg');
if ($hero_section_bg_id) {
    $hero_section_bg_url = wp_get_attachment_image_url($hero_section_bg_id, 'full'); // or 'large', 'medium'
} else {
    $hero_section_bg_url = ''; // or your fallback image URL
}

$trust_bar_text_1 = carbon_get_the_post_meta('trust_bar_text_1');
$trust_bar_text_2 = carbon_get_the_post_meta('trust_bar_text_2');
$trust_bar_text_3 = carbon_get_the_post_meta('trust_bar_text_3');
$trust_bar_text_4 = carbon_get_the_post_meta('trust_bar_text_4');

$section_2_h2 = carbon_get_the_post_meta('section_2_h2');
$section_2_text = carbon_get_the_post_meta('section_2_text');
$section_2_img_id = carbon_get_the_post_meta('section_2_img');
if ($section_2_img_id) {
    $section_2_img_url = wp_get_attachment_image_url($section_2_img_id, 'full'); // or 'large', 'medium'
} else {
    $section_2_img_url = ''; // or your fallback image URL
}

$reviews_h2 = carbon_get_the_post_meta('reviews_h2');
$reviews_html = carbon_get_the_post_meta('reviews_html');

$section_4_h2 = carbon_get_the_post_meta('section_4_h2');
$section_4_text_1 = carbon_get_the_post_meta('section_4_text_1');
$section_4_text_2 = carbon_get_the_post_meta('section_4_text_2');

$section_5_h2 = carbon_get_the_post_meta('section_5_h2');
$section_5_text = carbon_get_the_post_meta('section_5_text');

$section_6_h2 = carbon_get_the_post_meta('section_6_h2');
$section_6_text = carbon_get_the_post_meta('section_6_text');

$faq_h2 = carbon_get_the_post_meta('faq_h2');
$faq_text = carbon_get_the_post_meta('faq_text');

$section_8_h2 = carbon_get_the_post_meta('section_8_h2');
$section_8_text = carbon_get_the_post_meta('section_8_text');
$section_8_img_id = carbon_get_the_post_meta('section_8_img');
if ($section_8_img_id) {
    $section_8_img_url = wp_get_attachment_image_url($section_8_img_id, 'full'); // or 'large', 'medium'
} else {
    $section_8_img_url = ''; // or your fallback image URL
}
?>

<!-- Section 1: Hero -->
<section id="hero" class="hero-section"<?php if ( ! empty( $hero_section_bg_url ) ) : ?> style="background-image: url('<?php echo esc_url( $hero_section_bg_url ); ?>')"<?php endif; ?>>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <?php if (!empty($hero_section_h1)) : ?>
            <h1><?php echo esc_html($hero_section_h1); ?></h1>
        <?php endif; ?>

        <?php if (!empty($hero_section_h2)) : ?>
            <h2><?php echo esc_html($hero_section_h2); ?></h2>
        <?php endif; ?>

        <?php if ( ! empty( $client_tel ) ) : ?>
            <a class="call-button" style="background-color:<?php echo esc_attr( $client_primary_color ); ?>;" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $client_phone ) ); ?>">
                <i class="fas fa-phone-alt" aria-hidden="true"></i> Call <?php echo esc_html( $client_tel ); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
<section class="trust-bar" aria-label="Trust highlights">
    <ul class="trust-bar__list">
      <?php if ( ! empty( $trust_bar_text_1 ) ) : ?>
      <li class="trust-bar__item"><?php if ( ! empty( $gmb_reviews_url ) ) : ?><a href="<?php echo esc_url( $gmb_reviews_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $trust_bar_text_1 ); ?></a><?php else : ?><?php echo esc_html( $trust_bar_text_1 ); ?><?php endif; ?></li>
      <?php endif; ?>
      <?php if ( ! empty( $trust_bar_text_2 ) ) : ?><li class="trust-bar__item"><?php echo esc_html( $trust_bar_text_2 ); ?></li><?php endif; ?>
      <?php if ( ! empty( $trust_bar_text_3 ) ) : ?><li class="trust-bar__item"><?php echo esc_html( $trust_bar_text_3 ); ?></li><?php endif; ?>
      <?php if ( ! empty( $trust_bar_text_4 ) ) : ?><li class="trust-bar__item"><?php echo esc_html( $trust_bar_text_4 ); ?></li><?php endif; ?>
    </ul>
</section>
<!-- Section 2 -->
<section id="opening" class="content-image-section">
  <div class="content-image-wrapper">
    <div class="text-column">
      <h2><?php echo esc_html( $section_2_h2 ); ?></h2>
      <?php echo wp_kses_post( wp_specialchars_decode( (string) $section_2_text, ENT_QUOTES ) ); ?>
    </div>
    <div class="image-column">
      <img src="<?php echo esc_url( $section_2_img_url ); ?>" alt="<?php echo esc_attr( $client_name_for_alt . ' ' . $keyword_name_for_alt ); ?>">
    </div>
  </div>
</section>
<!-- Section 3: Reviews (only when the page has reviews; lib/assets.php skips Swiper otherwise) -->
<?php $reviews_html_safe = lpmanager_kses_rich_text( (string) $reviews_html ); ?>
<?php if ( '' !== trim( $reviews_html_safe ) ) : ?>
<section id="reviews" class="reviews-section" style="background-color: #F3F3F3;">
  <div class="reviews-wrapper">
    <h2><?php echo esc_html( $reviews_h2 ); ?></h2>
    <div class="reviews-html">
      <?php echo $reviews_html_safe; // phpcs:ignore WordPress.Security.EscapeOutput -- filtered by lpmanager_kses_rich_text() above. ?>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- Section 4 -->
<section id="services" class="icon-boxes-section">
  <div class="container">
    <h2><?php echo esc_html( $section_4_h2 ); ?></h2>
    <p class="section-intro"><?php echo lpmanager_kses_rich_text( $section_4_text_1 ); // phpcs:ignore WordPress.Security.EscapeOutput -- kses with an iframe host allowlist. ?></p>

    <?php 
    $icon_boxes = carbon_get_the_post_meta('icon_boxes');
    if ( !empty($icon_boxes) ) : ?>
      <div class="icon-boxes-wrapper">
        <?php foreach ( $icon_boxes as $box ) : ?>
          <div class="icon-box" style="background-color:<?php echo esc_attr( $client_primary_color ); ?>;">
            <div class="icon">
              <i style="color:<?php echo esc_attr( $contrast_text_color ); ?>;" class="<?php echo esc_attr( $box['icon_class'] ?? 'fas fa-star' ); ?>" aria-hidden="true"></i>
            </div>
            <h3 style="color:<?php echo esc_attr( $contrast_text_color ); ?>;"><?php echo esc_html( $box['title'] ?? '' ); ?></h3>
            <?php if ( ! empty( $box['text'] ) ) : ?>
            <p style="color:<?php echo esc_attr( $contrast_text_color ); ?>;"><?php echo esc_html( $box['text'] ?? '' ); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="section-outro"><?php echo esc_html( $section_4_text_2 ); ?></p>
  </div>
</section>
<!-- Section 5 -->
<section id="service-area" class="content-section bg-light">
  <div class="container">
    <h2><?php echo esc_html( $section_5_h2 ); ?></h2>
    <div class="section-text">
      <?php echo wp_kses_post( $section_5_text ); ?>
    </div>
  </div>
</section>
<!-- Section 6 -->
<section id="roads-highways" class="content-section">
  <div class="container">
    <h2><?php echo esc_html( $section_6_h2 ); ?></h2>
    <div class="section-text">
      <?php echo wp_kses_post( wp_specialchars_decode( (string) $section_6_text, ENT_QUOTES ) ); ?>
    </div>
  </div>
</section>
<!-- CTA -->
<section class="cta-bar" style="background-color: <?php echo esc_attr( $client_primary_color ); ?>;">
  <div class="cta-container">
    <div class="cta-text">
      <h2>Need a tow truck near me now?</h2>
    </div>
    <div class="cta-button">
      <a class="cta-call-button" style="background-color:<?php echo esc_attr( $client_secondary_color ); ?>;color:<?php echo esc_attr( $contrast_text_color_alt ); ?>;" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $client_phone ) ); ?>">
        <i aria-hidden="true" class="fas fa-phone-alt"></i>
        <?php echo esc_html($client_tel); ?>
      </a>
    </div>
  </div>
</section>
<!-- Section 7 FAQ -->
<section id="faq" class="faq-section">
  <div class="container">
    <h2><?php echo esc_html( $faq_h2 ); ?></h2>

    <?php 
      $faqs = carbon_get_the_post_meta('faq_items');
      if ( !empty($faqs) ) : 
    ?>
      <div class="e-n-accordion" aria-label="Frequently asked questions">
        <?php foreach ( $faqs as $index => $faq ) : 
          $question = $faq['question'] ?? '';
          $answer   = $faq['answer'] ?? '';
          if ( empty($question) || empty($answer) ) continue; 
          $faq_id = 'faq-' . $index;
          $faq_panel_id = 'faq-panel-' . $index;
        ?>

          <details id="<?php echo esc_attr( $faq_id ); ?>" class="e-n-accordion-item" <?php echo $index === 0 ? 'open' : ''; ?>>
            <summary class="e-n-accordion-item-title" style="background-color:<?php echo esc_attr( $client_primary_color ); ?>;" tabindex="0" aria-controls="<?php echo esc_attr( $faq_panel_id ); ?>">
              <span class="e-n-accordion-item-title-text" style="color:<?php echo esc_attr( $contrast_text_color ); ?>;"><?php echo esc_html( $question ); ?></span>
              <span class="e-n-accordion-item-title-icon">
                <span class="e-opened"><i class="fas fa-minus"></i></span>
                <span class="e-closed"><i class="fas fa-plus"></i></span>
              </span>
            </summary>

            <div id="<?php echo esc_attr( $faq_panel_id ); ?>" class="faq-answer-panel" role="region" aria-labelledby="<?php echo esc_attr( $faq_id ); ?>">
              <p><?php echo nl2br(esc_html($answer)); ?></p>
            </div>
          </details>

        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>
<!-- Section 8 -->
<section id="closing" class="content-image-section">
  <div class="content-image-wrapper">
    <div class="text-column">
      <h2><?php echo esc_html( $section_8_h2 ); ?></h2>
      <?php echo wp_kses_post( wp_specialchars_decode( (string) $section_8_text, ENT_QUOTES ) ); ?>
      <?php if ( ! empty( $client_tel ) ) : ?>
            <a class="call-button" style="background-color:<?php echo esc_attr( $client_primary_color ); ?>;" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $client_phone ) ); ?>">
                <i class="fas fa-phone-alt" aria-hidden="true"></i> Call <?php echo esc_html( $client_tel ); ?>
            </a>
      <?php endif; ?>
    </div>
    <div class="image-column">
      <img src="<?php echo esc_url( $section_8_img_url ); ?>" alt="<?php echo esc_attr( $client_name_for_alt . ' ' . $keyword_name_for_alt ); ?>">
    </div>
  </div>
</section>

<?php include \LPManager\templates\Template_Manager::get_template_path('footer.php'); ?>