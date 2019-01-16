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

if($method === 'PATCH'){
	//decode json from client
	$data = json_decode(file_get_contents('php://input'), true);
	$idBranchToMove = $objBranch->escape_bdd($_GET['id']);
	$idNewParent = null;
	//if id of new parent from client is OK so we stock it
	if(isset($data['id_new_parent']) && is_numeric($data['id_new_parent']))
		$idNewParent = $objBranch->escape_bdd($data['id_new_parent']);
	//The branchTomove's matpath 
	$moveMatpath = $objBranch->getMatpath($idBranchToMove);
	//if the branch doesn't exist
	if($moveMatpath === false || empty($moveMatpath)){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'No branches found.', 'records' => array()]);
		die();
	}
	//We get the branch and its children
	$query = 'SELECT id, parent_id, matpath FROM '.$objBranch->table_name.' WHERE matpath LIKE "'.$moveMatpath.'%" ORDER BY matpath';
	$res = $objBranch->dbSql->query($query);
	$rows = $objBranch->fetchAll($res);
	//The branch parent matpath
	$parentMatpath = $objBranch->getMatpath($idNewParent);
		
	if($parentMatpath === false){
		// set response code - 404 NOT FOUND
		http_response_code(404); 
		// show error
		echo json_encode(['message' => 'Error with parent branch.', 'records' => array()]);
		die();
	}
	//New index by id
	$rows = array_column($rows, null, 'id');
	$valuesQuery = array();
	//if the branchTomove is already a child of newParent
	if($rows[$idBranchToMove]['parent_id'] == $idNewParent){
		// set response code - 200 no modifications to do
		http_response_code(200); 
		// show error
		echo json_encode(['message' => 'No treatments required.', 'records' => array()]);
		die();
	}
	
	foreach($rows as $row){
		//if it's the roof of the tree that is being moved
		if($row['id'] == $idBranchToMove){
			//New parent_id and new matpath
			$rows[$idBranchToMove]['parent_id'] = (empty($idNewParent) ?"null":$idNewParent);
			$rows[$idBranchToMove]['matpath'] = $parentMatpath.$objBranch->getIDConvert($idBranchToMove);
			//We prepare for the sql query after
			array_push($valuesQuery, '('.$idBranchToMove.', '.$rows[$idBranchToMove]['parent_id'].', "'.$rows[$idBranchToMove]['matpath'].'")');
			continue;
		}
		//we redefine the matpath of children
		$rows[$row['id']]['matpath'] = $rows[$row['parent_id']]['matpath'].$objBranch->getIDConvert($row['id']);
		array_push($valuesQuery, '('.$row['id'].', '.$row['parent_id'].', "'.$rows[$row['id']]['matpath'].'")');
	}
	
	$valuesQuery = implode(',', $valuesQuery);
	
	$query = 'WITH UpdateValues(id, parent_id, matpath) AS (VALUES'.$valuesQuery.')'.
		'UPDATE '.$objBranch->table_name.' SET parent_id = (SELECT parent_id FROM UpdateValues WHERE '.$objBranch->table_name.'.id = UpdateValues.id),'.
		'matpath = (SELECT matpath FROM UpdateValues WHERE '.$objBranch->table_name.'.id = UpdateValues.id)'.
		'WHERE id IN (SELECT id FROM UpdateValues)';
		
	if($objBranch->dbSql->exec($query)){
		// set response code - 200 OK
		http_response_code(200);
		echo json_encode(['message' => 'Branch has been moved.', 'records' => array()]);
	}else{
		// set response code - 409 Conflict
		http_response_code(409);
		echo json_encode(['message' => 'Error Sqlite. Unable to move the branch.', 'records' => array()]);
	}
}else{
	// set response code - 405 Method not allowed
	http_response_code(405); 
	// show error
	echo json_encode(['message' => 'Method not allowed', 'records' => array()]);
}
?>
