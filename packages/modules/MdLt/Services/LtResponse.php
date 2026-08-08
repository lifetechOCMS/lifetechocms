<?php
namespace Lt\Modules\MdLt\Services;


class LtResponse 
{   
    public static string $defaultProductionErrorMessage = "Something went wrong. Please try again later.";

    public static function json($responseResult = "Unknown", $responseCode = "101", $responseCategory = "100", $responseData = [], $responseStatus = "fail", $responseOperationType = "others"  ) 
    {
        if ($responseResult === null || $responseResult === '') {
            $responseResult = "Unknown";
        }

        $payload = [
            'responseResult' => $responseResult,
            'responseCode' => $responseCode,
            'responseCategory' => $responseCategory
        ];

        if ($responseData !== [] && $responseData !== null) {
            $payload['responseData'] = $responseData;
        }

        return json_encode($payload);
    }
 

    public static function error($responseResult = "Unknown Error",$responseCode = "1043", $responseCategory = "100",$responseData = [], $responseStatus = "fail", $responseOperationType = "others")
    {
        $env = defined('LT_ENV') ? LT_ENV : 'development';  

        if ($env === "production") {
            self::writeLog($responseResult);
            return self::json(
                self::$defaultProductionErrorMessage ,
                "1043",
                "100"
            );
        } 
        return self::json(
            $responseResult,
            $responseCode,
            $responseCategory,
            $responseData,
            $responseStatus,
            $responseOperationType
        );
    }

    public static function writeLog($message, $context = [])
    {
        // Define log directory
        $logDir = defined('LT_LOG_PATH') 
            ? LT_LOG_PATH 
            : __DIR__ . '/logs';
            
           // exit();
        // Create directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Create daily log file
        $logFile = $logDir . '/Error-' . date('Y-m-d') . '.log';

        // Unique reference ID
        $logRef = uniqid('ERR_');

        // Build log entry
        $entry  = "[" . date('Y-m-d H:i:s') . "] ";
        $entry .= "[" . $logRef . "] ";

        // Handle message (string or array)
        if (is_array($message)) {
            $entry .= json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $entry .= $message;
        }

        // Add context if available
        if (!empty($context)) {
            $entry .= " | Context: " . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $entry .= PHP_EOL; 
        // Write to file (append mode + lock)
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        return $logRef;
    }
    
} 