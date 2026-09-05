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

    /**
     * Restores session from Bearer token if session is not already set.
     * This enables cross-domain auth (Vercel frontend -> Render backend).
     */
    protected function restoreSessionFromToken()
    {
        if (isset($_SESSION['user_id'])) {
            return; // already have a valid session
        }

        $secret = getenv('AUTH_SECRET') ?: 'iams_arms_secret_key_2025';
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        $token = '';
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
        } elseif (!empty($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        if (empty($token)) {
            return;
        }

        $decoded = base64_decode($token);
        $last_pipe = strrpos($decoded, '|');

        if ($last_pipe === false) return;

        $token_data = substr($decoded, 0, $last_pipe);
        $provided_hmac = substr($decoded, $last_pipe + 1);
        $expected_hmac = hash_hmac('sha256', $token_data, $secret);

        if (!hash_equals($expected_hmac, $provided_hmac)) {
            return;
        }

        $parts = explode('|', $token_data);
        if (count($parts) !== 4) return;

        // Restore session data from token
        $_SESSION['user_id'] = (int)$parts[0];
        $_SESSION['role'] = $parts[1];
        $_SESSION['teacher_id'] = $parts[2] !== '' ? (int)$parts[2] : null;
        $_SESSION['username'] = '';
    }

    // Auth helper for traditional PHP views
    protected function requireRole($role)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            $this->redirect('/login');
        }
    }

    // Auth helper for API endpoints (supports both session and Bearer token)
    protected function requireApiAuth()
    {
        $this->restoreSessionFromToken();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }
    }
}
