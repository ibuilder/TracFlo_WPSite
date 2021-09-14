<?php

if ( ! current_user_can( 'trac_edit_clients' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

get_header();
?>

<div id="sub-nav">
	<div class="wrap">
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-' . $pto->name . '/' ) ); ?>">+ New <?php echo esc_html( $pto->labels->singular_name ); ?></a>
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-contact/' ) ); ?>">+ New Contact</a>
		<?php /** / ?><a class="button" href="<?php echo esc_url( home_url( '/contacts/' ) ); ?>">View Contacts</a><?php /**/ ?>
	</div>
</div>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1><?php echo $pto->labels->name; ?></h1>

			<?php if (have_posts()) : ?>

			<?php do_action( 'patch/page_navi' ); ?>

			<div class="tableWrapper">
			<table class="table project-overview-table js-projects-table"><tbody class="has-clickable-table-rows">
<?php
			while (have_posts()) : the_post();
				$totals = get_post_meta( get_the_ID(), 'totals', true );

				// Set up the meter graph
				$meter_class = '';
				if ( empty($totals['total']) ) {
					$meter_class .= ' is-empty';
				} elseif ( ! empty($totals['balance']) ) {
					$meter_class .= ' is-balance';
				}
				$percent_balance = ( empty($totals['paid']) || empty($totals['total']) ) ? 0 : $totals['paid'] / $totals['total'] * 100;
/** /
echo '<pre style="clear:both;font-size:0.7em;text-align:left;width:100%;">';
print_r($totals);
echo "</pre>\n";
#exit;
/**/
?>

				<tr>
					<th class="col-name" style="text-transform: none;">
						<strong><?php echo strtoupper( get_the_title() ); ?></strong>
						(<a href="<?php the_permalink(); ?>">Edit</a> | <a href="<?php echo esc_url( home_url( '/add-contact/?cid=' . get_the_ID() ) ); ?>">Add Contact</a>)
					</th>

					<th class="col-meter">
						<div class="meter-graph<?php echo $meter_class; ?>">
							<div class="meter-graph-fill" style="width:<?php echo $percent_balance; ?>%"></div>
						</div>
					</th>
					<th class="col-balance">Balance: <?php echo trac_money_format( empty($totals['balance']) ? 0 : $totals['balance'] ); ?></th>

					<th class="col-email">Email</th>
					<th class="col-phone">Phone</th>
				</tr>

<?php
				$contacts = get_posts([
					'meta_query' => [
						[
							'name' => 'client',
							'value' => get_the_ID(),
						],
					],
					'order' => 'ASC',
					'orderby' => 'title',
					'post_type' => 'contact',
				]);

				if ( $contacts ) : foreach ( $contacts as $contact ) :
					$client = get_field('client', $contact->ID);
					$phone  = get_field('phone', $contact->ID);
					$email  = get_field('email', $contact->ID);
?>
					<tr>
						<td class="col-name" colspan="3">
							<a href="<?php esc_url( the_permalink( $contact ) ); ?>"><?php echo esc_html( get_the_title( $contact ) ); ?></a>
						</td>
						<td class="col-email">
						<?php if ( ! empty($email) ) : ?>
							<?php /** / ?><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><?php /**/ ?>
							<?php echo esc_html( $email ); ?>
						<?php endif; ?>
						</td>
						<td class="col-phone">
						<?php if ( ! empty($phone) ) : ?>
							<?php /** / ?><a href="tel:<?php echo str_replace(['.','-',' '], '', $phone); ?>"><?php echo $phone; ?></a><?php /**/ ?>
							<?php echo esc_html( apply_filters( 'trac/format/phone', $phone ) ); ?>
						<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; else: ?>
					<tr><td colspan="5"></td></tr>
				<?php endif; ?>

			<?php endwhile; ?>

			</tbody></table>
			</div>

			<?php do_action( 'patch/page_navi' ); ?>

			<?php else : ?>
				<h4>No items yet</h4>
			<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer();
