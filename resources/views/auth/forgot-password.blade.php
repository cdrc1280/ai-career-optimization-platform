@extends('layouts.web')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Forgot Password</h1>

            @if (session('status'))
                <div class="success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus />
                </label>

                <button class="btn primary" type="submit">Send Password Reset Link</button>
            </form>

            <p class="muted">Remembered? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </section>
@endsection
