<?php

/**
 * Email Templates
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Template
 * @param  {String} $subject [description]
 * @param  {String} $message [description]
 * @param  {String} $url     [description]
 * @return {[type]}          [description]
 */
function sell_media_html_email( $subject = '', $message = '', $url = '', $button = '' ) {

	ob_start();

	?>

	<!doctype html>
	<html lang="en">
	<head>
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
	body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
	table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
	img { -ms-interpolation-mode: bicubic; }
	img { border: 0; max-width: 100%; height: auto; line-height: 100%; outline: none; text-decoration: none; }
	table { border-collapse: collapse !important; }
	body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
	a[x-apple-data-detectors] {
		color: inherit !important;
		text-decoration: none !important;
		font-size: inherit !important;
		font-family: inherit !important;
		font-weight: inherit !important;
		line-height: inherit !important;
	}
	@media screen and (max-width: 600px) {
		.img-max {
			width: 100% !important;
			max-width: 100% !important;
			height: auto !important;
		}

		.max-width {
			max-width: 100% !important;
		}

		table table {
			width: 90% !important;
			max-width: 90% !important;
			padding-left: 5% !important;
			padding-right: 5% !important;
		}

		table table table td {
			display: block;
			padding-left: 0 !important;
			padding-right: 0 !important;
			width: 100% !important;
		}
	}
	div[style*="margin: 16px 0;"] { margin: 0 !important; }
	</style>
	</head>
	<body style="margin: 0 !important; padding: 0; !important background-color: #f6f6f6;" bgcolor="#f6f6f6">

	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-family: Helvetica, Arial, sans-serif;">
		<tr>
			<td align="center" valign="top" width="100%" bgcolor="#f6f6f6" style="background: #f6f6f6;">

				<table width="600" cellpadding="0" cellspacing="0" align="center">
					<tr>
					<td width="600" align="center" valign="top" style="font-family: Helvetica, Arial, sans-serif; color: #394240; padding: 40px 20px 40px 20px;">
						<h2 style="font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0 0 24px;"><a href="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>" style="color: #394240; border: 0; text-decoration: none;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a></h2>
					</td>
					</tr>
				</table>

				<table width="600" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="background-color: #ffffff;">
					<tr>
						<td width="600" valign="top">
							<table width="600" cellpadding="0" cellspacing="0" align="center">
								<tr>
								<td width="600" style="padding: 40px 40px 80px 40px;" valign="top" align="center">
									<br />
									<br />
									<h2 style="font-size: 20px; line-height: 30px; color: #444444; margin: 0; padding: 0; font-weight: bold;"><?php echo esc_html( $subject ); ?></h2>
									<br />
									<br />
									<p style="color: #444444; font-size: 16px; line-height: 24px; margin: 0; text-align: left">
										<?php echo $message; ?>
									</p>
									<?php if ( $button && $url ) : ?>
										<br />
										<br />
										<br />
										<a href="<?php echo esc_url( $url ); ?>" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 26px; background-color: #ec6d4d; padding: 14px 26px; border: 1px solid #ec6d4d; font-weight: bold; text-transform: uppercase;"><?php echo esc_html( $button ); ?></a>
										<br />
									<?php endif; ?>
								</td>
								</tr>
							</table>
							
						</td>
					</tr>
				</table>

				<table width="600" cellpadding="0" cellspacing="0" align="center">
					<tr>
						<td width="600" valign="top" align="center" style="padding: 20px; font-family: Helvetica, Arial, sans-serif; color: #999999;">
							<p style="font-size: 12px; line-height: 16px;">
							&copy; <?php echo esc_html( date( 'Y' ) ); ?> <a href="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>" target="_blank" style="color: #999999; text-decoration: none;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
							</p>
						</td>
					</tr>
				</table>

			</td>
		</tr>
	</table>
	</body>
	</html>

	<?php return ob_get_clean();
}
