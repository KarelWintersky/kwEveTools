<?php

function isAjaxCall()
{
    return ((!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
}



?>