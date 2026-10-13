<?php

require_once "../config.php";
require_once "../helpers/response.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 1. Get by ID
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            
            $q = "SELECT id, nama, nim FROM mahasiswa WHERE id = '$id'";
            $r = mysqli_query($koneksi, $q);
            
            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }
            
            $data = mysqli_fetch_assoc($r);
            
            if (!$data) {
                sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
            }
            
            sendResponse(true, "berhasil", $data, 200);
        }

        // 2. Get by Search
        if (isset($_GET['search'])) {
            $search = $_GET['search'];

            $q = "SELECT id, nama, nim FROM mahasiswa 
                  WHERE nama LIKE '%$search%' OR nim LIKE '%$search%' 
                  ORDER BY id DESC";

            $r = mysqli_query($koneksi, $q);
            
            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }
            
            $data = [];
            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // 3. Get with Pagination (Limit & Page)
        if (isset($_GET['page']) || isset($_GET['limit'])) {
            $page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            if ($page < 1) $page = 1;
            if ($limit < 1) $limit = 10;

            $offset = ($page - 1) * $limit;

            $q = "SELECT id, nama, nim FROM mahasiswa ORDER BY id DESC LIMIT $limit OFFSET $offset";
            
            $r = mysqli_query($koneksi, $q);
            
            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }
            
            $data = [];
            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // 4. Get All (Default)
        $q = "SELECT id, nama, nim FROM mahasiswa ORDER BY id DESC";
        $r = mysqli_query($koneksi, $q);

        if (!$r) sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);

        $data = [];
        while ($row = mysqli_fetch_assoc($r)) {
            $data[] = $row;
        }

        sendResponse(true, "berhasil", $data, 200);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['nama']) || !isset($input['nim'])) {
            sendResponse(false, "field nama dan nim wajib", null, 400);
        }

        $nama = $input['nama'];
        $nim  = $input['nim'];

        $cekNim = mysqli_query($koneksi, "SELECT id FROM mahasiswa WHERE nim='$nim'");
        if (mysqli_num_rows($cekNim) > 0) {
            sendResponse(false, "nim sudah terdaftar", null, 409);
        }

        $q = "INSERT INTO mahasiswa (nama, nim, created_at) VALUES ('$nama', '$nim', CURDATE())";
        if (mysqli_query($koneksi, $q)) {
            sendResponse(true, "data tersimpan", ["id" => mysqli_insert_id($koneksi)], 201);
        } else {
            sendResponse(false, mysqli_error($koneksi), null, 500); 
        }
        break;
}