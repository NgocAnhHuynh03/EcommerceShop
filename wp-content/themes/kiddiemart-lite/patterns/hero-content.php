<?php
 /**
  * Title: Hero Content
  * Slug: kiddiemart-lite/hero-content
  * Categories: kiddiemart-lite, page
  */
?>

<!-- wp:group {"align":"full","className":"wp-block-section  wp-block-hero-content"} -->
<div class="wp-block-group alignfull wp-block-section wp-block-hero-content">
	<!-- wp:group {"layout":{"inherit":false}} -->
	<div class="wp-block-group">
		<!-- wp:group {"style":{"color":{}},"layout":{"inherit":true,"type":"constrained"}} -->
		<div class="wp-block-group">
			<!-- wp:media-text {"mediaId":15319,"mediaLink":"#","mediaType":"image","className":"has-media-on-the-left"} -->
			<div class="wp-block-media-text alignwide is-stacked-on-mobile has-media-on-the-left">
				<figure class="wp-block-media-text__media">
					<img src="<?php echo esc_url( get_parent_theme_file_uri( '/assets/images/hero-content.png' ) ); ?>" alt="" class="wp-image-15319 size-full"/>
				</figure>
				<div class="wp-block-media-text__content">
					<!-- wp:group {"className":"wp-block-group-heading"} -->
					<div class="wp-block-group wp-block-group-heading">
						<!-- wp:heading -->
						<h2 class="wp-block-heading"><?php esc_html_e ( 'We Help To Provide All ', 'kiddiemart-lite' ); ?><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-button-color-color">Essentials</mark><?php esc_html_e ( ' For Kids', 'kiddiemart-lite' ); ?> </h2>
						<!-- /wp:heading -->

					</div>
					<!-- /wp:group -->
					<!-- wp:paragraph -->
					<p>
					<?php 
					esc_html_e ( 'Fun, educational activities for every occasion with premium
						toys and snacks. Learn more about our finest services! ', 'kiddiemart-lite' ) 
					?>
					</p>
					<!-- /wp:paragraph -->
					<!-- wp:buttons -->
					<div class="wp-block-buttons">
						<!-- wp:button {"className":"is-style-outline"} -->
						<div class="wp-block-button is-style-outline">
						<a class="wp-block-button__link wp-element-button"><?php esc_html_e ( 'Discover Now', 'kiddiemart-lite' ); ?></a>
						</div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
			</div>
			<!-- /wp:media-text -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->


