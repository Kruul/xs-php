<?

/**
 * Класс валидации входных параметров
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class Validator {
  /**
   * Константы типов действий
   */
  const RULE_TYPE_CHANGE = 0;
  const RULE_TYPE_CHECK = 1;


  /**
   * Список фильтров
   *
   * @var  array
   */
  protected static $filters = [];

  /**
   * Добавление фильтра
   *
   * @param  string    $name     Название фильтра
   * @param  callable  $handler  Обработчик фильтра
   */
  public static function add_filter($name, $handler) {
    self::$filters[$name] = $handler;
  }

  /**
   * Список чистильщиков
   *
   * @var  array
   */
  protected static $sanitizers = [];

  /**
   * Добавление чистильщика
   *
   * @param  string    $name     Название чистильщика
   * @param  callable  $handler  Обработчик чистильщика
   */
  public static function add_sanitize($name, $handler) {
    self::$sanitizers[$name] = $handler;
  }

  /**
   * Создание нового экземпляра фабричным методом
   *
   * @param   array   $data  Данные для валидации
   * @return  object         Объект экземпляра класса
   *
   * @example  $v = Validator::factory($_POST)
   *           Создаст экземпляр класса валидатора
   */
  public static function factory($data) {
    return new self($data);
  }

  /**
   * Список правил валидации полей
   *
   * @var  array
   */
  protected $fields = [];

  /**
   * Данные для валидации
   *
   * @var  array
   */
  public $data = [];

  /**
   * Список сообщений об ошибках, устанавливается после валидации
   *
   * @var  array
   */
  public $errors = [];

  /**
   * Индикатор создания нового правила для группы фильтров
   * Сбрасывается при указании сообщения ошибки для группы
   *
   * @var  bool
   */
  protected $new_rules_group = true;

  /**
   * Конструктор класса валидатора
   *
   * @param   array   $data  Данные для валидации
   *
   * @example  $v = new Validator($_POST)
   *           Создаст экземпляр класса валидатора
   */
  public function __construct($data) {
    $this->data = $data;
	}

  /**
   * Запуск проверки фильтра
   *
   * @param   string  $name    Название фильтра
   * @param   mixed   $value   Значение для проверки
   * @param   mixed   $params  Параметры проверки
   * @return  bool             Результат проверки
   */
  protected function filter($name, $value, $params = null) {
    $binded_filter = Closure::bind(self::$filters[$name], $this);
    return $binded_filter($value, $params);
  }

  /**
   * Запуск чистки параметра
   *
   * @param   string  $name    Название чистильшика
   * @param   mixed   $value   Значение для очистки
   * @param   mixed   $params  Параметры очистки
   * @return  bool             Результат очистки
   */
  protected function sanitize($name, $value, $params = null) {
    $binded_sanitize = Closure::bind(self::$sanitizers[$name], $this);
    return $binded_sanitize($value, $params);
  }

  /**
   * Добавление поля в проверку
   *
   * @param  string  $name  Ключ поля в данных для проверки
   */
  public function field($name) {
    $this->fields[] = [
      'name' => $name,
      'rules' => []
    ];

    return $this;
  }

  /**
   * Добавление правила чистки к последнему добавленному полю
   *
   * @param   string  $name    Название чистильщика
   * @param   mixed   $params  Параметры чистильщика
   * @return  object           Экземпляр класса валидатора
   */
  public function change($name, $params = null) {
    $this->fields[count($this->fields) - 1]['rules'][] = [
      'type' => self::RULE_TYPE_CHANGE,
      'name' => $name,
      'params' => $params
    ];

    return $this;
  }

  /**
   * Добавление правила проверки к последнему добавленному полю
   *
   * @param   string  $name    Название фильтра
   * @param   mixed   $params  Параметры фильтра
   * @return  object           Экземпляр класса валидатора
   */
  public function check($name, $params = null) {
    $field_index = count($this->fields) - 1;
    $rule_index = count($this->fields[$field_index]['rules']) - 1;

    if($this->new_rules_group) {
      $this->new_rules_group = false;
      $rule_index++;
      $this->fields[$field_index]['rules'][] = [
        'type' => self::RULE_TYPE_CHECK,
        'filters' => [],
        'message' => '',
        'stop_on_fail' => false
      ];
    }

    $this->fields[$field_index]['rules'][$rule_index]['filters'][] = [
      'name' => $name,
      'params' => $params
    ];

    return $this;
  }

  /**
   * Установка сообщения об ошибке последнему добавленному полю
   *
   * @param   string  $message  Текст сообщения
   * @return  object            Экземпляр класса валидатора
   */
  public function message($message) {
    $this->new_rules_group = true;

    $field_index = count($this->fields) - 1;
    $rule_index = count($this->fields[$field_index]['rules']) - 1;
    $this->fields[count($this->fields) - 1]['rules'][$rule_index]['message'] = $message;

    return $this;
  }

  /**
   * Индикатор прекращения проверки остальных правил поля при провале проверки
   *
   * @return  object  Экземпляр класса валидатора
   */
  public function stopOnFail() {
    $field_index = count($this->fields) - 1;
    $rule_index = count($this->fields[$field_index]['rules']) - 1;
    $this->fields[count($this->fields) - 1]['rules'][$rule_index]['stop_on_fail'] = true;

    return $this;
  }

  /**
   * Проведение проверки по всем добавленным правилам
   *
   * @return  bool  Результат проверки
   */
  public function validate() {
    $this->errors = [];
    $is_correct = true;

    foreach($this->fields as $field) {
      $field_name = $field['name'];
      if(!isset($this->data[$field_name])) $this->data[$field_name] = '';

      foreach($field['rules'] as $rule) {
        // Чистка значений
        if($rule['type'] == self::RULE_TYPE_CHANGE) {
          $this->data[$field_name] = $this->sanitize(
            $rule['name'],
            $this->data[$field_name],
            $rule['params']
          );
        }

        // Проверка
        if($rule['type'] == self::RULE_TYPE_CHECK) {
          $value = $this->data[$field_name];

          foreach($rule['filters'] as $filter) {
            $result = $this->filter($filter['name'], $value, $filter['params']);
            if(!$result) {
              if($rule['message'] != '') $this->errors[] = $rule['message'];

              $is_correct = false;

              if($rule['stop_on_fail']) continue 3;
              else break;
            }
          }
        }
      }
    }

    return $is_correct;
  }
}
