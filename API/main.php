<?php
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    date_default_timezone_set("America/Los_Angeles");
    if (isset($_GET['callback'])) {
        echo $_GET['callback'] . "(";
    }
    function make_cap($x) {
        if ($x >= 1e9) {
            return number_format($x / 1e9, 2) . " Billion";
        }
        if ($x >= 1e6) {
            return number_format($x / 1e6, 2) . " Million";
        }
        return number_format($x);
    }
    
    if (isset($_GET['mode'])) {
        if ($_GET['mode'] === "quote") {
            if (isset($_GET['symbol'])) {
                $the_url = "http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=" . urlencode($_GET['symbol']);
                $json = file_get_contents($the_url);
                $result = json_decode($json, true);
                $arr = array();
                if (!isset($result['Message']) && stristr($result['Status'], "failure") === FALSE) {
                    if ($result['Name'] == null) {
                        $arr['Symbol'] = urlencode($_GET['symbol']);
                        $arr['Name']  = $arr['Last'] = $arr['Change'] = $arr['ChangePercent']  = $arr['Time'] = $arr['Cap'] = $arr['Volume'] = $arr['ChangeYTD'] = $arr['YTDPercent'] = $arr['High'] = $arr['Low'] = $arr['Open'] = "API error";
                        $arr['ChangeClass'] = $arr['YTDClass'] = "flat";
                    } else {
                        $arr['Name'] = $result['Name'];
                        $arr['Symbol'] = $result['Symbol'];
                        $arr['Last'] =  number_format($result['LastPrice'], 2);
                        $arr['Change'] = number_format($result['Change'], 2);
                        $arr['ChangePercent'] = number_format($result['ChangePercent'], 2) .'%';
                        if ($arr['Change'] > 0) {
                            $arr['ChangeClass'] = "increase";
                        } else if ($arr['Change'] < 0) {
                            $arr['ChangeClass'] = "decrease";
                        } else {
                            $arr['ChangeClass'] = "flat";
                        }
                        $format = 'd F Y, h:i:s A';
                        if (isset($_GET['timeformat'])) {
                            $format = rawurldecode($_GET['timeformat']);
                        }
                        $arr['Time'] = date($format, strtotime($result['Timestamp']));
                        $arr['Cap'] = make_cap($result['MarketCap']);
                        $arr['Volume'] = number_format($result['Volume']);
                        $arr['ChangeYTD'] = number_format($result['ChangeYTD'], 2);
                        $arr['YTDPercent'] = number_format($result['ChangePercentYTD'], 2) . "%";
                        if ($result['ChangePercentYTD'] > 0) {
                            $arr['YTDClass'] = "increase";
                        } else if ($result['ChangePercentYTD'] < 0) {
                            $arr['YTDClass'] = "decrease";
                        } else {
                            $arr['YTDClass'] = "flat";
                        }
                        $arr['High'] = number_format($result['High'], 2);
                        $arr['Low'] = number_format($result['Low'], 2);
                        $arr['Open'] = number_format($result['Open'], 2);
                    }
                }
                echo json_encode($arr);
            }
        } else if ($_GET['mode'] === "lookup") {
            if (isset($_GET['q'])) {
                $the_url = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/json?input=" . urlencode($_GET['q']);
                $json = file_get_contents($the_url);
                $result = json_decode($json, true);
                echo json_encode($result);
            }
        } else if ($_GET['mode'] == "news") {
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode('1E4Fucya+5x9xae1HmKdbigM1kYekgETCADyLWqe2m4:1E4Fucya+5x9xae1HmKdbigM1kYekgETCADyLWqe2m4')
            )
));
            $url = 'https://api.datamarket.azure.com/Bing/Search/v1/News?Query=%27' . $_GET['symbol'] . '%27&$format=json';
            //echo $url;
            $json = file_get_contents($url, false, $context);
            $result = json_decode($json, true);
            foreach ($result['d']['results'] as &$e) {
                $e['Date'] = date('d M Y, H:i:s', strtotime($e['Date']));
            }
            echo json_encode($result);
            //echo "\n";
            //var_dump($result);
        } else if ($_GET['mode'] == "interactive") {
            $param = $_GET['parameters'];
            echo file_get_contents('http://dev.markitondemand.com/MODApis/Api/v2/InteractiveChart/json?parameters=' . $_GET['parameters']);
        }
    }
    if (isset($_GET['callback'])) {
        echo ")";
    }
?>
