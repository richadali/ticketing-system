<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $role   = Auth::user()->role->name;
        return view('modules.user.user')->with(compact('role'));
    }

    public function ViewContent(Request $request)
    {
        $sql = User::whereHas('role', function ($query) {
            $query->where('id', 2); // role_id for user is 2    
        })->with('role')
            ->get();

        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function StoreData(StoreUserRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            if (isset($request->id)) {   //Edit an admin
                $sql_count = User::where('id', $request->id)->count();
                if ($sql_count > 0) {
                    try {
                        DB::beginTransaction();
                        User::whereId($request->id)->update([
                            'name' => $this->normalizeString($request->name),
                            'password' => Hash::make($request->password),
                        ]);
                        DB::commit();
                        return response()->json(["flag" => "YY"]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(["flag" => "NN"]);
                    }
                } else {
                    return response()->json(["flag" => "NN"]);
                }
            } else {    //Create new admin
                try {
                    DB::beginTransaction();
                    $User = new User();
                    $User->name = $this->normalizeString($request->name);
                    $User->email = $this->normalizeString($request->email);
                    $User->password = Hash::make($request->password);
                    $User->role_id = 2;
                    $User->save();
                    DB::commit();
                    return response()->json(["flag" => "Y"]);
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(["flag" => "N"]);
                }
            }
        }
    }

    public function ShowData(Request $request)
    {
        $sql = User::select('id', 'name', 'email')
            ->where('id', $request->id)
            ->get();
        return response()->json($sql);
    }

    public function DeleteData(Request $request)
    {
        try {
            DB::beginTransaction();
            $sql = User::where('id', $request->id)->delete();
            DB::commit();
            return response()->json(["flag" => "Y"]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["flag" => "N"]);
        }
    }

    public function normalizeString($str)
    {
        $str = strip_tags($str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = mb_ereg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace('%', '-', $str);
        return $str;
    }

    /**
     * Show the change password form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showChangePasswordForm()
    {
        $role = Auth::user()->role ? Auth::user()->role->name : 'User';
        return view('modules.user.change-password', compact('role'));
    }

    /**
     * Change the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update password using the update method
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Password changed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update password. Please try again.');
        }
    }
}
