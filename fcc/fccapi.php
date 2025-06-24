<?php

include "fcc_config.php";

$dbconf = new fccConfig;
$serverName = $dbconf->serverName;
$database = $dbconf->database;
$uid = $dbconf->uid;
$pwd = $dbconf->pwd;

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database;ConnectionPooling=0", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    // echo "Connection failed: " . $e->getMessage();
}

$blocknumber = $_GET['blockcode'];

$queries = [
    "LicWireless" => "WITH RankedResults AS (
    SELECT 
        lfw.brand_name,
        lfw.technology,
        lfw.max_advertised_download_speed AS max_down,
        lfw.max_advertised_upload_speed AS max_up,
        ul.holding_company,
        ROW_NUMBER() OVER(PARTITION BY lfw.brand_name ORDER BY lfw.max_advertised_download_speed DESC) AS rn
    FROM LicensedFixedWireless_fixed_broadband lfw
    JOIN us_provider_list ul ON ul.frn = lfw.frn
    WHERE lfw.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1;",
    "cable_fixed_broadband" => "WITH RankedResults AS (
    SELECT 
        cfb.brand_name,
        cfb.technology,
        cfb.max_advertised_download_speed AS max_down,
        cfb.max_advertised_upload_speed AS max_up,
        ul.holding_company,
        ROW_NUMBER() OVER(PARTITION BY cfb.brand_name ORDER BY cfb.max_advertised_download_speed DESC) AS rn
    FROM Cable_fixed_broadband cfb
    JOIN us_provider_list ul ON ul.frn = cfb.frn
    WHERE cfb.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    // "other_fixed_broadband" => "WITH RankedResults AS (
    // SELECT 
    //     ofb.brand_name,
    //     ofb.technology,
    //     ofb.max_advertised_download_speed AS max_down,
    //     ofb.max_advertised_upload_speed AS max_up,
    //     ul.holding_company,
    //     ROW_NUMBER() OVER(PARTITION BY ofb.brand_name ORDER BY ofb.max_advertised_download_speed DESC) AS rn
    // FROM other_fixed_broadband ofb
    // JOIN us_provider_list ul ON ul.frn = ofb.frn
    // WHERE ofb.block_geoid = ?
    // )
    // SELECT 
    //     brand_name,
    //     technology,
    //     max_down,
    //     max_up,
    //     holding_company
    // FROM RankedResults
    // WHERE rn = 1",
    "copper_fixed_broadband" => "WITH RankedResults AS (
    SELECT 
        pfb.brand_name,
        pfb.technology,
        pfb.max_advertised_download_speed AS max_down,
        pfb.max_advertised_upload_speed AS max_up,
        ul.holding_company,
        ROW_NUMBER() OVER(PARTITION BY pfb.brand_name ORDER BY pfb.max_advertised_download_speed DESC) AS rn
    FROM copper_fixed_broadband pfb
    JOIN us_provider_list ul ON ul.frn = pfb.frn
    WHERE pfb.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    "fiber_to_prem" => "WITH RankedResults AS (
    SELECT 
        ftp.brand_name,
        ftp.technology,
        ftp.max_advertised_download_speed AS max_down,
        ftp.max_advertised_upload_speed AS max_up,
        ul.holding_company,
        ROW_NUMBER() OVER(PARTITION BY ftp.brand_name ORDER BY ftp.max_advertised_download_speed DESC) AS rn
    FROM FibertothePremises ftp
    JOIN us_provider_list ul ON ul.frn = ftp.frn
    WHERE ftp.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    "GSOSatellite" => "WITH RankedResults AS (
    SELECT 
        gfb.brand_name,
        gfb.technology,
        gfb.max_advertised_download_speed AS max_down,
        gfb.max_advertised_upload_speed AS max_up,
        ul.holding_company,
        ROW_NUMBER() OVER(PARTITION BY gfb.brand_name ORDER BY gfb.max_advertised_download_speed DESC) AS rn
    FROM GSOSatellite_fixed_broadband gfb
    JOIN us_provider_list ul ON ul.frn = gfb.frn
    WHERE gfb.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    "LBR" => "WITH RankedResults AS (
        SELECT 
            lbr.brand_name,
            lbr.technology,
            lbr.max_advertised_download_speed AS max_down,
            lbr.max_advertised_upload_speed AS max_up,
            ul.holding_company,
            ROW_NUMBER() OVER(PARTITION BY lbr.brand_name ORDER BY lbr.max_advertised_download_speed DESC) AS rn
        FROM LBRFixedWireless_fixed_broadband lbr
        JOIN us_provider_list ul ON ul.frn = lbr.frn
        WHERE lbr.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    "NGSOSatellite" => "WITH RankedResults AS (
        SELECT 
            ngso.brand_name,
            ngso.technology,
            ngso.max_advertised_download_speed AS max_down,
            ngso.max_advertised_upload_speed AS max_up,
            ul.holding_company,
            ROW_NUMBER() OVER(PARTITION BY ngso.brand_name ORDER BY ngso.max_advertised_download_speed DESC) AS rn
        FROM NGSOSatellite_fixed_broadband ngso
        JOIN us_provider_list ul ON ul.frn = ngso.frn
        WHERE ngso.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1",
    "UnlicWireless" => "WITH RankedResults AS (
        SELECT 
            ulw.brand_name,
            ulw.technology,
            ulw.max_advertised_download_speed AS max_down,
            ulw.max_advertised_upload_speed AS max_up,
            ul.holding_company,
            ROW_NUMBER() OVER(PARTITION BY ulw.brand_name ORDER BY ulw.max_advertised_download_speed DESC) AS rn
        FROM UnlicensedFixedWireless_fixed_broadband ulw
        JOIN us_provider_list ul ON ul.frn = ulw.frn
        WHERE ulw.block_geoid = ?
    )
    SELECT 
        brand_name,
        technology,
        max_down,
        max_up,
        holding_company
    FROM RankedResults
    WHERE rn = 1"
];


$data = [];
foreach ($queries as $type => $query) {
    $stmt = $conn->prepare($query);
    if (!$stmt->execute([$blocknumber])) {
        error_log("failed to query for the {$type}: " . print_r($stmt->errorInfo(), true));
        continue;
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($result)) {
        $data[$type] = $result;
    }
}

$flattenedData = array_reduce($data, function ($carry, $item) {
    foreach ($item as $entry) {
        $carry[] = $entry;
    }
    return $carry;
}, []);

header('Content-Type: application/json');
echo json_encode($flattenedData);
