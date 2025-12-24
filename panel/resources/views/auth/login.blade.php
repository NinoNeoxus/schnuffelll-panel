<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Schnuffelll</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        slate: { 900: '#0f172a', 800: '#1e293b' },
                        blue: { 500: '#3b82f6', 600: '#2563eb' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased text-slate-300 bg-cover bg-center" style="background-image: url('/bg.png');">
    <div class="min-h-full flex flex-col justify-center px-6 py-12 lg:px-8 bg-slate-900/80 backdrop-blur-sm">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm text-center">
            <img src="/logo.png" alt="Schnuffelll Logo" class="mx-auto h-24 w-auto drop-shadow-lg">
            <h2 class="mt-4 text-center text-4xl font-bold leading-9 tracking-tight text-white drop-shadow-md">
                <span class="text-blue-500">Schnuffe</span>lll
            </h2>
            <p class="mt-2 text-center text-sm text-slate-400">
                Enterprise Game Server Management
            </p>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <div class="bg-slate-800 py-8 px-6 shadow-xl rounded-lg border border-slate-700">
                <form class="space-y-6" action="/login" method="POST">
                    @csrf
                    
                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-white">Email address</label>
                        <div class="mt-2">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="block w-full rounded-md border-0 bg-slate-900/50 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium leading-6 text-white">Password</label>
                            <div class="text-sm">
                                <a href="#" class="font-semibold text-blue-500 hover:text-blue-400">Forgot password?</a>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="block w-full rounded-md border-0 bg-slate-900/50 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="flex w-full justify-center rounded-md bg-blue-500 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 transition-all duration-200">
                            Sign in to Dashboard
                        </button>
                    </div>
                </form>
            </div>
            
            <p class="mt-10 text-center text-xs text-slate-500">
                Protected by strict German Engineering protocols.
            </p>
        </div>
    </div>
</body>
</html>
