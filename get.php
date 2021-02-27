<?php

/* In deinem Child-Theme eine neue Datei erzeugen.
   In der fuctions.php des aktiven Themes die Datei mit z.B.:
   required_one('get.php');
   hinzufügen
*/

/*
add_shortcode('test123', 'test_function3' );

function test_function3 () {
	// WordPress Interne Funktionen
	
	$users = get_users();
	
	foreach($users as $key)
	{
		echo "ID: ". $key->ID."<br>";
		echo "username: " . $key->user_login."<br>"; 
	}
	
	// Zugriff auf DB-Tabellen - Tabelle muss natürlich vorher angelegt werden ;)
	global $wpdb;
	
	$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test1");
	
	foreach($res as $key)
	{
		echo "ID: ". $key->ID."<br>";
		echo "Vorname: " . $key->Vorname."<br>"; 
	}
}
*/


add_action( 'rest_api_init', function () {
	
// ----- GET -----
register_rest_route( 'test/v1', '/users', array(
 	'methods' => 'GET',
 	'callback' => 'get_request_func',
 	'permission_callback' => function () {
		return current_user_can( 'administrator' );
	}
   ));
});

function get_request_func () {
	global $wpdb;
	
	$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test1");
	
	foreach($res as $key)
	{
		$api[] = array (
			"ID" => $res[0]->ID,
			"Vorname" => $res[0]->Vorname 
		);
	}

	if ( empty( $api ) ) {
		 return new WP_REST_Response("Keine Daten vorhanden!", 200);
		 //return new WP_Error( "Keine Daten vorhanden!", array( 'status' => 204 ) );
		 //return new WP_REST_Response("Keine Daten vorhanden!", 204);
	 }
	
	return new WP_REST_Response( $api, 200 );
}
?>
