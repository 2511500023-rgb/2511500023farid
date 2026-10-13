<?php
require_once __DIR__ . "/helpers/response.php";

mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost"; // sesuaikan host database kamu
$user = "root";      // sesuaikan user database kamu
$pass = "";          // sesuaikan password database kamu
$db   = "jurusan"; // ganti dengan nama database kamu

$koneksi = @mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    sendResponse(
        false,
        "Koneksi database gagal",
        null,
        500
    );
}