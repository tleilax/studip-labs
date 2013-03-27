<?php
$file = 'uploads/' . date('YmdHis') . '.jpg';
$contents = $_POST['contents'];
$encodedData = str_replace(' ', '+', $contents);
$decodedData = base64_decode($encodedData);
file_put_contents($file, $decodedData);
