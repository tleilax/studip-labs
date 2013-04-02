<?php
    $contents = str_replace(' ', '+', $_POST['contents']);
    file_put_contents('uploads/' . date('YmdHis') . '.jpg', base64_decode($contents));
