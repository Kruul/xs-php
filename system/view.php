<?

/**
 * Класс для вывода результата работы контроллеров и моделей в файлы видов
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class View {
  /**
   * Расширение файлов шаблона
   *
   * @var  string
   */
  protected static $template_extension = '.php';

  /**
   * Хранение глобальных переменных вида
   * Глобальные переменых вида доступны в каждом созданном виде
   * в рамках отдельного запроса
   *
   * @var  array
   */
  protected static $global_data = [];

  /**
   * Устанавливает глобальную переменную вида
   *
   * @param  string  $key    Название глобальной переменной
   * @param  mixed   $value  Значение глобавльной переменной
   */
  public static function set_global($key, $value) {
		self::$global_data[$key] = $value;
	}

  /**
   * Рендеринг вида
   *
   * @param   string  $file_path  Путь к файлу вида
   * @param   array   $data       Массив с переменными вида
   * @return  string              Скомпилированная строка вида
   */
  protected static function render($file_path, $data) {
    // Защита от перезаписи переменной $file_path переменными вида
    $ljCc5sIoKjm4xNm3DIwI = $file_path;

    extract($data);

		ob_start();
		include($ljCc5sIoKjm4xNm3DIwI);

		return ob_get_clean();
	}

  /**
   * Создание нового экземпляра фабричным методом
   *
   * @param   string  $file  Название файла вида с учетом его директории
   * @param   array   $data  Локальные переменные вида
   * @return  object         Объект экземпляра класса
   *
   * @example  $view = View::factory('page')
   *           Создаст экземпляр класса вида из файла /views/page.php
   * @example  $view = View::factory('page', [ 'title' => 'Title' ])
   *           Создаст экземпляр класса вида с локальной переменной title
   * @example  $view = View::factory('profile/page')
   *           Создаст экземпляр класса вида из файла /views/profile/page.php
   */
  public static function factory($file, $data = []) {
    return new self($file, $data);
  }

  /**
   * Хранение локальных переменных вида
   * Устанавливаются для конкретного экземпляра вида
   *
   * @var  array
   */
  protected $data = [];

  /**
   * Путь к файлу вида
   *
   * @var  string
   */
  protected $file_path = '';

  /**
   * Конструктор класса вида
   *
   * @param  string  $file  Название файла вида с учетом его директории
   * @param  string  $data  Локальные переменные вида
   *
   * @example  $view = new View('page')
   *           Создаст экземпляр класса вида из файла /views/page.php
   * @example  $view = new View('page', [ 'title' => 'Title' ])
   *           Создаст экземпляр класса вида с локальной переменной title
   * @example  $view = new View('profile/page')
   *           Создаст экземпляр класса вида из файла /views/profile/page.php
   */
  public function __construct($file, $data = []) {
    $file_path = __DIR__ . '/../views/' . $file . self::$template_extension;
    if(!file_exists($file_path)) {
      throw new Exception('Файл вида "' . $file . '" не найден');
    }

    $this->data = $data;
    $this->file_path = $file_path;
	}

  /**
   * Магический метод установки локальной переменной вида
   *
   * @example  $view->key = 'value'
   *           Добавит локальную переменную вида
   *           с названием $key и значением 'value'
   */
	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

  /**
   * Устанавливает локальную переменную вида
   *
   * @param  string  $key    Название переменной
   * @param  mixed   $value  Значение переменной
   *
   * @example  $view->set('key', 'value')
   *           Добавит локальную переменную вида
   *           с названием $key и значением 'value'
   */
  public function set($key, $value) {
    $this->data[$key] = $value;
  }

  /**
   * Магический метод конвертации класса в строку
   *
   * @example  $this->response = $view
   *           Нет необходимости вызывать рендеринг вручную,
   *           он запустится автоматически, когда это будет нужно.
   */
  public function __toString() {
    try {
      $data = array_merge(self::$global_data, $this->data);
			return self::render($this->file_path, $data);
		}
		catch (Exception $e) {
      // Обход ограничение на исключения в методе __toString
      trigger_error(
        $e->getMessage() . "\n" . $e->getTraceAsString(),
        E_USER_ERROR
      );
			return '';
		}
	}
}
