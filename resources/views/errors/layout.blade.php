<!DOCTYPE html>
<html lang="en" class="bg-canvas">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title') · JourneyOps</title>
    @vite(['resources/css/app.css'])
</head>
<body class="grid grid-cols-1 min-h-screen place-items-center bg-canvas px-4 font-sans text-ink antialiased">
<main class="w-full max-w-md text-center">
    <p class="font-mono text-sm text-signal">@yield('code')</p>
    <h1 class="mt-3 text-3xl font-semibold tracking-tight">@yield('title')</h1>
    <p class="mt-3 text-ink-soft">@yield('message')</p>
    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <a href="/" class="btn-primary">Back to JourneyOps</a>
        <a href="/demo" class="btn-secondary">Guided demo</a>
    </div>
</main>
</body>
</html>
