<?php
session_start();
require_once "../config/config.php";

// Cek login admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Ambil semua transaksi
$transactions = [];
$sql = "SELECT 
            t.id, 
            u.id AS user_id, 
            u.full_name, 
            b.title, 
            t.borrow_date, 
            t.due_date, 
            t.return_date, 
            t.status,
            COALESCE(t.denda, 0) AS denda,
            COALESCE(t.payment_status, 'Unpaid') AS payment_status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN books b ON t.book_id = b.id
        ORDER BY t.borrow_date DESC";

$result = mysqli_query($link, $sql);
if ($result && mysqli_num_rows($result) > 0) {
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
    <title>Laporan - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#212121]">

    <div class="flex h-screen bg-[#212121] text-white">
        
        <!-- Sidebar -->
        <nav class="w-64 bg-[#333333] p-6 flex flex-col justify-between">
            <div>
                <div class="flex flex-col items-center mb-10">
                    <img src="https://placehold.co/100x100/A78BFA/FFFFFF?text=A" 
                         alt="Admin Profile" 
                         class="rounded-full w-24 h-24 mb-4 border-2 border-purple-400">
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($_SESSION['full_name']); ?></h3>
                    <p class="text-sm bg-green-500 px-3 py-1 rounded-full mt-2">Administrator</p>
                </div>

                <ul>
                    <li class="nav-item rounded-lg mb-2">
                        <a href="data-buku.php" class="flex items-center p-3 rounded-lg">
                            <i data-lucide="book-copy" class="mr-3"></i>Data Buku
                        </a>
                    </li>
                    <li class="nav-item rounded-lg mb-2">
                        <a href="data-anggota.php" class="flex items-center p-3 rounded-lg">
                            <i data-lucide="users" class="mr-3"></i>Data Anggota
                        </a>
                    </li>
                    <li class="nav-item rounded-lg mb-2">
                        <a href="transaksi.php" class="flex items-center p-3 rounded-lg">
                            <i data-lucide="arrow-right-left" class="mr-3"></i>Transaksi
                        </a>
                    </li>
                    <li class="nav-item rounded-lg mb-2">
                        <a href="laporan.php" class="flex items-center p-3 rounded-lg active-nav">
                            <i data-lucide="clipboard-list" class="mr-3"></i>Laporan
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <a href="../config/logout.php" class="flex items-center p-3 rounded-lg nav-item">
                    <i data-lucide="log-out" class="mr-3"></i>Logout
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            
            <!-- Header -->
            <header class="bg-[#A78BFA] text-black p-4 flex justify-between items-center shadow-md">
                <div class="flex items-center">
                    <i data-lucide="library" class="mr-3"></i>
                    <h1 class="text-xl font-semibold">Buku in - Sistem Informasi Perpustakaan</h1>
                </div>
                <div class="flex items-center">
                    <span class="mr-4">Admin</span>
                    <a href="profil-admin.php">
                        <img src="https://placehold.co/40x40/FFFFFF/333333?text=A" 
                             alt="User Avatar" 
                             class="rounded-full w-10 h-10 cursor-pointer">
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 p-8 overflow-y-auto">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-300">Laporan</h2>
                        <p class="text-lg text-gray-400">Data Laporan Transaksi</p>
                    </div>
                </div>

                <div class="bg-[#333333] p-6 rounded-xl shadow-lg">

                    <!-- Notifikasi -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'return'): ?>
                        <div class="bg-blue-500 text-black px-4 py-2 rounded mb-4">
                            Status pengembalian berhasil diperbarui.
                        </div>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'pay'): ?>
                        <div class="bg-green-500 text-black px-4 py-2 rounded mb-4">
                            Status pembayaran denda berhasil diperbarui.
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-gray-300 text-sm">
                            
                            <thead>
                                <tr class="border-b border-gray-600">
                                    <th class="p-3">No</th>
                                    <th class="p-3">ID Anggota</th>
                                    <th class="p-3">Nama Anggota</th>
                                    <th class="p-3">Tanggal Pinjam</th>
                                    <th class="p-3">Tenggat Kembali</th>
                                    <th class="p-3">Tanggal Kembali</th>
                                    <th class="p-3">Denda</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Pembayaran</th>
                                    <th class="p-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $i => $t): ?>
                                        <?php
                                            $denda = (int)$t['denda'];
                                            $pay = $t['payment_status'];
                                            $status = $t['status'];
                                        ?>
                                        <tr class="border-b border-gray-700 hover:bg-gray-700">
                                            <td class="p-3"><?= $i + 1 ?></td>
                                            <td class="p-3"><?= htmlspecialchars($t['user_id']) ?></td>
                                            <td class="p-3"><?= htmlspecialchars($t['full_name']) ?></td>
                                            <td class="p-3"><?= date('d/m/Y', strtotime($t['borrow_date'])) ?></td>
                                            <td class="p-3"><?= date('d/m/Y', strtotime($t['due_date'])) ?></td>
                                            <td class="p-3">
                                                <?= $t['return_date'] ? date('d/m/Y', strtotime($t['return_date'])) : '-' ?>
                                            </td>

                                            <!-- Denda -->
                                            <td class="p-3">
                                                <?php if ($denda === 0): ?>
                                                    <span class="text-green-400 font-bold">Tidak Ada Denda</span>
                                                <?php else: ?>
                                                    Rp. <?= number_format($denda, 0, ',', '.') ?>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Status buku -->
                                            <td class="p-3">
                                                <?php if ($status === 'Returned'): ?>
                                                    <span class="text-green-400 font-bold">Sudah Dikembalikan</span>
                                                <?php else: ?>
                                                    <span class="text-yellow-400 font-bold">Belum Dikembalikan</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Status Pembayaran -->
                                            <td class="p-3">
                                                <?php if ($denda === 0): ?>
                                                    <span class="text-green-400 font-bold">Lunas</span>
                                                <?php else: ?>
                                                    <?= $pay === 'Paid' 
                                                        ? '<span class="text-green-400 font-bold">Lunas</span>' 
                                                        : '<span class="text-red-400 font-bold">Belum Dibayar</span>' ?>
                                                <?php endif; ?>
                                            </td>

                                            <!-- ACTION -->
                                            <td class="p-3 space-x-2 flex">
                                                <!-- Konfirmasi Pengembalian -->
                                                <?php if ($status !== 'Returned'): ?>
                                                    <a href="../config/confirm_return.php?id=<?= $t['id']; ?>"
                                                       class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded"
                                                       onclick="return confirm('Konfirmasi buku sudah dikembalikan?');">
                                                        Kembalikan
                                                    </a>
                                                <?php endif; ?>

                                                <!-- Konfirmasi Pembayaran Denda -->
                                                <?php if ($denda > 0 && $pay !== 'Paid'): ?>
                                                    <a href="../config/payment_update.php?id=<?= $t['id']; ?>"
                                                       class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1 px-2 rounded"
                                                       onclick="return confirm('Konfirmasi pembayaran denda?');">
                                                        Sudah Dibayar
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center p-4 text-gray-400">
                                            Tidak ada data transaksi.
                                        </td>
                                    </tr>
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
