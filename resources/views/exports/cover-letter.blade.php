<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cover Letter</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin: 0; padding: 40px; }
        .header { margin-bottom: 30px; }
        .name { font-size: 24px; font-weight: bold; color: #1a1a2e; }
        .contact { font-size: 12px; color: #555; }
        .date { margin-bottom: 20px; }
        .employer { margin-bottom: 30px; }
        .content { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <div class="name">{{ $profile->full_name ?? $user->name }}</div>
        <div class="contact">
            {{ $profile->email ?? $user->email }}<br>
            {{ $profile->phone ?? '' }}<br>
            {{ $profile->location ?? '' }}
        </div>
    </div>

    <div class="date">
        {{ now()->format('F j, Y') }}
    </div>

    @if($coverLetter->jobPosting)
    <div class="employer">
        Hiring Manager<br>
        {{ $coverLetter->jobPosting->company_name }}<br>
        {{ $coverLetter->jobPosting->location ?? '' }}
    </div>
    @endif

    <div class="content">
{{ $coverLetter->content }}
    </div>
</body>
</html>
