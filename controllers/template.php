<?

/**
 * Контроллер базового шаблона
 */
class Controller_Template extends Controller {
  /**
   * Название базового шаблона вида
   * @var  string
   */
  public $template_name = 'layout';

  /**
   * Свойство для установки контента базового шаблона
   * @var  string
   */
  public $content;

  /**
   * Создает базовый шаблон и устанавливает ему свойство content
   */
  public function after() {
    // Работает только если в свойство response ничего не записано
    if($this->response === null) {
      View::set_global('controller', $this->controller);
			View::set_global('action', $this->action);

			$this->response = View::factory($this->template_name, [
        'content' => $this->content
      ]);
		}
  }
}
