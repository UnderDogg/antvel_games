## Antvel Introduction

[![StyleCI](https://styleci.io/repos/44852299/shield)](https://styleci.io/repos/44852299)

**This is an alpha, experimental release of Antvel. Everything will change gradually, looking forward to get the best of this awesome product. Thank you for testing!**

***Antvel*** is a eCommerce project written in Laravel 5.* intended for building a friendly eStore either for startups or big companies, and is highly opinionated towards that use case. Because it is a platform already built, you only will need to add as many products as you need  to start selling and taking orders from customers

***Antvel*** aim is giving to you and your clients the best eCommerse experience in a different aproach!

## Chat Rooms

<a href="https://www.hipchat.com/gU00TAMff" target="_blank">HipChat</a>

[![Join the chat at https://gitter.im/ant-vel/antVel](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/ant-vel/antVel?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)





<a name="installation"></a>
## Installation

First, clone the antvel repository into your local folder using:

```
git clone https://github.com/ant-vel/antVel.git
```

Next, you will need to run composer update/install into your ***antvel*** project folder
```
composer update/install
```

After run composer update, be sure to create and migrate your database and define your environment variables in your `.env` file. Also you have to run artisan migrate command, in arden to create the Antvel schema.

```
php artisan migrate:refresh
```

Then, you must run the Antvel seeder to create a functional demo, like so:

```
php artisan migrate:refresh --seed
```

At this point we are just missing run ***bower install***, in order to have all the frontend dependencies in your machine.  So, go to your antvel directory using command line, as so:

```
bower install
```

After run all these commands you should be able to look at your antvel version running in your browser without problems.

## Using Laravel Elixir

First at all, you need to install Gulp package typing this command line in your terminal. Like so:

```
npm install --global gulp
```
 
Second at all, you will need to step into your antvel folder, like so:
```
cd antVel
```

Third at all, type this following command in order for you to install the npm dependencies, like so:
```
npm install 
```

If you are developing on Windows or running your VM on it, you must try like so: 
```
npm install --no-bin-links
```

Now feel free to run gulp into your antVel folder, like so:
```
gulp
```



<a name="features"></a>
## reCaptcha Settings

Now tha you have everything setted up, you need to set the Antvel reCaptcha variables, in order to have the user loging working in that mode. First you need to go to the reCaptcha web site and get your environments values

```
https://www.google.com/recaptcha/admin#list
```

then you have to fill out the "Register a new site" form in order to obtain the reCaptcha keys

```
RECAPTCHA_PUBLIC_KEY, and RECAPTCHA_PRIVATE_KEY
```

So, you will need to assign those values into ***Antvel*** ```.env``` file. For example: 

```
RECAPTCHA_PUBLIC_KEY = antvelRecaptcahPrivateKey

RECAPTCHA_PRIVATE_KEY = antvelRecaptcahPrivateKey
```

***Note:*** If the ```APP_DEBUG == true```, the reCaptcha will not be applied

<a name="features"></a>
## Features

* Responsivity
* Open Source
* Social Media Integration
* Unlimited Categories
* Unlimited Products
* Unlimited Manufacturers
* Your Own Style
* Multi Language
* Multi Currency
* Product Reviews
* Product Ratings
* Downloadable Products
* Automatic Image Resizing
* Multiple Tax Rates
* Related Products (What other customers are looking at, Recommendations for you in our categories, Store Trending)
* Search Engine Optimization
* Sales Reports
* Wish Lists
* Products Suggestions
* Products Grouping (http://antvel.com/products/42)
* Addresses Book
* User Profiles (Sellers & Buyers)
* Produducts barcode
* Administrative Panel - (http://antvel.com/wpanel)
* Users Orders list with action status (process, Placed, Cancel, Rated)
* Products Rate
* Products Reviews
* Virtual Products (products key download integrated)
* Free Products Module
* Users Points
* Virtual Products Delivery
* Dinamic Products Features
* Company Profile
* Shopping Cart
* Users preferences control
* Users Notifications
* Company Info CMS
* Search Engine Suggestions
* Dinamic Breadcrumbs

### Demo Information

* ```Demo:``` http://antvel.com
* Admin: admin@admin.com / admin
* Buyer: buyer@antvel.com / admin
* Seller: seller@gmail.com / admin

### Contact

You can communicate with us using the following mediums:

* [Follow us on Twitter](http://twitter.com/octobercms) for announcements and updates.
* [Follow us on Facebook](http://facebook.com/octobercms) for announcements and updates.
* [Join us on hipChat](https://antvel.hipchat.com/home) to chat with us.
* [Email](gustavoocanto@gmail.com) for questions
* http://g-ocanto.com

### License

[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

The AntVel eCommerce is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Contributing

Before sending a Pull Request, be sure to review the [Contributing Guidelines](CONTRIBUTING.md) first.


