<?php

/**************************************************/
// Megaplan API example
// Пример создания сделки с использованием API
/**************************************************/

$params = [
    // В какой схеме сделки создавать лид, внутренний Id мегаплана
    'deals' => [
        'kupimdolg' => 1,
        'ddu' => 15,
        'bankrotstvo' => 16,
        'rastorjenie-braka' => 9,
    ],

    // Какого сотрудника назначать ответственным, внутренний Id мегаплана
//    'staff' => [
//        'ivanov' => 1000026,
//        'api-user' => 1000114,
//    ]
];

// Данные, которые приходят с леднига
$leadData = [
    'fio' => 'Петров Тестовый Лид',         // required
    'dealId' => $params['deals']['ddu'],    // required

    // по умолчанию, сделки и клиенты создаются от имени пользователя,
    // под которым произошла авторизация с API
//    'manager' => $params['staff']['api-user'],


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
    'Model[TypePerson]' => 'company',               // Тип клиента, компания
    'Model[CompanyName]' => $leadData['fio'],       // ФИО клиента
//    'Model[Responsibles]' => $leadData['manager'],  // Ответственный по клиенту
];
if (!empty($leadData['email'])) {
    $requestData['Model[Email]'] = $leadData['email']; // Email клиента
}
if (!empty($leadData['phone'])) {
    $requestData['Model[Phones]'] = [$leadData['phone']]; // Телефон клиента, может быть несколько
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
        'ProgramId' => $leadData['dealId'],         // ID схемы сделки
        'Model[Contractor]' => $clientId,           // ID клиента, для которого создаётся сделка
//        'Model[Manager]' => $leadData['manager'],   // Ответственный по сделке
    ];
    if (!empty($leadData['data'])) {
        $requestData['Model[Description]'] = $leadData['data']; // Дополнительная информация по лиду
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





