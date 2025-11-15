<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/Buku-in/php/config.php"; // path fix absolut

// Hanya admin yang boleh mengakses
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("Location: /Buku-in/login.php");
    exit;
}

// Validasi ID transaksi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request (missing id).");
}

$tx_id = (int) $_GET['id'];

// Ambil data transaksi
$sql = "SELECT status FROM transactions WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $tx_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die("Transaction not found.");
}

// Jika sudah dikembalikan → tidak bisa dikembalikan lagi
if ($data['status'] === 'Returned') {
    header("Location: /Buku-in/admin/laporan.php?msg=alreadyreturned");
    exit;
}

// Update status pengembalian
$update = "UPDATE transactions SET status='Returned', return_date=NOW() WHERE id = ?";
$stmt2 = mysqli_prepare($link, $update);
mysqli_stmt_bind_param($stmt2, "i", $tx_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

mysqli_close($link);

// Redirect selesai
header("Location: /Buku-in/admin/laporan.php?success=return");
exit;
