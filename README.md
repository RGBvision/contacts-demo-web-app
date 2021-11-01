# Инсталляция

- Распаковать архив на сервер.
- В случае распаковки НЕ в корневую папку домена, отредактировать `153` строку корневого файла `.htaccess`: заменить `RewriteBase /` на `RewriteBase /ИМЯ ПАПКИ/`.
- Создать базу данных, с которой будет работать система.
- Открыть в браузере сайт с установленной системой. При первом запуске инсталлятор запустится автоматически.
- В открывшемся диалоге инсталлятора ввести данные подключения к базе данных. Инсталлятор создаст таблицу и откроет страницу приложения.

# Структура проекта

    .
    └── /app                        # Классы приложения
        ├── /i18n                   # Интернационализация
        ├── AppController.php       # Класс контроллера
        ├── AppModel.php            # Класс модели
        └── AppView.html            # Представление
    └── /assets                     # Front-end библиотеки, JS и CSS
        ├── /css
        ├── /js
        └── /plugins                # Подключаемые сторонние Front-end библиотеки
    ├── /configs                    # Настройки подключения к БД и окружения
    ├── /install                    # Инсталлятор
    └── /system                     # Системные файлы
        ├── /core                   # Ядро системы
        ├── /helpers                # Вспомогательные классы
        ├── /i18n                   # Интернационализация
        ├── /lib                    # Библиотеки сторонних разработчиков
        ├── config.php              # Настройки системы по умолчанию 
        ├── init.php                # Файл инициализации системы
        └── Loader.php              # Загрузчик системы
    ├── index.php                   # Точка запуска приложения
    └── README.md
