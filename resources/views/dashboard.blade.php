@extends('layouts.web')

@section('content')
    <section class="dashboard">
        <header class="dashboard-header">
            <h1>Dashboard</h1>
            <p class="muted">Welcome back — here's a quick overview.</p>
        </header>

        @if (session('api_token'))
            <section class="api-token">
                <h2>API Token (for testing)</h2>
                <p class="muted">A temporary token was issued at login. Keep it secret.</p>
                <pre style="background:#f7fafc;padding:0.75rem;border-radius:6px;overflow:auto">{{ session('api_token') }}</pre>
                <p class="muted">Example curl:</p>
                <pre style="background:#fff8ea;padding:0.75rem;border-radius:6px;overflow:auto">curl -H "Authorization: Bearer {{ session('api_token') }}" http://127.0.0.1:8000/api/v1/resumes</pre>
            </section>
        @endif

        <div class="cards-3d" id="cards3d">
            @for ($i = 1; $i <= 8; $i++)
                <article class="card">
                    <h3>Panel {{ $i }}</h3>
                    <p class="small">Summary and quick actions for panel {{ $i }}.</p>
                    <a class="btn" href="#">Open</a>
                </article>
            @endfor
        </div>
    </section>
@endsection
