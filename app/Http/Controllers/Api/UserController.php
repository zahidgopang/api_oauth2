<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;
use Validator;
use App\Models\User;

class UserController extends Controller
{
    public function CreateUser(Request $request){

        try{
            global $DBConnection;
            if (Auth::guard('api')->user()) {

                $DBConnection->beginTransaction();

                $user = Auth::guard('api')->user();

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
                $input['created_by'] = $user->id;

                if ($request->file("photo")) {
                    $response = image_upload($request, 'users', 'photo');
                    if ($response['status']) {
                        $input['photo'] = $response['link'];
                    }
                }

                $user = User::create($input);
                $DBConnection->commit();
                return sendSuccessResponse('User created successfully.', $user);
            }
            return sendError('Unauthorised');

        }catch(Exception $e){
            $DBConnection->rollBack();
            return sendError($e->getMessage());
        }
    }
    public function UpdateUser(Request $request){

        try{
            global $DBConnection;
            if (Auth::guard('api')->user()) {

                $DBConnection->beginTransaction();

                $user = Auth::guard('api')->user();

                /**
                 * SignUp api
                 *
                 * @return \Illuminate\Http\Response
                 */

                $data = [
                    'user_id' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email',
                    'password' => 'confirmed'
                ];

                $validator = Validator::make($request->all(), $data);

                if ($validator->fails()) {
                    return sendValidationError($validator->errors(), $data);
                }

                if ( $request->user_id <= 0 || $request->user_id == "")
                {
                    return sendError('User is invalid');
                }

                $user = User::find($request->user_id);
                if ( ! $user ) {
                    return sendError('Invalid User!');
                }

                $check_duplicate_email = User::where('email', $request->email)->where('id', '!=', $request->user_id)->first();
                if ( $check_duplicate_email ) {
                    return sendError('User already exits with same email id');
                }

                $user->name = $request->first_name . ' ' . $request->last_name;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                if ( $request->password != "" ) {
                    $user->password = bcrypt($request->password);
                }

                if ($request->file("photo")) {
                    $response = image_upload($request, 'users', 'photo');
                    if ($response['status']) {
                        $user->photo = $response['link'];
                    }
                }

                $user->save();

                $DBConnection->commit();
                return sendSuccessResponse('User Updated successfully.', $user);
            }
            return sendError('Unauthorised');

        }catch(Exception $e){
            $DBConnection->rollBack();
            return sendError($e->getMessage());
        }
    }
    public function DeleteUser(Request $request){

        try{
            global $DBConnection;
            if (Auth::guard('api')->user()) {

                $DBConnection->beginTransaction();

                $user = Auth::guard('api')->user();

                /**
                 * SignUp api
                 *
                 * @return \Illuminate\Http\Response
                 */

                $data = [
                    'user_id' => 'required'
                ];

                $validator = Validator::make($request->all(), $data);

                if ($validator->fails()) {
                    return sendValidationError($validator->errors(), $data);
                }

                if ( $request->user_id <= 0 || $request->user_id == "")
                {
                    return sendError('User is invalid');
                }

                $user = User::find($request->user_id);
                if ( ! $user ) {
                    return sendError('Invalid User!');
                }

                User::find($request->user_id)->delete();

                $DBConnection->commit();
                return sendSuccessResponse('User Deleted successfully.', $user);
            }
            return sendError('Unauthorised');

        }catch(Exception $e){
            $DBConnection->rollBack();
            return sendError($e->getMessage());
        }
    }
    public function Logout(Request $request){
        $token = $request->user()->token();
        $token->revoke();
        return sendSuccessResponse('You have been successfully logged out!');
    }
    public function ShowAllUser(){
        $user = User::where('created_by', '!=',0)->paginate(15);
        return sendSuccessResponse('User List!', $user);
    }
}
