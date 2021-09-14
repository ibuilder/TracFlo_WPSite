<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Breakdown
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Breakdowns
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Manages breakdowns for employees, materials, equipment.
 * Version:           1.0.1
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'TracFlo_Breakdown';
if (! class_exists($class_name) ) :

class TracFlo_Breakdown
{
	/**
	 * Load the plugin.
	 *
	 * @since	1.0.0
	 * @return	void
	 */
	public function run()
	{
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		add_action( 'trac/breakdown/output',         [ $this, 'output' ] );
		add_filter( 'trac/breakdown/info/labor',     [ $this, 'labor_info' ], 10, 2 );
		add_filter( 'trac/breakdown/info/nonlabor',  [ $this, 'nonlabor_info' ], 10, 2 );
		add_filter( 'trac/breakdown/totals',         [ $this, 'get_total' ] );
	}

	public function output_object()
	{
		$output = new stdClass;
		$output->total = 0;
		$output->type = get_post_meta( get_the_ID(), 'type', true );
		$output->total_breakdowns = 0;
		$output->total_markups = 0;
		$output->titles = [
			'type'        => 'Type',
			'rate_type'   => 'Rate Type',
			'build'       => '',
			'total_hours' => 'Hours',
			'total'       => 'Total',
		];
		$output->titlesall = [];
		$output->rows = [];
		$output->rowsall = [];
		$output->markups = [];
		return $output;
	}

	public function calculate_markups( $output, $markups )
	{
		if ( $markups ) {
			foreach ( $markups as $markup ) {
				if ( empty($markup['title']) || empty($markup['amount']) ) { continue; }
				$markup['total'] = $output->total_breakdowns * ($markup['amount'] / 100);
				$output->markups[] = $markup;
				$output->total_markups += $markup['total'];
			}
		}
		return $output;
	}

	public function labor_info( $breakdowns, $markups=0 )
	{
		if ( ! $breakdowns ) { return false; }
		$total = 0;
		$output = $this->output_object();
		$output->type = 'labor';
		$output->titles['build'] = 'Headcount';
		$output->titlesall = [
			'type'     => 'Type',
			'quantity' => 'Headcount',
			'hours'    => 'Hours',
			'rate'     => 'Rate',
			'total'    => 'Total',
		];
		$output->total_hours = 0;

		foreach ( $breakdowns as $item ) {
			if ( empty($item['quantity']) ) { continue; }
			$total_hours = $item['quantity'] * $item['hours'];
			$total = ( ! empty($item['rate']) ) ? $item['quantity'] * ($item['hours'] * $item['rate']) : 0;
			$output->total_breakdowns += $total;

			$row = [
				'type'      => ( ! empty($item['type']) ? $item['type'] : '' ),
				'rate_type' => ( ! empty($item['rate_type']['label']) ? $item['rate_type']['label'] : '' ),
				'build'     => $item['quantity'] . " &times; " . $item['hours'] . " hours" . ( ! empty($item['rate']) ? " &times; " . trac_money_format( $item['rate'] ) : '' ),
				'total_hours' => $total_hours,
				'total'     => $total,
			];
			$output->rows[] = $row;

			$item['total_hours'] = $total_hours;
			$output->total_hours += $total_hours;
			$item['total'] = $total;
			$output->rowsall[] = $item;
		}

		if ( ! $output->rows ) {
			return null;
		}

		$output = $this->calculate_markups( $output, $markups );
		$output->total = $output->total_breakdowns + $output->total_markups;

		return $output;
	}

	public function nonlabor_info( $breakdowns, $markups=0 )
	{
		if ( ! $breakdowns ) { return false; }
		$total = 0;
		$output = $this->output_object();
		unset($output->titles['rate_type']);
		unset($output->titles['total_hours']);
		$output->type = 'nonlabor';
		$output->titlesall = [
			'type'     => 'Type',
			'quantity' => 'Quantity',
			'unit'     => 'Unit',
			'rate'     => 'Rate',
			'total'    => 'Total',
		];

		foreach ( $breakdowns as $item ) {
			if ( empty($item['type']) ) { continue; }
			$rate = ( ! empty($item['rate']) ? $item['rate'] : 0 );
			$total = $item['quantity'] * $rate;
			$output->total_breakdowns += $total;

			$row = [
				'type' => $item['type'],
				'build' => $item['quantity'] . ' ' . $item['unit'] . ( ! empty($rate) ? " &times; " . trac_money_format( $rate ) : '' ),
				'total' => $total,
			];
			$output->rows[] = $row;

			$item['total'] = $total;
			$output->rowsall[] = $item;
		}

		if ( ! $output->rows ) {
			return null;
		}

		$output = $this->calculate_markups( $output, $markups );
		$output->total = $output->total_breakdowns + $output->total_markups;

		return $output;
	}

	public function get_total( $post_id )
	{
		$total = 0;

		$labor_obj = apply_filters( 'trac/breakdown/info/labor', get_field( 'breakdowns_labor', $post_id ), get_field( 'markups_labor', $post_id ) );
		$total += ( $labor_obj ) ? $labor_obj->total : 0;

		$material_obj = apply_filters( 'trac/breakdown/info/nonlabor', get_field( 'breakdowns_material', $post_id ),  get_field( 'markups_material', $post_id ) );
		$total += ( $material_obj ) ? $material_obj->total : 0;

		$equipment_obj = apply_filters( 'trac/breakdown/info/nonlabor', get_field( 'breakdowns_equipment', $post_id ), get_field( 'markups_equipment', $post_id ) );
		$total += ( $equipment_obj ) ? $equipment_obj->total : 0;

		return $total;
	}

	public function output( $field_obj )
	{
/** /
echo '<pre style="clear:both;font-size:0.7em;text-align:left;width:100%;">';
print_r($field_obj);
echo "</pre>\n";
#exit;
/**/
		if ( ! is_object($field_obj) ) { return false; }
?>
		<div class="tableWrapper">
		<table class="table breakdowns-table js-breakdowns-table">

			<tr>
			<?php $count = 0; foreach ( $field_obj->titles as $key => $value ) : if ( 'total' != $key || 'total' != $field_obj->type ) :
				if ( 'total' === $key && empty($field_obj->total) ) { continue; } ?>
				<th class="col-<?php echo esc_html( $key . (! $count ? ' col-name' : '') ); ?>">
					<?php echo esc_html( $value ) ; ?>
				</th>
			<?php $count++; endif; endforeach; ?>
			</tr>

		<?php foreach ( $field_obj->rows as $key => $row ) : ?>
			<tr>
			<?php $count = 0; foreach ( $row as $key => $value ) : if ( 'total' != $key || 'total' != $field_obj->type ) :
				if ( 'total' === $key && empty($field_obj->total) ) { continue; } ?>
				<td class="col-<?php echo esc_html( $key . (!$count ? ' col-name' : '') ); ?>">
					<?php echo ( 'total' == $key ) ? esc_html( trac_money_format( $value ) ) : esc_html( $value ); #money_format( '%.2n', $value ) ?>
				</td>
			<?php $count++; endif; endforeach; ?>
			</tr>
		<?php endforeach; ?>

		<?php if ( 'total' != $field_obj->type && $field_obj->markups && $field_obj->total_markups ) : ?>
			<?php foreach ( $field_obj->markups as $key => $row ) : ?>
				<tr>
					<td class="col-name"><?php echo esc_html( $row['title'] ); # . ' (' . $row['amount'] . '%)' ?></td>
					<td></td>
					<?php if ( 'labor' === $field_obj->type ) : ?>
					<td></td>
					<td></td>
					<?php endif; ?>
					<td><?php echo esc_html( trac_money_format( $row['total'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		</table>
		</div>

		<?php if ( 'labor' === $field_obj->type ) : ?>
			<footer class="">
				<section class="breakdowns-total">
					Subtotal: <?php echo esc_html( $field_obj->total_hours ); ?><?php if ( ! empty($field_obj->total) ) : ?> Hours / <?php echo esc_html( trac_money_format( $field_obj->total ) ); ?><?php endif; ?>
				</section>
			</footer>
		<?php elseif ( ! empty($field_obj->total) && 'total' != $field_obj->type ) : ?>
			<footer class="">
				<section class="breakdowns-total">
					Subtotal: <?php echo esc_html( trac_money_format( $field_obj->total ) ); ?>
				</section>
			</footer>
		<?php endif; ?>
<?php
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
