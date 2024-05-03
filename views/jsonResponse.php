<?php
http_response_code($code);
echo json_encode(
    $values
);