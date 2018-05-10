<?php

namespace App;

class Functions {

    public static function date($date, $is_timestamp = false) {
        if($date === null) return null;
            
        try {
            setlocale(LC_ALL, 'pt_BR.utf-8', 'portuguese');
            date_default_timezone_set('America/Bahia');
    
            $date = $is_timestamp ? $date : strtotime($date);
    
            return [
                'formatado'   => strftime('%d de %B de %Y', $date),
                'formatado_com_hora' => strftime('%d de %B de %Y (%Hh e %Mmin)',$date),
                'timestamp'   => $date * 1000,
                'hora' => strftime('%Hh:%Mmin', $date),
                'amd' => strftime('%Y/%m/%d', $date),
                'dma' =>strftime('%d/%m/%Y', $date),
                'mes_ano' => strftime('%B de %Y',$date),
                'mes_ano_reduzido' => strftime('%b/%Y', $date),
                'mes_reduzido' => strftime('%b', $date)
            ];
        } catch (Exception $e) {
            return $date;
        }
    }

    public static function random($size=6) {
        return substr(str_shuffle(str_repeat(
                    $x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
                    ceil($size/strlen($x)) 
                )), 1, $size);
    }

    public static function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    public static function format($filename, $is_url = true) {
        $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $name = strtolower($name); //To Lowercase
        $name = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"), $name);
        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
        
        return $is_url ? $name . '.' . $extension : $name;
    }

    public static function move($midias, $final) {
        foreach($midias as $midia) {
            $dir = $final;
            $dir = str_replace("\\", "/", $dir);

            // Verifica se o diretório daquela ocorrência já existe, se não cria-o
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            //Se houverem arquivos com o mesmo nome, vai acrescendando incrementos numéricos na frente para compensar
            $tempname = '';
            $incrementor = '';
            do {
                $tempname = $dir.$incrementor.$midia['nome'];

                if($incrementor == '')
                    $incrementor = 0;

                $incrementor += 1;
            } while(file_exists($tempname));

            if (copy($midia['url'], $tempname))
                unlink($midia['url']); // Deleta os arquivos temporários

            $urls[] = $tempname;
        }

        return $urls;
    }


    public static function delete_from_folder($dirPath){
       
        if(!is_dir($dirPath)) return;

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/')
            $dirPath .= '/';

        $files = glob($dirPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                self::delete_from_folder($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dirPath);
    }

    public static function recurse_copy($src,$dst) { 
        if(!is_dir($src)) return;

        $dir = opendir($src); 
        @mkdir($dst); 

        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    Functions::recurse_copy($src . '/' . $file, $dst . '/' . $file); 
                } 
                else { 
                    if(file_exists($src . '/' . $file) && is_dir($dst)) {
                        copy($src . '/' . $file, $dst . '/' . $file); 
                    }
                } 
            } 
        } 
        
        closedir($dir); 
    }


    public static function notify($content, $player_ids = array(), $param = array(), $categories = array()) {
        $app_id = '5931c24f-eb21-4334-9f1f-9c741365f438';
        $rest_api_key = 'YWYwM2Q5YmItZDg3YS00Mjk4LThlYjQtYTQ2ZTY4MjhmMTMy';
        $color = 'FF0E76BC'; //ARGB

        $fields = [
            'app_id' => $app_id,
            'segments' => array('All'),
            'contents' => [
                'en' => $content
            ],
            'android_accent_color' => $color,
            'android_background_layout' => $color,
            'android_led_color' => $color,
            'include_player_ids' => $player_ids,
            'data' => $param,
            'tags' => $categories
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");        
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Basic '.$rest_api_key));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}