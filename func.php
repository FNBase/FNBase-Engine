<?php
// 기본 문자열 필터링 함수
function filt($arg, $opt){
    if($opt == 'htm'){ #html특문
        $val = htmlspecialchars($arg);
        $val = str_ireplace('"', '&quot;', $val);
        $val = str_ireplace("'", '&apos;', $val);
    }elseif($opt == 'abc'){ #영어, 숫자만
        $re = '/[^a-zA-Z0-9 ]+/m';
        $val = preg_replace($re, '', $arg);
    }elseif($opt == '123'){ #숫자만
        $re = '/[^0-9]+/m';
        $val = preg_replace($re, '', $arg);
    }elseif($opt == '영한'){ #영어, 숫자, 한글만
        $re = '/[^a-zA-Z0-9ㄱ-ㅎ가-힣_ ]+/m';
        $val = preg_replace($re, '', $arg);
    }elseif($opt == 'mail'){ #영어, 숫자, 한글만
        $re = '/[^a-zA-Z0-9@._-]+/m';
        $val = preg_replace($re, '', $arg);
    }elseif($opt == 'csv'){ #영어, 숫자, 한글, 쉼표(,)
        $re = '/[^a-zA-Z0-9ㄱ-ㅎ가-힣,_]+/m';
        $val = preg_replace($re, '', $arg);
    }else{
        $arg = str_replace("'", "\'", $arg);
        require_once 'editor/htmlpurifier/library/HTMLPurifier.auto.php';
        $purifier = new HTMLPurifier();
        $val = $purifier->purify($arg);
    }
    return $val;
}

function GenStr($length){
    $characters  = "0123456789";  
    $characters .= "abcdefghijklmnopqrstuvwxyz";  
    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      
    $string_generated = "";  
      
    $nmr_loops = $length;  
    while ($nmr_loops--)  
    {  
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];  
    }  
      
    return $string_generated;  
}

function get_gravatar( $email, $s = 56, $d = 'identicon', $r = 'pg', $img = false, $atts = array() ) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
        $url = '<img src="' . $url . '"';
        foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

function get_timeFlies($arg){
    $now_time = date('Y-m-d H:i:s');
    $time_check = strtotime($now_time) - strtotime($arg);
                        
    $total_time = $time_check;
    
    $days = floor($total_time/86400);
    $time = $total_time - ($days*86400);
    $hours = floor($time/3600);
    $time = $time - ($hours*3600);
    $min = floor($time/60);
    $sec = $time - ($min*60);
    
    if($days == 0 && $hours == 0 && $min == 0){
        $val = $sec.'초 전';
    }elseif($days == 0 && $hours == 0){
        $val = $min.'분 전';
    }elseif($days == 0){
        $val = $hours.'시간 전';
    }else{
        if($days >= 365){
            $years = floor($days / 365);
            $val = $years.'년 전';
        }elseif($days >= 21){
            $years = floor($days / 7);
            $val = $years.'주 전';
        }else{
            $val = $days.'일 전';
        }
    }
    return $val;
}

function textAlter($val, $isCon = 0){
    //공통 (금지어 등)
    /*$val = preg_replace('/(namu\.live|남라|나무라이브)/m', '"그 사이트"', $val);
    $val = preg_replace('/(\*ㅎㅎ|\*ㅇㅇ|\*ㄴㄴ|\*ㅅㅅ|\*ㅁㅁ)/m', '"그 파라과이인"', $val);
    $val = preg_replace('/(우만레|umanle)/m', '"그 파라과이 법인"', $val);*/

    //글 처리 (마크다운, 유튜브 등)
    if($isCon < 3){
        $val = preg_replace('/(>|^)((https|http):\/\/)([^< \n]+\/[^< \n]+\.(png|jpg|jpeg|gif|webp|svg))/mi', '$1<img style="max-width:100%" src="$3:$4">', $val);
        $val = preg_replace('/((https|http):\/\/)([^< \n]+\/[^< \n]+\.(mp4|mov|avi|ogg|ogv|flv|3gp|webm|mkv))/mi', '<video height="240" style="max-width:100%" src="$2:$3" preload="metadata" controls>', $val);
        $val = preg_replace('/https:\/\/(www\.|m\.){0,1}(youtube\.com\/watch\?v=|youtu\.be)([^< \n]+)/mi', '<iframe src="yt#escape#youtube.com/embed/$3" height="240" width="100%" allowfullscreen></iframe>', $val);
        if($isCon == 0){
            $val = preg_replace('/(https|http|ftp|mailto|tel):\/\/[a-zA-Z0-9-]*(\.|\@)[\w-]{2,63}[^< \n]*/m', '<a href="$0" target="_blank">$0</a>', $val);
        }elseif($isCon == 2){
            require_once 'php/Parsedown.php';
            $Parsedown = new Parsedown();
            $val = $Parsedown->text($val);
        }
        $val = str_ireplace('src="http:', 'src="http://', $val);
        $val = str_ireplace('src="https:', 'src="http://', $val);
        $val = str_ireplace('yt#escape#', 'https://', $val);
    }
    return $val;
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>