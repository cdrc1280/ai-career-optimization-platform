@extends('layouts.web')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Create an account</h1>

            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.create') }}">
                @csrf

                <label>Name
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus />
                </label>

                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" required />
                </label>

                <label>Password
                    <input type="password" name="password" required />
                </label>

                <label>Confirm Password
                    <input type="password" name="password_confirmation" required />
                </label>

                <button class="btn primary" type="submit">Create account</button>
            </form>

            <p class="muted">Already have an account? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </section>
@endsection
