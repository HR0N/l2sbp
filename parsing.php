<?php

include_once('env.php');
include_once('db.php');
include_once('tg-bot.php');
include_once('vendor/autoload.php');
use Telegram\Bot\Api;
use mydb\myDB;
use env\Env;

$tgBot = new TGBot(env::class);
$dbase = new myDB(env::class);
date_default_timezone_set('Europe/Kiev');
header('Content-type: text/html; charset=utf-8');
require_once __DIR__.'/libs/phpQuery-0.9.5.386-onefile/phpQuery-onefile.php';


/* description => parsing page
   return      => phpQuery document "$doc" */
function parse_order($url){
    $file = file_get_contents($url);
    return phpQuery::newDocument($file);
}
$epicBoss = ["Valakas"=>[], "Antharas"=>[], "Baium"=>[], "Queen Ant"=>[], "Beleth"=>[], "Orfen"=>[], "Core"=>[]];
$keyBoss  = ["Cabrio"=>[], "Hallate"=>[], "Kernon"=>[], "Golkonda"=>[], "Barakiel"=>[], "Commander Mos"=>[],
            "Hekaton"=>[], "Horus"=>[], "Shadith"=>[], "Tayr"=>[], "Brakki"=>[]];
$siege    = ["Giran"=>[], "Aden"=>[], "Dion"=>[], "Gludio"=>[], "Oren"=>[], "Goddard"=>[], "Schuttgart"=>[], "Rune"=>[],
            "Innadril"=>[]];


/* description => fetch parsed document to array (key bosses) x5
   return      => array  */
function fetch_key_bosses($doc){
    global $tgBot, $keyBoss;
    $i = 0;

    $content = $doc->find("#page_contents table a");

    foreach ($content as $item){
        $i++;
        if($i <= 50){
        foreach ($keyBoss as $key => $value){
            if(strpos($item->textContent, $key) && strlen($keyBoss[$key][0]) < 1){
                $exp = explode(": Убит босс ", $item->textContent);
                $keyBoss[$key] = [...$exp];
            }
        }
        }
    }
    return $keyBoss;
}


/* description => fetch parsed document to array (epic bosses) x5
   return      => array  */
function fetch_epic_bosses($doc){
    global $tgBot, $epicBoss;
    $i = 0;

    $content = $doc->find("#page_contents table a");

    foreach ($content as $item){
        $i++;
        if($i <= 50){
            foreach ($epicBoss as $key => $value){
                if(strpos($item->textContent, $key) && strlen($epicBoss[$key][0]) < 1){
                    $exp = explode(": Убит босс ", $item->textContent);
                    $epicBoss[$key] = [...$exp];
                }
            }
        }
    }
    return $epicBoss;
}


/* description => fetch parsed document to array (sieges) x5
   return      => array  */
function fetch_sieges($doc){
    global $tgBot, $siege;
    $i = 0;

    $content = $doc->find("#page_contents table a");

    foreach ($content as $item){
        $i++;
        if($i <= 50){
            if(strpos($item->textContent, "Начата осада")){
                foreach ($siege as $key => $value){
                    if(strpos($item->textContent, $key)){
                        $exp = explode(": Начата осада замка ", $item->textContent);
                        $siege[$key][0] = $exp[0];
                    }
                }
            }
            if(strpos($item->textContent, "Захвачен контроль")){
                foreach ($siege as $key => $value){
                    if(strpos($item->textContent, $key)){
                        $exp = explode(": Захвачен контроль над замком ", $item->textContent);
                        $siege[$key][1] = $exp[0];
                    }
                }
            }
            if(strpos($item->textContent, "Закончилась осада")){
                foreach ($siege as $key => $value){
                    if(strpos($item->textContent, $key)){
                        $exp = explode(": Закончилась осада замка ", $item->textContent);
                        $siege[$key][2] = $exp[0];
                    }
                }
            }
        }
    }
    return $siege;
}

/* description => march parsed data from x5 serve (epic bosses, key bosses, sieges)
   return      => JSON string */
function get_data_x5(){
    $url_epic_bosses = "https://asterios.tm/index.php?cmd=rss&serv=0&filter=epic";
    $url_key_bosses  = "https://asterios.tm/index.php?cmd=rss&serv=0&filter=keyboss";
    $url_siege       = "https://asterios.tm/index.php?cmd=rss&serv=0&filter=siege";

    $doc_epic_bosses = parse_order($url_epic_bosses);
    $doc_key_bosses = parse_order($url_key_bosses);
    $doc_siege = parse_order($url_siege);

    $data = [
        fetch_epic_bosses($doc_epic_bosses),
        fetch_key_bosses($doc_key_bosses),
        fetch_sieges($doc_siege)];

    return json_encode($data);
}


/* description => check total mount seconds of each 5 minutes
   return      => total seconds */
function total_sec_in_each_five_min(){
    $min = intval(mb_substr(date('i'), 1));
    if($min >= 5){$min-=5;}
    $sec = intval(date('s'));
    return $min * 60 + $sec;
}


/* description => start loop to parsing 3 times in min
   return      => total seconds */
function complex_x5(){
    global $tgBot, $dbase;
    $count = 0;
    while ($count < 3){
        sleep(15);
        $data = get_data_x5();

        echo '<pre>';
        echo var_dump($data);
        echo '</pre>';

        $dbase->set_rss_x5($data);
        $count++;
    }
}

complex_x5();