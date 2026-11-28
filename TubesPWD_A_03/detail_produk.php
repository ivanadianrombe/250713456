
<?php
session_start();

$pdo = require __DIR__ . '/database.php';

// MENGAMBIL DATA PRODUK
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);

$item = $stmt->fetch();

if (!$item) {
    die("Produk tidak ditemukan.");
}

// MENGATUR GAMBAR PRODUK
$gambar = $item['gambar'];

if (strpos($gambar, 'images/') !== 0) {
    $gambar = 'images/' . $gambar;
}

// PESAN REVIEW
$pesan = '';

// PROSES MENAMBAHKAN REVIEW
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_review'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $pesan = "Silakan pilih rating 1 sampai 5.";

    } elseif ($komentar === '') {
        $pesan = "Komentar tidak boleh kosong.";

    } else {

        $insert = $pdo->prepare("
            INSERT INTO reviews
            (product_id, user_id, rating, komentar)
            VALUES (?, ?, ?, ?)
        ");

        $insert->execute([
            $id,
            $_SESSION['user_id'],
            $rating,
            $komentar
        ]);

        header("Location: detail_produk.php?id=$id#review");
        exit;
    }
}

// MENGAMBIL DATA REVIEW
$reviewQuery = $pdo->prepare("
    SELECT reviews.*, users.full_name
    FROM reviews
    JOIN users ON reviews.user_id = users.id
    WHERE reviews.product_id = ?
    ORDER BY reviews.created_at DESC
");

$reviewQuery->execute([$id]);
$reviews = $reviewQuery->fetchAll();

// MENGHITUNG RATING
$ratingQuery = $pdo->prepare("
    SELECT AVG(rating) AS rata_rata, COUNT(*) AS jumlah
    FROM reviews
    WHERE product_id = ?
");

$ratingQuery->execute([$id]);
$ratingData = $ratingQuery->fetch();

$rataRata = $ratingData['rata_rata'] ?? 0;
$jumlahReview = $ratingData['jumlah'] ?? 0;

// MENGAMBIL PRODUK REKOMENDASI
$rekomendasiQuery = $pdo->prepare("
    SELECT * FROM products
    WHERE kategori = ? AND id != ?
    ORDER BY id ASC
    LIMIT 4
");

$rekomendasiQuery->execute([
    $item['kategori'],
    $id
]);

$rekomendasi = $rekomendasiQuery->fetchAll();

// FUNGSI BINTANG
function bintang($nilai) {
    $hasil = '';

    for ($i = 1; $i <= 5; $i++) {
        $hasil .= ($i <= round($nilai)) ? '★' : '☆';
    }

    return $hasil;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($item['nama_produk']) ?> | Sweet Crumbs
    </title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        body {
            background: #fffaf3;
            color: #553b2d;
        }

        .detail-container {
            max-width: 1100px;
            margin: 45px auto;
            padding: 20px;
        }

        .back-link {
            color: #8d7768;
            text-decoration: none;
        }

        .detail-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            align-items: center;
            margin-top: 20px;
        }

        .detail-content > img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 18px;
        }

        .detail-info h1 {
            font-size: 34px;
            margin: 12px 0;
        }

        .detail-price {
            color: #c87945;
            font-size: 26px;
            font-weight: bold;
        }

        .rating-stars {
            color: #e5a43b;
            font-size: 22px;
            letter-spacing: 2px;
        }

        .main-button {
            background: #c87945;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px 20px;
            cursor: pointer;
            margin-top: 10px;
        }

        .main-button:hover {
            background: #a95d30;
        }

        .section-title {
            margin: 65px 0 25px;
            font-size: 27px;
        }

        .review-summary,
        .review-form,
        .review-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #eadbce;
            margin-bottom: 20px;
        }

        .review-form select,
        .review-form textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0 18px;
            border: 1px solid #eadbce;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .review-form textarea {
            min-height: 110px;
            resize: vertical;
        }

        .review-date {
            color: #8d7768;
            font-size: 13px;
        }

        .message {
            background: #f5e5d5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product-card {
            background: white;
            border-radius: 13px;
            overflow: hidden;
            border: 1px solid #eadbce;
            transition: .3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card a {
            color: inherit;
            text-decoration: none;
        }

        .product-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-info h3 {
            font-size: 16px;
        }

        .product-info h4 {
            color: #c87945;
        }

        @media(max-width: 750px) {
            .detail-content {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

    </style>
</head>

<body>

<div class="detail-container">

    <a href="katalog.php" class="back-link">
        ← Kembali ke Katalog
    </a>

    <!-- DETAIL PRODUK -->

    <div class="detail-content">

        <img
            src="<?= htmlspecialchars($gambar) ?>"
            alt="<?= htmlspecialchars($item['nama_produk']) ?>"
        >

        <div class="detail-info">

            <p><?= htmlspecialchars($item['kategori']) ?></p>

            <h1>
                <?= htmlspecialchars($item['nama_produk']) ?>
            </h1>

            <div class="rating-stars">
                <?= bintang($rataRata) ?>
            </div>

            <p>
                <?= number_format($rataRata, 1) ?> / 5
                (<?= $jumlahReview ?> ulasan)
            </p>

            <p class="detail-price">
                Rp <?= number_format($item['harga'], 0, ',', '.') ?>
            </p>

            <p>
                <?= htmlspecialchars($item['deskripsi']) ?>
            </p>

            <!-- FORM KERANJANG -->

            <form action="proses_keranjang.php" method="POST">

                <input type="hidden" name="aksi" value="tambah">

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= $item['id'] ?>"
                >

                <label>Jumlah:</label>

                <input
                    type="number"
                    name="jumlah"
                    value="1"
                    min="1"
                    max="99"
                    required
                >

                <br>

                <button type="submit" class="main-button">
                    Masukkan ke Keranjang
                </button>

            </form>

        </div>

    </div>


    <!-- BAGIAN REVIEW -->

    <section id="review">

        <h2 class="section-title">
            Ulasan Pelanggan
        </h2>

        <div class="review-summary">

            <h1>
                <?= number_format($rataRata, 1) ?> / 5
            </h1>

            <div class="rating-stars">
                <?= bintang($rataRata) ?>
            </div>

            <p>
                Berdasarkan <?= $jumlahReview ?> ulasan pelanggan
            </p>

        </div>


        <!-- FORM REVIEW -->

        <div class="review-form">

            <h3>Tulis Ulasanmu</h3>

            <?php if ($pesan): ?>

                <div class="message">
                    <?= htmlspecialchars($pesan) ?>
                </div>

            <?php endif; ?>


            <?php if (isset($_SESSION['user_id'])): ?>

                <form method="POST">

                    <label>Berikan Rating</label>

                    <select name="rating" required>

                        <option value="">Pilih rating</option>

                        <option value="5">★★★★★ - Sangat Baik</option>

                        <option value="4">★★★★ - Baik</option>

                        <option value="3">★★★ - Cukup</option>

                        <option value="2">★★ - Kurang</option>

                        <option value="1">★ - Sangat Kurang</option>

                    </select>

                    <label>Komentar</label>

                    <textarea
                        name="komentar"
                        placeholder="Bagaimana pengalamanmu dengan produk ini?"
                        required
                    ></textarea>

                    <button
                        type="submit"
                        name="kirim_review"
                        class="main-button"
                    >
                        Kirim Ulasan
                    </button>

                </form>

            <?php else: ?>

                <p>
                    Silakan
                    <a href="login.php">login</a>
                    terlebih dahulu untuk memberikan ulasan.
                </p>

            <?php endif; ?>

        </div>


        <!-- DAFTAR REVIEW -->

        <h3>Review Pelanggan</h3>

        <?php if (count($reviews) > 0): ?>

            <?php foreach ($reviews as $review): ?>

                <div class="review-card">

                    <h4>
                        <?= htmlspecialchars($review['full_name']) ?>
                    </h4>

                    <div class="rating-stars">
                        <?= bintang($review['rating']) ?>
                    </div>

                    <p>
                        <?= nl2br(htmlspecialchars($review['komentar'])) ?>
                    </p>

                    <span class="review-date">
                        <?= date(
                            'd-m-Y',
                            strtotime($review['created_at'])
                        ) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>
                Belum ada ulasan untuk produk ini.
            </p>

        <?php endif; ?>

    </section>


    <!-- PRODUK REKOMENDASI -->

    <section>

        <h2 class="section-title">
            Kamu Mungkin Juga Suka
        </h2>

        <div class="product-grid">

            <?php foreach ($rekomendasi as $produkLain): ?>

                <?php
                    $gambarLain = $produkLain['gambar'];

                    if (strpos($gambarLain, 'images/') !== 0) {
                        $gambarLain = 'images/' . $gambarLain;
                    }
                ?>

                <div class="product-card">

                    <a href="detail_produk.php?id=<?= $produkLain['id'] ?>">

                        <img
                            src="<?= htmlspecialchars($gambarLain) ?>"
                            alt="<?= htmlspecialchars($produkLain['nama_produk']) ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?= htmlspecialchars($produkLain['nama_produk']) ?>
                            </h3>

                            <h4>
                                Rp <?= number_format($produkLain['harga'], 0, ',', '.') ?>
                            </h4>

                            <p>Lihat Detail →</p>

                        </div>

                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</div>


<div class="footer">
    <p>Sweet Crumbs Bakery &copy; 2026</p>
</div>

</body>
</html>
