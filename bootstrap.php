<?

/**
 * Установка внутренней кодировки скрипта
 */
mb_internal_encoding('UTF-8');

/**
 * Установка временной зоны
 */
date_default_timezone_set('Europe/Moscow');

/**
 * Переменная окружения
 * dev - разработка
 * prod - продакшен
 */
define('ENV', 'dev');

/**
 * Базовый путь до файлов фреймворка от базовой директории веб хоста (без слеша в конце)
 * Если файлы фреймворка находятся в корне директории веб хоста, оставить пустым
 */
define('BASE_PATH', '');

/**
 * Подключение установленных через composer зависимостей
 */
$autoload_path = __DIR__ . '/vendor/autoload.php';
if(file_exists($autoload_path)) include_once($autoload_path);

/**
 * Установка обработчиков ошибок
 */
Controller::set_error_404_handler([ 'Controller_Base', 'handler_404' ]);
Controller::set_error_500_handler([ 'Controller_Base', 'handler_500' ]);

/**
 * Настройка плагинов
 */
// Установка настроек подключения к базе данных
DB::config(Config::get('db', true));
// Инициализация плагина минификации выходного HTML
Html_Minify::init();
