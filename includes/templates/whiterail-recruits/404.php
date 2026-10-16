<?php get_header() ?>

    <section class="hero section blur_sides inside_pages">

        <?php
        $inside_hero_heading = carbon_get_theme_option('title_404');
        $sub_title_404 = carbon_get_theme_option('sub_title_404');
        $inside_hero_background = carbon_get_theme_option('background_404');

        $section_background = '';
        if(!empty($inside_hero_background)){
            $section_background = 'background-image: url('.esc_url( get_attachment_src( $inside_hero_background ) ).')';
        }
        ?>

        <div class="section__layer section__layer_base" style="<?php echo esc_attr( $section_background ); ?>"   data-bg-videos="<?php echo (!empty($inside_hero_section_modal_youtube_link) ? esc_attr( json_encode( $bg_videos_for_js ) ) : '') ?>">
            <div class="container">
                <div class="hero_content_wrapper">
                    <div class="hero_content_inner_wrapper">
                        <?php if ( !empty( $inside_hero_heading ) ) : ?>
                            <h1>
                                <?php echo esc_html( $inside_hero_heading ); ?>
                            </h1>
                        <?php endif; ?>

                        <?php if ( !empty( $sub_title_404 ) ) : ?>
                            <h2>
                                <?php echo esc_html( $sub_title_404 ); ?>
                            </h2>
                        <?php endif; ?>

                        <div class="button ">
                            <a  href="<?php bloginfo('url') ?>">
                                <span>
                                    <?php _e('Home','pantusa')?>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

<?php get_footer() ?>