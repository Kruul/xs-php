<?

/**
 * Класс для вывода результата работы контроллеров и моделей
 * в файлы видов, используя шаблонизатор Twig
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class View_Twig extends View {
  /**
   * Расширение файлов шаблона
   *
   * @var  string
   */
  protected static $template_extension = '.tpl';

  /**
   * Рендеринг вида
   *
   * @param   string  $file_path  Путь к файлу вида
   * @param   array   $data       Массив с переменными вида
   * @return  string              Скомпилированная строка вида
   */
  protected static function render($file_path, $data) {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/../views/');
    $twig = new Twig_Environment($loader);

    return $twig->render($file_path, $data);
	}
}
