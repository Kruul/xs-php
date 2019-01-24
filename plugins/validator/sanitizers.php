<?

/**
 * Триминг строки
 */
Validator::add_sanitize('trim', function($value) {
  return @trim($value);
});

/**
 * Конвертация в int тип
 */
Validator::add_sanitize('intval', function($value) {
  return @intval($value);
});

/**
 * Конвертация в float тип
 */
Validator::add_sanitize('floatval', function($value) {
  return @floatval($value);
});

/**
 * Конвертация в string тип
 */
Validator::add_sanitize('strval', function($value) {
  return @strval($value);
});

/**
 * Конвертация в строку валюты вида '111.55'
 */
Validator::add_sanitize('currency', function($value) {
  return number_format(@floatval($value), 2, '.', '');
});

/**
 * Перевод в нижний регистр
 */
Validator::add_sanitize('tolower', function($value) {
  return mb_strtolower($value);
});

/**
 * Удаление html спецсимволов (фильтрация от xss атак)
 */
Validator::add_sanitize('xss', function($value) {
  return htmlspecialchars(strip_tags($value));
});
