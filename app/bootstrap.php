<?

/**
 * Установка внутренней кодировки скрипта
 */
mb_internal_encoding('UTF-8');

/**
 * Установка локали скриптов
 */
setlocale(LC_ALL, 'ru_RU.utf-8');

/**
 * Установка временной зоны
 */
date_default_timezone_set('Europe/Moscow');

/**
 * Переменная окружения
 * dev - разработка
 * prod - продакшен
 *
 * @var  string
 */
define('ENV', 'dev');

/**
 * Базовый путь до файлов фреймворка от базовой директории веб хоста (без слеша в конце)
 * Если файлы фреймворка находятся в корне директории веб хоста, оставить пустым
 */
define('BASE_PATH', '');

/**
 * Настройки вывода ошибок в зависимости от переменной окружения
 */
if(ENV == 'prod') {
  error_reporting(0);
  ini_set('display_errors', 0);
} else {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
}

/**
 * Список REST методов для создания отдельных обработчиков запросов
 * @var  array
 */
$rest_methods = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ];

/**
 * Паттерны замены для роутов
 * @var  array
 */
$routes_patterns = [

];

/**
 * Роуты приложения
 * @var array
 */
$routes = [

];

/**
 * Подключение установленных через composer зависимостей
 */
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if(file_exists($autoload_path)) include_once($autoload_path);

/**
 * Запуск глобальных плагинов
 */
Html_Minify::init();