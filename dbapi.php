<?php
$api_loc = ($_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/../api/';

function get_from_api($api_name) {
  global $api_loc;

  $apiUrl = $api_loc . $api_name;
  $ch = curl_init($apiUrl);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  unset($ch);

  return json_decode($response, true);
}

function post_to_api($api_name, $data) {
  global $api_loc;

  $apiUrl = $api_loc . $api_name;
  $ch = curl_init($apiUrl);

  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  unset($ch);

  return json_decode($response, true);
}
?>