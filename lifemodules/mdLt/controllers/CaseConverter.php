<?php

class CaseConverter {

    // snake_case → camelCase
    public static function snakeToCamel($string) {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    // PascalCase → snake_case
    public static function pascalToSnake($string) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    // camelCase → snake_case
    public static function camelToSnake($string) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    // snake_case → PascalCase
    public static function snakeToPascal($string) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    // PascalCase → camelCase
    public static function pascalToCamel($string) {
        return lcfirst($string);
    }

    // camelCase → PascalCase
    public static function camelToPascal($string) {
        return ucfirst($string);
    }
    
    //snake to camel case from database records
    public static function toCamelCaseArray(array $data): array {
        return array_map(function ($row) {
            $newRow = [];
            foreach ($row as $key => $value) {
                $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                $newRow[$camelKey] = $value;
            }
            return $newRow;
        }, $data);
    }
    
    //snake to camel case from database records
    public static function arrayToCamelCaseArray(array $data): array {
        return array_map(function ($row) {
            $newRow = [];
            foreach ($row as $key => $value) {
                $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                $newRow[$camelKey] = $value;
            }
            return $newRow;
        }, $data);
    }
    public static function arrayToCamelCase(array $data): array {
        return array_map(function ($row) {
            $newRow = new stdClass();
            foreach ($row as $key => $value) {
                $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                $newRow->$camelKey = $value;
            }
            return $newRow;
        }, $data);
    }
} 
?>   
      