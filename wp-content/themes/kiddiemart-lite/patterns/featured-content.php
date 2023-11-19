<?php
 /**
  * Title: Featured Content
  * Slug: kiddiemart-lite/featured-content
  * Categories: kiddiemart-lite, page
  */
?>

<!-- wp:group {"align":"full","className":"wp-block-section wp-block-feature-content","layout":{"inherit":true}} -->
<div class="wp-block-group alignfull wp-block-section wp-block-feature-content">
	<!-- wp:group {"className":"wp-block-group-heading"} -->
	<div class="wp-block-group wp-block-group-heading">
		<!-- wp:heading {"textAlign":"center"} -->
		<h2 class="wp-block-heading has-text-align-center"> <?php esc_html_e ( 'Featured Contents', 'kiddiemart-lite' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center"> <?php esc_html_e ( 'Modern Furniture from Famous Manufacturers and Designers Offering the Finest Furnishings for Every Room of Your Home or Office', 'kiddiemart-lite' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
   <!-- wp:query {"queryId":17,"query":{"perPage":"3","pages":0,"offset":"0","postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"displayLayout":{"type":"flex","columns":3},"align":"wide"} -->
   <div class="wp-block-query alignwide">
	  <!-- wp:post-template -->
	  <!-- wp:group {"layout":{"inherit":false}} -->
	  <div class="wp-block-group">
		 <!-- wp:post-featured-image /-->
		 <!-- wp:group {"className":"featured-content wp-block-post-container","layout":{"type":"constrained"}} -->
		 <div class="wp-block-group featured-content wp-block-post-container">
			<!-- wp:group {"className":"wp-block-post-meta","layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group wp-block-post-meta">
			   <!-- wp:post-date {"format":"F j, Y","isLink":true} /-->
			   <!-- wp:post-author {"showAvatar":false} /-->
			</div>
			<!-- /wp:group -->
			<!-- wp:post-title {"level":4,"isLink":true,"fontSize":"content-heading"} /-->
			<!-- wp:read-more /-->
		 </div>
		 <!-- /wp:group -->
	  </div>
	  <!-- /wp:group -->
	  <!-- /wp:post-template -->
   </div>
   <!-- /wp:query -->
</div>
<!-- /wp:group -->


