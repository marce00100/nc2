<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\ParametrosModel as Parametro;

//use App\Http\Controllers\Controller;
//use App\Http\Requests;

class ParametrosController extends Controller
{

    public function obtenerValorParametro(Request $obj)
    {
        $dominio = $obj->dominio;
//        $parametro = $obj->parametro;
        $codigo = $obj->codigo;
        $valor = null;

        if ($codigo != null)
        {
            $params = Parametro::where('dominio', $dominio)->where('codigo', $codigo)->get();

            if (count($params) > 0)
            {
                $valor = $params[0]->valor;
            }
        }

        return response()->json([
                            "estado" => "success",
                            "valor" => $valor,
                        ], 200);
    }

    public function modificarValorParametro(Request $obj)
    {
        $parametro = Parametro::where('dominio', $obj->dominio)->where('codigo', $obj->codigo)->first();
        $parametro->valor = $obj->valor;
        $parametro->save();

        return response()->json([
                            "estado" => "success",
        ]);
    }

}
