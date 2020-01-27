
## :warning: Disclaimer

This project is not maintained anymore. It was a small try at pulling google fit data in a fitbit account. It is quick-and-dirty code with weird logic, but it worked well for me when I used it. I basically don't wear my Android watch anymore since a while now so I didn't keep up with the development of this tool.

# GoogleFeedBit

Google Fit to Fitbit : this is a web tool to add the steps registered with your Android wear device on your Fitbit account.

It's a rather botched attempt at putting all the footsteps gathered by my Android watch on my Fitbit account.

Login on the website with both your accounts and it will put Google Fit last week's data on Fitbit.

This really needs some automation to stop bothering to connect on the website regularly.

# Contributing

Here is a few things you need to know if you want to contribute to the project or work on your own version on your machine.

## Installation

Clone the project and install php dependencies with composer (`composer install`).
JavaScript dependencies are installed through bower. Install it and do a `bower install`.
CSS is processed through [pleeease](http://pleeease.io/) so you'll need to install it if you want to modify CSS.

## Configuration

When everything is installed you have to create your `config/secrets.php` file, containing your google and fitbit api keys (check the `config/secrets.sample.php` as an example).

## Starting a server

This can work on a simple PHP command-line server. Start this command in the root dir of your project to make the website available at localhost:8000.

```
php -S localhost:8000 -t public/
```

## How it works

I use [Slim](http://www.slimframework.com/) as a base framework. All the actual, interesting code is in `routes/all.php`. That's pretty much it. Go check it out :)

## Todo

* deal with paginated results of the Google Fit API
* certainly be more precise with the data transformation
* automate everything

![Important](http://i.imgur.com/HcVYw.jpg)
