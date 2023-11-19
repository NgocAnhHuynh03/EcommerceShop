<?php
 /**
  * Title: Header Media With Patterns
  * Slug: kiddiemart-lite/header-media-with-patterns
  * Categories: kiddiemart-lite, header
  */
?>

<!-- wp:cover {"url":"<?php echo esc_url( get_parent_theme_file_uri( '/assets/images/header-media.jpg' ) ); ?>","dimRatio":0,"isDark":false,"align":"full","className":"wp-block-custom-header-media wp-header-media-with-pattern wp-block-section wp-block-no-padding"} -->
<div class="wp-block-cover alignfull is-light wp-block-custom-header-media wp-header-media-with-pattern wp-block-section wp-block-no-padding">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
	<img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_parent_theme_file_uri( '/assets/images/header-media.jpg' ) ); ?>" data-object-fit="cover"/>
	<div class="wp-block-cover__inner-container">
		<!-- wp:group {"align":"wide","layout":{"inherit":true,"type":"constrained"}} -->
		<div class="wp-block-group alignwide">
			<!-- wp:media-text {"mediaPosition":"right","mediaType":"image"} -->
			<div class="wp-block-media-text alignwide has-media-on-the-right is-stacked-on-mobile">
				<div class="wp-block-media-text__content">
					<!-- wp:heading {"textAlign":"left"} -->
					<h2 class="has-text-align-left"><?php esc_html_e ( 'A Fun Kids Play Everyday', 'kiddiemart-lite' ); ?></h2>
					<!-- /wp:heading -->
					<!-- wp:paragraph {"align":"left"} -->
					<p class="has-text-align-left">
					<?php 
					esc_html_e ( 'In publishing and graphic design, It is a place use
common form of a document.', 'kiddiemart-lite' ); 
					?>
					</p>
					<!-- /wp:paragraph -->
					<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"},"style":{"spacing":{"margin":{"top":"14px"}}}} -->
					<div class="wp-block-buttons" style="margin-top:14px">
						<!-- wp:button -->
						<div class="wp-block-button">
						<a class="wp-block-button__link wp-element-button"><?php esc_html_e ( 'Shop Now', 'kiddiemart-lite' ); ?></a>
						</div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<figure class="wp-block-media-text__media">
					<img src="" alt=""/>
				</figure>
			</div>
			<!-- /wp:media-text -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"wp-header-media-pattern","layout":{"type":"constrained"}} -->
		<div class="wp-block-group wp-header-media-pattern">
			<!-- wp:image {"align":"full","id":15654,"sizeSlug":"full","linkDestination":"none"} -->
			<figure class="wp-block-image alignfull size-full">
				<img src="<?php echo esc_url( get_parent_theme_file_uri( '/assets/images/clouds-pattern.png' ) ); ?>" alt="" class="wp-image-15654"/>
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:group -->
	</div>
</div>
<!-- /wp:cover -->
