<?php

function gpon_info_widget() {
    $username = 'admin';
    $password = '1234';
    $context = stream_context_create(array(
        'http' => array(
            'header' => 'Authorization: Basic ' . base64_encode("$username:$password")
        )
    ));

    $json_data = @file_get_contents("http://10.10.1.1/cgi/get_gpon_info?rand=" . mt_rand(), false, $context);

    // Check if data was fetched
    if (!$json_data) {
        return "Error fetching GPON data.";
    }

    // Attempt to fix the malformed JSON
    $json_data_fixed = preg_replace('/(\w+):/i', '"$1":', $json_data);
    $info = json_decode($json_data_fixed, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Error decoding GPON data: " . json_last_error_msg() . "<br>Raw Data: " . $json_data;
    }

    $loid_auth_status = array("INIT", "SUCCESS", "LOID INEXITENCE", "PASSWORD ERROR", "FAIL");
    $info["loid_status"] = isset($loid_auth_status[$info["loid_status"]]) ? $loid_auth_status[$info["loid_status"]] : $info["loid_status"];

    $output = '<div class="gpon-widget">';
    $output .= "<strong>GPON Line Status:</strong> O" . $info["line_status"] . "<br>";
    $output .= "<strong>LOID Auth Status:</strong> " . $info["loid_status"] . "<br>";
    $output .= "<strong>Temperature:</strong> " . $info["temp"] . "C<br>";
    $output .= "<strong>Voltage:</strong> " . $info["voltage"] . "V<br>";
    $output .= "<strong>Current:</strong> " . $info["current"] . "mA<br>";
    $output .= "<strong>TX Power:</strong> " . $info["tx_power"] . "dBm<br>";
    $output .= "<strong>Rx Power:</strong> " . $info["rx_power"] . "dBm<br>";
    $output .= '</div>';

    return $output;
}

print(gpon_info_widget());

?>

<style>
    .gpon-widget {
        border: 1px solid #d5d5d5;
        border-radius: 5px;
        max-width: 100%;
        background-color: #f8f8f8;
        padding: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
    }

    .gpon-widget h3 {
        text-align: center;
        margin: 0;
        padding: 5px 0;
        border-bottom: 1px solid #d5d5d5;
    }
</style>
