<?php

namespace App\Http\Controllers;

use App\Events\GameFinished;
use App\Events\GameShipLocation;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\GameEvent;
use App\Events\GamePlayerDestroyShip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Util\Test;

class GameController extends Controller
{
    public function getWinsUser($id){
        $user = User::find($id);
        $gameswins = Game::where('status', 'finished')
        ->where(function ($query) use ($user) {
            $query->where('winner_id', $user->id)
                ->where(function ($query) use ($user) {
                    $query->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                });
        })
        ->with(['player1', 'player2'])
        ->get();

        return response()->json($gameswins);
    }
    
    public function getLosesUser($id){
        $user = User::find($id);
        $gameslose = Game::where('status', 'finished')
        ->where(function ($query) use ($user) {
            $query->where('loser_id', $user->id)
                ->where(function ($query) use ($user) {
                    $query->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                });
        })
        ->with(['player1', 'player2'])
        ->get();

        return response()->json($gameslose);
    }
    public function getGamesInfo($id = null){
        $user = auth()->user();
        $games = Game::where('status', 'finished')
        ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)
                    ->orWhere('player2_id', $user->id);
        })
        ->with(['player1', 'player2'])
        ->get();

        return response()->json(['games'=> $games]);
    }


    public function getWinsCountUser($id = null){
        $user = auth()->user();
        $countwins = Game::where('status', 'finished')
        ->where(function ($query) use ($user) {
            $query->where('winner_id', $user->id)
                ->where(function ($query) use ($user) {
                    $query->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                });
        })
        ->count();

        return response()->json(['wins'=> $countwins]);
    }

    public function getLosesCountUser($id= null){
        $user = auth()->user();
        $countlose = Game::where('status', 'finished')
        ->where(function ($query) use ($user) {
            $query->where('loser_id', $user->id)
                ->where(function ($query) use ($user) {
                    $query->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                });
        })
        ->count();

        return response()->json(['loses'=>$countlose]);
    }
    
    public function createGame(){
        $player1_id = Auth::user()->id;

        $existingGame = Game::where('player1_id', $player1_id)
            ->whereIn('status', ['playing', 'queue'])
            ->first();

        if ($existingGame) {
            return response()->json([
                'msg' => 'You already have a game in progress or in queue. Please finish it before starting a new one.',
            ], 400);
        }

        $game = new Game();
        $game->player1_id = $player1_id;
        $game->save();

        return response()->json([
            'msg' => 'Game created successfully',
            'gameId' => $game->id,
        ]);
    }

    public function uncreateGame(Request $request){
        $validator = Validator::make($request->all(), [
            'gameId' => 'required|integer|exists:games,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $gameId = $request->gameId;

        $game = Game::find($gameId);
        if ($game->status != 'queue'){
            return response()->json([
                'msg' => 'Game is not in queue',
            ], 400);
        }
        $game->delete();

        return response()->json([
            'msg' => 'Game uncreated successfully',
            'game_id' => $game->id,
        ]);
    }

    public function cancelfindGame(Request $request){
        $player_id = Auth::user()->id;

        Cache::put($player_id, 'cancelled', 1);

        return response()->json([
            'msg' => 'Game search cancelled',
        ], 200);
    }

    public function findGame(Request $request){
        $player2_id = Auth::user()->id;

        $existingGameAsPlayerOne = Game::where('player1_id', $player2_id)
            ->whereIn('status', ['playing', 'queue'])
            ->first();

        $existingGameAsPlayerTwo = Game::where('player2_id', $player2_id)
            ->whereIn('status', ['playing', 'queue'])
            ->first();

        if ($existingGameAsPlayerTwo || $existingGameAsPlayerOne) {
            return response()->json([
                'msg' => 'You already have a game in progress or in queue. Please finish it before starting a new one.',
            ], 400);
        }

        $random_game = Game::where('status', 'queue')->first();
        if (!$random_game) {
            return response()->json([
                'game_found' => false,
                'msg' => 'No games in queue',
            ], 400);
        }

        $random_game->player2_id = $player2_id;
        $random_game->status = 'playing';
        $random_game->ShipScreenPlayer = $player2_id;
        $random_game->save();

        try {
            event(new GameEvent(['gameId' => $random_game->id, 'players' => [$random_game->player1_id, $random_game->player2_id]]));
            Log::info('El evento TestEvent se ha enviado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al emitir el evento TestEvent: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json([
            'game_found' => true,
            'msg' => 'Game started successfully',
            'players' => [$random_game->player1_id, $random_game->player2_id],
            'turn' => $random_game->player1_id,
            'gameId' => $random_game->id,
        ]);
    }

    public function endGame(Request $request){
        $validator = Validator::make($request->all(), [
            'losser_id' => 'required|integer|exists:users,id',
            'gameId' => 'required|integer|exists:games,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }


        $game_id = $request->gameId;
        $losser_id = $request->losser_id;

        $game = Game::find($game_id);
        
        $game->status = 'finished';

        if ($game->player1_id == $losser_id) {
            $game->winner_id = $game->player2_id;
        }
        else if ($game->player2_id == $losser_id) {
            $game->winner_id = $game->player1_id;
        }


        $game->save();

        event(new GameFinished($game));

        return response()->json([
            'msg' => 'Game ended successfully',
            'game_id' => $game->id,
            'winner_id' => $game->winner_id,
        ]);
    }


    public function updateLocation(Request $request) {
        $game = Game::find($request->input('gameId'));
        if ($game == null){
            return response()->json(['success' => false, 'message' => 'Game not found'], 404);
        }
        $playerId = $request->input('player_id');  
        $newLocation = $request->input('location');
    
        if ($game->ShipScreenPlayer == $playerId) {
            if ($newLocation == 100) {
                $game->ShipScreenPlayer = ($game->ShipScreenPlayer == $game->player1_id) ? $game->player2_id : $game->player1_id;
                event(new GameShipLocation(['gameId' => $game->id, 'player_id' => $game->ShipScreenPlayer]));
            }
    
            if ($playerId == $game->player1_id) {
                $game->ShipLocationP1 = $newLocation;
            } else {
                $game->ShipLocationP2 = $newLocation;
            }
    
            $game->attempsP2 = 0;
            $game->attempsP1 = 0; 

            $game->save();
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false, 'message' => 'Not your turn']);
    }
    

    public function registerAttempt(Request $request) {
        $game = Game::find($request->input('gameId'));
        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Game not found']);
        }
    
        $playerId = $request->input('player_id');
    
        if ($game->ShipScreenPlayer != $playerId) {
            return response()->json(['success' => false, 'message' => 'Not your turn']);
        }
    
            if ($playerId == $game->player1_id) {
                $game->increment('PlayerDestroyShip1');
                $game->attempsP2 = 0;
            } else {
                $game->increment('PlayerDestroyShip2');
                $game->attempsP1 = 0; 
            }
    
            $game->ShipLocationP1 = 0;
            $game->ShipLocationP2 = 0;

            $game->increment('ShipDestroyed');
            $game->ShipScreenPlayer = $game->player2_id;
    

            if ($game->ShipDestroyed >= 6) {
                $game->status = 'finished';
                $game->winner_id = ($game->PlayerDestroyShip1 > $game->PlayerDestroyShip2) ? $game->player1_id : $game->player2_id;
                $game->loser_id = ($game->winner_id == $game->player1_id) ? $game->player2_id : $game->player1_id;
            }
    
            $game->save();

            if ($game->ShipDestroyed >= 6) {
                event(new GameFinished($game));
            }
            event(new GamePlayerDestroyShip($game, $playerId));

            return response()->json(['success' => true, 'message' => 'Ship destroyed and turns switched']);
        
    
    }
    


    public function infoGame(Request $request) {
        $game = Game::find($request->input('gameId'));
        if ($game == null){
            return response()->json(['success' => false, 'message' => 'Game not found'], 404);
        }

        return response()->json(['success' => true, 'game' => $game]);
    }



}
