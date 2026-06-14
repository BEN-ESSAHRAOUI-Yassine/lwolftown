<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyPlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('session_token');

        if ($token) {
            $player = Player::where('session_token', $token)->first();
            $request->merge(['_player' => $player]);
        }

        return $next($request);
    }
}
