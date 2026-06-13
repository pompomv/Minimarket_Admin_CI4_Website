<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->helpers = ['form', 'url', 'uuid'];
        $this->session = \Config\Services::session();
    }

    /** Shortcut: redirect with flash error */
    protected function withError(string $url, string $msg): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($url)->with('error', $msg);
    }

    /** Shortcut: redirect with flash success */
    protected function withSuccess(string $url, string $msg): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($url)->with('success', $msg);
    }
}
