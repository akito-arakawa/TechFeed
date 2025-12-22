<?php

namespace App\Http\Controllers;
use App\Services\HomeService;


class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function index(){
        $user = auth()->user();

        return response()->json(
            $this->homeService->getHome($user)
        );
    }
}
