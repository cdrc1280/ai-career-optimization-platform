<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        h1 { font-size: 24px; color: #1a1a2e; margin-bottom: 5px; text-align: center; }
        h2 { font-size: 16px; color: #2563eb; text-transform: uppercase; border-bottom: 2px solid #2563eb; padding-bottom: 5px; margin-top: 20px; }
        .subtitle { text-align: center; color: #2563eb; font-size: 14px; margin-bottom: 10px; }
        .contact { text-align: center; color: #555; font-size: 12px; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .item { margin-bottom: 15px; }
        .item-title { font-weight: bold; font-size: 14px; }
        .item-meta { font-size: 12px; color: #666; font-style: italic; }
        ul { margin-top: 5px; padding-left: 20px; }
        li { margin-bottom: 3px; font-size: 13px; }
    </style>
</head>
<body>
    @php
        $content = $resumeVersion->content ?? [];
        $personal = $content['personal_info'] ?? [];
    @endphp

    <h1>{{ $personal['full_name'] ?? 'Name' }}</h1>
    @if(isset($content['professional_title']))
        <div class="subtitle">{{ $content['professional_title'] }}</div>
    @endif
    
    <div class="contact">
        @php
            $contact = array_filter([
                $personal['email'] ?? null,
                $personal['phone'] ?? null,
                $personal['location'] ?? null,
                $personal['linkedin_url'] ?? null
            ]);
        @endphp
        {{ implode(' | ', $contact) }}
    </div>

    @if(!empty($content['summary']))
        <div class="section">
            <h2>Professional Summary</h2>
            <p>{{ $content['summary'] }}</p>
        </div>
    @endif

    @if(!empty($content['work_experience']))
        <div class="section">
            <h2>Work Experience</h2>
            @foreach($content['work_experience'] as $exp)
                <div class="item">
                    <div class="item-title">{{ $exp['title'] ?? '' }} | {{ $exp['company'] ?? '' }}</div>
                    <div class="item-meta">
                        {{ $exp['start_date'] ?? '' }} – {{ !empty($exp['is_current']) ? 'Present' : ($exp['end_date'] ?? '') }}
                        @if(!empty($exp['location'])) | {{ $exp['location'] }} @endif
                    </div>
                    <ul>
                        @foreach($exp['responsibilities'] ?? [] as $resp)
                            <li>{{ $resp }}</li>
                        @endforeach
                        @foreach($exp['achievements'] ?? [] as $ach)
                            <li><strong>{{ $ach }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($content['education']))
        <div class="section">
            <h2>Education</h2>
            @foreach($content['education'] as $edu)
                <div class="item">
                    <div class="item-title">{{ $edu['degree'] ?? '' }} in {{ $edu['field_of_study'] ?? '' }}</div>
                    <div class="item-meta">{{ $edu['institution'] ?? '' }} | {{ $edu['start_date'] ?? '' }} – {{ $edu['end_date'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($content['skills']))
        <div class="section">
            <h2>Skills</h2>
            <p>{{ implode(' • ', $content['skills']) }}</p>
        </div>
    @endif
</body>
</html>
