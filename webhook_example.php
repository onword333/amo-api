<?php

use GuzzleHttp\Psr7\Query;

use function GuzzleHttp\Promise\queue;

include __DIR__ . '/amo_api.php';
include __DIR__ . '/Chat2Desk.php';

date_default_timezone_set('Europe/Moscow');

// АМО ограничение: 7 запросов в секунду
// каждый раз берем рандомное количество секунд, пусть от 3 до 10
$randomSececond = rand(3, 10);
sleep($randomSececond);

$api = new AmoApi;
$token = $api->getToken();

$data = [];

$query = [];

// сопоставление полей ga и amo
$fieldsMap = [
  'phone' => '75352'
];

if (!empty($_POST['leads']['status'])) {
	$data = $_POST['leads']['status'];
}


if (!empty($data)) {
  $idLead = $data[0]['id'];
  $statusId = $data[0]['status_id'];
  $pipelineId = $data[0]['pipeline_id'];

  $res = $api->getLeadById($idLead);

  $company = '';
  $idCompany = '';

  if (isset($res->_embedded->companies[0]->id)) {
    $idCompany = $res->_embedded->companies[0]->id;
  }

  if (!empty($idCompany)) {
    $company = $api->getCompanyById($idCompany);
    $query['name'] = $company->name;
  }

  if (isset($company->custom_fields_values)) {
  
    foreach ($company->custom_fields_values as $item) {
      $field_id = $item->field_id;
      $searchRes = array_search($field_id, $fieldsMap);
    
      if (empty($searchRes)) {
        continue;
      }

      $value = isset($item->values[0]) ? $item->values[0]->value : '';
      if (empty($value)) {
        continue;
      }

      $query[$searchRes] = $value;
    }
  }

  $phone = '';
  if (isset($query['phone'])) {
    $phone = preg_replace("/[^0-9]/", '', $query['phone']);
  }

  // отправляем телефон

} else {
  $logText = 'error: данные от АМО отсутствуют ';
  mylog($logText);
}

http_response_code(200);

?>