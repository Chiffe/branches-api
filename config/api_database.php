<?php
class MyDB extends SQLite3 {
	
    public function __construct() {
        $this->open($_SERVER['DOCUMENT_ROOT'].Config::PATH_TO_SQLITE_FILE);
		if(!$this->createTables())
			die('Database error - Tables');
		else if(!$this->dumpTables())
			die('Database error - Dump tables');
    }
	
	private function createTables(){
		$commands = ['CREATE TABLE IF NOT EXISTS branches (
						id INTEGER PRIMARY KEY NOT NULL,
						name  VARCHAR (255) NOT NULL,
						data_1 TEXT,
						data_2 TEXT,
						data_3 TEXT,
						parent_id INT NULL,
						matpath VARCHAR (255) NOT NULL,
						FOREIGN KEY (parent_id) REFERENCES branches(id)
					)',
					'CREATE INDEX IF NOT EXISTS idx_branches_parent_id ON branches (parent_id)',
					'CREATE INDEX IF NOT EXISTS idx_branches_matpath ON branches (matpath)'
					];
        // execute the sql commands to create new tables
        foreach ($commands as $command) {
            if(!$this->exec($command))
				return false;
        }
		return true;
	}
	
	private function dumpTables(){
		//Count number of rows
		$numRows = $this->query('SELECT count(*) FROM branches')->fetchArray();
		//Empty table
		if($numRows[0] == 0){
			$datas = 'INSERT INTO branches (name, data_1, data_2,data_3, parent_id, matpath) VALUES
						("Branch 1","data 1","data 2","data 3", null, "11"),
						("Branch 2","data 1","data 2","data 3", null, "12"),
						("Branch 3","data 1","data 2","data 3", null, "13"),
						("Branch 4","data 1","data 2","data 3", null, "14"),
						("Branch 5","data 1","data 2","data 3", 1, "1115"),
						("Branch 6","data 1","data 2","data 3", 1, "1116"),
						("Branch 7","data 1","data 2","data 3", 1, "1117"),
						("Branch 8","data 1","data 2","data 3", 2, "1218"),
						("Branch 9","data 1","data 2","data 3", 3, "1319"),
						("Branch 10","data 1","data 2","data 3", 5, "11151a"),
						("Branch 11","data 1","data 2","data 3", 5, "11151b"),
						("Branch 12","data 1","data 2","data 3", 6, "11161c"),
						("Branch 13","data 1","data 2","data 3", 7, "11171d"),
						("Branch 14","data 1","data 2","data 3", 8, "12181e"),
						("Branch 15","data 1","data 2","data 3", 8, "12181f"),
						("Branch 16","data 1","data 2","data 3", 8, "12181g"),
						("Branch 17","data 1","data 2","data 3", 9, "13191h"),
						("Branch 18","data 1","data 2","data 3", 9, "13191i"),
						("Branch 19","data 1","data 2","data 3", 9, "13191j"),
						("Branch 20","data 1","data 2","data 3", 9, "13191k"),
						("Branch 21","data 1","data 2","data 3", 10, "11151a1l"),
						("Branch 22","data 1","data 2","data 3", 11, "11151b1m"),
						("Branch 23","data 1","data 2","data 3", 12, "11161c1n"),
						("Branch 24","data 1","data 2","data 3", 13, "11171d1o"),
						("Branch 25","data 1","data 2","data 3", 13, "11171d1p"),
						("Branch 26","data 1","data 2","data 3", 15, "12181f1q"),
						("Branch 27","data 1","data 2","data 3", 16, "12181g1r"),
						("Branch 28","data 1","data 2","data 3", 23, "11161c1n1s"),
						("Branch 29","data 1","data 2","data 3", 26, "12181f1q1t"),
						("Branch 30","data 1","data 2","data 3", 29, "12181f1q1t1u"),
						("Branch 31","data 1","data 2","data 3", 29, "12181f1q1t1v")'
			;
			return $this->exec($datas);
		}
		return true;
		
		$rows = $this->query('SELECT * FROM branches');
		while ($row = $rows->fetchArray(SQLITE3_ASSOC)){
			var_dump($row);
		}	
	}	
}
?>
