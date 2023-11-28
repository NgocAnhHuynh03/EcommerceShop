<?php
/**
 * Wishlist Wizard features template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="features-selector selected">
	<label for="free_features"  class="d-flex f-center gap-10 top-f-panel">
		<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/wishlist.svg" alt="<?php esc_attr_e( 'Starter', 'wc-wlfmc-wishlist' ); ?>">
		<span class="d-flex f-column">
			<strong><?php esc_attr_e( 'Starter', 'wc-wlfmc-wishlist' ); ?></strong>
			<small><?php esc_attr_e( 'Perfect for stores wanting to taste our solution', 'wc-wlfmc-wishlist' ); ?></small>
		</span>
		<input id="free_features" type="checkbox" name="features" value="free" checked autocomplete="off"/>
	</label>
	<div class="bottom-f-panel">
		<small><?php esc_attr_e( 'This plan includes 2 free features:', 'wc-wlfmc-wishlist' ); ?></small>
		<ul class="feature-list">
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'One Sequential email automation', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'One Sequential email automation', 'wc-wlfmc-wishlist' ); ?></li>
		</ul>
	</div>
</div>
<div class="features-selector">
	<label for="pro_features"   class="d-flex f-center gap-10 top-f-panel">
		<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/premium-demo.svg" alt="<?php esc_attr_e( 'Premium', 'wc-wlfmc-wishlist' ); ?>">
		<span class="d-flex f-column">
			<strong><?php esc_attr_e( 'Premium', 'wc-wlfmc-wishlist' ); ?></strong>
			<small><?php esc_attr_e( 'Perfect for stores that want to increase their sales and leads.', 'wc-wlfmc-wishlist' ); ?></small>
		</span>
		<input id="pro_features" type="checkbox" name="features" value="pro" autocomplete="off"/>
	</label>
	<div class="bottom-f-panel">
		<small><?php esc_attr_e( 'This plan includes 8 features:', 'wc-wlfmc-wishlist' ); ?></small>
		<ul class="feature-list">
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Multi-list', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Multi-list', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Next purchase cart', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Next purchase cart', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Waitlist', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Waitlist', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Abandoned cart', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Abandoned cart', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Multiple Sequential email automation', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Multiple Sequential email automation', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'One-shot email', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'One-shot email', 'wc-wlfmc-wishlist' ); ?></li>
			<li><img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" alt="<?php esc_attr_e( 'Analytics', 'wc-wlfmc-wishlist' ); ?>">&nbsp;<?php esc_attr_e( 'Analytics', 'wc-wlfmc-wishlist' ); ?></li>

		</ul>
	</div>
</div>
<div id="modal_features" class="mct-modal modal_features" style="display:none">
	<div class="modal-overlay modal-toggle" data-modal="modal_features"></div>
	<div class="modal-wrapper modal-transition modal-large">
		<button class="modal-close modal-toggle"
				data-modal="modal_features"><span
				class="dashicons dashicons-no-alt"></span></button>
		<div class="modal-body">
			<div class="modal-content">
				<h2><?php echo wp_kses_post( __( 'Would you like to purchase and install the <span style="color:#EA7A0B">Premium</span> plan now?', 'wc-wlfmc-wishlist' ) ); ?></h2>

				<p class="desc"><?php esc_attr_e( 'Upgrade to MoreConvert Premium now and unlock more amazing features that will supercharge your WooCommerce website.', 'wc-wlfmc-wishlist' ); ?></p>
				<div class="available-pro-features">
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/wishlist.svg" alt="<?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Wishlist', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Set a wishlist button to your store and find out your user\'s favorite items.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/multi-list.svg" alt="<?php esc_attr_e( 'Multi-list', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Multi-list', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Set a multi-list button to your store and allow to create of different lists depending on their needs.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/save-for-later.svg" alt="<?php esc_attr_e( 'Next purchase cart', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Next purchase cart', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Set a button for your users on their cart page and prevent them from deleting products when they aren\'t ready to buy.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/waitlist.svg" alt="<?php esc_attr_e( 'Waitlist', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Waitlist', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Set a button on your product page and help users to notify when a product comes back in stock or its price has changed.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/abandoned-cart.svg" alt="<?php esc_attr_e( 'Abandoned Cart', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Abandoned Cart', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'set a button and allow them to ask for an estimate for some items and create a connection with users.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/automation.svg" alt="<?php esc_attr_e( 'Sequential Email Automation', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Sequential Email Automation', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Set up once to send emails to users based on your terms.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/campaign.svg" alt="<?php esc_attr_e( 'One-shot Email', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'One-shot Email', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Send emails to your selected subscribers.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
					<div class="feature-panel d-flex  gap-10 ">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics.svg" alt="<?php esc_attr_e( 'Analytics', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php esc_attr_e( 'Analytics', 'wc-wlfmc-wishlist' ); ?></strong>
							<small><?php esc_attr_e( 'Find out the ways you can boost your sales based on users\' activities.', 'wc-wlfmc-wishlist' ); ?></small>
						</div>
					</div>
				</div>
				<p class="desc"><?php esc_attr_e( 'Don\'t wait - take advantage of this exclusive offer and start enjoying all the benefits of MoreConvert Premium today!', 'wc-wlfmc-wishlist' ); ?></p>

				<div class="modal-buttons">
					<div class="discount-panel d-flex gap-10">
						<img class="icon" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/discount.svg" alt="<?php esc_attr_e( 'Discount', 'wc-wlfmc-wishlist' ); ?>">
						<div class="d-flex f-column">
							<strong><?php echo wp_kses_post( __( 'Upgrade now and receive a <strong>10% OFF</strong> Coupon', 'wc-wlfmc-wishlist' ) ); ?></strong>
							<strong style="color:#EA7A0B"><u>U6YQ63W9</u></strong>
						</div>
					</div>
					<div class="">
						<p class="center-align mar">
							<a href="https://moreconvert.com/xwyx" class="btn-primary ico-btn check-btn"><?php esc_attr_e( 'Purchase now', 'wc-wlfmc-wishlist' ); ?></a>
						</p>
						<p class="center-align">
							<a href="#" data-modal="modal_features" class="btn-text modal-toggle"><?php esc_attr_e( 'NO, Not Ready Now', 'wc-wlfmc-wishlist' ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	.wizard-btns {
		max-width:380px;
		margin:20px auto 40px !important;
		gap:10px;

	}
	.wizard-btns .btn-primary {
		margin: 0 !important;
		flex: auto;
		min-width: calc( 50% - 10px);

	}
	.wizard-content .flexible-rows {
		width:100%;
	}
	.wizard-content.step-2 img {
		max-width:100%;
	}
	.wizard-content.step-2 img:not(.loop-item) {
		width:100%
	}
	.loop-image-positions {
		position:relative;
		width: 100%;
		overflow: hidden;
		min-height: 180px;
	}
	.loop-item {
		position:absolute;
		top:50%;
		left:50%;
		transform: translate( -50%, -50%)
	}

	.loop-bg {
		width: 100%;

		object-fit: cover;
		min-height: 180px;
	}
	body:not(.rtl) .features-selector input {
		margin-left:auto;
	}
	body.rtl .features-selector input {
		margin-right:auto;
	}
	.features-selector {
		border-radius: 8px;
		background: #fff;
		border: 1px solid #e4dbd0;
		display: block;
		margin-bottom: 20px;
	}
	.features-selector.selected {
		border: 1px solid #ea7a0b;
		box-shadow: 0 0 3px 3px rgba(234, 122, 11, 0.1);
	}
	.features-selector input[type="checkbox"]:checked {
		background: #EA7A0B;
		border-color: #EA7A0B !important;
	}
	.features-selector input[type="checkbox"]:checked:focus {
		border-color: #EA7A0B !important;
	}
	.feature-list {
		color:#EA7A0B;
		display: flex;
		flex-wrap: wrap;
		gap:10px;
	}
	.top-f-panel {
		border-radius: 8px 8px 0 0;
		background: #fdf9f5;
		padding: 20px;
	}
	.bottom-f-panel {
		padding: 10px;
	}
	.available-pro-features {
		background-color: rgba( 234,122,11, .15);
		border-radius: 16px;
		padding:15px;
		display:flex;
		flex-wrap: wrap;
		gap: 10px;
		justify-content: space-between;
	}
	.available-pro-features .feature-panel {
		padding:10px;
		border-radius: 8px;
		border:1px solid #EA7A0B;
		background: #fff;
		text-align: left;
		width:100%;
	}
	.available-pro-features .feature-panel img {
		width:54px;
		height:54px;
	}
	.discount-panel {
		border:1px solid #EA7A0B;
		border-radius: 8px;
		padding:10px;
		background-color:#FFFAF7;
		line-height: 2;
		text-align:left;
		align-items: flex-start;
	}
	.rtl .available-pro-features .feature-panel, .rtl .discount-panel {
		text-align:right
	}
	@media only screen and (min-width: 992px) {
		.mct-modal.modal_features .modal-wrapper {
			max-width: 700px
		}
		.available-pro-features .feature-panel {
			max-width: calc( 50% - 30px );
		}
		.modal_features .modal-buttons {
			flex-direction: row;
			justify-content: space-between;
		}
	}
	@media only screen and (max-width: 600px) {
		.feature-list li {
			flex-basis: calc(33.33% - 10px);
		}
	}


</style>
<script>
	(function ($) {
		$(document).ready(function() {
			var feature = $('input[name="features"]'),
				last = $('.steps .step-last'),
				ready = $('.steps .step-ready'),
				step_5 = $('.next-step.step-5');

			feature.click(function() {
				$(this).closest('.features-selector').toggleClass('selected', $(this).prop('checked'));

				if ($(this).prop('checked')) {
					feature.not(this).prop('checked', false);
					feature.not(this).closest('.features-selector').removeClass('selected');
				}

				if (!$('input[name="features"]:checked').length) {
					$(this).prop('checked', true);
					$(this).closest('.features-selector').addClass('selected');
				}

				if ($('#pro_features').is(':checked')) {
					step_5.data('modal', 'modal_features' );
					last.data('modal', 'modal_features' );
					last.data('modal', 'modal_features' );
					ready.data('modal', 'modal_features' );
					step_5.addClass('modal-toggle');
					last.addClass('modal-toggle');
					ready.addClass('modal-toggle');
				} else {
					step_5.removeClass('modal-toggle');
					last.removeClass('modal-toggle');
					ready.removeClass('modal-toggle');
				}
			});
		});
	})(jQuery);
</script>
