<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect;
use PDO;

class tableManagementService
{
        protected $request;
        protected $userId;
        protected $ltNow;
        public $sqlConnect;
        
        public function __construct(){
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
            $this->sqlConnect = DbConnect::dbDriver();
        }
        
        
        public function checkTableExist($tableName){
            $stmt = $this->sqlConnect->prepare("SHOW TABLES LIKE :tablename");
            $stmt->execute([':tablename' => $tableName]);
            $result = $stmt->fetch(\PDO::FETCH_NUM);
            
            return $result !== false;
            
        }
        
        
        
        public function indexTable(){
            $tableRecord = [];
            $sql = "SHOW TABLES LIKE '%tb%_'";
            $stmt = $this->sqlConnect->query($sql);
            $result = $stmt->fetchAll(\PDO::FETCH_NUM);
            
            foreach ($result as $r) {
                $tableName = $r[0];
            
                // Get table status info
                $statusStmt = $this->sqlConnect->query("SHOW TABLE STATUS WHERE Name = '{$tableName}'");
                $status = $statusStmt->fetch(\PDO::FETCH_ASSOC);
            
                $tableRecord[] = [
                    'id'   => ltId(),
                    'tableName'   => $tableName,
                    'recordCount' => $status['Rows'],
                    'size'        => round(($status['Data_length'] + $status['Index_length']) / 1048576, 2) . ' MB',
                    'engine'      => $status['Engine'],
                    'collation'   => $status['Collation'],
                    'created_at'  => $status['Create_time'],
                    'updated_at'  => $status['Update_time'],
                ];
            }
            
            return LtResponse::json("Tables successfully", '3309', '200', $tableRecord);
        }
        
        public function browseTable(){
            $tableName = $this->request->tableName;
            
            $limit = isset($this->request->limit) ? (int)$this->request->limit : 10;
            $page = isset($this->request->page) ? (int)$this->request->page : 1;
            // $search = isset($this->request->search) ? $this->request->search : '';
            
            if(empty($tableName)) return;
            
            $offset = ($page - 1) * $limit;
            // Fetch total number of record for the user
            $count = $this->sqlConnect->prepare("SELECT COUNT(*) AS total FROM $tableName");
            $count->execute();
            $totalRecords = $count->fetch(PDO::FETCH_OBJ)->total;
            
            
            $stmt = $this->sqlConnect->prepare("SELECT * FROM $tableName ORDER BY lt_id LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            
             $stmt->execute();
             $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if(empty($result)) return LtResponse::json("No Record Found For table {$tableName}", 101, 100);
            
            return LtResponse::json("Tables successfully", '3309', '200', ['data' => $result, 'total' => $totalRecords]);
        }
        
        
        // public function browseTable(){
        //     $tableName = $this->request->tableName;
            
        //     if(empty($tableName)) return;
            
        //     $stmt = $this->sqlConnect->prepare("SELECT * FROM $tableName");
        //     $stmt->execute();
        //     $result = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
        //     if(empty($result)) return LtResponse::json("No Record Found For table {$tableName}", 101, 100);
        //     return LtResponse::json("Tables successfully", '3309', '200', $result);
        // }
        
        public function tableStructure(){
            $tableName = $this->request->tableName;
            
            if(empty($tableName)) return;
            
            $stmt = $this->sqlConnect->prepare("DESCRIBE `$tableName`");
            $stmt->execute();
            $structure = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            if(empty($structure)) return LtResponse::json("No structure Found For table {$tableName}", 101, 100);
            return LtResponse::json("Fetched successfully", '3309', '200', $structure);
        }
        
        public function truncateTable(){
            $tableName = $this->request->tableName;
            
            if(empty($tableName)) return;
            
            $stmt = $this->sqlConnect->prepare("TRUNCATE TABLE `$tableName`");
            $stmt->execute();
            $stmt->fetchAll(\PDO::FETCH_OBJ);

            return LtResponse::json("Table {$tableName} truncated successfully", '3309', '200');
        }
        
        public function dropTable(){
            $tableName = $this->request->tableName;
            
            if(empty($tableName)) return;
            
            try {
                $stmt = $this->sqlConnect->prepare("DROP TABLE `$tableName`");
                $stmt->execute();
        
                return LtResponse::json("Table {$tableName} dropped successfully", 3309, 200);
            } catch (\PDOException $e) {
                return LtResponse::error("Error dropping table: " . $e->getMessage(), 500, 101);
            }

        }
 
        public function deleteTableRecord(){
            $tableField = $this->request->tableField;
            $tableFieldValue = $this->request->tableFieldValue;
            $tableName = $this->request->tableName;
            
            if(empty($tableField) || empty($tableFieldValue) || empty($tableName)) return;
            
            $stmt = $this->sqlConnect->prepare("DELETE FROM $tableName WHERE `$tableField` = :tbFieldValue");
            $stmt->execute([':tbFieldValue' => $tableFieldValue]);
            
            // if(empty($result)) return LtResponse::json("No Record Found For table {$tableName}", 101, 100);
            return LtResponse::json("Tables Delete successfully", '3309', '200');
        }
        
        public function createTable(){
            $tableName = $this->request->tableName;
            $tableField = $this->request->fields;
            //check if table name is not empty
            if(empty($tableName))  return LtResponse::json("Table Name Must not be empty", '3309', '200');
            /// check for existence of the table name
            if($this->checkTableExist($tableName)) return LtResponse::json("A table with this name {$tableName} already exists.", 3309, 100);
            // validate table
            if(empty($tableField) || !is_array($tableField)) return LtResponse::json("Table Field must be non-empty array", 3309, 100);
            // check for if tbale field consist of duplicate entry
            $fieldNames = array_map(fn($f) => $f['Field'], $tableField);
            if(count($fildNames) !== array_unique($tableField)) return LtResponse::json("Field names must be unique.", 3309, 100);
                
 
        }
        
        public function sqlExecute(){
            $sqlQuery = trim($this->request->sqlQuery);
            
            if(empty($sqlQuery)){
                return LtResponse::json("Sql query can not be empty", 3309, '100');
            }
        
            try {
        
                $stmt = $this->sqlConnect->prepare($sqlQuery);
                $stmt->execute();
        
                $result = [];
        
                if (preg_match('/^\s*SELECT/i', $sqlQuery)) {
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $result = [
                        'affectedRows' => $stmt->rowCount()
                    ];
                }
            
                // // 🧾 3. Log execution
                $sqlJson = json_encode($sqlQuery);
                $sqlInsert = "INSERT INTO tb_sql_executor_log (lt_id, sql_query, updated_by, created_at) VALUES (:id, :sqlQuery, :updatedBy, :createdAt)";
                
                $stmtInsert = $this->sqlConnect->prepare($sqlInsert);
                $stmtInsert->execute([
                    ":id" => ltId(),
                    ":sqlQuery" => $sqlQuery,
                    ":updatedBy" => $this->userId,
                    ":createdAt" => $this->ltNow
                    ]);
                
                return LtResponse::json('success', 3309, 200, $result);
        
            } catch (Throwable $e) {
                return LtResponse::error($e->getMessage(), 3309, 100);
            } 
        }
        
        
} 