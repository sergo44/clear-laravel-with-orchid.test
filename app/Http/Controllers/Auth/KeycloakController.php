<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Config;
use Error;
use Exception;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class KeycloakController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function callback()
    {
        try {

            $keycloakUser = Socialite::driver('keycloak')->user();

            session()->put('keycloak_id_token', $keycloakUser->accessTokenResponseBody['id_token'] ?? "");

            $user = User::updateOrCreate(
                ['email' => $keycloakUser->getEmail()],
                [
                    'name' => $keycloakUser->getName(),
                    'password' => Hash::make(Str::random(24)) // Генерим случайный пароль, надо что бы был
                ]
            );

            $this->syncRoles($user, $keycloakUser->user['realm_access']['roles'] ?? []);

            Auth::login($user);

            return redirect()->route('platform.main');

        } catch (Exception $e) {
            throw new Error(sprintf('Ошибка плагина авторизации keycloak: %s (%s)', $e->getMessage(), htmlspecialchars($_GET['error_description'] ?? "")));
        }
    }

    protected function syncRoles(User $user, array $keycloakRoles)
    {
        $permissions = $user->permissions ?? array();

        if (!isset($permissions['platform.index'])) {
            $permissions['platform.index'] = true;
        }

        // Для примера права
        /* if (in_array('admin', $keycloakRoles)) {
            $permissions['platform.systems.roles'] = 1;
            $permissions['platform.systems.users'] = 1;
            $permissions['platform.systems.attachment'] = 1;
        } */

        $user->permissions = $permissions;
        $user->save();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function logout(): RedirectResponse
    {
        $id = (string)session('keycloak_id_token');
        Auth::logout();
        return redirect()->to(Socialite::driver('keycloak')->getLogoutUrl(Config::get('app.url'), config('KEYCLOAK_CLIENT_ID', ''), $id));
    }
}
