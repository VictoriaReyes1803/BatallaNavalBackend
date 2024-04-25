<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Mail\EmailVerification;
use App\Mail\CodeVerification;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        try {
            $user->save();
        } catch (\Exception $e) {
            Log::critical($e);
            return response()->json(["success" => false, "message" => "Internal error."], 500);
        }

        try {
            Mail::to($user->email)->send(new EmailVerification($user));
        }catch (\Exception $e){
            Log::critical($e);
            $user->delete();
            return response()->json(["success" => false, "message" => "Internal error email."], 500);
        }

        return response()->json(["success" => true, "message" => "Registered correctly "], 201);
    }

    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'email' => 'required|email'
        ]);


        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json([
                'success' => false,
                'msg' => 'User not found'
            ], 404);
        }

        if(! $user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'success' => false,
                'msg' => 'Incorrect password'
            ], 401);
        }


        if($user->email_verified == false){
            return response()->json([
                'success' => false,
                'msg' => 'Email not verified'
            ], 403);
        }

     

        if($request->has('codigo')) {

            if (is_null($user->{'2fa_code'}) || is_null($user->{'2fa_code_at'})) {
                return response()->json(['msg' => 'You have yet to generate a verification code.', 'data' => $user], 405);
            }

            $minutosParaExpiracion = 5;

            $codigoValido = Carbon::now()->diffInMinutes($user->{'2fa_code_at'}) <= $minutosParaExpiracion;

            if (!$codigoValido) {
                return response()->json(['msg' => 'Expired code.', 'data' => $user], 405);
            }

            if($this->verifyCode($request->codigo, $user)){
                $token = $user->createToken('Accesstoken')->plainTextToken;
                $user->{'2fa_code'} = null;
                $user->{'2fa_code_at'} = null;
                return response()->json([
                    'msg' => 'Logged correctly',
                    'data' => $user,
                    'jwt' => $token,
                ]);

            } else {
                return response()->json(['msg' => 'Incorrect code.', 'data' => $user], 405);
            }

        } else {
            $this->getCode($user->id);
            return response()->json(['msg' => '2FA code sent. Please, verify it.', 'data' => $user], 201);
        }

    }

    function getCode($userId){
        if(!$userId){
            return response()->json(['mensaje' => 'Parameter not valid'], 404);
        }

        $user = User::find($userId);
        if(!$user){
            return response()->json(['mensaje' => 'User not found'], 404);
        }

        $codigo = random_int(100000, 999999);
        $user->{'2fa_code'} = encrypt($codigo);
        $user->{'2fa_code_at'} = Carbon::now();
        $user->save();

        $this->sendVerifyCodeEmail($user->email, $codigo);

    }


    function sendVerifyCodeEmail($email, $codigo){

        try {
            Mail::to($email)->send((new CodeVerification($codigo))->build());
        }catch (\Exception $e){
            return response()->json(["success" => false, "message" => "Internal error."], 500);
        }

    }

    function isCodeActive($userId){
        $user = User::find($userId);
        if(!$user){
            return response()->json(['mensaje' => 'User not found'], 404);
        }

        $minutosParaExpiracion = 5;

        $codigoValido = Carbon::now()->diffInMinutes($user->{'2fa_code_at'}) <= $minutosParaExpiracion;

        if (!$codigoValido) {
            return response()->json(['mensaje' => 'Code has expired'], 400);
        }

        return response()->json(['mensaje' => 'Code is still valid']);
    }


    function verifyCode($codigo_ingresado, $user) {

        if (hash_equals((string)decrypt($user->{'2fa_code'}), (string)$codigo_ingresado)) {
            $user->{'2fa_code'} = null;
            $user->{'2fa_code_at'} = null;
            $user->save();
            return true;
        }

        return false;
    }


    public function logout(){

        $user = Auth::user();

        if (!$user) {
            return response()->json(['msg' => 'User not found'], 404);
        };

        $userFind = User::find($user->id);

        $user->currentAccessToken()->delete();

        return response()->json(['status' => true]);

    }

}
