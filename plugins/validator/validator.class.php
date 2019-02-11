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
   * @return  object         Объект экземпляра класса
   *
   * @example  $v = Validator::factory()
   *           Создаст экземпляр класса валидатора
   */
  public static function factory() {
    return new self();
  }

  /**
   * Список правил валидации полей
   *
   * @var  array
   */
  protected $fields = [];

  /**
   * Отфильтрованный чистильщиками массив данных, устанавливается после валидации
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
   * Запуск проверки фильтра
   * Для вызова фильтра из функции фильтра
   *
   * @param   string  $name    Название фильтра
   * @param   mixed   $value   Значение для проверки
   * @param   mixed   $params  Параметры проверки
   * @return  bool             Результат проверки
   *
   * @example  $result = $this->filter('filter_name', 'value', $params)
   *           Вызов другого фильтра из функции фильтра
   *           При вызове функции фильтра к ней биндится экземпляр
   *           валидатора как $this
   */
  public function filter($name, $value, $params = null) {
    $binded_filter = Closure::bind(self::$filters[$name], $this);
    return $binded_filter($value, $params);
  }

  /**
   * Запуск чистки параметра
   * Для вызова чистильщика из функции чистильщика
   *
   * @param   string  $name    Название чистильшика
   * @param   mixed   $value   Значение для очистки
   * @param   mixed   $params  Параметры очистки
   * @return  bool             Результат очистки
   *
   * @example  $result = $this->sanitize('sanitize_name', 'value', $params)
   *           Вызов другого чистильщика из функции чистильщика
   *           При вызове функции чистки к ней биндится экземпляр
   *           валидатора как $this
   */
  public function sanitize($name, $value, $params = null) {
    $binded_sanitize = Closure::bind(self::$sanitizers[$name], $this);
    return $binded_sanitize($value, $params);
  }

  /**
   * Добавление поля в проверку
   *
   * @param  string  $name  Ключ поля в данных для проверки
   *
   * @example  $v->field('field_name')
   */
  public function field($name) {
    $this->new_rules_group = true;

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
   *
   * @example  $v->field('field_name')->change('intval')
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
   *
   * @example  $v->field('field_name')->check('not_empty')
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
   *
   * @example  $v->field('field_name')->check('not_empty')
   *             ->message('field_name не может быть пустым')
   */
  public function message($message) {
    $field_index = count($this->fields) - 1;
    $rule_index = count($this->fields[$field_index]['rules']) - 1;
    $this->fields[count($this->fields) - 1]['rules'][$rule_index]['message'] = $message;

    return $this;
  }

  /**
   * Индикатор прекращения проверки остальных правил поля при провале проверки
   *
   * @return  object  Экземпляр класса валидатора
   *
   * @example  $v->field('field_name')
   *             ->check('not_empty') // Если эта проверка не пройдена
   *             ->stopOnFail()
   *             ->check('accepted') // эта проверка не будет запущена
   */
  public function stopOnFail() {
    $field_index = count($this->fields) - 1;
    $rule_index = count($this->fields[$field_index]['rules']) - 1;
    $this->fields[count($this->fields) - 1]['rules'][$rule_index]['stop_on_fail'] = true;

    return $this;
  }

  /**
   * Проведение проверки по всем добавленным правилам
   * После проверки в экземпляр класса устанавливаются поля:
   *   $data - отфильтрованный чистильщиками массив данных
   *   $errors - массив с сообщениями об ошибках, пустой если ошибок не было
   *
   * @param   array  $data  Массив с данными для валидации
   * @return  bool          Результат проверки
   *
   * @example  $v->field('field_name')
   *             ->check('not_empty')
   *           $result = $v->validate($_POST);
   */
  public function validate($data) {
    $is_correct = true;
    $errors = [];

    foreach($this->fields as $field) {
      $field_name = $field['name'];
      if(!isset($data[$field_name])) $data[$field_name] = '';

      // Чистка значений
      foreach($field['rules'] as $rule) {
        if($rule['type'] == self::RULE_TYPE_CHANGE) {
          $data[$field_name] = $this->sanitize(
            $rule['name'],
            $data[$field_name],
            $rule['params']
          );
        }

        // Проверка значений
        if($rule['type'] == self::RULE_TYPE_CHECK) {
          $value = $data[$field_name];

          foreach($rule['filters'] as $filter) {
            $result = $this->filter($filter['name'], $value, $filter['params']);
            if(!$result) {
              if($rule['message'] != '') $errors[] = $rule['message'];

              $is_correct = false;

              if($rule['stop_on_fail']) continue 3;
              else break;
            }
          }
        }
      }
    }

    $this->data = $data;
    $this->errors = $errors;

    return $is_correct;
  }
}
