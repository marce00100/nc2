<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\NodosModel as Nodos;
use App\Http\Models\ContenidosModel as Contenidos;
use App\Http\Models\NormalizadosModel as Normalizados;
use App\Libs\Lib;

//use App\Http\Controllers\Controller;
//use App\Http\Requests;

class NodosContenidosController extends Controller
{

    public function obtenerNodoContenidos($id)
    {
        $nodo = Nodos::find($id);
        $contenido = Contenidos::find($id);
        $normalizado = Normalizados::find($id);
        
        
        
        return response()->json([
                    "mensaje" => "Encontrado",
                    "nodo" => $nodo->toArray(),
                    "contenido" => $contenido->toArray(),
                    "normalizado" => $normalizado->toArray()
                        ], 200);
    }

}
