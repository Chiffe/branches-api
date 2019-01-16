<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include config, database and branch object files
require_once '../config/config.php';
require_once '../config/api_database.php';
require_once '../objects/branch.php';
 
 //storage request method
$method = $_SERVER['REQUEST_METHOD'];
//Database sqlite
$db = new MyDB();
// initialize object
$objBranch = new Branch($db);

if($method === 'DELETE'){
	$id = $objBranch->escape_bdd($_GET['id']);
	$matpath = $objBranch->getMatpath($id);
	if($matpath === false || empty($matpath)){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'No branches found.', 'records' => array()]);
		die();
	}
	
	$query = 'DELETE FROM '.$objBranch->table_name.' WHERE matpath LIKE "'.$matpath.'%"';
	
	if($objBranch->dbSql->query($query) === false){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'No branches found. No record deletes.', 'records' => array()]);
	}else{
		// set response code - 200 OK, deleted
		http_response_code(200); 
		// show success
		echo json_encode(['message' => 'Branches deleted', 'records' => array()]);
	}
}else{
	// set response code - 405 Method not allowed
	http_response_code(405); 
	// show error
	echo json_encode(['message' => 'Method not allowed', 'records' => array()]);
}
?>
