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

if($method === 'GET'){
	//decode json from client
	$data = json_decode(file_get_contents('php://input'), true);
	//if you read one branch
	if(!empty($_GET)){
		$id = $objBranch->escape_bdd($_GET['id']);
		$children = false;
		//if children from client is OK so we stock it
		if(isset($data['children']) && is_bool($data['children']))
			$children = $data['children'];
		//Execute request sqlite
		$res = $objBranch->read($id, $children);
	}else
		$res = $objBranch->read();	
	
	$branches = $objBranch->fetchAll($res);
	//Build the tree
	$records = $objBranch->fetchItems($branches);
	
	//no records found
	if(empty($records)){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'No branches found.', 'records' => $records]);
	}else{
		// set response code - 200 OK
		http_response_code(200); 
		// show branches data in json format
		echo json_encode(['message' => '', 'records' => $records]);
	}
}else{
	// set response code - 405 Method not allowed
	http_response_code(405); 
	// show error
	echo json_encode(['message' => 'Method not allowed', 'records' => array()]);
}
?>
