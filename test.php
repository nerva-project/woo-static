<?php
/**
 * Created by IntelliJ IDEA.
 * User: Cedric
 * Date: 25/04/2018
 * Time: 15:45
 */

function getPriceFromTradeOgre(){
	$rawMakerts = file_get_contents('https://tradeogre.com/api/v1/markets');
	$markets = json_decode($rawMakerts, TRUE);
	if($markets !== null){
		foreach($markets as $market){
			$marketSymbol = array_keys($market)[0];
			if($marketSymbol === 'BTC-XNV'){
				$pricePerXnv = (float)$market[$marketSymbol]['price'];
				$volumeInBtc = (float)$market[$marketSymbol]['volume'];
				return array($pricePerXnv,$volumeInBtc/$pricePerXnv);
			}
		}
	}
	return array(null,null);
}


$totalVolume = 0;
$avgSatoshis = 0;

list($tradeOgrePrice,$tradeogreVolume) = getPriceFromTradeOgre();

if($tradeOgrePrice !== null){
	$totalVolume += $tradeogreVolume;
	$avgSatoshis += $tradeOgrePrice;
}

$xnvSatoshiPrice = 99999;
if($totalVolume > 0){
	$xnvSatoshiPrice = 0;
	if($tradeOgrePrice !== null) $xnvSatoshiPrice += $tradeOgrePrice * ($tradeogreVolume / $totalVolume);

	var_dump($xnvSatoshiPrice);
}