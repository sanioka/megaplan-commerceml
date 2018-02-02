<?php

/**************************************************/
// Megaplan API example
// Пример создания сделки с использованием формата CommerceML
// Работает плохо, лучше использовать /example-api-integration.php
/**************************************************/

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


// Формируем CommerceML-документ
$commerceML = generateCommerceML();

// Создаем сделку в Мегаплане
$result = $request->post('/BumsTradeApiV01/Deal/createFromOnlineStore.api', array('CommerceInfo' => $commerceML));
echo($result);

function generateCommerceML()
{
    $commerceML = include('view/commerceML.php');
    return $commerceML;
}


