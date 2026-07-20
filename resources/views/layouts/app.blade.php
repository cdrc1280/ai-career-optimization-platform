<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Career') }}</title>
    @vite(['resources/css/app.css', 'resources/js/main.ts'])
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
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
        </div>
    </div>

    <main class="main container">
        <aside class="side-panel">
            <ul>
                <li><a href="{{ route('dashboard') }}">Overview</a></li>
                <li><a href="#">Resumes</a></li>
                <li><a href="#">Job Postings</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </aside>

        <section class="content-wrapper">
            <div id="app">
                @yield('content')
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">© {{ date('Y') }} {{ config('app.name', 'AI Career') }}</div>
    </footer>
</body>

</html>
