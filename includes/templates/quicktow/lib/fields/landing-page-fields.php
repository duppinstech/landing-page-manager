<?php
/**
 * Carbon Fields for QuickTow template.
 *
 * @package Landing_Page_Manager
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use LPManager\core\Required_Fields_Registry;

Container::make( 'post_meta', __( 'QuickTow', 'landing-page-manager' ) )
	->show_on_post_type( 'landing_page' )
	->add_tab(
		__( 'Layout', 'landing-page-manager' ),
		array(
			// Key kept as wra_style_variant so existing pages keep their saved style.
			Field::make( 'select', 'wra_style_variant', __( 'Style variant', 'landing-page-manager' ) )
				->set_options(
					array(
						'style_1' => __( 'Style 1 — Full-width hero with side image, standard header/footer', 'landing-page-manager' ),
						'style_2' => __( 'Style 2 — Text header, stacked hero, alternate section layout', 'landing-page-manager' ),
						'style_3' => __( 'Style 3 — Heavy-duty: split hero, checkmark trust bar; footer emphasizes services & service area', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'style_1' )
				->set_required( true ),
		)
	)
	->add_tab(
		__( 'Header & branding', 'landing-page-manager' ),
		array(
			Field::make( 'html', 'wra_hdr_info', '' )->set_html(
				'<p>' . esc_html__( 'Logo comes from the client record when set; otherwise use the text logo fields below.', 'landing-page-manager' ) . '</p>'
			),
			Field::make( 'select', 'wra_header_layout', __( 'Header layout', 'landing-page-manager' ) )
				->set_options(
					array(
						'standard' => __( 'Standard (logo + phone + call button)', 'landing-page-manager' ),
						'minimal'  => __( 'Minimal (text logo + phone)', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'standard' ),
			Field::make( 'text', 'wra_header_logo_emoji', __( 'Logo badge (emoji or short text)', 'landing-page-manager' ) )
				->set_help_text( __( 'If you enter anything here (e.g. JT), it always shows in the red header badge and overrides the client logo image. Leave empty to use the client logo when set, or the truck emoji when not.', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_header_logo_text', __( 'Business name (text logo)', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_header_tagline', __( 'Tagline under logo', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_header_phone_label', __( 'Phone label (minimal header)', 'landing-page-manager' ) )
				->set_help_text( __( 'Optional small line above phone, e.g. 24/7 Emergency Dispatch', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'Hero', 'landing-page-manager' ),
		array(
			Field::make( 'textarea', 'wra_hero_h1', __( 'H1 (use line breaks for multiple lines)', 'landing-page-manager' ) )
				->set_help_text( __( 'Main headline for the hero. Should be filled for live pages; the plugin reminds you after save if it is empty.', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_hero_eyebrow', __( 'Eyebrow / kicker', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_hero_sub', __( 'Subheading', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_hero_support', __( 'Supporting line', 'landing-page-manager' ) ),
			Field::make( 'image', 'wra_hero_bg', __( 'Hero right-side image', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_hero_primary_btn_prefix', __( 'Primary button prefix text', 'landing-page-manager' ) )
				->set_default_value( 'CALL NOW:' )
				->set_help_text( __( 'Phone is appended automatically from the client.', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_hero_secondary_cta_text', __( 'Secondary button text', 'landing-page-manager' ) )
				->set_default_value( 'VIEW SERVICE AREA' ),
			Field::make( 'text', 'wra_hero_secondary_cta_url', __( 'Secondary button URL', 'landing-page-manager' ) )
				->set_default_value( '#area' ),
			Field::make( 'select', 'wra_show_hero_badge', __( 'Hero price / promo badge', 'landing-page-manager' ) )
				->set_options(
					array(
						'no'  => __( 'Hide', 'landing-page-manager' ),
						'yes' => __( 'Show', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'no' ),
			Field::make( 'text', 'wra_hero_badge_price', __( 'Badge headline', 'landing-page-manager' ) )
				->set_help_text( __( 'Example: JUMP STARTS FROM $60 (use &lt;em&gt; for accent)', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_hero_badge_sub', __( 'Badge subtext', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'Trust bar & strips', 'landing-page-manager' ),
		array(
			Field::make( 'complex', 'wra_trust_items', __( 'Trust bar items', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'text', __( 'Label', 'landing-page-manager' ) ),
					)
				)
				->set_help_text( __( 'Add, remove, and reorder (e.g. years in business, warranty, response time).', 'landing-page-manager' ) ),
			Field::make( 'select', 'wra_show_urgency', __( 'Urgency strip', 'landing-page-manager' ) )
				->set_options(
					array(
						'yes' => __( 'Show', 'landing-page-manager' ),
						'no'  => __( 'Hide', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'yes' ),
			Field::make( 'textarea', 'wra_urgency_text', __( 'Urgency strip text', 'landing-page-manager' ) )
				->set_help_text( __( 'HTML allowed. Link phone with &lt;a href="tel:..."&gt; if needed.', 'landing-page-manager' ) ),
			Field::make( 'select', 'wra_show_pricing_strip', __( 'Pricing strip (navy bar)', 'landing-page-manager' ) )
				->set_options(
					array(
						'no'  => __( 'Hide', 'landing-page-manager' ),
						'yes' => __( 'Show', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'no' ),
			Field::make( 'text', 'wra_pricing_pill_price', __( 'Pricing pill — price', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_pricing_pill_label', __( 'Pricing pill — label', 'landing-page-manager' ) )
				->set_rows( 2 ),
			Field::make( 'textarea', 'wra_pricing_strip_cta', __( 'Pricing strip — right column', 'landing-page-manager' ) )
				->set_rows( 3 ),
		)
	)
	->add_tab(
		__( 'Intro (near me)', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'wra_nm_label', __( 'Section label', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_nm_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'rich_text', 'wra_nm_body', __( 'Body copy', 'landing-page-manager' ) ),
			Field::make( 'image', 'wra_nm_image', __( 'Image', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_qf_title', __( 'Quick facts card title', 'landing-page-manager' ) )
				->set_default_value( 'Quick Facts' ),
			Field::make( 'complex', 'wra_qf_items', __( 'Quick facts lines', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'line', __( 'Line', 'landing-page-manager' ) ),
					)
				),
			Field::make( 'text', 'wra_nm_cta_text', __( 'CTA button text', 'landing-page-manager' ) )
				->set_default_value( 'Call for Immediate Response' ),
		)
	)
	->add_tab(
		__( 'Services grid', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'wra_sv_label', __( 'Section label', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_sv_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_sv_sub', __( 'Subheading', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_sv_cards', __( 'Service cards', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'textarea', 'icon', __( 'Icon', 'landing-page-manager' ) )
							->set_rows( 4 )
							->set_help_text( __( 'Single emoji, or paste a full inline SVG (from your sample HTML). Media Library SVG uploads work if your site allows SVG; pasting code here avoids uploads.', 'landing-page-manager' ) ),
						Field::make( 'text', 'title', __( 'Title', 'landing-page-manager' ) ),
						Field::make( 'textarea', 'text', __( 'Description', 'landing-page-manager' ) )
							->set_rows( 3 ),
					)
				),
		)
	)
	->add_tab(
		__( 'Stats row', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'wra_st_label', __( 'Section label', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_st_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_st_items', __( 'Stat blocks', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'textarea', 'num', __( 'Big number / word', 'landing-page-manager' ) )
							->set_help_text( __( 'HTML allowed for styling (e.g. %, spans).', 'landing-page-manager' ) )
							->set_rows( 2 ),
						Field::make( 'text', 'lbl', __( 'Label', 'landing-page-manager' ) ),
					)
				),
		)
	)
	->add_tab(
		__( 'Feature / roadside', 'landing-page-manager' ),
		array(
			Field::make( 'select', 'wra_show_feature', __( 'Feature / roadside section', 'landing-page-manager' ) )
				->set_options(
					array(
						'yes' => __( 'Show', 'landing-page-manager' ),
						'no'  => __( 'Hide', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'yes' ),
			Field::make( 'text', 'wra_feat_label', __( 'Section label', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_feat_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'rich_text', 'wra_feat_body', __( 'Body', 'landing-page-manager' ) ),
			Field::make( 'image', 'wra_feat_image', __( 'Image', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_feat_card_title', __( 'Checklist card title', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_feat_checklist', __( 'Checklist items', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'item', __( 'Item', 'landing-page-manager' ) ),
					)
				),
			Field::make( 'text', 'wra_feat_cta_text', __( 'CTA button text', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'CTA banner', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'wra_cta_label', __( 'Eyebrow', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_cta_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_cta_sub', __( 'Subtext', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_cta_btn_text', __( 'Button text', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'Service area', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'wra_area_label', __( 'Section label', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_area_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_area_sub', __( 'Intro text', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_area_regions', __( 'Regions & cities', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'name', __( 'Region name', 'landing-page-manager' ) ),
						Field::make( 'textarea', 'pills', __( 'City pills (one per line)', 'landing-page-manager' ) )
							->set_rows( 4 ),
					)
				),
			Field::make( 'text', 'wra_area_callout_title', __( 'Callout card title', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_area_callout_text', __( 'Callout text', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_area_hwy_title', __( 'Highways strip title', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_area_hwy_text', __( 'Highways / corridors (HTML or plain)', 'landing-page-manager' ) ),
			Field::make( 'rich_text', 'wra_area_closing', __( 'Closing line', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'FAQ', 'landing-page-manager' ),
		array(
			Field::make( 'select', 'wra_show_faq', __( 'FAQ section', 'landing-page-manager' ) )
				->set_options(
					array(
						'yes' => __( 'Show', 'landing-page-manager' ),
						'no'  => __( 'Hide', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'yes' ),
			Field::make( 'text', 'wra_faq_h2', __( 'Heading', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_faq_items', __( 'Questions', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'q', __( 'Question', 'landing-page-manager' ) ),
						Field::make( 'textarea', 'a', __( 'Answer', 'landing-page-manager' ) ),
					)
				),
		)
	)
	->add_tab(
		__( 'Footer', 'landing-page-manager' ),
		array(
			Field::make( 'select', 'wra_ft_layout', __( 'Footer layout', 'landing-page-manager' ) )
				->set_options(
					array(
						'standard' => __( 'Standard (CharMeck: service area or services + contact column)', 'landing-page-manager' ),
						'heavy'    => __( 'Heavy-duty (brand + services + service area links + “Also serving” line)', 'landing-page-manager' ),
					)
				)
				->set_default_value( 'standard' )
				->set_help_text( __( 'Use Heavy-duty for James heavy / equipment style footers. Style 1 pages can keep Standard.', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_ft_area_links', __( 'Footer — service area links (Heavy-duty layout)', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'label', __( 'City / area', 'landing-page-manager' ) ),
						Field::make( 'text', 'url', __( 'URL', 'landing-page-manager' ) )->set_default_value( '#' ),
					)
				)
				->set_help_text( __( 'Shown in the Service area column when Footer layout is Heavy-duty.', 'landing-page-manager' ) ),
			Field::make( 'text', 'wra_ft_tagline', __( 'Tagline under logo', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_ft_address_note', __( 'Address / service line', 'landing-page-manager' ) ),
			Field::make( 'complex', 'wra_ft_services', __( 'Footer — service links', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'label', __( 'Label', 'landing-page-manager' ) ),
						Field::make( 'text', 'url', __( 'URL', 'landing-page-manager' ) )->set_default_value( '#' ),
					)
				),
			Field::make( 'complex', 'wra_ft_hours', __( 'Hours rows', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'day', __( 'Day', 'landing-page-manager' ) ),
						Field::make( 'text', 'hours', __( 'Hours', 'landing-page-manager' ) ),
					)
				),
			Field::make( 'complex', 'wra_ft_nav', __( 'Footer — nav links', 'landing-page-manager' ) )
				->add_fields(
					array(
						Field::make( 'text', 'label', __( 'Label', 'landing-page-manager' ) ),
						Field::make( 'text', 'url', __( 'URL', 'landing-page-manager' ) )->set_default_value( '#' ),
					)
				),
			Field::make( 'textarea', 'wra_ft_cities', __( 'City list line', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'wra_ft_bottom', __( 'Footer bottom — left (copyright)', 'landing-page-manager' ) )
				->set_help_text( __( 'Shown left in the bottom bar on Style 1 and Style 3. HTML allowed. Leave empty for default © year + business name.', 'landing-page-manager' ) )
				->set_rows( 2 ),
			Field::make( 'textarea', 'wra_ft_bottom_right', __( 'Footer bottom — right (tagline / phone)', 'landing-page-manager' ) )
				->set_help_text( __( 'Optional. Shown right-aligned in the bottom bar on Style 1 and Style 3 (e.g. service keywords + phone). HTML allowed.', 'landing-page-manager' ) )
				->set_rows( 2 ),
		)
	)
	->add_tab(
		__( 'Advanced', 'landing-page-manager' ),
		array(
			Field::make( 'textarea', 'wra_schema_json_ld', __( 'JSON-LD (optional)', 'landing-page-manager' ) )
				->set_help_text( __( 'Paste a full JSON-LD script body (without script tags). Outputs in the head.', 'landing-page-manager' ) )
				->set_rows( 12 ),
			Field::make( 'text', 'wra_mobile_call_text', __( 'Mobile bar prefix', 'landing-page-manager' ) )
				->set_default_value( 'TAP TO CALL:' ),
		)
	);

Required_Fields_Registry::register(
		'quicktow',
		array(
			'wra_hero_h1',
		)
	);
