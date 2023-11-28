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
	<body <?php echo is_rtl() ? 'dir="rtl"' : ''; ?> <?php echo is_rtl() ? 'style="direction: rtl; text-align: right;"' : ''; ?> <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="-webkit-text-size-adjust:none;text-size-adjust:none">
		<table <?php echo is_rtl() ? 'dir="rtl"' : ''; ?> <?php echo is_rtl() ? 'style="direction: rtl; text-align: right;"' : ''; ?>  width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff">
			<tbody>
				<tr>
					<td style="padding: 50px">
						<?php if ( $email_heading ) : ?>
							<h1 style="font-size: 24px;font-weight: bold"><?php echo wp_kses_post( $email_heading ); ?></h1>
						<?php endif; ?>
						<?php echo $email_content ? wp_kses_post( $email_content ) : ''; ?>
						<?php if ( $email_footer ) : ?>
							<br>
							<?php echo wp_kses_post( $email_footer ); ?>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table
	</body>
</html>
