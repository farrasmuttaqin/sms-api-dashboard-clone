<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Libraries\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{

    /**
     * Create a new ProfileController instance.
     *
     * @return void
     */
    function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $users = auth()->user();

        return view('users.form', ['data' => $users]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {

        $user = auth()->user();

        $this->validate(
                $request,
                $this->validationRulesForEdit($user)
        );
        
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('profile.edit')
                    ->withErrors(trans('auth.failed'));
        }
        
        $input = $request->all();

        if ($request->hasFile('avatar')) {
            $input['avatar'] = $this->repo->storeImage($request->file('avatar'));
        }

        $saved = $this->repo->save($input, $user);

        return $saved 
                    ? redirect()
                        ->route('profile.edit')
                        ->with('alert-success', trans('app.success_update', ['name' => trans('app.your_profile')]))
                    : redirect()
                        ->route('profile.edit')
                        ->withInput()
                        ->withErrors([trans('validation.failed_save', ['name' => trans('app.your_profile')])]);
    }

    /**
     * Validation rules for edit request
     *
     * @param User $user
     * @return array
     */
    protected function validationRulesForEdit(User $user)
    {
        $unique = Rule::unique('AD_USER')
                    ->ignore($user->getKey(), $user->getKeyName());

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', $unique],
            'current_password' => ['required'],
            'password' => ['nullable', 'min:5', 'confirmed', 'string'],
            'avatar' => ['nullable', 'max:500', 'image'],
        ];
    }

}
