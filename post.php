<?php

/*
add_shortcode('test123', 'test_function3' );

function test_function3 () {

echo "<style>
		.alert {
		  padding: 20px;
		  background-color: #f44336; 
		  color: white;
		  margin-bottom: 15px;
		}
</style>";
	
	$gruppe = get_user_meta(40, 'gruppe', true);
	
	if($gruppe == "")
	{
		echo "<div class=\"alert\">
			<span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">
			×
			</span><p class=\"lead\" style=\"display:inline\">
			Du bist aktuell keiner Gruppe zugeordnet.</p>
		</div>";
	}
	else
	{
		echo "Herzlich Willkommen in der Gruppe ". $gruppe;
	}
*/
	
	// WordPress Interne Funktionen
	/*
	$users = get_users();
	
	foreach($users as $key)
	{
		echo "ID: ". $key->ID."<br>";
		echo "username: " . $key->user_login."<br>"; 
	}*/
	
	// Zugriff auf DB-Tabellen
	//global $wpdb;
	
	//$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test1");
	/*
	foreach($res as $key)
	{
		echo "ID: ". $key->ID."<br>";
		echo "Vorname: " . $key->Vorname."<br>"; 
	}

$vorname = "Maria";
$name = "Mustermann";
$geschlecht = "weiblich";
$geburtstag = "2021-01-01";
	
	
$table = $wpdb->prefix.'test1';
$data = array(
	'Vorname' => $vorname,
	'Nachname' => $name,
	'Geschlecht' => $geschlecht,
	'Geburtstag' => $geburtstag
);
$format = array('%s','%s','%s', '%s');
$wpdb->insert($table,$data,$format);
$my_id = $wpdb->insert_id;
	
echo "Eingefügte ID: ". $my_id;
*/
}



// --- REST-API ---

add_action( 'rest_api_init', function () {
	
// ----- GET -----
register_rest_route( 'test/v1', '/users', array(
 	'methods' => 'GET',
 	'callback' => 'get_request_func',
 	'permission_callback' => function () {
		return current_user_can( 'administrator' );
	}
));
	
// ----- POST -----
register_rest_route( 'test/v1', '/users/(?P<id>\d+)', array(
	'methods' => 'POST',
	'callback' => 'post_request_func',
	'args' => array(
		'id' => array(
			'validate_callback' => function($param, $request, $key) {
				return is_numeric( $param );
			}
		),
	),
	'permission_callback' => function () {
		return current_user_can( 'administrator' );
	}
));
	
// ----- POST -----
  	register_rest_route( 'test/v2', '/users/(?P<id>\d+)', array(
    	'methods' => 'POST',
    	'callback' => 'post_request_func2',
   		 'args' => array(
   		   'id' => array(
   		      'validate_callback' => is_numeric('id')
   			),
    	),
    	'permission_callback' => function () {
      		return current_user_can( 'administrator' );
		}
    ));
});

function post_request_func2 ( $request ) {
	
    $id = $request->get_url_params()["id"];
	$group = $request->get_param( 'gruppe' );
	update_user_meta($id, 'gruppe', $group);

	return new WP_REST_Response( "Erfolg - ". $id ." Gruppe: ". $group, 200 );
}

function post_request_func ( $request ){
	
	// Daten von der API ermitteln
	
	// You can access parameters via direct array access on the object:
 	// $param = $request['id'];
 
    // Or via the helper method:
	//  $param2 = $request->get_param();
 
    // You can get the combined, merged set of parameters:
    //  $parameters = $request->get_params();
 
   // The individual sets of parameters are also available, if needed:
   // $parameters = $request->get_url_params();
   // $parameters = $request->get_query_params();
   // $parameters = $request->get_body_params();
   // $parameters = $request->get_json_params();
   // $parameters = $request->get_default_params();
	
	$body = $request->get_body_params();
	
	$vorname = sanitize_text_field($body["Vorname"]);
	$name = sanitize_text_field($body["Nachname"]);
	$geschlecht = sanitize_text_field($body["Geschlecht"]);
	$geburtsdatum = sanitize_text_field($body["Geburtsdatum"]);
	
	// Daten in Datenbank einfügen
	/*
	global $wpdb;
	$table = $wpdb->prefix.'test1';
	$data = array(
		'Vorname' => $vorname,
		'Nachname' => $name,
		'Geschlecht' => $geschlecht,
		'Geburtstag' => $geburtsdatum
	);
	$format = array('%s','%s','%s', '%s');
	$wpdb->insert($table,$data,$format);
	$my_id = $wpdb->insert_id;
	*/

	return new WP_REST_Response("Erfolg", 200 );
}
 

function get_request_func () {
	global $wpdb;
	
	$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test1");
	
	foreach($res as $key)
	{
		$api[] = array (
			"ID" => $res[0]->ID,
			"Vorname" => $res[0]->Vorname,
			// Anlegen einer Verschachtelung mit X-beliebigen Unterelementen
			"Test" => array (
				"ID" => 5,
				"Name" => array (
					"ID" => 10
				)
			)
		);
	}

	if ( empty( $api ) ) {
		 return new WP_REST_Response("Keine Daten vorhanden!", 200);
		 //return new WP_Error( "Keine Daten vorhanden!", array( 'status' => 204 ) );
		 //return new WP_REST_Response("Keine Daten vorhanden!", 204);
	 }
	
	return new WP_REST_Response( $api, 200 );
}

// --- Tabellenspalten in der Benutzertabelle im WordPress Backend hinzufügen ---

// Spalte erstellen
add_filter('manage_users_columns', 'custom_add_user_id_column');
// Inhalte einfügen
add_filter('manage_users_custom_column',  'custom_show_user_id_column_content', 10, 3);

function custom_add_user_id_column($columns) {
    $columns['Gruppe'] = 'Gruppe';
    return $columns;
}
function custom_show_user_id_column_content($value, $column_name, $user_id) {
    
	$user = get_userdata( $user_id );
    if ( 'Gruppe' == $column_name )
	{
		$test = get_user_meta( $user->ID, 'gruppe', true );
			
			if( !empty ($test) )
			{
				return $test;
			} 
			else
			{	
				return "Nicht zugewiesen";
			}
	}
    return $value;
}

?>
