<?php

namespace Cegrent\Current;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class CurrentAPI
{
	protected $client;

	public function __construct()
  {
		$this->cache_length = Config::get('current.cache_length');
		$this->log_message = "currentrms/api/v".Config::get('current.version');

		$this->client = new Client([
			'cookies' => false,
			'headers' => array(
				"X-AUTH-TOKEN" => Config::get('current.api_key'),
				"X-SUBDOMAIN" => Config::get('current.domain')
			),
			'base_uri' => "https://api.current-rms.com/api/v".Config::get('current.version')."/",
			'http_errors' => true
		]);
  }

	/**
	*	get
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@return $this->build()
	**/
	public function get($stub, $params, $array = array())
	{
		return $this->build('get', $stub, $params, $array);
	}

	/**
	*	get
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@return $this->build()
	**/
	public function pdf($stub, $params, $array = array())
	{
		return $this->buildPDF('get', $stub, $params, $array);
	}

	/**
	*	post
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
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
	* @param array 			$params
	* @param boolean		$cache
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
	* @param array 			$params
	* @param boolean		$cache
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
	* @param array 			$params
	* @param boolean		$cache
	*	@param array 			$array
	*	@return array  		$data
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
				$data = $this->client->request($method, $path, ['json' => $array])->getBody()->getContents();

				// are we caching?
				if($this->cache_length > 0) {
					// cache request
					$this->cache($data, $cache_key);
				}
			}

			// json_decode the object
			return json_decode($data, true);
		} catch (ClientException $e) {
			return array('error' => $e->getMessage());
	 	} catch (RequestException $e) {
			return array('error' => $e->getMessage());
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
	* clearCache
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
	* cache
	*
	* @param  array  $data
  * @param  string  $key
  * @return void
  */
	private function cache($data, $key)
	{
		Log::info($this->log_message.' caching: '.$key);
		Cache::put($key, $data, Carbon::now()->addMinutes($this->cache_length));
	}

	/**
	* hasCache
	*
  * @param  string  $key
  * @return object	Cache
  */
	private function hasCache($key)
	{
		return Cache::has($key);
	}

	/**
	* getCache
	*
  * @param  string  $key
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
	* @return string	$str
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
