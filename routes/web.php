<?php
use App\Http\Models\StopwordsModel as Sw;
use App\Http\Controllers\ConfigController as ConfigController;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix'=> 'fuentes'], 
    function(){
        Route::get("/", 'FuentesController@index');
        Route::get("/{id}", 'FuentesController@show');
        Route::post("/", 'FuentesController@store');
        Route::delete("/{id}", 'FuentesController@destroy');
    });

Route::group(['prefix' => 'configuracion'],
    function(){
        Route::get("/sw", "ConfigController@listarStopwords");
        Route::get("/sw/{id}", "ConfigController@obtenerStopword");
        Route::post("/sw", "ConfigController@insertarStopword");
        Route::put("/sw/{id}", "ConfigController@actualizarStopword");
        Route::delete("/sw/{id}", "ConfigController@eliminarStopword");
    });


Route::get("crawl/validosCrawl", "CrawlController@validosCrawl"); // fuentes validas para realizar crawl,
Route::get("crawl/obtieneDatos", "CrawlController@obtieneDatos"); 
Route::get("crawl/fuente/{id}", "CrawlController@crawlFuente");
Route::get("crawl/nodoContenido/{id}", "CrawlController@crawlNodoContenido");

/////////////////// UPGRADE
Route::group(['prefix' => 'crawler'], function(){
    Route::get("/run", "CrawlingController@crawlerRun");
    Route::get("/stop", "CrawlingController@crawlerStop");
});



Route::get("parametros/getValor", 'ParametrosController@obtenerValorParametro');
Route::post("parametros/putValor", 'ParametrosController@modificarValorParametro');
Route::get("parametros/dominioAll", 'ParametrosController@obtenerDominioAll');


Route::get("explorar/obtenerfiltros",     'ExplorarController@obtenerfiltros');
Route::post("explorar/obtenerbusqueda",   'ExplorarController@obtenerBusqueda');
Route::get("explorar/obtenernoticia/{id}",'ExplorarController@obtenerNoticia');


Route::get("nodo/contenido/{id}", "NodosContenidosController@obtenerNodoContenidos");

/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
// funcion de prueba
Route::get('cnfgphp', function ()
{
   echo phpinfo();
});

Route::get("prueba2", function(){
    return view('super');
});
Route::get("suite", function(){
    return view('suite');
});

Route::get("prueba", function()
{
//
//    $frase = 'continuacion, quiroga           advierte choque        nivel, hermano, se,  estan jugando, hermano? , escucha dialogo, dando grado fraternidad antecedente existia comunicacion continua gobierno cooperativistas. choque,';
//    echo $frase . PHP_EOL;
//    echo "<br>";
//    $palabrasStemm = array();
//    foreach (explode(' ', $frase) as $palabra)
//    {
//        echo $palabra . " ";
//        $palabrasStemm[] = App\Libs\Stemm_es::stemm($palabra);
//    }
//    echo "<br>";
//    echo "<br>";
//    $frase2 = implode(' ', $palabrasStemm);
//    echo $frase2;
//    echo "<br><br>otros     ";
//
//    echo App\Libs\Stemm_es::stemm('concentración') . " " . App\Libs\Stemm_es::stemm('concentrado') . " ";
//    echo App\Libs\Stemm_es::stemm('lealtad') . " " . App\Libs\Stemm_es::stemm('concentrando') . " ";
//    echo App\Libs\Stemm_es::stemm('canción') . " " . App\Libs\Stemm_es::stemm('quirogueando') . " ";
//
//
//    echo "<br><br><br><br><br>--------------------------------------------------------------------<br><br><br><br>";
    $settings = array(
                'oauth_access_token' => "52908821-N0JdXNiBthaiOPsfofe98XzfL8zE9INqxObXVaYGQ",
                'oauth_access_token_secret' => "58JZqRvCdcCNpdhLemyYvTBuzsP2OjQnxkLHc6wNw7Ajr",
                'consumer_key' => "BFz5zJeOBSBWuE8wAdjb2WOxd",
                'consumer_secret' => "uYt7qo1HUQaBUjrCF8rDQCw82xe94Fs1FUdUbS12KqdypKsXn0"
    );

    $twitter = new TwitterAPIExchange($settings);

    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $getfield = '?screen_name=muyInteresante&count=100';
//    $url = 'https://api.twitter.com/1.1/search/tweets.json'; //statuses/user_timeline.json';
//    $getfield = '?q=eresCurioso&count=100'; //screen_name=eresCurioso&count=100';

    $requestMethod = 'GET';
    $json = $twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();
    return $json;
    return response()->json([
                        "twits" => $json,
                    ], 200);
});