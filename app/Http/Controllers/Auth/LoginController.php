<?php

namespace App\Http\Controllers\Auth;

use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use App\UserRole;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }
    //
    public function login(Request $request)
    {
        $data = $request->all();
        $prevUser = false;
        $defaultRole = null;

        try {
            $checkMail = User::where('email', $data['email'])->count();
            if (!isset($data['email']) || !isset($data['password']) || $checkMail === 0) {
                return response()->json(['error' => 'Incorrect Email/Password'], 401);
            }

            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            $user = User::where('email', $data['email'])->first();
            $defaultRole = $this->getDefaultRole($user->id);
            $token = $this->jwt->attempt($credentials);

            if (!$token) {
                return response()->json(['error' => 'Incorrect Email/Password'], 401);
            }

            $this->jwt->setToken($token);
            $authenticatedUser = $this->jwt->authenticate();

            // $authenticatedUser = $this->jwt->authenticate($token);

            if (!$authenticatedUser) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $result = $this->loginHelper($authenticatedUser, $defaultRole, [], $token, $prevUser);

        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($result, 200);
    }


    public function register(Request $request)
    {
        $data = $request->all();

        try {
            $checkMail = User::where('email', $data['email'])->count();
            if ($checkMail > 0) {
                return response()->json(['error' => 'Email already exists'], 409);
            }

            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();

            $defaultRole = new UserRole();
            $defaultRole->user_id = $user->id;
            $defaultRole->role_id = Role::where('common_name', 'student')->first()->id; // Default role as student
            $defaultRole->save();

            $credentials = ['email' => $data['email'], 'password' => $data['password']];
            $token = $this->jwt->attempt($credentials, ['roleId' => $defaultRole->role_id]);

            if (!$token) {
                return response()->json(['error' => 'Unable to generate token'], 500);
            }

            $this->jwt->setToken($token);
            $authenticatedUser = $this->jwt->authenticate();

            if (!$authenticatedUser) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $result = $this->loginHelper($authenticatedUser, $defaultRole, [], $token, false);

        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($result, 201);
    }



    public function loginHelper($user,$defaultRole,
                                $result,$token,$prevUser,$is_bs=false){
        if ($user->status <> 1)
            return response()->json(['error' => 'Your Account Has been deactivated. Please contact Admin'], 401);
        $user->login_status = User::$Login;
        $user->forced_logout = 0;
        $user->save();

        $defaultRole->role->isAdmin = in_array($defaultRole->role->common_name, Role::$AdminRole);

        $result['user'] = $user;
        $result['token'] = $token;
        $result['user_role'] = $defaultRole;
        $result['role'] = $defaultRole->role;
        $result[$defaultRole->role->common_name] = $user->userJoinRole($defaultRole->role->common_name);
        return  $result;
    }

    public function getDefaultRole($userId){
        $defaultRole = UserRole::defaultRole($userId);
        if (is_null($defaultRole) || $defaultRole->count() == 0) {
            return response()->json(['error' => 'You Either do not have a Role, or Your Role has been Discontinued. 
                            Contact Admin for further details']);
        }
        return  $defaultRole;
    }
}
