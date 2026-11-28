
<?php
session_start();

$pdo = require __DIR__ . '/database.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {

    $id = (int)($_POST['product_id'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 1);

    $query = $pdo->prepare(
        "SELECT id FROM products WHERE id = :id"
    );

    $query->execute([':id' => $id]);

    if (!$query->fetch() || $jumlah < 1 || $jumlah > 99) {
        header('Location: katalog.php');
        exit;
    }

    if (isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id] += $jumlah;
    } else {
        $_SESSION['keranjang'][$id] = $jumlah;
    }

    header('Location: keranjang.php');
    exit;
}

if ($aksi === 'hapus') {

    $id = (int)($_POST['product_id'] ?? 0);

    unset($_SESSION['keranjang'][$id]);

    header('Location: keranjang.php');
    exit;
}

if ($aksi === 'update') {

    $id = (int)($_POST['product_id'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 1);

    if (isset($_SESSION['keranjang'][$id])) {
        if ($jumlah <= 0) {
            unset($_SESSION['keranjang'][$id]);
        } else {
            $_SESSION['keranjang'][$id] = min($jumlah, 99);
        }
    }

    header('Location: keranjang.php');
    exit;
}

header('Location: katalog.php');
exit;
?>
