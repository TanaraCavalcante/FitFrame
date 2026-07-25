<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta Password — Gestione FitFrame</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h3 class="mb-4">Reimposta Password</h3>

            <form method="POST" action="{{ route('backend.password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nuova password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Conferma password</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-100">Reimposta Password</button>
            </form>
        </div>
    </div>
</body>
</html>
