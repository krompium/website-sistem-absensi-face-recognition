@extends('layouts.app')

@section('title', 'home - face recognition')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-user-shield text-blue-600"></i> face recognition system
        </h1>

        <!-- status panel -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">total wajah</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $faces['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-user-check text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">orang unik</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($faces['unique_names'] ?? []) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-4">
                        <i class="fas fa-server text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">status api</p>
                        <p class="text-lg font-bold {{ $faces ? 'text-green-600' : 'text-red-600' }}">
                            {{ $faces ? 'online' : 'offline' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- daftar wajah tersimpan -->
        @if ($faces && count($faces['unique_names']) > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    <i class="fas fa-list mr-2"></i>daftar wajah tersimpan
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">jumlah foto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($faces['unique_names'] as $name)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <span class="font-medium text-gray-900">{{ $name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ $faces['counts'][$name] }} foto
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('delete', $name) }}" method="POST" class="inline"
                                            onsubmit="return confirm('yakin hapus semua data {{ $name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                                <i class="fas fa-trash mr-1"></i>hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-4xl mb-3"></i>
                <p class="text-gray-700 mb-3">belum ada data wajah tersimpan</p>
                <a href="{{ route('train') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    <i class="fas fa-upload mr-2"></i>mulai training
                </a>
            </div>
        @endif

        <!-- quick actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <a href="{{ route('train') }}"
                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md p-6 text-center transition">
                <i class="fas fa-upload text-4xl mb-3"></i>
                <h3 class="font-bold text-lg">upload foto training</h3>
                <p class="text-sm mt-2 opacity-90">tambah wajah baru ke database</p>
            </a>

            <a href="{{ route('detect') }}"
                class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg shadow-md p-6 text-center transition">
                <i class="fas fa-search text-4xl mb-3"></i>
                <h3 class="font-bold text-lg">deteksi dari foto</h3>
                <p class="text-sm mt-2 opacity-90">upload foto untuk dikenali</p>
            </a>

            <a href="{{ route('live') }}"
                class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg shadow-md p-6 text-center transition">
                <i class="fas fa-video text-4xl mb-3"></i>
                <h3 class="font-bold text-lg">live camera</h3>
                <p class="text-sm mt-2 opacity-90">deteksi real-time dari webcam</p>
            </a>
        </div>
    </div>
@endsection
