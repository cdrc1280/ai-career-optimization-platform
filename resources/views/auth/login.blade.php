@extends('layouts.web')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Login</h1>

            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus />
                </label>

                <label>Password
                    <input type="password" name="password" required />
                </label>

                <label class="remember"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                    Remember me</label>

                <button class="btn primary" type="submit">Log in</button>
            </form>

            <p class="muted">Don’t have an account? <a href="{{ route('register') }}">Register</a></p>
        </div>
    </section>
@endsection
