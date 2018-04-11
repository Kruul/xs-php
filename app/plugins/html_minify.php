<?

/**
 * Минификация выходного HTML кода
 *
 * @package  XS-PHP
 * @version  1.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Html_Minify {
  public static function init() {
    // Добавляет функцию постобработки запроса
    // Функция сжимает html код ответа засчет удаления пробелов между тегами
    Controller::add_after_handler(function($controller) {
      // Конвертация ответа в строку, так как ответ хранится как экземпляр класса вида
      $response = (string) $controller->response;

      $search = ['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/<!--(.|\s)*?-->/'];
      $replace = ['>', '<', '\\1', ''];

      $controller->response = preg_replace($search, $replace, $response);
    });
  }
}
