<?php
class Branch{
 
    // database object and table name
    public $dbSql;
    public $table_name = "branches";
 
    // object properties
    public $id;
    public $name;
    public $data_1;
    public $data_2;
    public $data_3;
    public $parent_id;
    public $matpath;
	
	private $fields = [
			'name'		=> ['required' => true, 'numeric' => false],
			'data_1' 	=> ['required' => false, 'numeric' => false],
			'data_2' 	=> ['required' => false, 'numeric' => false],
			'data_3' 	=> ['required' => false, 'numeric' => false]
		];
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->dbSql = $db;
    }
		
	public function read($id = null, $children = false){
		$query = 'SELECT * FROM '.$this->table_name.' ORDER BY matpath';
		
		if($id != null && is_numeric($id)){
			if($children){
				$matpath = $this->getMatpath($id);
				if($matpath === false || empty($matpath))
					$query = 'SELECT * FROM '.$this->table_name.' WHERE id = '.$id;
				else
					$query = 'SELECT * FROM '.$this->table_name.' WHERE matpath LIKE "'.$matpath.'%" ORDER BY matpath';
			}else
				$query = 'SELECT * FROM '.$this->table_name.' WHERE id = '.$id;
		}
				
		return $this->dbSql->query($query);
	}
	
	public function getIDConvert($id){
		if(isset($id) && !empty($id) && is_numeric($id)){
			$idConvert = base_convert($id, 10, 36);
			return base_convert(strlen($idConvert), 10, 36).$idConvert;			
		}
		return false;
	}
	
	public function fetchAll($res){
		$items = array();
		
		while ($row = $res->fetchArray(SQLITE3_ASSOC))
			array_push($items, $row);
		
		return $items;		
	}
	
	public function getMatpath($id){
		if(!isset($id) || empty($id) || !is_integer($id))
			return '';
		
		$query = 'SELECT matpath FROM '.$this->table_name.' WHERE id = '.$id;
		
		$res = $this->dbSql->query($query);
		$row = $res->fetchArray(SQLITE3_ASSOC);
		
		if($row === false)
			return false;
		else if(empty($row))
			return '';
		
		return $row['matpath'];
	}
	
	public function escape_bdd($string){
		if(is_numeric($string))
			$string = intval($string);
		else{
			$string = $this->dbSql->escapeString($string);
		}
		
		return $string;
	}
	
	public function filterData($datas, $mode_create = false){
		$dataFilter = array();
		
		foreach($this->fields as $field => $params){
			if(isset($datas[$field])){
				if($params['required'] && empty($datas[$field]))
					return false;
				
				$dataFilter[$field] = ($params['numeric'] ?$this->escape_bdd($datas[$field]):'"'.$this->escape_bdd($datas[$field]).'"');
			}else if($mode_create)
				if($params['required'])
					return false;
		}
		
		return $dataFilter;
	}
	
	public function fetchItems($rows, $parentId = null){		
		$ni = count($rows);
		if($ni === 0)
			return array();
		else if ($ni === 1)
			return $rows;
		else if ($parentId === null && $rows[0]['parent_id'] !== null)
			$parentId = $rows[0]['parent_id'];
		
		// branches array
		$items = array();
		for($i=0; $i < $ni; $i++){
			if($rows[$i]['parent_id'] == $parentId){
				$rows[$i]['children'] = $this->fetchItems($rows, $rows[$i]['id']);
				array_push($items, $rows[$i]);
			}
		}
		return $items;
	}
}
?>
