<!doctype html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite([
        'resources/css/app.css',
        'resources/js/app.ts',
    ])
</head>
<body>
<div id="app"></div>
</body>
</html>
