<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Auth as AuthConfig;
use App\Models\UserModel;
use Myth\Auth\Entities\User;

class AuthController extends BaseController
{
    /**
     * @var AuthConfig
     */
    protected $authConfig;

    /**
     * @var \Myth\Auth\Auth
     */
    protected $auth;

    public function __construct()
    {
        $this->authConfig = config('Auth');
        $this->auth = service('authentication');
    }

    //--------------------------------------------------------------------
    // Login Methods
    //--------------------------------------------------------------------

    public function login()
    {
        // If already logged in, redirect home
        if ($this->auth->check()) {
            return redirect()->to('/');
        }

        return view('pages/auth/login', [
            'config' => $this->authConfig,
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'login'    => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $login    = $this->request->getPost('login');
        $password = $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');

        // Try to log them in...
        if (! $this->auth->attempt(['username' => $login, 'password' => $password], $remember)) {
            return redirect()->back()->withInput()->with('error', $this->auth->error() ?? 'Invalid login credentials.');
        }

        // Redirect to the dashboard
        return redirect()->to('/dashboard')->with('success', 'Logged in successfully.');
    }

    public function logout()
    {
        // Logout via Myth:Auth
        $this->auth->logout();
        
        // Force destroy session to ensure complete logout
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Logged out successfully.');
    }

    //--------------------------------------------------------------------
    // Register Methods
    //--------------------------------------------------------------------

    public function register()
    {
        // check if already logged in.
        if ($this->auth->check()) {
            return redirect()->back();
        }

        // Check if registration is allowed
        if (! $this->authConfig->allowRegistration) {
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        return view('pages/auth/register', [
            'config' => $this->authConfig,
        ]);
    }

    public function attemptRegister()
    {
        // Check if registration is allowed
        if (! $this->authConfig->allowRegistration) {
            return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
        }

        $users = model(UserModel::class);

        // Validate basics first since some password rules rely on these fields
        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Validate passwords
        $rules = [
            'password'     => 'required|strong_password',
            'pass_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Save the user
        $allowedPostFields = array_merge(['password', 'email'], $this->authConfig->validFields, $this->authConfig->personalFields);
        $user = new User($this->request->getPost($allowedPostFields));

        $this->authConfig->requireActivation = null;
        $user->activate();

        if (! empty($this->authConfig->defaultUserGroup)) {
            $users = $users->withGroup($this->authConfig->defaultUserGroup);
        }

        if (! $users->save($user)) {
            return redirect()->back()->withInput()->with('error', $users->errors());
        }

        // Success!
        return redirect()->to(url_to('login'))->with('success', lang('Auth.registerSuccess'));
    }
}
