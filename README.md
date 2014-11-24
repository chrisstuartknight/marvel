MarvelAPI to DatasiftAPI Processing
===================================

The code should run without installing too much in the way of dependencies as most has already been taken care of via composer, and barring cURL the rest is native to PHP. It's arguable I got a *little* carried away here as halfway through... I found PHPUnit, and started having a little play with that. Interestingly, that is also one of the only times I've ever found a use for require over require_once! 

If I'd have had some more time, I'd do some more PHPUnit tests, but I suspect you're interested if I code like a total mentalist. Either way, I've actually found this pretty good fun. I decided to separate it into two sections, so I can generate a data file, and then process that when I require, saving a bit of processing time. Was quite impressed the delay on the API seemed to be under six seconds when I was testing it.

A configuration file is found at config/config.php. You may alter these to different credentials, you can open up other configuration options also in increase amount of characters returned, or disable the limit all together. 

For want of clarity, I've assumed no prior installations. Tested with PHP 5.3 and PHP 5.5

Installation on Ubuntu
----------------------
- sudo apt-get update
- sudo apt-get install php5 php5-curl git (this totally unnecessarily installs apache)
- cd ~
- git clone https://github.com/chrisstuartknight/marvel.git
- cd marvel
- php -f composer.phar update (not required)

Generate Datasift Data
----------------------
- php -f job.php > [output]

Generate Results
----------------
Note, these options are not required, all are in the config
- php -f process.php [data] [output] [mode]

Run Tests
---------
- vendor/bin/phpunit tests