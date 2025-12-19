<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'face recognition system')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- navbar -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-user-shield text-2xl mr-3"></i>
                    <span class="font-bold text-xl">face recognition</span>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('home') }}"
                        class="hover:bg-blue-700 px-3 py-2 rounded {{ request()->routeIs('home') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-home mr-1"></i> home
                    </a>
                    <a href="{{ route('train') }}"
                        class="hover:bg-blue-700 px-3 py-2 rounded {{ request()->routeIs('train') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-upload mr-1"></i> training
                    </a>
                    <a href="{{ route('detect') }}"
                        class="hover:bg-blue-700 px-3 py-2 rounded {{ request()->routeIs('detect') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-search mr-1"></i> detect
                    </a>
                    <a href="{{ route('live') }}"
                        class="hover:bg-blue-700 px-3 py-2 rounded {{ request()->routeIs('live') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-video mr-1"></i> live camera
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- alert messages -->
    @if (session('success'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- main content -->
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-6 text-center">
            <p>face recognition system powered by python flask + laravel</p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>