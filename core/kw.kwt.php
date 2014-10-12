<?php
/*
KarelWintersky's Template simple engine ver 1.3
1.3 changed:

попытка сделать возможность парсить вложенные override-переменные, например
в override-массив попадает конструкция array ('val1' => 'data1', 'val2' => array( 'val3' => 'n') ) )
мы хотим такую конструкцию корректно распарсить так, чтобы можно было подставить n по соотв ключу,
например 'val2/val3' as 'n' или как-то похоже. Внимание, вопрос - как лучше строить такой ключ?

*/

function flatten($array, $prefix = '', $suffix = '/')
{
    $result = array();
    if (!is_array($array)) return null; // exit if array is empty
    foreach ($array as $key => $value)
    {
        if (is_array($value))
            $result = array_merge($result, flatten($value, $prefix . $key . $suffix, $suffix));
        else
            $result[$prefix . $key] = $value;
    }
    return $result;
}


class kwt
{
    private $file;
    private $tag_open = '{%';
    private $tag_close = '%}';
    private $overrides = array();
    private $content;

    private function get_include_contents($filename)
    {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        return null; // was false
    }

    private function buildKey($array, $key)
    {
        $value = $array [ $key ];
        $rkey = '';
        if (!is_array($value) ) {
            $rkey = $key;
        } else {
            foreach ( $value as $kkey => $kvalue ) {
                $rkey .= $key.'/'.$this->buildKey( $kvalue, $kkey );
            }
        }
        return $rkey;
    }

    // функция-обработчик. заменяет переменные в файле согласно массиву overrides
    private function kwt_callback(&$buffer)
    {
        $buf = $buffer;
        foreach ($this->overrides as $key => $value)
        {
            $skey = $this->tag_open.$key.$this->tag_close;
            $buf = str_replace($skey, $value, $buf);
        }
        return $buf;
    }

    // constructor: создаем экземпляр класса.
    // загружаем шаблон из $file, $open & $close - строки, обрамляющие заменяемые переменные
    public function __construct($file, $open = '{%', $close = '%}')
    {
        $this->file = dirname($_SERVER['SCRIPT_FILENAME']).'/'.$file;
        $this->tag_open = $open;
        $this->tag_close = $close;
        $this->content = $this->get_include_contents($this->file);
    }

    // создает (или дополняет) массив замещаемых переменных в шаблоне
    public function override($arr)
    {
        if (!empty($arr)) {
            foreach ($arr as $ki => $kv) {
                if (!array_key_exists(strtolower($ki), $this->overrides))
                    $this->overrides[strtolower($ki)] = $kv;
            }
        } else {
            $this->overrides = array_merge($this->overrides, $arr);
        }
        /* вообще-то надо обрабатывать массив вложенных ключей тут */
        $this->overrides = flatten( $this->overrides );
    }

    // возвращает обработанный шаблон в переменную (для использования в шаблонах верхнего уровня)
    public function get()
    {
        $return = $this->kwt_callback($this->content);
        return $return;
    }

    // выводит шаблон в буфер вывода, то есть в stdout (эквивалент функции flush() )
    public function out()
    {
        print $this->kwt_callback($this->content);
    }

    // переопределяет параметры экранирования заменяемых переменных, принимает строки
    public function config($start,$end)
    {
        $this->tag_open = $start;
        $this->tag_close = $end;
    }

    /* функции-обертки */

    /* вывод в stdout */
    public function flush()
    {
        $this->out();
    }

    /* вывод в переменную */
    public function getcontent()
    {
        return $this->get();
    }

    /* обертка, которая где-то (в ядре) вызывается, но функционально не нужна */
    public function contentstart()
    {}

}
?>