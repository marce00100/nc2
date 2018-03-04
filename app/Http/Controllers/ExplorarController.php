<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libs\Lib;
use Illuminate\Support\Facades\DB as DB;

//use App\Http\Controllers\Controller;
//use App\Http\Requests;

class ExplorarController extends Controller
{

    public function obtenerfiltros()
    {
        $fuentes = collect( DB::select("
        SELECT f.id, f.fuente_url, f.fuente_nombre, f.fuente_seccion,  f.fuente_tipo, 
                f.pais, f.ciudad 
         FROM fuentes f 
         -- WHERE f.vigente      
         ORDER BY f.fuente_nombre, f.fuente_seccion  "));

        $paisCiudad = \DB::select("SELECT  pais || ' - ' || ciudad as pais_ciudad from fuentes 
                                    group by pais_ciudad
                                    order by pais_ciudad");


        return response()->json([
                            "fuentes" => $fuentes,
                            "paises_ciudades" =>$paisCiudad
                        ]);
    }

    public function obtenerBusqueda(Request $req)
    {   

        $page = $req->pagina;
        $limit = $req->limite;     
        $data = $dataContenido = [];
        $t1 = microtime(true);
        $queryCampos = "SELECT f.id as id_fuente, f.fuente_nombre, f.fuente_seccion, f.pais, f.ciudad,  f.vigente,
                        n.id as id_nodo, n.titulo as titulo_noticia, n.descripcion as descripcion_noticia, n.link, n.fecha_pub, 
                        substring(nr.texto_sin_html,1,400) || ' ...' as texto_sin_html_corto, 0001 as ou
                        FROM fuentes f, nodos n, normalizados nr
                        WHERE f.id = n.id_fuente AND n.id = nr.id_nodo  ";
     
        $condiciones = $condicionesDescripcion = $condicionesContenido = ""; 
        if($req->id_fuente <> null)
            $condiciones .= " AND f.id = '" . $req->id_fuente ."' " ;
        if($req->pais_ciudad <> null)
            $condiciones .= " AND f.pais || ' - ' || f.ciudad = '" . $req->pais_ciudad . "' ";

        if($req->texto_busqueda <> null)
        {
            $condiciones .= " AND n.titulo ILIKE '% " . $req->texto_busqueda . " %' "; // que este en titular, tendra ordenUnion ou = 1
            $condicionesDescripcion .= " AND n.descripcion ILIKE '% " . $req->texto_busqueda . " %' AND n.titulo NOT ILIKE '% " . $req->texto_busqueda . " %' "; // que este en descripcion pero que NO ESTE en titular, tendra ordenUnion ou = 2
        }
        
        if($req->texto_busqueda <> null && $req->busqueda_profunda)
            $condicionesContenido .= " AND  nr.texto_sin_html ILIKE '% " . $req->texto_busqueda . " %'  AND n.descripcion NOT ILIKE '% " . $req->texto_busqueda . " %' AND n.titulo NOT ILIKE '% " . $req->texto_busqueda . " %' "; // que este en contenido pero que NO ESTE en descripcion y que NO ESTE en titular, tendra ordenUnion ou = 3

        $rangobusqueda = " OFFSET ({$page} - 1) * {$limit} LIMIT {$limit} ";

        /*
        Arma el query resultante
         */
        $grandQuery = $queryCampos . $condiciones ;
        if($req->texto_busqueda <> null || $req->texto_busqueda <> null)
            $grandQuery.= ' UNION ' .  str_replace('0001', 2, $queryCampos) . $condicionesDescripcion;
        if($req->texto_busqueda <> null && $req->busqueda_profunda)
            $grandQuery .= ' UNION ' .  str_replace('0001', 3, $queryCampos) . $condicionesContenido;

        $grandQuery .= ' ORDER BY ou '; // ordenar por ou orden union para mantener prioridad

        $total_registros = collect(\DB::select("SELECT count(*) as cantidad_total FROM   ({$grandQuery}) AS grandquery "))->first();
        $data = \DB::select($grandQuery . $rangobusqueda);

        $t2 = microtime(true);
        return response()->json([
            'mensaje'=>'ok',
            'data'=> $data,
            'pagina' => $page,
            'total_registros' => $total_registros->cantidad_total,
            'query' => $grandQuery,
            'tiempo' => $t2 - $t1,
        ]);
    }

    public function obtenerNoticia($id_nodo)
    {
        $nodo = collect(\DB::select("SELECT f.id as id_fuente, f.fuente_nombre, f.fuente_seccion, f.pais, f.ciudad,  f.vigente,
                            n.id as id_nodo, n.titulo as titulo_noticia, n.descripcion as descripcion_noticia, n.link, n.autor,  n.fecha_pub, n.content, n.categoria,
                            nr.texto_sin_html
                            FROM fuentes f, nodos n, normalizados nr
                            WHERE f.id = n.id_fuente AND n.id = nr.id_nodo
                            AND n.id = '" . $id_nodo . "' "))->first();
        return response()->json([
            'mensaje'=>'ok',
            'data'=> $nodo
        ]);
    }

   

}