<?php
require_once('kw.kwt.php');

function convertSymbols($str)
{
    return html_entity_decode(str_replace('\u','&#x', $str), ENT_NOQUOTES,'UTF-8');
}

function getCharacters($id, $key)
{
    $chars = array();
    $request = "https://api.eveonline.com/account/characters.xml.aspx?keyID={$id}&vCode={$key}";
    $content = @file_get_contents($request);

    $xml = new SimpleXMLElement($content);
    $chars = array(
        'state' => 'ok',
        'error' => 0
    );

    foreach ( $xml->result->rowset->row as $character ) {
        $chars['data'][] = array(
            'type'  => 'option',
            'value' => (string)$character['characterID'],
            'text'  => (string)$character['name']
        );
    }
    return $chars;
}

function getCharacterNameByID($id)
{
    $request = "https://api.eveonline.com/eve/CharacterName.xml.aspx?IDs={$id}";
    $xml = new SimpleXMLElement( file_get_contents($request) );
    return (string)$xml->result->rowset->row[0]['name'];
}

function getWallet($id, $key, $charid, $count=-1, $from = -1 )
{
    $full_wallet = array();

    if ($count == -1 || $count > 2560) {
        $w_offset = -1;
        $portion_size = 2560;
        do {
            $portion = getWallet($id, $key, $charid, $portion_size , $w_offset);

            $loaded = count( $portion );

            $last = end( $portion );
            $w_offset = $last['id'];

            $full_wallet = array_merge( $full_wallet, $portion);

        } while ($loaded != 0);
    } else {
        $from_str = ($from != -1 ) ? "&fromID={$from}" : "";
        $request = "https://api.eveonline.com/char/WalletJournal.xml.aspx?keyID={$id}&vCode={$key}&characterID={$charid}&rowCount={$count}";
        $request .= $from_str;

        $data = file_get_contents($request);

        $xml = new SimpleXMLElement($data);

        foreach($xml->result->rowset->row as $wallet_record) {
            $refid = (string)$wallet_record['refID'];

            $full_wallet[ $refid ] = array(
                'id'            => (string)$wallet_record['refID'],
                'datetime'          => (string)$wallet_record['date'],
                'from'          => (string)$wallet_record['ownerName1'],
                'fromid'        => (int)$wallet_record['ownerID1'],
                'to'            => (string)$wallet_record['ownerName2'],
                'toid'        => (int)$wallet_record['ownerID2'],
                'amount'        => (int)$wallet_record['amount'],
                //@todo: string number_format ( float $number , int $decimals = 0 , string $dec_point = '.' , string $thousands_sep = ',' )
                'reason'        => ($wallet_record['reason'] != "") ? convertSymbols($wallet_record['reason']) : "&nbsp;",
                'type'          => trim((string)$wallet_record['refTypeID']) // see https://api.eveonline.com/eve/RefTypes.xml.aspx
            );
        }
    }
    return $full_wallet;
}

function filterWallet($walletdata, $toid, $transaction_type)
{
    $outwallet = array();
    foreach ($walletdata as $refid => $record )
    {
        if ( ($record['toid'] == $toid) && ($record['type'] == $transaction_type)) {
            $outwallet [ $refid ] = $record;
        }
    }
    return $outwallet;
}

function viewWalletRow( $record )
{
    $html = new kwt('assets/ogb.table.row.html');
    $html->override( array (
        'date'  => $record['datetime'],
        'from'  => $record['from'],
        'amount'=> $record['amount'],
        'reason'=> $record['reason']
    ));
    return $html->get();
}

function saveLogToFile($filename, $data)
{
    $fh = fopen($filename, 'a+');
    $data['request_time'] = date("d F Y G:i:s");
    fwrite($fh, json_encode($data)."\r\n");
    fclose($fh);
}

?>