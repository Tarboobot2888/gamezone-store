<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredServices = Service::with('game')
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $featuredAccounts = GameAccount::with('game')
            ->available()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $popularGames = Game::active()
            ->withCount('services')
            ->orderBy('services_count', 'desc')
            ->limit(12)
            ->get();

        return view('home', compact('featuredServices', 'featuredAccounts', 'popularGames'));
    }

    public function topup()
    {
        $games = Game::active()
            ->has('services')
            ->with(['services' => function ($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('topup', compact('games'));
    }

    public function accounts()
    {
        $games = Game::active()->orderBy('name')->get();
        $accounts = GameAccount::with('game')
            ->available()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('accounts', compact('games', 'accounts'));
    }

    public function accountDetail(GameAccount $account)
    {
        $account->load('game');
        $relatedAccounts = GameAccount::where('game_id', $account->game_id)
            ->where('id', '!=', $account->id)
            ->available()
            ->limit(4)
            ->get();

        return view('account-detail', compact('account', 'relatedAccounts'));
    }

    public function faq()
    {
        return view('faq');
    }

    public function contact()
    {
        return view('contact');
    }
}
