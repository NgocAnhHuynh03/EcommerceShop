<?php
 /**
  * Title: Header Default
  * Slug: kiddiemart-lite/header-default
  * Categories: kiddiemart-lite, header
  */
?>

<!-- wp:group {"align":"full","className":"wp-block-header-default","layout":{"inherit":true,"type":"constrained"}} -->
<div class="wp-block-group alignfull wp-block-header-default">
	<!-- wp:group {"align":"wide","className":"wp-block-main-header","layout":{"inherit":true,"type":"constrained"}} -->
	<div class="wp-block-group alignwide wp-block-main-header">
		<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"bottom":"var(\u002d\u002dwp\u002d\u002dcustom\u002d\u002dspacing\u002d\u002dtiny)","top":"var(\u002d\u002dwp\u002d\u002dcustom\u002d\u002dspacing\u002d\u002dtiny)"}}},"className":"wp-block-main-header-container","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<div class="wp-block-group alignfull wp-block-main-header-container" style="padding-top:var(--wp--custom--spacing--tiny);padding-bottom:var(--wp--custom--spacing--tiny)">
			<!-- wp:group {"style":{"spacing":{"blockGap":"15px"}},"className":"wp-header-logo","layout":{"type":"flex"}} -->
			<div class="wp-block-group wp-header-logo">
				<!-- wp:group -->
				<div class="wp-block-group">
					<!-- wp:site-title /-->
					<!-- wp:site-tagline {"style":{"spacing":{"margin":{"top":"4px"}}}} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
			<!-- wp:navigation {"icon":"menu","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"left"}} -->
			<!-- /wp:navigation -->
			<!-- wp:group {"className":"wp-header-right","layout":{"type":"flex","allowOrientation":false}} -->
			<div class="wp-block-group wp-header-right">
				<!-- wp:group {"className":"search-container"} -->
				<div class="wp-block-group search-container">
					<!-- wp:search {"label":"Search","showLabel":false,"buttonText":"Search","buttonUseIcon":true,"className":"dummy-icon"} /-->
						<!-- wp:group {"className":"search-popup"} -->
					<div class="wp-block-group search-popup">
						<!-- wp:search {"label":"Search Your Products","showLabel":true,"placeholder":"What are you looking for...","buttonText":"Search","buttonUseIcon":true,"query":{"post_type":"product"},"className":"search-product"} /-->
						<!-- wp:pattern {"slug":"kiddiemart-lite/search-latest-products"} /-->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:group -->
				<!-- wp:woocommerce/customer-account {"displayStyle":"icon_only"} /-->
				<!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->






