<?php 
    class DatabaseConfig
    {
        public static function get(): array
        {
            return [
                'host' => getenv('LT_DB_HOST') ?: "localhost",
                'user' => getenv('LT_DB_USER') ?: "root",
                'pass' => getenv('LT_DB_PASS') ?: "",
                'name' => getenv('LT_DB_NAME') ?: "hubs",
            ];
        }
    }
     