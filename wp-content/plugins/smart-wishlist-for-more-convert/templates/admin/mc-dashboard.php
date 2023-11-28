<?php
/**
 * Wishlist dashboard template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.5
 */

/**
 * Template Variables:
 *
 * @var $var array Array of attributes that needs to be sent to sub-template
 * @var $lists array lists
 * @var $marketing array marketing toolkits
 * @var $reports array reports
 * @var $external_links array external links
 * @var $global_settings string global settings
 * @var $marketing_settings string marketing settings
 * @var $blogs           array blog posts
 * @var $show_quick_setup bool
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


wp_enqueue_style( 'wlfmc-dashboard' );
?>
<div id="wlfmc_dashboard">
	<?php if ( $show_quick_setup ) : ?>
		<div class="mct-article mar-bot-20 mc-setup-notice">
			<div class="article-title mar-bot-20">
				<div>
					<h2><?php esc_html_e( 'Welcome to MoreConvert 🎉', 'wc-wlfmc-wishlist' ); ?></h2>
					<div class="description"><?php esc_html_e( 'You made the best decision by choosing the MoreConvert; our goal is you can sell more,not to entertain your user with some buttons and lists!', 'wc-wlfmc-wishlist' ); ?></div>
				</div>
			</div>
			<div class="d-flex f-center space-between gap-10 justify-center-on-med">
				<div class="white-panel-wrapper d-flex f-column gap-10">
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'mc-wishlist-setup' ), admin_url( 'admin.php' ) ) ); ?>" class="white-panel d-flex f-center gap-10">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/getting-start.svg" alt="<?php esc_attr_e( 'Getting Started', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Getting Started', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Ready to have some simple settings together?', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</a>
					<a href="https://moreconvert.com/ujn2" class="white-panel d-flex f-center gap-10">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/doc.svg" alt="<?php esc_attr_e( 'Full setup wizard guide', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Full setup wizard guide', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Learn how to setup MoreConvert.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</a>
				</div>
				<img class="setup-notice-image" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/setup-notice.svg" alt="notice">
			</div>
		</div>
	<?php endif; ?>
	<div class="mct-article  mar-bot-20">
		<div class="article-title">
			<h2><?php esc_html_e( 'Reports', 'wc-wlfmc-wishlist' ); ?></h2>
			<div class="description"><?php esc_html_e( 'This is a summary of what is currently happening on your website’s wishlist.', 'wc-wlfmc-wishlist' ); ?></div>
		</div>
		<ul class="mct-tools mc-row">
			<?php foreach ( $reports as $k => $tool ) : ?>
				<li>
					<a href="<?php echo '' !== $tool['url'] ? esc_url( $tool['url'] ) : ''; ?>" class="<?php echo esc_attr( $tool['link_class'] ); ?>" data-modal="modal_analytics">
						<?php
						if ( isset( $tool['image'] ) ) {
							echo wp_kses_post( $tool['image'] );
						} elseif ( isset( $tool['image_url'] ) ) {
							echo '<img class="icon" src="' . esc_url( $tool['image_url'] ) . '"/>';
						}
						?>
						<div>
							<span class="tool-title"><?php echo esc_attr( $tool['title'] ); ?></span>
							<strong class="tool-count">
								<?php echo wp_kses_post( $tool['count'] ); ?>
							</strong>
						</div>
					</a>

				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php if ( isset( $external_links ) ) : ?>
		<ul class="mct-tools flat-style">
			<?php foreach ( $external_links as $k => $tool ) : ?>
				<li>
					<a href="<?php echo isset( $tool['url'] ) ? esc_url( $tool['url'] ) : ''; ?>" target="<?php echo isset( $tool['target'] ) ? esc_attr( $tool['target'] ) : '_self'; ?>">
						<?php
						if ( isset( $tool['image'] ) ) {
							echo wp_kses_post( $tool['image'] );
						} elseif ( isset( $tool['image_url'] ) ) {
							echo '<img class="icon" src="' . esc_url( $tool['image_url'] ) . '"/>';
						}
						?>
						<strong class="tool-title"><?php echo esc_attr( $tool['title'] ); ?></strong>
					</a>

				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<div class="mct-article  mar-bot-20">
		<div class="article-title d-flex f-center space-between">
			<div>
				<h2><?php esc_html_e( 'Lists Setting', 'wc-wlfmc-wishlist' ); ?></h2>
				<div class="description"><?php esc_html_e( 'Gather leads and boost your sales with attention-grabbing lists for potential customers.', 'wc-wlfmc-wishlist' ); ?></div>
			</div>
			<?php if ( isset( $global_settings ) ) : ?>
				<a href="<?php echo esc_url( $global_settings ); ?>" class="btn-flat btn-orange">
					<span><?php esc_html_e( 'Global Settings', 'wc-wlfmc-wishlist' ); ?></span>
					<svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
						<rect width="24" height="24" rx="6" fill="#fff"/>
						<g transform="translate(4.463 4.463)">
							<path d="M12.459,6.308H12.1a.642.642,0,0,1-.554-.41V5.876a.626.626,0,0,1,.122-.7l.255-.255a.626.626,0,0,0,0-.875l-.9-.864a.626.626,0,0,0-.875,0l-.255.255a.626.626,0,0,1-.692.122h0a.642.642,0,0,1-.41-.554V2.614A.615.615,0,0,0,8.151,2H6.922a.615.615,0,0,0-.615.615v.36a.642.642,0,0,1-.41.554H5.876a.626.626,0,0,1-.692-.089l-.255-.255a.626.626,0,0,0-.875,0l-.869.869a.626.626,0,0,0,0,.875l.255.255a.626.626,0,0,1,.122.692h0a.642.642,0,0,1-.554.41H2.614A.615.615,0,0,0,2,6.922V8.151a.615.615,0,0,0,.615.615h.36a.642.642,0,0,1,.554.41h0a.626.626,0,0,1-.122.7l-.255.255a.626.626,0,0,0,0,.875l.869.869a.626.626,0,0,0,.875,0l.255-.255a.626.626,0,0,1,.725-.105h0a.642.642,0,0,1,.41.554v.36a.615.615,0,0,0,.637.648H8.151a.615.615,0,0,0,.615-.615V12.1a.642.642,0,0,1,.41-.554h0a.626.626,0,0,1,.7.122l.255.255a.626.626,0,0,0,.875,0l.869-.869a.626.626,0,0,0,0-.875l-.255-.255a.626.626,0,0,1-.105-.725h0a.642.642,0,0,1,.554-.41h.36a.615.615,0,0,0,.648-.637V6.922a.615.615,0,0,0-.615-.615Z" transform="translate(0 0)" fill="#fd5d00"/>
							<circle cx="2" cy="2" r="2" transform="translate(5.537 5.537)" fill="#fff"/>
						</g>
					</svg>

				</a>
			<?php endif; ?>
		</div>
		<ul class="mct-tools">
			<?php foreach ( $lists as $k => $tool ) : ?>
				<li class="<?php echo isset( $tool['container_class'] ) ? esc_attr( $tool['container_class'] ) : ''; ?>">
					<a href="<?php echo isset( $tool['url'] ) ? esc_url( $tool['url'] ) : ''; ?>" data-modal="modal_<?php echo esc_attr( $k ); ?>" class="<?php echo isset( $tool['popup'] ) ? 'modal-toggle' : ''; ?>">
						<?php echo ! isset( $tool['url'] ) && isset( $tool['pro_label'] ) ? '<span class="is-pro">' . esc_attr( $tool['pro_label'] ) . '</span>' : ''; ?>
						<?php
						if ( isset( $tool['image'] ) ) {
							echo wp_kses_post( $tool['image'] );
						} elseif ( isset( $tool['image_url'] ) ) {
							echo '<img class="icon" src="' . esc_url( $tool['image_url'] ) . '"/>';
						}
						?>
						<span
							class="tool-title"><?php echo esc_attr( $tool['title'] ); ?></span>
						<?php if ( isset( $tool['desc'] ) && '' !== $tool['desc'] ) : ?>
							<p class="tool-desc">
								<?php echo wp_kses_post( $tool['desc'] ); ?>
							</p>
						<?php endif; ?>
					</a>
					<?php if ( isset( $tool['popup'] ) ) : ?>
						<div id="modal_<?php echo esc_attr( $k ); ?>" class="mct-modal <?php echo esc_attr( 'modal_' . $k ); ?>" style="display:none">
							<div class="modal-overlay modal-toggle" data-modal="modal_<?php echo esc_attr( $k ); ?>"></div>
							<div class="modal-wrapper modal-transition <?php echo isset( $tool['popup']['class'] ) ? esc_attr( $tool['popup']['class'] ) : ''; ?>">
								<button class="modal-close modal-toggle"
										data-modal="modal_<?php echo esc_attr( $k ); ?>"><span
										class="dashicons dashicons-no-alt"></span></button>
								<div class="modal-body">
									<div class="modal-image">
										<?php if ( isset( $tool['popup']['image_link'] ) ) : ?>
											<a href="<?php echo esc_url( $tool['popup']['image_link'] ); ?>" target="_blank">
										<?php endif; ?>
											<img src="<?php echo esc_url( $tool['popup']['image_url'] ); ?>" alt="image"/>
										<?php if ( isset( $tool['popup']['image_link'] ) ) : ?>
											</a>
										<?php endif; ?>
									</div>
									<div class="modal-content">
										<?php if ( isset( $tool['popup']['title_icon'] ) ) : ?>
											<img src="<?php echo esc_url( $tool['popup']['title_icon'] ); ?>" width="72" height="72" alt="title"/>
										<?php endif; ?>

										<h2><?php echo wp_kses_post( $tool['popup']['title'] ); ?></h2>
										<?php if ( isset( $tool['popup']['desc'] ) ) : ?>
											<p class="desc"><?php echo wp_kses_post( $tool['popup']['desc'] ); ?></p>
										<?php endif; ?>
										<?php if ( isset( $tool['popup']['buttons'] ) && ! empty( $tool['popup']['buttons'] ) ) : ?>
											<div class="modal-buttons">
												<?php foreach ( $tool['popup']['buttons'] as $button ) : ?>
													<a data-modal="modal_<?php echo esc_attr( $k ); ?>" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
														<span><?php echo esc_attr( $button['btn_label'] ); ?></span>
														<?php if ( isset( $button['btn_svg'] ) ) : ?>
															<?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														<?php endif; ?>
													</a>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
								<div class="modal-footer">
									<?php if ( isset( $tool['popup']['features'] ) && ! empty( $tool['popup']['features'] ) ) : ?>
										<?php if ( isset( $tool['popup']['features_title'] ) && ! empty( $tool['popup']['features_title'] ) ) : ?>
											<p class="feature-title"><?php echo wp_kses_post( $tool['popup']['features_title'] ); ?></p>
										<?php endif; ?>
										<div class="features-card">
											<div class="features">
												<?php foreach ( $tool['popup']['features'] as $features ) : ?>
													<div class="column d-flex f-center gap-5">
														<img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
														<span><?php echo esc_attr( $features['desc'] ); ?></span>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="mct-article mar-bot-20">
		<div class="article-title d-flex f-center space-between">
			<div>
				<h2><?php esc_html_e( 'Marketing Toolkit', 'wc-wlfmc-wishlist' ); ?></h2>
				<div class="description"><?php esc_html_e( 'Select A Tool To Increase Your Sale', 'wc-wlfmc-wishlist' ); ?></div>
			</div>
			<?php if ( isset( $marketing_settings ) ) : ?>
				<a href="<?php echo esc_url( $marketing_settings ); ?>" class="btn-flat btn-orange">
					<span><?php esc_html_e( 'Marketing Settings', 'wc-wlfmc-wishlist' ); ?></span>
					<svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
						<rect width="24" height="24" rx="6" fill="#fff"/>
						<g transform="translate(4.463 4.463)">
							<path d="M12.459,6.308H12.1a.642.642,0,0,1-.554-.41V5.876a.626.626,0,0,1,.122-.7l.255-.255a.626.626,0,0,0,0-.875l-.9-.864a.626.626,0,0,0-.875,0l-.255.255a.626.626,0,0,1-.692.122h0a.642.642,0,0,1-.41-.554V2.614A.615.615,0,0,0,8.151,2H6.922a.615.615,0,0,0-.615.615v.36a.642.642,0,0,1-.41.554H5.876a.626.626,0,0,1-.692-.089l-.255-.255a.626.626,0,0,0-.875,0l-.869.869a.626.626,0,0,0,0,.875l.255.255a.626.626,0,0,1,.122.692h0a.642.642,0,0,1-.554.41H2.614A.615.615,0,0,0,2,6.922V8.151a.615.615,0,0,0,.615.615h.36a.642.642,0,0,1,.554.41h0a.626.626,0,0,1-.122.7l-.255.255a.626.626,0,0,0,0,.875l.869.869a.626.626,0,0,0,.875,0l.255-.255a.626.626,0,0,1,.725-.105h0a.642.642,0,0,1,.41.554v.36a.615.615,0,0,0,.637.648H8.151a.615.615,0,0,0,.615-.615V12.1a.642.642,0,0,1,.41-.554h0a.626.626,0,0,1,.7.122l.255.255a.626.626,0,0,0,.875,0l.869-.869a.626.626,0,0,0,0-.875l-.255-.255a.626.626,0,0,1-.105-.725h0a.642.642,0,0,1,.554-.41h.36a.615.615,0,0,0,.648-.637V6.922a.615.615,0,0,0-.615-.615Z" transform="translate(0 0)" fill="#fd5d00"/>
							<circle cx="2" cy="2" r="2" transform="translate(5.537 5.537)" fill="#fff"/>
						</g>
					</svg>

				</a>
			<?php endif; ?>
		</div>
		<ul class="mct-tools">
			<?php foreach ( $marketing as $k => $tool ) : ?>
				<li class="<?php echo isset( $tool['container_class'] ) ? esc_attr( $tool['container_class'] ) : ''; ?>">
					<a href="<?php echo isset( $tool['url'] ) ? esc_url( $tool['url'] ) : ''; ?>" data-modal="modal_<?php echo esc_attr( $k ); ?>" class="<?php echo isset( $tool['popup'] ) ? 'modal-toggle' : ''; ?>">
						<?php echo ! isset( $tool['url'] ) && isset( $tool['pro_label'] ) ? '<span class="is-pro">' . esc_attr( $tool['pro_label'] ) . '</span>' : ''; ?>
						<?php
						if ( isset( $tool['image'] ) ) {
							echo wp_kses_post( $tool['image'] );
						} elseif ( isset( $tool['image_url'] ) ) {
							echo '<img class="icon" src="' . esc_url( $tool['image_url'] ) . '"/>';
						}
						?>

						<span
							class="tool-title"><?php echo esc_attr( $tool['title'] ); ?></span>
						<?php if ( isset( $tool['desc'] ) && '' !== $tool['desc'] ) : ?>
							<p class="tool-desc">
								<?php echo wp_kses_post( $tool['desc'] ); ?>
							</p>
						<?php endif; ?>
					</a>
					<?php if ( isset( $tool['popup'] ) ) : ?>
						<div id="modal_<?php echo esc_attr( $k ); ?>" class="mct-modal <?php echo esc_attr( 'modal_' . $k ); ?>" style="display:none">
							<div class="modal-overlay modal-toggle" data-modal="modal_<?php echo esc_attr( $k ); ?>"></div>
							<div class="modal-wrapper modal-transition <?php echo isset( $tool['popup']['class'] ) ? esc_attr( $tool['popup']['class'] ) : ''; ?>">
								<button class="modal-close modal-toggle"
										data-modal="modal_<?php echo esc_attr( $k ); ?>"><span
											class="dashicons dashicons-no-alt"></span></button>
								<div class="modal-body">
									<div class="modal-image">
										<?php if ( isset( $tool['popup']['image_link'] ) ) : ?>
										<a href="<?php echo esc_url( $tool['popup']['image_link'] ); ?>" target="_blank">
											<?php endif; ?>
											<img src="<?php echo esc_url( $tool['popup']['image_url'] ); ?>" alt="image"/>
											<?php if ( isset( $tool['popup']['image_link'] ) ) : ?>
										</a>
									<?php endif; ?>
									</div>
									<div class="modal-content">
										<?php if ( isset( $tool['popup']['title_icon'] ) ) : ?>
											<img src="<?php echo esc_url( $tool['popup']['title_icon'] ); ?>" width="72" height="72" alt="title"/>
										<?php endif; ?>

										<h2><?php echo wp_kses_post( $tool['popup']['title'] ); ?></h2>
										<?php if ( isset( $tool['popup']['desc'] ) ) : ?>
											<p class="desc"><?php echo wp_kses_post( $tool['popup']['desc'] ); ?></p>
										<?php endif; ?>
										<?php if ( isset( $tool['popup']['buttons'] ) && ! empty( $tool['popup']['buttons'] ) ) : ?>
											<div class="modal-buttons">
												<?php foreach ( $tool['popup']['buttons'] as $button ) : ?>
													<a data-modal="modal_<?php echo esc_attr( $k ); ?>" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
														<span><?php echo esc_attr( $button['btn_label'] ); ?></span>
														<?php if ( isset( $button['btn_svg'] ) ) : ?>
															<?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														<?php endif; ?>
													</a>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
								<div class="modal-footer">
									<?php if ( isset( $tool['popup']['features'] ) && ! empty( $tool['popup']['features'] ) ) : ?>
										<?php if ( isset( $tool['popup']['features_title'] ) && ! empty( $tool['popup']['features_title'] ) ) : ?>
											<p class="feature-title"><?php echo wp_kses_post( $tool['popup']['features_title'] ); ?></p>
										<?php endif; ?>
										<div class="features-card">
											<div class="features">
												<?php foreach ( $tool['popup']['features'] as $features ) : ?>
													<div class="column d-flex f-center gap-5">
														<img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
														<span><?php echo esc_attr( $features['desc'] ); ?></span>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php if ( ! empty( $blogs ) ) : ?>
		<div class="mct-article  mar-bot-20">
			<div class="article-title d-flex f-center space-between">
				<div>
					<h2><?php esc_html_e( 'Blog and Article', 'wc-wlfmc-wishlist' ); ?></h2>
					<div class="description"><?php esc_html_e( 'Discover articles and tutorials to help you to achieve greater success.', 'wc-wlfmc-wishlist' ); ?></div>
				</div>
				<?php if ( isset( $marketing_tips ) ) : ?>
					<a href="<?php echo esc_url( $marketing_tips ); ?>" class="btn-flat btn-orange">
						<span><?php esc_html_e( 'Marketing tips', 'wc-wlfmc-wishlist' ); ?></span>
						<svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
							<rect width="24" height="24" rx="6" fill="#fff"/>
							<path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7 15.099) rotate(-90)" fill="#fd5d00" fill-rule="evenodd"/>
						</svg>
					</a>
				<?php endif; ?>
			</div>
			<ul class="mct-tools mc-row blogs">
				<?php foreach ( $blogs as $k => $blog_post ) : ?>
					<li class="d-flex gap-10">
						<?php
						if ( isset( $blog_post['image'] ) ) {
							echo wp_kses_post( $blog_post['image'] );
						} elseif ( isset( $blog_post['image_url'] ) ) {
							echo '<img width="130" height="130" src="' . esc_url( $blog_post['image_url'] ) . '"/>';
						}
						?>
						<div>
							<span class="tool-title"><?php echo esc_attr( $blog_post['title'] ); ?></span>
							<a href="<?php echo esc_url( $blog_post['post_link'] ); ?>" target="_blank">
								<span style="color:#FD5D00"><?php esc_attr_e( 'Read more', 'wc-wlfmc-wishlist' ); ?></span>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
									<rect width="24" height="24" rx="6" fill="#fd5d00"></rect>
									<path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path>
								</svg>
							</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
