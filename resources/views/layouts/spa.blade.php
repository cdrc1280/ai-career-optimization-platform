<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Career') }}</title>
    @vite(['resources/css/app.css', 'resources/js/main.ts'])
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light');
        }
    </script>
</head>

<body>
    <div id="app-vue"></div>
</body>

</html>
