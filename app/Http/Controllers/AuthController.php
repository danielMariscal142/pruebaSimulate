<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Direction;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Mail;
use App\Mail\CreacionUsuarioMail;
use App\Mail\ApproveUserMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Inertia\Response;
use Illuminate\View\View;
use League\OAuth1\Client\Client;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','registerUser']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        // Modificar la duración de vida del token en la configuración de JWT
        config(['jwt.ttl' => 3600]); // Duración de vida de 1 hora (3600 segundos)

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        if (!$user->is_synchronized || !$user->is_approved) {
            return response()->json(['error' => 'User not synchronized or approved'], 401);
        }

        $roles = $user->roles()->pluck('name');

        // Ahora, obtén la duración de vida del token después de haber ajustado la configuración
        $expiration = auth()->factory()->getTTL();
        //  dd(auth()->factory());
        return response()->json([
            'token' => $token,
            'roles' => $roles,
            'expires_in' => $expiration,
        ]);
    }





    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function dataCompany() {
        $userJson = response()->json(auth()->user())->getContent();
        $user = json_decode($userJson);
        $companyId = $user->company_id;
        $company = Company::find($companyId);
        return $company->name;
    }


    public function userList()
    {
        if (auth()->user()->hasRole('superadmin')) {
            $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'user');
            })->with(['directions', 'company' => function ($query) {
                $query->select('id', 'name');
            }])->get();
            return response()->json($users);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function userListAdmin()
    {
        if (auth()->user()->hasRole('superadmin')) {
            $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->with(['directions', 'company' => function ($query) {
                $query->select('id', 'name');
            }])->get();
            return response()->json($users);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }



    public function getUser(Request $request)
    {
        $userId = $request->input('id');
        if ($request->user()->hasRole('admin')) {
            $user = User::with('directions')->find($userId);
            if ($user) {
                return response()->json($user);
            } else {
                return response()->json(['error' => 'User not found'], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }


    public function approveUser(Request $request)
    {
        if (auth()->user()->hasRole('superadmin')) {
            $userId = $request->input('id');
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $user->is_approved = true;
            $user->save();
            Mail::to($user->email)->send(new ApproveUserMail());

            return response()->json(['message' => 'User approved successfully'], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function denieUser(Request $request)
    {
        if (auth()->user()->hasRole('superadmin')) {
            $userId = $request->input('id');
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $user->is_approved = false;
            $user->save();

            return response()->json(['message' => 'User approved successfully'], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function editUser(Request $request)
    {
        if (auth()->user()->hasRole('superadmin')) {
            $userId = $request->input('id');
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $user->name = $request->input('name');
            $user->phone = $request->input('phone');
            $user->email = $request->input('email');
            $user->save();

            $diretionId = $request->input('id_direction');
            $direction = Direction::find($diretionId);
            if (!$direction) {
                return response()->json(['error' => 'Direction not found'], 404);
            }
            $direction->label = $request->input('directions');
            $direction->save();
            return response()->json(['message' => 'User approved successfully'], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|min:7',
            'netsuite_id'=> 'required',
            'company'=>'required'
        ]);
        $validatorDirection = Validator::make($request->all(), [
            'direction'=>'required|string'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        if($validatorDirection->fails()){
            return response()->json($validatorDirection->errors()->toJson(),400);
        }

        $company = Company::create([
            'name'=>$request->company,
        ]);
        $user = User::create(array_merge(
            $validator->validate(),
            ['company_id'=>$company->id],
            ['password' => bcrypt($request->password)]
        ));

        $direction = Direction::create([
            'status' => true,
            'user_id' => $user->id,
            'label' => $request->direction
        ]);
        // Asignar el rol 'user' al usuario registrado
        $user->roles()->attach(Role::where('name', 'admin')->first());
        // Mail::to($request->email)->send(new CreacionUsuarioMail());
        // return response()->json([
        //     'message' => '¡Usuario registrado exitosamente!',
        //     'user' => $user
        // ], 201);
    }



    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|min:7',
        ]);
        $validatorDirection = Validator::make($request->all(), [
            'direction'=>'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        if($validatorDirection->fails()){
            return response()->json($validatorDirection->errors()->toJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            [
                'password' => bcrypt($request->password),
                'is_synchronized' => true,
                'is_approved' => true
            ]
        ));


        $direction = Direction::create([
            'status' => true,
            'user_id' => $user->id,
            'label' => $request->direction
        ]);
        // Asignar el rol 'user' al usuario registrado
        $user->roles()->attach(Role::where('name', 'user')->first());

        return response()->json([
            'message' => '¡Usuario registrado exitosamente!',
            'user' => $user
        ], 201);
    }

    public function registerNetsuite(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'nameEmpresa' => 'required',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string|min:7',
                'netsuite_id'=>'required',
                'contacts'=>'required'
            ]);
            $validatorDirection = Validator::make($request->all(), [
                'direction'=>'required|string|min:8'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(),400);
            }

            if($validatorDirection->fails()){
                return response()->json($validatorDirection->errors()->toJson(),400);
            }

            $company = Company::create(
                ['name'=>$request->nameEmpresa]
            );

            $user = User::create(
            [
                'name'=>$request->nameEmpresa,
                'email'=>$request->email,
                'password'=>bcrypt($request->password),
                'netsuite_id'=>$request->netsuite_id,
                'phone'=>$request->phone,
                'is_synchronized' => true,
                'is_approved' => true,
                'company_id'=>$company->id
            ]
            );

            $direction = Direction::create([
                'status' => true,
                'user_id' => $user->id,
                'label' => $request->direction
            ]);
            $user->roles()->attach(Role::where('name', 'admin')->first());

                $contacts = $request->contacts;

                foreach ($contacts as $contact) {
                    $this->createUserNetsuite($contact,$company->id,1);
                }
                return response()->json([
                    'message' => '¡Usuario registrado exitosamente!',
                    'user' => $user
                ], 201);

    }
    private function createUserNetsuite($contactArray,$companyId,$value){
        $contact="";
        if($value==1){
            $contact = (object) $contactArray;
        }
        else{
            $contact=$contactArray;
        }
            $user = User::create(
            [
                'name'=>$contact->name,
                'email'=>$contact->email,
                'password'=>bcrypt($contact->password),
                'netsuite_id'=>$contact->netsuite_id,
                'phone'=>$contact->phone,
                'is_synchronized' => true,
                'is_approved' => true,
                'company_id'=>$companyId
            ]
            );

            $direction = Direction::create([
                'status' => true,
                'user_id' => $user->id,
                'label' => $contact->direction
            ]);
            $user->roles()->attach(Role::where('name', 'user')->first());
    }

    public function editNetsuite(Request $request)
    {
        $netsuiteId = $request->netsuite_id;
        $user = User::where('netsuite_id', $netsuiteId)->first();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->save();

            $direction = Direction::where('user_id', $user->id)->first();
            if (!$direction) {
                return response()->json(['error' => 'Direction not found'], 404);
            }
            $direction->label = $request->direction;
            $direction->save();
            $contacts=$request->contacts;
            foreach ($contacts as $contact) {
                $this->editUserNetsuite($contact,$user->company_id);
            }

            return response()->json(['message' => 'User edit successfully'], 200);
    }
    private function editUserNetsuite($contactArray,$companyId){
        $contact = (object) $contactArray;
        $netsuiteId = $contact->netsuite_id;
        $user = User::where('netsuite_id', $netsuiteId)->first();
            if (!$user) {
                $this->createUserNetsuite($contact,$companyId,2);
            }
            else{
                $user->name = $contact->name;
                $user->phone = $contact->phone;
                $user->email = $contact->email;
                $user->save();

                $direction = Direction::where('user_id', $user->id)->first();
                if (!$direction) {
                    return response()->json(['error' => 'Direction not found'], 404);
                }
                $direction->label = $contact->direction;
                $direction->save();
            }
    }
}
