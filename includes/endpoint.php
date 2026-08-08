<?php
$dataEndPointUrl = [
    'scheme'    => 'http',              // Protocol: http | https
    'port'      => '',                   // Optional port (e.g. 8080). Leave empty for empty port
    'host'      => 'localhost',  // Domain name (no trailing slash)

    'subfolder' => '/hubs',               // Optional base directory inside the domain
                                         // e.g. https://www.domain.com/hubs
                                         // Leave empty '' if app is in root (https://www.domain.com)

    'endpoint'  => '/api/v1/v2',         // Application/API path (must start with '/')
];

// Build endpoint
$endpointUrl =$dataEndPointUrl['scheme'] . '://' . $dataEndPointUrl['host'] . (!empty($dataEndPointUrl['port']) ? ':' . $dataEndPointUrl['port'] : '') . (!empty(trim($dataEndPointUrl['subfolder'], '/'))  ? '/' . trim($dataEndPointUrl['subfolder'], '/'): '') . '/' . ltrim($dataEndPointUrl['endpoint'], '/');
//sample of the endpoint is  'https://www.lifetech.host/hubs/api/v1/v2'; 


    return $endpointUrl; 
?>