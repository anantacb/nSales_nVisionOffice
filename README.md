<p align="center"><a href="https://laravel.com" target="_blank">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400">
</a>
</p>
<p align="center">
<a href="https://vuejs.org/" target="_blank">
    <img src="https://www.cdnlogo.com/logos/v/92/vue-js.svg" width="150"></a>
</p>

# Project Setup

**Using PHP 8 and VUE 3**

### If Using Valet and default php version is not 8

- valet isolate php@8.2

### Copy env file

- cp .env.example .env

*Set database credentials in .env file*

**If you want to use redis for caching need to [install](https://redis.io/docs/getting-started/installation/) redis in
local and set `CACHE_DRIVER=redis`**

## Composer

- composer install
- php artisan key:generate
- php artisan jwt:secret

## Node

- npm install

### dev build

- npm run dev

### Production Build

- npm run build
