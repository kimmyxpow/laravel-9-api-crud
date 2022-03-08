<?php

namespace App\Http\Controllers;

use App\Http\Lib\BaseApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public $allUsers;

    public function __construct()
    {
        $api = new BaseApi;
        $users = $api->index('/user');

        $pages = ceil($users['total'] / $users['limit']);
        
        $all = [];
        $fix = [];

        for ($i = 1; $i < $pages; $i++) {
            array_push($all, $api->index('/user', ['page' => $i])['data']);
        }

        for ($i = 0; $i < count($all); $i++) {
            foreach ($all[$i] as $value) {
                array_push($fix, $value);
            }
        }

        $this->allUsers = $fix;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $api = new BaseApi;
        $users = $api->index('/user');

        if (request('page')) {
            $users = $api->index('/user', ['page' => request('page')]);
        }

        if (request('search')) {
            $users = $this->search(request('search'), request('page') != null ? (int) request('page') : 1);
        }

        return view('users.index', [
            'users' => $users,
            'title' => 'User Dashboard',
            'breadcrumbs' => [
                [
                    'url' => route('users.index'),
                    'name' => 'User'
                ]
            ]
        ])->with('i', (request()->input('page', 1) - 1) * 20);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create', [
            'title' => 'User Create',
            'breadcrumbs' => [
                [
                    'url' => route('users.index'),
                    'name' => 'User'
                ],
                [
                    'url' => route('users.create'),
                    'name' => 'Create'
                ],
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // buat variable baru untuk menset parameter agar sesuai dengan documentasi
        $payload = [
            'firstName' => $request->input('nama_depan'),
            'lastName' => $request->input('nama_belakang'),
            'email' => $request->input('email'),
        ];

        $baseApi = new BaseApi;
        $response = $baseApi->create('/user/create', $payload);

        // handle jika request API nya gagal
        // diblade nanti bisa ditambahkan toast alert
        if ($response->failed()) {
            // $response->json agar response dari API bisa di akses sebagai array
            $errors = $response->json('data');

            return to_route('users.create')->withErrors($errors)->withInput();
        }

        return to_route('users.index')->with('message', 'Data berhasil disimpan',);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //kalian bisa coba untuk dd($response) untuk test apakah api nya sudah benar atau belum
        //sesuai documentasi api detail user akan menshow data detail seperti `email` yg tidak dimunculkan di api list index
        $response = (new BaseApi)->detail('/user', $id);
        return view('users.edit')->with([
            'user' => $response->json()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //kalian bisa coba untuk dd($response) untuk test apakah api nya sudah benar atau belum
        //sesuai documentasi api detail user akan menshow data detail seperti `email` yg tidak dimunculkan di api list index
        $response = (new BaseApi)->detail('/user', $id);

        return view('users.edit')->with([
            'user' => $response->json(),
            'title' => 'User Edit',
            'breadcrumbs' => [
                [
                    'url' => route('users.index'),
                    'name' => 'User'
                ],
                [
                    'url' => route('users.edit', $id),
                    'name' => 'Edit'
                ],
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //column yg bisa di update sesuai dengan documentasi dummyapi.io hanyalah
        // `fisrtName`, `lastName`
        $payload = [
            'firstName' => $request->input('nama_depan'),
            'lastName' => $request->input('nama_belakang'),
        ];

        $response = (new BaseApi)->update('/user', $id, $payload);

        if ($response->failed()) {
            $errors = $response->json('data');

            return to_route('users.edit', $id)->withErrors($errors)->withInput();
        }

        return to_route('users.index')->with('message', 'Data berhasil diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = (new BaseApi)->delete('/user', $id);

        if ($response->failed()) {
            return redirect()->back()->with('message', 'Data gagal dihapus');
        }

        return redirect()->back()->with('message', 'Data berhasil dihapus');
    }

    public function search($search, $page = 1)
    {
        $api = new BaseApi;

        $names = [];

        foreach ($this->allUsers as $user) {
            array_push($names, [
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
            ]);
        }

        $result1 = array_keys(
            array_filter(
                array_column($names, 'firstName'),
                function ($item) use ($search) {
                    if (stripos($item, $search) !== false) {
                        return true;
                    }
                    return false;
                }
            )
        );

        $result2 = array_keys(
            array_filter(
                array_column($names, 'lastName'),
                function ($item) use ($search) {
                    if (stripos($item, $search) !== false) {
                        return true;
                    }
                    return false;
                }
            )
        );

        $result = array_merge($result1, $result2);

        $newUsers = [
            'data' => [],
            'total' => count($result),
            'page' => $page,
            'limit' => 20,
        ];

        $keyCurrent = $page * 20 - 20;

        foreach ($result as $key => $value) {
            if ($key < $keyCurrent && $page != 1) {
                continue;
            }

            if ($key <= 20 * $page && $page != 1 && $keyCurrent > 20 * $page) {
                continue;
            }

            if (count($newUsers['data']) < 20) {
                array_push($newUsers['data'], $this->allUsers[$value]);
            }
        }

        return $newUsers;
    }
}
