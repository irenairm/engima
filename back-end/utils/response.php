<?php
function returnResponse($status_code, $message)
{
    echo json_encode(
        array(
            "status_code" => $status_code,
            "message" => $message
        )
    );
}

function returnSearch($status_code, $message, $count)
{
    echo json_encode(
        array(
            "status_code" => $status_code,
            "message" => $message,
            "count" => $count
        )
    );
}
