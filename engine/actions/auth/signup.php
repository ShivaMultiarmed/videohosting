<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    
    require("{$Root}engine/common.php");

    require("{$Root}engine/classes/auth.php");

    echo json_encode(Auth::signUp($_POST));
?>