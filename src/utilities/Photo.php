<?php

class Photo {

	const MIN_WIDTH 	= 48;
	const MIN_HEIGHT 	= 48;	
	const MAX_WIDTH 	= 240;
	const MAX_HEIGHT 	= 240;
	
	public static function localize($img='', $path, $lim=false, $saveas='', $quality=75){
		if(!!$img){
			if(strpos($img, 'data:')===0){
				return static::base64ToLocalImage($img, $path, $lim, $saveas, $quality);
			}
			else if(strpos($img, 'http')===0){
				return static::remoteToLocalImage($img, $path, $lim, $saveas, $quality);
			}
		}
		return '';
	}
	
	private static function base64ToLocalImage($img, $path, $lim=false, $saveas='', $quality=75){
		
		$imgdata = base64_decode($img);
		if(!!$imgdata){
			
			require_once 'Master/base/libraries/Images/SimpleImage.php';
			
			$str = str_replace('data:', '', $img);
			$arr = explode(';', $str);
			$type = $arr[0];
			$data = str_replace('base64,', '', $arr[1]);
			
			$data = base64_decode($data);
			$ext = static::getExt($type);
			if(!!$data && !!$ext){
				$name = !!$saveas?$saveas:Bella::createId(32);
				$name.=$ext;
				$file = $path.$name;
				$save = @file_put_contents($file, $data);
				if(!!$save){
					$sm = new SimpleImage;
					$sm->load($file);
					$w = $sm->getWidth();
					$h = $sm->getHeight();
					
					$minWidth = static::MIN_WIDTH;
					$minHeight = static::MIN_HEIGHT;			
					$maxWidth = static::MAX_WIDTH;
					$maxHeight = static::MAX_HEIGHT;
						
					if(!!$lim){
						if(isset($lim['minWidth'])){
							$minWidth = $lim['minWidth'];
						}
						if(isset($lim['minHeight'])){
							$minHeight = $lim['minHeight'];
						}				
						if(isset($lim['maxWidth'])){
							$maxWidth = $lim['maxWidth'];
						}
						if(isset($lim['maxHeight'])){
							$maxHeight = $lim['maxHeight'];
						}
					}		
					if($w < $minWidth || $h <$minHeight){
						unlink($file);
						return false;
					}
					if($w > $maxWidth || $h > $maxHeight){
						$sm->fillTo($maxWidth, $maxHeight);
						$sm->save($file, false, $quality);
					}
					return $name;
				}
			}
		}		
	}
	private static function remoteToLocalImage($url, $path, $lim=false, $saveas='', $quality=75){
		
		$im = @getimagesize($url);
		
		if(!!$im){
			
			require_once 'Master/base/libraries/Images/SimpleImage.php';
			
			$type = $im['mime'];
			$width = $im[0];
			$height = $im[1];

			$minWidth = static::MIN_WIDTH;
			$minHeight = static::MIN_HEIGHT;			
			$maxWidth = static::MAX_WIDTH;
			$maxHeight = static::MAX_HEIGHT;
			
			if(!!$lim){
				$lim = (object) $lim;
				if(isset($lim->minWidth)){
					$minWidth = $lim->minWidth;
				}
				if(isset($lim->minHeight)){
					$minHeight = $lim->minHeight;
				}				
				if(isset($lim->maxWidth)){
					$maxWidth = $lim->maxWidth;
				}
				if(isset($lim->maxHeight)){
					$maxHeight = $lim->maxHeight;
				}
			}
			if($width>$minWidth && $height>$minHeight && strpos($type, 'image')===0){
				
				$ext = static::getExt($type);
				
				if(!!$ext){
					$content = Remote::pull($url);
					if(!!$content){
						$name = !!$saveas?$saveas:Bella::createId(32);
						$name.=$ext;
						$file = $path.$name;
						
						$save = @file_put_contents($file, $content);
						if(!!$save){
							if($width>$maxWidth || $height>$maxHeight){
								$sm = new SimpleImage;
								$sm->load($file);
								$sm->fillTo($maxWidth, $maxHeight);
								$sm->save($file, false, $quality);
							}
							return $name;
						}
					}
				}			
			}	
		}
		return false;
	}	
	
	private static function getExt($mime){
		$ext = '';
		switch($mime){
			case 'image/jpg':
			case 'image/jpeg': $ext='.jpg';break;
			case 'image/png': $ext='.png';break;
			case 'image/gif': $ext='.gif';break;
		}		
		return $ext;
	}
	
}
