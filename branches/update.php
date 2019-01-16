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

if($method === 'PUT'){
	//decode json from client
	$datas = json_decode(file_get_contents('php://input'), true);
	$id = $objBranch->escape_bdd($_GET['id']);
	
	if(empty($datas) || !is_array($datas)){
		// set response code - 400 BAD REQUEST
		http_response_code(400); 
		// show error
		echo json_encode(['message' => 'Bad request. Datas unavailable.', 'records' => array()]);
		die();
	}
	//We get the branch
	$res = $objBranch->read($id);
	$res = $objBranch->fetchAll($res);
	
	if(empty($res)){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'No branches found.', 'records' => array()]);
		die();
	}
	//Stock the current branch
	$currentBranch = $res[0];
	
	//Filter the datas so we get only the values
	if(!($updateBranch = $objBranch->filterData($datas))){
		// set response code - 409 Conflict
		http_response_code(409);
		echo json_encode(['message' => 'Error. Unable to update the branch. See the datas sent.', 'records' => array()]);
		die();
	}
	
	$preQuery = array();
	//Prepare the request
	foreach($updateBranch as $key => $val){
		//Associate key and value on the same string
		array_push($preQuery, $key.' = '.$val);
		//Update the currently branch
		$currentBranch[$key] = $val;
	}
		
	$query = 'UPDATE '.$objBranch->table_name.' SET '.implode(',', $preQuery).' WHERE id = '.$id;
	$res = $objBranch->dbSql->query($query);
	
	if($res === false){
		// set response code - 409 Conflict
		http_response_code(409);
		echo json_encode(['message' => 'Sql Error. Unable to update the branch.', 'records' => array()]);
	}else{
		// set response code - 200 OK
		http_response_code(200); 
		// show success
		echo json_encode(['message' => 'Branch updated', 'records' => $currentBranch]);
	}
}else{
	// set response code - 405 Method not allowed
	http_response_code(405); 
	// show error
	echo json_encode(['message' => 'Method not allowed', 'records' => array()]);
}
?>
