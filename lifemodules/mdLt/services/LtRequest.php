<?php   
class LtRequest
{
    public $method;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        switch ($this->method) {
            case 'POST':
                $this->loadData($_POST);
                break;
            case 'GET':
                $this->loadData($_GET);
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $this->loadRawInput();
                break;
        }
    }

    private function loadData(array $source)
    {
        foreach ($source as $key => $value) {
            $this->$key = $value;
        }
    }

    private function loadRawInput()
    {
        $input = file_get_contents('php://input');

        // Try to parse as JSON first
        $data = json_decode($input, true);
        //var_dump($data);
        //echo "trying on this page".$data.' lkk';
        if (is_array($data)) {
            $this->loadData($data);
        } else { 
            parse_str($input, $parsed);  
            $this->loadData($parsed);
        }
        
        foreach (LtRequestPlaceholder::all() as $key => $value) {
             $this->$key = $value;
        }
    }
    
    public function validateRequest(array $rules = []) { 
 // Check if data is empty, meaning use LtRequest
            if (empty($rules)) {
                 return LtResponse::json("Your data should not be empty");
            }
        // Convert all public properties (except 'method') into an array
            $requestData = get_object_vars($this);
            unset($requestData['method']);
            return LtValidator::validate($requestData, $rules); 
    }
    
}
?>    