<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function search(Request $request)
    {
        // Capturo los valores enviados por parametro
        $q = $request->input('q');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Obtengo el contenido del archivo TSV
        $file = file_get_contents(env('PUBLIC_PATH', base_path('storage/app/')).'cities_canada-usa.tsv'); 

        // Array que almacenara los registros que coincidan con la busqueda
        $cities['suggestions'] = []; 

        // Separo el contenido del fichero por cada salto de linea
        $splitContents = explode("\n", $file);

        // Flag utilizada para saltar la primera fila
        $isFirst = true;

        // Defino arreglo con los Fipscode para Canada
        $fipsCanada = [
            '01' => 'AB',
            '02' => 'BC',
            '03' => 'MB',
            '04' => 'NB',
            '05' => 'NL',
            '13' => 'NT',
            '07' => 'NS',
            '14' => 'NU',
            '08' => 'ON',
            '09' => 'PE',
            '10' => 'QC',
            '11' => 'SK',
            '12' => 'YT'
        ];

        foreach($splitContents as $line)
        {
            if($isFirst) {
                // Solo entra en la primera iteracion para saltar la fila de los nombres de las columnas
                $isFirst = false;
            }
            else {
                // Descarto el ultimo caracter de cada fila correspondiente a /r
                $line = substr($line, 0, -1);

                // Separo la fila por valores separados por la tabulación /t
                $city = explode("\t", $line);

                // Arreglo temporal utilizado para guardar los valores de cada coincidencia
                $city_tmp = [];

                // Valido si el nombre de la ciudad contiene el parametro 'q' (no sensible a mayusculas)
                if(isset($city[1]) && strpos(strtolower($city[1]), strtolower($q)) !== false) {
                    
                    // Defino el nombre del país y Fipscode
                    if($city[8] == 'US') {
                        $country = 'USA'; 
                        $fipscode = $city[10];
                    }
                    elseif ($city[8] == 'CA') {
                        $country = 'Canada';
                        $fipscode = $fipsCanada[$city[10]];
                    }

                    // Guardo el nombre, la latitud, la longitud en el arreglo temporal
                    $city_tmp['name'] = $city[1].', '.$fipscode.', '.$country;
                    $city_tmp['latitude'] = $city[4];
                    $city_tmp['longitude'] = $city[5];

                    // Si los parametros latitude y longitude son validos aplico formula de Haversine para calcular el Score, sino el Score sera 0
                    if(is_float(abs($latitude)) && is_float(abs($longitude))) {
                        $r = 6.371;
                        $lat1 = deg2rad($latitude);
                        $lat2 = deg2rad($city[4]);
                        $deltaLat = deg2rad($city[4]-$latitude);
                        $deltaLon = deg2rad($city[5]-$longitude);
                        $a = sin($deltaLat/2)*sin($deltaLat/2)+cos($lat1)*cos($lat2)*sin($deltaLon/2)*sin($deltaLon/2);
                        $c = 2*atan2(sqrt($a), sqrt(1-$a));
                        $d = $r*$c;
                        // Guardo los resultados limitando a mostrar un solo decimal
                        $city_tmp['score'] = sprintf("%.1f", (1-bcdiv($d, 1, 1)));
                    }
                    else
                        $city_tmp['score'] = 0;

                    // Agrego el arreglo temporal al arreglo de sugerencias
                    array_push($cities['suggestions'], $city_tmp);
                }
            }
        }

        // Ordenamiento por Score
        usort($cities['suggestions'], function($a, $b) {
            return $a['score'] > $b['score'] ? -1 : 1; 
        });

        // Transformo Array a JSON y retorno
        $json_suggestions = json_encode($cities);
        return $json_suggestions;
    }
}