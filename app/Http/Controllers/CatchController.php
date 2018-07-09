<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimplePie;
use Sunra\PhpSimple\HtmlDomParser;
use App\Libs\Lib;

class CatchController extends Controller
{


    public function showpage(Request $req)
    {
        $url = 'http://www.la-razon.com/nacional/Bolivia-Colombia-Santos-presidente-vitsita_0_2801719831.html';
        // $dom =  file_get_contents($url);
        $dom = HtmlDomParser::file_get_html($url, false, null, 0, -1, true, true, DEFAULT_TARGET_CHARSET, true, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
        //                $dom = HtmlDomParser::file_get_html($nodo->link);
        //                    $elems = $dom->find('div');   
                // $contenido->id_nodo = $nodo->id;
        $contenido = (string) $dom->find('body', 0);
        return response()->json([
            'data'=> $contenido,
            'url' => $url]);

    } 



//     public function validosCrawl()
//     {
//         $fuentesValidas = \DB::select("SELECT id, fuente_nombre, fuente_url, prioridad, modificado_en
//                                         FROM fuentes WHERE permite_rastrear ORDER BY prioridad, fuente_nombre ");

//         return response()->json([
//                             "mensaje" => "Encontrados " . count($fuentesValidas),
//                             "fuentes" => $fuentesValidas,
//                         ], 200);
//     }

//     //  // para obtener los datos principales  de la fuentes     
//     public function obtieneDatos()
//     {

//         $numArticulos = Nodos::count();
//         $procesados = Normalizados::count();
//         $topFuentes = \DB::select("SELECT f.fuente_nombre, count(*) as articulos 
//                                     from fuentes f, nodos n 
//                                     where f.id = n.id_fuente and n.procesado = 1
//                                      group by f.fuente_nombre
//                                      order by articulos desc
//                                      limit 10");
//         return response()->json([
//                             "estado" => "success",
//                             "total_articulos" => $numArticulos,
//                             "total_procesados" => $procesados,
//                             "top_fuentes" => $topFuentes
//                         ], 200);
//     }

//     public function crawlerRun()
//     {
//         $n = json_decode($this->validosCrawl(), true) ; 
//         // $numero = $n['mensaje'];
//         return response()->json([
//             'validos' => $n,
//             'mensaje' => 'ssssssss']);
//     }

//     /*
//      * Realiza el rastrillaje de la fuente RSS y sus nodos, por lo tanto                      //
//      * actualiza la tabla de Fuentes y Realiza las inserciones
//      * de cada uno de los items en la Tabla nodos
//      */

//     public function crawlFuente($id)
//     {
//         $fuenteBD = Fuentes::find($id);
//         $nodosReturn = array();
//         $contador = $contadorControl = 0;
//         switch (strtoupper($fuenteBD->fuente_tipo))
//         {
//             case 'RSS':
//                 {
//                     $feed = $this->configSimplePie($fuenteBD->fuente_url);
//                     $tituloFeed = $feed->get_title();
//                     if ($tituloFeed === null || $tituloFeed === '') //verifica si existe
//                     {
//                         $fuenteBD->vigente = '0';
//                     }
//                     else
//                     {
//                         $this->actualizarDatosFuente($fuenteBD, $feed);
//                         foreach ($feed->get_items() as $itemNodo)
//                         {
//                             /////// quitar contador*////////////////////********************************/////////////////////////////////////////////////////////
//                            // if ($contadorControl < 2)
//                            // {
//                                 $nodoLink = $itemNodo->get_link();
//                                 $exist_itemNodo_bd = Nodos::where('link', '=', $nodoLink)->get(); //verifica si ya existe
//                                 if (count($exist_itemNodo_bd) == 0)
//                                 {
//                                     $nodo = new Nodos();
//                                     $this->actualizarDatosNodo($nodo, $itemNodo, $fuenteBD);
//                                     $nodo->save();                                    
//                                     $nodosReturn[] = $this->creaSalidaNodo($nodo); //creaSalidaNodo para que la salida no ocupe tanto solo los datos necesarios
//                                    // $contador++;
//                                 }
//                                 else
//                                 {
//                                     $nodo = $exist_itemNodo_bd[0];
//                                     if ($nodo->procesado <> 1)
//                                     {
//                                         $nodosReturn[] = $this->creaSalidaNodo($nodo); //creaSalidaNodo para que la salida no ocupe tanto solo los datos necesarios
//                                        // $contador++;
//                                     }
//                                 }
//                            //     $contadorControl++;
//                            // }
//                             /////////////////////////////////////////////////////*******************************///////////////////////////////
//                         }
//                     }
//                 }
//                 break;
//             case 'TWITTER':
//                 {
//                     $twitts = json_decode($this->obtieneJsonTwitter($fuenteBD->fuente_url, 10));
//                     return ['tw' => $twitts];
//                     $titulo = $twitts[0]->user->name;
//                     if ($titulo == null || $titulo == '') //verifica si existe
//                     {
//                         $fuenteBD->vigente = '0';
//                     }
//                     else
//                     {
//                         $this->actualizaDatosFuenteTwitter($fuenteBD, $twitts);
//                         foreach ($twitts as $item)
//                         {
//                             $titulo = $item->text;
//                             $link = count($item->entities->urls) > 0 ? $item->entities->urls[0]->url : '';
//                             $item_bd = Nodos::where('titulo', '=', $titulo)->where('link', '=', $link)->get();
//                             if (count($item_bd) == 0)
//                             {
//                                 $nodo = new Nodos();
//                                 $this->actualizaNodoTwitter($nodo, $item, $fuenteBD);
//                                 $nodo->save();
//                    // if ($contador < 1)
//                    // {
//                                 $nodosReturn[] = $this->creaSalidaNodo($nodo);
//                                 $contador++;
//                    // }
//                             }
//                             else
//                             {
//                                 $nodo = $item_bd[0];
//                                 if ($nodo->procesado <> 1)
//                                     $nodosReturn[] = $this->creaSalidaNodo($nodo);
//                             }
//                         }
//                     }
//                 }
//                 break;
//         }
//         //******************* descomentar ************************
//         $fuenteBD->save();
//         //******************* ************************
//         return response()->json([
//                             "mensaje" => "Realizado",
//                             "fuente" => $fuenteBD,
//                             "nodos_items" => $nodosReturn,
//                             "articulos_encontrados" => count($nodosReturn) //$contador,
//                         ], 200);
//     }

//     /*
//      * Esta funcion realiza el rastreo o crawl del contenido de cada item que se envia mediante el Id del nodo
//      * que ya esta almacenado en la BD en la tabla nodos, luego rastrea esa url y la almacena
//      * en la tabla contenidos, luego realiza las conversiones de texto para almacenar 
//      * cada normalizacion en la tabla Normalizados
//      */

//     public function crawlNodoContenido($id)
//     {
//         //quitar/////////////***********************
// //        $tiempo = rand(3, 6);
// //        sleep($tiempo);
// //        return response()->json([
// //                            "mensaje" => "Realizado en " . $tiempo,
// ////                "nodo " => $nodo,
// //                            "id" => $id,
// //                        ], 200);
// ////***********************************************

//         $nodo = Nodos::find($id);
//         $contenido = new Contenidos;
//         $normalizado = new Normalizados;
//         $estado = 'error';
//         try
//         {
//             if ($nodo->link != '')
//             {
//                 $dom = HtmlDomParser::file_get_html($nodo->link, false, null, 0, -1, true, true, DEFAULT_TARGET_CHARSET, true, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
// //                $dom = HtmlDomParser::file_get_html($nodo->link);
// //                    $elems = $dom->find('div');   
//                 $contenido->id_nodo = $nodo->id;
//                 $contenido->contenido = (string) $dom->find('body', 0);
//                 $contenido->creado_en = Lib::FechaHoraActual();
//                 $normalizado->id_nodo = $id;
//                 $normalizado->texto_sin_html = Lib::sinHtmlCaracteresEspeciales($dom->plaintext);
                
                

// //                $body = $dom->find('body', 0);
// //                $desdeBody = Lib::sinHtmlCaracteresEspeciales($body->plaintext);
// //                return response()->json([
// ////                            'contenidoCompleto' => (string)$dom,
// //                            'contenido' =>  $contenido->contenido,
// //                            'textoSInhtml' => $normalizado->texto_sin_html, 
// //                            'desdeBody'=>$desdeBody
// //                ]);
                
//                 $stopwordsArray = ConfigController::stopwordsActivosArray();
//                 $normalizado->texto_sin_stopwords = Lib::quitaStopwords($stopwordsArray, $normalizado->texto_sin_html);

//                 //se convierte en un vector de palabras para cada una sea lematizada
//                 $palabrasStemming = array();
//                 foreach (explode(' ', $normalizado->texto_sin_stopwords) as $palabra)
//                 {
//                     $palabrasStemming[] = \App\Libs\Stemm_es::stemm($palabra);
//                 }
//                 $normalizado->texto_lema = implode(' ', $palabrasStemming);
//                 $normalizado->creado_en = Lib::FechaHoraActual();
//             }
//             else
//             {
//                 $contenido->id_nodo = $nodo->id;
//                 $contenido->contenido = '';
//                 $contenido->creado_en = Lib::FechaHoraActual();
//                 $normalizado->id_nodo = $id;
//                 $normalizado->texto_sin_html = '';
//                 $normalizado->texto_sin_stopwords = '';
//                 $normalizado->texto_lema = '';
//                 $normalizado->creado_en = Lib::FechaHoraActual();
//             }
// //******************* descomentar ************************
//             $contenido->save();
//             $normalizado->save();
// //        ********************************
//             $nodo->procesado = 1;
//             $nodo->save();
//             $estado = 'success';
//         }
//         catch (Exception $e)
//         {
            
//         }
//         return response()->json([
//                             "estado" => $estado,
//                             "mensaje" => "Realizado",
//                             "nodo " => $nodo,
//                             "id" => $id,
//                         ], 200);
//     }

// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//     /*
//      * Funcion que configura el SimplePie feeder
//      */

//     public function configSimplePie($url)
//     {
//         $feed = new SimplePie();
//         $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
// //Basically adding headers to the request
//         $context = stream_context_create($opts);
//         $feed->set_raw_data(file_get_contents($url, false, $context));

// //        $feed->set_raw_data(file_get_contents($url)); //  set_feed_url($url);
//         $feed->set_timeout(30);
//         $feed->enable_cache(true);
//         $feed->set_cache_location(storage_path() . '/cache');
//         //TODO Modificar el tiempo en segundos que durara la cache
//         $feed->set_cache_duration(60 * 15); // en segundos 
//         $feed->set_output_encoding('utf-8');
//         $feed->init();
//         $feed->handle_content_type();
//         return $feed;
//     }

//     private function actualizarDatosFuente($fuente, $feed)
//     {
//         $fuente->titulo = $feed->get_title();
//         $fuente->link = $feed->get_link();
//         $fuente->descripcion = $feed->get_description();
//         $fuente->tipo = $feed->get_type();
//         $fuente->ultima_pub = $feed->get_items()[0]->get_date();
//         $fuente->vigente = true;
//         $fuente->numero_pasadas ++;
//         $fuente->ultima_pasada = Lib::FechaHoraActual();
//         $fuente->lenguaje = $feed->get_language();
//         return $fuente;
//     }

//     /*
//      * Carga los datos a un nodo
//      */

//     private function actualizarDatosNodo($nodo, $itemNodo, $fuente)
//     {
//         $nodo->id = Lib::UUID();
//         $nodo->id_fuente = $fuente->id;
//         $nodo->titulo = $itemNodo->get_title();
//         $nodo->descripcion = $itemNodo->get_description();
//         $nodo->link = $itemNodo->get_link();
//         $nodo->autor = Lib::implode_array_column_object("; ", $itemNodo->get_authors(), "name");
//         $nodo->fecha_pub = $itemNodo->get_date();
//         $nodo->content = $itemNodo->get_content();
//         $nodo->categoria = Lib::implode_array_column_object("; ", $itemNodo->get_categories(), "term");
//         $nodo->creado_por = 'user-0';
//         $nodo->creado_en = Lib::FechaHoraActual();
//     }

//     private function obtieneJsonTwitter($screenName, $count)
//     {
//         $settings = array(
//                     'oauth_access_token' => "52908821-N0JdXNiBthaiOPsfofe98XzfL8zE9INqxObXVaYGQ",
//                     'oauth_access_token_secret' => "58JZqRvCdcCNpdhLemyYvTBuzsP2OjQnxkLHc6wNw7Ajr",
//                     'consumer_key' => "BFz5zJeOBSBWuE8wAdjb2WOxd",
//                     'consumer_secret' => "uYt7qo1HUQaBUjrCF8rDQCw82xe94Fs1FUdUbS12KqdypKsXn0"
//         );
//         $twitter = new \TwitterAPIExchange($settings);
//         $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
//         $getfield = "?screen_name=$screenName&count=$count";
//         //    $url = 'https://api.twitter.com/1.1/search/tweets.json'; //statuses/user_timeline.json';
//         //    $getfield = '?q=eresCurioso&count=100'; //screen_name=eresCurioso&count=100';
//         $requestMethod = 'GET';
//         $json = $twitter->setGetfield($getfield)
//                 ->buildOauth($url, $requestMethod)
//                 ->performRequest();
//         return $json;
//     }

//     private function actualizaDatosFuenteTwitter($fuente, $rows)
//     {
//         $fuente->titulo = $rows[0]->user->name;
//         $fuente->link = $rows[0]->user->url;
//         $fuente->descripcion = $rows[0]->user->description;
//         $fuente->tipo = 'twitter';
//         $fuente->ultima_pub = $rows[0]->created_at;
//         $fuente->vigente = true;
//         $fuente->numero_pasadas ++;
//         $fuente->ultima_pasada = Lib::FechaHoraActual();
//         $fuente->lenguaje = $rows[0]->user->lang;
//         return $fuente;
//     }

//     private function actualizaNodoTwitter($nodo, $item, $fuente)
//     {
//         $nodo->id = Lib::UUID();
//         $nodo->id_fuente = $fuente->id;
//         $nodo->titulo = $item->text;
//         $nodo->descripcion = $item->text;
//         $nodo->link = count($item->entities->urls) > 0 ? $item->entities->urls[0]->url : '';
//         $nodo->autor = $item->user->screen_name;
//         $nodo->fecha_pub = $item->created_at;
//         $nodo->content = $item->text;
//         $nodo->categoria = ''; //implode(';', $item->entities->hashtags);

//         $nodo->creado_por = 'user-0';
// //                $nodo->modificado_por = $request->modificado_por;
//         $nodo->creado_en = Lib::FechaHoraActual();
//         $nodo->modificado_en = Lib::FechaHoraActual();
//     }

//     //Crea un nodo solo con los datos mas importantes para la salida , y que no sea muy grande en meoria
//     private function creaSalidaNodo($nodo)
//     {
//         $nodoExt = new \stdClass();
//         $nodoExt->id = $nodo->id;
//         $nodoExt->titulo = $nodo->titulo;
//         $nodoExt->descripcion = $nodo->descripcion;
//         $nodoExt->link = $nodo->link;
//         $nodoExt->fecha_pub = $nodo->fecha_pub;

//         /*         * ********************comentar********************************************* */
// //                        $dom = HtmlDomParser::file_get_html($nodo->link);
// //                        $nodoExt->Articulo = (string) $dom->find('body', 0);
// //                        $nodoExt->ArticuloPlain = $dom->plaintext;
// //                        $nodoExt->ArticuloNormalizado = Lib::sinHtmlCaracteresEspeciales($nodoExt->ArticuloPlain);
// //                        $nodoExt->ArticuloSTOP_WORD = Lib::stopWords($nodoExt->ArticuloNormalizado);
// //                        $palabrasStemming = array();
// //                        foreach (explode(' ', $nodoExt->ArticuloSTOP_WORD) as $palabra)
// //                        {
// //                            $palabrasStemming[] = \App\Libs\Stemm_es::stemm($palabra);
// //                        }
// //                        $nodoExt->ArticuloSTEMMING = implode(' ', $palabrasStemming);
//         /*         * *************************************************************************** */
//         return $nodoExt;
//     }

}
