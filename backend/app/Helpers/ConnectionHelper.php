<?php

namespace App\Helpers;

class ConnectionHelper
{
    public static function centralTable(string $table): string
    {
        return self::centralConnection().'.'.$table;
    }

    public static function centralConnection(): string
    {
        $connection = config('tenancy.database.central_connection', config('database.default'));
        return is_string($connection) && $connection !== '' ? $connection : 'pgsql';
    }
}
