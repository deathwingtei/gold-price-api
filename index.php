<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
    header("HTTP/1.1 200 OK");

    if(isset($_GET['date'])){
        $date = $_GET['date'];
    }else{
        $date = date("Y-m-d");
    }


    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    try {
        $date1=date_create(date("Y-m-d"));
        $date2=date_create($date);
        $date_diff=date_diff($date1,$date2);

        $date_diff_mark = $date_diff->format('%R');
        $date_diff_count = $date_diff->format('%a');
        $date_week = date('l', strtotime($date));
        if($date_week=="Sunday"){
            $date = date("Y-m-d",strtotime("-1 days",strtotime($date)));
        }
        // echo $date;

        if($date_diff_mark=="+"&& $date_diff_count>0){
            header("HTTP/1.1 400");
            $data_return = array(
                "success"=>0,
                "date"=>"",
                "price"=>0,
                "msg"=>"Date must lower or equal current date"
            );
            print_r(json_encode($data_return,JSON_UNESCAPED_UNICODE));
            exit;
        }

        // about 1 page has 3 days
        $offset = 0;
        $found = 0;

        $devind_date = 0;
        
        if($date_diff_count>365){
            $devind_date = ceil(($date_diff_count)/10);
        }else if($date_diff_count>30){
            $devind_date = ceil(($date_diff_count)/(20));
        }

        // echo $devind_date."  ///  ".$date_diff_count;
        for ($i=0; $i < 120; $i++) { 
            if($found==0){
                $offset = ((($devind_date))+$i) * 20;
                echo $offset."<br>";
                $found = getgoldprice($date,$offset);
            }
        }
        if($found==0){
            header("HTTP/1.1 500");
            $data_return = array(
                "success"=>0,
                "date"=>"",
                "price"=>0,
                "msg"=>"No Data"
            );
            print_r(json_encode($data_return,JSON_UNESCAPED_UNICODE));
            exit;
        }

    } catch (ErrorException $e) {
        header("HTTP/1.1 500");
        $data_return = array(
            "success"=>0,
            "date"=>"",
            "price"=>0,
            "msg"=>"No Data"
        );
        print_r(json_encode($data_return,JSON_UNESCAPED_UNICODE));
    }

    function short_month_th($month = 1){
        $month = intval($month);
        switch ($month) {
            case '1':
                return "ม.ค.";
            break;
            case '2':
                return "ก.พ.";
            break;
            case '3':
                return "มี.ค.";
            break;
            case '4':
                return "เม.ย.";
            break;
            case '5':
                return "พ.ค.";
            break;
            case '6':
                return "มิ.ย.";
            break;
            case '7':
                return "ก.ค.";
            break;
            case '8':
                return "ส.ค.";
            break;
            case '9':
                return "ก.ย.";
            break;
            case '10':
                return "ต.ค.";
            break;
            case '11':
                return "พ.ย.";
            break;
            case '12':
                return "ธ.ค.";
            break;
            default:
                return "ม.ค.";
            break;
        }
    }

    function getgoldprice($date,$offset){
        // https://stackoverflow.com/questions/9813273/web-scraping-in-php source
        $found = 0;
        include_once 'simplehtmldom/simple_html_dom.php';
        $ex_date = explode("-",$date);
        $found_month = short_month_th($ex_date[1]);
        $found_date = $ex_date[2]." ".$found_month." ".$ex_date[0]." 09";
        // offset = page - perpage = 20
        $html = file_get_html('https://www.taradthong.com/webadmin/price_list.php?offset='.$offset);
        // 0-99 index for list
        // 0 = date row 1 and 95 = date last(row 20)
        for ($i=0; $i < 100; $i+=5) { 
            $date_for_find = $html->find('div', $i);
            $pos = strpos($date_for_find->plaintext, $found_date);
            if($pos!==false){
                $found = 1;
                $price_for_find = $html->find('div', ($i+1));
                $price_show = explode(" - ",$price_for_find->plaintext);
                $data_return = array(
                    "success"=>0,
                    "date"=>$date_for_find->plaintext,
                    "price"=>$price_show[0],
                    "msg"=>""
                );
                print_r(json_encode($data_return,JSON_UNESCAPED_UNICODE));
                exit;
            }
            // 
        }
        return $found;
    }


?>