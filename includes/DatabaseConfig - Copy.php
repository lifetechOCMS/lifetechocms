<?php 
        class DatabaseConfig
	    {
	        public static function get(): array
	        {
	            return [
	                'host' => getenv('LT_DB_HOST') ?: "",
	                'user' => getenv('LT_DB_USER') ?: "",
	                'pass' => getenv('LT_DB_PASS') ?: "",
	                'name' => getenv('LT_DB_NAME') ?: "",
	            ];
	        }
	    }
     