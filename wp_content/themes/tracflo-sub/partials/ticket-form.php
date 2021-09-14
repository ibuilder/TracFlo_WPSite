<?php

acf_form([
	'field_groups' => [ 'tracflo-tickets' ],
	'return'       => add_query_arg( 'update', 'ticket', get_permalink() ),
	'submit_value' => 'Update Ticket',
]);
