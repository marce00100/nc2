<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\StopwordsModel as Stopwords;

//use App\Http\Controllers\Controller;
//use App\Http\Requests;

class ConfigController extends Controller
{

    public function listarStopwords()
    {
        $Stopwords = Stopwords::orderBy('palabra', 'ASC')->get();
        return response()->json([
                    "mensaje" => "Encontrados " . $Stopwords->count(),
                    "stopwords" => $Stopwords->toArray(),
                        ], 200);
    }

    public function obtenerStopword($id)
    {
        $palabra = Stopwords::find($id);
        return response()->json([
                    "mensaje" => "Encontrado",
                    "stopword" => $palabra->toArray(),
                        ], 200);
    }

    public function insertarStopword(Request $request)
    {
        $stopword = new Stopwords();
        $stopword->palabra = $request->palabra;
        $stopword->categoria = $request->categoria;
        $stopword->activa = $request->activa;
        $stopword->save();

        return response()->json([
                    "mensaje" => "Creada",
                    "stopword" => $stopword->toArray(),
                        ], 201);
    }

    // Funcion para modificar los datos llenados por el usuario
    public function actualizarStopword(Request $request, $id)
    {
        $stopword = Stopwords::find($id);
        $stopword->palabra = $request->palabra;
        $stopword->categoria = $request->categoria;
        $stopword->activa = $request->activa;
        $stopword->save();
        return response()->json([
                    "mensaje" => "Stopword modificada",
                    "stopword" => $stopword->toArray(),
                        ], 201);
    }

    public function eliminarStopword($id)
    {
        $stopword = Stopwords::find($id);
        $stopword->delete();
        return response()->json([
                    "mensaje" => "palabra eliminada",
                        ], 201);
    }

    public static function stopwordsActivosArray()
    {
        $listaStopwords = Stopwords::where("activa", "=", true)->get();
        $stopwords = array();
        foreach ($listaStopwords as $stopword)
        {
            // se arma un patron del tipo   / palabra / => ' ' que sera remplazado en la funcion preg_replace
            $stopwords['/ ' . $stopword->palabra . ' /'] = ' ';
        }
        return ($stopwords);
    }

}
