<?php
try {
  $pdo = new PDO("mysql:host=site-shop;dbname=shopbase", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Ошибка подключения: " . $e->getMessage());
}
?>
<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="style/style.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<?php include 'includes/db.php'; ?>

<div class="news-slider-wrapper">
  <div class="news-slider">
    <div class="swiper mySwiper" style="width: 80%;">
      <div class="swiper-wrapper">
        <?php
        try {
          $stmt = $pdo->query("SELECT * FROM news ORDER BY date DESC LIMIT 5");
          if ($stmt->rowCount() > 0) {
            while ($news = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<div class='swiper-slide'>";
              echo "<h3>" . htmlspecialchars($news['title']) . "</h3>";
              echo "<p class='news-date'>" . date('d.m.Y', strtotime($news['date'])) . "</p>";
              echo "<p>" . htmlspecialchars($news['content']) . "</p>";
              echo "</div>";
            }
          } else {
            echo "<div class='swiper-slide'><p>Нет новостей на данный момент.</p></div>";
          }
        } catch (PDOException $e) {
          echo "<div class='swiper-slide'><p>Ошибка загрузки новостей.</p></div>";
        }
        ?>
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    new Swiper('.mySwiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  });
</script>

<div class="center-product">
  <h2>Товары недели</h2>
  <div class="products-slider" id="productsSlider">
    <?php
    try {
      $stmt = $pdo->prepare("SELECT * FROM products WHERE is_weekly = 1");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $imagePath = trim($row['image']);
          if (empty($imagePath) || !file_exists($imagePath)) {
            $imagePath = 'gui/null.png';
          }

          echo '<a href="product-details.php?id=' . urlencode($row['id']) . '" class="product-link">';
          echo '<div class="product">';
          echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row['name']) . '" />';
          echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
          echo '<p>Категория: ' . htmlspecialchars($row['category']) . '</p>';
          echo '<p>Цена: ' . htmlspecialchars($row['price']) . ' руб.</p>';
          echo '</div></a>';
        }
      } else {
        echo '<p>Нет товаров для отображения.</p>';
      }
    } catch (PDOException $e) {
      echo '<p>Ошибка загрузки товаров недели.</p>';
    }
    ?>
  </div>
</div>

<div class="all-products-list">
  <h2>Все товары</h2>
  <?php
  try {
    $res_all = $pdo->query("SELECT * FROM products");
    if ($res_all && $res_all->rowCount() > 0) {
      while ($row = $res_all->fetch(PDO::FETCH_ASSOC)) {
        $imagePath = trim($row['image']);
        if (empty($imagePath) || !file_exists($imagePath)) {
          $imagePath = 'gui/null.png';
        }

        echo '<div class="product-item">';
        echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row['name']) . '" />';
        echo '<div class="product-info">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>Категория: ' . htmlspecialchars($row['category']) . '</p>';
        echo '<p>Цена: ' . htmlspecialchars($row['price']) . ' руб.</p>';
        echo '</div>';
  
        echo '<div class="product-buttons">';
        echo '<a href="#" class="add-to-cart" data-product-id="' . htmlspecialchars($row['id']) . '">Добавить в корзину</a>';
        echo '<a href="product-details.php?id=' . urlencode($row['id']) . '" class="more-info">Дополнительная информация</a>';
        echo '</div>';

        if (!empty($row['description'])) {
          echo '<div class="product-details" id="details-' . htmlspecialchars($row['id']) . ' " style="display:none;">';
          echo '<p>' . htmlspecialchars($row['description']) . '</p>';
          echo '</div>';
        }

        echo '</div>';
      }
    } else {
      echo '<p>Нет доступных товаров.</p>';
    }
  } catch (PDOException $e) {
    echo '<p>Ошибка загрузки товаров.</p>';
  }
  ?>

  <div class="center-container">
    <a href="catalog.php" class="view-all-button">Посмотреть весь каталог</a>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const productId = this.getAttribute('data-product-id');

        fetch('../core/add_cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: productId })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert('Товар добавлен в корзину!');
              const cartLink = document.getElementById('cart-link');
              if (cartLink && data.cartCount !== undefined) {
                cartLink.innerHTML = `Корзина (${data.cartCount})`;
              }
            } else {
              alert('Ошибка: ' + data.message);
            }
          })
          .catch(error => console.error('Ошибка:', error));
      });
    });
  });
</script>

<?php include 'includes/footer.php'; ?>