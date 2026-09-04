<?php

namespace App\Controllers;

class Controller
{
    // Helper function to render a view
    protected function view($viewName, $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            // Start output buffering
            ob_start();
            require $viewFile;
            $content = ob_get_clean();
            
            // If the view requires a layout, we can wrap it here or handle it in the view itself
            // For now, let's assume auth views and the printable report card don't use the app layout.
            if (strpos($viewName, 'auth/') === 0 || $viewName === 'results/report_card' || $viewName === 'results/report_card_all') {
                echo $content;
            } else {
                // Wrap in layout
                require __DIR__ . '/../views/layouts/app.php';
            }
        } else {
            die("View $viewName not found.");
        }
    }

    // Helper function to return JSON response
    protected function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Helper function to redirect
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    // Auth helper
    protected function requireRole($role)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            $this->redirect('/login');
        }
    }
}
