# Ion Auth 2 with Codeigniter integration
This is a sample implementation of an Authentication Controller with Ion Auth, which can also dictate the layout theme that would display. 


## Dependencies
- ci-migrate (https://bitbucket.org/yogibear54/ci-migrate)
- ci-layout (https://bitbucket.org/yogibear54/ci-layout)
- Ion Auth (https://github.com/benedmunds/CodeIgniter-Ion-Auth)


## Installation Guide

1. Install ion auth files from (https://github.com/benedmunds/CodeIgniter-Ion-Auth)
2. Install ci-migrate (https://bitbucket.org/yogibear54/ci-migrate)
3. Install ci-layout (https://bitbucket.org/yogibear54/ci-layout)
4. Setup and run migration for Ion Auth
    - Open application/config/database.php and add in your database connection information here
    - Open application/config/migration.php
        - Make sure migration is enabled i.e. `$config['migration_enabled'] = TRUE;`
        - Make sure migration type is sequential i.e. `$config['migration_type'] = 'sequential';`
        - Depending on your current application, increment the migration version by 1.  i.e. `$config['migration_version'] = 3;`
        - if migration version is greater than one, rename file application/migrations/001_install_ion_auth.php to the next migration number i.e. 003_install_ion_auth.php
        - Run the migration, open bash shell, and in the site root type the following to perform a migration:  `php index.php migrate/now`
5. Update autoload configuration file: application/config/autoload.php
    - Add autoload libs for layout and auth: `$autoload['libraries'] = array('session', 'ion_auth', 'database', 'layout');`
    - Add url helper: `$autoload['helper'] = array('url');`
    - Add layout config: `$autoload['config'] = array('layout');`
    - Add ion auth lang: `$autoload['language'] = array('ion_auth');`





