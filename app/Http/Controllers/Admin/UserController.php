<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Http\Controllers\Controller;
use Firstwap\SmsApiDashboard\Libraries\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    function __construct(UserRepository $repo)
    {
        $this->privileges(Privilege::USER_PAGE_READ);
        $this->privileges([Privilege::USER_ACC_SYSTEM, Privilege::USER_ACC_COMPANY])->only('table');
        $this->privileges(Privilege::USER_PAGE_WRITE)->only(['create', 'store', 'update', 'edit']);
        $this->middleware('ajax')->only(['table','delete']);

        $this->repo = $repo;
    }

    /**
     * Display users view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Display a listing of user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function table(Request $request)
    {
        $params = $request->all();

        $data = $this->repo->table($params);

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $this->validate($request, $this->validationRulesForCreate());

        $input = $this->processRequestInput($request);
        
        $saved = $this->repo->save($input);

        return $saved
                ? redirect()
                        ->route('user.index')
                        ->with('alert-success', trans('validation.success_save', ['name' => 'user '.$input['name']]))
                : $this->failedSaveResponse();
    }

    /**
     * Failed response when unsuccessful save data to database
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function failedSaveResponse()
    {
        return back()
                ->withInput()
                ->withErrors([trans('validation.failed_save', ['name' => 'user'])]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  integer $user
     * @return \Illuminate\Http\Response
     */
    public function edit($user)
    {
        $this->authorize('update', User::class);

        $user = $this->repo->find($user);

        if (is_null($user)) {
            return redirect()
                    ->route('user.index')
                    ->withErrors(['user' => trans('validation.exists', ['attribute' => 'user'])]);
        }

        $this->authorize('update', $user);
        
        return view('users.form',['data'=> $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $this->authorize('update', User::class);

        $user = $this->repo->find($user);

        if (is_null($user)) {
            return redirect()
                    ->route('user.index')
                    ->withErrors(['user' => trans('validation.exists', ['attribute' => 'user'])]);
        }

        $this->validate($request, $this->validationRulesForEdit($user));

        $input = $this->processRequestInput($request);

        $saved = $this->repo->save($input, $user);

        return $saved 
                ? redirect()
                    ->route('user.index')
                    ->with('alert-success', trans('app.success_update', ['name' => 'user '.$user->name]))
                : $this->failedSaveResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($user)
    {
        $this->authorize('delete', User::class);

        $deleted = $this->repo->delete($user);

        return response()->json(['deleted' => $deleted]);
    }

    /**
     * Validation rules for store request
     *
     * @return array
     */
    protected function validationRulesForCreate()
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'unique:AD_USER,email', 'max:100'],
            'password' => ['required', 'min:5', 'confirmed', 'string'],
            'active' => ['boolean'],
            'client_id' => ['required', 'integer', 'exists:mysql_sms_api.CLIENT,client_id'],
            'avatar' => ['nullable', 'max:500', 'image'],
            'api_users' => ['nullable', 'string'],
            'roles' => ['required', 'string'],
        ];
    }

    /**
     * Validation rules for store request
     *
     * @return array
     */
    protected function validationRulesForEdit(User $user)
    {
        $baseRules = $this->validationRulesForCreate();
        $baseRules['email'][2] = Rule::unique('AD_USER')->ignore($user->getKey(), $user->getKeyName());
        $baseRules['password'][0] = 'nullable';

        return $baseRules;
    }
    
    /**
     * Process the input value from request before store to database
     *
     * @param Request $request
     * @return array
     */
    protected function processRequestInput(Request $request)
    {
        $input = $request->all();

        if ($request->has('roles')) {
            $input['roles'] = array_filter(explode(',', $input['roles']));
        }

        if ($request->has('api_users')) {
            $input['api_users'] = array_filter(explode(',', $input['api_users']));
        }

        if ($request->hasFile('avatar')) {
            $input['avatar'] = $this->repo->storeImage($request->file('avatar'));
        }

        return $input;
    }

}
