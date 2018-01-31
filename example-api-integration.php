<?php

/**************************************************/
// Megaplan API example
// Пример создания сделки с использованием API
/**************************************************/

$params = [
    // В какой схеме сделки создавать лид, внутренний Id мегаплана
    'deals' => [
        'ddu' => 15,
        'kupimdolg' => -1
    ],

    // Какого сотрудника назначать ответственным, внутренний Id мегаплана
    'staff' => [
        'ivanov' => 1000026,
    ]
];

// Данные, которые приходят с леднига
$leadData = [
    'fio' => 'Петров Тестовый Лид',         // required
    'dealId' => $params['deals']['ddu'],    // required
    'manager' => $params['staff']['ivanov'],// required

    // not required fields
    'phone' => '+7 (926) 000-00-00',
    'email' => 'test@mail.local',
    'data' => "Описание лида\n
               Вторая строка",
];

// Подключаем библиотеку API
include('resources/request.php');
include('resources/params.php');

// Авторизуемся в Мегаплане
$request = new SdfApi_Request('', '', $host, true);
$response = json_decode(
    $request->get(
        '/BumsCommonApiV01/User/authorize.api',
        array(
            'Login' => $login,
            'Password' => md5($password)
        )
    )
);

// Получаем AccessId и SecretKey
$accessId = $response->data->AccessId;
$secretKey = $response->data->SecretKey;

// Переподключаемся с полученными AccessId и SecretKey
unset($request);
$request = new SdfApi_Request($accessId, $secretKey, $host, true);

/**
 * Создаём клиента
 */

$requestData = [
    'Model[TypePerson]' => 'company',
    'Model[CompanyName]' => $leadData['fio'],
    'Model[Responsibles]' => $leadData['manager'],
];
if (!empty($leadData['email'])) {
    $requestData['Model[Email]'] = $leadData['email'];
}
if (!empty($leadData['phone'])) {
    $requestData['Model[Phones]'] = [$leadData['phone']];
}

$response = $request->post('/BumsCrmApiV01/Contractor/save.api', $requestData);
$responseData = json_decode($response);

$clientId = '';
if (!empty($responseData->status->code && $responseData->status->code == 'ok')) {
    $clientId = $responseData->data->contractor->Id;
} else {
    echo('Error! ' . $response);
    die;
}

/**
 * Создаём сделку
 */

if (!empty($clientId)) {

    $requestData = [
        'ProgramId' => $leadData['dealId'],
        'Model[Contractor]' => $clientId,
        'Model[Manager]' => $leadData['manager'],
    ];
    if (!empty($leadData['data'])) {
        $requestData['Model[Description]'] = $leadData['data'];
    }

    $response = $request->post('/BumsTradeApiV01/Deal/save.api', $requestData);
    $responseData = json_decode($response);

    if (!empty($responseData->status->code && $responseData->status->code == 'ok')) {
        // Сделка создана успешно
        echo($response);
    } else {
        echo('Error! ' . $response);
    }

}




