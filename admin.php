<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli('site-shop', 'root', '', 'shopbase');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $id");
}
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM products WHERE id = $id");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $password, $is_admin);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'];
    $stmt = $conn->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $price, $description);
    $stmt->execute();
}
?>

<div id="editUserPanel" class="edit-panel" style="display:none;">
    <div class="edit-panel-content">
        <button class="close-btn" onclick="closeEditUserPanel()">×</button>
        <h2>Редактировать пользователя</h2>
        <form id="editProductForm" onsubmit="event.preventDefault(); saveUser();">
            <input type="hidden" id="editUserId" />

            <label for="editUsername">Имя:</label>
            <input type="text" id="editUsername" name="username" />

            <label for="editPassword">Пароль:</label>
            <input type="password" id="editPassword" name="password" />

            <button type="submit" class="button">Сохранить</button>
        </form>
    </div>
</div>

<div id="editPanel" class="edit-panel" style="display:none;">
    <div class="edit-panel-content">
        <button class="close-btn" onclick="closeEditPanel()">×</button>
        <h2>Редактировать товар</h2>
        <form id="editProductForm" onsubmit="event.preventDefault(); saveProduct();">
            <input type="hidden" id="editProductId" name="id" />

            <label for="name">Название:</label>
            <input type="text" id="name" name="name" placeholder="" />

            <label for="category">Категория:</label>
            <input type="text" id="category" name="category" placeholder="" />

            <label for="price">Цена:</label>
            <input type="number" id="price" name="price" placeholder="" />

            <label for="image_url">URL изображения:</label>
            <input type="text" id="image_url" name="image_url" placeholder="" />

            <label for="description">Описание:</label>
            <input type="text" id="description" name="description" placeholder="" class="large-input" />

            <label for="stock_quantity">Количество:</label>
            <input type="number" id="stock_quantity" name="stock_quantity" placeholder="" />

            <button type="submit" class="button">Сохранить</button>
        </form>
    </div>
</div>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style/style_admin.css">
</head>
<?php include 'includes/header.php'; ?>
<style>
    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #4CAF50;
        border: none;
        font-size: 24px;
        cursor: pointer;
        width: 40px;
    }

    .close-btn:hover {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color:#45a049;
        border: none;
        font-size: 24px;
        cursor: pointer;
    }
</style>

<body>
    <h1>Админ-панель</h1>

    <div class="accordion-container">
        <button class="accordion-button" onclick="togglePanelT()">Пользователи</button>
        <div class="accordion-panel" id="productsPanelT">
            <div class="products-listT">
                <ul>
                    <?php
                    $result_users = $conn->query("SELECT * FROM users");
                    while ($user = $result_users->fetch_assoc()) {
                        echo "<strong>ID:</strong> {$user['id']} <strong>Имя</strong>: {$user['username']}";
                        echo "<div class='user-actions'>";
                        echo "    <div class='buttons-container'>";
                        echo "<a class='button-link' href='#' onclick='openEditUserPanel({$user['id']}); return false;'>Редактировать</a>";
                        echo "        <a class='button-link' href='?delete_user={$user['id']}' onclick='return confirm(\"Удалить этого пользователя?\")'>Удалить</a>";
                        echo "    </div>";
                        echo "</div>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function openEditUserPanel(userId) {
            const panel = document.getElementById('editUserPanel');

            fetch('core/get_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Ошибка: ' + data.error);
                        return;
                    }

                    document.getElementById('editUserId').value = data.id;
                    document.getElementById('editUsername').value = data.username;
                    document.getElementById('editPassword').value = '';

                    panel.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Ошибка при получении данных:', error);
                    alert('Произошла ошибка');
                });
        }

        function saveUser() {
            const id = document.getElementById('editUserId').value;
            const username = document.getElementById('editUsername').value.trim();
            const password = document.getElementById('editPassword').value.trim();

            fetch('core/save_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    id: id,
                    username: username,
                    password: password
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Пользователь успешно сохранён');
                        closeEditUserPanel();
                        location.reload();
                    } else if (data.error) {
                        alert('Ошибка: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при сохранении');
                });
        }

        function closeEditUserPanel() {
            document.getElementById('editUserPanel').style.display = 'none';
        }
    </script>

    <div class="accordion-container">
        <button class="accordion-button" onclick="togglePanel()">Товары</button>
        <div class="accordion-panel" id="productsPanel">
            <div class="products-list">
                <?php
                $result_products = $conn->query("SELECT * FROM products");
                while ($product = $result_products->fetch_assoc()) {
                    $image = !empty($product['image']) && file_exists($product['image']) ? $product['image'] : 'gui/null.png';

                    echo "<div class='product-card'>";
                    echo "<img src='{$image}' alt='{$product['name']}' class='product-image' />";
                    echo "<h3 class='product-name'>{$product['name']}</h3>";
                    echo "<p class='product-price'>Цена: {$product['price']} руб.</p>";
                    echo "<p class='product-price'>Количество: {$product['stock_quantity']} шт.</p>";
                    echo "<button class='edit-button' onclick='openEditPanel({$product['id']})'>Редактировать</button>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function togglePanel() {
            document.getElementById('productsPanel').classList.toggle('open');
        }

        function togglePanelT() {
            document.getElementById('productsPanelT').classList.toggle('open');
        }

        function closeEditPanel() {
            document.getElementById('editPanel').style.display = 'none';
        }

        function openEditPanel(productId) {
            const panel = document.getElementById('editPanel');
            panel.style.display = 'none';

            fetch('core/get_product.php?id=' + productId)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error('Ответ сервера: ' + text); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert('Ошибка: ' + data.error);
                        return;
                    }

                    document.getElementById('editProductId').value = data.id || '';

                    document.getElementById('name').placeholder = data.name || '';
                    document.getElementById('category').placeholder = data.category || '';
                    document.getElementById('price').placeholder = data.price || '';
                    document.getElementById('image_url').placeholder = data.image_url || '';
                    document.getElementById('description').placeholder = data.description || '';
                    document.getElementById('stock_quantity').placeholder = data.stock_quantity || '';

                    document.getElementById('name').value = '';
                    document.getElementById('category').value = '';
                    document.getElementById('price').value = '';
                    document.getElementById('image_url').value = '';
                    document.getElementById('description').value = '';
                    document.getElementById('stock_quantity').value = '';

                    panel.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Ошибка при получении данных:', error);
                    alert(error.message);
                });
        }

        function saveProduct() {
            const id = document.getElementById('editProductId').value;

            const nameField = document.getElementById('name');
            const categoryField = document.getElementById('category');
            const priceField = document.getElementById('price');
            const imageField = document.getElementById('image_url');
            const descriptionField = document.getElementById('description');
            const stockQuantityField = document.getElementById('stock_quantity');

            const name = nameField.value.trim() || nameField.placeholder;
            const category = categoryField.value.trim() || categoryField.placeholder;
            const price = priceField.value.trim() || priceField.placeholder;
            const image_url_value = imageField.value.trim() || imageField.placeholder;
            const description = descriptionField.value.trim() || descriptionField.placeholder;
            const stock_quantity = stockQuantityField.value.trim() || stockQuantityField.placeholder;

            fetch('core/save_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    id: id,
                    name: name,
                    category: category,
                    price: price,
                    image_url: image_url_value,
                    description: description,
                    stock_quantity: stock_quantity,
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Товар успешно сохранён');
                    } else if (data.error) {
                        alert('Ошибка:' + data.error);
                    }
                    closeEditPanel();
                    location.reload();
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при сохранении');
                });
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>