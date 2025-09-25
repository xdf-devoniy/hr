<?php
$botToken = "8308079078:AAGSMTeUuzvYk8Uk1tKlnSEWCwVDcKhZrmY";
$chatId = "-1002768595242";

function sendMessage($chatId, $message, $botToken) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    file_get_contents($url . '?' . http_build_query($data));
}

function sendDocument($chatId, $file, $caption, $botToken) {
    $url = "https://api.telegram.org/bot$botToken/sendDocument";
    $postFields = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'document' => new CURLFile($file['tmp_name'], $file['type'], $file['name'])
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_exec($ch);
    curl_close($ch);
}

$answers = [
    "1) Ism sharifingiz" => $_POST['full_name'],
    "2) Telefon raqamingiz" => $_POST['phone'],
    "3) 1-3-5 kunlari bo'sh vaqtlar" => $_POST['time135'],
    "4) 2-4-6 kunlari bo'sh vaqtlar" => $_POST['time246'],
    "5) Ikkinchi ishingiz haqida" => $_POST['second_job'],
    "6) Qaysi darajadagi o'quvchilar" => $_POST['levels'],
    "7) Rus tilida dars bera olasizmi?" => $_POST['russian'],
    "8) Biz bilan qancha muddat ishlamoqchisiz?" => $_POST['duration'],
    "9) Tajribangiz haqida" => $_POST['experience'],
    "10) Qancha o‘quvchiga dars bergansiz" => $_POST['students_taught'],
    "11) Qaysi bilim dargohlarini tugatgansiz" => $_POST['education'],
    "12) Tug‘ilgan kuningiz" => $_POST['birthdate'],
    "13) IELTS kursiga kimda tayyorlangansiz?" => $_POST['ielts_trainer'],
    "14) Ustun tomoningiz" => $_POST['strengths'],
    "15) O‘quvchi tezroq o‘rganishi uchun nima qilasiz?" => $_POST['strategy'],
    "16) 3 yillik maqsadlaringiz" => $_POST['goals'],
    "17) Otangiz haqida" => $_POST['father'],
    "18) Onangiz haqida" => $_POST['mother'],
    "19) Turmush o‘rtog‘ingiz haqida" => $_POST['partner'],
    "20) Boshqa oila a’zolaringiz" => $_POST['relatives']
];

$message = "<b>📝 Yangi nomzod!</b>\n\n";
foreach ($answers as $q => $a) {
    $message .= "<b>$q:</b>\n" . htmlspecialchars($a) . "\n\n";
}

sendMessage($chatId, $message, $botToken);

if (!empty($_FILES['ielts_cert']['tmp_name'])) {
    sendDocument($chatId, $_FILES['ielts_cert'], "IELTS sertifikati", $botToken);
}
if (!empty($_FILES['photo']['tmp_name'])) {
    sendDocument($chatId, $_FILES['photo'], "Nomzod rasmi", $botToken);
}

echo "✅ Arizangiz yuborildi!";
?>
