<?php

namespace Cegrent\Current;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\ClientException;

class CurrentAPI
{
	// protected $client;
	protected $request;

	public function __construct()
	{
		$this->cache_length = Config::get('current.cache_length');
		$this->log_message = "currentrms/api/v".Config::get('current.version');

		// $this->client = new Client([
		// 	'cookies' => false,
		// 	'headers' => array(
		// 		"X-AUTH-TOKEN" => Config::get('current.api_key'),
		// 		"X-SUBDOMAIN" => Config::get('current.domain'),
		// 		"X-TIME-ZONE" => Config::get('current.time_zone')
		// 	),
		// 	'base_uri' => "https://api.current-rms.com/api/v".Config::get('current.version')."/",
		// 	'http_errors' => true
		// ]);

		$this->request = Http::withOptions([
				'debug' => false,
			])
			->retry(5, 100, function ($exception) {
				return $exception instanceof ConnectionException;
			})
			->baseUrl("https://api.current-rms.com/api/v".Config::get('current.version')."/")
			->acceptJson()
			->withHeaders([
				"X-AUTH-TOKEN" => Config::get('current.api_key'),
				"X-SUBDOMAIN" => Config::get('current.domain'),
				"X-TIME-ZONE" => Config::get('current.time_zone')
			]);
	}

	/**
	*	get
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@return $this->build()
	**/
	public function get($stub, $params, $array = array(), $cache = true)
	{
		return $this->build('get', $stub, $params, $array, $cache);
	}

	/**
	*	get
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@return $this->build()
	**/
	public function pdf($stub, $params, $array = array(), $cache = false)
	{
		return $this->buildPDF('get', $stub, $params, $array, $cache);
	}

	/**
	*	post
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function post($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('post', $stub, $params, $array, $cache);
	}

	/**
	*	put
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function put($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('put', $stub, $params, $array, $cache);
	}

	/**
	*	delete
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function delete($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('delete', $stub, $params, $array, $cache);
	}

	/**
	*	request
	*
	*
	*	@param string			$method
	* 	@param array 			$params
	* 	@param boolean			$cache
	*	@param array 			$array
	*	@return array  			$data
	**/
	public function build($method, $stub, $params, $array = array(), $cache = true)
	{
		try {
			$path = $stub."?".$this->params($params);
			// create a cache key
			$cache_key = base64_encode($path);
	
			// check cache exists
			if($cache && $this->cache_length > 0 && $this->hasCache($cache_key)) {
				// get cached object
				$data = $this->getCache($cache_key);
			} else {
				// log info for request
				Log:info($this->log_message.' ('.$method.') '.$path);
	
				// do live request
	
				if($method == "get") {
					$data = $this->request->get($stub, $params);
				}
	
				if($method == "post") {
					$data = $this->request->post($stub, $array);
				}
			}
	
			if($data->successful()) {
				// collect
				$data->collect();
	
				// are we caching?
				if($this->cache_length > 0) {
					// cache request
					$this->cache($data, $cache_key);
				}
	
				return $data;
			} elseif($data->serverError()) {
				$data->throw();			
			} elseif($data->clientError()) {
				$data->throw();			
			} elseif($data->failed()) {
				$data->throw();			
			}
		} catch(RequestException $e) {
			report($e);	
			abort(500);
		} catch(ConnectionException $e) {
			report($e);	
			abort(500);
		}	
	}

	public function buildPDF($method, $stub, $params, $array = array(), $cache = true)
	{
		try {
			$path = $stub."?".$this->params($params);
			
			// log info for request
			Log:info($this->log_message.' ('.$method.') '.$path);

			// do live request
			$data = $this->client->request($method, $path, ['json' => $array])->getBody()->getContents();

			return $data;
		} catch (ClientException $e) {
			return array('error' => $e->getMessage());
	 	} catch (RequestException $e) {
			return array('error' => $e->getMessage());
		}
	}

	/**
	* 	clearCache
	*
	*	@return void
	*/
	public function clearCache()
	{
		// Log message
		Log::info($this->log_message.' clearing cache');

		// Clear cache
		Cache::flush();
	}

	// Private functions

	/**
	* 	cache
	*
	* 	@param  array  	$data
  	* 	@param  string  $key
  	* 	@return void
  	*/
	private function cache($data, $key)
	{
		Log::info($this->log_message.' caching: '.$key);
		Cache::put($key, $data, Carbon::now()->addMinutes($this->cache_length));
	}

	/**
	* hasCache
	*
  	* @param  string  	$key
  	* @return object	Cache
  	*/
	private function hasCache($key)
	{
		return Cache::has($key);
	}

	/**
	* getCache
	*
  	* @param  string  	$key
  	* @return object	Cache
  	*/
	private function getCache($key)
	{
		Log:info($this->log_message.' getting cache: "'.$key.'"');
		return Cache::get($key);
	}

	/**
	*	Params string builder
	*
	*	@param array 		$array
	* 	@return string		$str
	*/
	private function params($array)
	{
		$str = "";
		$i = 0;

		foreach($array as $a => $k) {
			$str .= $a."=".$k;

			if(++$i != count($array)) {
				$str .= "&";
			}
		}

		return $str;
	}
}
