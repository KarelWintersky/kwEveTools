<?php
require_once 'kw.XML.php';

class kwEveAPI
{
    private $url_api = 'https://api.eveonline.com';
    private $url_api_key = '';
    private $characters_list = array();

    public static function convertSymbols($str)
    {
        return html_entity_decode(str_replace('\u','&#x', $str), ENT_NOQUOTES,'UTF-8');
    }

    public function getXML($request, $params)
    {
        return kwXML::getXML( $this->url_api.$request.$this->url_api_key.$params );
    }

    public function getSimpleXML($request, $params)
    {
        return simplexml_load_string( $this->getXML($request, $params) );
    }

    public static function convertXMLtoJSON($xml)
    {
        return $json = json_encode($xml);
    }

    public static function convertXMLtoArray($xml)
    {
        return json_decode( json_encode($xml), TRUE );
    }

    public function __construct($key, $vcode)
    {
        $this->url_api_key = "?keyID={$key}&vCode={$vcode}";

        $xml_charslist = $this->getXML('/account/Characters.xml.aspx', '');
    }

    public function getCharacterID($id, $key)
    {
        $request = "https://api.eveonline.com/account/characters.xml.aspx?keyID={$id}&vCode={$key}";
        $content = file_get_contents($request);

        $xml = new SimpleXMLElement($content);

        return $xml->result[0]->rowset->row['characterID'];
    }


}


?>