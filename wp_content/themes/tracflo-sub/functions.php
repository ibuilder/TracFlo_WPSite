<?php

// Turn cache busting URL off
add_filter( 'trac/add_cache', '__return_true' );

/**/
function redirect_nonloggedin_add_post( $posts ) {
	global $wp, $wp_query;
	if ( false !== strpos($wp->request, 'client/ticket/') ) {
		$wp_query->ticket_public_view = 1;
	} elseif ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( get_permalink() ) );
		die;
	}
}
add_filter( 'wp', 'redirect_nonloggedin_add_post', 1 );

/**
 * ACF suggested speed upgrade
 */
add_filter( 'acf/settings/remove_wp_meta_box', '__return_true' );


/**
 * Set monetary locale
 */
setlocale( LC_MONETARY, 'en_US.UTF-8' );


/**
 * Set money symbol for now
 */
define( 'TRAC_MONEY_SYMBOL', '$' );
define( 'TRAC_DATE_FORMAT', 'm/d/y' );


function trac_money_format( $number ) {
	//if ( ! $number ) { return $number; }
	//return money_format( '%.2n', $number );#TRAC_MONEY_SYMBOL . 
	return TRAC_MONEY_SYMBOL . number_format( (int) $number, 2 );
}


function trac_date( $date ) {
	return date_i18n( TRAC_DATE_FORMAT, $date );
}


function trac_option( $setting_name ) {
	$output = null;
	if ( 'name' === $setting_name || 'company' === $setting_name ) {
		$setting = trim( get_option( 'options_company_name' ) );
		$output = esc_html( $setting );
	} elseif ( 'logo' === $setting_name ) {
		$setting = get_field( 'company_logo', 'options' );
		if ( false !== strpos( basename($_SERVER['REQUEST_URI']), 'pdf' ) ) {
			$upload_dir = wp_upload_dir();
			$url = ( is_ssl() ) ? str_replace( 'http://', 'https://', $upload_dir['baseurl'] ) : $upload_dir['baseurl'];
			$setting = str_replace( $url, $upload_dir['basedir'], $setting );
		}
		$output = esc_url_raw( $setting );
	} elseif ( 'address' === $setting_name ) {
		$setting = trim( get_option( 'options_company_address' ) );
		$output = str_replace( "\n", '<br>', esc_html( $setting ) );
	} elseif ( 'phone' === $setting_name ) {
		$setting = trim( get_option( 'options_company_phone' ) );
		$output = esc_html( $setting );
	} elseif ( $setting = get_field('default_hours', 'options') ) {
		$output = $setting;
	}
	return $output;
}


/**
 * Redirect non-admin from dashboard (which they can't access anyhow)
 */
function trac_redirect_nonadmin() {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return; }
	if ( current_user_can('administrator') ) { return; }
	if ( defined('DOING_AJAX') && DOING_AJAX ) { return; }
	wp_safe_redirect( home_url() );
	die;
}
add_action( 'admin_init', 'trac_redirect_nonadmin' );


/**
 * Remove the admin bar for non admin
 * /
if ( ! current_user_can( 'manage_options' ) ) {
	add_filter( 'show_admin_bar', '__return_false' );
}


/**
 * Require log in
 * /
function trac_getUrl() {
	$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
	$url .= '://' . $_SERVER['SERVER_NAME'];
	$url .= in_array( $_SERVER['SERVER_PORT'], [ '80', '443' ] ) ? '' : ':' . $_SERVER['SERVER_PORT'];
	$url .= $_SERVER['REQUEST_URI'];
	return $url;
}
function trac_forcelogin() {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return; }
	if ( defined('DOING_AJAX') && DOING_AJAX ) { return; }
	if ( ! is_user_logged_in() && false === strpos($_SERVER['SERVER_NAME'], 'tracflodemo') ) {
		$url = trac_getUrl();
		$whitelist    = apply_filters( 'trac/forcelogin/whitelist', [] );
		$redirect_url = apply_filters( 'trac/forcelogin/redirect', $url );
		if ( preg_replace( '/\?.* /', '', $url) != preg_replace('/\?.* /', '', wp_login_url() ) && ! in_array($url, $whitelist) ) {
			wp_safe_redirect( wp_login_url( $redirect_url ), 302 );
			die;
		}
	}
}
add_action( 'init', 'trac_forcelogin' );


/**
 * Set up the theme
 */
define( 'trac_DEVELOPER',     'TracFlo' );
define( 'trac_DEVELOPER_URL', 'http://www.TracFlo.io/' );


/**
 * Google Maps
 */
define( 'GOOGLE_API_KEY', 'AIzaSyCk64jOvnuIwxYP0rytTW6PjuNbLzQl_lI' );
add_action( 'acf/init', function() {
	acf_update_setting( 'google_api_key', GOOGLE_API_KEY );
});


/**
 * Easy way to get theme info if needed
 */
#$trac_info = wp_get_theme();
#define( 'trac_VERSION',        $trac_info->Version );


/**
 * Load modules
 *
 * Comment out modules that are not desired for the current site.
 */

/* Admin */
include( 'admin/admin.php' );
include( 'admin/login.php' );
include( 'admin/tinymce.php' );

/* Front end */
include( 'includes/cleanup.php' );
include( 'includes/rewrites.php' );
include( 'includes/theme-support.php' );
include( 'includes/enqueue.php' );
include( 'includes/page-navi.php' );
include( 'includes/force-login.php' );
include( 'includes/onboarding.php' );

/* Plugins */
include( 'includes/plugins/tracflo-breakdown.php' );
include( 'includes/plugins/tracflo-base.php' );
include( 'includes/plugins/tracflo-client.php' );
include( 'includes/plugins/tracflo-project.php' );
include( 'includes/plugins/tracflo-user.php' );
include( 'includes/plugins/tracflo-contact.php' );
include( 'includes/plugins/tracflo-settings.php' );
include( 'includes/plugins/tracflo-change-order.php' );
include( 'includes/plugins/tracflo-history.php' );
include( 'includes/plugins/tracflo-ticket.php' );
include( 'includes/plugins/tracflo-timesheet.php' );


/**
 * Add options page support
 */
if ( function_exists('acf_add_options_page') ) {
	acf_add_options_page([
		'page_title' => get_option('blogname') . ' Settings',
		'position'   => -1,
		'menu_title' => 'Account Settings',
	]);
}


/**
 * Customize Site
 */

function trac_filter_format_last_number( $last_number ) {
	if ( false !== strpos($last_number, '.') ) {
		$number_array = explode( '.', $last_number );
		if ( $number_array && is_numeric($number_array[ count($number_array) - 1 ]) ) {
			$number_array[ count($number_array) - 1 ] = trac_format_last_number_add( $number_array[ count($number_array) - 1 ] );
			return implode( '.', $number_array );
		}
	} elseif ( false !== strpos($last_number, '-') ) {
		$number_array = explode( '-', $last_number );
		if ( $number_array && is_numeric($number_array[ count($number_array) - 1 ]) ) {
			$number_array[ count($number_array) - 1 ] = trac_format_last_number_add( $number_array[ count($number_array) - 1 ] );
			return implode( '-', $number_array );
		}
	} elseif ( is_numeric($last_number) ) {
		return trac_format_last_number_add( $last_number );
	}
	return 0;
}
function trac_format_last_number_add( $number ) {
	$length = strlen( $number );
	$number = $number + 1;
	return str_pad($number, $length, '0', STR_PAD_LEFT);
}
add_filter( 'trac/format/last_number', 'trac_filter_format_last_number' );


/* Update db keys * /
function trac_update_meta_keys() {
	global $wpdb;
	$metaitems = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE `meta_key` LIKE '%_breakdown'" );
	if ( $metaitems ) {
		foreach ( $metaitems as $metaitem ) {
			$new_key = 'breakdowns_' . ltrim( str_replace('_breakdown', '', $metaitem->meta_key), '_' );
			if ( 0 === strpos($metaitem->meta_key, '_') ) {
				$new_key = '_' . $new_key;
			}
			echo ' ' . $metaitem->meta_key . ' = '. $new_key ."<br>\n";#exit;
			$wpdb->update( $wpdb->postmeta, ['meta_key' => $new_key], ['meta_id' => $metaitem->meta_id], ['%s'], ['%d'] );
		}
	}
}
/**/


/**
 * Control phone number formatting to keep uniform
 */
function trac_filter_format_phone( $phone ) {
	return preg_replace( '~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phone );
}
add_filter( 'trac/format/phone', 'trac_filter_format_phone' );

/**
 * Get the totals for an item into an easy array
 */
function trac_get_totals( $post_id ) {
	$total       = ( $amount = get_post_meta( get_the_ID(), 'total', true ) ) ? $amount : 0;
	$paid_total  = ( $amount = get_post_meta( get_the_ID(), 'paid_total', true ) ) ? $amount : 0;
	return [
		'total' => $total,
		'paid_total' => $paid_total,
		'balance' => $total - $paid_total,
	];
}
add_filter( 'trac/item/totals', 'trac_get_totals' );

/**
 * Change the current project or reset to none
 * /
if ( 2 === get_current_blog_id() ) {
	function trac_change_project() {
		if ( ! empty($_GET['pid']) ) {
			if ( 'reset' == $_GET['pid'] ) {
				setcookie( 'project', null, -1, '/', $_SERVER['HTTP_HOST'] );
				unset( $_COOKIE['project'] );
			} elseif ( $post = get_post($_GET['pid']) ) {
				setcookie( 'project', $post->ID, time()+3600, '/', $_SERVER['HTTP_HOST'] );
			}

			$post_type_object = get_queried_object();
			if ( ! empty($post_type_object->name) ) {
				wp_safe_redirect( get_post_type_archive_link( $post_type_object->name ) );
				die;
			}
		}
	}
	add_action( 'wp', 'trac_change_project' );
}

/**
 * Change the current year or reset to none
 * /
if ( 2 === get_current_blog_id() ) {
	function trac_change_year() {
		if ( ! empty($_GET['yid']) ) {
			if ( 'reset' == $_GET['yid'] ) {
				setcookie( 'year', null, -1, '/', $_SERVER['HTTP_HOST'] );
				unset( $_COOKIE['year'] );
			} elseif ( is_int( $_GET['yid'] ) && 4 === strlen( $_GET['yid'] ) ) {
				setcookie( 'year', $_GET['yid'], time()+3600, '/', $_SERVER['HTTP_HOST'] );
			}
			wp_safe_redirect( get_post_type_archive_link( get_post_type() ) );
			die;
		}
	}
	add_action( 'wp', 'trac_change_year' );
}

/**/

function trac_upload_po( $file, $filename, $post_id=null ) {
	$upload = wp_upload_bits( $filename, null, @file_get_contents($file) );

	if ( false !== $upload['error'] ) {
		return new WP_Error( 'upload_error', 'The file failed to upload.' );
	}

	$file           = $upload['file'];
	$file_url       = $upload['url'];
	$filename       = basename($file);

	// Check the type of file. We'll use this as the 'post_mime_type'.
	$filetype       = wp_check_filetype( $filename, null );

	// Get the path to the upload directory.
	$wp_upload_dir  = wp_upload_dir();

	$attachment     = array(
		'guid'           => $file_url,
		'post_content'   => '',
		'post_mime_type' => $filetype['type'],
		'post_status'    => 'inherit',
		'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
	);

	if ( $post_id ) {
		$attachment['post_parent'] = $post_id;
	}

	$attachment_id = wp_insert_attachment( $attachment, $file, $post_id );
	if ( ! is_wp_error($attachment_id) ) {
		if ( false !== strpos($filetype['type'], 'image') ) {
			include( ABSPATH . "wp-admin" . '/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id,  $attachment_data );
		}
	}

	return $attachment_id;
}

function trac_create_full_co_pdf( $post_id ) {
	$post_type = get_post_type( $post_id );
	if ( 'co' !== $post_type ) { return false; }

	$upload_dir = wp_upload_dir();
	$up_path    = $upload_dir['basedir'];
	$up_url     = $upload_dir['baseurl'];

	// Get stored PDF url
	#$pdf_url  = get_post_meta( $post_id, 'pdf', true );
	#$pdf_path = str_replace($up_url, $up_path, $pdf_url);
	// If no PDF already, generate one
	#if ( ! $pdf_url || ! file_exists( $pdf_path ) ) {
		trac_print_pdf( $post_id );
		$pdf_url = get_post_meta( $post_id, 'pdf', true );
		$pdf_path = str_replace($up_url, $up_path, $pdf_url);
	#}

	// Make sure a PDF exists
	if ( file_exists( $pdf_path ) ) {
		// Set up PDF
		if ( ! class_exists( 'mPDF' ) ) { include( 'includes/vendor/autoload.php' ); }
		$mpdf = new mPDF('utf-8', 'Letter', '12', '', 10, 10, 6.35, 6.35);
		$mpdf->SetImportUse();

		$co_number  = get_post_meta( $post_id, 'number', true );
		$company    = preg_replace( "/[^a-zA-Z0-9 -]+/", '', trac_option( 'name' ) );
		$company    = str_replace( ' ', '_', $company );
		$filename   = "CO_{$co_number}_{$company}.pdf";

		$pagesInFile = $mpdf->SetSourceFile( $pdf_path );
		for ( $i = 1; $i <= $pagesInFile; $i++ ) {
			$tplId = $mpdf->ImportPage( $i );
			$mpdf->UseTemplate( $tplId );
		}

		// Add tickets if any
		$type = get_post_meta( $post_id, 'type', true );
		if ( 'time' == $type ) {
			$tickets = get_post_meta( $post_id, 'tickets', true );
			if ( $tickets ) {
				foreach ( $tickets as $ticket ) {
					$mpdf->WriteHTML( '<pagebreak />' );

					// Add the ticket to new pdf
					$ticket_url  = get_post_meta( $ticket, 'pdf', true );
					$ticket_path = str_replace($up_url, $up_path, $ticket_url);
					if ( ! $ticket_url || ! file_exists( $ticket_path ) ) {
						trac_print_pdf( $ticket );
						$ticket_file = get_post_meta( $ticket, 'pdf', true );
						$ticket_path = str_replace($up_url, $up_path, $ticket_url);
					}
					if ( file_exists( $ticket_path ) ) {
						$pagesInFile = $mpdf->SetSourceFile( $ticket_path );
						for ( $i = 1; $i <= $pagesInFile; $i++ ) {
							$tplId = $mpdf->ImportPage( $i );
							$mpdf->UseTemplate( $tplId );
						}
					}
				}
			}
		}

		// Output PDF
		$mpdf->Output( $filename, 'I' );
	}
}


function trac_maybe_show_pdf() {
	if ( $pdf_id = get_query_var( 'pdf_id' ) ) {
		trac_create_full_co_pdf( $pdf_id );
		die;
	}
}
add_action( 'wp', 'trac_maybe_show_pdf' );


/**
 * Print the PDF file and store url in meta
 *
 * @author  Jake Snyder
 * @return	string pdf file absolute path
 */
function trac_print_pdf( $post_id ) {
	$post_type     = get_post_type( $post_id );
	$pdf_types     = ['co','ticket'];
	if ( ! in_array($post_type, $pdf_types) || 'publish' != get_post_status( $post_id ) ) { return false; }

	// PDF URI
	$filename      = "$post_id.pdf";
	$directory     = 'pdf';
	$upload_dir    = wp_upload_dir();

	$pdf_file_path = $upload_dir['basedir'] . "/$directory/";
	$pdf_file_url  = $upload_dir['baseurl'] . "/$directory/";
	$style_uri     = get_stylesheet_directory() . '/assets/css/style.css';
	$stylesheet    = file_get_contents( $style_uri );

	// Create the PDF directory if it doesn't exist already
	if ( ! file_exists( $pdf_file_path ) ) { mkdir( $pdf_file_path, 0777, true ); }

	// Output a pdf
	if ( ! class_exists( 'mPDF' ) ) { include( 'includes/vendor/autoload.php' ); }
	$mpdf = new mPDF('utf-8', 'Letter', '12', '', 10, 10, 6.35, 6.35);
	//$mpdf->debug = true;
	$mpdf->setDisplayMode('fullpage');
	#$mpdf->SetUserRights();
	$mpdf->title2annots = false;
	#$mpdf->SetAuthor('TracFlo App');
	#$mpdf->SetCreator('TracFlo');

	// Get the content from template
	global $post, $wpdb;
	$post = get_post( $post_id );
	setup_postdata( $post );
	$print_pdf = true;
	ob_start();

	#include( get_template_directory() . '/single-' . $post_type . '.php' );
	include( apply_filters( "trac/$post_type/locate_template", 'single-' . $post_type . '.php' ) );
	$content = ob_get_clean();
	wp_reset_postdata();

	// Start PDF
	$mpdf->WriteHTML( $stylesheet, 1 );
	$mpdf->WriteHTML( $content );

	// Output a PDF
	$mpdf->Output( $pdf_file_path . $filename, 'F' );
	// Store in the metadata
	update_post_meta( $post_id, 'pdf', $pdf_file_url . $filename );

/** /
	// Add invoices if any
	if ( 'co' == $post_type ) {
		$type = get_post_meta( $post_id, 'type', true );
		if ( file_exists( $pdf_file_path . $filename ) && 'time' == $type ) {
			$tickets = get_post_meta( $post_id, 'tickets', true );
			if ( $tickets ) {
				$mpdf = new mPDF('utf-8', 'Letter', '12', '', 10, 10, 6.35, 6.35);
				$mpdf->SetImportUse();
				$pagesInFile = $mpdf->SetSourceFile( $pdf_file_path . $filename );
				for ( $i = 1; $i <= $pagesInFile; $i++ ) {
					$tplId = $mpdf->ImportPage( $i );
					$mpdf->UseTemplate( $tplId );
					$mpdf->WriteHTML( '<pagebreak />' );
				}

				$filesTotal = sizeof($tickets);
				$fileNumber = 1;
				foreach ( $tickets as $ticket ) {
					// Add the invoice to new pdf
					$ticket_pdf_url = get_post_meta( $ticket, 'pdf', true );
					$invoice_file = $ticket_pdf_url;//$pdf_file_path . "$ticket.pdf";
					if ( ! file_exists( $invoice_file ) ) {
						trac_print_pdf( $ticket );
					}
					if ( file_exists( $invoice_file ) ) {
						$pagesInFile = $mpdf->SetSourceFile( $invoice_file );
						for ( $i = 1; $i <= $pagesInFile; $i++ ) {
							$tplId = $mpdf->ImportPage( $i );
							$mpdf->UseTemplate( $tplId );
							if ( $fileNumber < $filesTotal || $i != $pagesInFile ) {
								$mpdf->WriteHTML( '<pagebreak />' );
							}
						}
					}
					$fileNumber++;
				}
				#$filename2   = "CO_{$co_number}_{$company}_i.pdf";
				$mpdf->Output( $pdf_file_path . $filename, 'F' );
				#update_post_meta( $post_id, 'pdf', $pdf_file_url . $filename );
			}
		}
	}
/**/
	return $pdf_file_url . $filename;
}

function trac_create_pdf_save( $post_id ) {
	trac_print_pdf( $post_id );
}
#add_action( 'save_post', 'trac_create_pdf_save', 99 );
#add_action( 'acf/save_post', 'trac_create_pdf_save', 99 );


/** /
if ( ! empty($_REQUEST['pdfupdate']) ) {
	$query = new WP_Query([
		'post_type'      => ['co','ticket'],
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	]);
	while ( $query->have_posts() ) {
		$query->the_post();
		if ( $file = trac_print_pdf( get_the_ID() ) ) {
			echo "PDF created for: $post_id at <a href='$file' target='_blank'>$file</a>" . "</br>\n";
		} else {
			echo "PDF could not be created: $post_id !" . "</br>\n";
		}
	}
	die( 'Completed' );
}
/** /

if ( class_exists('WP_CLI') ) {

	class Trac_Cli extends WP_CLI_Command
	{
		public function pdfupdatepost( $post_id ) {
			if ( $file = trac_print_pdf( $post_id ) ) {
				WP_CLI::success( "PDF created for: $post_id at $file" );
			} else {
				WP_CLI::error( "PDF could not be created: $post_id !" );
			}
		}

		public function pdfupdate( $arg = array() ) {
			// Update the PDF for a single post
			if ( is_numeric($arg[0]) ) { 
				$this->pdfupdatepost( $arg[0] );
				return;
			// Update PDF post_type (CO or TICKET)
			} elseif ( is_string($arg[0]) ) {
				$query = new WP_Query([
					'post_type'      => $arg[0],
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				]);
				while ( $query->have_posts() ) {
					$query->the_post();
					$this->pdfupdatepost( get_the_ID() );
				}
				return;
			// Update PDF for all tickets and change orders
			} else {
				$query = new WP_Query([
					'post_type'      => ['co','ticket'],
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				]);
				while ( $query->have_posts() ) {
					$query->the_post();
					$this->pdfupdatepost( get_the_ID() );
				}
				return;
			}
		}
	}

	WP_CLI::add_command( 'trac', 'Trac_Cli' );

}
/**/
