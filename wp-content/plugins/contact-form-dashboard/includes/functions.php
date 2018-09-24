<?php

function cfd_msg_csv_row( $inputs = array() ) {
	$row = array();

	foreach ( $inputs as $input ) {
		$input = preg_replace( '/(?<!\r)\n/', "\r\n", $input );
		$input = esc_sql( $input );
		$input = sprintf( '"%s"', $input );
		$row[] = $input;
	}

	return implode( ',', $row );
}
