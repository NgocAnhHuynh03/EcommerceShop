<?php
/**
 * Mc template email
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.2.2
 */

/**
 * Template variables:
 *
 * @var $email_heading string Email heading string
 * @var $email_footer  string Email footer string
 * @var $email         WC_Email Email object
 * @var $email_content string Email content (HTML)
 * @var $mc_options    array Email options
 * @var $wishlist_url  string Wishlist url
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<title><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></title>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="background-color:#fff;margin:0;padding:0;-webkit-text-size-adjust:none;text-size-adjust:none">
		<table id="wrapper" <?php echo is_rtl() ? 'dir="rtl"' : ''; ?> <?php echo is_rtl() ? 'style="direction: rtl; text-align: right;"' : ''; ?> width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff">
			<tbody>
				<tr>
					<td style="padding: 50px">
						<table class="row" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0">
							<tbody>
								<tr>
									<td style="padding: 20px 0">
										<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;color:#000;" width="500">
											<tbody>
												<tr>
													<td align="left">
														<a href="<?php echo esc_url( home_url() ); ?>">
															<?php if ( isset( $mc_options['logo'] ) && '' !== $mc_options['logo'] ) : ?>
																<img src="<?php echo esc_url( $mc_options['logo'][0] ); ?>" style="max-height: 60px; width: auto" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" >

															<?php else : ?>
																<img src="<?php echo esc_url( MC_WLFMC_URL . 'assets/frontend/images/email-logo.jpg' ); ?>" style="max-height: 60px; width: auto" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" >

															<?php endif; ?>
														</a>
													</td>
													<td align="right">
														<a href="<?php echo esc_url( $wishlist_url ); ?>"><?php esc_html_e( 'My Wishlist', 'wc-wlfmc-wishlist' ); ?></a>
													</td>

												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="row"  width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0">
							<tbody>
								<tr>
									<td>
										<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;color:#000;border:1px solid #F1F1F1;background-color: #FEFEFE"  width="500">
											<tbody>
												<tr>
													<td style="padding:20px">
														<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" >
															<tbody>
																<tr>
																	<td valign="top">
																		<h1 style="font-size: 24px;font-weight: bold"><?php echo wp_kses_post( $email_heading ); ?></h1>
																	</td>
																</tr>
																<tr>
																	<td style="padding: 0 0 30px 0">
																		<?php echo wp_kses_post( $email_content ); ?>
																	</td>
																</tr>
																<?php if ( isset( $mc_options['avatar'] ) && '' !== $mc_options['avatar'] ) : ?>
																	<tr>
																		<td align="center">
																			<img src="<?php echo esc_url( $mc_options['avatar'][0] ); ?>" style="margin-bottom: 10px;border-radius: 50%" width="90px" height="90px" >
																		</td>
																	</tr>
																<?php else : ?>
																	<tr>
																		<td align="center">
																			<img src="<?php echo esc_url( MC_WLFMC_URL . 'assets/frontend/images/customer-avatar.jpg' ); ?>" style="margin-bottom: 10px;border-radius: 50%" width="90px" height="90px" >
																		</td>
																	</tr>
																<?php endif; ?>
																<?php if ( isset( $mc_options['customer-name'] ) && '' !== $mc_options['customer-name'] ) : ?>
																	<tr>
																		<td  align="center"><p style="margin-bottom: 0;font-weight: 500"><?php echo esc_attr( $mc_options['customer-name'] ); ?></p></td>
																	</tr>
																<?php endif; ?>
																<?php if ( isset( $mc_options['customer-job'] ) && '' !== $mc_options['customer-job'] ) : ?>
																	<tr>
																		<td  align="center"><p style="margin-top: 0;font-weight: 500"><?php echo esc_attr( $mc_options['customer-job'] ); ?></p></td>
																	</tr>
																<?php endif; ?>
																<tr>
																	<td>
																		<table class="divider_block" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%" cellspacing="0" cellpadding="10" border="0">
																			<tbody>
																				<tr>
																					<td align="center">
																						<table role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td class="divider_inner" style="font-size:1px;line-height:1px;border-top:1px solid #bbb"><span> </span></td></tr></tbody></table>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
																<tr>
																	<td  align="center">
																		<table style="mso-table-lspace:0;mso-table-rspace:0"  cellspacing="0" cellpadding="0" border="0">
																			<tbody>
																				<?php if ( ! empty( $mc_options['socials'] ) ) : ?>
																					<tr>
																						<?php foreach ( $mc_options['socials'] as $key => $social ) : ?>
																							<td align="center">
																								<a target="<?php echo wlfmc_is_true( $mc_options['social-link-in-new-tab'] ) ? '_blank' : '_self'; ?>" rel="noopener" class="<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $social['url'] ); ?>" title="<?php echo esc_attr( $key ); ?>">
																									<img src="<?php echo esc_url( apply_filters( 'wlfmc_mc_template_' . $key . '_icon', $social['image'] ) ); ?>" alt="<?php echo esc_attr( $key ); ?>"  width="<?php echo esc_attr( $mc_options['social-size'] ); ?>" height="<?php echo esc_attr( $mc_options['social-size'] ); ?>"/>
																								</a>
																							</td>
																						<?php endforeach; ?>
																					</tr>
																				<?php endif; ?>
																				<?php if ( $email_footer ) : ?>
																					<tr>
																						<td valign="top" align="center" colspan="<?php echo ! empty( $mc_options['socials'] ) ? count( $mc_options['socials'] ) : 1; ?>" style="padding: 10px">
																							<?php echo wp_kses_post( $email_footer ); ?>
																						</td>
																					</tr>
																				<?php endif; ?>
																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
