<?php

namespace Bella;

class Bella{
	
	public static function createId($len=16, $prefix='', $withUnderscore=false){
		$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ9876543210'.($withUnderscore?'_':'').'abcdefghijklmnopqrstuvwxyz';
		$max = strlen($base)-1;
		$s = '';
		mt_srand((double)microtime()*1000000);
			while(strlen($s)<$len-strlen($prefix)){
				$s.=$base{mt_rand(0,$max)};
			}
		return $prefix.$s;
	}

	public static function removeSpecialChar($s){
		$s = preg_replace('/  */', '-', $s);
		$s = preg_replace('/[^A-Za-z0-9\-]/', '', $s);
		return preg_replace('/-+/', '-', $s);
	}
	
	public static function stripAccent($str){
		if(!$str) return '';
		  $unicode = array(
			'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|ä',
			'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Ä',
			'c'=>'ç','C'=>'Ç','d'=>'đ','D'=>'Đ',
			'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|ë',
			'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ|Ë',
			'i'=>'í|ì|ỉ|ĩ|ị|ï|î',
			'I'=>'Í|Ì|Ỉ|Ĩ|Ị|Ï|Î',
			'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|ö',
			'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ô|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ|Ö',
			'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|û',
			'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự|Û',
			'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
			'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
		  );
			foreach($unicode as $nonUnicode=>$uni){
				$str = preg_replace("/($uni)/", $nonUnicode, $str);
			}
		return $str;
	} 
	
	 public static function trim_all( $str , $what = NULL , $with = ' ' ){
        if($what === NULL){
            $what = "\\x00-\\x20";
        }
        return trim( preg_replace( "/[".$what."]+/" , $with , $str ) , $what);
    }
    
    public static function oval($arr){
        if(isset($arr)){
            $ob = array();
            foreach($arr as $key=>$val){
                if(is_null($val)){
                    $val = '';
                }
                else if(is_numeric($val)){
                    $val*=1;
                }
                $ob[$key] = $val;
            }
            return (object) $ob;
        }
        return false;
    }
    
	public static function makeAlias($s){
		$x = static::stripAccent($s);
		$x = static::removeSpecialChar($x);
		$x = str_replace([' ', '?', '/'], '-', $x);
		return $x;
	}
	
	public static function fill($s){
		return str_replace(["\r", "\n","\r\n", "\t"],'',$s);
	}
	
	public static function truncate($s, $len, $type='word'){
		if(is_string($s) && strlen($s)>$len){
			
			$newStr='';
			
			$s = static::rip_tags($s);
			
			if($type=='letter'){
				$count = count($s);
				$lim = ($len > $count) ? $count : $len;
				$newStr = mb_substr($s, 0, $lim);
			}
			else if($type=='word'){
				$tmp = [];
				$arr = explode(' ', $s);
				$count = count($arr);
				$lim = ($len > $count) ? $count : $len;
				$i = 0;
				while(count($tmp)<$lim){
					$_s = trim($arr[$i]);
					if(!!$_s){
						if(strlen($_s)>50){
							$_s = mb_substr($_s, 0, 50).'...';
						}
						array_push($tmp, $_s);
					}
					$i++;
				}
				if(count($tmp)>0){
					$newStr = implode(' ', $tmp);
				}
				else{
					$newStr = $s;
				}
			}
			if(strlen($newStr)<strlen($s)){
				$newStr.='...';
			}
			return $newStr;
		}
		return $s;
	}
	
	public static function rip_tags($s){
		$s = preg_replace("'<style[^>]*>.*</style>'siU", ' ', $s);
		$s = preg_replace("'<script[^>]*>.*</script>'siU", ' ', $s);
		$s = preg_replace('@<[\/\!]*?[^<>]*?>@si', ' ', $s);
		$s = str_replace(["\n", "\r", "\t", '<br>', '&amp;nbsp;',  '&nbsp;'], ' ', $s);
		$s = trim(preg_replace('/ {2,}/', ' ', $s));
		return $s; 
	}
	
	public static function date($time = false, $sformat = false){
		if(!$time){
			$time = time();
		}
		$format = !$sformat?Config::get('global')->dateFormat:$sformat;
		return date($format, $time);
	}
	
	public static function relativeTime($ptime) {
		$etime = time() - $ptime;
		
		if ($etime < 1) {
			return 'Just now';
		}
		
		$a = [
			12 * 30 * 24 * 60 * 60  =>  'year',
			30 * 24 * 60 * 60       =>  'month',
			24 * 60 * 60            =>  'day',
			60 * 60                 =>  'hour',
			60                      =>  'minute',
			1                       =>  'second'
		];
		
		foreach ($a as $secs => $str) {
			$d = $etime / $secs;
			if ($d >= 1) {
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's' : ''). ' ago';
			}
		}
		return '';
	}
	
	public static function IP(){
		$keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($keys as $key){
			if(array_key_exists($key, $_SERVER) === true){
				foreach (explode(',', $_SERVER[$key]) as $ip){
					$ip = trim($ip);
					if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
						return $ip;
					}
				}
			}
		}
		return '';
	}
	
	public static function encrypt($str, $key=''){
		if(!!$str && is_string($str)){
			return sha1(hash('sha512', $str.$key.$str));
		}
		return false;
	}
	
	public static function redirect($u='', $external=false){
		$config = Config::get('global');
		if(!$u){
			$u='';
		}
		if(!$external){
			$u = substr($config->baseDir, 0, strlen($config->baseDir)-1).$u;
		}
		header("location:$u");
	}
	
	public static function end(){
		$p 	 = Path::get();
		$arr = [];
		foreach($p as $item){
			if($item!=''){
				array_push($arr, $item);
			}
		}
		$url = implode('/',$arr);
		
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        
		$MSG_404 = 'The requested URL <span class="path">/'.$url .'</span> was not found on this server.';
		$config = Config::get('global');
		require($config->error_pages_dir.'404.php');
		exit;	
	}	
	public static function deny(){
		
		header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: OAuth realm=""');
        
		$MSG_401 = 'We\'re sorry, you do not have sufficient permissions to access this page.';
		$config = Config::get('global');
		require($config->error_pages_dir.'401.php');
		exit;	
	}	
	public static function shutdown(){
		$config = Config::get('global');
		require($config->error_pages_dir.'maintenance.php');
		exit;	
	}
	public static function json($s, $end=true){
		error_reporting(0);
		ob_end_clean();
		header('content-type:text/javascript;charset=utf-8');
		if(!!$end){
			echo self::fill(json_encode($s));
			exit();
		}
		header('connection: close');
		ignore_user_abort(true);
		ob_start();
		echo self::fill(json_encode($s));
		$size = ob_get_length();
		header("Content-Length: $size");		
		@ob_flush();
		@ob_end_flush();
		@flush();
	}	
	
	public static function loadCoordinator($c, $m = 'parse'){
		$ctrl = false;
		$class = 'Bella\\'.$c.'Controller';
		if(class_exists($class)){
			$ctrl = new $class();
			$ctrl->setName($c);	
		}
		else{
			$dir = Config::get('global')->controllers_dir;
			$f = $dir.$c.'.php';
			if(file_exists($f)){
				include $f;
			}
		}
		if(class_exists($class)){
			$ctrl = new $class();
			$ctrl->setName($c);
			if($m){
				if(method_exists($ctrl, $m)){
					$ctrl->$m();
				}	
				else{
					Except::showError("Error : Called class or method is undefined.");
				}		
			}
		}
		return $ctrl;
	}
	
	public static function loadHandler($h){
		$class = 'Bella\\'.$h.'Model';
		if(class_exists($class)){
			return new $class();
		}
		else{
			$dir = Config::get('global')->models_dir;
			$f = $dir.$h.'.php';
			if(file_exists($f)){
				include $f;
				if(class_exists($class)){
					$cls = new $class();
					$cls->setName($h);
					return $cls;
				}
			}
			else{
				Except::showError("Error : Called class or method is undefined.");
			}
		}
		return self::deny();
	}
	
	public static function loadHelper($dirname, $filename=''){
		$path = realpath(dirname( __FILE__ )).'/../helpers/';
		
		$dir = $path.$dirname.'/';
		if(is_dir($dir)){
			if(!!$filename){
				$file = $dir.$filename.(strpos($filename, '.php')===false?'.php':'');
				if(file_exists($file)){
					include_once($file);
				}
			}
			else{
				foreach(glob($dir.'*.php') as $file){
					 include_once $file;
				}
			}
		}
	}
	
	public static function loadPackage($path=''){
		$dir = 'vendor/';
		if(!!$path){
			$file = $dir.$path;
			if(file_exists($file)){
				include_once($file);
			}
		}
	}
	
	public static function initialize(){
		Config::init();
		
		if(Config::get('active')===0){
            return Bella::shutdown();
        }
        
        $q = Config::get('global');
        if(is_dir($q->requires_dir)){
			foreach(glob($q->requires_dir.'*.php') as $file){
				 include_once $file;
			}
		}
        Session::init();
        Path::init();
        Request::init();
        
        $cls = false;
        $path = Path::get(0);
       
		if(!$path){
			$cls = Bella::loadCoordinator('index');
		}
		else{
			$cls = Bella::loadCoordinator($path);
		}
		if(!$cls){
			return Bella::end();
		}
	}
}

Bella::initialize();
