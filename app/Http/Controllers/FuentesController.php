<?php

namespace App\Http\Controllers;

use App\Http\Models\FuentesModel as Fuentes;
use App\Libs\Lib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as DB;

//use App\Http\Controllers\Controller;
//use App\Http\Requests;

class FuentesController extends Controller
{

    public function index()
    {
        $fuentes = DB::select("
            SELECT f.id, f.fuente_url, f.fuente_nombre, f.fuente_seccion,  f.fuente_tipo,
            f.pais, f.ciudad, f.permite_rastrear,
            f.titulo, f.link, f.descripcion, f.ultima_pub, f.vigente,
            f.numero_pasadas, f.ultima_pasada, f.lenguaje, f.prioridad,
            case when f.numero_pasadas = 0 then 0 else count(*) end as nodos
            FROM fuentes f left join nodos n on f.id = n.id_fuente
            GROUP BY f.id, f.fuente_url, f.fuente_nombre, f.fuente_seccion,  f.fuente_tipo,
            f.pais, f.ciudad, f.permite_rastrear,
            f.titulo, f.link, f.descripcion, f.ultima_pub, f.vigente,
            f.numero_pasadas, f.ultima_pasada, f.lenguaje, f.prioridad

            ORDER BY f.prioridad, f.fuente_nombre, f.fuente_seccion  ");

        return response()->json([
            "estado"  => "success",
            "mensaje" => "Total " . count($fuentes),
            "fuentes" => $fuentes,
        ], 200);
    }

    public function store(Request $request)
    {
        $mensaje                = "";
        $fuente                 = new Fuentes();
        $fuente->fuente_url     = trim($request->fuente_url);
        $fuente->fuente_nombre  = trim(strtoupper($request->fuente_nombre));
        $fuente->fuente_seccion = trim(strtoupper($request->fuente_seccion));

        $exist = collect(DB::select("SELECT * FROM fuentes WHERE (to_upper(fuente_nombre) = '{$fuente->fuente_nombre}'  AND to_upper(fuente_seccion) = '{$fuente->fuente_seccion}' ) OR (tu_upper(fuente_url) = '{strtoupper($fuente->fuente_url)}' )"))->first();

        if ($exist == null) {
            $id                       = Lib::UUID();
            $fuente->id               = $id;
            $fuente->fuente_tipo      = $request->fuente_tipo;
            $fuente->pais             = strtoupper($request->pais);
            $fuente->ciudad           = $request->ciudad;
            $fuente->permite_rastrear = $request->permite_rastrear;
            $fuente->prioridad        = $request->prioridad;
            $fuente->numero_pasadas   = 0;
            $fuente->creado_por       = 'user-0';
            $fuente->creado_en        = Lib::FechaHoraActual();
            $fuente->save();
            $mensaje = "Guardado";
        } else {

            if ($fuente->fuente_url == $exist->fuente_url) {
                $mensaje = "La URL ya existe.";
            }

            if ($fuente->fuente_nombre == $exist->fuente_nombre && $fuente->fuente_seccion == $exist->fuente_seccion) {
                $mensaje = "EL NOMBRE de la fuente y SECCION ya existen.";
            }

            if ($fuente->fuente_url == $exist->fuente_url && $fuente->fuente_nombre == $exist->fuente_nombre && $fuente->fuente_seccion == $exist->fuente_seccion) {
                $mensaje = "La FUENTE ya existe.";
            }

            $fuente = $exist;
        }

        return response()->json([
            "estado"  => $mensaje == "Guardado" ? "success" : "exist",
            "mensaje" => $mensaje,
            "fuente"  => $fuente,
        ], 201);
    }

    public function show($id)
    {
        $fuente = Fuentes::find($id);
        return response()->json([
            "estado"  => "success",
            "mensaje" => "Encontrado",
            "fuente"  => $fuente->toArray(),
        ], 200);
    }

    // Funcion para modificar los datos llenados por el usuario
    public function update(Request $request, $id)
    {
        $mensaje = "";
        $estado  = "success";
        $fuente                 = Fuentes::find($id);
        $fuente->fuente_url     = trim($request->fuente_url);
        $fuente->fuente_nombre  = trim(strtoupper($request->fuente_nombre));
        $fuente->fuente_seccion = trim(strtoupper($request->fuente_seccion));

        $repetidos = DB::select(DB::raw("SELECT * FROM fuentes WHERE id <>'$id' AND "
            . "( fuente_url = '$fuente->fuente_url' OR (fuente_nombre = '$fuente->fuente_nombre' AND fuente_seccion = '$fuente->fuente_seccion'  )) "));

        if (count($repetidos) == 0) {
            $fuente->fuente_tipo      = $request->fuente_tipo;
            $fuente->pais             = $request->pais;
            $fuente->ciudad           = $request->ciudad;
            $fuente->permite_rastrear = $request->permite_rastrear;
            $fuente->prioridad        = $request->prioridad;

            $fuente->modificado_por = 'user-0';
            $fuente->modificado_en  = Lib::FechaHoraActual();
            $fuente->save();
            $mensaje = "Fuente modificada";
        } else {
            $exist  = $repetidos[0];
            $estado = "exist";
            if ($fuente->fuente_url == $exist->fuente_url) {
                $mensaje = "La URL ya existe.";
            }

            if ($fuente->fuente_nombre == $exist->fuente_nombre && $fuente->fuente_seccion == $exist->fuente_seccion) {
                $mensaje = "EL NOMBRE de la fuente y SECCION ya existen.";
            }

            if ($fuente->fuente_url == $exist->fuente_url && $fuente->fuente_nombre == $exist->fuente_nombre && $fuente->fuente_seccion == $exist->fuente_seccion) {
                $mensaje = "La FUENTE ya existe.";
            }

            $fuente = $exist;
        }
        return response()->json([
            "estado"  => $estado,
            "mensaje" => $mensaje,
            "fuente"  => $fuente,
        ], 201);
    }

    /*
     * Funcion que elimina una fuente
     */

    public function destroy($id)
    {
        $fuente = Fuentes::find($id);
        $fuente->delete();
        return response()->json([
            "mensaje" => "Fuente eliminada",
        ], 201);
    }

    ////////////////// de prueba //////////////////////////////////////////////////////////////
    //<editor-fold defaultstate="collapsed" desc="codigo de prueba ">
    //    public function prueba1()
    //    {
    //        $nums = array();
    //
    //        for ($i = 0; $i <= 9; $i++)
    //        {
    //            $item = new \stdClass();
    //            $item->id = rand(100, 999);
    //            $nums[] = $item;
    //        }
    //        return response()->json([
    //                            'data' => $nums,
    //                        ], 200);
    //    }
    //
    //    public function prueba2($id)
    //    {
    //        $nodos = array();
    //        for ($i = 1; $i <= 6; $i++)
    //        {
    //            $item = new \stdClass();
    //            $item->nodo = $id * 1000 + $i;
    //            $nodos[] = $item;
    //        }
    //        return response()->json([
    //                            'fuente' => $id,
    //                            'nodos' => $nodos
    //                        ], 200);
    //    }
    //
    //    public function prueba3($nodo)
    //    {
    //
    //        $n1 = time();
    //        sleep(rand(1, 5));
    //        $n2 = time();
    //        $res = $n2 - $n1;
    //        return response()->json([
    //                            'estado' => 'success',
    //                            'mensaje' => 'procesado ' . $nodo . " en $res",
    //                            'nodo' => $nodo
    //                        ], 200);
    //    }
    //</editor-fold >
}
