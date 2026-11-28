<?php
session_start();

$pdo = require __DIR__ . '/database.php';

$keranjang = $_SESSION['keranjang'] ?? [];

$total = 0;
$produkKeranjang = [];

if (!empty($keranjang)) {

    $ids = array_keys($keranjang);
    $placeholder = implode(',', array_fill(0, count($ids), '?'));

    $query = $pdo->prepare(
        "SELECT * FROM products WHERE id IN ($placeholder)"
    );

    $query->execute($ids);

    $produkKeranjang = $query->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Sweet Crumbs</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            align-items: center;
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        .cart-item img {
            width: 110px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-info {
            flex: 1;
        }

        .cart-info h3 {
            color: #553b2d;
        }

        .cart-info p {
            color: #8d7768;
        }

        .cart-item input {
            width: 65px;
            padding: 8px;
        }

        .cart-item button,
        .checkout-button {
            background: #c87945;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
        }

        .cart-total {
            text-align: right;
            margin-top: 30px;
            color: #553b2d;
        }

        .empty-cart {
            text-align: center;
            padding: 50px;
        }

        @media(max-width: 600px) {
            .cart-item {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

<div class="cart-container">

    <h1>Keranjang Belanja</h1>

    <a href="katalog.php">← Kembali ke Katalog</a>

    <?php if (empty($produkKeranjang)): ?>

        <div class="empty-cart">
            <h2>Keranjang masih kosong</h2>
            <p>Yuk, pilih roti dan pastry favoritmu!</p>
            <a href="katalog.php">Lihat Katalog</a>
        </div>

    <?php else: ?>

        <?php foreach ($produkKeranjang as $item): ?>

            <?php
                $id = (int)$item['id'];
                $jumlah = (int)($keranjang[$id] ?? 0);
                $subtotal = $item['harga'] * $jumlah;
                $total += $subtotal;
            ?>

            <div class="cart-item">

                <img
                    src="images/<?= htmlspecialchars($item['gambar']) ?>"
                    alt="<?= htmlspecialchars($item['nama_produk']) ?>"
                >

                <div class="cart-info">

                    <h3><?= htmlspecialchars($item['nama_produk']) ?></h3>

                    <p>
                        Rp<?= number_format($item['harga'], 0, ',', '.') ?>
                    </p>

                    <p>
                        Subtotal:
                        Rp<?= number_format($subtotal, 0, ',', '.') ?>
                    </p>

                </div>

                <form action="proses_keranjang.php" method="POST">

                    <input type="hidden" name="aksi" value="update">

                    <input type="hidden" name="product_id"
                           value="<?= $id ?>">

                    <input type="number" name="jumlah"
                           value="<?= $jumlah ?>" min="0" max="99">

                    <button type="submit">Update</button>

                </form>

                <form action="proses_keranjang.php" method="POST">

                    <input type="hidden" name="aksi" value="hapus">

                    <input type="hidden" name="product_id"
                           value="<?= $id ?>">

                    <button type="submit"
                            onclick="return confirm('Hapus produk ini?')">
                        Hapus
                    </button>

                </form>

            </div>

        <?php endforeach; ?>

        <div class="cart-total">

            <h2>
                Total:
                Rp<?= number_format($total, 0, ',', '.') ?>
            </h2>

            <a href="checkout.php" class="checkout-button">
                Lanjut Checkout
            </a>

        </div>

    <?php endif; ?>

</div>

</body>
</html>