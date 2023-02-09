<?php
/*
This code is used to redeem a voucher code from the TrueMoney API. 
It first checks if the request is using HTTPS and if it is a POST request. 
It then checks the content type, either application/json or application/x-www-form-urlencoded,
and assigns the voucher code and mobile number from the data. 
It then checks if the voucher code matches a certain pattern before checking if both fields are filled out. 
If they are not, an error message is returned. 
A random string of 18 characters is generated for the User Agent header 
before making a cURL request to TrueMoney's API with the voucher code and mobile number.
The result of this cURL request is then echoed as JSON data.
*/

// Create By: TinnerKun ( SycerNetwork & Servwire )

if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") { header('Content-Type: application/json'); return exit(json_encode(['message' => 'Please use https']));}

if($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Content-Type: application/json');
    return exit(json_encode(['message' => 'Please use POST method']));
}

if($_SERVER['CONTENT_TYPE'] == 'application/json') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $voucher_code = @htmlspecialchars($data['voucher_code']);
    $mobile = @htmlspecialchars($data['mobile']);
}

if($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
    $voucher_code = @htmlspecialchars($_POST['voucher_code']);
    $mobile = @htmlspecialchars($_POST['mobile']);
}

if(!preg_match('/^[a-zA-Z0-9]{18}$/', $voucher_code)) { header('Content-Type: application/json'); return exit(json_encode(['message' => 'Voucher code is invalid | "voucher_code" : "18 characters"']));}

if(empty($voucher_code) || empty($mobile)) {
    header('Content-Type: application/json');
    
    if(empty($voucher_code) && empty($mobile)) return exit(json_encode(['message' => 'Voucher code and Mobile is required | "voucher_code" : "18 characters", "mobile" : "10 digits"']));

    if(empty($voucher_code)) return exit(json_encode(['message' => 'Voucher code is required | "voucher_code" : "18 characters"']));
    if(empty($mobile)) return exit(json_encode(['message' => 'Mobile is required | "mobile" : "10 digits"']));
}

function random_string($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://gift.truemoney.com/campaign/vouchers/$voucher_code/redeem");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{
    \"mobile\" : \"$mobile\",
    \"voucher_code\" : \"$voucher_code\"
}");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
$headers = array();
$headers[] = "User-Agent: Kuy - ".random_string(18);
$headers[] = "Content-Type: application/json";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

header('Content-Type: application/json');
echo $result;
?>
