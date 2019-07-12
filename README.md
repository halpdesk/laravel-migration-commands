# Laravel Migration Commands

This package provides commands for migrating files in a Laravel project, which are useful at developing time.

By halpdesk, 2019-07-22

## List of Commmands

### Migrate all tables

**`migrate:all`**

Rolls back all migration files and migrates them again

> To seed the database after migration is finished, use the `--seed` option.

### Migrate specific file

**`migrate:specific --file=`** `<file>`

Migrate one specific file. Does not take regard to order of migration files. The `--file` parameter is required and takes a full (absolute) path.

> To instead rollback the migration, use the the `--rollback` option.

### Drop all tables

**`migrate:dropall`**

Disables all foreign key constraints and drops all tables. This is useful when  when a migration file is renamed or the order is changed during development. The user is not prompted with a choice to continue before proceeding as well as a count down which can be cancelled with CTRL+C.

> To escape the prompt and countdown, add the `--force` option.

### Re-seed database

**`db:reseed`**

Disables all foreign key constriants, truncates all tables and seeds all tables

> To use another class instead of DatabaseSeeder, use the `class` option
