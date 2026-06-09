<?php

namespace App\Controllers;

class AdminPanelController extends AdminController
{

    public function index()
    {

        //получаем логи
        $pathLogs = LOGS.'/';
        $dir = opendir($pathLogs);
        $arrDirs = [];

        while($file = readdir($dir)) {
            if (is_dir($pathLogs.$file) && $file != '.' && $file != '..') {
                $arrDirs[] = $file;
            }

        }
        $res = [];
        foreach ($arrDirs as $arrDir) {
            $f = scandir($pathLogs.$arrDir);

            foreach ($f as $file){
                if(preg_match('/\.(txt)/', $file)){ // Выводим только .png
                    $res[$arrDir][] = $file;
                }
            }
            if(isset($res[$arrDir])) {
                arsort($res[$arrDir]);
                $res[$arrDir] = array_slice($res[$arrDir], 0, 3);
            }

        }


        $this->render('pages/home', [
            'meta_title' => 'Админ панель',
            'filesLogs' => $res,
        ]);
    }
}