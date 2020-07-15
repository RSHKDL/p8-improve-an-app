#8 ToDoAndCo
========

ToDoAndCo is a Symfony 3.4 project built during my [web development learning path](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony) with OpenClassrooms.

This project is a very basic **To do list**. The goal was to improve upon an existing project.

## About
### Back-end

* Symfony 3.4
* Doctrine

### Friendly with

* PSR-1, PSR-4, PSR-12
* Symfony Best Practices (mostly)

## Install

1. Clone or download the repository `git@github.com:RSHKDL/p8-improve-an-app.git` into your environment.
2. Change the files **app/config/parameters.yml** and with your own data.
3. Install the database and optionally inject the fixtures:
    ```
    $ bin/console doctrine:database:create
    $ bin/console doctrine:schema:create
    $ bin/console doctrine:fixtures:load
    ```

## Test the application

1. You'll need to register to create tasks.
2. Alternatively, you can create an **admin account** by running this command:
    ```
    $ bin/console app:admin-create
    ```
3. You can also use the following command to purge anonymous tasks (tasks that remain when their user is deleted).
    ```
    $ bin/console app:purge-tasks
    ```

## Tests

- Test are done with [Phpunit](https://phpunit.readthedocs.io/en/8.3/) only.
- Use the following command to run all the tests:
    ```
    $ ./vendor/bin/simple-phpunit
    ```

## Contributing

If you want to contribute, see the file **CONTRIBUTING.md** first.
