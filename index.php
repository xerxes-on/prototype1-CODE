<?php
date_default_timezone_set('Asia/Tashkent');
error_reporting(0);
function get_data_api($city){
    $connection = mysqli_connect('localhost','root',"",'cs0017', 3306) or die('Failed to connect to DB');
    $city = mysqli_real_escape_string($connection, $city);
    $url = 'https://api.openweathermap.org/data/2.5/weather?q='.$city.'&units=metric&appid=e1863b8f17319cb49b4c04ddbd09cb6d';
    $data = @file_get_contents($url);
    if ($data === false) {
        header('Location: index.php?error=invalid_input');
        exit('Invalid City Name');
    }

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
<body class="bg-gray-900">
    <div class="page1 flex sm:justify-end justify-center sm:items-center items-end dark:bg-gray-800 dark:border-gray-700 sm:p-10">
        <?php
            if(isset($_GET['error'])) {
                    echo '
                <div class="bg-red-100 z-10 border border-red-400 rounded-xl text-red-700 px-4 py-3 rounded left-3 top-3 absolute" id="alert">
                  <strong class="font-bold">Hey man!</strong>
                  <span class="block sm:inline">Can u watch out, bad input &#128548;</span>
                </div>
                ';
            }
        ?>
        <div>
            <div class="cart-img absolute right-10 top-3 p-4 bg-blue-200">
                <h1 style="font-size: 1.5vw;color: black"><?php
                        echo isset($result) ? $result['cityname']: null;
                    ?></h1>
            </div>
        </div>
        <div class="relative container1 justify-between flex flex-col p-6  sm:w-1/3 mb-5">
            <div class="cart-img">
                <h1 class="text-xl sm:text-3xl p-2"><?php
                        echo isset($result) ? $result['description']:"City Name";
                    ?></h1>
            </div>
            <form method="post" class="searchbox">
                <label class="flex" for="search"></label>
                    <input type="text" class="inp h-1/2" id='search' name="city" value="<?php
                                echo isset($result) ? $result['cityname']:"";
                    ?>">
                <div class="searchbox">
                   <button class="btn text-xm sm:text-xl p-1 font-bold sm:py-1 sm:px-3 rounded-full" name="submit_btn" value="set">Search</button>
               </div>
            </form>
            <div class="w-img sm:h-1/2 h-1/2 sm:m-6 ">
            </div>
            <div class="static h-1/4 rounded-lg data justify-between flex flex-col">
                <span class="static text-4xl sm:text-6xl"><d id="c-degree"> <?php
                        echo isset($result) ? $result['temp']:"0";
                        ?></d>&deg;C</span>
                <span class="static text-2xl sm:text-3xl">Humidity: <d id="humidity"><?php
                        echo isset($result) ? $result['humidity']:"0";
                        ?></d> %</span>
                 <span class="static text-xm sm:text-xl text-blue-700 font-bold">From: <d id="humidity"><?php
                        echo isset($result) ? $result['source']:"loading...";
                        ?></d></span>
            </div>
        </div>

    </div>
    <script src="./script.js"></script>
</body>
</html>