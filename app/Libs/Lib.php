<?php

namespace App\Libs;

class Lib
{

    public static function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    public static function implode_array_column_object($glue, $array, $column)
    {
        if (isset($array[0]->$column))
        {
            $implodeResult = $array[0]->$column;
            for ($i = 1; $i < count($array); $i++)
            {
                $implodeResult .= $glue . $array[$i]->$column;
            }
        }
        else
        {
            $implodeResult = '';
        }

        return $implodeResult;
    }

    //funcion desarrollada para generar UUIDs
    public static function UUID()
    {
        //si se desea usar la funcion com_create_guid borrar los espacios y la barra baja
        if (function_exists('com_create_guid    _'))
        {
            return com_create_guid();
        }
        else
        {
            list($usec, $sec) = explode(" ", microtime());
            $micro = (string) $sec . "-" . (string) ($usec + rand(0, 99) / 100000000);

            $charid = strtoupper(md5($micro));
            $uuid = substr($charid, 0, 8) . '-'
                    . substr($charid, 8, 4) . '-'
                    . substr($charid, 12, 4) . '-'
                    . substr($charid, 16, 4) . '-'
                    . substr($charid, 20, 12);
            return $uuid;
        }
    }

    public static function post($item)
    {
        $post = isset($_POST[$item]) ? $_POST[$item] : "";
        return $post;
    }

    public static function crearGuidVacio()
    {
        return "00000000-0000-0000-0000-000000000000";
    }

    public static function FechaHoraActual()
    {
        return \Carbon\Carbon::now('America/La_Paz');
    }

    // <editor-fold defaultstate="collapsed" desc="Funcion Normalizar quita caracteres raros">
    public static function sinHtmlCaracteresEspeciales($stringSinHtml)
    {
        $minusculas = mb_strtolower($stringSinHtml, 'UTF-8');
        $patron = array(
            '/á|Á|&aacute;/' => 'a',
            '/é|É|&eacute;/' => 'e',
            '/í|Í|&iacute;/' => 'i',
            '/ó|Ó|&oacute;/' => 'o',
            '/ú|Ú|&uacute;/' => 'u',
            '/à|â|ã|ä|å|æ|ª|À|Â|Ã|Ä|Å|Æ/' => 'a',
            '/è|ê|ë|ð|È|Ê|Ë|Ð/' => 'e',
            '/ì|î|ï|Ì|Î|Ï/' => 'i',
            '/ò|ô|õ|ö|ø|º|Ò|Ô|Õ|Ö|Ø/' => 'o',
            '/ù|û|ü|Ù|Û|Ü/' => 'u',
            '/ñ|Ñ|&ntil;/' => 'n',
            '/ç|Ç/' => 'c',
            '/ý|ÿ|Ý|Ÿ/' => 'y',
            '/þ|Þ/' => 't',
            '/ß/' => 's',
//            '/\n |\t |\r |&nbsp; |&ndash; |&ldquo; |&iquest; |&bull; |&hellip; |&rdquo; /' => ' ',
//            '/ \n| \t| \r| &nbsp;| &ndash;| &ldquo;| &iquest;| &bull;| &hellip;| &rdquo;/' => ' ',
            '/\n|\t|\r|&nbsp;|&ndash;|&ldquo;|&iquest;|&bull;|&hellip;|&ntilde;|&rdquo;/' => ' ',
            
            '/  /' => ' '
        );

        return preg_replace(array_keys($patron), array_values($patron), $minusculas);

//        return preg_replace(array('/á/','/é/','/í/','/ó/','/ú/', '/ñ/'), array('a','e','i','o','u'), $minusculas);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Funcion lista STOPS WORDS">

    public static function quitaStopwords($patronStopWords, $texto)
    {
        return preg_replace(array_keys($patronStopWords), array_values($patronStopWords), $texto);
    }
// </editor-fold>
    

}
