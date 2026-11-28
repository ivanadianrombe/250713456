<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');

    if ($product_id <= 0 || $rating < 1 || $rating > 5 || $komentar === '') {
        die('Data review tidak valid.');
    }

    $cekProduk = $pdo->prepare(
        "SELECT id FROM products WHERE id = :id"
    );

    $cekProduk->execute([':id' => $product_id]);

    if (!$cekProduk->fetch()) {
        die('Produk tidak ditemukan.');
    }

    $query = $pdo->prepare(
        "INSERT INTO reviews
        (product_id, user_id, rating, komentar)
        VALUES
        (:product_id, :user_id, :rating, :komentar)"
    );

    $query->execute([
        ':product_id' => $product_id,
        ':user_id' => $_SESSION['user_id'],
        ':rating' => $rating,
        ':komentar' => $komentar
    ]);

    header('Location: detail_produk.php?id=' . $product_id . '#review-section');
    exit;
}

header('Location: katalog.php');
exit;
?>