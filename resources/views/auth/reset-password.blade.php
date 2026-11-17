<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | TRIX WAY</title>
    <link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] flex items-center justify-center p-5">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 sm:p-10 shadow-2xl shadow-indigo-900/10">
        <div class="mb-7 text-center">
            <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="mx-auto mb-6 max-h-14 max-w-[170px] object-contain">
            <h1 class="text-2xl font-bold text-[#1C2A40]">Create New Password</h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">Buat password baru untuk akun HR TRIX-WAY.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email HR</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password Baru</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
            </div>
            <button type="submit" class="w-full rounded-xl bg-[#1C2A40] px-5 py-3.5 font-bold text-white transition hover:bg-indigo-600">Simpan Password Baru</button>
        </form>
    </main>
</body>
</html>
