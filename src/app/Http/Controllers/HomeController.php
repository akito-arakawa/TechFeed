<?php

namespace App\Http\Controllers;
use App\Services\HomeService;


class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $section = request()->query('section', 'recommended');

        $validSections = ['recommended', 'popular', 'new'];
        if (!in_array($section, $validSections)) {
            $section = 'recommended';
        }

        return response()->json(
            $this->homeService->getHome($user, $section)
        );
    }
}
