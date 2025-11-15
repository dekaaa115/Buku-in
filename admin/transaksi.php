<?php
session_start();
require_once "../php/config.php";

// Cek login dan role admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Fetch semua transaksi + payment_status + denda
$transactions = [];
$sql = "SELECT 
            t.id, 
            b.title AS book_title, 
            u.full_name AS user_name, 
            t.borrow_date, 
            t.return_date,
            t.due_date,
            t.denda,
            t.payment_status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN books b ON t.book_id = b.id
        ORDER BY t.borrow_date DESC";

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    mysqli_free_result($result);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#212121]">

<div class="flex h-screen bg-[#212121] text-white">
    
    <!-- Sidebar -->
    <nav class="w-64 bg-[#333333] p-6 flex flex-col justify-between">
        <div>
            <div class="flex flex-col items-center mb-10">
                <img src="https://placehold.co/100x100/A78BFA/FFFFFF?text=A" class="rounded-full w-24 h-24 mb-4 border-2 border-purple-400">
                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                <p class="text-sm bg-green-500 px-3 py-1 rounded-full mt-2">Administrator</p>
            </div>
            <ul>
                <li class="nav-item mb-2"><a href="data-buku.php" class="flex p-3"><i data-lucide="book-copy" class="mr-3"></i>Data Buku</a></li>
                <li class="nav-item mb-2"><a href="data-anggota.php" class="flex p-3"><i data-lucide="users" class="mr-3"></i>Data Anggota</a></li>
                <li class="nav-item mb-2"><a href="transaksi.php" class="flex p-3 active-nav"><i data-lucide="arrow-right-left" class="mr-3"></i>Transaksi</a></li>
                <li class="nav-item mb-2"><a href="laporan.php" class="flex p-3"><i data-lucide="clipboard-list" class="mr-3"></i>Laporan</a></li>
            </ul>
        </div>
        <div>
            <a href="../php/logout.php" class="flex items-center p-3 nav-item"><i data-lucide="log-out" class="mr-3"></i>Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">

        <!-- Header -->
        <header class="bg-[#A78BFA] text-black p-4 flex justify-between items-center">
            <div class="flex items-center">
                <i data-lucide="library" class="mr-3"></i>
                <h1 class="text-xl font-semibold">Buku in - Sistem Informasi Perpustakaan</h1>
            </div>
            <a href="profil-admin.php">
                <img src="https://placehold.co/40x40/FFFFFF/333333?text=A" class="rounded-full w-10 h-10">
            </a>
        </header>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold text-gray-300 mb-4">Transaksi</h2>

            <div class="bg-[#333333] p-6 rounded-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-gray-300">
                        <thead>
                            <tr class="border-b border-gray-600">
                                <th class="p-4">No</th>
                                <th class="p-4">Judul Buku</th>
                                <th class="p-4">Nama Peminjam</th>
                                <th class="p-4">Tanggal Pinjam</th>
                                <th class="p-4">Tanggal Kembali</th>
                                <th class="p-4">Denda</th>
                                <th class="p-4">Status Pembayaran</th>
                                <th class="p-4">Update</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($transactions as $i => $transaction): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700">

                                <td class="p-4"><?= $i + 1 ?></td>
                                <td class="p-4"><?= htmlspecialchars($transaction['book_title']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($transaction['user_name']) ?></td>

                                <td class="p-4"><?= date('d F Y', strtotime($transaction['borrow_date'])) ?></td>

                                <td class="p-4">
                                    <?= $transaction['return_date'] 
                                        ? date('d F Y', strtotime($transaction['return_date'])) 
                                        : '<span class="text-red-400">Belum Kembali</span>' ?>
                                </td>

                                <!-- Denda -->
                                <td class="p-4">
                                    <?php if ($transaction['denda'] > 0): ?>
                                        Rp <?= number_format($transaction['denda'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-green-400">Tidak Ada</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status Pembayaran -->
                                <td class="p-4">
                                    <?php
                                        if ($transaction['denda'] == 0) {
                                            echo '<span class="text-green-400 font-bold">Tidak Ada Denda</span>';
                                        } elseif ($transaction['payment_status'] === "Paid") {
                                            echo '<span class="text-green-400 font-bold">Lunas</span>';
                                        } else {
                                            echo '<span class="text-red-400 font-bold">Belum Dibayar</span>';
                                        }
                                    ?>
                                </td>

                                <!-- Tombol Update -->
                                <td class="p-4">
                                    <?php if ($transaction['denda'] > 0 && $transaction['payment_status'] !== "Paid"): ?>
                                        <a href="../php/payment_update.php?id=<?= $transaction['id'] ?>"
                                           class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded"
                                           onclick="return confirm('Konfirmasi pembayaran?');">
                                            Sudah Dibayar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="8" class="text-center p-4">Tidak ada transaksi.</td></tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

</div>

<script>lucide.createIcons();</script>
</body>
</html>
