<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ConnectionHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class ConnectionHelperTest extends UnitTestCase
{
    public static function connections(): array
    {
        return [['central', 'mysql', 'central'], ['', 'mysql', 'pgsql'], [null, 'mysql', 'pgsql'],
            [false, 'mysql', 'pgsql'], [42, 'mysql', 'pgsql']];
    }

    #[DataProvider('connections')]
    public function test_connection_and_table_use_valid_connection_or_fallback(mixed $central, string $default, string $expected): void
    {
        config(['tenancy.database.central_connection' => $central, 'database.default' => $default]);
        $this->assertSame($expected, ConnectionHelper::centralConnection());
        $this->assertSame($expected.'.roles', ConnectionHelper::centralTable('roles'));
    }

    public function test_missing_configuration_uses_database_default(): void
    {
        config(['tenancy.database' => [], 'database.default' => 'mysql']);
        $this->assertSame('mysql', ConnectionHelper::centralConnection());
    }
}
