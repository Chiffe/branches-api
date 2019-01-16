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

if($method === 'POST'){
	//decode json from client
	$datas = json_decode(file_get_contents('php://input'), true);
	
	if(empty($datas) || !is_array($datas)){
		// set response code - 400 BAD REQUEST
		http_response_code(400); 
		// show error
		echo json_encode(['message' => 'Bad request. Datas unavailable.', 'records' => array()]);
		die();
	}
	
	$branchParent = array();
	
	if(!($newBranch = $objBranch->filterData($datas, true))){
		// set response code - 400 BAD REQUEST
		http_response_code(400); 
		// show error
		echo json_encode(['message' => 'Bad request. Datas unavailable.', 'records' => array()]);
		die();
	}
	
	if(isset($datas['parent_id']) && !empty($datas['parent_id'])){
		$datas['parent_id'] = $objBranch->escape_bdd($datas['parent_id']);
		$res = $objBranch->read($datas['parent_id']);
		$res = $objBranch->fetchAll($res);
		
		if(empty($res)){
			// set response code - 404 NOT FOUND
			http_response_code(404); 
			// show error
			echo json_encode(['message' => 'No branches found for "parent_id".', 'records' => array()]);
			die();
		}
		
		$branchParent = $res[0];
		$newBranch['parent_id'] = $branchParent['id'];
	}
	$newBranch['matpath'] = '"undefined"';
	
	$objBranch->dbSql->exec('BEGIN;');		
	$query = 'INSERT INTO '.$objBranch->table_name.' ('.implode(',', array_keys($newBranch)).') VALUES ('.implode(',', array_values($newBranch)).')';
	$res = $objBranch->dbSql->query($query);
	if(!$res){
		// set response code - 409 Conflict
		http_response_code(409);
		echo json_encode(['message' => 'Sql Error. Unable to insert the new branch.', 'records' => array()]);
	}
	
	$newBranchId = $objBranch->dbSql->lastInsertRowid();
	$query = 'UPDATE '.$objBranch->table_name.' SET matpath = "'.(empty($branchParent) ?$objBranch->getIDConvert($newBranchId) :$branchParent['matpath'].$objBranch->getIDConvert($newBranchId)).'" WHERE id = '.$newBranchId;
	$res = $objBranch->dbSql->query($query);
	if(!$res){
		$objBranch->dbSql->exec('ROLLBACK;');
		// set response code - 409 Conflict
		http_response_code(409);
		echo json_encode(['message' => 'Sql Error. Unable to update the new branch matpath.', 'records' => array()]);
		die();
	}			
	else
		$objBranch->dbSql->exec('COMMIT;');
	
	// set response code - 201 OK, created
	http_response_code(201); 
	// show success
	echo json_encode(['message' => 'Branch created', 'records' => ['id' => $newBranchId]]);
}else{
	// set response code - 405 Method not allowed
	http_response_code(405); 
	// show error
	echo json_encode(['message' => 'Method not allowed', 'records' => array()]);
}
?>
