<?php

namespace App\Controllers;

use App\Controllers\AdminController;

class LogsController extends AdminController
{
    public function show(string $type, string $date = null)
    {
        $date = $date ?? date('Y-m-d');
        $logFile = LOGS . '/' . $type . '/' . $date . '.txt';

        if (!file_exists($logFile)) {
            $this->redirect('/admin123');
            return;
        }

        $content = file_get_contents($logFile);

        $this->render('pages/log', [
            'type' => $type,
            'date' => $date,
            'content' => $content,
            'title' => "Логи: {$type} - {$date}"
        ]);
    }

    public function download(string $type, string $date)
    {
        $logFile = LOGS . '/' . $type . '/' . $date . '.txt';

        if (!file_exists($logFile)) {
            $this->redirect('/admin123');
            return;
        }

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($logFile) . '"');
        readfile($logFile);
        exit;
    }
}