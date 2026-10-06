<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') — DompetKu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;background:linear-gradient(135deg,#134e4a,#0f766e 60%,#14b8a6);display:flex;align-items:center;justify-content:center;padding:1rem}
.auth{max-width:420px;width:100%;border:0;border-radius:1.25rem;box-shadow:0 20px 50px rgba(0,0,0,.25)}
.btn-brand{background:#0f766e;color:#fff}.btn-brand:hover{background:#134e4a;color:#fff}
.form-control{padding:.7rem .9rem;border-radius:.6rem} a{color:#0f766e}
</style>
</head>
<body>
<div class="card auth p-4 p-md-5">
  <div class="text-center mb-4"><div class="fs-2 fw-bold" style="color:#0f766e"><i class="bi bi-wallet2"></i> DompetKu</div><div class="text-secondary">@yield('subtitle')</div></div>
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  @yield('content')
</div>
</body>
</html>
