<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    | You can find out more at: https://api.current-rms.com/doc.
    */

    'api_key' => env('CURRENT_API_KEY', ''),

		/*
		|--------------------------------------------------------------------------
		| Domain
		|--------------------------------------------------------------------------
		| This is the domain you use to log into Current, if the URL is
    | ceg.current-rms.com, then the domain would be "ceg".
		*/

		'domain' => env('CURRENT_DOMAIN', ''),

    /*
		|--------------------------------------------------------------------------
		| API Version
		|--------------------------------------------------------------------------
		|
		*/

		'version' => env('CURRENT_API_VERSION', '1'),

    /*
    |--------------------------------------------------------------------------
    | Cache Length
    |--------------------------------------------------------------------------
    | How many minutes do you want the cache to be? If set to 0, caching is
    | disabled.
    */

    'cache_length' => env('CURRENT_CACHE', '0'),
];
