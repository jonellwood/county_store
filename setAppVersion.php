<?php
/*
Created: 2024/07/05
Last modified: 2026/02/16 09:20:52
Organization: Berkeley County IT Department
Purpose: Extract version number from package.json for display in footer
Note: Version is now managed by standard-version via npm run release
*/

function updateAppVersion($packageJsonPath)
{
    // Read package.json
    $packageJson = file_get_contents($packageJsonPath);

    if ($packageJson === false) {
        return 'Unknown';
    }

    // Decode JSON
    $packageData = json_decode($packageJson, true);

    // Extract version, fallback to 'Unknown' if not found
    $appVersion = isset($packageData['version']) ? $packageData['version'] : 'Unknown';

    return $appVersion;
}

// Path to package.json
$packageJsonPath = './package.json';

// Extract version and set in session
$appVersion = updateAppVersion($packageJsonPath);
$_SESSION['appVersion'] = $appVersion;
