<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - {{ config('app.name', 'CareerAI') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#080e1a] text-white">
    <div class="auth-page" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
        <!-- Ambient background orbs -->
        <div class="auth-bg-orb" style="width:360px;height:360px;top:-80px;left:-100px;background:radial-gradient(circle,rgba(59,130,246,0.15),transparent 70%);animation-delay:0s; position: absolute;"></div>
        <div class="auth-bg-orb" style="width:280px;height:280px;bottom:-60px;right:-60px;background:radial-gradient(circle,rgba(99,102,241,0.12),transparent 70%);animation-delay:3s; position: absolute;"></div>

        <div class="auth-card" style="position: relative; z-index: 10; width: 100%; max-width: 420px; background: rgba(255,255,255,0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
            <!-- Branding -->
            <div class="text-center mb-8" style="text-align: center; margin-bottom: 30px;">
                <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 24px rgba(59,130,246,0.4)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#e2e8f0;line-height:1.2">Forgot Password</h1>
                <p style="color:#94a3b8;font-size:0.875rem;margin-top:6px">Enter your email to receive a reset link</p>
            </div>

            @if (session('status'))
                <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
                    <span style="color:#34d399;flex-shrink:0;font-size:0.9rem">✓</span>
                    <p style="color:#34d399;font-size:0.875rem">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
                    <span style="color:#fca5a5;flex-shrink:0;font-size:0.9rem">⚠</span>
                    <div style="color:#fca5a5;font-size:0.875rem">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:16px">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                        <label for="email" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500;">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                               style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 14px; color: white; width: 100%; outline: none; transition: border-color 0.2s;" 
                               placeholder="you@example.com"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                    </div>

                    <button type="submit" style="background: linear-gradient(135deg,#3b82f6,#6366f1); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; width: 100%; margin-top: 8px;"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        Send Password Reset Link
                    </button>
                </div>
            </form>

            <p style="text-align:center;font-size:0.875rem;color:#64748b;margin-top:24px">
                Remember your password?
                <a href="{{ route('login') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
