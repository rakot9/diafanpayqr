<?php
/**
 * Конструктор кнопки PayQR
 */

class PayqrButton
{
    /*
     * Основные атрибуты кнопки
     */
    
    // Название кнопки в приложении PayQR и другие текстовые изменения (buy - "Купить", pay - "Оплатить")
    private $scenario = 'buy';
    // Содержание заказа (массив позиций заказа)
    private $productsArray = array();
    // Сумма заказа
    private $amount; 
    

    /*
     * Варианты оформления кнопки PayQR
     */
    //Цвет кнопки
    const COLOR_DEFAULT = "";
    const COLOR_ORANGE = "payqr-button_orange";
    const COLOR_RED = "payqr-button_red";
    const COLOR_BLUE = "payqr-button_blue";
    const COLOR_GREEN = "payqr-button_green";
    
    //Форма кнопки
    const FORM_DEFAULT = "";
    const FORM_SHARP = "payqr-button_sharp";
    const FORM_RUDE = "payqr-button_rude";
    const FORM_SOFT = "payqr-button_soft";
    const FORM_OVAL = "payqr-button_oval";
    const FORM_SLEEK = "payqr-button_sleek";
    
    // Градиент
    const GRADIENT_DEFAULT = '';
    const GRADIENT_FLAT = 'payqr-button_flat';
    const GRADIENT_GRADIENT = 'payqr-button_gradient';
    
    // Тень
    const SHADOW_DEFAULT = "";
    const SHADOW_SHADOW = "payqr-button_shadow";
    const SHADOW_NOSHADOW = "payqr-button_noshadow";
    
    // Регистр текста
    const TEXTCASE_DEFAULT = "";
    const TEXTCASE_UPPER = "payqr-button_text-uppercase";
    const TEXTCASE_LOWER = "payqr-button_text-lowercase";
    const TEXTCASE_STANDARD = "payqr-button_text-standardcase";
    
    // Толщина текста
    const FONTWEIGHT_DEFAULT = "";
    const FONTWEIGHT_BOLD = "payqr-button_text-bold";
    const FONTWEIGHT_NORMAL = "payqr-button_text-normal";
    
    // Размер текста
    const FONTSIZE_DEFAULT = "";
    const FONTSIZE_SMALL = "payqr-button_text-small";
    const FONTSIZE_LARGE = "payqr-button_text-large";
    const FONTSIZE_MEDIUM = "payqr-button_text-medium";
    
    private $classes = array(
        "payqr-button"
    );
    private $styles = array(
        "width" => "36px",
        "height" => "163px",
    );
    
    // Название кнопки
    private $button_text = "Купить быстрее";
    // Кастомные атрибуты
    private $attrs = array();

    /*
     * Запрашиваемые поля
     */
    const REQUIRE_FIRSTNAME = 'data-firstname-required';
    const REQUIRE_MIDDLENAME = 'data-middlename-required';
    const REQUIRE_LASTNAME = 'data-lastname-required';
    const REQUIRE_PHONE = 'data-phone-required';
    const REQUIRE_EMAIL = 'data-email-required';
    const REQUIRE_DELIVERY = 'data-delivery-required';
    const REQUIRE_DELIVERYCASES = 'data-deliverycases-required';
    const REQUIRE_PICKPOINTS = 'data-pickpoints-required';
    const REQUIRE_PROMOCODE = 'data-promocode-required';
    const REQUIRE_PROMOCARD = 'data-promocard-required';
    
    const FIELD_DEFAULT = 'default';
    const FIELD_REQUIRED = 'required';
    const FIELD_DENY = 'deny';
    const FIELD_NONREQUIRED = 'nonrequired';
    
    private $order_id = false; // номер заказа
    private $ordergroup = false; // номер товарной группы
    private $promocode_details = false; // описание поля для ввода промо-кода или номера карты лояльности
    private $promocard_details = false; // описание поля для ввода промо-кода или номера карты лояльности
    private $userdata = false; // заполнение любых дополнительных служебных/аналитических данных в свободном формате
    
    public function __construct($amount, $productsArray = array())
    {
      $this->amount = $amount;
      $this->productsArray = $productsArray;
    }

    /**
     * Возвращает код скрипта PayQR для размещения в head интернет-сайта
     */
    public static function getJs()
    {
      return '<script src="https://payqr.ru/popup.js?merchId=' . PayqrConfig ::$merchantID . '"></script>';
    }

    /**
     * Устанавливает ширину кнопки PayQR
     * @param $width
     */
    public function setWidth($width)
    {
      $this->styles["width"] = $width . "px";
    }

    /**
     * Устанавливает высоту кнопки PayQR
     * @param $height
     */
    public function setHeight($height)
    {
      $this->styles["height"] = $height . "px";
    }

    /**
     * Устанавливает css классы кнопки
     * @param $class
     */
    public function setCssClass($class)
    {
      $this->classes[] = $class;
    }

    /**
     * Устанавливает payqr css классы кнопки
     * @param $property
     */
    public function setProperty($property)
    {
        $this->setCssClass($property);
    }

    /**
     * Устанавливает css стили кнопки
     * @param $key
     * @param $value
     */
    public function setStyle($key, $value)
    {
        $this->styles[$key] = $value;
    }
    
    /**
     * Устанавливает любые кастомные аттрибуты в кнопке
     * @param $attr
     * @param $value
     */
    public function setAttr($attr, $value)
    {
        $this->attrs[$attr] = $value;
    }
    
    /**
     * Устанавливает какие поля запрашивать у пользователя
     * @param $field
     * @param $type
     */
    public function setRequiredField($field, $type)
    {
        $this->setAttr($field, $type);
    }
    
    /**
     * Устанавливает текст кнопки (только в случае если активирован параметр idkfa)
     * @param $text
     */
    public function setButtonText($text)
    {
        $this->button_text = $text;
    }
    
    /**
     * Устанавливает параметр разрешающий свободную установку стилей кнопки
     */
    public function setIdfka()
    {
        $this->classes[] = 'payqr-button_idkfa';
    }

    /**
     * Устанавливает номер товарной группы
     * @param $ordergroup
     */
    public function setOrderGroup($ordergroup)
    {
      $this->ordergroup = $ordergroup;
    }

    /**
     * Устанавливает Дополнительные данные получателя денежных средств к значению промо-кода, указанного пользователем
     * @param $details
     */
    public function setPromoCodeDetails($details)
    {
      $this->promocode_details = $details;
    }

    /**
     * Устанавливает Дополнительные данные получателя денежных средств к значению номера карты лояльности, указанного пользователем
     * @param $details
     */
    public function setPromoCardDetails($details)
    {
      $this->promocard_details = $details;
    }

    /**
     * Устанавливает userdata
     * @param $userdata
     */
    public function setUserData($userdata){
      $this->userdata = $userdata;
    }

    /**
     * Устанавливает номер заказа
     * @param $order_id
     */
    public function setOrderId($order_id)
    {
      $this->order_id = $order_id;
    }

    /**
     * Устанавливает номер заказа
     * @param $value
     */
    public function checkValue($value)
    {
        $replaceArray = array(
            "'" => "&#039;",
            '"' => "\"",
        );
        $newValue = str_replace(array_keys($replaceArray), array_values($replaceArray), $value);
        return $newValue;
    }

    /**
     * Проверка значений корзины
     */
    private function getCart()
    {
        foreach ($this->productsArray as $key=>$value)
        {
            if($key == "name")
            {
                $this->productsArray[$key] = $this->checkValue($value);
            }
        }
        return json_encode($this->productsArray);
    }    

    
    /**
     * Возвращает html кнопки
     * @return string
     */
    public function getHtmlButton()
    {
        $_attrs["class"] = implode(" ", $this->classes);
        $_attrs["data-scenario"] = $this->scenario;
        $_attrs["data-cart"] = $this->getCart();
        $_attrs["data-amount"] = $this->amount;
        
        if($this->order_id){
            $_attrs["order_id"] = $this->order_id;
        }
        if($this->ordergroup){
            $_attrs["ordergroup"] = $this->ordergroup;
        }
        if($this->promocode_details){
            $_attrs["promocode_details"] = $this->promocode_details;
        }
        if($this->promocard_details){
            $_attrs["promocard_details"] = $this->promocard_details;
        }
        if($this->userdata){
            $_attrs["userdata"] = $this->userdata;
        }
        
        $styles = array();
        foreach ($this->styles as $key=>$value)
        {
            $styles[] = "{$key}: {$value};";
        }
        $_attrs["style"] = implode(" ", $styles);
        
        foreach ($_attrs as $key=>$value)
        {
            $attrs[] = "{$key}='{$value}'";
        }
        foreach ($this->attrs as $key=>$value)
        {
            $attrs[] = "{$key}='{$value}'";
        }
        
        $html = "<button ";
        $html .= implode(" ", $attrs);
        $html .= ">";
        $html .= $this->button_text;
        $html .= "</button>";
        return $html;
    }
} 