<?php
/**
 * Mc template email styles
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}?>

* {
	box-sizing: border-box
}

body {
	margin: 0;
	padding: 0;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

a[x-apple-data-detectors] {
	color:#E43B67 !important;
	text-decoration: inherit !important
}
p {
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	line-height: inherit;
	margin: 10px 0;
	padding: 0;
}

h1 {
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 30px;
	font-weight: 300;
	line-height: 150%;
	margin: 0;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h2 {
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 18px;
	font-weight: bold;
	line-height: 130%;
	margin: 0 0 18px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 16px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

img {
	border: none;
	display: inline-block;
	font-size: 14px;
	font-weight: bold;
	height: auto;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
	vertical-align: middle;
	margin: 5px;
	max-width: 100%;
	height: auto;
}
