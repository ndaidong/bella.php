<?php

namespace Bella;

class API{
	
	private $baseURL = '';
	private $token = '';
	private $me = null;
	private $version = '';
	
	function __construct($uri){
		if(substr($uri, strlen($uri)-1, 1)){
			$uri = substr($uri, 0, strlen($uri)-1);
		}
		$this->baseURL = $uri;
	}
	
	
	/**
     *  Post a collection of values to remote service
     *  Method : push(String $action [, Array $parameters])
     *  Return : a JSON object, same structure as returned by Web Service
    **/
    public function post($action, $parameters){
        $url = $this->baseURL.'/'.(!!$this->version?$this->version.'/':'').$action;
        $data = $this->merge($parameters);
        $response = Remote::push($url, $data);
        if(!!$response){
            $ob = json_decode($response);
            if(!!$ob && is_object($ob)){
                return $ob;
            }
        }
        return false;
    }

    /**
     *  Get data from remote service
     *  Method : pull(String $action [, Array $parameters])
     *  Return : a JSON object, same structure as returned by Web Service
    **/
    public function get($action, $parameters=false){
        $url = $this->baseURL.'/'.(!!$this->version?$this->version.'/':'').$action.'?';
        $url = $this->attach($url, $parameters);
        $response = Remote::pull($url);
        if(!!$response){
            $ob = json_decode($response);
            if(!!$ob && is_object($ob)){
				if(!isset($ob->error) || !$ob->error){
					return $ob;
				}
            }
        }
        return false;
    }
    
    /**
     *  Some small helpers
    **/
    
	public function getBaseURL(){
		return $this->baseURL;
	}
	
	public function me(){
		return $me;
	}
	
	/**
     *  Make a query string by adding action and parameters to base URI
     *  Method : merge(String $action [, Array $data])
     *  Return : a string URL
    **/
    private function buildQuery($action, $data=false){
        $url = $this->baseURL;
        if(!!$action){
            $url.='/'.$action.'?';
        }
        if(!!$data){
            if(is_string($data)){
                $url.=$data;
            }
            else{
                $url = $this->attach($url, $data);
            }
        }
        return $url;
    }

    /**
     *  Merge the stored and pre-defined values with new parameters
     *  Method : merge([Array $data])
     *  Return : an array contains the parameters will be send to web service
    **/
    private function merge($data=false){
        $output = array();
        $arr = array(
            'token'  => $this->token,
        );
        if(!!$data && is_array($data)){
            foreach($data as $key=>$val){
                $output[$key] = $val;
            }
        }
        foreach($arr as $key=>$val){
            if(!isset($output[$key])){
                $output[$key] = $val;
            }
        }
        return $output;
    }

    /**
     *  Add more parameters to query string
     *  Method : attach(String $url [, Array $data])
     *  Return : an URL string with parameters
    **/
    private function attach($url, $data=false){
        $queries = $this->merge($data);
        $parameters = array();
        foreach($queries as $key=>$val){
            array_push($parameters, $key.'='.urlencode($val));
        }
        if(count($parameters)>0){
            $url.=implode('&', $parameters);
        }
        return $url;
    }
}
