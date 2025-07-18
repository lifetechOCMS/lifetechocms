<?php 
ltImport('mdLt', 'LtLWToken.php');

class LtAuth
{
    private static $life_user;

    public function __construct()
    {
        self::$life_user = new Tb_registrations;
    }

    public static function hidInitiate()
    {
        self::$life_user = new Tb_registrations;
    }

    public static function activateUser($lifetech_general_id)
    {
        self::hidInitiate();
        $result = self::$life_user->select()->where('user_id', '=', $lifetech_general_id)->get();
        $user = $result[0] ?? null;

        if (!$user) {
            return LtResponse::json("Record Not Found", "103", "100");
        }

        $id = $user->user_id;
        self::setLtSession($user, $id);
        //get api tokens timeout parameter
        $toGetapiTokenExpiredTimeOut = ApiTokenDetails::apiTokenExpiredTimeOut();
        LtLWToken::insertLwtToken($id, $user->role_encrypt,$toGetapiTokenExpiredTimeOut);
    }

    public static function login($username, $password = "")
    {
        self::hidInitiate();

        if ($password === "") {
            $result = self::getUserByUsernameOrEmail($username);
            $response = count($result) === 1 ? $result[0]->lifetech_general_id : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "211" : "103", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($username, $password, 'getUserByUsernameOrEmail');
    }

    public static function loginWithEmail($email, $password = "")
    {
        self::hidInitiate();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return LtResponse::json("Email Not Valid", "102", "100");
        }

        if ($password === "") {
            $result = self::getUserByEmail($email);
            $response = count($result) === 1 ? $result[0]->lifetech_general_id : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "211" : "103", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($email, $password, 'getUserByEmail');
    }

    public static function loginWithUsername($username, $password = "")
    {
        self::hidInitiate();

        if ($password === "") {
            $result = self::getUserByUsername($username);
            $response = count($result) === 1 ? $result[0]->lifetech_general_id : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "212" : "103", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($username, $password, 'getUserByUsername');
    }

    private static function handleLoginAttempt($input, $password, $getter)
    {
        $input = trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        $password = trim(htmlspecialchars($password, ENT_QUOTES, 'UTF-8'));

        $users = self::$getter($input);
        
        if (!$users) {
            return LtResponse::json("Record Not Found", "115", "100");
        }
        
        foreach ($users ?? [] as $user) {
            if ($user->activation_status != 1) {
                return LtResponse::json("Account Deactivated", "115", "100");
            }

            if (LtDdm::verifyPassword($user, $password)) {
                self::internalActivateUser($user);
                $user->lw_token = LtSession::get('lw_token');
                return LtResponse::json("Login Succesful", "210", "200",$user);
            }
        }
        return LtResponse::json("Incorrect Password", "101", "100");
    }

    private static function getUserByUsernameOrEmail($username)
    {
        return self::$life_user->select()->where('lifetech_username', '=', $username, '(')
            ->orWhere('lifetech_email', '=', $username, '', ')')->get();
    }

    private static function getUserByEmail($email)
    {
        return self::$life_user->select()->where('lifetech_email', '=', $email)
            ->andWhere('activation_status', '=', '1')->get();
    }

    private static function getUserByUsername($username)
    {
        return self::$life_user->select()->where('lifetech_username', '=', $username)
            ->andWhere('activation_status', '=', '1')->get();
    } 
    public static function getFormRole($catname)
    {
        $categories = new Category;
        $query = $categories->select()->where('categoryname', '=', $catname)->get();

        if (count($query) > 0) {
            return json_decode(json_encode([
                'encryptRole' => $query[0]->categoryrole,
                'loginStatus' => $query[0]->loginStatus
            ]));
        }

        $encryptRole = md5(3);
        $categories->categoryname = $catname;
        $categories->categoryrole = $encryptRole;
        $categories->loginStatus = 1;
        $categories->insert();

        return json_decode(json_encode([
            'encryptRole' => $encryptRole,
            'loginStatus' => 1
        ]));
    }

    public function loginAnalysis() {}

    public static function user()
    {
        self::hidInitiate();

        if (!LtSession::has('user_id')) {
            return LtResponse::json("Record not found", "103", "100");
        }

        $id = LtSession::get("user_id");
        return LtResponse::json([
            'user_id' => $id,
            'surname' => LtSession::get("{$id}_surname"),
            'firstname' => LtSession::get("{$id}_firstname"),
            'othername' => LtSession::get("{$id}_othername"),
            'email' => LtSession::get("{$id}_email"),
            'phone' => LtSession::get("{$id}_phone"),
            'role' => LtSession::get("{$id}_role"),
            'username' => LtSession::get("{$id}_username"),
            'hash' => LtSession::get("{$id}_hash"),
            'salt' => LtSession::get("{$id}_salt")
        ], "210", "200");
    }

    public static function check()
    {
        return LtSession::has('user_id');
    }

    public static function logout()
    {
        if (LtSession::has('user_id')) {
            LtSession::forget('user_id');
            LtSession::destroy();
            return true;
        }
        return false;
    }

    private static function setLtSession($user, $id)
    {
        LtSession::set('user_id', $id);
        LtSession::set("{$id}_email", $user->lifetech_email);
        LtSession::set("{$id}_firstname", $user->lifetech_firstname);
        LtSession::set("{$id}_othername", $user->lifetech_othername);
        LtSession::set("{$id}_phone", $user->lifetech_phone_number);
        LtSession::set("{$id}_role", $user->role_encrypt);
        LtSession::set("{$id}_surname", $user->lifetech_surname);
        LtSession::set("{$id}_username", $user->lifetech_username);
        LtSession::set("{$id}_salt", $user->salt);
        LtSession::set("{$id}_hash", $user->lifetech_password);
    }

    static function internalActivateUser($user)
    {
        $user_id = $user->user_id ?: $user->lifetech_general_id;
        $key = $user->user_id ? 'user_id' : 'lifetech_general_id';

        self::setLtSession($user, $user_id);
        LtSession::set('lifetech_urid', $user->role_encrypt);
        $toGetapiTokenExpiredTimeOut = ApiTokenDetails::apiTokenExpiredTimeOut();
        LtLWToken::insertLwtToken($user_id, $user->role_encrypt,$toGetapiTokenExpiredTimeOut);
        self::updateLastAccess($user_id, $key);
    }

    public static function updateLastAccess($id, $key="")
    {
        $record = new Tb_registrations;
        $record->last_access = date("Y-m-d H:i:s");
        $record->update($id, $key);
    }

    public static function loginWithId($id, $password)
    {
        self::hidInitiate();

        $id = trim(htmlspecialchars($id, ENT_QUOTES, 'UTF-8'));
        $password = trim(htmlspecialchars($password, ENT_QUOTES, 'UTF-8'));

        $user = self::$life_user->findId($id);
        if (LtDdm::verifyPassword($user, $password)) {
            self::internalActivateUser($user);
            return LtResponse::json($user, "210", "200");
        }

        return LtResponse::json("Incorrect Password", "101", "100");
    }

    
}
?>
      
      