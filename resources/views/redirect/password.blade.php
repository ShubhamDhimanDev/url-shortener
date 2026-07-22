<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Protected Link</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }
        .lock-icon { text-align: center; font-size: 3rem; margin-bottom: 1rem; }
        h1 { margin: 0 0 .5rem; font-size: 1.4rem; text-align: center; color: #111; }
        p.subtitle { text-align: center; color: #6b7280; margin: 0 0 1.5rem; font-size: .9rem; }
        label { display: block; margin-bottom: .4rem; font-size: .85rem; font-weight: 600; color: #374151; }
        input[type="password"] {
            width: 100%; padding: .65rem .75rem; border: 1px solid #d1d5db;
            border-radius: 8px; font-size: 1rem; outline: none;
            transition: border-color .2s;
        }
        input[type="password"]:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .error { color: #dc2626; font-size: .83rem; margin-top: .35rem; }
        button {
            margin-top: 1rem; width: 100%; padding: .75rem;
            background: #6366f1; color: #fff; border: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background .2s;
        }
        button:hover { background: #4f46e5; }
    </style>
</head>
<body>
<div class="card">
    <div class="lock-icon">🔒</div>
    <h1>Password Required</h1>
    <p class="subtitle">This link is password-protected. Enter the password to continue.</p>

    <form method="POST" action="{{ route('redirect.unlock', $link->short_code) }}">
        @csrf
        <label for="password">Password</label>
        <input
            id="password"
            type="password"
            name="password"
            autofocus
            placeholder="Enter password"
        >
        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <button type="submit">Unlock &amp; Continue →</button>
    </form>
</div>
</body>
</html>
