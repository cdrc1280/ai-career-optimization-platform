<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - {{ config('app.name', 'CareerAI') }}</title>
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
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0110 0v4"></path>
                    </svg>
                </div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#e2e8f0;line-height:1.2">Reset Password</h1>
                <p style="color:#94a3b8;font-size:0.875rem;margin-top:6px">Enter your new secure password below</p>
            </div>

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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />
                
                <div style="display:flex;flex-direction:column;gap:16px">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                        <label for="email" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500;">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus readonly
                               style="background: rgba(15,23,42,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 12px 14px; color: #94a3b8; width: 100%; outline: none;" />
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                        <label for="password" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500;">New Password</label>
                        <input id="password" type="password" name="password" required 
                               style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 14px; color: white; width: 100%; outline: none; transition: border-color 0.2s;" 
                               placeholder="••••••••"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                        <label for="password_confirmation" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500;">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required 
                               style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 14px; color: white; width: 100%; outline: none; transition: border-color 0.2s;" 
                               placeholder="••••••••"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'" />
                    </div>

                    <button type="submit" style="background: linear-gradient(135deg,#3b82f6,#6366f1); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; width: 100%; margin-top: 8px;"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
