<?php
namespace Lt\Modules\MdLt\Services;

use DbConnect;
use Lt\Modules\MdLt\Services\LtResponse;


class LtService
{
    
    public static function getIsValue($raw)
    {
        if ($raw === null) {
            return null; // no filter
        }

        $val = strtolower((string)$raw);

        if (in_array($val, ['1','true','yes','on'], true)) {
            return 1;
        }

        if (in_array($val, ['0','false','no','off'], true)) {
            return 0;
        }

        return null; // invalid or unknown
    }
    
     public static function generateLtId($length = 12)
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'),0,$length);
    }
    
     public static function alterTableColumn($tables, $column, $type = 'TINYINT(1)', $default = 0) {
        $sqlConnect = DbConnect::dbDriver();
        $results = [];
    
        foreach ($tables as $table) {
            try {
                // Use backticks for identifiers
                $colCheck = $sqlConnect->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
                if ($colCheck->rowCount() > 0) {
                    continue;
                }
                $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $type DEFAULT $default";
                $sqlConnect->exec($sql);
                $results .= LtResponse::json("Column '$column' added successfully to '$table'", '201', '200');
            } catch (PDOException $e) {
                $results .= LtResponse::error("Error modifying '$table': " . $e->getMessage(), '101', '100');
            }
        }
    
        return $results;
    }
    
    public static function dropTableColumn($tables, $column){
        $sqlConnect = DbConnect::dbDriver();
        $results = [];
        foreach($tables as $table){
            $sql = "ALTER TABLE `$table` DROP COLUMN `$column`";
                $sqlConnect->exec($sql);
                $results .= LtResponse::json("Column '$column' added successfully to '$table'", '201', '200');
        }
        
        return $results;
        
    }
}
