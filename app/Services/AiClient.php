<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Single choke point for every AI provider call in the app. Swapping
 * OpenAI for Anthropic/another provider later means editing this one
 * class, not every service that uses AI.
 *
 * When MOCK_AI=true (or OPENAI_API_KEY is blank), returns rich
 * context-aware mock data so the full app can be demonstrated without
 * an OpenAI account.
 */
class AiClient
{
    public function __construct(
        private readonly string $apiKey = '',
        private readonly string $model = 'gpt-4o',
    ) {
        $this->apiKey = $apiKey !== '' ? $apiKey : (string) config('services.openai.api_key');
        $this->model  = config('services.openai.model', 'gpt-4o');
    }

    private function isMockMode(): bool
    {
        return config('services.mock_ai', false) || empty($this->apiKey);
    }

    /**
     * Sends a system + user prompt pair and forces a JSON object response.
     */
    public function completeJson(string $systemPrompt, string $userPrompt, string $schemaHint): array
    {
        if ($this->isMockMode()) {
            return $this->mockResponse($systemPrompt, $userPrompt);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $this->model,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => $systemPrompt."\n\nRespond ONLY with valid JSON matching this shape:\n".$schemaHint],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('AI provider request failed: '.$response->body());
        }

        $content = $response->json('choices.0.message.content');
        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned non-JSON content.');
        }

        return $decoded;
    }

    private function mockResponse(string $systemPrompt, string $userPrompt): array
    {
        $sp = strtolower($systemPrompt);

        // Resume parsing
        if (str_contains($sp, 'parse') || str_contains($sp, 'convert raw resume') || str_contains($sp, 'structured json')) {
            return $this->mockParsedResume();
        }

        // Job posting extraction
        if (str_contains($sp, 'job posting') || str_contains($sp, 'extract structured job')) {
            return $this->mockJobPosting();
        }

        // Resume analysis
        if (str_contains($sp, 'analysis') || str_contains($sp, 'compare') || str_contains($sp, 'score')) {
            return $this->mockAnalysis();
        }

        // Resume optimization
        if (str_contains($sp, 'optimiz') || str_contains($sp, 'tailored')) {
            $payload = json_decode($userPrompt, true);
            $resume  = $payload['resume'] ?? $this->mockParsedResume();

            return [
                'resume'     => $resume,
                'change_log' => [
                    ['section' => 'summary', 'change_type' => 'reworded', 'before' => 'Experienced developer', 'after' => 'Results-driven full-stack developer with 5+ years delivering scalable web applications', 'reason' => 'Stronger opening statement with quantified experience'],
                    ['section' => 'work_experience', 'change_type' => 'reworded', 'before' => 'Developed features', 'after' => 'Architected and delivered 20+ production features using Laravel and Vue.js', 'reason' => 'Added specificity and quantification to match JD requirements'],
                    ['section' => 'skills', 'change_type' => 'reordered', 'before' => 'PHP, JavaScript, MySQL', 'after' => 'Laravel, PHP 8.x, Vue.js 3, MySQL, Redis', 'reason' => 'Reordered to match job description keyword priority'],
                ],
            ];
        }

        // Cover letter
        if (str_contains($sp, 'cover letter') || str_contains($sp, 'write cover')) {
            $payload  = json_decode($userPrompt, true);
            $company  = $payload['job_posting']['company'] ?? 'your organization';
            $position = $payload['job_posting']['title']   ?? 'this position';

            return [
                'content' => "Dear Hiring Manager,\n\nI am writing to express my enthusiastic interest in the {$position} position at {$company}. With over five years of hands-on experience building scalable web applications using Laravel, Vue.js, and modern cloud infrastructure, I am confident that my skills and passion for engineering excellence align perfectly with your team's mission.\n\nIn my current role, I have led the development of multiple production-grade applications serving thousands of daily users. I architected a microservices-based platform that reduced deployment time by 60%, and mentored a team of four engineers through an agile transformation. I am particularly drawn to {$company}'s commitment to innovation and the challenges this role presents in scaling your platform to the next level.\n\nI would love the opportunity to bring my expertise in backend architecture, API design, and performance optimization to {$company}. Thank you for considering my application — I look forward to discussing how I can contribute to your team's success.\n\nBest regards,\nJohn Doe",
            ];
        }

        return ['result' => 'ok'];
    }

    private function mockParsedResume(): array
    {
        return [
            'personal_info' => [
                'full_name'      => 'John Doe',
                'email'          => 'john.doe@example.com',
                'phone'          => '+63 912 345 6789',
                'location'       => 'Manila, Philippines',
                'linkedin_url'   => 'https://linkedin.com/in/johndoe',
                'github_url'     => 'https://github.com/johndoe',
                'portfolio_url'  => 'https://johndoe.dev',
            ],
            'professional_title' => 'Senior Full-Stack Developer',
            'summary'            => 'Results-driven full-stack developer with 5+ years of experience building scalable web applications. Specialized in Laravel, Vue.js, and cloud-native architectures. Passionate about clean code, developer experience, and shipping products users love.',
            'work_experience'    => [
                [
                    'company'          => 'TechCorp Philippines',
                    'title'            => 'Senior Software Engineer',
                    'location'         => 'Manila, Philippines',
                    'start_date'       => '2021-03',
                    'end_date'         => null,
                    'is_current'       => true,
                    'responsibilities' => [
                        'Architected and delivered 20+ production features using Laravel 10 and Vue.js 3',
                        'Led a team of 4 engineers through agile sprints, achieving 95% on-time delivery',
                        'Designed and implemented RESTful APIs consumed by 3 mobile applications',
                        'Reduced application load time by 40% through caching strategies and query optimization',
                    ],
                    'achievements' => [
                        'Promoted to Senior Engineer within 18 months due to exceptional performance',
                        'Delivered a flagship product used by 50,000+ daily active users',
                    ],
                ],
                [
                    'company'          => 'StartupXYZ',
                    'title'            => 'Full-Stack Developer',
                    'location'         => 'Cebu, Philippines',
                    'start_date'       => '2019-06',
                    'end_date'         => '2021-02',
                    'is_current'       => false,
                    'responsibilities' => [
                        'Developed and maintained e-commerce platform using Laravel and React',
                        'Integrated multiple payment gateways (Stripe, PayMongo, GCash)',
                        'Implemented automated testing achieving 80% code coverage',
                    ],
                    'achievements' => [
                        'Increased checkout conversion rate by 25% through UX improvements',
                    ],
                ],
            ],
            'education' => [
                [
                    'institution'    => 'University of the Philippines Diliman',
                    'degree'         => 'Bachelor of Science',
                    'field_of_study' => 'Computer Science',
                    'start_date'     => '2015-06',
                    'end_date'       => '2019-04',
                ],
            ],
            'skills'           => ['PHP', 'Laravel', 'Vue.js', 'JavaScript', 'TypeScript', 'MySQL', 'PostgreSQL', 'Redis', 'Docker', 'Git', 'REST APIs', 'TailwindCSS', 'Linux', 'AWS', 'CI/CD'],
            'certifications'   => [
                ['name' => 'AWS Certified Developer – Associate', 'issuing_organization' => 'Amazon Web Services', 'issued_date' => '2022-09'],
            ],
            'languages' => [
                ['name' => 'English', 'proficiency' => 'fluent'],
                ['name' => 'Filipino', 'proficiency' => 'native'],
            ],
            'projects' => [
                [
                    'name'         => 'CareerBoost AI',
                    'description'  => 'AI-powered resume optimizer that increased user interview rates by 35%',
                    'technologies' => ['Laravel', 'Vue.js', 'OpenAI API', 'PostgreSQL'],
                ],
                [
                    'name'         => 'PayFlow',
                    'description'  => 'Payment processing microservice handling ₱10M+ monthly transactions',
                    'technologies' => ['Node.js', 'Redis', 'Stripe', 'Docker'],
                ],
            ],
            'confidence'        => 'high',
            'extraction_notes'  => [],
        ];
    }

    private function mockJobPosting(): array
    {
        return [
            'company_name'           => 'InnovateTech Inc.',
            'job_title'              => 'Senior Laravel Developer',
            'required_skills'        => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Git', 'Docker'],
            'preferred_skills'       => ['Vue.js', 'Redis', 'AWS', 'PostgreSQL', 'TypeScript'],
            'responsibilities'       => [
                'Design and implement scalable backend systems using Laravel',
                'Collaborate with frontend team to deliver full-stack features',
                'Write clean, testable, and well-documented code',
                'Participate in code reviews and mentor junior developers',
                'Optimize application performance and database queries',
            ],
            'qualifications' => [
                '4+ years of professional PHP/Laravel development experience',
                'Strong understanding of SOLID principles and design patterns',
                'Experience with RESTful API design and development',
                'Proficiency with relational databases (MySQL or PostgreSQL)',
            ],
            'experience_requirement' => '4-6 years',
            'industry'               => 'Software / Technology',
            'keywords'               => ['Laravel', 'PHP', 'Backend', 'API', 'Microservices', 'Agile', 'Senior'],
            'technologies'           => ['PHP 8.x', 'Laravel 10+', 'MySQL 8', 'Redis', 'Docker', 'GitHub Actions'],
            'soft_skills'            => ['Communication', 'Problem-solving', 'Leadership', 'Teamwork', 'Attention to detail'],
            'salary_range'           => '₱80,000 - ₱120,000/month',
            'work_setup'             => 'hybrid',
        ];
    }

    private function mockAnalysis(): array
    {
        return [
            'overall_match_score'      => 87,
            'skills_match_score'       => 92,
            'experience_match_score'   => 85,
            'education_match_score'    => 90,
            'ats_compatibility_score'  => 78,
            'keyword_coverage_score'   => 83,
            'industry_alignment_score' => 91,
            'matching_skills'          => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Git', 'Docker', 'Vue.js', 'Redis'],
            'missing_skills'           => ['Kubernetes', 'GraphQL', 'Terraform'],
            'present_keywords'         => ['Laravel', 'PHP', 'Backend', 'API', 'Agile', 'Senior', 'Docker', 'Redis'],
            'missing_keywords'         => ['Kubernetes', 'GraphQL', 'CI/CD pipelines', 'Terraform'],
            'recommended_keywords'     => ['microservices', 'event-driven', 'test-driven development', 'DDD'],
            'ats_issues'               => [
                'Use standard section headers (Experience, Education, Skills) for better ATS parsing',
                'Avoid tables and columns — some ATS systems cannot parse them correctly',
                'Ensure dates are in a consistent format (Month YYYY)',
            ],
            'score_explanations' => [
                'overall'    => 'Strong overall match. Your Laravel and PHP expertise directly align with the core requirements.',
                'skills'     => 'You possess 8 of 11 required/preferred skills. Missing Kubernetes, GraphQL, and Terraform.',
                'experience' => '5 years of experience meets the 4-6 year requirement. Your leadership experience is a plus.',
                'education'  => 'BS Computer Science from a reputable university meets the educational requirements.',
                'ats'        => 'Minor formatting issues detected that may reduce ATS readability.',
                'keywords'   => 'Good keyword coverage but missing some infrastructure-related terms.',
                'industry'   => 'Your background in software/tech companies aligns perfectly with this role.',
            ],
            'skill_gap_details' => [
                [
                    'skill'              => 'Kubernetes',
                    'why_it_matters'     => 'Container orchestration is critical for the company\'s microservices deployment strategy',
                    'where_in_jd'        => 'Mentioned in the infrastructure requirements and preferred qualifications',
                    'learning_resources' => [
                        'https://kubernetes.io/docs/tutorials/',
                        'https://www.udemy.com/course/learn-devops-the-complete-kubernetes-course/',
                        'https://kodekloud.com/courses/kubernetes-for-the-absolute-beginners/',
                    ],
                ],
                [
                    'skill'              => 'GraphQL',
                    'why_it_matters'     => 'The company is migrating from REST to GraphQL for their client-facing APIs',
                    'where_in_jd'        => 'Listed under preferred technologies and future roadmap',
                    'learning_resources' => [
                        'https://graphql.org/learn/',
                        'https://www.howtographql.com/',
                        'https://lighthouse-php.com/6/getting-started/installation.html',
                    ],
                ],
                [
                    'skill'              => 'Terraform',
                    'why_it_matters'     => 'Infrastructure-as-code is part of the team\'s DevOps practices',
                    'where_in_jd'        => 'Mentioned in the DevOps requirements section',
                    'learning_resources' => [
                        'https://developer.hashicorp.com/terraform/tutorials',
                        'https://www.coursera.org/learn/terraform-for-beginners',
                    ],
                ],
            ],
            'career_recommendations' => [
                'certifications' => [
                    ['name' => 'Certified Kubernetes Administrator (CKA)', 'provider' => 'CNCF', 'relevance' => 'High — directly addresses the Kubernetes skill gap'],
                    ['name' => 'AWS Solutions Architect – Associate', 'provider' => 'Amazon Web Services', 'relevance' => 'High — strengthens cloud infrastructure profile'],
                    ['name' => 'HashiCorp Certified: Terraform Associate', 'provider' => 'HashiCorp', 'relevance' => 'Medium — useful for DevOps-adjacent roles'],
                ],
                'courses' => [
                    ['title' => 'Complete Kubernetes Course', 'platform' => 'Udemy', 'url' => 'https://www.udemy.com/course/learn-devops-the-complete-kubernetes-course/'],
                    ['title' => 'GraphQL with Laravel & Lighthouse', 'platform' => 'Laracasts', 'url' => 'https://laracasts.com/series/graphql-with-laravel-and-lighthouse'],
                    ['title' => 'System Design for Senior Engineers', 'platform' => 'Educative', 'url' => 'https://www.educative.io/courses/grokking-modern-system-design-interview-for-engineers-managers'],
                ],
                'career_paths'     => ['Senior Backend Engineer → Principal Engineer', 'Senior Laravel Developer → Engineering Manager', 'Full-Stack Developer → Solutions Architect', 'Backend Developer → DevOps Engineer'],
                'portfolio_projects' => [
                    'Build a Kubernetes-deployed Laravel app with Helm charts to demonstrate orchestration skills',
                    'Create a GraphQL API using Laravel Lighthouse for a blog or e-commerce project',
                    'Implement a Terraform module for a multi-environment AWS infrastructure',
                ],
            ],
            'interview_prep' => [
                'behavioral' => [
                    ['question' => 'Tell me about a time you led a team through a challenging project.', 'suggested_answer' => 'In my role at TechCorp Philippines, I led a team of 4 engineers to deliver a critical payment integration under a 6-week deadline. I implemented daily standups, created clear task breakdowns, and unblocked teammates proactively. We delivered 2 days early with zero production issues.'],
                    ['question' => 'Describe a situation where you had to make a difficult technical decision.', 'suggested_answer' => 'When our startup\'s monolith was struggling with scaling, I proposed and led the migration to a microservices architecture. I presented a phased roadmap to stakeholders, extracted the payment service first (lowest risk), and established patterns the team followed for subsequent extractions.'],
                    ['question' => 'How do you handle disagreements with teammates about technical approaches?', 'suggested_answer' => 'I advocate for data-driven decisions. I present my reasoning clearly, listen to opposing views, and when needed, I propose building a small proof-of-concept to let the results speak. I always prioritize team consensus over being right.'],
                ],
                'technical' => [
                    ['question' => 'Explain the difference between Laravel\'s service container and service provider.', 'suggested_answer' => 'The service container is Laravel\'s IoC container — it resolves class dependencies and performs dependency injection. Service providers are the bootstrap mechanism that tell the container what to bind. They have register() (for bindings) and boot() (for post-registration setup) methods. Every Laravel feature is bootstrapped through service providers.'],
                    ['question' => 'How would you design an API rate limiting system in Laravel?', 'suggested_answer' => 'I\'d use Laravel\'s built-in throttle middleware with RateLimiter::for() to define named limiters. For distributed systems, I\'d back it with Redis using the redis cache driver. I\'d implement sliding window rate limiting for smoother UX, return Retry-After headers on 429 responses, and differentiate limits by user tier (free vs premium).'],
                    ['question' => 'What strategies do you use to optimize slow Laravel queries?', 'suggested_answer' => 'First I identify N+1 problems using Laravel Debugbar or Telescope. Then I add eager loading with with(). For complex queries I\'ll use raw joins or query builder instead of Eloquent. I add appropriate database indexes, implement caching with Redis for frequently-read data, and use pagination instead of loading full datasets.'],
                ],
                'company_specific' => [
                    ['question' => 'Why do you want to work at InnovateTech?', 'suggested_answer' => 'InnovateTech\'s focus on building scalable, impactful software products resonates with my career goals. I\'m particularly excited about the microservices migration you\'re undertaking — I\'ve led similar architectural transformations and know the technical and cultural challenges involved. I\'d love to contribute my experience and continue growing in that direction.'],
                    ['question' => 'How would you contribute to InnovateTech\'s engineering culture?', 'suggested_answer' => 'I\'d bring a strong bias toward code quality, testing, and documentation — practices I\'ve championed throughout my career. I\'d be an active participant in code reviews, willing to mentor junior team members, and I\'d push for clear engineering standards while remaining pragmatic about delivery deadlines.'],
                ],
                'role_specific' => [
                    ['question' => 'How would you approach architecting a new Laravel microservice?', 'suggested_answer' => 'I\'d start with domain modeling — clearly defining the service boundary and its responsibilities. Then I\'d set up the Laravel skeleton with proper structure (Repositories, Services, DTOs). I\'d design the API contract first (API-first approach), implement comprehensive feature tests, add a health check endpoint, and containerize it with Docker. Finally I\'d document it in OpenAPI/Swagger.'],
                    ['question' => 'How do you ensure code quality in a fast-paced environment?', 'suggested_answer' => 'I implement automated quality gates: PHPStan for static analysis, PHP CodeSniffer for style consistency, and PHPUnit for feature/unit tests. These run in CI/CD so no broken code can merge. I also enforce PR reviews — at least one approval before merge. These practices actually speed up delivery by catching bugs early.'],
                ],
            ],
        ];
    }
}
