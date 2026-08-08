<?php 
ob_start(); 
//getting information about the required functions
$maxUpload      =  (ini_get('upload_max_filesize'));
$maxPost        =  (ini_get('post_max_size'));
$maxtime      = (ini_get('max_execution_time')); 
function phpSizeToBytes($size)
{
    $size = trim($size);
    $unit = strtolower(substr($size, -1));
    $value = (float) $size;

    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return (int) $value;
}
$minimum = 40 * 1024 * 1024; // 40 MB

$maxUploadNew      = phpSizeToBytes(ini_get('upload_max_filesize'));
$maxPostNew        = phpSizeToBytes(ini_get('post_max_size'));
$maxtimeNew      = (ini_get('max_execution_time'));


$keys = [];

if ($maxUploadNew < $minimum){  
    $Res_maxUpload = false;
    $keys['Upload_Max_Size'] = 'Upload max size of above 40M';
 }else{$a=2;
    $Res_maxUpload = true;
    unset($keys['Upload_Max_Size']);
 }

if ($maxPostNew < $minimum){ 
    $Res_maxPost = false;
    $keys['Post_Max_Size'] = 'Post max size of above 40M';
 }else{$b=3;
    $Res_maxPost = true; 
     unset($keys['Post_Max_Size']);
 }
if ($maxtime < 120){ 
    $Res_maxtime =false; 
    $keys['Max_Execution_Time'] = 'Execution Time of above 120sec';
 }else{$c=4;
    $Res_maxtime = true;
    unset($keys['Max_Execution_Time']);
 }

    






//Step 2 setup


if(isset($_POST['dbinfo'])){
  //echo 'welcome';
  $host = $_POST['hostname'];
  $username =  $_POST['username'];
  $password = $_POST['password'];
  $dbname = $_POST['dbBase'];
  $errror =""; 
  



  function isHttps()
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
    );
}

function ltScheme()
{
    return isHttps() ? 'https' : 'http';
}

function ltHost()
{
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

    // remove port if HTTP_HOST contains one, e.g localhost:8080
    return explode(':', $host)[0];
}

function ltPort()
{
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';

    // If HTTP_HOST has port, use it
    if (strpos($httpHost, ':') !== false) {
        return explode(':', $httpHost)[1];
    }

    // Otherwise check SERVER_PORT
    $port = $_SERVER['SERVER_PORT'] ?? '';

    // Hide default ports
    if (
        ($port == 80 && ltScheme() === 'http') ||
        ($port == 443 && ltScheme() === 'https')
    ) {
        return '';
    }

    return $port;
}

function ltSubfolder()
{

    $reqUrl = $_SERVER['REQUEST_URI'];
    $djkasdgs = substr($reqUrl,0,-19); 
    return $djkasdgs;
    
    // Example: /v2_8/admin-end/welcome/login
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    // Example output: /v2_8 if project is inside htdocs/v2_8
    $parts = explode('/', trim($scriptName, '/'));

    if (!empty($parts[0])) {
        return '/' . $parts[0];
    }

    return '';
}

function ltDomain()
{
    $port = ltPort();

    return ltHost() . (!empty($port) ? ':' . $port : '');
}

function ltBaseUrl()
{
    return ltScheme() . '://' . ltDomain();
}

function ltEndPointUrl()
{
    return [
        'scheme'    => ltScheme(),
        'port'      => ltPort(),
        'host'      => ltHost(),
        'subfolder' => ltSubfolder(),
        'base_url'  => ltBaseUrl(),
        'endpoint'  => "/api/v1/v2",
    ];
}

$dataEndPointUrl = ltEndPointUrl();
          


$reqUrl = $_SERVER['REQUEST_URI'];
$djkasdgs = substr($reqUrl,0,-19); 
$endpointValue = ltBaseUrl().$djkasdgs;
if( empty($host) || empty($username)      || empty($dbname) ){
echo '<div  style="margin-left:80px"><font   color="#FF0000"><h2>Sorry!!,  Your elements must not be empty</h2></font></div>';
exit();
}else{

try{$connect2db = new PDO("mysql:dbname=$dbname; host=$host", $username, $password);
$connect2db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);$connect2db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if($connect2db){//global $connect2db;

    

}

}catch(PDOException $e){
    try {
        // Connect to MySQL as a root/admin user
        $pdo = new PDO("mysql:host=$host", "$username", $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Define the database, new user, and credentials
      

        $database =$dbname;
        $newUser = $username;
        $newHost = $host; // or '%' for any host
        $newPassword = $password;

        // SQL to create the database
        $createDatabaseSQL = "CREATE DATABASE IF NOT EXISTS $database";

        // SQL to create the user
        $createUserSQL = "CREATE USER :username@:host IDENTIFIED BY :password";

        // SQL to grant all privileges on the new database to the new user
        $grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON $database.* TO :username@:host";
        //$grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON *.* TO 'lifetech_ocms_todeleteu'@'localhost'";
        //$grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON *.* TO :username@:host ";
        
        // Execute the CREATE DATABASE statement
        $pdo->exec($createDatabaseSQL);
       // echo "Database `$database` created successfully.\n";
      if($newUser != "root" && empty($newPassword)){
        // Prepare and execute the CREATE USER statement
        $stmt = $pdo->prepare($createUserSQL);
        $stmt->execute([
            ':username' => $newUser,
            ':host' => $newHost,
            ':password' => $newPassword,
        ]);
        //echo "User `$newUser` created successfully.\n";

        // Prepare and execute the GRANT PRIVILEGES statement
        $stmt = $pdo->prepare($grantPrivilegesSQL);
        $stmt->execute([
            ':username' => $newUser,
            ':host' => $newHost,
        ]);
        //echo "Privileges granted to `$newUser` on `$database`.\n";

        // Flush privileges to apply changes
        $pdo->exec('FLUSH PRIVILEGES');
        // echo "Privileges flushed successfully.\n";
      }
    } catch (PDOException $e) {
       $errror = "Error: " . $e->getMessage();
    }

    if ($errror != ""){
    echo'<div  style="margin-left:10px; background-color:yellow"><font   color="#FF0000"><h3>'.$errror.'</h3</font><br> <h2><font   color="blue">I will advise you create the Database and assign the user Privilege directly on your DBMS before you continue!!</font></h2></div>';
    exit();
   // goto secontinu; 
    }else{
      
    } 
}

// to write the endpoint files
$endpointValue = $endpointValue."/api/v1/v2";

       $pageurlEnd= 'endpoint.php'; 
        $fhEnd = fopen($pageurlEnd,"w");
$contentEnd= <<<PHP
                <?php
                \$dataEndPointUrl = [
                    'scheme'    => '{$dataEndPointUrl['scheme']}',              // Protocol: http | https
                    'port'      => '{$dataEndPointUrl['port']}',                   // Optional port (e.g. 8080). Leave empty for empty port
                    'host'      => '{$dataEndPointUrl['host']}',  // Domain name (no trailing slash)
                
                    'subfolder' => '{$dataEndPointUrl['subfolder']}',               // Optional base directory inside the domain
                                                         // e.g. https://www.domain.com/hubs
                                                         // Leave empty '' if app is in root (https://www.domain.com)
                
                    'endpoint'  => '{$dataEndPointUrl['endpoint']}',         // Application/API path (must start with '/')
                ];
                
               
                // Build endpoint
                \$endpointUrl = \$dataEndPointUrl['scheme'] . '://' .
                    \$dataEndPointUrl['host'] . (!empty(\$dataEndPointUrl['port']) ? ':' .\$dataEndPointUrl['port'] : '') .
                    (!empty(trim(\$dataEndPointUrl['subfolder'], '/')) ? '/' . trim(\$dataEndPointUrl['subfolder'], '/')
                        : '') .'/' . ltrim(\$dataEndPointUrl['endpoint'], '/');



                //sample of the endpoint is  'https://www.lifetech.host/hubs/api/v1/v2'; 
                
                
                    return \$endpointUrl; 
                ?>
                PHP;
   fwrite($fhEnd,$contentEnd);
            fclose($fhEnd); 
            
//to write session identity file 
$siteSessionId = (string) random_int(100000000000000, 999999999999999);

       $pageurlEnd= 'app.identity.php'; 
        $fhEnd = fopen($pageurlEnd,"w");

       $pageurlEnd= 'app.identity.php'; 
        $fhEnd = fopen($pageurlEnd,"w");
   $contentEnd = <<<PHP
            <?php

            /**
             * -------------------------------------------------------
             * LifeTech OCMS - Site Identity
             * -------------------------------------------------------
             * This file is auto-generated during installation.
             * It generates a unique PHP session name for this
             * website installation to prevent session collisions
             * between multiple LifeTech instances.
             * -------------------------------------------------------
             */

            \$hostParts = explode(':', \$_SERVER['HTTP_HOST'] ?? '');

            \$host = \$hostParts[0];
            \$port = \$hostParts[1] ?? '';

            \$identifier = \$host;

            if (\$port !== '') {
                \$identifier .= '_' . \$port;
            }

            \$uniqId = "{$siteSessionId}";

            \$identifier .= '_' . (trim(\$uniqId, '/') ?: 'ROOT');

            \$appIdentifier = preg_replace('/[^A-Za-z0-9_]/', '_', \$identifier);
            \$sessionName = 'LTSESSID_' . \$appIdentifier;

            session_name(\$sessionName);

            PHP;
   fwrite($fhEnd,$contentEnd);
            fclose($fhEnd); 
            

//to write backend switching json

$pageurlEndJson = 'backendswitching.json';

$dataJson = [
    [
        'ltId'       => '658887544',
        'siteName'   => 'Default Site',
        'scheme'     => ltScheme(),
        'port'       => ltPort(),
        'host'       => ltHost(),
        'subfolder'  => ltSubfolder(),
        'endpoint'   => '/api/v1/v2',
        'username'   => 'developer',
        'isEnabled'  => 1,
        'password'   => '',
        'loginPath'  => '/users/login',
        'serverType' => 'main'
    ]
];

$contentJson = json_encode(
    $dataJson,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if (file_put_contents($pageurlEndJson, $contentJson) === false) {
    throw new Exception('Unable to create backendswitching.json');
}

/*
        $fhEndJson = fopen($pageurlEndJson,"w");
$contentEndJson= <<<PHP
                <?php
                ?>
                PHP;
   fwrite($fhEndJson,$contentEndJson);
            fclose($fhEndJson); 
  */          





// to write the connection files

       $pageurl= 'connect2db_setup.php'; 
        
        $fh = fopen($pageurl,"w");
          
          $content='<?php     

      $host = "'.$host.'";
      $username =  "'.$username.'";
      $password = "'.$password.'";
      $dbname = "'.$dbname.'";



      try{$connect2db = new PDO("mysql:dbname=$dbname; host=$host", $username, $password);
      $connect2db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);$connect2db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if($connect2db){

      }}catch(PDOException $e){echo "The Database Information Supplied is not Correct, Reverify!!!";
          exit();
      }        
      ?>';
      fwrite($fh,$content);
            fclose($fh);
            
      $pageurldelete= 'connect2dbdelete.php'; 
        
        $fhdelete = fopen($pageurldelete,"w");
          
          $contentdelete='<?php 
        class DatabaseConfig
        {
            public static function get(): array
            {
                return [
                    \'host\' => getenv(\'LT_DB_HOST\') ?: "'.$host.'",
                    \'user\' => getenv(\'LT_DB_USER\') ?: "'.$username.'",
                    \'pass\' => getenv(\'LT_DB_PASS\') ?: "'.$password.'",
                    \'name\' => getenv(\'LT_DB_NAME\') ?: "'.$dbname.'",
                ];
            }
        }
     ';


        fwrite($fhdelete,$contentdelete);
            fclose($fhdelete);
            rename ($pageurldelete, "DatabaseConfig.php");
            

      echo 'success';  
      exit();
     
}

      
secontinu:      


}

  
//step 3


 if(isset($_POST['lastStep'])){
    ob_end_clean();
    //echo "success";
   // exit();
   
   //error_reporting(1);  
include('connect2db_setup.php');
/*
  $reqUrl = $_SERVER['REQUEST_URI']; $djkasdgs = substr($reqUrl,0,-19); 
  echo $djkasdgs;
  exit();
  */
 
 $rFileLoadnn = 'lifetech_installation_db.sql';
                 $query = ''; $mnt4= '';
                          $sqlScript = file($rFileLoadnn); 
                          foreach ($sqlScript as $line) {
                          $mnt4= $mnt4 . $line;
                            $startWith = substr(trim($line), 0 ,2);
                            $endWith = substr(trim($line), -1 ,1);
                            $endWithL = substr(trim($line), -22 ,22); 
                              
                            $query = $query . $line;
                            if ($endWith == ';') {
                                if(strpos($query, 'AUTOCOMMIT') !== false){}else{ 
                                $query = substr($query,0,-1); $query.=';';
                                try{
                                $qry= $connect2db->prepare($query);
                                $qry->execute();
                                // echo 'almost on it'.$query;
                                }catch(Exception $e){
                                    echo $e->getMessage();
                                }
                                   }                              
                              $query= '';   
                            }
                          
                          }                         

          
            try{
                
                 $reqUrl = $_SERVER['REQUEST_URI'];
  $djkasdgs = substr($reqUrl,0,-19); 
            $qry1= $connect2db->prepare("update tb_site set site_host_address='$djkasdgs'  where sn='1'");
              if($qry1->execute()){
                 
                
                unlink("connect2db_setup.php"); 
                unlink("setup.php");
                unlink("setup_docker_endpoint.php");
                 ob_end_clean();
                 echo 'success';
                
                 exit();
                }else{
                echo '<h1> .....Sorry Your Software Database was unable to Finalize...<br />!
                    If the problem persist, please import the database file directly into your database before retrying<br/><h1>';
                 echo '<h1>Retry!!!! <a href="setup3.php"> Again</a>!!!<h1>';
                  
                 exit();

                } 
            }catch(Exception $e){
                echo $e->getMessage();
            }
                
    echo "not done";
    exit();
    }
    
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Installer Wizard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <style>
    /* THE TWIST: A 3D Tilt-Up Animation */
    @keyframes enter {
      0% {
        opacity: 0;
        /* Start slightly smaller, pushed down, and tilted back */
        transform: perspective(1000px) scale(0.95) translateY(20px) rotateX(-10deg);
      }
      100% {
        opacity: 1;
        /* End flat, full size, and centered */
        transform: perspective(1000px) scale(1) translateY(0) rotateX(0);
      }
    }

    .animate-enter {
      /* Added 'forwards' to ensure it stays in the final state */
      animation: enter 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      /* Improve text rendering during 3D transform */
      backface-visibility: hidden; 
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-3xl bg-white rounded-xl shadow-2xl p-8 transform transition-all">

  <div class="mb-8">
    <div class="flex justify-between text-sm mb-2 font-medium text-gray-500">
      
      <span>PHP Check</span>
      <span>Database</span>
      <span>Finish</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
      <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-700 ease-out" style="width: 33%"></div>
    </div>
  </div>

  <div id="stepContainer">
    <div class="step animate-enter" data-step="1">
      <h2 class="text-2xl font-bold mb-2 text-gray-800">PHP Configuration Setup</h2>
      
      <div id="step1Status" class="text-gray-500 mb-6"><?php echo ($Res_maxUpload && $Res_maxPost && $Res_maxtime) ? '<span class="text-green-700 bg-green-100 text-lg px-2 py-1 rounded font-bold">Bravo!!, Configuration checked and passed. click verify to continue</span>' : 'Checking configuration...' ?></div>

      <ul class="space-y-3 mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
        <li class="flex justify-between items-center">
            <?php if($Res_maxUpload): ?>
                <span class="text-green-700 bg-green-100 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Upload Max Size</span>
                <span class="text-green-600 bg-green-100 hover:shadow hover:text-shadow-lg px-2 py-1 rounded text-xs font-bold">✔ OK</span>
            <?php else: ?>
                <span class="text-red-800 bg-red-300 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Upload Max Sized is: <?php echo $maxUpload ?></span>
                <span class="text-black-600 bg-black-100 px-2 py-1 rounded text-xs font-bold">should not be less than 40M</span>
            <?php endif; ?>
        </li>
        <li class="flex justify-between items-center">
            <?php if($Res_maxPost): ?>
                <span class="text-green-700 bg-green-100 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Post Max Size</span>
                <span class="text-green-600 bg-green-100 hover:shadow hover:text-shadow-lg px-2 py-1 rounded text-xs font-bold">✔ OK</span>
            <?php else: ?>
                <span class="text-red-800 bg-red-300 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Post Max Size is: <?php echo $maxPost; ?></span>
                <span class="text-black-600 bg-black-100 px-2 py-1 rounded text-xs font-bold">should not be less than 40M</span>
            <?php endif; ?>
        </li>
        <li class="flex justify-between items-center">
            <?php if($Res_maxtime): ?>
                <span class="text-green-700 bg-green-100 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Execution Time</span>
                <span class="text-green-600 bg-green-100 hover:shadow hover:text-shadow-lg px-2 py-1 rounded text-xs font-bold">✔ OK</span>
            <?php else: ?>
                <span class="text-red-800 bg-red-300 hover:shadow hover:text-shadow-lg text-lg px-2 py-1 rounded font-bold">Execution Time is: <?php echo $maxtime; ?></span>
                <span class="text-black-600 bg-black-100 px-2 py-1 rounded text-xs font-bold">should not be less than 120sec</span>
            <?php endif; ?>
        </li>
      </ul>
     <?php if($Res_maxUpload && $Res_maxPost && $Res_maxtime): ?>
      <button onclick="runStep(1)" class="w-full bg-blue-600 hover:bg-blue-700 transition-colors text-white py-3 rounded-lg font-semibold shadow-md">Verify & Continue</button>
     <?php else: ?>
      <div class="bg-yellow-50 border border-yellow-100 rounded-lg h-full w-full p-4 shadow-lg hover:shadow-xl hover:text-shadow-lg"> 
        <p><stong>Path File: <?php echo  php_ini_loaded_file(); ?></stong></p>
        <p><stong><b>Note:</b></stong></p>
        <p>In respective of PhP version you are using, You are to search for 
        <b>PhP.ini Configuration</b> 
        file in your server directories and adjust <b><?php echo implode(', ', array_keys($keys)); ?></b> parameter to meet atleast the minimum requirement of <b><?php echo implode(', ', array_values($keys)); ?></b>
        Once changes has been made <strong>restart</strong> your server for the changes to be effected
        <br /><br />Search for: <b><?php echo strtolower(implode(', ', array_keys($keys))); ?></b> to adjust the value </div>
        </p>
      </div>
     <?php endif; ?>
    </div>

    <div class="step hidden" data-step="2">
      <h2 class="text-2xl font-bold mb-2 text-gray-800">Database Configuration</h2>
      <div id="step2Status" class="text-gray-500 mb-6">Enter your connection details</div>

      <div class="grid gap-4 mb-8">
           <div id="getResponse">
            </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Database Name:</label>
            <input class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none transition" id="dbBase" readonly onfocus="this.removeAttribute('readonly');" autocomplete="off" placeholder="Database Name" />
        </div>
        <div class="showPriviledge hidden">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username:</label>
                <input class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none transition" id="username" value="root" placeholder="DB Username" />
            </div>
                
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Host Name:</label>
                <input class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none transition" id="hostname" value="localhost" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password:</label>
                <input type="password" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none transition" id="password" placeholder="DB Password" />
            </div>
        </div>
        <div><span class="text-lg text-green-800 font-bold">Advance Database Priviledge </span><button class="bg-blue-500 text-white hover:bg-blue-700 rounded-lg p-1 ml-2 transition-colors shadow-md font-semibold" id="togglePriviledge">Show</button></div>
        
      </div>
      <div class="flex justify-between gap-4">
        <button onclick="goToStep(1)" class="w-1/3 px-6 py-3 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 font-medium transition">Back</button>
        <button type="submit" class="w-2/3 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md font-medium transition" id="verifyDatabase">Create / Verify</button>
      </div>
    </div>

    <div class="step hidden text-center" data-step="3" >
          <div class="lastStepSuccessMessage">
            <!--Success Mesage here-->
            Please wait ... while  Database tables are Installing...
        </div>
    </div>

  </div> <div id="loader" class="hidden absolute inset-0 bg-white/80 flex flex-col items-center justify-center z-10 rounded-xl backdrop-blur-sm">
    <div class="animate-spin rounded-full h-10 w-10 border-4 border-gray-200 border-t-blue-600 mb-4"></div>
    <span class="text-gray-600 font-medium animate-pulse">Processing...</span>
  </div>

</div>

<script>
    let showPriviledge = false;
  let currentStep = localStorage.getItem('installerStep') ? parseInt(localStorage.getItem('installerStep')) : 1;

  document.getElementById('togglePriviledge').addEventListener('click', function(e){
      showPriviledge = !showPriviledge;
      let btn = e.target;
      showPriviledge ? btn.textContent = 'Hide' : btn.textContent = 'Show';
      document.querySelector('.showPriviledge').classList.toggle('hidden');
  })

  function updateProgress(step) {
    const percent = step === 1 ? 33 : step === 2 ? 66 : 100;
    document.getElementById('progressBar').style.width = percent + '%';
  }

  function goToStep(step) {
    // 1. Hide all steps
    const allSteps = document.querySelectorAll('.step');
    allSteps.forEach(s => {
        s.classList.add('hidden');
        s.classList.remove('animate-enter'); // Remove animation class to reset it
    });

    // 2. Select current step
    const target = document.querySelector(`[data-step="${step}"]`);
    
    // 3. Show step and RE-ADD animation class
    target.classList.remove('hidden');
    
    // Small timeout ensures the browser registers the class removal before re-adding
    // This forces the animation to replay
    requestAnimationFrame(() => {
        target.classList.add('animate-enter');
    });

    updateProgress(step);
    localStorage.setItem('installerStep', step);
    currentStep = step;
  }

  function runStep(step) {
    const loader = document.getElementById('loader');
    loader.classList.remove('hidden'); // Show overlay loader
    
    // Simulated async operation
    setTimeout(() => {
      loader.classList.add('hidden');
      goToStep(step + 1);
    }, 1200);
  }

  
  
  
  document.getElementById('verifyDatabase').addEventListener('click', async (e) => {
  e.preventDefault();

  const formField = ['hostname', 'username', 'password', 'dbBase'];
  const fd = new FormData();

  for (const field of formField) {
    const input = document.getElementById(field);
    if (!input) {
      console.error(`Element with ID '${field}' not found`);
      return;
    }
    const value = input.value.trim();
    if (field === 'dbBase' && !value) {
      alert('database field is required');
      return;
    }
    fd.append(field, value);
  }

  // this makes isset($_POST['dbinfo']) true
  fd.append('dbinfo', 'dbinfo');

  const res = await fetch(window.location.href, {
    method: 'POST',
    headers: { 'Accept': 'application/json' }, // do NOT set Content-Type for FormData
    body: fd
  });

  const text = await res.text(); // safer first
  try {
    const json = JSON.parse(text);
    document.getElementById('getResponse') = json;
    console.log(json);
  } catch (err) {
      const resp  =text;
      if (resp === "success"){
         document.getElementById('getResponse').innerHTML ='<div  style="margin-left:100px; background-color:green"><font color="white"><h2> Connection successful...Database is Loading..</h2> </font></div>';
         //runStep(2);
         verifyLastStage();
         setTimeout(() => runStep(2), 1000);
      
      }else{
         document.getElementById('getResponse').innerHTML =resp; 
      }
      
      
    //console.error('Not JSON:', text.slice(0, 500));
  }
});


  
  async function verifyLastStage(){
        const formData = new FormData();
        formData.append('lastStep', 'lastStep');

        const res = await fetch(window.location.href, {
            method: 'POST',
            headers: { 'Accept': 'application/json' }, // do NOT set Content-Type for FormData
            body: formData
        });
        //document.getElementById('myButton').click();
          const text = await res.text(); // safer first
          try {
            const json = JSON.parse(text);
            document.getElementById('getResponse') = json;
          } catch (err) {
              const resp  =text;
              if (resp === "success"){
                 document.querySelector('.lastStepSuccessMessage').innerHTML = `<div class="mb-6 flex justify-center" id="myButton">
                    <div class="h-16 w-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-3xl">
                        ✓
                    </div>
                </div>
                <h2 class="text-2xl font-bold mb-2 text-gray-800">Installation Complete</h2>
                <div id="step3Status" class="text-gray-500 mb-8">Software has been installed successfully.</div>
                <a href="#" class="inline-block bg-green-600 hover:bg-green-700 text-white px-10 py-3 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-1">Page will shortly redirect to Home Page</a>`;
               
                 setTimeout(function() {
                      window.location.href = "../";
                  }, 2000);
              
              }else{
                 document.querySelector('.lastStepSuccessMessage').innerHTML = "Sorry!!! Database ALready Installed"+resp ; 
                  setTimeout(function() {
                      window.location.href = "../";
                  }, 2000);
              }
           
          }
   
    };
    
</script>

</body>
</html>