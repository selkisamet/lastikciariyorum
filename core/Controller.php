<?php

class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);

        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }

        require $viewFile;
    }

    protected function redirect($url)
    {
        if (strpos($url, 'http') !== 0) {
            $config = require __DIR__ . '/../config/config.php';
            $siteUrl = rtrim($config['site_url'], '/');
            $url = $siteUrl . '/' . ltrim($url, '/');
        }

        header("Location: $url");
        exit;
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getConfig($key = null)
    {
        $config = require __DIR__ . '/../config/config.php';

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? null;
    }
}
