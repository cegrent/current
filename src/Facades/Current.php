<?php

namespace Ceghirepro\Current\Facades;

use Illuminate\Support\Facades\Facade;

class Current extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
	protected static function getFacadeAccessor()
	{
  	return "Current";
	}
}
