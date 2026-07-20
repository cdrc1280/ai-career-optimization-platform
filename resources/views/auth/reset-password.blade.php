@extends('layouts.web')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Reset Password</h1>

            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}" />

                <label>Email
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus />
                </label>

                <label>New Password
                    <input type="password" name="password" required />
                </label>

                <label>Confirm Password
                    <input type="password" name="password_confirmation" required />
                </label>

                <button class="btn primary" type="submit">Reset Password</button>
            </form>

            <p class="muted">Back to <a href="{{ route('login') }}">Login</a></p>
        </div>
    </section>
@endsection
