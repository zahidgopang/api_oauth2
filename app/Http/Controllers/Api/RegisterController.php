<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class RegisterController extends Controller
{
    public function SignUp(Request $request)
    {
        /**
         * SignUp api
         *
         * @return \Illuminate\Http\Response
         */

        $data = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,$this->id,id',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ];

        $validator = Validator::make($request->all(), $data);

        if ($validator->fails()) {
            return sendValidationError($validator->errors(), $data);
        }

        $input = [];
        $input['name'] = $request->first_name . ' ' . $request->last_name;
        $input['first_name'] = $request->first_name;
        $input['last_name'] = $request->last_name;
        $input['email'] = $request->email;
        $input['password'] = bcrypt($request->password);

        if ($request->file("photo")) {
            $response = image_upload($request, 'users', 'photo');
            if ($response['status']) {
                $input['photo'] = $response['link'];
            }
        }

        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;
        return sendSuccessResponse('User register successfully.', $success);
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
        $user = Auth::user();
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
        return sendSuccessResponse('Login successfully.', $success);
        }else{
            return sendError('Unauthorised');
        }
    }
}
