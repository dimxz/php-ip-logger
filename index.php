<?php
function get_ip_address()
{
    if (
        !empty($_SERVER["HTTP_CLIENT_IP"]) &&
        validate_ip($_SERVER["HTTP_CLIENT_IP"])
    ) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        if (strpos($_SERVER["HTTP_X_FORWARDED_FOR"], ",") !== false) {
            $iplist = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
            foreach ($iplist as $ip) {
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        } else {
            if (validate_ip($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                return $_SERVER["HTTP_X_FORWARDED_FOR"];
            }
        }
    }
    if (
        !empty($_SERVER["HTTP_X_FORWARDED"]) &&
        validate_ip($_SERVER["HTTP_X_FORWARDED"])
    ) {
        return $_SERVER["HTTP_X_FORWARDED"];
    }
    if (
        !empty($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"]) &&
        validate_ip($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])
    ) {
        return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
    }
    if (
        !empty($_SERVER["HTTP_FORWARDED_FOR"]) &&
        validate_ip($_SERVER["HTTP_FORWARDED_FOR"])
    ) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    }
    if (
        !empty($_SERVER["HTTP_FORWARDED"]) &&
        validate_ip($_SERVER["HTTP_FORWARDED"])
    ) {
        return $_SERVER["HTTP_FORWARDED"];
    }
    return $_SERVER["REMOTE_ADDR"];
}
function validate_ip($ip)
{
    if (strtolower($ip) === "unknown") {
        return false;
    }
    $ip = ip2long($ip);
    if ($ip !== false && $ip !== -1) {
        $ip = sprintf("%u", $ip);
        if ($ip >= 0 && $ip <= 50331647) {
            return false;
        }
        if ($ip >= 167772160 && $ip <= 184549375) {
            return false;
        }
        if ($ip >= 2130706432 && $ip <= 2147483647) {
            return false;
        }
        if ($ip >= 2851995648 && $ip <= 2852061183) {
            return false;
        }
        if ($ip >= 2886729728 && $ip <= 2887778303) {
            return false;
        }
        if ($ip >= 3221225984 && $ip <= 3221226239) {
            return false;
        }
        if ($ip >= 3232235520 && $ip <= 3232301055) {
            return false;
        }
        if ($ip >= 4294967040) {
            return false;
        }
    }
    return true;
}
$ip_address = get_ip_address();
$user_agent = $_SERVER["HTTP_USER_AGENT"];


// Konfigurasi databse mysql //
$mysqlHost = "sql200.infinityfree.com";
$mysqlUser = "if0_35857146";
$mysqlPassword = "DMS18888";
$mysqlDatabase = "if0_35857146_visitor_data";

$mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase);

// Cek koneksi database
if ($mysqli->connect_error) {
    die("Koneksi ke database gagal: " . $mysqli->connect_error);
}

// Mengambil data ip dengan API //
$ip_api_url = "http://ip-api.com/json/$ip_address";
$ip_api_ch = curl_init($ip_api_url);
curl_setopt($ip_api_ch, CURLOPT_RETURNTRANSFER, true);
$ip_api_response = curl_exec($ip_api_ch);
curl_close($ip_api_ch);
if ($ip_api_response === false) {
    die("Permintaan API gagal: " . curl_error($ip_api_ch));
}
// echo($ip_api_response);
$ip_api_data = json_decode($ip_api_response, true);

// Mengambil data device dengan API //
$e_user_agent = urlencode($user_agent);
// echo($e_user_agent);
$device_api_url = "https://api.apicagent.com/?ua=$e_user_agent";
$device_api_ch = curl_init($device_api_url);
curl_setopt($device_api_ch, CURLOPT_RETURNTRANSFER, true);
$device_api_response = curl_exec($device_api_ch);
curl_close($device_api_ch);
if ($device_api_response === false) {
    die("Permintaan API gagal: " . curl_error($device_api_ch));
}
//echo($device_api_response);


$device_api_data = json_decode($device_api_response, true);

// Mendapatkan total semua data yang ada di database
$total_query = "SELECT COUNT(*) as total_rows FROM visitor_data";
$total_result = $mysqli->query($total_query);
$row = $total_result->fetch_assoc();
$total_data = $row['total_rows'];


// Mendapatkan data yang akan disimpan
$website = $_SERVER['HTTP_HOST'];
$country = $ip_api_data["country"];
$city = $ip_api_data["city"];
$zip = $ip_api_data["zip"];
$latitude = $ip_api_data["lat"];
$longitude = $ip_api_data["lon"];
$browser = $device_api_data["client"]["name"];
$device_type = $device_api_data["device"]["type"];
$device_brand = $device_api_data["device"]["brand"];
$device_model = $device_api_data["device"]["model"];
$os = $device_api_data["os_family"];

// Mengecek apakah data ip sudah ada di database mysql //
$check_query = "SELECT id FROM visitor_data WHERE ip_address = '$ip_address' AND website = '$website' ";
$result_check = $mysqli->query($check_query);

// Menyimpan data ke MySQL dan mengirim data ke dmzApi dan database MySQL jika data belum ada
if ($result_check->num_rows == 0) {
    if ($user_agent == "FacebookBot"){
    }
    else {
        // Simpan data ke database MySQL
        $insert_query = "INSERT INTO visitor_data (website, ip_address, country, city, zip, latitude, longitude, os, browser, device_type, device_model, device_brand) VALUES ('$website','$ip_address', '$country', '$city', '$zip', '$latitude', '$longitude', '$os', '$browser', '$device_type', '$device_model', '$device_brand')";

        if ($mysqli->query($insert_query) === true) {
            
            }
        } else {
            //  echo "Error: " . $mysqli->error;
            echo "Error: ";
        }
    }
} else {
     echo " ";
}

?>
