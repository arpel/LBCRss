<?php
/**
 * Code minimalisme de génération de flux RSS pour Leboncoin.fr
 * @version 1.0
 */


$dirname = dirname(__FILE__);

require $dirname."/lib/feedgenerator/FeedGenerator.php";
require $dirname."/lib/lbc.php";

date_default_timezone_set("Europe/Paris");

if (empty($_GET["url"])) {
    require $dirname."/form.php";
    return;
}

try {
    $_GET["url"] = Lbc::formatUrl($_GET["url"]);
} catch (Exception $e) {
    echo "Cette adresse ne semble pas valide.";
    exit;
}

if (!empty($_GET["queryname"])) {
    $title = "LeBonCoin"." - ".$_GET["queryname"];
} else {
    $title = "LeBonCoin";
    $urlParams = parse_url($_GET["url"]);
    if (!empty($urlParams["query"])) {
        parse_str($urlParams["query"], $aQuery);
        if (!empty($aQuery["q"])) {
            $title .= " - ".$aQuery["q"];
        }
    }
}

$feeds = new FeedGenerator();
$feeds->setGenerator(new RSSGenerator);
$feeds->setTitle($title);
$feeds->setChannelLink(
    !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"?"https":"http".
    "://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]
);
$feeds->setLink("http://www.leboncoin.fr");
$feeds->setDescription("Flux RSS de la recherche : ".htmlspecialchars($_GET["url"]));

$content = file_get_contents($_GET["url"]);
$ads = Lbc_Parser::process($content, $_GET);

if (!empty($_GET["multipleURLs"])) 
{
    $additionnalURLs = array(explode("\n", $_GET["multipleURLs"]));

    if (trim($_GET["multipleURLs"])) {
            $additionnalURLs = array_map("trim", explode("\n", $_GET["multipleURLs"]));
    }

    foreach ($additionnalURLs AS $newURL) {
        $newcontent = file_get_contents($newURL);
        $newads = Lbc_Parser::process($newcontent, $_GET);
        if (count($newads)) {
            $ads = array_merge($ads, $newads);
        }
    }
}

// foreach ($ads AS $ad) {
//     $ad->pprint();
// }

if (count($ads)) {
    foreach ($ads AS $ad) {
        $item = new FeedItem(
            md5($ad->getId().$ad->getDate()),
            $ad->getTitle(),
            $ad->getLink(),
            require $dirname."/view.phtml"
        );
        $item->pubDate = gmdate("D, d M Y H:i:s O", $ad->getDate())." GMT";
        $feeds->addItem($item);
    }
}

$feeds->display();
