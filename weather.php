<?php



header("Content-Type: application/json; Charset-UTF-8");
$qu = str_replace(" ", "+", $_GET['query'])."+날씨"; // Query string  ?query=Location_Name
$url = "https://m.search.naver.com/search.naver?sm=mtp_sug.psn&where=m&query=".$qu; //Set URL
 
try{
    $ch = curl_init();
    $header = array("User-Agent: Samsung A737: SAMSUNG-SGH-A737/UCGI3 SHP/VPP/R5 NetFront/3.4 SMM-MMS/1.2.0 profile/MIDP-2.0 configuration/CLDC-1.1 UP.Link/6.3.1.17.0"); //Samsung A737의 User-Agent
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //Header
    curl_setopt($ch, CURLOPT_URL, $url); //URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Return as String
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //Connection Timeout(Sec)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //Check SSL Verify
 
    $result = curl_exec($ch); //CURL execute
    curl_close($ch); //Close this CURL
    $result = explode('</section>', explode('<section class="sc csm cs_weather _cs_weather" data-dss-logarea="x7t">', $result)[1])[0]; //Element including weather info.
    $loc = explode('</h2>', explode('<h2 class="title">', $result)[1])[0]; //Searching location
    $cur_wea = explode('</span>', explode('<span class="blind">', explode('<div class="weather_main">', $result)[1])[1])[0]; //Current weather
    $temp = preg_replace('/<[^>]+>/', '', explode('</strong>', explode('</span>', explode('<div class="temperature_text">', $result)[1])[1])[0]); //Current degree point
    $feel_temp = preg_replace('/[가-힣]/', '', preg_replace('/<[^>]+>/', '', explode('</strong>', explode('<dd class="feeling_temperature">', $result)[1])[0]))."°"; //Effective degree point
    $up_temp = preg_replace('/<[^>]+>/', '', explode('</strong>', explode('<dd class="up_temperature">', $result)[1])[0]); //Today highist temperature
    $down_temp = preg_replace('/<[^>]+>/', '', explode('</strong>', explode('<dd class="down_temperature">', $result)[1])[0]); //Today lowist temperature
    $than_prev = explode('<br>', explode('<p class="summary">', $result)[1])[0]; //Comparison with prev_date
    $sign3 = explode('<li class="sign3">', $result); //Aero status
    $fine = $sign3[1]; //Fine dust element
    $fine_num = explode('</span>', explode('<span class="figure_result">', $fine)[1])[0]; //Fine dust with Number
    $fine_text = explode('</span>', explode('<span class="figure_text">', $fine)[1])[0]; //Fine dust with text
    $ultra_fine = $sign3[2]; //Ultra-Fine dust element
    $ultra_fine_num = explode('</span>', explode('<span class="figure_result">', $ultra_fine)[1])[0]; //Ultra-Fine dust with Number
    $ultra_fine_text = explode('</span>', explode('<span class="figure_text">', $ultra_fine)[1])[0]; //Ultra-Fine dust with text
    $hourly_weather = explode('<li class="_li">', explode('</ul>', explode('<ul>', explode('<div class="graph_inner _hourly_weather">', $result)[1])[1])[0]); //Hourly elements
    for($count = 0; $count < (sizeof($hourly_weather) - 1); $count++) {
        $time[$count] = explode('</dt>', explode('<dt class="time">', $hourly_weather[$count + 1])[1])[0]; //Time
        $weather[$count] = explode('</span>', explode('<span class="blind">', $hourly_weather[$count + 1])[1])[0]; //Weather by time
        $tempe[$count] = preg_replace("/<[^>]+>/", "", explode('</span>', explode('<span class="num">', $hourly_weather[$count + 1])[1])[0]); //Temperature by time
        $by_time[$time[$count]] = array("weather" => $weather[$count], "temperature" => $tempe[$count]); //Associative array about status by time
    }
    $result = array(
        "location" => $loc,
        "current" => array(
            "weather" => $cur_wea,
            "temperature" => $temp,
            "feelingTemperature" => $feel_temp,
            "comparisonWithPrev" => $than_prev,
            "fine" => $fine_num,
            "fineText" => $fine_text,
            "ultraFine" => $ultra_fine_num,
            "ultraFineText" => $ultra_fine_text
        ),
        "updown" => array(
            "upTemperature" => $up_temp,
            "downTemperature" => $down_temp
        ),
        "weatherByHours" => $by_time
    ); //Associative array for encode to JSON String
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); //Encode the associative array to JSON String for print

} catch ( Exception $e ) {
    echo $e->getMessage (); //ErrorMessage
}


?>


