<?php

namespace Bella;

trait MySQL{
	
	protected static $connection	=	null;
	protected static $server		=	null;
	protected static $db			=	null;
	protected static $user		=	null;
	protected static $pass		=	null;
	
	public static function insert($table, $fields, $values=null){
		$arrFields = [];
		$arrValues = [];
		$_undefined = [];
		if(!!$fields){
			if(!!$values && is_array($fields) && is_array($values) && count($fields)==count($values)){
				foreach($fields as $item){
					array_push($arrFields, $item);
				}
				foreach($values as $item){
					array_push($arrValues, $item);
					array_push($_undefined, '?');
				}
			}
			else{
				foreach($fields as $key=>$value){
					array_push($arrFields, $key);
					array_push($arrValues, $value);
					array_push($_undefined, '?');
				}					
			}
			if(count($arrFields)>0 && count($arrFields)==count($arrValues)){			
				$strField = implode(', ', $arrFields); 
				$strUndefined = implode(', ', $_undefined); 
				$sql = static::build("Insert into $table ($strField) values ($strUndefined)", $arrValues);
				return static::query($sql);
			}
		}
		return false;
	}

	public static function update($table, $fields, $where=false, $condition=false){
		$arrFields = [];
		$arrValues = [];
		if(!!$fields){
			foreach($fields as $key=>$value){
				array_push($arrFields, "$key=?");
				array_push($arrValues, $value);
			}					
			if(count($arrFields)>0 && count($arrFields)==count($arrValues)){			
				$strField = implode(', ', $arrFields); 
				$cond = '';
				if(!!$where){
					$cond = ' where '.static::build($where, $condition);
				}				 
				$sql = static::build("Update $table set $strField", $arrValues);
				$sql.=$cond;
				return static::query($sql);
			}
		}
		return false;
	}
			
	public static function increment($table, $field, $step=1, $where=false, $condition=false){
		$cond = '';
		if(!!$where){
			$cond = ' where '.static::build($where, $condition);
		}
		$sql = "Update $table set $field=$field+$step $cond";
		return static::query($sql);
	}
	public static function decrement($table, $field, $step=1, $where=false, $condition=false){
		$cond = '';
		if(!!$where){
			$cond = ' where '.static::build($where, $condition);
		}
		$sql = "Update $table set $field=$field-$step $cond";
		return static::query($sql);
	}
	
	public static function remove($table, $where=false, $condition=false){
		$sql = "Delete from $table";
		if(!!$where){
			$sql.= ' where '.static::build($where, $condition);
		}
		return static::query($sql);
	}
	
	public static function selectOne($table, $fields=false, $where=false, $condition=false){
		$cond = '';
		if(!!$where && !!$condition){
			$cond = ' where '.static::build($where, $condition);
		}
		if(!!$fields && is_array($fields)){
			$arrFields = implode(', ', $fields);
		}
		else{
			$arrFields = '*';
		}
		$sql = "Select $arrFields from $table $cond";
		
		$result = static::query($sql);
		if(!!$result && is_array($result) && count($result)>0){
			return Bella::oval($result[0]);
		}
		return null;
	}
	
	public static function select($table, $columns='*', $where=false, $condition=false, $limit=''){
		$fields = !$columns?'*':$columns;
		if(is_array($columns)){
			$fields = implode(', ', $columns);
		}
		$cond = '';
		if(!!$where){
			$cond = ' where '.static::build($where, $condition);
		}
		$sql = "Select $fields from $table $cond ".$limit;

		$result = static::query($sql);
		if(!!$result && is_array($result) && count($result)>0){
			$temp = [];
			foreach($result as $item){
				array_push($temp, Bella::oval($item));
			}
			return $temp;
		}
		return null;
	}
		
	public static function last($table, $limit=15, $order=false){
		$_order = '';
		if($order){
			$_a = [];
			foreach($order as $k=>$v){
				array_push($_a, $k.' '.$v);
			}
			$_order = ' order by '.implode(', ', $_a);
		}
		$sql = "Select * from $table $_order limit 0, $limit";
		$result = static::query($sql);
		if(!!$result && is_array($result) && count($result)>0){
			$temp = [];
			foreach($result as $item){
				array_push($temp, Bella::oval($item));
			}
			return $temp;
		}
		return null;
	}
	

	public static function total($table, $where=false, $condition=false){
		$cond = '';
		if(!!$where){
			$cond = ' where '.static::build($where, $condition);
		}
		$sql = "Select count(*) as total from $table $cond";
		$result = static::query($sql);
		if(!!$result && is_array($result) && count($result)==1){
			return isset($result[0]['total'])?$result[0]['total']*1:0;
		}
		return 0;
	}

		
	public static function connect($database=false){
		if(!static::$server && !static::$db){
			if(!!$database){
				$_mysql = $database;
			}
			else{
				$_databases = Config::get('databases');
				$_mysql = (object) $_databases->mysql;
			}
			static::$server	= $_mysql->server;
			static::$db		= $_mysql->dbname;
			static::$user	= $_mysql->username;
			static::$pass	= $_mysql->password;
		}
		if(!static::$connection){
			$c = @mysql_connect(static::$server, static::$user, static::$pass);
			if(!$c){
				static::showError('Error : Connection failed : '.mysql_error());	
			}
			if(!mysql_select_db(static::$db, $c)){
				static::showError('Error : Selection database failed : '.mysql_error());				
			}
			static::$connection = $c;
		}
	}	
				
	public static function disconnect(){
		if(static::$connection){
			mysql_close(static::$connection);
			static::$connection = null;
		}
	}	
	
	public static function execute($sql){
		static::connect();
		$r = [];
		if($data = @mysql_query(static::escape($sql), static::$connection)){
			if(!!$data && mysql_num_rows($data)>0){
				mysql_data_seek($data, 0);
				while($row = mysql_fetch_assoc($data)){
					foreach($row as $key => $val){
						$row[$key] = static::unescape($val);
					}
					array_push($r, $row);
				}
			}
			mysql_free_result($data);
		}
		else{
			static::showError('Invalid query: ' .mysql_error()."\n\n".$sql);
		}
		static::disconnect();
		return $r;		
	}
	
	protected static function query($sql, $params = false, $closing = true){
		if(!!$params){
			$sql = static::build($sql, $params);
		}
		static::connect();
		if(stripos($sql, 'select')===0){
			$r = [];
			if($data = @mysql_query($sql, static::$connection)){
				if(!!$data && mysql_num_rows($data)>0){
					mysql_data_seek($data, 0);
					while($row = mysql_fetch_assoc($data)){
						foreach($row as $key => $val){
							$row[$key] = static::unescape($val);
						}
						array_push($r, $row);
					}
				}
				mysql_free_result($data);
			}
			else{
				static::showError('Invalid query: ' .mysql_error()."\n\n".$sql);
			}
		}
		else{
			$r = false;
			if(@mysql_query($sql, static::$connection)){
				$r = true;
			}
			else{
				static::showError('Invalid query: ' .mysql_error()."\n\n".$sql);
			}			
		}
		if($closing){
			static::disconnect();
		}
		return $r;
	}
	
	protected static function build($sql, $params){
		if(is_string($params)){
			$params = static::escape($params);
		}
		else if(is_array($params)){
			for($i=0;$i<count($params);$i++){
			  $params[$i] = static::escape($params[$i]);
			}
		}		
		$t = $sql;
		if(!!$t && !!$params){
			$sql = vsprintf(str_replace("?","'%s'", $t), $params);
		}	
		return $sql;	
	}
	
	protected static function escape($str){
		return str_replace(
			["\\","\0","\n","\r","\x1a","'",'"'],
			["\\\\","\\0","\\n","\\r","\Z","\'",'\"'],
			$str
		);
	}		
	protected static function unescape($str){
		return str_replace(
			["\\\\","\\0","\\n","\\r","\Z","\'",'\"'],
			["\\","\0","\n","\r","\x1a","'",'"'],
			$str
		);
	}
		
	protected static function showError($msg=''){
		if(Config::get('debug')==1){
			trigger_error($msg);
		}
	}  
}


