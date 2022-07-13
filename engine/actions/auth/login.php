<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    require($Root . "engine/common.php");

    require($Root . "engine/classes/auth.php");

    $nickname = trim(htmlspecialchars($_POST['nickname']));
    $password = trim(htmlspecialchars($_POST['password']));

    echo json_encode(Auth::login($nickname, $password));
?>