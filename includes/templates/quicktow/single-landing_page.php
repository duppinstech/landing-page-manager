<?php
/**
 * Template Name: QuickTow
 * Description: Local services landing page — hero, trust bar, services, stats, service area, FAQ (QuickTow).
 *
 * @package Landing_Page_Manager
 */

require_once dirname( __FILE__ ) . '/lib/helper-functions.php';

use LPManager\templates\Template_Manager;

$post_id = get_the_ID();
$client_terms = get_the_terms( $post_id, 'client' );
$client_term_id = ( $client_terms && ! is_wp_error( $client_terms ) && ! empty( $client_terms ) ) ? (int) $client_terms[0]->term_id : 0;

// The page's own number wins, then the client's default (one number for display and tel: links).
$client_phone = lpmanager_get_landing_phone( $post_id );
$client_tel   = $client_phone;

$client_name = $client_term_id ? get_term_field( 'name', $client_term_id, 'client' ) : '';
if ( is_wp_error( $client_name ) ) {
	$client_name = '';
}
$client_logo_url = '';
if ( $client_term_id && function_exists( 'carbon_get_term_meta' ) ) {
	$logo_id = carbon_get_term_meta( $client_term_id, 'client_logo' );
	if ( $logo_id ) {
		$client_logo_url = wp_get_attachment_image_url( (int) $logo_id, 'full' );
	}
}

$client_primary = ( $client_term_id && function_exists( 'carbon_get_term_meta' ) ) ? carbon_get_term_meta( $client_term_id, 'client_primary_color' ) : '';
$client_secondary = ( $client_term_id && function_exists( 'carbon_get_term_meta' ) ) ? carbon_get_term_meta( $client_term_id, 'client_secondary_color' ) : '';
// Printed into a <style> block, so only a real hex colour is allowed through.
$client_primary = sanitize_hex_color( (string) $client_primary );
$client_secondary = sanitize_hex_color( (string) $client_secondary );
$client_primary = $client_primary ? $client_primary : '#002868';
$client_secondary = $client_secondary ? $client_secondary : '#B22234';

$tel_digits = lpm_wra_phone_digits( $client_phone );

// All template meta.
$wra_style_variant_raw = '';
if ( function_exists( 'carbon_get_post_meta' ) ) {
	$wra_style_variant_raw = (string) carbon_get_post_meta( $post_id, 'wra_style_variant' );
}
if ( '' === $wra_style_variant_raw ) {
	$wra_style_variant_raw = (string) carbon_get_the_post_meta( 'wra_style_variant' );
}
// Fallback if Carbon did not register the field on this request (meta still exists in DB).
if ( '' === $wra_style_variant_raw && $post_id ) {
	$wra_style_variant_raw = (string) get_post_meta( $post_id, '_wra_style_variant', true );
}
$wra_style_variant_raw = strtolower( trim( $wra_style_variant_raw ) );
// Stored values: style_1/2/3, plus legacy aliases from early versions (kept so old pages keep their style).
// Internal names (layout1, layout2, layout_3) drive CSS classes; they carry no client names.
if ( in_array( $wra_style_variant_raw, array( 'style_3', 'style3', 'james_heavy', 'james_hd', '3' ), true ) ) {
	$wra_style_variant = 'layout_3';
} elseif ( in_array( $wra_style_variant_raw, array( 'style_2', 'style2', 'charmeck', '2' ), true ) ) {
	$wra_style_variant = 'layout2';
} else {
	$wra_style_variant = 'layout1';
}
$wra_header_layout = carbon_get_the_post_meta( 'wra_header_layout' ) ?: 'standard';
$wra_header_logo_emoji = carbon_get_the_post_meta( 'wra_header_logo_emoji' );
$wra_logo_badge_text   = trim( (string) ( null !== $wra_header_logo_emoji ? $wra_header_logo_emoji : '' ) );
$wra_use_logo_badge    = ( '' !== $wra_logo_badge_text );
$wra_header_logo_text = carbon_get_the_post_meta( 'wra_header_logo_text' );
$wra_header_tagline = carbon_get_the_post_meta( 'wra_header_tagline' );
$wra_header_phone_label = carbon_get_the_post_meta( 'wra_header_phone_label' );

$wra_hero_h1 = carbon_get_the_post_meta( 'wra_hero_h1' );
$wra_hero_eyebrow = carbon_get_the_post_meta( 'wra_hero_eyebrow' );
$wra_hero_sub = carbon_get_the_post_meta( 'wra_hero_sub' );
$wra_hero_support = carbon_get_the_post_meta( 'wra_hero_support' );
$wra_hero_bg_id = carbon_get_the_post_meta( 'wra_hero_bg' );
$wra_hero_bg_url = $wra_hero_bg_id ? wp_get_attachment_image_url( (int) $wra_hero_bg_id, 'full' ) : '';
$wra_hero_primary_btn_prefix = carbon_get_the_post_meta( 'wra_hero_primary_btn_prefix' );
if ( $wra_hero_primary_btn_prefix === '' || $wra_hero_primary_btn_prefix === null ) {
	$wra_hero_primary_btn_prefix = __( 'CALL NOW:', 'landing-page-manager' );
}
$wra_hero_secondary_cta_text = carbon_get_the_post_meta( 'wra_hero_secondary_cta_text' );
$wra_hero_secondary_cta_url = carbon_get_the_post_meta( 'wra_hero_secondary_cta_url' ) ?: '#area';
$wra_show_hero_badge = carbon_get_the_post_meta( 'wra_show_hero_badge' );
$wra_hero_badge_price = carbon_get_the_post_meta( 'wra_hero_badge_price' );
$wra_hero_badge_sub = carbon_get_the_post_meta( 'wra_hero_badge_sub' );

$wra_trust_items = carbon_get_the_post_meta( 'wra_trust_items' ) ?: array();
$wra_show_urgency = carbon_get_the_post_meta( 'wra_show_urgency' );
$wra_urgency_text = carbon_get_the_post_meta( 'wra_urgency_text' );
$wra_show_pricing_strip = carbon_get_the_post_meta( 'wra_show_pricing_strip' );
$wra_pricing_pill_price = carbon_get_the_post_meta( 'wra_pricing_pill_price' );
$wra_pricing_pill_label = carbon_get_the_post_meta( 'wra_pricing_pill_label' );
$wra_pricing_strip_cta = carbon_get_the_post_meta( 'wra_pricing_strip_cta' );

$wra_nm_label = carbon_get_the_post_meta( 'wra_nm_label' );
$wra_nm_h2 = carbon_get_the_post_meta( 'wra_nm_h2' );
$wra_nm_body = carbon_get_the_post_meta( 'wra_nm_body' );
$wra_nm_image_id = carbon_get_the_post_meta( 'wra_nm_image' );
$wra_nm_image_url = $wra_nm_image_id ? wp_get_attachment_image_url( (int) $wra_nm_image_id, 'full' ) : '';
$wra_qf_title = carbon_get_the_post_meta( 'wra_qf_title' );
$wra_qf_items = carbon_get_the_post_meta( 'wra_qf_items' ) ?: array();
$wra_nm_cta_text = carbon_get_the_post_meta( 'wra_nm_cta_text' );

$wra_sv_label = carbon_get_the_post_meta( 'wra_sv_label' );
$wra_sv_h2 = carbon_get_the_post_meta( 'wra_sv_h2' );
$wra_sv_sub = carbon_get_the_post_meta( 'wra_sv_sub' );
$wra_sv_cards = carbon_get_the_post_meta( 'wra_sv_cards' ) ?: array();

$wra_st_label = carbon_get_the_post_meta( 'wra_st_label' );
$wra_st_h2 = carbon_get_the_post_meta( 'wra_st_h2' );
$wra_st_items = carbon_get_the_post_meta( 'wra_st_items' ) ?: array();

$wra_show_feature = carbon_get_the_post_meta( 'wra_show_feature' );
$wra_feat_label = carbon_get_the_post_meta( 'wra_feat_label' );
$wra_feat_h2 = carbon_get_the_post_meta( 'wra_feat_h2' );
$wra_feat_body = carbon_get_the_post_meta( 'wra_feat_body' );
$wra_feat_image_id = carbon_get_the_post_meta( 'wra_feat_image' );
$wra_feat_image_url = $wra_feat_image_id ? wp_get_attachment_image_url( (int) $wra_feat_image_id, 'full' ) : '';
$wra_feat_card_title = carbon_get_the_post_meta( 'wra_feat_card_title' );
$wra_feat_checklist = carbon_get_the_post_meta( 'wra_feat_checklist' ) ?: array();
$wra_feat_cta_text = carbon_get_the_post_meta( 'wra_feat_cta_text' );

$wra_cta_label = carbon_get_the_post_meta( 'wra_cta_label' );
$wra_cta_h2 = carbon_get_the_post_meta( 'wra_cta_h2' );
$wra_cta_sub = carbon_get_the_post_meta( 'wra_cta_sub' );
$wra_cta_btn_text = carbon_get_the_post_meta( 'wra_cta_btn_text' );

$wra_area_label = carbon_get_the_post_meta( 'wra_area_label' );
$wra_area_h2 = carbon_get_the_post_meta( 'wra_area_h2' );
$wra_area_sub = carbon_get_the_post_meta( 'wra_area_sub' );
$wra_area_regions = carbon_get_the_post_meta( 'wra_area_regions' ) ?: array();
$wra_area_callout_title = carbon_get_the_post_meta( 'wra_area_callout_title' );
$wra_area_callout_text = carbon_get_the_post_meta( 'wra_area_callout_text' );
$wra_area_hwy_title = carbon_get_the_post_meta( 'wra_area_hwy_title' );
$wra_area_hwy_text = carbon_get_the_post_meta( 'wra_area_hwy_text' );
$wra_area_closing = carbon_get_the_post_meta( 'wra_area_closing' );

$wra_show_faq = carbon_get_the_post_meta( 'wra_show_faq' );
$wra_faq_h2 = carbon_get_the_post_meta( 'wra_faq_h2' );
$wra_faq_items = carbon_get_the_post_meta( 'wra_faq_items' ) ?: array();

$wra_ft_tagline = carbon_get_the_post_meta( 'wra_ft_tagline' );
$wra_ft_address_note = carbon_get_the_post_meta( 'wra_ft_address_note' );
$wra_ft_services = carbon_get_the_post_meta( 'wra_ft_services' ) ?: array();
$wra_ft_hours = carbon_get_the_post_meta( 'wra_ft_hours' ) ?: array();
$wra_ft_nav = carbon_get_the_post_meta( 'wra_ft_nav' ) ?: array();
$wra_ft_cities = carbon_get_the_post_meta( 'wra_ft_cities' );
$wra_ft_bottom = carbon_get_the_post_meta( 'wra_ft_bottom' );
$wra_ft_bottom_right = carbon_get_the_post_meta( 'wra_ft_bottom_right' );
$wra_ft_layout  = carbon_get_the_post_meta( 'wra_ft_layout' );
$wra_ft_layout  = $wra_ft_layout ? (string) $wra_ft_layout : 'standard';
$wra_ft_area_links = carbon_get_the_post_meta( 'wra_ft_area_links' );
$wra_ft_area_links = is_array( $wra_ft_area_links ) ? $wra_ft_area_links : array();

$wra_mobile_call_text = carbon_get_the_post_meta( 'wra_mobile_call_text' ) ?: 'TAP TO CALL:';

global $wra_schema_json_ld;
$wra_schema_json_ld = carbon_get_the_post_meta( 'wra_schema_json_ld' );

// Select fields: yes/no (empty meta defaults to visible FAQ/feature/urgency).
$wra_show_urgency       = (string) $wra_show_urgency !== 'no';
$wra_show_feature       = (string) $wra_show_feature !== 'no';
$wra_show_faq           = (string) $wra_show_faq !== 'no';
$wra_show_pricing_strip = (string) $wra_show_pricing_strip === 'yes';
$wra_show_hero_badge    = (string) $wra_show_hero_badge === 'yes';

if ( empty( $wra_header_logo_text ) ) {
	$wra_header_logo_text = is_string( $client_name ) ? $client_name : '';
}

include Template_Manager::get_template_path( 'header.php' );
?>

<main id="main-content">
<?php require dirname( __FILE__ ) . '/partials/landing-body.php'; ?>
</main>

<?php require dirname( __FILE__ ) . '/partials/site-footer.php'; ?>

<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" id="mcall"><span><?php echo esc_html( $wra_mobile_call_text ); ?>&nbsp;<?php echo esc_html( $client_phone ); ?></span></a>
<?php endif; ?>

<?php include Template_Manager::get_template_path( 'footer.php' ); ?>
