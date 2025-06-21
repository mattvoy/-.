<?php
    session_start();
    require_once 'php/db.php';
    
    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $ads_per_page = 9;
        $offset = ($page - 1) * $ads_per_page;
    
        $sql = "SELECT id, title, description, price, town, image_url, created_at, `condition` FROM ads";
        $params = [];
        $types = "";
    
        $whereClauses = [];
    
        if (!empty($search)) {
            $whereClauses[] = "title LIKE ?";
            $search = "%" . $search . "%";
            $params[] = $search;
            $types .= "s";
        } else {
            if ($category !== 'Другое') {
                $whereClauses[] = "category = ?";
                $params[] = $category;
                $types .= "s";
            }
        }
    
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
    
        $sql .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $ads_per_page;
        $types .= "ii";
    
        if ($stmt = $conn->prepare($sql)) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
    
            $stmt->execute();
            $result = $stmt->get_result();
            $ads = [];
    
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_urls = explode(",", $row['image_url']);
                    $first_image = !empty($image_urls[0]) ? trim($image_urls[0]) : '';
                    if (strpos($first_image, "../") === 0) {
                        $first_image = substr($first_image, 3);
                    }
                    echo '<div class="dec-item" data-condition="' . htmlspecialchars($row['condition']) . '"
                             data-id="' . htmlspecialchars($row['id']) . '"
                             data-price="' . htmlspecialchars($row['price']) . '"
                             data-date="' . strtotime($row['created_at']) . '">';
                    echo '<a href="ad.php?id=' . htmlspecialchars($row['id']) . '">';
                    echo '<img src="php/' . htmlspecialchars($first_image) . '"
                             alt="' . htmlspecialchars($row['title']) . '"/>';
                    echo '<div class="dec-info">';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p class="price">' . htmlspecialchars($row['price']) . ' руб.</p>';
                    echo '<p class="location">' . htmlspecialchars($row['town']) . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>В этой категории нет объявлений.</p>';
            }
        } else {
            echo '<p>Ошибка при подготовке запроса для объявлений: ' . $conn->error . '</p>';
        }
        exit();
    }
    
    if (!isset($_GET['category'])) {
        header("Location: index.php");
        exit;
    }
    
    $category = $_GET['category'];
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $ads_per_page = 9;
    
    $page = 1;
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $page = (int)$_GET['page'];
    }
    
    $offset = ($page - 1) * $ads_per_page;
    
    $sql = "SELECT id, title, description, price, town, image_url, created_at, `condition` FROM ads";
    $params = [];
    $types = "";
    
    $whereClauses = [];
    
    if (!empty($search)) {
        $whereClauses[] = "title LIKE ?";
        $search = "%" . $search . "%";
        $params[] = $search;
        $types .= "s";
    } else {
        if ($category !== 'Другое') {
            $whereClauses[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }
    }
    
    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $ads_per_page;
    $types .= "ii";
    
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $ads = [];
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $image_urls = explode(",", $row['image_url']);
                $first_image = !empty($image_urls[0]) ? trim($image_urls[0]) : '';
                if (strpos($first_image, "../") === 0) {
                    $first_image = substr($first_image, 3);
                }
                $ads[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'town' => $row['town'],
                    'first_image' => $first_image,
                    'created_at' => $row['created_at'],
                    'condition' => $row['condition']
                ];
            }
        }
    } else {
        die("Ошибка при подготовке запроса для объявлений: " . $conn->error);
    }
    
    $sql_count = "SELECT COUNT(*) FROM ads";
    $params_count = [];
    $types_count = "";
    
    if ($category !== 'Другое' && empty($search)) {
        $sql_count .= " WHERE category = ?";
        $params_count[] = $category;
        $types_count .= "s";
    }
    
    if ($stmt_count = $conn->prepare($sql_count)) {
        if (!empty($params_count)) {
            $stmt_count->bind_param($types_count, ...$params_count);
        }
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $total_ads = $result_count->fetch_row()[0];
        $total_pages = ceil($total_ads / $ads_per_page);
    
    } else {
        die("Ошибка при подготовке запроса для подсчета объявлений: " . $conn->error);
    }
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Категория - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="icon" href="favicon.png" type="image/x-icon" />
    <style>
        .sort-options {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <a href="index.php" class="logo">Руки.ру</a>
        <button class="search-toggle">
                <img src="img/search.png" alt="">
            </button>
        <div class="search-container">
          <form class="search-form" action="category.php" method="GET">
            <input type="hidden" name="category" value="Другие">
            <input type="text" name="search" placeholder="Поиск">
            <button type="submit">Найти</button>
          </form>
        </div>
        <div class="auth-buttons">
            <a id="login-link" href="login.php">Войти</a>
            <a
                id="profile-link"
                href="profile.php"
                class="button-primary"
                id="profile-link"
            >Личный кабинет</a
            >
            <a href="post-ad.php" class="button-primary">Разместить объявление</a>
        </div>
    </div>
</header>



<main>
    <div class="container">
        <h1>Название категории</h1>

        <div class="category-page">
            <button class="filters-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="filters-container">
                <aside class="filters">
                <h2>Фильтры</h2>
                <form class="filters__form">
                    <div class="filter-group">
                        <h3>Цена</h3>
                        <input type="number" placeholder="От" id="price-from"/>
                        <input type="number" placeholder="До" id="price-to"/>
                    </div>
                    <div class="filter-group">
                        <h3>Состояние</h3>
                        <label><input type="checkbox" value="new" class="filter-checkbox" data-filter="condition"/>
                            Новый</label> <br>
                        <label><input type="checkbox" value="used" class="filter-checkbox" data-filter="condition"/>
                            Б/У</label> <br>
                        <label><input type="checkbox" value="excellent" class="filter-checkbox"
                                      data-filter="condition"/> Отличное</label> <br>
                        <label><input type="checkbox" value="middle" class="filter-checkbox"
                                      data-filter="condition"/> Среднее</label> <br>
                        <label><input type="checkbox" value="bad" class="filter-checkbox"
                                      data-filter="condition"/> Плохое</label>
                        </div>
                        <div class="filter-group">
                            <h3>Город</h3>
                            <input type="text" placeholder="Введите город" id="city-input">
                        </div>
                        <a href="#" type="button" class="apply-filters" id="apply-filters">Применить фильтры</a>
                        <a href="#" type="button" class="reset-filters" id="reset-filters">Сбросить фильтры</a>
                    </form>
                </aside>
            </div>

            <script src="js/filter.js"></script>

            <section class="dec-list">
                <h2>Объявления</h2>

                <div class="sort-options">
                    <label for="sort-by">Сортировать по:</label>
                    <select class="sort-by" id="sort-by">
                        <option value="default">По умолчанию</option>
                        <option value="price-asc">Цене (возрастание)</option>
                        <option value="price-desc">Цене (убывание)</option>
                        <option value="date-newest">Дате (сначала новые)</option>
                        <option value="date-oldest">Дате (сначала старые)</option>
                    </select>
                </div>

                <div class="dec-grid" id="dec-grid-container">
                    <?php if (count($ads) > 0): ?>
                        <?php foreach ($ads as $ad): ?>
                            <div class="dec-item" data-condition="<?php echo htmlspecialchars($ad['condition']); ?>"
                                 data-id="<?php echo htmlspecialchars($ad['id']); ?>"
                                 data-price="<?php echo htmlspecialchars($ad['price']); ?>"
                                 data-date="<?php echo strtotime($ad['created_at']); ?>">
                                <a href="ad.php?id=<?php echo htmlspecialchars($ad['id']); ?>">
                                    <img src="php/<?php echo htmlspecialchars($ad['first_image']); ?>"
                                         alt="<?php echo htmlspecialchars($ad['title']); ?>"/>
                                    <div class="dec-info">
                                        <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                                        <p class="price"><?php echo htmlspecialchars($ad['price']); ?> руб.</p>
                                        <p class="location"><?php echo htmlspecialchars($ad['town']); ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>В этой категории нет объявлений.</p>
                    <?php endif; ?>
                </div>
                <div class="pagination">
                    <?php
                        if ($total_pages > 1):
                            if ($page > 1): ?>
                                <a href="#" data-page="<?php echo $page - 1; ?>">Предыдущая</a>
                            <?php endif;

                            for ($i = 1; $i <= $total_pages; $i++):
                                if ($i == $page): ?>
                                    <a href="#" data-page="<?php echo $i; ?>" class="active"><?php echo $i; ?></a>
                                <?php else: ?>
                                    <a href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif;
                            endfor;

                            if ($page < $total_pages): ?>
                                <a href="#" data-page="<?php echo $page + 1; ?>">Следующая</a>
                            <?php endif;
                        endif;
                    ?>
                </div>
            </section>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; 2025 Руки.ру</p>
    </div>
</footer>

<script>
      document.addEventListener("DOMContentLoaded", function () {
        const searchToggle = document.querySelector(".search-toggle");
        const searchContainer = document.querySelector(".search-container");

        searchToggle.addEventListener("click", function () {
          searchContainer.classList.toggle("active");
        });
      });
    </script>

<script>
    function updatePaginationVisibility() {
    const adItems = document.querySelectorAll('.dec-item');
    const pagination = document.querySelector('.pagination');
    let visibleAdCount = 0;
    for (let i = 0; i < adItems.length; i++) {
        if (adItems[i].offsetParent !== null) {
            visibleAdCount++;
        }
    }

    const totalPages = document.querySelectorAll('.pagination a').length - 1;
    const currentPage = document.querySelector('.pagination a.active').dataset.page;

    console.log('visibleAdCount:', visibleAdCount);
    console.log('totalPages:', totalPages);
    console.log('currentPage:', currentPage);

    if (visibleAdCount <= 7 && currentPage < totalPages) {
        pagination.classList.add('hidden');
    } else {
        pagination.classList.remove('hidden');
    }
    }

    function updateActivePaginationButton(currentPage) {
        const paginationLinks = document.querySelectorAll('.pagination a');
        console.log("updateActivePaginationButton called with page:", currentPage);

        console.log("paginationLinks:", paginationLinks);

        paginationLinks.forEach(link => {
            link.className = link.className.replace('active', '');
            console.log("link:", link);
        });

        const activeLink = document.querySelector(`.pagination a[data-page="${currentPage}"]`);
        console.log("activeLink:", activeLink);
        if (activeLink) {
            activeLink.classList.add('active');
        }
        }

    document.addEventListener('DOMContentLoaded', function() {
    const paginationLinks = document.querySelectorAll('.pagination a');

    paginationLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const page = this.dataset.page;
            loadAds(page);
        });
    });

    function loadAds(page) {
        const category = encodeURIComponent(getCategory());
        const search = encodeURIComponent(getSearch());
        const newURL = `category.php?category=${encodeURIComponent(getCategory())}&search=${encodeURIComponent(getSearch())}&page=${page}`;

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `category.php?ajax=1&category=${encodeURIComponent(getCategory())}&search=${encodeURIComponent(getSearch())}&page=${page}`, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                document.getElementById('dec-grid-container').innerHTML = xhr.responseText;
                updateActivePaginationButton(page);
                updatePaginationVisibility();
                history.pushState({page: page}, '', newURL);
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Request failed');
        };
        xhr.send();
    }

    function getCategory() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('category') || 'Другие';
    }

    function getSearch() {
        return document.querySelector('input[name="search"]').value;
    }

    const currentPage = urlParams.get('page') || 1;
    updateActivePaginationButton(currentPage);
});
</script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const categoryName = urlParams.get("category") || "Объявления";
    document.querySelector("h1").textContent = categoryName;
</script>

<script>
    const loginLink = document.getElementById("login-link");
    const profileLink = document.getElementById("profile-link");

    async function checkSession() {
        try {
            const response = await fetch("php/check_session.php");
            const data = await response.json();

            if (data.loggedIn) {
                loginLink.style.display = "none";
                profileLink.style.display = "block";
            } else {
                loginLink.style.display = "block";
                profileLink.style.display = "none";
            }
        } catch (error) {
            console.error("Error checking session:", error);
        }
    }

    document.addEventListener("DOMContentLoaded", checkSession);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sortBySelect = document.getElementById('sort-by');
        const adGrid = document.querySelector('.dec-grid');
        const adItems = Array.from(adGrid.querySelectorAll('.dec-item'));

        const originalOrder = adItems.map(item => item.cloneNode(true));

        sortBySelect.addEventListener('change', function () {
            const sortBy = sortBySelect.value;

            let sortedAds = [...adItems];

            switch (sortBy) {
                case 'price-asc':
                    sortedAds.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
                    break;
                case 'price-desc':
                    sortedAds.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
                    break;
                case 'date-newest':
                    sortedAds.sort((a, b) => parseFloat(b.dataset.date) - parseFloat(a.dataset.date));
                    break;
                case 'date-oldest':
                    sortedAds.sort((a, b) => parseFloat(a.dataset.date) - parseFloat(b.dataset.date));
                    break;
                default:
                    sortedAds = originalOrder.map(item => item.cloneNode(true));
                    break;
            }

            adGrid.innerHTML = '';

            sortedAds.forEach(ad => adGrid.appendChild(ad));
        });
    });
</script>
</body>
</html>