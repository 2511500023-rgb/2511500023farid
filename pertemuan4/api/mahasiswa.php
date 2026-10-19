<?php

require_once "../config.php";
require_once "../helpers/response.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
       
        //ini get
//Get by id
//mahasiswa.php?id=1
if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $q = "SELECT m.id, m.nama, m.nim, j.nama_jurusan AS jurusan FROM mahasiswa m LEFT JOIN jurusan j on m.jurusan_id = j.id WHERE m.id = $id";

    $r = mysqli_query($koneksi, $q);

    if(!$r) {
        sendResponse(
            false,
            "Query gagal : " . mysqli_error($koneksi),
            null,
            500
        );

    }

    $data = mysqli_fetch_assoc($r);

    if(!$data) {
        sendResponse(
            false,
            "Mahasiswa tidak ditemukan",
            null,
            404
        );
    }

    sendResponse(
        true,
        "Berhasil", 
        $data,
        200
    );

}

//search
//api/mahasiswa.php?search=a
if(isset($_GET['search'])) {
    $search = $_GET['search'];

    $q = "SELECT m.id, m.nama, m.nim, j.nama_jurusan AS jurusan FROM mahasiswa m LEFT JOIN jurusan j on m.jurusan_id = j.id WHERE m.nama LIKE '%$search%' OR m.nim LIKE '%$search%' ORDER BY m.id DESC";

    $r = mysqli_query($koneksi, $q);

    if(!$r) {
        sendResponse(
            false,
            "Query gagal : " . mysqli_error($koneksi),
            null,
            500
        );

    }

     $data = [];
        while($row = mysqli_fetch_assoc($r)) {
            $data[] = $row;
        }

    sendResponse(
        true,
        "Berhasil", 
        $data,
        200
    );

}

//limit&offset
//pagination
//mahasiswa.php?limit=2
//mahasiswa.php?page=2
if(isset($_GET['page']) || isset($_GET['limit'])) {

    $page =isset($_GET['page'])
        ? (int)$_GET['page']
        : 1;

    $limit = isset($_GET['limit'])
        ? (int)$_GET['limit']
        : 10;

    if($page < 1) $page = 1;

    if($limit < 1) $limit = 10;

    $offset = ($page - 1) * $limit;

    $q = "SELECT m.id, m.nama, m.nim, j.nama_jurusan AS jurusan FROM mahasiswa m LEFT JOIN jurusan j on m.jurusan_id = j.id ORDER BY m.id DESC LIMIT $limit OFFSET $offset";

    $r = mysqli_query($koneksi, $q);

    if(!$r) {
        sendResponse(
            false,
            "Query gagal : " . mysqli_error($koneksi),
            null,
            500
        );

    }

     $data = [];
        while($row = mysqli_fetch_assoc($r)) {
            $data[] = $row;
        }

    sendResponse(
        true,
        "Berhasil",
        $data,
        200
    );
}

$q = "SELECT m.id, m.nama, m.nim, j.nama_jurusan AS jurusan FROM mahasiswa m LEFT JOIN jurusan j on m.jurusan_id = j.id ORDER BY m.id DESC";


$r = mysqli_query($koneksi, $q);

if(!$r) {
    sendResponse(
        false,
        "Query gagal : " . mysqli_error($koneksi),
        null,
        500
    );

}

 $data = [];
    while($row = mysqli_fetch_assoc($r)) {
        $data[] = $row;
    }

sendResponse(
    true,
    "Berhasil", 
    $data,
    200
);
        //akhir get
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        //cek apakah input ada dan field nama, nim, jurusan_id ada
        if (!isset($input['nama']) || !isset($input['nim']) || !isset($input['jurusan_id'])) {
            sendResponse(false, "Field nama, nim, dan jurusan_id harus diisi", null, 400);
        }

        $nama = $input['nama'];
        $nim = $input['nim'];
        $jurusan_id = (int) $input['jurusan_id'];

        //cek jurusan apakah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM jurusan WHERE id = $jurusan_id");
        if (mysqli_num_rows($cek) == 0) {
            sendResponse(false, "Jurusan tidak ditemukan", null, 404);
        }

        //cek nim apakah unik atau tidak 
        $cek_nim = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nim = '$nim'");
        if (mysqli_num_rows($cek_nim) > 0) {
            sendResponse(false, "NIM sudah terdaftar", null, 400);
        }

       // insert data
       $q = "INSERT INTO mahasiswa (nama, nim, jurusan_id, created_at) VALUES ('$nama', '$nim', $jurusan_id, CURDATE())";
       if (mysqli_query($koneksi, $q)) {
           sendResponse(true, "Data tersimpan", ["id" => mysqli_insert_id($koneksi)], 201);
       } else {
           sendResponse(false, mysqli_error($koneksi), null, 500);
       }
        break;
}