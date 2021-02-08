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

## Notes - MySQL

- HOW TO PROCESS MONTHLY MARKET STATS

1. CHROME EXTENSION OF STATS CENTER, FETCHING MONTHLY DATA TO TABLE `wp_pid_market`
2. CHROME EXTENSION OF STATS CENTER, SET CURRENT REPORT YEAR AND MONTH TO TABLE `wp_pid_stats_date_pointer`

```
 pointer_id 1 : current date
 pointer_id 2 : previous date
 pointer_id 3 : start date
```

3. GENERATE PIVOTAL DATA BY MySQL Workbench Query, INSERT NEW DATA TO TABLE `wp_pid_market_pivot`

```SQL
  INSERT INTO wp_pid_market_pivot (`date`, neighborhood_id, data_type, townhouse, `all`, apartment, detached)
  SELECT
    `p`.`Date` AS `date`,
    `p`.`Neighborhood_ID` AS `neighborhood_id`,
    "HPI" AS `data_type`,
    MAX((CASE
      WHEN (`p`.`Property_Type` = 'Townhouse') THEN `p`.`HPI`
      ELSE NULL
    END)) AS `Townhouse`,
    MAX((CASE
      WHEN (`p`.`Property_Type` = 'All') THEN `p`.`HPI`
      ELSE NULL
    END)) AS `All`,
    MAX((CASE
      WHEN (`p`.`Property_Type` = 'Apartment') THEN `p`.`HPI`
      ELSE NULL
    END)) AS `Apartment`,
    MAX((CASE
      WHEN (`p`.`Property_Type` = 'Detached') THEN `p`.`HPI`
      ELSE NULL
    END)) AS `Detached`
  FROM
    `wp_pid_market` `p`
  WHERE
    ((MONTH(`p`.`Date`) = WP_PID_CUR_REPORT_MONTH())
      AND (YEAR(`p`.`Date`) = WP_PID_CUR_REPORT_YEAR()))
  GROUP BY `p`.`Neighborhood_ID`;
```

4. CHECK WPDATATABLE 29 FOR GREATER VANCOUVER ALL CITIES

```sql
  view `wp_pid_market_latest` // get the latest monthly hpi stats
  view `wp_pid_hpi_grouped` // pivot the stats grouped by City
```

5. CHECK WPDATATABLE 45 FOR GVA SUB AREAS

6. CHECK GVA NBHS SINGLE HOUSE MONTHLY HPI TABLE - WPDATATABLE 38

```sql
  post 大温哥华地区各居民社区独立屋基准房价月度涨跌幅排名表 WDT 38
  view `wp_pid_view_gva_nbh_hpi_report_data` // new view created on 2021-02-07
  view `wp_pid_view_gva_nbh_hpi_report_wrapper` // new view created on 2021-02-07
```

7. CHECK GVA CITY SINGLE HOUSE MONTHLY HPI TABLE - WDT 40

```sql
  post 大温哥华地区各城市,区域独立屋基准房价月度涨跌幅排名表 WDT 40
  view `wp_pid_view_gva_nbh_hpi_report_data` // 2021-02-07
  view `wp_pid_view_gva_cities_hpi_report_wrapper` // 2021-02-07
```
