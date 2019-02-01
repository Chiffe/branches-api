# Branch API - Brighte Capital
## Introduction
The API allows you to manage branches.


## Show branches  
Return json data with all branches
* **URL**  

    /branches/
    
* **METHOD**  

    `GET`

* **URL Params**  

    None

* **Data Params**  

    None
    
* **Success response**  

    * **Code** : 200  
    **Content** : `{"message" : "", "records" : [array]}`
    
* **Error response**  

    * **Code** : 404  
    **Content** : `{"message" : "No branches found.", "records" : [array]}`
    
## Show one branch  
Return json data about a one branch and or not his children
* **URL**  

    /branches/:id
    
* **METHOD**  

    `GET`

* **URL Params**  

    **Required**:  
    
    `id=[integer]`

* **Data Params**  

    **Optional**:  
    
    `children=[boolean]` *default*: **false**
    
* **Success response**  

    * **Code** : 200  
    **Content** : `{"message" : "", "records" : [array]}`
    
* **Error response**  

    * **Code** : 404  
    **Content** : `{"message" : "No branches found.", "records" : [array]}`
    
## Create a branch  
Create a branch
* **URL**  

    /branches/
    
* **METHOD**  

    `POST`

* **URL Params**  

    None

* **Data Params**  

    `name=[string]` **required**  
    `data_1=[string | null]`   
    `data_2=[string | null]`   
    `data_3=[string | null]`   
    `parent_id=[integer | null]`
    
* **Success response**  

    * **Code** : 201  
    **Content** : `{"message" : "Branch created", "records" : {"id" : [integer]}}`
    
* **Error response**  

    * **Code** : 409  
    **Content** : `{"message" : "Unable to insert the new branch.", "records" : [array]}`
    
## Update a branch  
Update a branch. To change "parent_id" use **Move a branch**
* **URL**  

    /branches/:id
    
* **METHOD**  

    `PUT`

* **URL Params**  

    **Required**:  
    
    `id=[integer]`

* **Data Params**  

    `name=[string]`   
    `data_1=[string | null]`   
    `data_2=[string | null]`   
    `data_3=[string | null]`
    
* **Success response**  

    * **Code** : 200  
    **Content** : `{"message" : "Branch updated", "records" : [array]}`
    
* **Error response**  

    * **Code** : 400  
    **Content** : `{'message' : 'Bad request. Datas unavailable.', 'records' : [array]}`
    
    OR
    
    * **Code** : 404  
    **Content** : `{'message' : 'No branches found.', 'records' : [array]}`
    
    OR
    
    * **Code** : 409  
    **Content** : `{'message' : 'Error. Unable to update the branch. See the datas sent.', 'records' : [array]}`
    
    OR
    
    * **Code** : 409  
    **Content** : `{'message' : 'Sql Error. Unable to update the branch.', 'records' : [array]}`
    
## Move a branch  
Move a branch and all his children
* **URL**  

    /branches/move/:id
    
* **METHOD**  

    `PATCH`

* **URL Params**  

    **Required**:  
    
    `id=[integer]`

* **Data Params**  

    `id_new_parent=[integer | null]` default: **NULL**   
    
    Use `id_new_parent=null` for move a branch on the top of the tree
    
* **Success response**  

    * **Code** : 200  
    **Content** : `{'message' : 'Branch has been moved', 'records' : [array]}`
    
    OR
    
    * **Code** : 200  
    **Content** : `{'message' : 'No treatments required.', 'records' : [array]}`
    
* **Error response**  

    * **Code** : 404  
    **Content** : `{'message' : 'Error with parent branch.', 'records' : [array]}`
    
    OR
    
    * **Code** : 404  
    **Content** : `{'message' : 'No branches found.', 'records' : [array]}`
    
    OR
    
    * **Code** : 409  
    **Content** : `{'message' : 'Sql Error. Unable to move the branch.', 'records' : [array]}`
    
## Delete a branch  
Delete a branch and all his children
* **URL**  

    /branches/:id
    
* **METHOD**  

    `DELETE`

* **URL Params**  

    **Required**:  
    
    `id=[integer]`

* **Data Params**  

    None
    
* **Success response**  

    * **Code** : 200  
    **Content** : `{'message' : 'Branches deleted', 'records' : [array]}`
    
* **Error response**  

    * **Code** : 404  
    **Content** : `{'message' : 'No branches found. No record deletes.', 'records' : [array]}`
    
    OR
    
    * **Code** : 404  
    **Content** : `{'message' : 'No branches found.', 'records' : [array]}`
