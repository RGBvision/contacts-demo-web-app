<?php

if ($_POST['action'] && $_POST['action'] === 'install') {

    $config = array(
        'dbengine' => $_POST['dbengine'],
        'dbhost' => $_POST['dbhost'],
        'dbuser' => $_POST['dbuser'],
        'dbpass' => $_POST['dbpass'],
        'dbname' => $_POST['dbname'],
        'dbport' => (int)$_POST['dbport']
    );


    require_once __DIR__ . '/../system/Loader.php';

    Loader::init();

    // Загрузка EasyDB
    Loader::addDirectory(__DIR__ . '/../system/lib/db/');

    // Загрузка DB
    Loader::addClass('DB', __DIR__ . '/../system/core/DB.php');

    // Загрузка вспомогательных классов
    Loader::addDirectory(__DIR__ . '/../system/helpers/');

    // Подключение к БД
    DB::init($config);

    // Если подключение прошло успешно, то можно создать таблицу и записать конфигурацию. В противном случае пользователь получит 503 ошибку.

    $query = '';

    switch ($config['dbengine']) {
        case "mysql":
            $query = File::getContent(__DIR__ . '/mysql.sql');
            break;
        case "postgresql":
            $query = File::getContent(__DIR__ . '/pgsql.sql');
            break;
    }

    if ($query) {

        DB::query('DROP TABLE IF EXISTS `contacts`');
        DB::query($query);

        // Создание файла конфигурации подключения к БД
        File::putContent(__DIR__ . '/../configs/db.config.php', '<?php' . PHP_EOL . PHP_EOL . '$config = ' . var_export($config, true) . ';' . PHP_EOL);

        // Запуск приложения
        header('Location: /');
    }
}

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Инсталляция</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">
    <link href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/plugins/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<main class="container py-5">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Введите данные подключения к базе данных</h5>
            <h6 class="card-subtitle mb-2 text-muted small">Все поля обязательны для заполнения</h6>
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="install">
                <div class="col-md-4">
                    <label for="dbEngine" class="form-label">Драйвер БД</label>
                    <select class="form-select" id="dbEngine" name="dbengine" required>
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="postgresql">PostgreSQL</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="dbHost" class="form-label">Хост</label>
                    <input type="text" class="form-control" id="dbHost" name="dbhost" value="localhost" required>
                </div>
                <div class="col-md-4">
                    <label for="dbPort" class="form-label">Порт</label>
                    <input type="text" class="form-control" id="dbPort" name="dbport" value="3306" required>
                </div>
                <div class="col-md-4">
                    <label for="dbName" class="form-label">Имя БД</label>
                    <input type="text" class="form-control" id="dbName" name="dbname" value="" required>
                </div>
                <div class="col-md-4">
                    <label for="dbUser" class="form-label">Пользователь</label>
                    <input type="text" class="form-control" id="dbUser" name="dbuser" value="" required>
                </div>
                <div class="col-md-4">
                    <label for="dbPass" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="dbPass" name="dbpass" value="" required>
                </div>
                <div class="col-12 text-center text-md-end">
                    <button class="btn btn-primary px-5" type="submit">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script src="/assets/plugins/jquery/jquery-3.6.0.min.js"></script>
<script src="/assets/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(() => {
        $('#dbEngine').change(function () {
            const port = $('#dbPort');
            switch ($(this).val()) {
                case "mysql":
                    port.val('3306');
                    break;
                case "postgresql":
                    port.val('5432');
                    break;
            }
        });
    });
</script>
</body>
</html>