<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Poseidon</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Poseidon</h1>
        <p class="text-sm text-gray-500 mt-1">Admin Panel</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    autofocus
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                           {{ $errors->has('username') ? 'border-red-400' : 'border-gray-300' }}"
                >
                @error('username')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>

            <button
                type="submit"
                class="w-full py-2 px-4 bg-blue-700 hover:bg-blue-800 text-white font-medium rounded-lg text-sm transition-colors"
            >
                Sign In
            </button>
        </form>
    </div>
</div>

</body>
</html>
