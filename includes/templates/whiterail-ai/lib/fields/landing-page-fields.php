<?php

use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;

Container::make( 'post_meta', __('SEO Sections', 'whiterail-ai') )
    ->show_on_post_type('landing_page')
    ->add_tab(
        __('Hero', 'whiterail-ai'),
        array(
            Field::make( 'html', 'hero_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Hero section.</strong> One column, background image with H1 and H2 text overlay.</p>
                ' ),
            Field::make( 'text', 'hero_section_h1', __('H1 Heading', 'whiterail-ai') ),
            Field::make( 'text', 'hero_section_h2', __('H2 Heading', 'whiterail-ai') ),
			Field::make( 'image', 'hero_section_bg', 'Hero Background Image' )
        )
    )
    ->add_tab(
        __('Trust Bar', 'whiterail-ai'),
        array(
            Field::make( 'html', 'trust_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Trust Bar section.</strong> Four columns of H4 headings. First text box automatically links to company GMB page.</p>
                ' ),
            Field::make( 'text', 'trust_bar_text_1', 'Trust Bar Text 1' ),
            Field::make( 'text', 'trust_bar_text_2', 'Trust Bar Text 2' ),
            Field::make( 'text', 'trust_bar_text_3', 'Trust Bar Text 3' ),
            Field::make( 'text', 'trust_bar_text_4', 'Trust Bar Text 4' )
        )
    )
    ->add_tab(
        __('Opening', 'whiterail-ai'),
        array(
            Field::make( 'html', 'opening_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Opening section.</strong> Two columns, text and image.</p>
                ' ),
            Field::make( 'text', 'section_2_h2', __('H2 Heading', 'whiterail-ai') ),
            Field::make( 'rich_text', 'section_2_text', __('Paragraph Text', 'whiterail-ai') )
				->set_rows(10),
            Field::make( 'image', 'section_2_img', 'Image' )
        )
    )
	->add_tab(
        __('Reviews', 'whiterail-ai'),
        array(
            Field::make( 'html', 'reviews_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Reviews section.</strong> Use this tab to display client reviews on this landing page.</p>
                ' ),
        	Field::make( 'text', 'reviews_h2', __('H2 Heading', 'whiterail-ai') )         
            	->set_default_value('What Our Customers Say'),
            Field::make( 'textarea', 'reviews_html', __('Reviews Widget Code (iFrame)', 'whiterail-ai') )
                ->set_attribute('data-custom-id', 'reviews_html_code')
                ->set_help_text( __( 'Paste your review widget code here, or click "Fetch Reviews" to pull this client\'s Google reviews using the Google Maps server key (Settings → API Keys). Results are reused for 12 hours, so new Google reviews can take up to 12 hours to appear.', 'landing-page-manager' ) )
				->set_rows(10),
            Field::make( 'html', 'fetch_reviews_button' )
                ->set_html( '<button id="fetch-reviews-btn" class="button button-primary">Fetch Reviews</button>
                                <div id="fetch-reviews-status" style="margin-top: 10px; font-size: 14px;"></div>
                                ' )
        )
    )
	->add_tab(
        __('Icon Boxes', 'whiterail-ai'),
        array(
            Field::make( 'html', 'icon_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Icon Box section.</strong> Three columns, icon, heading and text.</p>
                ' ),
            Field::make( 'text', 'section_4_h2', __('Heading', 'whiterail-ai') )
				->set_default_value('Fast Response, Fair Prices, No Hassle'),
            Field::make( 'rich_text', 'section_4_text_1', __('Text', 'whiterail-ai') )
            	->set_default_value('We know downtime costs money. That’s why we provide:'),
            Field::make('complex', 'icon_boxes', 'Icon Boxes')
                ->add_fields([
                    Field::make('select', 'icon_class', 'Icon')
                        ->set_options([
                            'fas fa-anchor'           => '⚓ Anchor',
                            'fas fa-bolt'             => '⚡ Bolt',
                            'fas fa-car'              => '🚗 Car',
                            'fas fa-cogs'             => '⚙️ Cogs',
                            'fas fa-construction'     => '👷 Construction Helmet',
                            'fas fa-dollar-sign'      => '💲 Dollar Sign',
                            'fas fa-dumpster'         => '🗑️ Dumpster',
                            'fas fa-gas-pump'         => '⛽ Gas Pump',
                            'fas fa-gift'             => '🎁 Gift',
                            'fas fa-hand-holding-usd' => '🤲💵 Hand Holding USD',
                            'fas fa-hands-helping'    => '🤝 Hands Helping',
                            'fas fa-hard-hat'         => '🦺 Hard Hat',
                            'fas fa-headset'          => '🎧 Headset',
                            'fas fa-heart'            => '❤️ Heart',
                            'fas fa-lock'             => '🔒 Lock',
                            'fas fa-map-marker-alt'   => '📍 Map Marker',
                            'fas fa-money-bill-wave'  => '💸 Money Bill Wave',
                            'fas fa-phone'            => '📞 Phone',
                            'fas fa-piggy-bank'       => '🐷 Piggy Bank',
                            'fas fa-plug'             => '🔌 Plug',
                            'fas fa-star'             => '⭐ Star',
                            'fas fa-star-half-alt'    => '⭐ Star Half',
                            'fas fa-ship'             => '🚢 Ship',
                            'fas fa-shipping-fast'    => '🚚 Shipping Fast',
                            'fas fa-smile'            => '😊 Smiley Face',
                            'fas fa-thumbs-up'        => '👍 Thumbs Up',
                            'fas fa-tools'            => '🛠️ Tools',
                            'fas fa-truck'            => '🚛 Truck',
                            'fas fa-umbrella'         => '☂️ Umbrella',
                            'fas fa-wrench'           => '🔧 Wrench',
                            'fas fa-bicycle'          => '🚲 Bicycle',
                            'fas fa-briefcase'        => '💼 Briefcase',
                            'fas fa-bus'              => '🚌 Bus',
                            'fas fa-chart-line'       => '📈 Chart Line',
                            'fas fa-clipboard-list'   => '📋 Clipboard List',
                            'fas fa-envelope'         => '✉️ Envelope',
                            'fas fa-fire-extinguisher'=> '🧯 Fire Extinguisher',
                            'fas fa-flask'            => '⚗️ Flask',
                            'fas fa-lightbulb'        => '💡 Lightbulb',
                            'fas fa-road'             => '🛣️ Road',
                        ])
                        ->set_default_value('fas fa-truck')
                        ->set_help_text('Select an icon to display for this box.'),
                    Field::make('text', 'title', 'Title'),
                    Field::make('text', 'text', 'Text')
                ]),
            Field::make( 'rich_text', 'section_4_text_2', __('Additional Text', 'whiterail-ai') )
            	->set_default_value('Need a tow after hours? <b>We’re open 24/7, including holidays.</b>')
        )
    )
	->add_tab(
        __('Service Area', 'whiterail-ai'),
        array(
            Field::make( 'html', 'section_5_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Service Areas section.</strong> One column, H2 heading and text. Lists automatically format into inner columns.</p>
                ' ),
        	Field::make( 'text', 'section_5_h2', __('Heading', 'whiterail-ai') )
				->set_default_value('Roads & Highways We Serve'),
            Field::make( 'rich_text', 'section_5_text', __('Text', 'whiterail-ai') )
        )
    )
    ->add_tab(
        __('Roads & Highways', 'whiterail-ai'),
        array(
            Field::make( 'html', 'section_6_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Roads & Highways section.</strong> One column, H2 heading and text. Lists automatically format into inner columns.</p>
                ' ),
        	Field::make( 'text', 'section_6_h2', __('Heading', 'whiterail-ai') )
				->set_default_value('Major Roads & Highways We Serve'),
            Field::make( 'rich_text', 'section_6_text', __('Text', 'whiterail-ai') )
        )
    )
    ->add_tab(
        __('FAQ', 'whiterail-ai'),
        array(
            Field::make( 'html', 'faq_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Frequently Asked Questions section.</strong> One column, accordion and text.</p>
                ' ),
        	Field::make( 'text', 'faq_h2', __('Heading', 'whiterail-ai') )
				->set_default_value('Frequently Asked Questions'),
            Field::make( 'rich_text', 'faq_text', __('Text', 'whiterail-ai') ),
            Field::make('complex', 'faq_items', 'FAQs')
                ->add_fields([
                    Field::make('text', 'question', 'Question'),
                    Field::make('textarea', 'answer', 'Answer')
                ])
        )
    )
    ->add_tab(
        __('Closing', 'whiterail-ai'),
        array(
            Field::make( 'html', 'closing_info', 'About This Section' )
                ->set_html( '
                    <p><strong>This is the Closing section.</strong> Two columns, text and image.</p>
                ' ),
            Field::make( 'text', 'section_8_h2', __('H2 Heading', 'whiterail-ai') ),
            Field::make( 'rich_text', 'section_8_text', __('Paragraph Text', 'whiterail-ai') )
				->set_rows(10),
            Field::make( 'image', 'section_8_img', 'Image' )
        )
    )
;

use LPManager\core\Required_Fields_Registry;

Required_Fields_Registry::register('whiterail-ai', [
    'hero_section_h1',
    'hero_section_h2',
    'hero_section_bg',
    'trust_bar_text_1',
    'trust_bar_text_2',
    'trust_bar_text_3',
    'trust_bar_text_4',
    'section_2_h2',
    'section_2_text',
    'section_2_img',
    'reviews_h2',
    'section_8_h2',
    'section_8_text',
    'section_8_img'
    // add others as needed
]);