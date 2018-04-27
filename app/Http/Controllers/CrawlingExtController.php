<?php

namespace App\Http\Controllers;

use App\Libs\Lib;
use SimplePie;
use Sunra\PhpSimple\HtmlDomParser;

class CrawlingExtController extends Controller
{

    public static function fuentesValidas()
    {
        return collect(\DB::select("SELECT *
                                        FROM fuentes WHERE permite_rastrear ORDER BY prioridad, fuente_nombre "));
    }

    public static function estadoRunning()
    {
    	return ParametrosController::obtenerValor('crawl', 'running') == '1';
    }



    /*-----------------------------------------------------------------------------------
    | Funcion que configura los parametros del dominio CRAWL para que se inicie el crawling
    */
    public static function crawlIniciaParametros()
    {
        $idErrorActual = \DB::table('errores')->max('id');
        ParametrosController::modificarValor('crawl', 'running', 1);
        ParametrosController::modificarValor('crawl', 't_ini', Lib::FechaHoraActual());
        ParametrosController::modificarValor('crawl', 'cicles', 0);
        ParametrosController::modificarValor('crawl', 'error', $idErrorActual == null ? -1 : $idErrorActual);
    }


    /*-----------------------------------------------------------------------------------
    | Funcion que configura el SimplePie feeder
    */
    public static function configSimplePie($url)
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

    /* -------------------------------------------------------------------------------
    | Para actualizar los datos de la FUENTE despues de haber recorrido sus nodos
    */
    public static function actualizarDatosFuente($fuente, $feed)
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

    /*----------------------------------------------------------------------------------------
    | inserta un NODO
    */
    public static function insertarNodo($itemNodo, $id_fuente)
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
        $nodo->visto = false;

        $nodo->id = \DB::table('nodos')->insertGetId(get_object_vars($nodo));
        return $nodo;
    }



    /*----------------------------------------------------------------------------------------
    | inserta un ERROR
    */
    public static function insertaError($e, $f, $n, $fase)
    {
        $error = [
            'id_fuente'     => $f->id,
            'fuente'        => $f->fuente_nombre,
            'seccion'       => $f->fuente_seccion,
            'fuente_url'    => $f->fuente_url,
            'error'         => $e,
            // 'id_nodo'       => $n->id,
            // 'nodo_url'      => $n->link,
            'creado_en'     => Lib::FechaHoraActual(),
            'fase'          => $fase
        ];
        \DB::table('errores')->insert($error);
    }

}