# Current RMS Laravel Package
A lightweight package that will allow your Laravel application to talk to the [Current RMS API](http://api.current-rms.com/doc).

## Getting Started
Add to your project ```composer require ceghirepro/current dev-master```.

1. Add ```Ceghirepro\Current\CurrentServiceProvider::class``` to providers in config/app.php
2. Add ```'Current' => Ceghirepro\Current\Current::class``` to aliases in config/app.php
3. Run ```php artisan vendor:publish```
4. Add API Key and domain details to config/current.php

###Typical usage
GET request on ```/products```:
```Current::get('/products', array('page' => '1', 'per_page' => '20', 'filtermode' => 'all'))```

POST request on ```/availability/group```:
```Current::post('/availability/group', array(), array('product_availability_view_options' => array('product_group_id' => 1))```
