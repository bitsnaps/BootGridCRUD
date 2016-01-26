<?php

date_default_timezone_set('UTC');
define('EMAIL_CONTACT', 'bitsnaps@yahoo.fr');
define('AUTHOR', 'bitSnaps');
define('DEBUG_MODE', true);

//alternative to json_encode for PHP < 5.4 (fix utf8 encoding issue)
function raw_json_encode($input) {
    return preg_replace_callback(
        '/\\\\u([0-9a-zA-Z]{4})/',
        function ($matches) {
            return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
        },
        json_encode($input)
    );
}

require_once __DIR__.'/phpar/ActiveRecord.php';

if (DEBUG_MODE){
    $db_host      = "127.0.0.1";
    $db_user      = "root";
    $db_pass      = "";
    $db_name    = "mysql";
} else {
    $db_host      = "your.hosting.com";
    $db_user      = "your_db_user";
    $db_pass      = "your_db_password";
    $db_name    = "your_db_name";
}

//setup connections
$connections = array(
	'development' => "mysql://$db_user:$db_pass@$db_host/$db_name;charset=utf8",
	'production' => "mysql://$db_user:$db_pass@$db_host/$db_name;charset=utf8"
);

//initialize ActiveRecord
ActiveRecord\Config::initialize(function($cfg) use ($connections)
{
    $cfg->set_model_directory('.');
    $cfg->set_connections($connections);
    $cfg->set_default_connection('production');
});
//Abstract Model
abstract class BaseModel extends ActiveRecord\Model {
	
	//explicit connection ([production|development])
	//static $connection = 'production';
	
	//explicit database name
	// static $db = 'my_database_name';
    
    private $searchField;
    private $primaryKey;
    private $labels = array();
    private $fields = array();
    
    public static $no_result = '{
			"current": 0,
			"rowCount": 0,
		"rows":[]
		,
			"total": 0
		}';

    public function getJson($id = '1'){
        $searchPhrase = (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase']))?$_POST['searchPhrase']:'';

        //get query parameters    
        $sort = (isset($_POST['sort']) && !empty($_POST['sort']))?$_POST['sort']:array();
        $rowCount = (isset($_POST['rowCount']) && !empty($_POST['rowCount']))?$_POST['rowCount']:0;
        $current = (isset($_POST['current']) && !empty($_POST['current']))?$_POST['current']-1:0;

            //order by clause
            $orderClause = '';
            $i = 0;
            if (count($sort) > 0){
                foreach ($sort as $field => $order)
                    $orderClause.= ($i++>0?',':'').$field.' '.$order;
            }

            //filter condition
            $conditions = '';
            if (!empty($searchPhrase)){
                $conditions.=$this->getSearchField().' LIKE "%'.addslashes($searchPhrase).'%"';
            }

        $parameters = array(
        //		'joins'=>array('servers'), //not yet supported
                'select'=>empty($this->fields)?'*':join($this->fields,','),
                'conditions'=>array($this->getPK().' >= ?'.(empty($conditions)?'':' and ('.$conditions.')'), $id),
                'order'=>$orderClause
                );
        $allRecords = $this::all($parameters);

        if ($rowCount > 0){
            $parameters['limit']=$rowCount;
            $parameters['offset']=$current*$rowCount;
        }

        $records = $this::all($parameters);

        $totalRows = count($allRecords);
        $rows = array();
        foreach ($records as $s)	
            array_push($rows, $s->attributes());	

        return '{
                    "current": '.($current+1).',
                    "rowCount": '.$rowCount.',
                "rows":'.( raw_json_encode($rows) ).',
                    "total": '.$totalRows.'
                }';
    } //getJson()
    
    public function getName(){
        return get_class($this);
    }
    protected function prettyName($value){
        return ucwords(str_replace('_',' ',$value));
    }
    public function setLabels($l){
        foreach ($l as $field => $label){
            if (is_int($field))
                $this->labels[$label] = $this->prettyName($label);
            else
                $this->labels[$field] = ($label == '*') ?$this->prettyName($field).'*':$label;
        } //foreach
        $this->fields = array_keys($this->labels);
    }
    private function fillFields(){
        $attributes = array();
        foreach ($this->getAttributes() as $k => $v)        
            $attributes[$k] = $this->prettyName($k);
        $this->setLabels($attributes);
    }
	
    public function getLabels($withoutOptions = false){
        if (count($this->labels) == 0){
            $this->fillFields();
        }
        $l = array();
        if ($withoutOptions){
            foreach ($this->labels as $field => $label){
                $l[$field] = str_replace('*', '', $label);
            }
            return $l;
        }
        return $this->labels;
    }
	
    public function getFields(){
        return $this->fields;
    }
	
    public function setSearchField($field_name){
        $this->searchField = $field_name;
    }
	
    public function getSearchField(){
        if (empty($this->searchField)){
            $this->searchField = $this->getFirstField();
        }
        return $this->searchField;
    }
	
    public function getAttributes(){
        return $this->attributes();
    }
	
    public function setPK($pk){
        $this->primaryKey = $pk;
    }
	
    public function getPK(){
        if (empty($this->primaryKey)){
            $this->primaryKey = $this->getFirstField();
        }
        return $this->primaryKey;
    }
	
    protected function getFirstField(){
        $f = $this->labels;
        reset($f);
        return key($f);
    }
    
    public function update($values){
        $model = $this::find($values[$this->getPK()]);
        if (!empty($model)){
            foreach ($model->attributes() as $k => $v){
                //you could also use property_exists() to check in different way
                if (array_key_exists($k, $values))
                    $model->$k = htmlentities($values[$k], ENT_COMPAT, 'UTF-8'); //need some security enhancements...
            }
            return $model;
        }
    }
    
    public function insert($values){
        $model = new $this();
        foreach ($model->attributes() as $k => $v){
            if (array_key_exists($k, $values))
                $model->$k = htmlentities($values[$k], ENT_COMPAT, 'UTF-8'); //need some security enhancements...
        }
        return $model;
    }
    
    public function remove($rid){
        $model = $this::find($rid);
        $model->delete();
    }
    
} //BaseModel

/* Models */
class User extends BaseModel{
	//explicit table name
	 static $table_name = 'user';
} //User



?>
