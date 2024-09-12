DataTableBundle Demo Application
========================

This project is the official [DataTableBundle][1] Demo application that showcases the main features of the bundle.

It is a fork of the [Symfony Demo application][2].

Requirements
------------

  * PHP 8.2.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][3].

Installation
------------

[Download Composer][5] and use the `composer` binary installed on your computer to run these commands:

```bash
composer create-project kreyu/data-table-bundle-demo my_project
cd my_project/
composer install
```

Usage
-----

There's no need to configure anything before running the application.
If you have [installed the Symfony CLI][4], run this command:

```bash
cd my_project/
symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/` to use the built-in 
PHP web server or [configure a web server][3] like Nginx or Apache to run the application.

Tests
-----

Execute this command to run tests:

```bash
cd my_project/
./bin/phpunit
```

[1]: https://github.com/Kreyu/data-table-bundle
[2]: https://github.com/symfony/demo
[3]: https://symfony.com/doc/current/setup.html#technical-requirements
[4]: https://symfony.com/doc/current/setup/web_server_configuration.html
[5]: https://getcomposer.org/
