# Covid Vaccination Centres

A drupal 9 based site for booking of vaccine slots.

## Requirement

- Drupal 9 with php7.3 or higher.
- Dependency is managed via composer 2.
- For more detailed info kindly follow [this](https://www.drupal.org/docs/understanding-drupal/how-drupal-9-was-made-and-what-is-included/environment-requirements-of).
- (Optional) Local development setup is done by [ddev](https://github.com/abhisekmazumdar/vaccination-centres/blob/main/.ddev/config.yaml).

## Local Development

If using ddev
```bash
ddev start
```
And import the database from [here](https://github.com/abhisekmazumdar/vaccination-centres/blob/main/database).
```bash
ddev composer install
```
Unzip, coopy and paste file folder to `web/sites/default` from [here](https://github.com/abhisekmazumdar/vaccination-centres/tree/main/database)

## Authors

This site is developed by [Abhisek Mazumdar](http://abhisek.xyz/)
