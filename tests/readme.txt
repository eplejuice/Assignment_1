Test code for assignment #1 i IMT2571 Data Modelling and Database Systems.

These are tests for the book collection in assignment 1. To run these tests, you need to install PHPUnit and the Mink source browser controller/emulator for web applications that is written in PHP. To install Mink, you also need the PHP Composer. 

How to set it all up:

1. Install PHPUnit as explained here: https://phpunit.de/manual/current/en/installation.html, but name the resulting phpunit file phpunit6 to avoid conflicts with potentially other installed versions of PHPUnit.
2. Install the Composer as explained here: https://getcomposer.org/download/
3. Go to the tests directory (where this readme file is stored)
4. Install PHPUnit, DBUint, Mink, and the Mink Goutte driver by using Composer
   composer require --dev phpunit/phpunit ^6.2 phpunit/dbunit behat/mink behat/mink-goutte-driver
   
... and you should be ready to go.

Run the tests by opening a shell/command window. Go to the tests directory. Run PHPUnit:
   phpunit6 UnitTests.php
   phpunit6 FunctionalTests.php

You should run the unit tests whenever you add or modify code in the model class and you should run the functional tests when you all the unit tests are passed.

The unit tests assume that you are using a database named test containing a table named Book with attributes id, title, author, and description. These names appear in both the UnitTestFixtures.php and in UnitTests.php.