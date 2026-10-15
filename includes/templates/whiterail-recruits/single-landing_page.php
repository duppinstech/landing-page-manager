<?php 
/*
Template Name: Whiterail Recruits
Description: A simple landing page template for Whiterail Recruits.
*/
?>

<?php include \LPManager\templates\Template_Manager::get_template_path( 'header.php' ); ?>

<?php
    $hero_section_heading = carbon_get_the_post_meta('hero_section_heading');
    $hero_section_bg = carbon_get_the_post_meta('hero_section_bg');
	// Invalid colours sanitize to empty, so the existing "else" branches below supply the fallback.
	$company_color_main = sanitize_hex_color( (string) carbon_get_the_post_meta('company_color_main') );
	$company_color_secondary = sanitize_hex_color( (string) carbon_get_the_post_meta('company_color_secondary') );
?>

<section class="hero section blur_sides" style="background:url(' <?php echo esc_url( $hero_section_bg ); ?> ')">
	<div class="container">
		<div class="hero_content_wrapper">
			<div class="row">
				<div class="col-lg-8 col-sm-12">
					<?php if ( !empty( $hero_section_heading ) ) : ?>
					<div class="title_wrapper">
						<h1>
							<?php echo esc_html( $hero_section_heading ); ?>
						</h1>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
    </div>
</section>
<section class="section blur_sides">
	<?php if ( !empty( $company_color_main ) ) : ?>
		<div class="no-resume" style="background-color:<?php echo esc_attr( $company_color_main ); ?>;padding:10px;">
	<?php else : ?>
		<div class="no-resume" style="background-color:#2196F3;padding:10px;">
	<?php endif; ?>
		<div>
			<h2 style="text-align:center;color:#fff;text-transform:uppercase;">No Resume Needed</h2>
		</div>
		<div style="text-align:center;">
			<img width="50" height="50" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png" class="attachment-full size-full wp-image-19" alt="" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png 469w, https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png 275w" sizes="(max-width: 469px) 100vw, 469px">															
		</div>
	</div>
</section>

<?php $recruits_url = esc_url( carbon_get_the_post_meta('recruits_url') ); ?>
<section class="apply section blur_sides">
	<div class="apply-now" style="padding:40px;">
		<div style="text-align:center;">
			<a href="<?php echo $recruits_url; ?>">
				<img decoding="async" width="500" height="518" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png" class="attachment-full size-full wp-image-31" alt="Apply Now Button" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1170w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 300w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1024w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 768w" sizes="(max-width: 1170px) 100vw, 1170px">
			</a>
		</div>
	</div>
</section>

<section id="about" class="company section blur_sides">
	<?php
		$company_content_title = carbon_get_the_post_meta('company_content_title');
		$company_content = wpautop( carbon_get_the_post_meta('company_content') );
	?>
	<?php if ( !empty( $company_content_title ) ) : ?>
		<?php if ( !empty( $company_color_main ) && !empty( $company_color_secondary ) ) : ?>
			<div class="title_wrapper wow slideInUp" style="background-image: linear-gradient(270deg, <?php echo esc_attr( $company_color_secondary ); ?> 0%, <?php echo esc_attr( $company_color_main ); ?> 90%);" data-wow-duration="1s" >
		<?php else : ?>
			<div class="title_wrapper_color wow slideInUp" data-wow-duration="1s" >
		<?php endif; ?>
			<h2 style="color:#fff;text-transform:uppercase;font-size:3em;">
				<?php echo esc_html( $company_content_title ); ?>
			</h2>
		</div>
		<div class="content">
			<?php echo wp_kses_post( $company_content ); ?>
		</div>
	<?php endif; ?>
</section>

<?php $reviews = carbon_get_the_post_meta('company_reviews'); ?>
<?php if ( !empty( $reviews ) ) : ?>
<section class="reviews section blur_sides">
	<div class="reviews-4-col row">
		<?php foreach ( $reviews as $review_key => $review ): ?>
			<?php if ( count($reviews) % 4 == 0 ) : ?>
				<div class="col-lg-3 col-md-6 col-sm-12 reviews_title wow slideInUp">
			<?php elseif ( count($review) % 2 == 0 ) : ?>
				<div class="col-lg-6 col-md-6 col-sm-12 reviews_title wow slideInUp">
			<?php else : ?>
				<div class="col-lg-4 col-md-12 col-sm-12 reviews_title wow slideInUp">
			<?php endif; ?>
				<div class="review" id="review_<?php echo esc_attr( $review_key ); ?>">
					<div class="content_wrapper">
						<div class="content_inner_wrapper">
							<?php if( !empty( $review['review_img'] ) ): ?>
							<img class="review-img" src="<?php echo esc_url( $review['review_img'] ); ?>" />
							<?php endif; ?>

							<?php if( !empty( $review['review_title'] ) ): ?>
							<h3 class="review-heading" ><?php echo esc_html( $review['review_title'] ); ?></h3>
							<?php endif; ?>

							<?php if( !empty( $review['review_desc'] ) ): ?>
							<p class="review-text" ><?php echo esc_html( $review['review_desc'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php $benefits = carbon_get_the_post_meta('benefits_content'); ?>

<?php if ( !empty( $benefits ) ) : ?>
<section id="benefits" class="benefits section blur_sides">
	<?php
		$benefits_content_title = carbon_get_the_post_meta('benefits_content_title');
	?>
	<?php if ( !empty( $benefits_content_title ) ) : ?>
		<?php if ( !empty( $company_color_main ) ) : ?>
			<div class="title_wrapper wow slideInUp" style="background-image: linear-gradient(270deg, <?php echo esc_attr( $company_color_secondary ? $company_color_secondary : '#2196F3' ); ?> 0%, <?php echo esc_attr( $company_color_main ); ?> 90%);" data-wow-duration="1s" >
		<?php else : ?>
			<div class="title_wrapper_color wow slideInUp" data-wow-duration="1s" >
		<?php endif; ?>
			<h2 style="color:#fff;text-transform:uppercase;font-size:3em;">
				<?php echo esc_html( $benefits_content_title ); ?>
			</h2>
		</div>
		<div>
		</div>
	<?php endif; ?>
</section>
<?php $benefits = carbon_get_the_post_meta('benefits_content'); ?>
<section class="benefits section blur_sides">
	<div class="benefits-4-col row">
		<?php foreach ( $benefits as $benefit_key => $benefit ): ?>
			<?php if ( count($benefits) % 4 == 0 ) : ?>
				<div class="col-lg-3 col-md-6 col-sm-12 reviews_title wow slideInUp">
			<?php elseif ( count($benefits) % 2 == 0 ) : ?>
				<div class="col-lg-6 col-md-6 col-sm-12 reviews_title wow slideInUp">
			<?php else : ?>
				<div class="col-lg-4 col-md-12 col-sm-12 reviews_title wow slideInUp">
			<?php endif; ?>
				<div class="benefit" id="benefit_<?php echo esc_attr( $benefit_key ); ?>">
					<div class="content_wrapper">
						<div class="content_inner_wrapper">
							<?php if( !empty( $benefit['benefits_icon'] ) ): ?>
							<img class="benefit-img" src="<?php echo esc_url( $benefit['benefits_icon'] ); ?>" />
							<?php endif; ?>

							<?php if( !empty( $benefit['benefits_title'] ) ): ?>
							<h3 class="benefit-heading" ><?php echo esc_html( $benefit['benefits_title'] ); ?></h3>
							<?php endif; ?>

							<?php if( !empty( $benefit['benefits_desc'] ) ): ?>
							<p class="benefit-text" ><?php echo esc_html( $benefit['benefits_desc'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
<?php
	$benefits_content_img = carbon_get_the_post_meta('benefits_content_img');
	if ( function_exists('get_attachment_src') && !empty($benefits_content_img) ) {
        $benefits_content_img = get_attachment_src( $benefits_content_img );
    }

?>
<?php if ( !empty( $benefits_content_img ) ) : ?>
<section class="benefits section blur_sides">
	<div class="container bg_img_500" style="background:url(' <?php echo esc_url( $benefits_content_img ); ?> ')">
	</div>
</section>
<?php endif; ?>
		
<section class="section blur_sides">
	<?php if ( !empty( $company_color_main ) ) : ?>
		<div class="no-resume" style="background-color:<?php echo esc_attr( $company_color_main ); ?>;padding:10px;">
	<?php else : ?>
		<div class="no-resume" style="background-color:#2196F3;padding:10px;">
	<?php endif; ?>
		<div>
			<h2 style="text-align:center;color:#fff;text-transform:uppercase;">No Resume Needed</h2>
		</div>
		<div style="text-align:center;">
			<img width="50" height="50" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png" class="attachment-full size-full wp-image-19" alt="" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png 469w, https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows-275x300.png 275w" sizes="(max-width: 469px) 100vw, 469px">															</div>
	</div>
</section>

<?php $recruits_url = carbon_get_the_post_meta('recruits_url'); ?>
<section class="apply section blur_sides">
	<div class="apply-now" style="padding:40px;">
		<div style="text-align:center;">
			<a href=" <?php echo esc_url( $recruits_url ); ?> ">
				<img decoding="async" width="500" height="518" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png" class="attachment-full size-full wp-image-31" alt="" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1170w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 300w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1024w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 768w" sizes="(max-width: 1170px) 100vw, 1170px">
			</a>
		</div>
	</div>
</section>

<section id="more" name="more" class="more section blur_sides">
	<?php
		$more_content_title = carbon_get_the_post_meta('more_content_title');
        $raw_more_content  = carbon_get_the_post_meta('more_content');

        $more_content = '';
        if ( !empty( $raw_more_content ) && is_string( $raw_more_content ) ) {
            $more_content = wpautop( $raw_more_content );
        }
	?>
	<?php if ( !empty( $more_content_title ) ) : ?>
		<?php if ( !empty( $company_color_main ) ) : ?>
			<div class="title_wrapper wow slideInUp" style="background-image: linear-gradient(270deg, <?php echo esc_attr( $company_color_secondary ? $company_color_secondary : '#2196F3' ); ?> 0%, <?php echo esc_attr( $company_color_main ); ?> 90%);" data-wow-duration="1s" >
		<?php else : ?>
			<div class="title_wrapper_color wow slideInUp" data-wow-duration="1s" >
		<?php endif; ?>
			<h2 style="color:#fff;text-transform:uppercase;font-size:3em;">
				<?php echo esc_html( $more_content_title ); ?>
			</h2>
		</div>
		<div class="content">
			<?php echo wp_kses_post( $more_content ); ?>
		</div>
	<?php endif; ?>
</section>
<?php
	$more_content_img = carbon_get_the_post_meta('more_content_img');
	if ( function_exists('get_attachment_src') && !empty($more_content_img) ) {
        $more_content_img = get_attachment_src( $more_content_img );
    }

?>
<?php if ( !empty( $more_content_img ) ) : ?>
<section class="more section blur_sides">
	<div class="container bg_img_500" style="background:url(' <?php echo esc_url( $more_content_img ); ?> ')">
	</div>
</section>
<?php endif; ?>

<section class="section blur_sides">
	<?php if ( !empty( $company_color_main ) ) : ?>
		<div class="no-resume" style="background-color:<?php echo esc_attr( $company_color_main ); ?>;padding:10px;">
	<?php else : ?>
		<div class="no-resume" style="background-color:#2196F3;padding:10px;">
	<?php endif; ?>
		<div>
			<h2 style="text-align:center;color:#fff;text-transform:uppercase;">No Resume Needed</h2>
		</div>
		<div style="text-align:center;">
			<img width="50" height="50" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png" class="attachment-full size-full wp-image-19" alt="" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows.png 469w, https://whiterailrecruits.com/wp-content/uploads/2021/10/whiterail-recruits-arrows-275x300.png 275w" sizes="(max-width: 469px) 100vw, 469px">															</div>
	</div>
</section>

<?php $recruits_url = carbon_get_the_post_meta('recruits_url'); ?>
<section class="apply section blur_sides">
	<div class="apply-now" style="padding:40px;">
		<div style="text-align:center;">
			<a href=" <?php echo esc_url( $recruits_url ); ?> ">
				<img decoding="async" width="500" height="518" src="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png" class="attachment-full size-full wp-image-31" alt="" srcset="https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1170w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 300w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 1024w, https://whiterailrecruits.com/wp-content/uploads/2021/10/button_with_text.png 768w" sizes="(max-width: 1170px) 100vw, 1170px">
			</a>
		</div>
	</div>
</section>

<section class="powered section blur_sides">
	<div class="powered-by" style="padding:10px;">
		<div style="text-align:center">
			<p>Powered by <a href="https://whiterailrecruits.com/">Whiterail</a></p>
		</div>
	</div>
</section>

<?php include \LPManager\templates\Template_Manager::get_template_path( 'footer.php' ); ?>