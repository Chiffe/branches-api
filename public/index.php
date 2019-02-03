<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;

require '../vendor/autoload.php';
require '../config/settings.php';
require '../database/Connect.php';
require '../model/Branch.php';


$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();
$container['db'] = new Connect($container);


$app->group('/branches', function ($app) {
	
	/**
	*
	* 			VIEW ALL BRANCHES
	*
	*/
	$app->get('', function ($request, $response, $args){
		$objBranch = new Branch();
		//Get the branches and transform them to array
		$branches = $objBranch->getAll()->get()->toArray();
		//Create the tree
		$branches = $objBranch->fetchItems($branches);
		
		//If no branches
		if(empty($branches))
			return $response->withJson(['message' => 'No branches found.', 'records' => []], 404);
		else
			return $response->withJson(['message' => '', 'records' => $branches]);
	});
	
	/**
	*
	* 			CREATE ONE BRANCH
	*
	*/
	$app->post('', function ($request, $response, $args){
		$datas = $request->getParsedBody();
		//If "datas" is unavailable
		if(empty($datas) || !is_array($datas))
			return $response->withJson(['message' => 'Bad request. Datas unavailable.', 'records' => []], 400);
		
		// Create new model with datas
		$objBranch = new Branch($datas);
		//remove the useless datas
		$objBranch->filterData();
		
		//We set up manually the field "parent_id"
		if(isset($datas['parent_id']) && !empty($datas['parent_id']))
			$objBranch->parent_id = $datas['parent_id'];
		
		//We save the new model
		if($objBranch->save())
			return $response->withJson(['message' => 'Branch created.', 'records' => ['id' => $objBranch->id]], 201);
		else
			return $response->withJson(['message' => 'Unable to insert the new branch.', 'records' => []], 409);
	});
	
	/**
	*
	* 			MOVE A BRANCH
	*
	*/
	$app->patch('/move/{id:[0-9]+}', function ($request, $response, $args){
		$datas = $request->getParsedBody();
		//Find the model for this 'id'
		$objBranch = Branch::find($args['id']);
		//Branch not found
		if($objBranch === null)
			return $response->withJson(['message' => 'No branch found.', 'records' => []], 404);
		
		//Set by default
		$idNewParent = null;
		//If id of new parent from client is OK
		if(isset($datas['id_new_parent']) && is_numeric($datas['id_new_parent']))
			$idNewParent = $datas['id_new_parent'];
		
		//Get all the family of the branch
		$branches = $objBranch->getFamily($objBranch->matpath)->get();
		
		//Set the parentMatpath
		$parentMatpath = '';
		//If the branch doesn't move on the top of the tree
		if($idNewParent !== null){
			//Get the matpath of branch parent
			$branchParent = Branch::find($idNewParent, ['matpath']);
			//if branch parent doesn't exist
			if($branchParent === null)
				return $response->withJson(['message' => 'Branch parent doesn\'t exist.', 'records' => []], 404);
			
			$parentMatpath = $branchParent->matpath;
		}
		
		//If the branch is already a child of branch parent
		if($objBranch->parent_id == $idNewParent)
			return $response->withJson(['message' => 'No treatments required.', 'records' => []]);
		
		//Index by id
		$branchesArray = array_column($branches->toArray(), null, 'id');
		
		DB::transaction(function() use ($branches, $args, $idNewParent, $parentMatpath){
			foreach ($branches as $branch) {
				//The first branch that the customer move
				if($branch->id == $args['id']){
					//Affect the new parent_id
					$branch->parent_id = $idNewParent;
					//Affect the new matpath
					$branch->matpath = $branchesArray[$branch->id]['matpath'] = $parentMatpath.$branch->getIdConvert(true);
					//Update the model
					$res = $branch->save();
					
					
					if(!$res)
						return $response->withJson(['message' => 'Unable to move the branch.', 'records' => []], 409);
					
					continue;
				}		
				
				//Affect the new matpath
				$branch->matpath = $branchesArray[$branch->id]['matpath'] = $branchesArray[$branch->parent_id]['matpath'].$branch->getIdConvert(true);
				$res = $branch->save();
				
				if(!$res)
					return $response->withJson(['message' => 'Unable to move the branch.', 'records' => []], 409);
			}
		});
		
		return $response->withJson(['message' => 'Branch has been moved.', 'records' => []]);
	});
	
	$app->group('/{id:[0-9]+}', function ($app){
		
		/**
		*
		* 			VIEW ONE BRANCH
		*
		*/
		$app->get('', function ($request, $response, $args){
			//Get the data request
			$datas = $request->getParsedBody();
			//New object model
			$objBranch = new Branch();
			//Set the $branches var
			$branches = array();
			
			//If the var children exist and is true
			if(isset($datas['children']) && is_bool($datas['children']) && $datas['children']){
				//We get all the family of the branch
				$branches = $objBranch->children($args['id']);
				//If false, the branch doesn't exist
				if(!$branches)
					return $response->withJson(['message' => 'No branches found.', 'records' => []], 404);
				
				//Create the tree
				$branches = $objBranch->fetchItems($branches);
			}else{
				//Get the branch
				$branches = $objBranch->find($args['id']);
				//If false, the branch doesn't exist
				if(!$branches)
					return $response->withJson(['message' => 'No branches found.', 'records' => []], 404);
				
				$branches = $branches->toArray();
			}
			
			return $response->withJson(['message' => '', 'records' => $branches]);
		});
		
		/**
		*
		* 			UPDATE ONE BRANCH
		*
		*/
		$app->put('', function ($request, $response, $args){
			$datas = $request->getParsedBody();
			//If "datas" is unavailable
			if(empty($datas) || !is_array($datas))
				return $response->withJson(['message' => 'Bad request. Datas unavailable.', 'records' => []], 400);
			// Find the model for this 'id'
			$objBranch = Branch::find($args['id']);
			//Branch not found
			if($objBranch === null)
					return $response->withJson(['message' => 'No branch found.', 'records' => []], 404);
			
			//Set the attributes from user's datas
			$objBranch->setRawAttributes($datas);
			//remove the useless datas
			$objBranch->filterData();
			
			//We update the model
			if($objBranch->update())
				return $response->withJson(['message' => 'Branch updated.', 'records' => []]);
			else
				return $response->withJson(['message' => 'Unable to update the branch.', 'records' => []], 409);
		});
		
		/**
		*
		* 			DELETE BRANCH
		*
		*/
		$app->delete('', function ($request, $response, $args){
			// Find the model for this 'id'
			$objBranch = Branch::find($args['id']);
			//Branch not found
			if($objBranch === null)
					return $response->withJson(['message' => 'No branch found. No record deleted.', 'records' => []], 404);
			
			$deletedRows = Branch::where('matpath', 'like', $objBranch->matpath.'%')->delete();
			
			return $response->withJson(['message' => $deletedRows.' Branch(es) deleted.', 'records' => []]);
		});		
	});
});

$app->run();
?>
