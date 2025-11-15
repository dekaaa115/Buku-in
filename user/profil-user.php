<?php
session_start();
require_once "../php/config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$id = $_SESSION["id"];

// ===================================================================
//  HANDLE UPDATE PROFILE + UPLOAD FOTO
// ===================================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name    = $_POST['full_name'];
    $nickname     = $_POST['nickname'];
    $gender       = $_POST['gender'];
    $kelas        = $_POST['kelas'];
    $phone_number = $_POST['phone_number'];
    $address      = $_POST['address'];

    // ---------------------------------------------------------------
    //  HANDLE FOTO PROFIL
    // ---------------------------------------------------------------
    $profile_image_url = null;

    if (!empty($_FILES["profile_image"]["name"])) {

        $target_dir = "../uploads/profile/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = "user_" . $id . "_" . time() . ".jpg";
        $target_file = $target_dir . $file_name;

        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);

        $profile_image_url = "uploads/profile/" . $file_name;

        $sql_img = "UPDATE users SET profile_image_url=? WHERE id=?";
        $stmt_img = mysqli_prepare($link, $sql_img);
        mysqli_stmt_bind_param($stmt_img, "si", $profile_image_url, $id);
        mysqli_stmt_execute($stmt_img);
        mysqli_stmt_close($stmt_img);
    }

    // ---------------------------------------------------------------
    //  UPDATE DATA USER
    // ---------------------------------------------------------------
    $sql_update = "UPDATE users SET 
        full_name=?, nickname=?, gender=?, kelas=?, phone_number=?, address=? 
        WHERE id=?";

    $stmt = mysqli_prepare($link, $sql_update);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $full_name, $nickname, $gender, $kelas, $phone_number, $address, $id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: profil-user.php?update=success");
    exit;
}

// ===================================================================
//  LOAD DATA USER
// ===================================================================
$sql = "SELECT id, full_name, nickname, email, kelas, phone_number, address, gender, profile_image_url 
        FROM users WHERE id = ?";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil User - Buku in</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body class="bg-[#212121] text-white">

<div class="flex h-screen">

    <!-- ================= SIDEBAR ================= -->
    <nav class="w-64 bg-[#333333] p-6 flex flex-col justify-between">
        <div>
            <div class="flex flex-col items-center mb-10">

                <!-- FOTO PROFIL -->
                <img src="../<?php echo htmlspecialchars($user['profile_image_url']); ?>"
                     onerror="this.onerror=null; this.src='https://placehold.co/100x100/A78BFA/FFFFFF?text=<?php echo strtoupper(substr($user['full_name'],0,1)); ?>';"
                     class="rounded-full w-24 h-24 mb-4 border-2 border-purple-400 object-cover">

                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p class="text-sm bg-blue-500 px-3 py-1 rounded-full mt-2">Pengguna</p>
            </div>

            <ul>
                <li class="mb-2"><a href="daftar-buku.php" class="flex items-center p-3 rounded-lg"><i data-lucide="book-open" class="mr-3"></i>Daftar Buku</a></li>
                <li class="mb-2"><a href="data-form.php" class="flex items-center p-3 rounded-lg"><i data-lucide="file-pen-line" class="mr-3"></i>Data Form</a></li>
                <li class="mb-2"><a href="transaksi.php" class="flex items-center p-3 rounded-lg"><i data-lucide="qr-code" class="mr-3"></i>Transaksi</a></li>
                <li class="mb-2"><a href="history.php" class="flex items-center p-3 rounded-lg"><i data-lucide="history" class="mr-3"></i>History</a></li>
            </ul>
        </div>

        <a href="../php/logout.php" class="flex items-center p-3 rounded-lg"><i data-lucide="log-out" class="mr-3"></i>Logout</a>
    </nav>

    <!-- ================= MAIN ================= -->
    <main class="flex-1 flex flex-col">

        <!-- HEADER -->
        <header class="bg-[#A78BFA] text-black p-4 flex justify-between items-center shadow-md">
            <div class="flex items-center">
                <i data-lucide="library" class="mr-3"></i>
                <h1 class="text-xl font-semibold">Buku in - Sistem Informasi Perpustakaan</h1>
            </div>

            <div class="flex items-center">
                <span class="mr-4"><?php echo htmlspecialchars($user['full_name']); ?></span>
                <img src="../<?php echo htmlspecialchars($user['profile_image_url']); ?>"
                     onerror="this.onerror=null; this.src='https://placehold.co/40x40/FFFFFF/333333?text=<?php echo strtoupper(substr($user['full_name'],0,1)); ?>';"
                     class="rounded-full w-10 h-10 object-cover">
            </div>
        </header>

        <div class="flex-1 p-8 overflow-y-auto">

            <?php if(isset($_GET['update'])) { ?>
                <div class="bg-green-500 text-black px-4 py-2 rounded mb-4">
                    ✔ Profile berhasil diperbarui
                </div>
            <?php } ?>

            <h2 class="text-3xl font-bold mb-6 text-gray-300">My Profile</h2>

            <div class="bg-[#333333] p-8 rounded-xl shadow-lg">

                <!-- FOTO & INFO -->
                <div class="flex items-center mb-8">
                    <img src="../<?php echo htmlspecialchars($user['profile_image_url']); ?>"
                         onerror="this.onerror=null; this.src='https://placehold.co/100x100/A78BFA/FFFFFF?text=<?php echo strtoupper(substr($user['full_name'],0,1)); ?>';"
                         class="rounded-full w-24 h-24 mr-6 border-2 border-purple-400 object-cover">

                    <div>
                        <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <!-- ================= FORM ================= -->
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-gray-400 mb-2">Update Foto Profil</label>
                        <input type="file" name="profile_image" class="w-full bg-[#4F4F4F] p-3 rounded">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Full Name</label>
                        <input type="text" name="full_name" class="w-full bg-[#4F4F4F] p-3 rounded"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Nickname</label>
                        <input type="text" name="nickname" class="w-full bg-[#4F4F4F] p-3 rounded"
                               value="<?php echo htmlspecialchars($user['nickname']); ?>">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Gender</label>
                        <select name="gender" class="w-full bg-[#4F4F4F] p-3 rounded">
                            <option value="Male"   <?php echo ($user['gender']=="Male")?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['gender']=="Female")?'selected':''; ?>>Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Kelas</label>
                        <input type="text" name="kelas" class="w-full bg-[#4F4F4F] p-3 rounded"
                               value="<?php echo htmlspecialchars($user['kelas']); ?>">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Phone Number</label>
                        <input type="text" name="phone_number" class="w-full bg-[#4F4F4F] p-3 rounded"
                               value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2">Address</label>
                        <input type="text" name="address" class="w-full bg-[#4F4F4F] p-3 rounded"
                               value="<?php echo htmlspecialchars($user['address']); ?>">
                    </div>

                    <div class="col-span-2 flex justify-end mt-4">
                        <button type="submit"
                            class="bg-[#A78BFA] hover:bg-purple-600 px-8 py-3 rounded-lg font-bold text-black">
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>
