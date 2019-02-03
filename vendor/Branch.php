<?php

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
	
	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
	
	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'parent_id', 'matpath'];
	
	/**
	* The attributes that only accept in the request
	*
	*/
	private $fields = ['name', 'data_1', 'data_2', 'data_3'];
	
	/**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'parent_id' => 'integer',
        'matpath' => 'string'
    ];
	
	/**
     * Get the children of the branch
     */
    public function children($id){
		//Get the matpath of the parent branch
		$branch = $this->find($id, ['matpath']);
		
		//If the branch is not found
		if(empty($branch))
			return false;
		
		//We return all the family of this parent
		return $branch->getFamily($branch->matpath)->get()->toArray();
    }
	
	public static function boot()
    {
        parent::boot();

        // Setup event bindings...
        Branch::saving(function($branch){
			//If it's not a creation, the branch already exist
			if($branch->getOriginal('id') !== null){
				//If the fields "matpath" or "name" set to null
				if((isset($branch->matpath) && empty($branch->matpath)) || (isset($branch->name) && empty($branch->name)))
					return false;
			}
			else{ //If it's a creation
				//The fields "matpath" and "name" cannot be empty during the creation
				if(!isset($branch->name) || empty($branch->name))
					return false;
				if(!isset($branch->matpath) || empty($branch->matpath))
					$branch->matpath = 'undefined';				
			}
        });
		
		Branch::created(function($branch){
			//Get the id convert of the new branch
			$branch->getIdConvert();
			
			$matpath = $branch->id_convert;
			
			//If the branch has a parent, we need the parent's matpath
			if(isset($branch->parent_id)){
				//Get the parent's matpath
				$branchParent = self::find($branch->parent_id, ['matpath']);
				$matpath = $branchParent->matpath.$matpath;
			}
			
			$branch->filterData(true);
			$branch->matpath = $matpath;
			$branch->save();
        });
    }
	
	/**
     * Set the branch's parent_id.
     *
     * @param  integer  $value
     * @return integer
     */
    public function setParentIdAttribute($value){
		//if branch parent doesn't exist
		if($this->find($value) === null)
			$value = null;
		
        $this->attributes['parent_id'] = $value;
    }
	
	/**
	* Convert id to baseConvert 36
	*
	* @param  boolean  $return Must return the id
    * @return string|null
	*
	*/
	public function getIdConvert($return = false){
		//If "id" exist in the model
		if(isset($this->id) && !empty($this->id)){
			//Convert the id
			$idConvert = base_convert($this->id, 10, 36);
			//Convert the length of the id convert and mix them
			$idConvert = base_convert(strlen($idConvert), 10, 36).$idConvert;
			
			//Return only the value
			if($return)
				return $idConvert;
			
			//else set a new attribute
			$this->attributes['id_convert'] = base_convert(strlen($idConvert), 10, 36).$idConvert;	
		}
	}
	
	/**
	* Filter the user's data. Allow to get a model clean
	*
	*@param boolean $keepAll Allow to keep the guarded fields
	*
	*/
	public function filterData($keepAll = false){
		//Get the attributes
		$attrs = $this->getAttributes();
		//Key of the attributes
		$fieldsData = array_keys($attrs);
		
		//Comparaion fields
		$haystack = $this->fields;
		//If we need to keep all the fields
		if($keepAll)
			$haystack = array_merge($this->fields, $this->guarded);
		
		//if the field doesn't match with "fields" so unset it
		foreach($fieldsData as $field){
		if(!in_array($field, $haystack))
				$this->__unset($field);
		}
	}
	
	
   /**
     * Scope a query to only include all banches of the same family (matpath).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGetFamily($query, $matpath)
    {
        return $query->where('matpath', 'like', $matpath.'%')
			->orderBy('matpath', 'asc');
    }
	
	/**
     * Scope a query to include all banches.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGetAll($query)
    {
        return $query->orderBy('matpath', 'asc');
    }
	
	
	/**
	*Create the tree
	*
	*@param  array  $rows flat tree
	*@param  integer|null  $parentId ID of parent branch or null if it's top level
	*
	*@return array Array of the tree
	*/
	public function fetchItems($rows, $parentId = null){
		//number of branches
		$ni = count($rows);
		if($ni === 0)
			return array();
		else if ($ni === 1)
			return $rows;
		//If the first branch isn't the top level, we change the var parentId
		else if ($parentId === null && $rows[0]['parent_id'] !== null)
			$parentId = $rows[0]['parent_id'];
		
		// branches array
		$items = array();
		for($i=0; $i < $ni; $i++){
			//If the branch is the children of parent branch
			if($rows[$i]['parent_id'] == $parentId){
				//Get the children of the child branch
				$rows[$i]['children'] = self::fetchItems($rows, $rows[$i]['id']);
				//Add the child branch and all her children
				array_push($items, $rows[$i]);
			}
		}
		return $items;
	}
}
?>
