<?php

namespace App\Http\Controllers;

use App\Libs\Lib;
use Illuminate\Http\Request;

class FuentesController extends Controller
{
    
    public function index()
    {
        $fuentes = \DB::select("
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

    public function show($id)
    {
        $fuente = \DB::table('fuentes')->find($id);
        return response()->json([
            "estado"  => "success",
            "mensaje" => "Encontrado",
            "fuente"  => $fuente,
        ], 200);
    }

    public function store(Request $request)
    {
        $mensaje = "";
        $accion  = $request->id == null ? 'insert' : 'update';
        $id      = $request->id;

        $fuente                   = new \stdClass();
        $fuente->fuente_url       = trim($request->fuente_url);
        $fuente->fuente_nombre    = trim(strtoupper($request->fuente_nombre));
        $fuente->fuente_seccion   = trim(strtoupper($request->fuente_seccion));
        $fuente->fuente_tipo      = $request->fuente_tipo;
        $fuente->pais             = trim(strtoupper($request->pais));
        $fuente->ciudad           = $request->ciudad;
        $fuente->permite_rastrear = $request->permite_rastrear;
        $fuente->prioridad        = $request->prioridad;

        $query = "SELECT * FROM fuentes
                    WHERE " . ($accion == 'update' ? " id <>'{$request->id}' AND " : "") .
                                    "(  (
                                        trim(upper(fuente_nombre)) = '" . $fuente->fuente_nombre . "'
                                        AND trim(upper(fuente_seccion)) = '" . $fuente->fuente_seccion . "' )
                                    OR (trim(upper(fuente_url)) = '" . trim(strtoupper($request->fuente_url)) . "')  )";
        $exist = collect(\DB::select($query))->first();

        if ($exist == null) {
            if ($accion == 'insert') {
                $fuente->numero_pasadas = 0;
                $fuente->creado_por     = 'user-0';
                $fuente->creado_en      = Lib::FechaHoraActual();
                $fuente->id             = \DB::table('fuentes')->insertGetId(get_object_vars($fuente));
                $mensaje                = "Guardado";
            }
            if ($accion == 'update') {
                $fuente->modificado_por = 'user-0';
                $fuente->modificado_en  = Lib::FechaHoraActual();
                \DB::table('fuentes')->where('id', $id)->update(get_object_vars($fuente));
                $mensaje = "Guardado";
            }
        } else {

            if (trim(strtoupper($request->fuente_url)) == trim(strtoupper($exist->fuente_url))) {
                $mensaje = "Existe otra FUENTE con la misma URL.";
            } else {
                $mensaje = "Existe otra fuente con el mismo NOMBRE y SECCION).";
            }

            $fuente = $exist;
        }

        return response()->json([
            "estado"  => $mensaje == "Guardado" ? "success" : "exist",
            "mensaje" => $mensaje,
            "fuente"  => $fuente,
            "id"      => $id,
        ], 201);
    }

    public function destroy($id)
    {
        $fuente = \DB::table('fuentes')->where('id',$id)->delete();
        return response()->json([
            "mensaje" => "Fuente eliminada",
        ], 201);
    }
}
