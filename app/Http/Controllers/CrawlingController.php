<?php

namespace App\Http\Controllers;

// use App\Http\Models\ContenidosModel as Contenido;
// use App\Http\Models\NodosModel as Nodo;
// use App\Http\Models\NormalizadosModel as Normalizado;
use App\Libs\Lib;
use SimplePie;
use Sunra\PhpSimple\HtmlDomParser;

class CrawlingController extends Controller
{

    /**================================================================
    | API: funcion para realizar el crawling de las fuentes validas
     */
    public function crawlerRun($tasks = 1)
    {
        $timeM     = ParametrosController::obtenerValor('crawl', 'time_m') * 60;
        $fuentes   = $this->fuentesValidas();
        $res       = array();
        $cont      = 0;
        $timeIni   = time();
        $timeIniT  = time();
        $timeTrans = $timeM;

        while ($this->estadoRunning())
        {
            if($timeTrans >= $timeM)
            {
                $timeIni = time();
                foreach ($fuentes as $fuente)
                {
                    if ($this->estadoRunning() == false)
                        break;
                    
                    try {
                        // $nodos = $this->crawlFuenteAndNodos($fuente);
                        $cont++;
                        
                    } catch (Exception $e) {
                        // $this->insertaError($e, $fuente, )
                    }

                }

            }
            sleep(2);
            $timeTrans = time() - $timeIni;

        }

        $v = [
            'uno' => 1,
            'dos' => 20];

        return response()->json([
            // 'ingresados Nuevos' => $nodos,
            'cont'              => $cont,
            'timeIniT'          => $timeIniT,
            'timeIni'           => $timeIni,
            'timeTrans'         => $timeTrans,
            'timeTransT'        => time() - $timeIniT,
            'i' => $v['dos'],

        ]);
    }

    /*
     * Realiza el rastrillaje de la fuente RSS y sus nodos, por lo tanto                      //
     * actualiza la tabla de Fuentes y Realiza las inserciones
     * de cada uno de los items en la Tabla nodos
     */

    public function crawlFuenteAndNodos($fuente)
    {
        $nodosReturn = array();
        $tipoFuente  = strtoupper(trim($fuente->fuente_tipo));
        if ($tipoFuente == 'RSS')
        {
            $feed       = $this->configSimplePie($fuente->fuente_url);
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
                    $existNodoBD = \DB::table('nodos')->where('link', '=', $itemNodo->get_link())->first(); //verifica si ya existe
                    if ($existNodoBD == null)
                    {
                        $nodo = $this->insertarNodo($itemNodo, $fuente->id);
                        $this->crawlNodoContenido($nodo);
                        $nodosReturn[] = $nodo->id;
                    }
                    else if ($existNodoBD != null && $existNodoBD->procesado == 0)
                    {
                        $this->crawlNodoContenido($existNodoBD);
                    }

                }
            }
            $this->actualizarDatosFuente($fuente, $feed);
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
        return $nodosReturn;
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
        $estado      = 'error';
        try
        {
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
                $normalizado->texto_sin_stopwords = Lib::quitaStopwords($stopwordsArray, $normalizado->texto_sin_html);

                //se convierte en un vector de palabras para cada una sea lematizada
                $palabrasStemming = array();
                foreach (explode(' ', $normalizado->texto_sin_stopwords) as $palabra)
                {
                    $palabrasStemming[] = \App\Libs\Stemm_es::stemm($palabra);
                }
                $normalizado->texto_lema = implode(' ', $palabrasStemming);
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
        catch (Exception $e)
        {

        }
        return $estado;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function fuentesValidas()
    {
        return collect(\DB::select("SELECT *
                                        FROM fuentes WHERE permite_rastrear ORDER BY prioridad, fuente_nombre "));
    }

    private function estadoRunning()
    {
        return ParametrosController::obtenerValor('crawl', 'running') == '1';
    }

    /*---------------------------------------------
    | Funcion que configura el SimplePie feeder
     */
    public function configSimplePie($url)
    {
        $feed = new SimplePie();
        $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
        //Basically adding headers to the request
        $context = stream_context_create($opts);
        $feed->set_raw_data(file_get_contents($url, false, $context));

        //        $feed->set_raw_data(file_get_contents($url)); //  set_feed_url($url);
        $feed->set_timeout(15);
        $feed->enable_cache(true);
        $feed->set_cache_location(storage_path() . '/cache');
        //TODO Modificar el tiempo en segundos que durara la cache
        $feed->set_cache_duration(60 * 15); // en segundos
        $feed->set_output_encoding('utf-8');
        $feed->init();
        $feed->handle_content_type();
        return $feed;
    }

    private function insertaError($e, $f, $n, $fase)
    {
        $error = [
            'id_fuente'     => $f->id,
            'fuente'        => $f->fuente_nombre,
            'seccion'       => $f->fuente_seccion,
            'fuente_url'    => $f->fuente_url,
            'error'         => $e,
            'id_nodo'       => $n->id,
            'nodo_url'      => $n->link,
            'creado_en'     => Lib::FechaHoraActual(),
            'fase'          => $fase
        ];
        \DB::table('errores')->insert($error);
    }

    private function actualizarDatosFuente($fuente, $feed)
    {
        $fuente->titulo      = $feed->get_title();
        $fuente->link        = $feed->get_link();
        $fuente->descripcion = $feed->get_description();
        $fuente->tipo        = $feed->get_type();
        $fuente->ultima_pub  = $feed->get_items()[0]->get_date();
        $fuente->vigente     = true;
        $fuente->numero_pasadas++;
        $fuente->ultima_pasada = Lib::FechaHoraActual();
        $fuente->lenguaje      = $feed->get_language();
        return $fuente;
    }

    private function insertarNodo($itemNodo, $id_fuente)
    {
        $nodo              = new \stdClass();
        $nodo->id_fuente   = $id_fuente;
        $nodo->titulo      = $itemNodo->get_title();
        $nodo->descripcion = $itemNodo->get_description();
        $nodo->link        = $itemNodo->get_link();
        $nodo->autor       = Lib::implode_array_column_object("; ", $itemNodo->get_authors(), "name");
        $nodo->fecha_pub   = $itemNodo->get_date();
        $nodo->content     = $itemNodo->get_content();
        $nodo->categoria   = Lib::implode_array_column_object("; ", $itemNodo->get_categories(), "term");
        $nodo->procesado   = 0;
        $nodo->creado_por  = 'user-0';
        $nodo->creado_en   = Lib::FechaHoraActual();

        $nodo->id = \DB::table('nodos')->insertGetId(get_object_vars($nodo));
        return $nodo;
    }

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
