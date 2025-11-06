<?php
// session_start();

function updateAppVersion($changelogPath)
{
    $changelogContent = file_get_contents($changelogPath);
    // Get the version number using regex
    // Note the format of #### (markdown for h4) with a - (dash) after version number. In my markdown file, I have the date after that dash but that is up to you. Just update the regex to reflect if you something differnt directly before and after the version number you are extracting. 
    preg_match('/## Version ([\d.]+) -/', $changelogContent, $matches);
    $appVersion = isset($matches[1]) ? $matches[1] : 'Unknown';

    return $appVersion;
}
// This is the path to the changelog file in my app. I am lazy and it is in the folder as my changelog.md file. Adjsut as needed for your app.
$changelogPath = './changelog.md';

// this calls the function to extract the version number and set the value into a variable.
$appVersion = updateAppVersion($changelogPath);

// This can be removed. For demonstrtation and debugging only
// echo "App Version: $appVersion";
// set Session variable to hold the extracted version number. 
$_SESSION['appVersion'] = $appVersion;

// technically I guess we could say $_SESSION['appVersion'] = updateAppVersion($changelogPath); as well.... up to you. Make it better, submit a PR. :)