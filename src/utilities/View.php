<?php

namespace Bella;
use Handlebars;

class View{
	
	public $layout = '';
	public $template = '';
	
	public $modules = [];
	public $dependencies = [];
	public $libs = [];
	
	public $engine = false;
	
	public $data = [
		'meta' => [],
		'header' => [],
		'css' => [],
		'js' => [],
	];
	
	function __construct(){
		Bella::loadPackage('xamin/handlebars.php/src/Handlebars/Autoloader.php');
		Handlebars\Autoloader::register();
		$this->engine = new Handlebars\Handlebars;
		
		$config = Config::get('settings');
		
		$header = Config::get('application');
		$this->data['header'] = (object) $header;
		$this->data['textData'] = (object) [];
		$this->data['scriptData'] = (object) [];
		
		$this->data['meta'] = (object) [
			'server'	=> Config::get('server'),
			'domain'	=> Config::get('domain'),
			'home'		=> '//'.$_SERVER['SERVER_NAME'].$config->baseDir,
			'baseDir'	=> $config->baseDir,
			'tracking'	=> $config->tracking
		];	
		
		$this->setTemplate('default');
		$this->setLayout('default');
	}
	
	public function setLayout($name, $path=''){
		if(!$path){
			$path = Config::get('settings')->views_dir.'layout/';
		}
		if(strpos($name, '.php')===false && strpos($name, '.htm')===false && strpos($name, '.xml')===false){
			$name.='.html';
		}
		$this->layout = $path.$name;
	}
	
	public function setTemplate($name, $path=''){
		if(!$path){
			$path = Config::get('settings')->views_dir.'templates/';
		}
		if(strpos($name, '.php')===false && strpos($name, '.htm')===false && strpos($name, '.xml')===false){
			$name.='.html';
		}
		$this->template = $path.$name;
	}
		
	public function registerCSS($arr){
		$css = $this->data['css'];
		if(is_string($arr)){
			array_push($css, $arr);
		}
		else if(is_array($arr)){
			$css = array_merge_recursive($css, $arr);
		}
		$this->data['css'] = array_unique($css);
	}
	
	public function registerScript($arr){
		$js = $this->data['js'];
		if(is_string($arr)){
			array_push($js, $arr);
		}
		else if(is_array($arr)){
			$js = array_merge_recursive($js, $arr);
		}
		$this->data['js'] = array_unique($js);
	}
	
	public function registerModules($mod){
		if(is_array($mod)){
			$modules = $this->modules;
			$this->modules = array_merge_recursive($modules, $mod);
		}
		else{
			array_push($this->modules, $mod);
		}
	}
	public function registerDependencies($dep){
		if(is_array($dep)){
			$dependencies = $this->dependencies;
			$this->dependencies = array_merge_recursive($dependencies, $dep);
		}
		else{
			array_push($this->dependencies, $dep);
		}
	}
	public function registerLibs($lib){
		if(is_array($lib)){
			$libs = $this->libs;
			$this->libs = array_merge_recursive($libs, $lib);
		}
		else{
			array_push($this->libs, $lib);
		}
	}


	public function setHeader($ob){
		if(is_object($ob) || is_array($ob)){
			foreach($ob as $k=>$v){
				$this->data['header']->$k = $v;
			}
		}
	}
		
	public function setTextData($ob){
		if(is_object($ob) || is_array($ob)){
			foreach($ob as $k=>$v){
				$this->data['textData']->$k = $v;
			}
		}
	}
	
	public function setScriptData($ob){
		if(is_object($ob) || is_array($ob)){
			foreach($ob as $k=>$v){
				$this->data['scriptData']->$k = $v;
			}
		}
	}

	public function render($data=false){
		$output = Request::input('output');
		if($output=='json'){
			return Response::json($data);
		}
		else if($output=='xml'){
			return Response::xml($data);
		}
		return $this->renderHTML($data);
	}

	private function strReplaceAssoc(array $replace, $subject){
		return str_replace(array_keys($replace), array_values($replace), $subject);    
	} 
	
	public function renderHTML($data=false){
		
		$meta = $this->data['meta'];
		$header = $this->data['header'];
		
		if(!$header->url){
			$header->url = $meta->home;
		}
		
		$sTemplate = File::read($this->template);
		$sLayout = File::read($this->layout);
		
		$sTemplate = str_replace('{{name}}', $header->name, $sTemplate);
		$sTemplate = str_replace('{{description}}', $header->description, $sTemplate);
		$sTemplate = str_replace('{{keywords}}', $header->keywords, $sTemplate);
		$sTemplate = str_replace('{{slogan}}', $header->slogan, $sTemplate);
		$sTemplate = str_replace('{{title}}', $header->title, $sTemplate);
		$sTemplate = str_replace('{{url}}', $header->url, $sTemplate);
		$sTemplate = str_replace('{{canonical}}', $header->canonical, $sTemplate);
		$sTemplate = str_replace('{{image}}', $header->image, $sTemplate);
		$sTemplate = str_replace('{{creator}}', isset($header->creator)?$header->creator:isset($header->author)?(is_object($header->author)?$header->author->name:$header->author):'', $sTemplate);
		$sTemplate = str_replace('{{siteURL}}', $meta->home, $sTemplate);
		
		$jsData = 'var SDATA = '.json_encode([
			'site' => $meta,
			'page'=> $this->data['scriptData'],
			'requires' => [
				'libs' => $this->libs,
				'modules' => $this->modules,
				'dependencies' => $this->dependencies,
			]
		]);
		$sTemplate = str_replace('{@SCRIPTDATA}', $jsData, $sTemplate);
		
		$conf = Config::get('settings');
		
		// handling CSS files
		$path = $conf->baseDir.$conf->public_dir.'css/';
		
		$tplStyle = '';
		$remoteCSS = [];
		$localCSS = [];
		foreach($this->data['css'] as $file){
			if(!!$file){
				if(strpos($file, 'http://')===0 || strpos($file, 'https://')===0){
					array_push($remoteCSS, $file);
				}
				else{
					if(strpos($file, '.css')===false){
						$file = $file.'.css';
					}
					array_push($localCSS, $path.$file);			
				}	
			}
		}
		if(count($remoteCSS)>0){
			foreach($remoteCSS as $rfile){
				$tplStyle.='<link rel="stylesheet" type="text/css" href="'.$rfile.'">';
			}
		}
		
		if(count($localCSS)>0){
			if(isset($conf->cssCacheDir)){
				$aStyle = [];
				$cssKey = md5(implode(";", $localCSS));
				$file = $conf->cssCacheDir.$cssKey.'.css';
				$style = '';
				foreach($localCSS as $f){
					$f = substr($f, 1);
					if(file_exists($f)){
						$style.= File::read($f);
					}
				}
				$style = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $style);
				$style = str_replace(': ', ':', $style);
				$style = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $style);					
				File::write($file, $style);
				$tplStyle.='<link rel="stylesheet" type="text/css" href="'.$conf->baseDir.$file.'">';
			}
			else{
				$aStyle = [];
				foreach($localCSS as $file){
					array_push($aStyle, '<link rel="stylesheet" type="text/css" href="'.$file.'">');
				}
				$tplStyle.=implode("\n    ", $aStyle);
			}
		}
		
		$sTemplate = str_replace('{@STYLE}', $tplStyle, $sTemplate);
		
		
		// handling JS files
		$path = $conf->baseDir.$conf->public_dir.'js/';
		
		$arr = array();
		foreach($this->data['js'] as $file){
			if(!!$file){
				array_push($arr, $path.$file);				
			}
		}
		if(count($arr)>0){
			$aScript = [];
			foreach($arr as $file){
				if(strpos($file, '.js')===false){
					$file = $file.'.js';
				}
				array_push($aScript, '<script type="text/javascript" src="'.$file.'"></script>');
			}
			$sTemplate = str_replace('{@SCRIPT}', implode("\n    ", $aScript), $sTemplate);
		}
		else{
			$sTemplate = str_replace('{@SCRIPT}', '', $sTemplate);
		}
		
		//Response::json($data);
		$s = str_replace('{@CONTEXT}', $sLayout, $sTemplate);
		$s = str_replace('{@BASEDIR}', $conf->baseDir, $s);
		
		$data['application'] = $header;
		Response::html($this->engine->render($s, $data));
	}

	private function clean($css){
		$style = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		$style = str_replace(': ', ':', $style);
		$style = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $style);	
		return $style;
	}
	public function output(){
		Bella::deny();
	}
}


