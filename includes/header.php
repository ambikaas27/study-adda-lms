<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $pageTitle = isset($pageTitle) ? $pageTitle . " | Study Adda" : "Study Adda - Complete Study Solution";
    ?>
    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
        crossorigin="anonymous">

    <link rel="stylesheet" href="/MyProject/css/style.css">

    <!-- Favicon — stops tab flickering -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">


</head>

<body>

    <nav class="navbar navbar-expand-lg main-navbar sticky-top">
        <div class="container">

            <!-- LOGO -->
            <a class="navbar-brand" href="/MyProject/index.php">
                <img src="/MyProject/images/logo.png" alt="Study Adda Logo" height="50">
            </a>

            <!-- HAMBURGER BUTTON -->
            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMain"
                aria-controls="navbarMain"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- NAV LINKS -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);

                    $navLinks = [
                        ["href" => "/MyProject/index.php",   "label" => "Home"],
                        ["href" => "/MyProject/about.php",   "label" => "About"],
                        ["href" => "/MyProject/courses.php", "label" => "Courses"],
                        ["href" => "/MyProject/contact.php", "label" => "Contact Us"],
                    ];

                    foreach ($navLinks as $link) {
                        $isActive = ($currentPage === basename($link["href"])) ? "active" : "";
                        echo '<li class="nav-item">
                                <a class="nav-link ' . $isActive . '" href="' . $link["href"] . '">' . $link["label"] . '</a>
                              </li>';
                    }
                    ?>

                </ul>

                <!-- RIGHT SIDE: Search + Auth Buttons -->
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <!-- Search -->
                    <form class="d-flex" role="search" action="/MyProject/search.php" method="GET">
                        <input class="form-control me-2 search-input"
                            type="search"
                            name="q"
                            placeholder="Search courses..."
                            aria-label="Search">
                        <button class="btn btn-search" type="submit">Search</button>
                    </form>

                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $dashboardLink = match ($_SESSION['role']) {
                            'admin'      => '/MyProject/admin/dashboard.php',
                            'instructor' => '/MyProject/instructor/dashboard.php',
                            default      => '/MyProject/student/dashboard.php',
                        };
                        echo '<a href="' . $dashboardLink . '" class="btn btn-dashboard">Dashboard</a>';
                        echo '<a href="/MyProject/logout.php" class="btn btn-logout">Logout</a>';
                    } else {
                        echo '<a href="/MyProject/login.php" class="btn btn-login">Login</a>';
                        echo '<a href="/MyProject/register.php" class="btn btn-register">Register</a>';
                    }
                    ?>

                </div>

            </div>
        </div>
    </nav>