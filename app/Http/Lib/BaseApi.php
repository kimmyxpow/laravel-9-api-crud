<?php

namespace App\Http\Lib;

use Illuminate\Support\Facades\Http;

class BaseApi
{
    protected $baseUrl;
    protected $appId;

    public function __construct()
    {
        $this->baseUrl = env('API_HOST');
        //app id ini harus ada ketika menggunakan fake API dari dummyapi
        //biasanya kalau API buatan sendiri itu menggunakan token hasil dari login user
        $this->appId = env('API_ID');
    }

    public function index(String $endpoint, array $data = [])
    {
        return $this->client()->get($endpoint, $data);
    }

    public function create(String $endpoint, array $data = [])
    {
        return $this->client()->post($endpoint, $data);
    }

    public function detail(String $endpoint, String $id, array $data = [])
    {
        return $this->client()->get("$endpoint/$id", $data);
    }

    public function update(String $endpoint, String $id, array $data = [])
    {
        return $this->client()->put("$endpoint/$id", $data);
    }

    public function delete(String $endpoint, String $id)
    {
        return $this->client()->delete("$endpoint/$id");
    }

    private function client()
    {
        return Http::withHeaders(['app-id' => $this->appId])->baseUrl($this->baseUrl);
    }
}
