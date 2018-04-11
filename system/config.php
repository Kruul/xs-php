<?

/**
 * Класс загрузки файлов настроек
 * Файлы настроек хранятся в директории /app/config/
 *
 * @package  XS-PHP
 * @version  1.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Config {
  /**
   * Переменная для хранения уже загруженых настроек
   * Загруженные настройки кэшируются для отдельного запроса
   * @var array
   */
  protected static $cache = array();

  /**
   * Загрузка файла настроек по названию
   *
   * @param   string  $config_name  Название файла настроек
   * @return  mixed                 Данные файла настроек или NULL, в случае отсутствия файла
   */
  protected static function load($config_name) {
    if(isset(self::$cache[$config_name])) return Config::$cache[$config_name];

    $config_filepath = __DIR__ . '/../app/config/' . $config_name . '.php';
    if(!file_exists($config_filepath)) return null;
    self::$cache[$config_name] = include($config_filepath);

    return self::$cache[$config_name];
  }

  /**
   * Получение настроек по названию
   *
   * Название файла настроек 'db.prod.php' состоит из:
   * -- самого названия 'db'
   * -- переменной окружения 'prod' (необязательная часть)
   * -- расширения файла '.php'
   * Название необходимо передавать без указания расширения.
   * Название необходимо передавать без указания переменной окружения.
   * Файл настройки с переменной окружения загрузится автоматически после
   * проверки, что файла настроек без переменной окружения не существует.
   * Если нет необходимости проверять существование файла настроек без переменной
   * окружения, то нужно указать 2 параметр как true.
   *
   * @param   string   $config_name  Название файла настроек
   * @param   boolean  $conside_env  Загружать сразу же файл настроек с переменной окружения
   * @return  mixed                  Данные файла настроек или NULL, в случае отсутствия файла
   *
   * @example  Config::get('main')  Загрузит файл main.php или main.prod.php
   *                                в случае отсутсвия main.php и указания в ENV 'prod'
   * @example  Config::get('main', true)  Загрузит файл main.dev.php не проверяя
   *                                      существования файла main.php
   */
  public static function get($config_name, $conside_env = false) {
    if($conside_env) return self::load($config_name . '.' . ENV);

    $config = self::load($config_name);
    if($config) return $config;

    return self::load($config_name . '.' . ENV);
  }
}
