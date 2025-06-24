<?php
// File path
//$logFile = __DIR__ . '/logs/error_log.txt';
// $logFile = __DIR__ . '/logs/butt_log.txt';
// include_once(dirname(__FILE__) . '/../components/header.php');
// include_once(dirname(__FILE__) . '/../components/sidenav.php');
// $pageId = 'b4130068-6e83-492b-9956-eedcb8529bb7';
// $accessRequired = Page::getAccessRequired($pageId);
// // echo "<!-- Access Required: $accessRequired -->";
// AccessControl::enforce($accessRequired);
$logFile = 'error_log.txt';

// Check if the file exists
if (!file_exists($logFile)) {
    die("Stupid file not found in the /logs/ directory.");
}

// Read the file and split it into lines
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Reverse the lines for newest entries first
$lines = array_reverse($lines);
// include(dirname(__FILE__) . '/../components/header.php');
// include(dirname(__FILE__) . '/../components/sidenav.php');
// include(dirname(__FILE__) . '/../mp/mpnav.php');
// AccessControl::enforce(100);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>

    <style>
        /* body {
        font-family: Arial, sans-serif;
        margin: 20px;
    } */
        .main {
            margin: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: large;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: var(--bg);
        }

        tr:nth-child(even) {
            background-color: var(--fg);
            color: var(--bg)
        }

        .timestamp {
            font-weight: bold;
            color: #2a5d84;
        }

        .highlight {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- <h1>Log Viewer</h1> -->
    <div class="main">
        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                        <?php
                        // Parse the line for timestamp and message
                        preg_match('/^\[(.*?)\](.*)$/', $line, $matches);
                        $timestamp = $matches[1] ?? 'Unknown';
                        // if ($timestamp !== 'Unknown') {
                        //     $dt = new DateTime('@' . $timestamp);
                        //     $dt->setTimezone(new DateTimeZone('America/New_York')); // Replace with your desired timezone
                        //     $timestamp = $dt->getTimestamp();
                        // }
                        $message = $matches[2] ?? $line;

                        // Highlight specific fields
                        $message = preg_replace('/User ID: ([\w-]+)/', 'User ID: <span class="highlight">$1</span>', $message);
                        $message = preg_replace('/Card ID: ([\w-]+)/', 'Card ID: <span class="highlight">$1</span>', $message);
                        $message = preg_replace('/username: (\w+\.\w+)/', 'username: <span class="highlight">$1</span>', $message);
                        ?>
                        <tr>
                            <td class="timestamp"><?= htmlspecialchars($timestamp) ?></td>
                            <td><?= nl2br(htmlspecialchars($message)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>