<?php
require_once('core/kw.core.php');
require_once('core/eveapi.php');

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
            // $charname = getCharacterNameByID($cid);

            $wallet = getWallet($_GET['key'], $_GET['vcode'], $cid);
            $wallet = filterWallet( $wallet , $cid, 10 );

            $out = '<table border="1">'.viewWalletAsTable($wallet).'</table>';

            print($out);

            // print_r( $wallet );
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
    <link rel="stylesheet" href="assets/ogb.wallet.css">

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