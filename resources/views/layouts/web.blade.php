<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Career') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="topbar">
        <div class="container">
            <div class="brand"><a href="{{ url('/') }}">{{ config('app.name', 'AI Career') }}</a></div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @guest
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @else
                    <form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf<button
                            class="link-btn">Logout</button></form>
                @endguest
            </nav>
        </div>
    </div>

    <main class="main container">
        <section class="content-wrapper">
            @yield('content')
        </section>
    </main>

    <footer class="footer">
        <div class="container">© {{ date('Y') }} {{ config('app.name', 'AI Career') }}</div>
    </footer>
</body>

</html>
