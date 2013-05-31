## Laravel PHP Framework

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as authentication, routing, sessions, and caching.

Laravel aims to make the development process a pleasing one for the developer without sacrificing application functionality. Happy developers make the best code. To this end, we've attempted to combine the very best of what we have seen in other web frameworks, including frameworks implemented in other languages, such as Ruby on Rails, ASP.NET MVC, and Sinatra.

Laravel is accessible, yet powerful, providing powerful tools needed for large, robust applications. A superb inversion of control container, expressive migration system, and tightly integrated unit testing support give you the tools you need to build any application with which you are tasked.

## Official Documentation

Documentation for the entire framework can be found on the [Laravel website](http://laravel.com/docs).

### Contributing To Laravel

**All issues and pull requests should be filed on the [laravel/framework](http://github.com/laravel/framework) repository.**

### License


The Laravel framework is open-sourced software license under the [MIT license](http://opensource.org/licenses/MIT)

##Laravel 4 - Starter Kit

This is a Laravel 4 Starter Kit, it will help you getting started with Laravel 4.  
Fork of a repo by [brunogaspar](https://github.com/brunogaspar/laravel4-starter-kit/)

It includes examples on how to use the framework itself and how to use some packages, like the awesome [Sentry 2](https://github.com/cartalyst/sentry) package. 
It also include an example on how to use Opauth package for authentication via Social authentication providers like facebook/google.

-----

##Included goodies

* Twitter Bootstrap 2.3.1
* jQuery 1.9.1
* Custom Error Pages:
	* 403 for forbidden page accesses
	* 404 for not found pages
	* 500 for internal server errors
* Cartalyst Sentry 2 for Authentication and Authorization
* Back-end
	* User and Group management
* Front-end
	* User login, registration, forgot password
	* User account area
* Packages included:
	* [Cartalyst Sentry 2](https://github.com/cartalyst/sentry)
	* [Jason Lewis Expressive Date](https://github.com/jasonlewis/expressive-date)
	* [Meido HTML](https://github.com/meido/html)
	* [Meido Str](https://github.com/meido/str)
        * [Opauth] (https://github.com/opauth/opauth)

-----

##How to Install

###1) Downloading
####1.1) Clone the Repository

	git clone https://github.com/opencubicles/laravel4_starterkit your-folder

####1.2) Download the Repository

	https://github.com/opencubicles/laravel4_starterkit/archive/master.zip

-----

###2) Install the Dependencies via Composer
#####2.1) If you don't have composer installed globally

	cd your-folder
	curl -s http://getcomposer.org/installer | php
	php composer.phar install

#####2.2) For globally composer installations

	cd your-folder
	composer install

-----

###3) Setup Database

Now that you have the Laravel 4 cloned and all the dependencies installed, you need to create a database for it.

After the database is created, open the file `app/config/database.php` and update the needed entries..

-----

###4) Sentry Database migration, simply run php artisan migrate --package=cartalyst/sentry from the command line and then run php artisan migrate

-----

###5) Accessing the Administration

To access the administration page, you just need to access `http://your-host/public/admin` on your browser and it will automatically redirect you to the login page, in the login page, just fill in and submit the form.

After you being authenticated, you will be redirected back to the administration page.

###6) Opauth Configuration for user authentication via Facebook (or any other supported authentication providers by Opauth)
1. Create Facebook application at https://developers.facebook.com/apps/
   - Remember to enter App Domains
   - "Website with Facebook Login" must be checked, but for "Site URL", you can enter any landing URL.
2. open the file `app/config/opauth.php` and update it with at least `App ID` and `App Secret`.

Now, you need to create yourself a user and finish the installation.

-----

For any issues, feel free to drop an email to opencubicles@gmail.com

###LICENSE
DBAD LICENSE
=======
The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

