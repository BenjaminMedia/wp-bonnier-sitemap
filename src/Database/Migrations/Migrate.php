<?php

namespace Bonnier\WP\Sitemap\Database\Migrations;

use Bonnier\WP\Sitemap\Database\Exceptions\MigrationException;

class Migrate
{
    const OPTION = 'bonnier-sitemap-migration';
    const TABLE = 'bonnier_sitemap';

    public static function run()
    {
        $dbVersion = intval(get_option(self::OPTION) ?: 0);
        $migrations = collect([
            CreateSitemapTable::class,
        ]);

        if ($dbVersion >= count($migrations)) {
            return;
        }

        $migrations->each(function (string $migration, int $index) use ($dbVersion) {
            $migrationReflection = new \ReflectionClass($migration);
            if (!$migrationReflection->implementsInterface(Migration::class)) {
                throw new MigrationException(
                    sprintf('The migration \'%s\' does not implement the Migration interface', $migration)
                );
            }
            if ($index < $dbVersion) {
                return;
            }
            /** @var Migration $migration */
            $migration::migrate();
            if ($migration::verify()) {
                update_option(self::OPTION, $index + 1);
            } else {
                throw new MigrationException(sprintf('An error occured running the migration \'%s\'', $migration));
            }
        });
    }
}
