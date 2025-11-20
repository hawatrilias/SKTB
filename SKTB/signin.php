<?php

session_start();

// =========================================================================
// 1. LOGIKA UNTUK MENAMPILKAN POP-UP (HARUS DI ATAS)
// =========================================================================
$showPopup = false;
if (isset($_SESSION['show_role_popup']) && $_SESSION['show_role_popup'] === true) {
    $showPopup = true;
    // Hapus flag agar pop-up tidak muncul lagi saat refresh
    unset($_SESSION['show_role_popup']); 
}
// =========================================================================


// ====== 2. KRITIS: REDIRECT JIKA SUDAH LOGIN (DENGAN KONDISI BARU) ======
// Jika role ada TAPI pop-up tidak diperlukan, baru redirect ke dashboard
if (isset($_SESSION['role']) && $showPopup === false) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin.php');
    } else if ($_SESSION['role'] == 'kasir') {
        header('Location: kasir.php');
    }
    exit();
}
// ===============================================

// 1. Ambil pesan error dari sesi sebelum dihapus
$errors = [
    'signin' => $_SESSION['signin_error'] ?? '',
    'signup' => $_SESSION['signup_error'] ?? '' // Tetap ada jika Anda punya form SignUp
];
$activeForm = $_SESSION['active_form'] ?? 'signin';

// 2. Hapus variabel sesi error setelah diambil (memastikan error hanya muncul 1x)
unset($_SESSION['signin_error']);
unset($_SESSION['signup_error']);
unset($_SESSION['active_form']);
// CATATAN: session_unset() DILARANG KERAS di sini karena menghapus sesi login!

function showError($error)
{
    // Tambahkan class CSS 'error-message' di file sign.css Anda untuk styling
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm)
{
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Floristy Muse</title>
    <link rel="stylesheet" href="sign.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .modal {
            display: none; /* Sembunyikan secara default */
            position: fixed;
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7); 
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.5);
            max-width: 450px;
            width: 90%;
        }

        .modal-content h3 {
            margin-top: 0;
            color: #670d2f; 
            font-size: 1.6rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .role-options {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .role-btn {
            flex: 1; 
            background-color: #670d2f; /* Warna default, sesuaikan */
            color: white;
            padding: 15px 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.5;
        }

        .role-btn:hover {
            background-color: #4a0020;
            transform: translateY(-2px);
        }
        
        .role-btn i {
            font-size: 2rem;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="pict/rose.jpeg" alt="Flower Icon">

        <div class="form-box <?= isActiveForm('signin', $activeForm); ?>" id="signin-form">
            <h1 class="h1in">LOGIN</h1>
            <form action="sign.php" method="post" class="sign-box">

                <?= showError($errors['signin']); ?>

                <input type="email" name="email" placeholder="Email" required><br><br>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <button type="submit" name="signin">LOGIN</button>
            </form>
        </div>
    </div>

    <div id="roleModal" class="modal">
        <div class="modal-content">
            <h3>Selamat Datang!</h3>
            <p>Pilih peran yang ingin Anda gunakan untuk melanjutkan:</p>
            <div class="role-options">
                
                <a href="admin.php" class="role-btn">
                    <i class="fas fa-user-shield"></i> 
                    <span>Dashboard Admin</span>
                </a>
                
                <a href="kasir.php" class="role-btn">
                    <i class="fas fa-cash-register"></i> 
                    <span>Dashboard Kasir</span>
                </a>
            </div>
            <div style="margin-top: 20px; font-size: 0.9em; color: #888;">
                <p>Pilihan Anda akan mengarahkan ke dashboard yang spesifik.</p>
            </div>
        </div>
    </div>

    <script>
        const roleModal = document.getElementById('roleModal');
        
        const showPopupFlag = <?= json_encode($showPopup); ?>;

        if (showPopupFlag) {
            roleModal.style.display = 'flex';
        }
    </script>

</body>

</html>