<?php

namespace Ceghirepro\Current;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class CurrentAPI
{
	/**
	* Test function
	* @return dd("hello")
	*/
	public function hello() {
		$client = new Client();
		dd($client);
	}
}
