<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParametrosController extends Controller
{

    public function getValorParametro(Request $obj)
    {
        $valor = ParametrosController::obtenerValor($obj->dominio, $obj->codigo);
        return response()->json([
            "estado" => "success",
            "valor"  => $valor,
        ]);
    }

    public function putValorParametro(Request $obj)
    {
        ParametrosController::modificarValor($obj->dominio, $obj->codigo, $obj->valor);
        return response()->json([
            "estado" => "success",
            "valor"  => $obj->valor,
        ]);
    }

    public function obtenerDominioAll(Request $obj)
    {       
        $objetos = \DB::table('parametros')->where('dominio', $obj->dominio)->get();
        return response()->json([
            'estado' => 'succes',
            'data'   => $objetos,
        ]);
    }

    public static function obtenerValor($dominio, $codigo)
    {
        $param = \DB::table('parametros')->where('dominio', $dominio)->where('codigo', $codigo)->first();
        return $param->valor;
    }

    public static function modificarValor($dominio, $codigo, $valor)
    {
        \DB::table('parametros')->where('dominio', $dominio)->where('codigo', $codigo)->update(['valor' => $valor]);
    }

}
