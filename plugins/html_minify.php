<?

/**
 * Минификация выходного HTML кода
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Html_Minify {
  public static function init() {
    // Добавляет функцию постобработки запроса
    // Функция сжимает html код ответа за счет удаления пробелов между тегами
    Controller::add_after_handler(function($controller) {
      // Ничего не делать, если в response массив
      if(gettype($controller->response) == 'array') return;

      // Конвертация ответа в строку, так как ответ хранится как экземпляр класса вида
      $response = (string) $controller->response;

      // Регулярное выражение для удаления пробелов
      $re =
      '%                # Collapse whitespace everywhere but in blacklisted elements.
        (?>             # Match all whitespans other than single space.
          [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
        | \s{2,}        # or two or more consecutive-any-whitespace.
        )               # Note: The remaining regex consumes no text at all...
        (?=             # Ensure we are not in a blacklist tag.
          [^<]*+        # Either zero or more non-"<" {normal*}
          (?:           # Begin {(special normal*)*} construct
            <           # or a < starting a non-blacklist tag.
            (?!/?(?:textarea|pre|script)\b)
            [^<]*+      # more non-"<" {normal*}
          )*+           # Finish "unrolling-the-loop"
          (?:           # Begin alternation group.
            <           # Either a blacklist start tag.
            (?>textarea|pre|script)\b
          | \z          # or end of file.
          )             # End alternation group.
        )               # If we made it here, we are not in a blacklist tag.
      %Six';
      $response_minify = preg_replace($re, ' ', $response);
      if($response_minify === null) {
        trigger_error(
          "Ошибка в плагине Html_Minify\nСлишком большой объем HTML кода\nПодробнее: https://stackoverflow.com/questions/5312349/minifying-final-html-output-using-regular-expressions-with-codeigniter",
          E_USER_ERROR
        );
      }
      $controller->response = $response_minify;
    });
  }
}
