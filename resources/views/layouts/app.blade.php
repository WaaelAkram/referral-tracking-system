<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Referral System</title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <style>
  #scaler {
    transform: scale(0.85);
    transform-origin: top left;
    width: 117.6%;
  }

  body {
    padding-top: 0; /* remove extra space */
    font-size: 0.9rem; /* smaller global font */
  }

  main.container {
    padding-top: 60px; /* space below fixed navbar */
  }

  input.form-control,
  select.form-select,
  textarea.form-control {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
  }

  button.btn {
    font-size: 0.85rem;
    padding: 0.25rem 0.75rem;
  }
</style>

</head>
<body>
  <div id="scaler">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid px-0">

     <a class="navbar-brand ms-0 ps-0" href="{{ url('/') }}" style="margin-left: 0; padding-left: 0;">
    Dental Referral System
</a>


        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.referrals') }}">Dashboard</a>
            </li>
            <!-- Add more nav items here as needed -->
          </ul>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="container">
      @yield('content')
    </main>
  </div>

  <!-- Bootstrap JS (with Popper) CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
