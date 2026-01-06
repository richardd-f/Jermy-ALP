<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;
use Illuminate\Support\Facades\Log;

class RajaOngkirController extends Controller
{
    /**
     * Show provinces from Raja Ongkir API
     */
    public function index()
    {
        $provinces = [];
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key'    => config('rajaongkir.api_key'),
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

            if ($response->successful()) {
                $provinces = $response->json()['data'] ?? [];
            }
        } catch (\Throwable $e) {
            Log::error('Failed to fetch provinces: ' . $e->getMessage());
        }

        return view('rajaongkir', compact('provinces'));
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key'    => config('rajaongkir.api_key'),
            ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

            return response()->json($response->json()['data'] ?? []);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch cities: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key'    => config('rajaongkir.api_key'),
            ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

            return response()->json($response->json()['data'] ?? []);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch districts: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Save or update authenticated user's address
     */
    public function saveAddress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk menyimpan alamat.'
            ], 401);
        }

        $payload = $request->validate([
            'province_id'    => 'required|integer',
            'city_id'        => 'required|integer',
            'district_id'    => 'required|integer',
            'street_address' => 'nullable|string|max:255',
        ]);

        try {
            // Update existing address for user or create new
            $address = Address::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'province'       => $payload['province_id'],
                    'city'           => $payload['city_id'],
                    'subdistrict'    => $payload['district_id'],
                    'street_address' => $payload['street_address'] ?? '',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil disimpan.',
                'address' => $address
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan alamat.',
                'error' => $e->getMessage(),
                'payload' => $payload,
                'user_id' => $user->id
            ], 500);
        }
    }
}
