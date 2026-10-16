        </main>

        <?php if( !is_404() ) : ?>
            <footer class="footer section color_white custom-font">
                <div id="footer">
				</div>
                <div id="copyright">
                    <div class="container">
                        <div class="container_inner">
                            <div class="copyright">&copy; <?php echo esc_html( date_i18n( 'Y' ) . ' ' . ( ! empty( $client_name ) && 'Unknown Client' !== $client_name ? $client_name : get_bloginfo( 'name' ) ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'landing-page-manager' ); ?></div>

                            <div class="developed">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        <?php endif; ?>

		<?php wp_footer() ?>
	</body>
</html>
