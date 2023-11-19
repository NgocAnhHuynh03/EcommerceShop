<?php
 /**
  * Title: Footer Three Columns
  * Slug: kiddiemart-lite/footer-three-columns
  * Categories: kiddiemart-lite, footer
  */
?>

<!-- wp:group {"align":"full","className":"footer-dark","layout":{"inherit":true,"type":"constrained"}} -->
<div class="wp-block-group alignfull footer-dark">
	<!-- wp:group {"align":"full","className":"wp-block-footer wp-block-widget-area","layout":{"inherit":true}} -->
	<div class="wp-block-group alignfull wp-block-footer wp-block-widget-area">
		<!-- wp:group {"align":"wide"} -->
		<div class="wp-block-group alignwide">
			<!-- wp:columns {"className":"wp-block-footer-widget-area-columns"} -->
			<div class="wp-block-columns wp-block-footer-widget-area-columns">
				<!-- wp:column {"className":"wp-block-widget"} -->
				<div class="wp-block-column wp-block-widget">
					<!-- wp:group -->
					<div class="wp-block-group">
						<!-- wp:heading {"level":1,"style":{"typography":{"textTransform":"capitalize"}},"className":"wp-block-site-title"} -->
						<h1 class="wp-block-site-title"><?php esc_html_e ( 'Kiddiemart', 'kiddiemart-lite' ); ?></h1>
						<!-- /wp:heading -->
						<!-- wp:paragraph -->
						<p><?php esc_html_e ( '+123 456 7890', 'kiddiemart-lite' ); ?></p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0"}}}} -->
						<p style="margin-top:0"><?php esc_html_e ( '3465 Bronco Kiddiemart LLC', 'kiddiemart-lite' ); ?></p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0"}}}} -->
						<p style="margin-top:0"><a href="#"><?php esc_html_e ( 'California 56476', 'kiddiemart-lite' ); ?></a></p>
						<!-- /wp:paragraph -->
						<!-- wp:social-links {"className":"is-style-logos-only","layout":{"type":"flex","justifyContent":"left"}} -->
						<ul class="wp-block-social-links is-style-logos-only">
							<!-- wp:social-link {"url":"#","service":"facebook"} /-->
							<!-- wp:social-link {"url":"#","service":"twitter"} /-->
							<!-- wp:social-link {"url":"#","service":"linkedin"} /-->
							<!-- wp:social-link {"url":"#","service":"youtube"} /-->
							<!-- wp:social-link {"url":"#","service":"instagram"} /-->
						</ul>
						<!-- /wp:social-links -->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:column -->
				<!-- wp:column {"className":"wp-block-widget"} -->
				<div class="wp-block-column wp-block-widget">
					<!-- wp:group -->
					<div class="wp-block-group">
							<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0px","right":"0px","bottom":"20px","left":"0px"}}}} -->
							<h2 id="contact" style="margin-top:0px;margin-right:0px;margin-bottom:20px;margin-left:0px"><?php esc_html_e ( 'About', 'kiddiemart-lite' ); ?></h2>
							<!-- /wp:heading -->
							<!-- wp:navigation {"overlayMenu":"never"} /-->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:column -->
				<!-- wp:column {"className":"wp-block-widget wp-contact-address"} -->
				<div class="wp-block-column wp-block-widget wp-contact-address">
							<!-- wp:group -->
							<div class="wp-block-group">
							<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0px","right":"0px","bottom":"20px","left":"0px"}}}} -->
							<h2 id="contact" style="margin-top:0px;margin-right:0px;margin-bottom:20px;margin-left:0px">Subscribe Newsletter</h2>
							<!-- /wp:heading -->
							<!-- wp:tnp/minimal -->
							<div style="padding:20px" class="wp-block-tnp-minimal">
								<div>[newsletter_form type="minimal"]</div>
							</div>
							<!-- /wp:tnp/minimal -->
						</div>
						<!-- /wp:group -->
					
					</div>
				<!-- /wp:column -->
			</div>
			<!-- /wp:columns -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
