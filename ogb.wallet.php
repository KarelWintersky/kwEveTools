<?php
require_once('core/kw.EveAPI.php');

function isAjaxCall()
{
    return ((!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
}

function getCharacters($id, $key)
{
    $chars = array();
    $request = "https://api.eveonline.com/account/characters.xml.aspx?keyID={$id}&vCode={$key}";
    $content = file_get_contents($request);

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

}

if (isAjaxCall()) {
    $actor = $_GET['actor'];
    switch ($actor) {
        case 'getcharacters': {
            $key    = $_GET['key'];
            $vcode  = $_GET['vcode'];

            $characters = getCharacters($key, $vcode);

            print( json_encode( $characters ) );

            break;
        }
        case 'getwallet': {
            $cid = $_GET['cid'];
            getCharacterNameByID($id);


            print_r( $_GET );
            break;
        }
    }
} else {
    ?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>OGB Wallet transactions log</title>
    <script type="text/javascript" src="assets/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="assets/kw.options.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $.ajaxSetup({cache: false});
            var $form = $("#inputform");
            var url = $form.attr('action');
            var $key = $form.find("input[name='key']");
            var $vcode = $form.find("input[name='vcode']");

            $('#actor-clear').on('click', function() {
                $("select[name='charlist']").empty().prop('disabled', true);
                $('#actor-getwallet').prop('disabled', true);
                $('#actor-getcsv').prop('disabled', true);
            });
            var getting;


            $('#actor-getcharacters').on('click', function(){
                if ( ($key.val().length * $vcode.val().length) != 0 ) {
                    getting = $
                            .get( url, {
                                actor   :   'getcharacters',
                                key     :   $key.val(),
                                vcode   :   $vcode.val()
                            } )
                            .done( function(data) {
                                BuildSelectorExpert('charlist', data, false, false);
                                $("#actor-getwallet").prop('disabled', false);
                            } );
                } else {
                    $("#output").html( 'Key or vCode can\'t be empty! ' );
                }
            });

            $("#actor-getwallet").on('click', function() {
                var cid = getSelectedOptionValue('inputform', 'charlist');
                if ( ($key.val().length * $vcode.val().length * cid) != 0 ) {
                    getting = $
                            .get( url, {
                                actor   : 'getwallet',
                                key     : $key.val(),
                                vcode   : $vcode.val(),
                                cid     : cid
                            })
                            .done( function(data) {
                                $("#output").html( data );
                            });
                } else {
                    $("#output").html( 'Key or vCode can\'t be empty! Also, please, select character! ' );
                }
            });

        });
    </script>
    <style>
        fieldset {
            border: 1px navy solid;
        }
        dt {
            float: left;
            width: 80px;
            text-align: right;
            padding-right: 5px;
            min-height: 1px;
        }
        dd {
            position: relative;
            top: -1px;
            margin-bottom: 10px;
        }
        select {
            width: 200px;
        }
        #actor-clear {
            margin-left: 10em;
        }
    </style>
</head>
<body>

<form action="<?=$SCRIPT_NAME;?>" method="get" id="inputform">
    <dl>
        <dt>Key:</dt>
        <dd><input type="text" name="key" size="80"> </dd>
        <dt>vCode:</dt>
        <dd><input type="text" name="vcode"  size="80"></dd>
        <dt>&nbsp;</dt>
        <dd>
            <button type="button" id="actor-getcharacters"> Get characters </button>
            <button type="button" id="actor-clear"> Clear </button>
        </dd>
        <dt>Character:</dt>
        <dd><select name="charlist" disabled></select></dd>
        <dt>&nbsp;</dt>
        <dd>
            <button type="button" id="actor-getwallet" disabled> Get wallet </button>
            &nbsp;&nbsp;&nbsp;
            <button type="button" id="actor-getcsv" disabled> Download CSV </button>
        </dd>
    </dl>

</form>

<fieldset>
    <legend>Result</legend>
    <pre id="output">

    </pre>
</fieldset>


</body>
</html>

<?php
}
?>