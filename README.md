# PIDHomes Website

> Help Buyers and Sellers to make Real Estate Decision

## Notes - Composer

- every php component should have a composer.json file
  For example:
  ```json
  {
    "name": "pidhomes/pidhomes",
    "type": "library",
    "description": "PIDHomes Classes, Functions & Constants Library Modules",
    "license": "MIT",
    "authors": [
      {
        "name": "Peter Qu",
        "email": "pqu007@gmail.com"
      }
    ],
    "require": {
      "php": ">=5.3.3"
    },
    "autoload": {
      "psr-4": {
        "PIDHomes\\": ""
      },
      "files": ["PIDConstants.php"]
    }
  }
  ```
- vendor folder should have a component folder, the composer.json file should be kept under the folder.
- composer should have a installed.json file, the customized component's composer.json file will be copy to this file as a json section
- After having change the composer.json and installed.json, new autoload.php file should be created:

  1. cd directory
     `$ cd C:\\wamp64\\www\\PIDRealty4\\wp-content\themes\realhomes-child-3`
  2. regenerate autoload.php file

  `$ composer dump-autoload `

  3. change file name to pid-autoload.php

- Composer autoload psr-4 only load classes
- Composer autoload psr-4 does not load functions and constants

- functions and constants in the same namespace have to be loaded by "files" option in the json file
  For Example:

```json
{
  "autoload": {
    "psr-4": {
      "PIDHomes\\": ""
    },
    "files": ["PIDConstants.php"]
  }
}
```
