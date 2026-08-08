<?php 
ob_start();


 


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

  

    // to write the endpoint files
    $endpointValue = $endpointValue."/api/v1/v2";
    $endSubfolder = $dataEndPointUrl['subfolder']; 

    require_once("DbConnect.php");  
    $connect2db =  DbConnect::dbDriver();   
    $qry1= $connect2db->prepare("update tb_site set site_host_address='$endSubfolder'  where sn='1'");
                  if($qry1->execute()){
                  }

$pageurlEnd= 'includes/endpoint.php'; 
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

       $pageurlEnd= 'includes/app.identity.php'; 
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

$pageurlEndJson = 'includes/backendswitching.json';

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

?>