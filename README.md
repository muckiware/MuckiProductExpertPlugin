# MuckiProductExpertPlugin

## Requirements
- Shopware 6.6.x
- PHP 8.2.x or greater

## Installation
```shell
bin/console plugin:install -a MuckiProductExpertPlugin
```

# Testing
## phpstan
### Install
Install phpstan, if required
```shell
cd custom/plugin/MuckiProductExpertPlugin
composer install
```
### Execute
```shell
cd custom/plugins/MuckiProductExpertPlugin 
composer run-script phpstan
```
## Unit test
### Execute first time
```shell
./vendor/bin/phpunit --configuration="custom/plugins/MuckiProductExpertPlugin" --testsuite "migration"
```

### Execute regular run
```shell
./vendor/bin/phpunit --configuration="custom/plugins/MuckiProductExpertPlugin"
```
