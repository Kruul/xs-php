<?

/**
 * Класс работы с базой данных
 *
 * @package  XS-PHP
 * @version  2.0.0
 * @author   Sergei Ivankov <sergeiivankov@yandex.ru>
 * @link     https://github.com/xooler/xs-php
 */
class DB {
	/**
	 * Объект текущего соединения с базой данных
	 *
	 * @var  object
	 */
	private static $conn;

	/**
	 * Стандартные настройки подключения к базе данных
	 *
	 * @var  array
	 */
	private static $defaults = [
		'host' => 'localhost',
		'user' => '',
		'pass' => '',
		'base' => '',
		'port' => NULL,
		'socket' => NULL,
		'charset' => 'utf8'
	];

  /**
	 * Установленные настройки для подключения к базе данных
	 *
	 * @var  array
	 */
  private static $config = [];

  /**
   * Установка настроек подключения к базе данных
   *
   * @param  array  $config  Настройки подключения к базе данных
   */
  public static function config($config) {
		self::$config = $config;
	}

	/**
	 * Публичный метод для компиляции строки запроса с параметрами
	 *
	 * @return  string  Скомпилированная строка запроса
	 *
	 * @example  self::parse("SELECT * FROM users WHERE id = ?i", 5)
	 *           Вернет строку "SELECT * FROM users WHERE id = 5"
	 */
	public static function parse() {
		return self::prepare_query(func_get_args());
	}

	/**
	 * Выполнение запроса без парсинга результата
	 *
	 * @return  object  Объект ответа библиотеки
	 *
	 * @example  self::query("INSERT INTO users (id, name) VALUES (1, 'Ivan')")
	 *           Используется для запросов, в которых нет необходимости
	 *           парсить результат
	 */
	public static function query() {
		return self::raw_query(self::prepare_query(func_get_args()));
	}

	/**
	 * Получение первого элемента результата запроса
	 *
	 * @return  string|null  Первый элемент результата запроса
	 *
	 * @example  self::get_one("SELECT name FROM users WHERE id = ?i", 5)
	 *           Вернет строку 'Ivan'
	 */
	public static function get_one() {
		$query = self::prepare_query(func_get_args());
		if($res = self::raw_query($query)) {
			$row = self::fetch($res);
			self::free($res);
			if(is_array($row)) return reset($row);
		}
		return null;
	}

	/**
	 * Получение первой строки результатов запроса
	 *
	 * @return  array|null  Массив с полями строки результата запроса
	 *
	 * @example  self::get_row("SELECT id, name FROM users WHERE id = ?i", 5)
	 *           Вернет массив с результаами [ 'id' => '5', 'name' => 'Ivan' ]
	 */
	public static function get_row() {
		$query = self::prepare_query(func_get_args());
		if($res = self::raw_query($query)) {
			$ret = self::fetch($res);
			self::free($res);
			return $ret;
		}
		return null;
	}

	/**
	 * Получение всех строк результатов запроса
	 *
	 * @return  array  Массив массивов с полями результатов запроса
	 *
	 * @example  self::get_all("SELECT id, name FROM users")
	 *           Вернет массив массивов с результаами:
	 *           [
	 *             [ 'id' => '3', 'name' => 'Ivan' ],
	 *             [ 'id' => '4', 'name' => 'Ivan' ],
	 *             [ 'id' => '5', 'name' => 'Ivan' ]
	 *           ]
	 */
	public static function get_all() {
		$ret = array();
		$query = self::prepare_query(func_get_args());
		if($res = self::raw_query($query)) {
			while($row = self::fetch($res)) $ret[] = $row;
			self::free($res);
		}
		return $ret;
	}

	/**
	 * Получение первой колонки полей результата запроса
	 *
	 * @return  array  Массив значений колонки
	 *
	 * @example  self::get_col("SELECT id FROM users")
	 *           Вернет массив с результатами: [ '3', '4', '5' ]
	 */
	public static function get_col() {
		$ret = array();
		$query = self::prepare_query(func_get_args());
		if($res = self::raw_query($query)) {
			while($row = self::fetch($res)) $ret[] = reset($row);
			self::free($res);
		}
		return $ret;
	}

	/**
	 * Получение ассоциативного массива с ключем - значение поля с именем первого
	 * параметра, а значениями - строками результата запроса
	 *
	 * @return  array  Ассоциативный массив результатов
	 *
	 * @example  self::get_ind('id', "SELECT id, name FROM users")
	 *           Вернет ассоциативный массив с результатами:
	 *           [
	 *             '3' => [ 'id' => '3', 'name' => 'Ivan' ],
	 *             '4' => [ 'id' => '4', 'name' => 'Ivan' ],
	 *             '5' => [ 'id' => '5', 'name' => 'Ivan' ]
	 *           ]
	 */
	public static function get_ind() {
		$args = func_get_args();
		$index = array_shift($args);
		$query = self::prepare_query($args);

		$ret = array();
		if($res = self::raw_query($query)) {
			while($row = self::fetch($res)) {
				$ret[$row[$index]] = $row;
			}
			self::free($res);
		}
		return $ret;
	}

	/**
	 * Получение ассоциативного массива с ключем - значение поля с именем первого
	 * параметра, а значениями - первой колонкой результатов
	 *
	 * @return  array  Ассоциативный массив результатов
	 *
	 * @example  self::get_ind_col('id', "SELECT id, name FROM users")
	 *           Вернет ассоциативный массив с результатами:
	 *           [
	 *             '3' => 'Ivan',
	 *             '4' => 'Ivan',
	 *             '5' => 'Ivan'
	 *           ]
	 */
	public static function get_ind_col() {
		$args = func_get_args();
		$index = array_shift($args);
		$query = self::prepare_query($args);

		$ret = array();
		if($res = self::raw_query($query)) {
			while($row = self::fetch($res)) {
				$key = $row[$index];
				unset($row[$index]);
				$ret[$key] = reset($row);
			}
			self::free($res);
		}
		return $ret;
	}

	/**
	 * Возвращает идентификатор последней добавленной записи
	 *
	 * @return  string  Идентификатор записи
	 */
	public static function insert_id() {
		return mysqli_insert_id(self::$conn);
	}

	/**
	 * Возвращает количество затронутых ячеек в последнем запросе
	 *
	 * @return  int  Количество ячеек
	 */
	public static function affected_rows() {
		return mysqli_affected_rows(self::$conn);
	}

	/**
	 * Возвращает число рядов в результирующей выборке
	 * @param   object  $result  Объект результата запроса библиотеки
	 * @return  int              Количество рядов
	 */
	public static function num_rows($result) {
		return mysqli_num_rows($result);
	}

	/**
	 * Освобождает память от результатов запроса
	 * @param   object  $result  Объект результата запроса библиотеки
	 */
	private static function free($result) {
		mysqli_free_result($result);
	}

	/**
	 * Парсинг объекта ответа от библиотеки
	 * @param   object  $result  Результат выполнения запроса от библиотеки
	 * @return  array            Массив результатов парсинга
	 */
	private static function fetch($result) {
		return mysqli_fetch_array($result, MYSQLI_ASSOC);
	}

	/**
	 * Выполнение запроса у базе данных
	 * @param   string  $query  Строка запроса
	 * @return  object          Объект ответа библиотеки
	 */
	private static function raw_query($query) {
		$res = mysqli_query(self::$conn, $query);

		if(!$res) {
			$error = mysqli_error(self::$conn);
			self::error("$error. Полный запрос: [$query]");
		}

		return $res;
	}

	/**
	 * Компиляция строки запроса к базе данных
	 *
	 * @param   array   $args  Агрументы функции
	 * @return  string         Скомпилированная строка запроса
	 */
	private static function prepare_query($args) {
		if(!self::$conn) {
			// Слияние стандартных и параметров из файла настроек
			$opt = array_merge(self::$defaults, self::$config);

			// Подключение к базе данных
			@self::$conn = mysqli_connect($opt['host'], $opt['user'], $opt['pass'], $opt['base'], $opt['port'], $opt['socket']);
			if(!self::$conn) self::error(mysqli_connect_errno() . ' ' . mysqli_connect_error());

			// Установка кодировки
			mysqli_set_charset(self::$conn, $opt['charset']) or self::error(mysqli_error(self::$conn));
			unset($opt);
		}

		// Получение информации о запросе
		$query = '';
		$raw = array_shift($args);
		$array = preg_split('~(\?[nslifuap])~u', $raw, null, PREG_SPLIT_DELIM_CAPTURE);
		$anum = count($args);
		$pnum = floor(count($array) / 2);

		// Проверка количества параметров и количества аргументов
		if($pnum != $anum) self::error("Количество аргументов ($anum) не соответствует количеству параметров ($pnum) в [$raw]");

		// Формирование строки запроса
		foreach($array as $i => $part) {
			if(($i % 2) == 0) {
				$query .= $part;
				continue;
			}

			$value = array_shift($args);
			switch ($part) {
				case '?i':
					$part = self::escape_int($value);
					break;
				case '?f':
					$part = self::escape_float($value);
					break;
				case '?s':
					$part = self::escape_string($value);
					break;
				case '?n':
					$part = self::escape_ident($value);
					break;
				case '?l':
					$part = self::escape_like($value);
					break;
				case '?a':
					$part = self::create_in($value);
					break;
				case '?u':
					$part = self::create_set($value);
					break;
				case '?p':
					$part = $value;
					break;
			}

			$query .= $part;
		}
		return $query;
	}

	/**
	 * Проверка числового параметра
	 *
	 * @param   int|float         $value  Параметр
	 * @return  string|int|float          Параметр после проверки
	 */
	public static function escape_int($value) {
		if($value === NULL) return 'NULL';

		if(!is_numeric($value)) {
			self::error('Ошибка в числовом (?i) параметре, передан ' . gettype($value));
			return false;
		}

		if(is_float($value)) $value = number_format($value, 0, '.', '');

		return $value;
	}

	/**
	 * Проверка параметра числа с плавабщей запятой
	 *
	 * @param   int|float         $value  Параметр
	 * @return  string|int|float          Параметр после проверки
	 */
	public static function escape_float($value) {
		if($value === NULL) return 'NULL';

		if(!is_numeric((float) $value)) {
			self::error('Ошибка в числовом с плавающей запятой (?f) параметре, передан ' . gettype($value));
			return false;
		}

		$value = (float) str_replace(',', '.', $value);

		return $value;
	}

	/**
	 * Проверка строкового параметра
	 *
	 * @param   string  $value  Параметр
	 * @return  string          Параметр после проверки
	 */
	public static function escape_string($value) {
		if($value === NULL) return 'NULL';

		return "'" . mysqli_real_escape_string(self::$conn, $value) . "'";
	}

	/**
	 * Проверка строкового параметра для LIKE
	 *
	 * @param   string  $value  Параметр
	 * @return  string          Параметр после проверки
	 */
	public static function escape_like($value) {
		if($value === NULL) {
			self::error('Ошибка в строковом LIKE (?l) параметре, передан ' . gettype($value));
			return false;
		}

		return "'%" . mysqli_real_escape_string(self::$conn, $value) . "%'";
	}

	/**
	 * Проверка строкового параметра для идентификатора
	 *
	 * @param   string  $value  Параметр
	 * @return  string          Параметр после проверки
	 */
	public static function escape_ident($value) {
		if($value) return "`" . str_replace("`", "``", $value) . "`";
		else self::error('Пустое значение для параметра идентификатора (?n)');
	}

	/**
	 * Формирование строки для массива IN
	 *
	 * @param   array   $value  Массив
	 * @return  string          Сформированная строка для запроса
	 */
	public static function create_in($data) {
		if(!is_array($data)) {
			self::error('Параметром для операции IN (?a) должен быть массив');
			return;
		}

		if(!$data) return 'NULL';

		$query = $comma = '';
		foreach($data as $value) {
			$query .= $comma . self::escape_string($value);
			$comma = ',';
		}

		return $query;
	}

	/**
	 * Формирование строки для операции SET
	 *
	 * @param   array   $value  Ассоциативный массив
	 * @return  string          Сформированная строка для запроса
	 */
	public static function create_set($data) {
		if(!is_array($data)) {
			self::error('Параметр для операции SET (?u) должен быть массивом, передан ' . gettype($data));
			return;
		}

		if(!$data) {
			self::error('Передан пустой массив для операции SET (?u)');
			return;
		}

		$query = $comma = '';
		foreach($data as $key => $value) {
			$query .= $comma . self::escape_ident($key) . '=' . self::escape_string($value);
			$comma = ',';
		}

		return $query;
	}

	/**
	 * Вывод ошибки класса работы с базой данных
	 *
	 * @param   string  $err  Текст ошибки
	 */
	private static function error($err) {
		throw new Exception(__CLASS__ . ': ' . $err);
	}
}
