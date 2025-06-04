<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        app()->setLocale($lang);

        return Inertia::render('Welcome', [
            'translations' => [
                'terms' => [
                    'title' => __('terms.title'),
                    'content' => __('terms.content'),
                    'accept' => __('terms.accept'),
                    'start' => __('terms.start'),
                    'select_language' => __('terms.select_language'),
                ]
            ]
        ]);
    }
} 