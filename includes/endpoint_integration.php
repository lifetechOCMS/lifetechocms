<?php 

function removeWww($host)
{
    // remove port if present
    $host = explode(':', strtolower($host))[0];

    // remove starting www.
    return preg_replace('/^www\./', '', $host);
}

function getHostOnly($host)
{
    return explode(':', strtolower($host))[0];
}

function getPortOnly($host)
{
    $parts = explode(':', $host);
    return $parts[1] ?? '';
}

function getCurrentScheme()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';
}

// Current browser URL details
$urlHostRaw = $_SERVER['HTTP_HOST'] ?? '';
$urlHost    = getHostOnly($urlHostRaw);
$urlPort    = getPortOnly($urlHostRaw);
$urlScheme  = getCurrentScheme();

$currentUri = $_SERVER['REQUEST_URI'] ?? '/';

// Your endpoint config
$dataEndPointUrlSS = include("includes/endpoint.php");

/*
Expected endpoint.php should return array like:

return [
    'scheme'    => 'https',
    'port'      => '',
    'host'      => 'www.lifetech.host',
    'subfolder' => 'hubs',
    'endpoint'  => '/api/v1/v2',
];
*/

$endPointScheme = $dataEndPointUrl['scheme'] ?? 'https';
$endPointHost   = $dataEndPointUrl['host'] ?? '';
$endPointPort   = $dataEndPointUrl['port'] ?? '';

 
// Clean host comparison
$cleanUrlHost      = removeWww($urlHost);
$cleanEndPointHost = removeWww($endPointHost);

// Use current URL port if it exists, otherwise use endpoint port
$redirectPort = !empty($urlPort) ? ':' . $urlPort : '';

// Default redirect target
$redirectScheme = $endPointScheme;
$redirectHost   = $endPointHost;

$shouldRedirect = false;

// 1. Same exact host but wrong scheme http or https
if ($urlHost === $endPointHost && $urlScheme !== $endPointScheme) {
    $shouldRedirect = true;
} 
// 2. Same domain but www difference
if ($cleanUrlHost === $cleanEndPointHost && $urlHost !== $endPointHost) { 
    $shouldRedirect = true;
}

// 3. Same domain but port changed 8000 to 8800
if ($cleanUrlHost === $cleanEndPointHost && $urlPort !== $endPointPort) {
    if(!empty($urlPort)){
        $shouldRedirect = true;
        
        //update the port address for backend usage only on dev env
        if (LT_ENV === 'development') 
        {
            $dataEndPointUrl['port'] = $urlPort;
            $file = __DIR__ . "/endpoint.php";
            $content = <<<PHP
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
                \$endpointUrl = \$dataEndPointUrl['scheme'] . '://' .\$dataEndPointUrl['host'] . (!empty(\$dataEndPointUrl['port']) ? ':' . \$dataEndPointUrl['port'] : '') .  (!empty(\$dataEndPointUrl['subfolder']) ? '/' . trim(\$dataEndPointUrl['subfolder'], '/') : '') .  '/' . ltrim(\$dataEndPointUrl['endpoint'], '/'); 

                //sample of the endpoint is  'https://www.lifetech.host/hubs/api/v1/v2'; 
                
                
                    return \$endpointUrl; 
                ?>
                PHP;
        
                // Write back to file
                file_put_contents($file, $content);
         
        }
    }
}

// Redirect only when needed
if ($shouldRedirect) {
    header(
        "Location: {$redirectScheme}://{$redirectHost}{$redirectPort}{$currentUri}",
        true,
        301
    );
    exit;
}


?>