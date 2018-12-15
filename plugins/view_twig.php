<?

/**
 * Класс для вывода результата работы контроллеров и моделей в файлы видов, используя шаблонизатор Twig
 * Файлы видов хранятся в директории /views/
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class View_Twig {
  /**
   * Хранение глобальных переменных вида
   * Глобальные переменых вида доступны в каждом созданном виде в рамках отдельного запроса
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
   * Рендерит вид, используя глобальные и локальные переменные вида
   *
   * @param   string  $file_path  Путь к файлу вида
   * @param   array   $data       Массив с локальными переменными вида
   * @return  string              Скомпилированная строка вида
   */
  protected static function render($file_path, $data) {
    // Локальные переменные перекрывают глобальные
    $data = array_merge(self::$global_data, $data);

    $loader = new Twig_Loader_Filesystem(__DIR__ . '/../views/');
    $twig = new Twig_Environment($loader);

    return $twig->render($file_path, $data);
	}

  /**
   * Создание нового экземпляра фабричным методом
   *
   * Смотреть описание конструктора класса
   */
  public static function factory($file) {
    return new self($file);
  }

  /**
   * Хранение локальных переменных вида
   * Устанавливаются для конкретного экземпляра вида
   * @var  array
   */
  protected $data = [];

  /**
   * Путь к файлу вида
   * @var  string
   */
  protected $file_path;

  /**
   * Конструктор класса вида
   *
   * @param  string  $key  Название файла вида с учетом его директории
   *
   * @example  $view = new View('page')  Создаст экземпляр класса вида
   *                                     из файла /views/page.php
   * @example  $view = new View('profile/page')  Создаст экземпляр класса вида
   *                                             из файла /views/profile/page.php
   */
  public function __construct($file) {
    $file_path = __DIR__ . '/../views/' . $file . '.tpl';
    if(!file_exists($file_path)) {
      throw new Exception('Файл вида "' . $file . '" не найден');
    }

    $this->file_path = $file . '.tpl';
	}

  /**
   * Магический метод установки локальной переменной вида
   *
   * @example  $view->key = 'value'  Добавит локальную переменную вида
   *                                 с названием $key и значением 'value'
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
   * @example  $view->set('key', 'value')  Добавит локальную переменную вида
   *                                        с названием $key и значением 'value'
   */
  public function set($key, $value) {
    $this->data[$key] = $value;
  }

  /**
   * Магический метод конвертации класса в строку
   *
   * @example  $this->response = $view  Нет необходимости вызывать рендеринг вручную,
   *                                    он запустится автоматически, когда это будет нужно.
   */
  public function __toString() {
    try {
			return self::render($this->file_path, $this->data);
		}
		catch (Exception $e) {
      // Обход ограничение на исключения в методе __toString
      trigger_error($e->getMessage() . "\n" . $e->getTraceAsString(), E_USER_ERROR);
			return '';
		}
	}
}
