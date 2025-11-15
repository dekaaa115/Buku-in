<?php
// Initialize the session
session_start();
require_once "../php/config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Fetch unpaid fine transaction
$transaction_data = null;
$user_id = $_SESSION["id"];

$sql = "SELECT t.id, b.title as book_title, t.fine_amount 
        FROM transactions t 
        JOIN books b ON t.book_id = b.id 
        WHERE t.user_id = ? 
        AND t.fine_amount > 0 
        AND t.fine_paid_status = 'Unpaid'
        ORDER BY t.borrow_date ASC 
        LIMIT 1";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $transaction_data = mysqli_fetch_assoc($result);
        }
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Buku in</title>
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
                    <?php
                        $img_url = isset($_SESSION['profile_image_url']) && !empty($_SESSION['profile_image_url']) ? htmlspecialchars($_SESSION['profile_image_url']) : 'assets/images/default_avatar.png';
                        $fallback_text_initial = substr(htmlspecialchars($_SESSION['full_name']), 0, 1);
                        $fallback_url = "https://placehold.co/100x100/A78BFA/FFFFFF?text=" . $fallback_text_initial;
                    ?>
                    <img src="../<?php echo $img_url; ?>" 
                         onerror="this.onerror=null; this.src='<?php echo $fallback_url; ?>'" 
                         class="rounded-full w-24 h-24 mb-4 border-2 border-purple-400 object-cover">
                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                    <p class="text-sm bg-blue-500 px-3 py-1 rounded-full mt-2">Pengguna</p>
                </div>

                <ul>
                    <li class="nav-item mb-2"><a href="daftar-buku.php" class="flex items-center p-3 rounded-lg"><i data-lucide="book-open" class="mr-3"></i>Daftar Buku</a></li>
                    <li class="nav-item mb-2"><a href="data-form.php" class="flex items-center p-3 rounded-lg"><i data-lucide="file-pen-line" class="mr-3"></i>Data Form</a></li>
                    <li class="nav-item mb-2"><a href="transaksi.php" class="flex items-center p-3 rounded-lg active-nav"><i data-lucide="qr-code" class="mr-3"></i>Transaksi</a></li>
                    <li class="nav-item mb-2"><a href="history.php" class="flex items-center p-3 rounded-lg"><i data-lucide="history" class="mr-3"></i>History</a></li>
                </ul>
            </div>

            <div>
                <a href="../php/logout.php" class="flex items-center p-3 rounded-lg nav-item">
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
                    <span class="mr-4"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="profil-user.php">
                        <?php $header_fallback_url = "https://placehold.co/40x40/FFFFFF/333333?text=" . $fallback_text_initial; ?>
                        <img src="../<?php echo $img_url; ?>" 
                             onerror="this.onerror=null; this.src='<?php echo $header_fallback_url; ?>'" 
                             class="rounded-full w-10 h-10 cursor-pointer object-cover">
                    </a>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 p-8 overflow-y-auto">

                <h2 class="text-3xl font-bold text-gray-300">Transaksi</h2>
                <p class="text-lg text-gray-400 bg-[#4A4A4A] inline-block px-4 py-1 rounded-full mt-2">Pembayaran Denda</p>

                <div class="bg-[#333333] p-8 rounded-xl shadow-lg mt-6">

                    <?php if ($transaction_data): ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <!-- QRIS -->
                            <div class="text-center border-r border-gray-600 pr-8">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-semibold">QRIS</h3>
                                    <p class="font-bold text-lg">QRIS</p>
                                </div>

                                <p class="text-left text-gray-400">Jumlah Bayar</p>
                                <p class="text-left text-3xl font-bold mb-4">
                                    Rp. <?php echo number_format($transaction_data['fine_amount'], 0, ',', '.'); ?>
                                </p>

                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=example"
                                     class="mx-auto w-48 h-48 bg-white p-2 rounded-lg">

                                <p class="text-sm text-gray-500 mt-2">*Klik untuk memperbesar kode QR</p>

                                <!-- Cara Pembayaran -->
                                <a href="https://gopay.co.id/blog/cara-bayar-pakai-qris" 
                                   target="_blank"
                                   class="w-full mt-4 bg-[#4A4A4A] hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg block text-center">
                                   Cara Pembayaran
                                </a>

                                <!-- Button Selesai Bayar -->
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="transaction_id" value="<?php echo $transaction_data['id']; ?>">
                                    <button type="submit"
                                            class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                                        Saya Sudah Bayar
                                    </button>
                                </form>

                            </div>

                            <!-- DETAIL -->
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Detail Transaksi</h3>

                                <div class="space-y-4 text-gray-300">

                                    <div class="border-b border-gray-600 pb-2">
                                        <p class="text-gray-400">Nama Peminjam:</p>
                                        <p><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                                    </div>

                                    <div class="border-b border-gray-600 pb-2">
                                        <p class="text-gray-400">Judul Buku:</p>
                                        <p><?php echo htmlspecialchars($transaction_data['book_title']); ?></p>
                                    </div>

                                    <div>
                                        <p class="text-gray-400 font-semibold mb-2">Rincian Pembayaran:</p>

                                        <div class="flex justify-between">
                                            <span>Denda buku "<?php echo htmlspecialchars($transaction_data['book_title']); ?>"</span>
                                            <span>Rp <?php echo number_format($transaction_data['fine_amount'], 0, ',', '.'); ?></span>
                                        </div>

                                        <div class="flex justify-between font-bold mt-4 border-t border-gray-500 pt-2">
                                            <span>Total</span>
                                            <span>Rp <?php echo number_format($transaction_data['fine_amount'], 0, ',', '.'); ?></span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- Jika tidak ada denda -->
                        <div class="text-center text-gray-400 p-10">
                            <i data-lucide="badge-check" class="w-16 h-16 mx-auto mb-4 text-green-500"></i>
                            <h3 class="text-2xl font-bold">Tidak Ada Denda</h3>
                            <p class="mt-2">Anda tidak memiliki denda yang perlu dibayarkan. Terima kasih!</p>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
