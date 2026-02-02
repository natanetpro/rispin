<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');
        
        // Must be logged in
        if (!logged_in()) {
            return redirect()->to('/login');
        }
        
        if (empty($arguments)) {
            return;
        }

        $permission = $arguments[0];
        
        helper('permission'); // Load our custom helper
        
        if (!check_permission($permission)) {
            // log_message('error', 'ACCESS DENIED: User ' . user_id() . ' tried to access ' . $permission);
            
            // Return 403
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/html/error_403', [
                    'title'   => '403 Forbidden',
                    'message' => 'You do not have permission to access this page.'
                ]));
        }
        
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return;
    }
}
