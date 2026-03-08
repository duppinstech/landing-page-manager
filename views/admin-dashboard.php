<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$clients = get_terms( [
    'taxonomy'   => 'client',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
] );
?>

<div class="wrap">
    <h1>Landing Page Manager</h1>
    <p><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=client' ); ?>" class="button button-primary">Add New Client</a></p>

    <?php if ( ! empty( $clients ) && ! is_wp_error( $clients ) ) : ?>
        <ul>
            <?php foreach ( $clients as $client ) : ?>
                <li>
                    <strong><?php echo esc_html( $client->name ); ?></strong>
                    <?php
                    $landing_pages = new WP_Query( [
                        'post_type'      => 'landing_page',
                        'posts_per_page' => -1,
                        'tax_query'      => [
                            [
                                'taxonomy' => 'client',
                                'field'    => 'slug',
                                'terms'    => $client->slug,
                            ],
                        ],
                    ] );

                    if ( $landing_pages->have_posts() ) :
                        echo '<ul>';
                        while ( $landing_pages->have_posts() ) :
                            $landing_pages->the_post();
                            ?>
                            <li>
                                <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php the_title(); ?></a>
                            </li>
                            <?php
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else :
                        echo '<p><em>No landing pages yet.</em></p>';
                    endif;
                    ?>

                    <p><a href="<?php echo admin_url( 'post-new.php?post_type=landing_page' ); ?>" class="button">Add Landing Page</a></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No clients found. Start by <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=client' ); ?>">adding a client</a>.</p>
    <?php endif; ?>
</div>