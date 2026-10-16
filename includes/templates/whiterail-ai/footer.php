        </main>

        <?php
        use \LPManager\assets\Assets_Manager;
        use LPManager\core\Google_Maps_Helper;
        
        // Get term ID as described above
        $current_post_id = get_the_ID();
	    $client_terms = get_the_terms($current_post_id, 'client');
        	// Get carbon fields
        $client_address = '';
        $client_place_id = '';
        $client_google_search_url = '';
        $gmb_url = '';
        $client_tel = carbon_get_the_post_meta( 'client_tel' );
        $client_primary_color = '#007BFF';
        $client_secondary_color = '#FF4136';
        $client_logo_id = 0;
        $client_logo_url = '';
        $social_links = [];
        $footer_text_color = '#FFFFFF';
        $copyright_text_color = '#FFFFFF';
        $client_name = '';
        $client_domain = home_url( '/' );
        $client_phone = $client_tel;

        if ( ! empty( $client_terms ) && ! is_wp_error( $client_terms ) ) {
            $client_term = $client_terms[0];
            $client_term_id = $client_term->term_id;
            $client_name = $client_term->name;
            $client_address = carbon_get_term_meta( $client_term_id, 'client_address' ) ?: '';
            $client_place_id = carbon_get_term_meta( $client_term_id, 'client_place_id' ) ?: '';
            $client_google_search_url = carbon_get_term_meta( $client_term_id, 'client_google_search_url' ) ?: '';
            $gmb_url = $client_place_id ? Google_Maps_Helper::get_gmb_url( $client_place_id ) : $client_google_search_url;
            $client_primary_color = sanitize_hex_color( (string) carbon_get_term_meta( $client_term_id, 'client_primary_color' ) ) ?: '#007BFF';
            $client_secondary_color = sanitize_hex_color( (string) carbon_get_term_meta( $client_term_id, 'client_secondary_color' ) ) ?: '#FF4136';
            $client_logo_id = carbon_get_term_meta( $client_term_id, 'client_logo' );
            $client_logo_url = $client_logo_id ? wp_get_attachment_image_url( $client_logo_id, 'full' ) : '';
            $social_links = carbon_get_term_meta( $client_term_id, 'social_links' ) ?: [];
            $client_domain = carbon_get_term_meta( $client_term_id, 'client_domain' ) ?: '';
            if ( $client_domain && strpos( $client_domain, '://' ) === false ) {
                $client_domain = 'https://' . $client_domain;
            }
            $client_phone = carbon_get_term_meta( $client_term_id, 'client_phone' ) ?: $client_tel;
            $footer_text_color = Assets_Manager::get_contrast_text_color( $client_primary_color );
            $copyright_text_color = Assets_Manager::get_contrast_text_color( $client_secondary_color );
        }
        ?>

        <footer class="site-footer" style="max-width: 100%; color:<?php echo esc_attr($footer_text_color); ?>";>

        <!-- Google Map -->
        <?php
        // Step 1: Get client term ID linked to current landing page
        $post_id = get_the_ID();
        $client_terms = wp_get_post_terms($post_id, 'client');

        if (!is_wp_error($client_terms) && !empty($client_terms)) {
            $term_id = $client_terms[0]->term_id;

            // Step 2: Fetch lat/lng from term meta
            $lat = carbon_get_term_meta($term_id, 'client_address_lat');
            $lng = carbon_get_term_meta($term_id, 'client_address_lng');
            $api_key = carbon_get_theme_option('lpmanager_google_maps_api_key');

            if ($lat && $lng && $api_key) :
                $maps_src = 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( $api_key ) . '&libraries=marker&loading=async&callback=initFooterMap';
                /* translators: %s: business name */
                $map_label = $client_name ? sprintf( __( 'Map showing the location of %s', 'landing-page-manager' ), $client_name ) : __( 'Location map', 'landing-page-manager' );
            ?>
                <div id="footer-map" role="region" aria-label="<?php echo esc_attr( $map_label ); ?>" style="width: 100%; height: 400px;"></div>
                <script>
                    function initFooterMap() {
                        var mapOptions = {
                            center: { lat: <?php echo floatval($lat); ?>, lng: <?php echo floatval($lng); ?> },
                            zoom: 14,
                            disableDefaultUI: true,
                            styles: [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2e5d4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]}]
                        };
                        var map = new google.maps.Map(document.getElementById("footer-map"), mapOptions);

                        var marker;
                        var hasMapId = typeof map.get === "function" && !!map.get("mapId");
                        if (
                            hasMapId &&
                            google.maps.marker &&
                            google.maps.marker.AdvancedMarkerElement
                        ) {
                            marker = new google.maps.marker.AdvancedMarkerElement({
                                position: mapOptions.center,
                                map: map,
                                title: "<?php echo esc_js( $client_name ); ?>"
                            });
                        } else {
                            marker = new google.maps.Marker({
                                position: mapOptions.center,
                                map: map,
                                title: "<?php echo esc_js( $client_name ); ?>"
                            });
                        }

                        // Values are interpolated as JSON string literals: esc_html alone does not stop ` or ${ breaking out of a template literal.
                        var infoWindowContent = `
                            <div style="max-width:200px;">
                                <h3 style="margin-top:0;">${<?php echo wp_json_encode( esc_html( $client_name ) ); ?>}</h3>
                                <p>${<?php echo wp_json_encode( esc_html( $client_address ) ); ?>}</p>
                                <p><a href="tel:${<?php echo wp_json_encode( esc_attr( preg_replace( '/[^0-9+]/', '', $client_phone ) ) ); ?>}">${<?php echo wp_json_encode( esc_html( $client_phone ) ); ?>}</a></p>
                            </div>
                        `;

                        var infoWindow = new google.maps.InfoWindow({
                            content: infoWindowContent
                        });

                        // Open on marker click (Advanced Marker uses gmp-click).
                        if (marker && typeof marker.addEventListener === "function") {
                            marker.addEventListener("gmp-click", function() {
                                infoWindow.open(map, marker);
                            });
                        } else if (marker && typeof marker.addListener === "function") {
                            marker.addListener("click", function() {
                                infoWindow.open(map, marker);
                            });
                        }

                        // Optionally auto-open on load
                        infoWindow.open(map, marker);
                    }

                    // Maps JS is billed per load, and the map sits at the bottom of the page: only load it
                    // when the visitor scrolls near it. The container's fixed height prevents layout shift.
                    (function () {
                        var el = document.getElementById("footer-map");
                        var src = <?php echo wp_json_encode( $maps_src ); ?>;
                        var loaded = false;
                        function loadMaps() {
                            if (loaded) return;
                            loaded = true;
                            var s = document.createElement("script");
                            s.src = src;
                            s.async = true;
                            document.head.appendChild(s);
                        }
                        if (el && "IntersectionObserver" in window) {
                            var io = new IntersectionObserver(function (entries) {
                                for (var i = 0; i < entries.length; i++) {
                                    if (entries[i].isIntersecting) {
                                        io.disconnect();
                                        loadMaps();
                                        break;
                                    }
                                }
                            }, { rootMargin: "300px 0px" });
                            io.observe(el);
                        } else {
                            loadMaps();
                        }
                    })();
                </script>
            <?php
            elseif ( current_user_can( 'edit_posts' ) ) :
                // Setup hints are for editors only; visitors just don't see a map.
            ?>
                <p style="text-align:center; color:#fff;"><?php esc_html_e( 'Map unavailable: the client needs a geocoded address and a Google Maps browser key must be set. (Only logged-in editors see this.)', 'landing-page-manager' ); ?></p>
            <?php
            endif;
        } elseif ( current_user_can( 'edit_posts' ) ) {
            echo '<p style="text-align:center; color:#fff;">' . esc_html__( 'No client assigned to this landing page. (Only logged-in editors see this.)', 'landing-page-manager' ) . '</p>';
        }
        ?>

        <!-- Footer Main Section -->
        <div class="footer-main" style="background-color: <?php echo esc_attr( $client_primary_color ); ?>; color: <?php echo esc_attr( $footer_text_color ); ?>;">

            <div class="footer-container">

                <!-- Column 1: Logo -->
                <div class="footer-col footer-logo">
                    <a href="<?php echo esc_url($client_domain); ?>">
                        <?php if ($client_logo_url) : ?>
                            <img src="<?php echo esc_url($client_logo_url); ?>" alt="Logo" />
                        <?php else : ?>
                            <span>Logo</span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Column 3: Hours -->
                <div class="footer-col">
                    <h3>Hours of Operation</h3>
                    <table class="hours-table">
                        <tbody>
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day) {
                                echo '<tr><td>' . esc_html($day) . '</td><td>Open 24 Hours</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Column 4: Contact -->
                <div class="footer-col">
                    <h3>Contact</h3>
                    <p><a href="<?php echo esc_url( $gmb_url ); ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo esc_attr( $footer_text_color ); ?>;"><?php echo esc_html( $client_address ); ?></a></p>
                    <?php if ( ! empty( $client_tel ) ) : ?>
                    <p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $client_tel ) ); ?>" style="color: <?php echo esc_attr( $footer_text_color ); ?>;"><?php echo esc_html( $client_tel ); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="footer-bottom" style="background-color:<?php echo esc_attr($client_secondary_color); ?>;">
            <div class="footer-container">
                <div class="copyright" style="color:<?php echo esc_attr($copyright_text_color); ?>;">
                    &copy; <?php echo esc_html( date('Y') ); ?> <?php echo esc_html(get_bloginfo('name')); ?>. All rights reserved.
                </div>

                <?php if ( ! empty( $social_links ) && is_array( $social_links ) ) : ?>
                    <ul class="social-icons" aria-label="<?php esc_attr_e( 'Social media links', 'landing-page-manager' ); ?>">
                        <?php foreach ( $social_links as $link ) :
                            $icon_class = trim( $link['platform'] ?? '' );
                            $url        = trim( $link['url'] ?? '' );
                            if ( empty( $url ) || empty( $icon_class ) ) {
                                continue;
                            }
                        ?>
                        <li class="social-icons__item">
                            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="<?php echo esc_attr( $icon_class ); ?>" style="color: <?php echo esc_attr( $copyright_text_color ); ?>;" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </footer>

		<?php do_action( 'wp_footer' ); ?>
	</body>
</html>