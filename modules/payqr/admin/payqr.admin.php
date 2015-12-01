<?php
/**
 * Редактирование статей
 * 
 * @package    Diafan.CMS
 * @author     diafan.ru
 * @version    5.4
 * @license    http://cms.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2014 OOO «Диафан» (http://diafan.ru)
 */
if (!defined('DIAFAN'))
{
    include dirname(dirname(dirname(__FILE__))).'/includes/404.php';
}

/**
 * Payqr_admin
 */
class Payqr_admin extends Frame_admin
{
	private $ShowInPlace = array("cart", "product", "category");

	private $buttonXmlStructure = array();

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'payqr';

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'act', // показать/скрыть
		'del', // удалить
		'datetime', // показывать дату в списке, сортировать по дате
		'trash', // использовать корзину
		'order', // сортируется
	);

    public function init()
    {
        if(! empty($_POST['action']))
        {
            if($_POST['action'] == "save")
            {
                $this->save();
            }
        }

        parent::init();
    }

    public function show()
    {
        //вызываем функционал библиотеки PayQR
        require_once __DIR__ . '/../../../payqr/PayqrConfig.php';
        $auth = new PayqrModuleAuth(1);
        $user = $auth->getUser();
        if($user)
        {
            
            $html = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>';

            if(is_file(__DIR__ . '/js/button.js'))
            {
                $html .= file_get_contents(__DIR__ . '/js/button.js');
            }

            if(is_file(__DIR__ . '/css/buttonstyle.css'))
            {
                $html .= file_get_contents(__DIR__ . '/css/buttonstyle.css');
            }
            
            $button = new PayqrButtonPage($user);
            
            if(isset($_POST["PayqrSettings"]))
            {
                $button->save($_POST["PayqrSettings"]);
            }
            $html .= $button->getHtml();
            echo $html;
        }
    }

	/**
	 * Выводит контент модуля
	 * 
	 * @return void
	 */
	public function show_()
	{
        $result = DB::query_fetch_array("SELECT value FROM {config} WHERE module_name='payqr'");

        $settings = isset($result['value'])? json_decode($result['value'], true) : array();

        $xml_structure = $this->getStructure();

        //инициализируем общие настройки кнопки
        $html = "<form action='' method='post'>";
        $html.= "<input type='hidden' name='action' value='save'>";
        $html.= "<input type='hidden' name='module' value='payqr'>";

        foreach($xml_structure as $row)
        {
            if(isset($row->field) && !$this->buttonStructure($row))
            {
                $html.= $this->generateHtml($row, $settings);
            }
        }

        //инициализиурем параметры кнопки в соответствии с местом отображения
        foreach($this->ShowInPlace as $place)
        {
            foreach($this->buttonXmlStructure as $xmlrow)
            {
                $html.= $this->generateHtml($xmlrow, $settings, $place);
            }
        }

        $html.= "<input type='submit'>";

        $html.= "</form>";

        echo $html;
	}

	/**
     * 
     * @param type $xmlRow
     * @param type $settings
     * @param type $place - Для какого места (карточка товара, корзина, категория товара) будет настраиваться настройка
     * @return type
     */
    private function generateHtml($xmlRow, $settings, $place = false)
    {
        $html = "";
        
        $button_option = $xmlRow->field;
            
        $html .= "<div class=''>";
            $html .= "<div class=''>";
            $html .= $button_option[4]['value'];
            $html .= "</div>";

            $html .= "<div class=''>";

            $fieldName = (string)($place ? $place . $button_option[0]['value'] : $button_option[0]['value']);

            switch ($button_option[1]['value'])
            {
                case 'text':
                    $html .= "<input type='text' name='".$fieldName."' value='" . (isset($settings[$fieldName])? $settings[$fieldName] : $button_option[2]['value']) ."' ".
                                                            ($button_option[5]['value'] == "0" ? "disabled='disabled'" : "") . ">";
                    break;
                case 'select':
                    $select = json_decode($button_option[3]['value'], true);

                    $html .= "<select name='".$fieldName."' ". ($button_option[5]['value'] == "0"? "disabled='disabled'" : "") .">";

                    foreach($select as $element) {

						$html .= "<option value='";

                    	if(isset($settings[$fieldName]) && !empty($settings[$fieldName]) && $button_option[2]['value'] == $settings[$fieldName])
                    	{
                    		$html .= $settings[$fieldName] . "' selected";
                    	}
                    	else 
                    	{
                    		$html .= $button_option[2]['value']. "' ";
                    	}

                    	$html .= ">" . $element . "</option>";

                    }
                    $html .= "</select>";
                    break;
            }

            $html .= "</div>";
        $html .= "</div>";
        
        return $html;
    }


    /**
     * Получение структуры кнопки
     * @return array
     */	
    private function getStructure()
    {
            $path = __DIR__ . '/../';

            if(file_exists( $path . 'payqr.button.schema.xml'))
            {
            $xmlObject = new SimpleXMLElement(file_get_contents($path . 'payqr.button.schema.xml'));

            return $xmlObject ? $xmlObject : array();
            }
    }

    /**
     * Проверяет, является поле структурой кнопки
     * @return bool
     */
    private function buttonStructure($xmlRow)
    {
        $button_option = $xmlRow->field;

        $fieldName = $button_option[0]['value'];

        if(strpos($fieldName, "button") !== false)
        {
            $this->buttonXmlStructure[] = $xmlRow;

            return true;
        }
        return false;
    }

    public function save()
    {
        file_put_contents("action.log", "save", FILE_APPEND);
        file_put_contents("action.log", print_r($_POST, true), FILE_APPEND);
        /*
        *  Проверяем наличие настройки в таблице конфиго, в случае наличия обновляем, иначе производим вставку
        */
        $result = DB::query_fetch_array("SELECT * FROM {config} WHERE module_name='payqr' AND name='button'");

        if(!$result)
        {
            file_put_contents("action.log", "Insert", FILE_APPEND);
            DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('%s', '%s', '%s')", 'payqr', 'button', json_encode($_POST));
        }
        else
        {
            file_put_contents("action.log", "Update", FILE_APPEND);
            DB::query("UPDATE {config} SET value='%s' WHERE module_name='payqr' AND name='button'", json_encode($_POST));
        }
    }
}