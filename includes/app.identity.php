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

$hostParts = explode(':', $_SERVER['HTTP_HOST'] ?? '');

$host = $hostParts[0];
$port = $hostParts[1] ?? '';

$identifier = $host;

if ($port !== '') {
    $identifier .= '_' . $port;
}

$uniqId = "install_42";

$identifier .= '_' . (trim($uniqId, '/') ?: 'ROOT');

$appIdentifier = preg_replace('/[^A-Za-z0-9_]/', '_', $identifier);
$sessionName = 'LTSESSID_' . $appIdentifier;

session_name($sessionName);
