<?php
/**
 * api/cities.php
 */

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once 'db.php';

$cities = [
    ['id' => 'Nouakchott', 'name_ar' => 'نواكشوط', 'name_en' => 'Nouakchott'],
    ['id' => 'Nouadhibou', 'name_ar' => 'نواذيبو', 'name_en' => 'Nouadhibou'],
    ['id' => 'Kiffa', 'name_ar' => 'كيفه', 'name_en' => 'Kiffa'],
    ['id' => 'Rosso', 'name_ar' => 'روصو', 'name_en' => 'Rosso'],
    ['id' => 'Zouérat', 'name_ar' => 'زويرات', 'name_en' => 'Zouérat'],
    ['id' => 'Boutilimit', 'name_ar' => 'بوتلميت', 'name_en' => 'Boutilimit'],
    ['id' => 'Akjoujt', 'name_ar' => 'أكجوجت', 'name_en' => 'Akjoujt'],
    ['id' => 'Aioun El Atrouss', 'name_ar' => 'عيون العتروس', 'name_en' => 'Aioun El Atrouss'],
    ['id' => 'Néma', 'name_ar' => 'النعمة', 'name_en' => 'Néma']
];

function cleanSendResponse($data, $statusCode = 200) {
    if (ob_get_length()) ob_clean(); 
    sendResponse($data, $statusCode);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    cleanSendResponse([
        "status" => "success",
        "source" => "static",
        "data" => $cities
    ]);
}
