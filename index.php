<?php
date_default_timezone_set('Asia/Tashkent');
error_reporting(0);
function get_data_api($city){
    $connection = mysqli_connect('vae.h.filess.io', 'prototype2_bursttown', '2eda018e8f7b9bb9ce88d3f321a320122c5ff806', 'prototype2_bursttown', 3307) or die('Failed to connect to DB');
    $city = mysqli_real_escape_string($connection, $city);
    $url = 'https://api.openweathermap.org/data/2.5/weather?q='.$city.'&units=metric&appid=e1863b8f17319cb49b4c04ddbd09cb6d';
    $data = file_get_contents($url) or die('Invalid City Name');
    $weather = json_decode($data, true);
    $cityName = $weather["name"];
    $humidity = $weather["main"]["humidity"];
    $temp = $weather["main"]["temp"];
    $description = $weather["weather"][0]["description"];
    $date = date("Y-m-d H:i:s");
    $query = "INSERT INTO weather_data(data_created, cityname, humidity, temp, description)
              VALUES ('{$date}', '{$cityName}', '{$humidity}', '{$temp}', '{$description}')";
    $result = mysqli_query($connection, $query);
    if(!$result) {
        return false;
    }
    mysqli_close($connection);

    return true;
}
function data_exists($city)
{
    $connection = mysqli_connect('localhost','root',"",'cs0017', 3306) or die('Failed to connect to DB');
    $query_city_exists = "SELECT * FROM weather_data WHERE cityname = '{$city}' AND
    data_created >= DATE_SUB(NOW(), INTERVAL 3100 SECOND) ORDER BY data_created DESC limit 1";
    $result = mysqli_query($connection, $query_city_exists);
    return (bool)$result->num_rows;
}
function get_data_db($city){
    $connection = mysqli_connect('localhost','root',"",'cs0017', 3306) or die('Failed to connect to DB');
    $query_city_exists = "SELECT * FROM weather_data WHERE cityname = '{$city}' ORDER BY id DESC limit 1";
    $result = mysqli_query($connection, $query_city_exists);
    $d = [];
    while($row = mysqli_fetch_array($result)) {
        $d[] = $row;
    }
    return $d;
}

if (isset($_POST['submit_btn'])){
    $city = $_POST["city"];
    $source = 'Database';
    if (!data_exists($city)){
        get_data_api($city);
        $source = 'API';
    }
    $d = get_data_db($city);
    $result = $d[0];
    $result['source'] = $source;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prototype 2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page1 h-screen flex justify-end items-center ">
        <div>
            <div class="cart-img absolute right-10 top-3 p-4 bg-blue-200">
                <h1 style="font-size: 1.5vw;color: black"><?php
                        echo isset($result) ? $result['cityname']: null;
                    ?></h1>
            </div>
        </div>
        <div class="container1 flex flex-col shadow-lg p-6 max-w-md ml-auto ">
            <div class="cart-img">
                <h1 class="text-4xl p-3"><?php
                        echo isset($result) ? $result['description']:"City Name";
                    ?></h1>
            </div>
        <form method="post" class="searchbox">
            <label class="flex" for="search"></label>
                <input type="text" class="inp" id='search' name="city" value="<?php
                            echo isset($result) ? $result['cityname']:"";
                ?>">
            <div class="searchbox">
               <button class="btn" name="submit_btn" value="set">Search</button>
           </div>
        </form>
            <div class="w-img">
            </div>
            <div class="h-1/4 rounded-lg data">
                <span class="text-6xl"><d id="c-degree"> <?php
                        echo isset($result) ? $result['temp']:"0";
                        ?></d>&deg;C</span>
                <span class="text-3xl">Humidity: <d id="humidity"><?php
                        echo isset($result) ? $result['humidity']:"0";
                        ?></d> %</span>
                 <span class="text-xl text-blue-700 font-bold">From: <d id="humidity"><?php
                        echo isset($result) ? $result['source']:"loading...";
                        ?></d></span>
            </div>
        </div>
    </div>
    <script src="./script.js"></script>
</body>
</html>