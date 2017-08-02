<?php

namespace Ceghirepro\Current;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class CurrentAPI
{
	protected $client;
	
	/**
	*
	**/
	public function __construct()
  {
      $this->client = new Client;
  }

	/**
	* Test function
	* @return dd("hello")
	*/
	public function hello()
	{
			dd($this->client);
	}
}
