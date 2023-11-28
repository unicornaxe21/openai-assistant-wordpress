<?php 

$intro_question_1 = 'intro_question_1';
$intro_question_2 = 'intro_question_2';
$intro_question_3 = 'intro_question_3';
$max_tokens = 150;
$openai_api_key = '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';
$assistant_id = 'asst_BT07fG92Kv5lgJkjQwTyN15q';
$instructions = "";

// Обработка запроса POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $inputText = $data['message'];

    $url = 'https://api.openai.com/v1/assistants/' . $assistant_id . '/messages';

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ];

    $postData = [
        'model' => "gpt-3.5-turbo-1106",
        'inputs' => $inputText,
        'max_tokens' => $max_tokens
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($postData)
    ));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    } else {
        echo $result;
    }

    curl_close($ch);
    exit;
}