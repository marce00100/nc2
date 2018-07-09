<?php

namespace App\Http\Controllers;

use App\Libs\Lib;
use SimplePie;
use Sunra\PhpSimple\HtmlDomParser;
use App\Http\Controllers\CrawlingExtController as crwlExt;

class CrawlingController extends Controller
{

    /**================================================================
    | API: funcion para realizar el crawling de las fuentes validas
     */
    public function crawlerRun($tasks = 1)
    {
        set_time_limit(0);
        $timeM     = ParametrosController::obtenerValor('crawling', 'time_m');// * 60;        
        $ciclo      = 0;
        $timeIni   = time();
        $timeIniT  = time();
        $timeTrans = $timeM;
        $fuentes = crwlExt::fuentesValidas();
        return response()->json(['f'=>$fuentes]);

        while (crwlExt::estadoRunning())
        {
            $fuentes = crwlExt::fuentesValidas();
            if($timeTrans >= $timeM)
            {
                $ciclo++;
                ParametrosController::modificarValor('crawl', 'cicles', $ciclo);
                $timeIni = time();
                foreach ($fuentes as $fuente)
                {
                    if (crwlExt::estadoRunning() == false)
                        break;
                    
                    try {
                        // TODO actualizar los vistos en nodos , sincronizar los ciclo para que sea con tareas paralelas
                        $this->crawlFuenteAndNodos($fuente);                      
                    } catch (\Exception $e) {
                        crwlExt::insertaError($e, $fuente, null, '' );
                    }
                }                
            }

            sleep(2);
            $timeTrans = time() - $timeIni;
        }

        return response()->json([
            'ciclo'             => $ciclo,
            'timeIniT'          => $timeIniT,
            'timeIni'           => $timeIni,
            'timeTrans'         => $timeTrans,
            'timeTransT'        => time() - $timeIniT,

        ]);
    }

    /*
    | Realiza el rastrillaje de la fuente RSS y sus nodos, por lo tanto                      //
    | actualiza la tabla de Fuentes y Realiza las inserciones
    | de cada uno de los items en la Tabla nodos
     */

    public function crawlFuenteAndNodos($fuente)
    {
        /* xxxxxxxxxxx TODO quitar $contNodos xxxxxxxxxxx */
        $contNodos = 0;
        $tipoFuente  = strtoupper(trim($fuente->fuente_tipo));
        if ($tipoFuente == 'RSS')
        {
            $feed       = crwlExt::configSimplePie($fuente->fuente_url);
            $tituloFeed = $feed->get_title();
            if ($tituloFeed === null || $tituloFeed === '') //verifica si existe
            {
                $fuente->vigente = false;
            }
            else
            {
                $itemsNodos = $feed->get_items();
                foreach ($itemsNodos as $itemNodo)
                {
                    /*xxxx TODO quitar  xxxxxxxxxxxxxxxxxxxxxx*/
                    if($contNodos < 2) {  
                        /* xxxxxxxxxxx */
                        try {
                            $existNodoBD = \DB::table('nodos')->where('link', '=', $itemNodo->get_link())->first(); //verifica si ya existe
                            if ($existNodoBD == null)
                            {
                                $nodo = crwlExt::insertarNodo($itemNodo, $fuente->id);
                                $this->crawlNodoContenido($nodo);
                            }
                            else if ($existNodoBD != null && $existNodoBD->procesado == 0)
                            {
                                $this->crawlNodoContenido($existNodoBD);
                            }
                        }
                        catch(\Exception $e){
                            crwlExt::insertaError($e, $fuente, $itemNodo, '' );
                        }
                    /* xxxxxxxxxxx TODO quitar xxxxxxxxxxxxxxxxxxxxxx */
                    } 
                    $contNodos++; /* xxxxxxxxxxx */
                }
            }
            crwlExt::actualizarDatosFuente($fuente, $feed);
        }
        elseif ($tipoFuente == 'TWITTER')
        {
            $twitts = json_decode($this->obtieneJsonTwitter($fuenteBD->fuente_url, 10));
            return ['tw' => $twitts];
            $titulo = $twitts[0]->user->name;
            if ($titulo == null || $titulo == '') //verifica si existe
            {
                $fuenteBD->vigente = '0';
            }
            else
            {
                $this->actualizaDatosFuenteTwitter($fuenteBD, $twitts);
                foreach ($twitts as $item)
                {
                    $titulo  = $item->text;
                    $link    = count($item->entities->urls) > 0 ? $item->entities->urls[0]->url : '';
                    $item_bd = Nodos::where('titulo', '=', $titulo)->where('link', '=', $link)->get();
                    if (count($item_bd) == 0)
                    {
                        $nodo = new Nodos();
                        $this->actualizaNodoTwitter($nodo, $item, $fuenteBD);
                        $nodo->save();
                        // if ($contador < 1)
                        // {
                        $nodosReturn[] = $this->creaSalidaNodo($nodo);
                        $contador++;
                        // }
                    }
                    else
                    {
                        $nodo = $item_bd[0];
                        if ($nodo->procesado != 1)
                        {
                            $nodosReturn[] = $this->creaSalidaNodo($nodo);
                        }

                    }
                }
            }
        }

        //******************* descomentar ************************
        \DB::table('fuentes')->where('id', $fuente->id)->update(get_object_vars($fuente));
        //******************* ************************
    }

    /**-----------------------------------------------------------------------------------------------------
    |Esta funcion realiza el rastreo o crawl del contenido de cada item que se envia mediante el Id del nodo
    | que ya esta almacenado en la BD en la tabla nodos, luego rastrea esa url y la almacena
    | en la tabla contenidos, luego realiza las conversiones de texto para almacenar
    | cada normalizacion en la tabla Normalizados
     */
    public function crawlNodoContenido($nodo)
    {
        // $nodo        = Nodos::find($id);
        $contenido   = new \stdClass;
        $normalizado = new \stdClass;
        $estado      = 'txt_sin_html';
        $error      = '';

        if ($nodo->link != '')
        {
            $dom = HtmlDomParser::file_get_html($nodo->link, false, null, 0, -1, true, true, DEFAULT_TARGET_CHARSET, true, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
                //                $dom = HtmlDomParser::file_get_html($nodo->link);
                //                    $elems = $dom->find('div');
            $contenido->id_nodo          = $nodo->id;
            $contenido->contenido        = (string) $dom->find('body', 0);
            $contenido->creado_en        = Lib::FechaHoraActual();
            $normalizado->id_nodo        = $nodo->id;
            $normalizado->texto_sin_html = Lib::sinHtmlCaracteresEspeciales($dom->plaintext);

                //                $body = $dom->find('body', 0);
                //                $desdeBody = Lib::sinHtmlCaracteresEspeciales($body->plaintext);
                //                return response()->json([
                ////                            'contenidoCompleto' => (string)$dom,
                //                            'contenido' =>  $contenido->contenido,
                //                            'textoSInhtml' => $normalizado->texto_sin_html,
                //                            'desdeBody'=>$desdeBody
                //                ]);
            $stopwordsArray                   = ConfigController::stopwordsActivosArray();
            $estado      = 'txt_sin_stopwords';
            $normalizado->texto_sin_stopwords = Lib::quitaStopwords($stopwordsArray, $normalizado->texto_sin_html);
            $estado      = 'txt_lema';
                //se convierte en un vector de palabras para cada una sea lematizada
            $palabrasStemming = array();
            foreach (explode(' ', $normalizado->texto_sin_stopwords) as $palabra)
            {
                $palabrasStemming[] = \App\Libs\Stemm_es::stemm($palabra);
            }
            $normalizado->texto_lema = implode(' ', $palabrasStemming);
            $estado      = 'success';
            $normalizado->creado_en  = Lib::FechaHoraActual();
        }
        else
        {
            $contenido->id_nodo               = $nodo->id;
            $contenido->contenido             = '';
            $contenido->creado_en             = Lib::FechaHoraActual();
            $normalizado->id_nodo             = $id;
            $normalizado->texto_sin_html      = '';
            $normalizado->texto_sin_stopwords = '';
            $normalizado->texto_lema          = '';
            $normalizado->creado_en           = Lib::FechaHoraActual();
        }
            //******************* descomentar ************************
        \DB::table('contenidos')->insert(get_object_vars($contenido));
        \DB::table('normalizados')->insert(get_object_vars($normalizado));
            //        ********************************
        $nodo->procesado = 1;
        \DB::table('nodos')->where('id', $nodo->id)->update(get_object_vars($nodo));
        $estado = 'success';

        
    }















    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    private function obtieneJsonTwitter($screenName, $count)
    {
        $settings = array(
            'oauth_access_token'        => "52908821-N0JdXNiBthaiOPsfofe98XzfL8zE9INqxObXVaYGQ",
            'oauth_access_token_secret' => "58JZqRvCdcCNpdhLemyYvTBuzsP2OjQnxkLHc6wNw7Ajr",
            'consumer_key'              => "BFz5zJeOBSBWuE8wAdjb2WOxd",
            'consumer_secret'           => "uYt7qo1HUQaBUjrCF8rDQCw82xe94Fs1FUdUbS12KqdypKsXn0",
        );
        $twitter  = new \TwitterAPIExchange($settings);
        $url      = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $getfield = "?screen_name=$screenName&count=$count";
        //    $url = 'https://api.twitter.com/1.1/search/tweets.json'; //statuses/user_timeline.json';
        //    $getfield = '?q=eresCurioso&count=100'; //screen_name=eresCurioso&count=100';
        $requestMethod = 'GET';
        $json          = $twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();
        return $json;
    }

    private function actualizaDatosFuenteTwitter($fuente, $rows)
    {
        $fuente->titulo      = $rows[0]->user->name;
        $fuente->link        = $rows[0]->user->url;
        $fuente->descripcion = $rows[0]->user->description;
        $fuente->tipo        = 'twitter';
        $fuente->ultima_pub  = $rows[0]->created_at;
        $fuente->vigente     = true;
        $fuente->numero_pasadas++;
        $fuente->ultima_pasada = Lib::FechaHoraActual();
        $fuente->lenguaje      = $rows[0]->user->lang;
        return $fuente;
    }

    private function actualizaNodoTwitter($nodo, $item, $fuente)
    {
        $nodo->id          = Lib::UUID();
        $nodo->id_fuente   = $fuente->id;
        $nodo->titulo      = $item->text;
        $nodo->descripcion = $item->text;
        $nodo->link        = count($item->entities->urls) > 0 ? $item->entities->urls[0]->url : '';
        $nodo->autor       = $item->user->screen_name;
        $nodo->fecha_pub   = $item->created_at;
        $nodo->content     = $item->text;
        $nodo->categoria   = ''; //implode(';', $item->entities->hashtags);

        $nodo->creado_por = 'user-0';
        //                $nodo->modificado_por = $request->modificado_por;
        $nodo->creado_en     = Lib::FechaHoraActual();
        $nodo->modificado_en = Lib::FechaHoraActual();
    }

}
