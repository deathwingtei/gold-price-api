<?php
    if(isset($_GET['date'])){
        $date = $_GET['date'];
    }else{
        $date = date("Y-m-d");
    }

    echo $date."<br>";

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    try {
        getgoldprice();
    } catch (ErrorException $e) {
        print_r(json_encode(array("return"=>0,"msg"=>"No Data"),JSON_UNESCAPED_UNICODE));
    }

    function getgoldprice(){
        // https://stackoverflow.com/questions/9813273/web-scraping-in-php source
        $found = 0;
        require 'simplehtmldom/simple_html_dom.php';
        // offset = page - perpage = 20
        $html = file_get_html('https://www.taradthong.com/webadmin/price_list.php?offset=0');
        // 0-99 index for list
        // 0 = date row 1 and 95 = date last(row 20)
        for ($i=0; $i < 100; $i+=5) { 
            $title = $html->find('div', $i);
            echo $title->plaintext."<br>\n";
        }

    }


?>