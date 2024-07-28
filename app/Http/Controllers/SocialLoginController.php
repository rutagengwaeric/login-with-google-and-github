<?php

namespace App\Http\Controllers;

use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    //

    public function toProvider($driver)
    {
        return Socialite::driver($driver)->redirect();
    }
    // End Method

    public function handleCallback($driver)
    {
        $user = Socialite::driver($driver)->user();
        $user_account = SocialLogin::where('provider', $driver)->where('provider_id', $user->getId())->first();
        if($user_account){
            Auth::login($user_account->user);
            Session::regenerate();

            return redirect()->route('dashboard');
        }else{

            $db_user = User::where('id', $user->getId())->first();

            if($db_user){

                SocialLogin::create([
                    'provider' => $driver,
                    'provider_id' => $user->getId(),
                    'user_id' => $db_user->id
                ]);
                Auth::login($db_user);
                Session::regenerate();
                return redirect()->route('dashboard');

            }else{

                $new_user = User::create([
                    'user_id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => bcrypt(random_int(1000, 9999)),
                ]);
                SocialLogin::create([
                    'provider' => $driver,
                    'provider_id' => $user->getId(),
                    'user_id' => $new_user->id
                ]);

                Auth::login($new_user);
                Session::regenerate();
                return redirect()->route('dashboard');
            }

        }

        // dd($user);
    }
}
