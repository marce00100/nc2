<?php

namespace App\Http\Controllers;

use App\Libs\Lib;
use Illuminate\Http\Request;

class FuentesController extends Controller
{
    
    public function listar()
    {
        $fuentes = \DB::select("
                                SELECT f.id as id_fuente, f.fuente_nombre, f.pais, f.ciudad, 
                                s.id as id_seccion, s.url, s.seccion, s.tipo, 
                                s.permite_rastrear, s.prioridad, s.titulo, s.link, s.descripcion, s.lenguaje, s.vigente, s.numero_pasadas, 
                                       s.ultima_pub, s.ultima_pasada
                                FROM fuentes f, secciones s 
                                WHERE s.id_fuente = f.id
                                ORDER BY f.fuente_nombre, s.seccion  ");

        return response()->json([
            "estado"  => "success",
            "mensaje" => "Total " . count($fuentes),
            "fuentes" => $fuentes,
        ], 200);
    }

    public function obtenerFuentes()
    {
        $fuentes = \DB::select("SELECT * from fuentes");
        return response()->json([
            'data' => $fuentes,
            'estado' => 'success'
        ]);
    }

    public function getFuente($id)
    {
        $fuente = \DB::table('fuentes')->find($id);
        return response()->json([
            "estado"  => "success",
            "mensaje" => "Encontrado",
            "fuente"  => $fuente,
        ], 200);
    }

    public function guardar(Request $request)
    {
        $mensaje = "";
        $accion  = $request->id == null ? 'insert' : 'update';

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
                $fuente->id             = Lib::UUID();
                \DB::table('fuentes')->insert(get_object_vars($fuente));
                // $fuente->id             = \DB::table('fuentes')->insertGetId(get_object_vars($fuente));
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
            "fuente" => $fuente
        ]);
    }

    public function destroy($id)
    {
        $fuente = \DB::table('fuentes')->where('id',$id)->delete();
        return response()->json([
            "mensaje" => "Fuente eliminada",
        ], 201);
    }

    public function fuentesRastreo($rastreo)
    {
        $fuentes = \DB::select("SELECT * FROM fuentes WHERE permite_rastrear = '{$rastreo}' ORDER by ultima_pasada asc, prioridad asc");
        return response()->json([
                                    'data'=> $fuentes
                                ]);
    }
}
