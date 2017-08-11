<?php

namespace daifuku\parser;

use DOMDocument;
use DOMXPath;

class HihumiWikiParser implements Parser {
	
	public function getName() {
		return "HihumiWikiParser";
	}

	public function parseHTML($buf) {
		$data = [];

		$dom = new DOMDocument('1.0', 'UTF-8');
	    @$dom->loadHTML($buf, LIBXML_COMPACT | LIBXML_NOBLANKS);
	    $xpath = new DOMXPath($dom);

	    //idがinfoのオブジェクトを取得
	    $node = $xpath->query('id("info")');
	    if($node->length > 0) {
	        //trからtdのリストを取得
	        $list = $xpath->query('.//tbody/tr[4]/td', $node->item(0));

	        //tdを文字列へ
	        $data = [
	            "asset" => $xpath->evaluate('string()', $list->item(0)),
	            "people" => $xpath->evaluate('string()', $list->item(1)),
	            "salary" => $xpath->evaluate('string()', $list->item(2)),
	            "per_assets" => $xpath->evaluate('string()', $list->item(3)),
	            "upd" => $xpath->evaluate('string()', $list->item(4)),
	        ];

	        $data["online_players"] = array();
	        $rn = $xpath->query('//table[@id="players"]//tr[position() mod 2 = 0]');
	        foreach($rn as $k => $n) {
	            $p = $xpath->query("./td", $n);//note//0:nodata/1:name/2:is_admin/3:online_info or nodata/4:login_time/5:last_time

	            $is_online = mb_convert_encoding($xpath->evaluate('string()', $p->item(3)), "UTF-8", "UTF-8");
	            if(mb_strlen($is_online) <= 0) {//offline
	                break;
	            }

	            $data["online_players"][$k] = [
	                "name" => $xpath->evaluate('string()', $p->item(1)),
	            ];
	        }
	    }

	    return $data;
	}
}