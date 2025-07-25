<?php

namespace App\Http\Controllers;

use App\Models\Producer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProducerController extends Controller
{
    /**
     * Store a newly created producer in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produsen_supplier' => 'required|string|max:255',
            'kontak_whatsapp' => 'required|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'catatan' => 'nullable|string|max:500',
        ], [
            'nama_produsen_supplier.required' => 'Nama produsen/supplier harus diisi.',
            'nama_produsen_supplier.max' => 'Nama produsen/supplier maksimal 255 karakter.',
            'kontak_whatsapp.required' => 'Kontak WhatsApp harus diisi.',
            'kontak_whatsapp.max' => 'Kontak WhatsApp maksimal 20 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'no_telp.max' => 'No. telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal menambah mitra produsen. Silakan periksa input Anda.');
        }

        try {
            Producer::create($request->all());

            return redirect()->route('order.items')
                ->with('success', 'Mitra produsen berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambah mitra produsen: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified producer in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_produsen_supplier' => 'required|string|max:255',
            'kontak_whatsapp' => 'required|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'catatan' => 'nullable|string|max:500',
        ], [
            'nama_produsen_supplier.required' => 'Nama produsen/supplier harus diisi.',
            'nama_produsen_supplier.max' => 'Nama produsen/supplier maksimal 255 karakter.',
            'kontak_whatsapp.required' => 'Kontak WhatsApp harus diisi.',
            'kontak_whatsapp.max' => 'Kontak WhatsApp maksimal 20 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'no_telp.max' => 'No. telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal mengupdate mitra produsen. Silakan periksa input Anda.');
        }

        try {
            $producer = Producer::findOrFail($id);
            $producer->update($request->all());

            return redirect()->route('order.items')
                ->with('success', 'Mitra produsen berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate mitra produsen: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified producer from storage.
     */
    public function destroy($id)
    {
        try {
            $producer = Producer::findOrFail($id);
            
            // Check if producer has related items
            if ($producer->incomingItems()->count() > 0 || 
                $producer->outgoingItems()->count() > 0 || 
                $producer->verificationItems()->count() > 0) {
                return redirect()->back()
                    ->with('warning', 'Tidak dapat menghapus mitra produsen karena masih memiliki data terkait.');
            }
            
            $producer->delete();

            return redirect()->route('order.items')
                ->with('success', 'Mitra produsen berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus mitra produsen: ' . $e->getMessage());
        }
    }
}