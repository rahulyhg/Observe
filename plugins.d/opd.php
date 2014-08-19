<?php

CensusPluginRegister::instance()->register( "CensusPluginOPD" );


class CensusPluginOPD extends CensusPlugin
{
	protected $id = "opd";	
	
	public function applyTo( $curl )
	{
		$dom = new DOMDocument();
		$urls = array();
	
		@$dom->loadHTML( $curl->webpage );
		$xpath = new DOMXPath($dom);

		$links = $xpath->query("//link[@rel='openorg']");

		foreach( $links as $link_tag )
		{
			$url = strtolower($link_tag->getAttribute("href"));
			break;
		}

		$base_url = preg_replace('#^(.*)/+$#', '\1', $curl->info['url']);
		
		if(!isset($url)){
			
			$tmpcurl = $this->_opd_get("{$base_url}/.well-known/openorg",$base_url);
			if($tmpcurl['http_code']=='200'){
				//need to check 404's
				$tmpcurl4 = $this->_opd_get("{$base_url}/404TestURLThatIsReallyLongSoIShouldGetTheCorectHeader",$base_url);
				if($tmpcurl4['http_code']=='404'){
					$url = $tmpcurl['url'];
				}else{
					return false;
				}
			}elseif(strlen($tmpcurl['redirect_url'])){
				$url = $tmpcurl['redirect_url'];
			}else{
				return false;
			}

		}
		

		
		if(strpos($url, 'http') === 0)
		{
			$r = $url;
		}
		elseif(strpos($url, '/') === 0)
		{
			$r = $base_url.$url;
		}
		else
		{
			$r = $base_url."/".$url;
		}

		
		return $r; 
	}	
	
	private function _opd_get($url,$base){
		
		$s = curl_init();

		curl_setopt($s,CURLOPT_URL,$url);
		curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($s,CURLOPT_FOLLOWLOCATION,false);
		curl_setopt($s,CURLOPT_USERAGENT,'OPDFinder (http://opd.data.ac.uk/)');
		curl_setopt($s,CURLOPT_REFERER,$base);

		curl_exec($s);
		
		$info = curl_getinfo($s);

		curl_close($s);
		return $info;
	}	
	
}
