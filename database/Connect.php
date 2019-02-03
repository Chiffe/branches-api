<?php

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Connect
{
	
	public function __construct($container){
        if(!isset($container['settings']['db']))
			return $container;
		else{
			try {
				$pdo = new PDO("sqlite:../database/api_db.sqlite3");
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				
				$res = $pdo->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='".$container['settings']['table_name']."'")->fetch();
				
				if($res['count(*)'] == 0){
					$commands = ['CREATE TABLE IF NOT EXISTS '.$container['settings']['table_name'].' (
								id INTEGER PRIMARY KEY NOT NULL,
								name  VARCHAR (255) NOT NULL,
								data_1 TEXT,
								data_2 TEXT,
								data_3 TEXT,
								parent_id INT NULL,
								matpath VARCHAR (255) NOT NULL,
								FOREIGN KEY (parent_id) REFERENCES '.$container['settings']['table_name'].'(id)
							)',
							'CREATE INDEX IF NOT EXISTS idx_'.$container['settings']['table_name'].'_parent_id ON '.$container['settings']['table_name'].' (parent_id)',
							'CREATE INDEX IF NOT EXISTS idx_'.$container['settings']['table_name'].'_matpath ON '.$container['settings']['table_name'].' (matpath)'
							];
					// execute the sql commands to create new tables
					foreach ($commands as $command)
						$pdo->exec($command);
						
					$branches = [
						['name' => 'Branch 1', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => null, 'matpath' => '11'],
						['name' => 'Branch 2', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => null, 'matpath' => '12'],
						['name' => 'Branch 3', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => null, 'matpath' => '13'],
						['name' => 'Branch 4', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => null, 'matpath' => '14'],
						['name' => 'Branch 5', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 1, 'matpath' => '1115'],
						['name' => 'Branch 6', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 1, 'matpath' => '1116'],
						['name' => 'Branch 7', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 1, 'matpath' => '1117'],
						['name' => 'Branch 8', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 2, 'matpath' => '1218'],
						['name' => 'Branch 9', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 3, 'matpath' => '1319'],
						['name' => 'Branch 10', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 5, 'matpath' => '11151a'],
						['name' => 'Branch 11', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 5, 'matpath' => '11151b'],
						['name' => 'Branch 12', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 6, 'matpath' => '11161c'],
						['name' => 'Branch 13', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 7, 'matpath' => '11171d'],
						['name' => 'Branch 14', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 8, 'matpath' => '12181e'],
						['name' => 'Branch 15', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 8, 'matpath' => '12181f'],
						['name' => 'Branch 16', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 8, 'matpath' => '12181g'],
						['name' => 'Branch 17', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 9, 'matpath' => '13191h'],
						['name' => 'Branch 18', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 9, 'matpath' => '13191i'],
						['name' => 'Branch 19', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 9, 'matpath' => '13191j'],
						['name' => 'Branch 20', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 9, 'matpath' => '13191k'],
						['name' => 'Branch 21', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 10, 'matpath' => '11151a1l'],
						['name' => 'Branch 22', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 11, 'matpath' => '11151b1m'],
						['name' => 'Branch 23', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 12, 'matpath' => '11161c1n'],
						['name' => 'Branch 24', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 13, 'matpath' => '11171d1o'],
						['name' => 'Branch 25', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 13, 'matpath' => '11171d1p'],
						['name' => 'Branch 26', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 15, 'matpath' => '12181f1q'],
						['name' => 'Branch 27', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 16, 'matpath' => '12181g1r'],
						['name' => 'Branch 28', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 23, 'matpath' => '11161c1n1s'],
						['name' => 'Branch 29', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 26, 'matpath' => '12181f1q1t'],
						['name' => 'Branch 30', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 29, 'matpath' => '12181f1q1t1u'],
						['name' => 'Branch 31', 'data_1' => 'data 1', 'data_2' => 'data 2', 'data_3' => 'data 3', 'parent_id' => 29, 'matpath' => '12181f1q1t1v']
					];
					
					// Prepare INSERT statement to SQLite3 file db
					$insert = 'INSERT INTO '.$container['settings']['table_name'].' (name, data_1, data_2,data_3, parent_id, matpath) 
								VALUES (:name, :data_1, :data_2, :data_3, :parent_id, :matpath)';
					$stmt = $pdo->prepare($insert);
				
					// Bind parameters to statement variables
					$stmt->bindParam(':name', $name);
					$stmt->bindParam(':data_1', $data_1);
					$stmt->bindParam(':data_2', $data_2);
					$stmt->bindParam(':data_3', $data_3);
					$stmt->bindParam(':parent_id', $parent_id);
					$stmt->bindParam(':matpath', $matpath);
				
					// Loop thru all branches and execute prepared insert statement
					foreach ($branches as $b) {
						// Set values to bound variables
						$name = $b['name'];
						$data_1 = $b['data_1'];
						$data_2 = $b['data_2'];
						$data_3 = $b['data_3'];
						$parent_id = $b['parent_id'];
						$matpath = $b['matpath'];
					
						// Execute statement
						$stmt->execute();
					}
				}
				$pdo = null;
				
				$capsule = new \Illuminate\Database\Capsule\Manager;
				$capsule->addConnection($container['settings']['db']);
				
				// Set the event dispatcher used by Eloquent models
				$capsule->setEventDispatcher(new Dispatcher(new Container));
			
				$capsule->setAsGlobal();
				$capsule->bootEloquent();
			
				return $capsule;
			}
			catch(PDOException $e) {
				// Print PDOException message
				echo $e->getMessage();
			}
		}
    }	
}
?>
