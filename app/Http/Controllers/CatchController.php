<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;
use Sunra\PhpSimple\HtmlDomParser;
use App\Libs\Lib;

class CatchController extends Controller
{
// 

    public function showpage(Request $req)
    {
        $req->url = 'http://localhost/dashboard/';
        $url = $req->url;
        
        $dom = HtmlDomParser::file_get_html($url, false, null, 0, -1, true, true, DEFAULT_TARGET_CHARSET, true, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
        $contenido = $dom;
        // $contenido =  (string) $dom->find('body', 0);
        

        /*
        $lines_array=file($url);
        $contenido=implode('',$lines_array);
         */
        
        // $contenido =  file_get_contents($url);
        // return response($url);
        $contenido = str_replace("<!DOCTYPE html>", "", $contenido);
        $contenido = str_replace("<meta ", "<", $contenido);
        // $contenido = str_replace('src="','src="' . $req->base_fuente, $contenido);
        // $contenido = str_replace('src="','src="' . $req->base_fuente, $contenido);
       
        return response()->json([
            'data'=> mb_convert_encoding($contenido, 'UTF-8', 'UTF-8'),
            // 'arr'=> $lines_array,
            'url' => $url]);

    } 



}
