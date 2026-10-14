<x-guest-layout>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  /* Full-screen overlay so the layout's own wrapper doesn't show around the card */
  .erp-login { position: fixed; inset: 0; z-index: 9999; overflow-y: auto; background: #444e58;
    font-family: 'Mulish', 'Muli', 'Segoe UI', Arial, sans-serif; color: #2f3a45; }
  .erp-login *, .erp-login *::before, .erp-login *::after { box-sizing: border-box; margin: 0; padding: 0; }
  .erp-stage { position: relative; min-height: 100%; overflow: hidden; padding: 24px 0;
    display: flex; align-items: center; justify-content: center; }

  /* Decorations */
  .erp-deco { position: absolute; pointer-events: none; }
  .erp-cup { top: 0; right: 0; width: 270px; height: 260px; }
  .erp-notebook { bottom: 0; left: 0; width: 360px; height: 368px; }

  /* Card */
  .erp-card { position: relative; z-index: 1; width: 420px; max-width: calc(100% - 32px);
    background: #fff; border-radius: 6px; padding: 46px 83px 36px; }
  

  .erp-login h1 { font-size: 24px; font-weight: 400; margin-bottom: 40px; letter-spacing: .1px; color: #2f3a45; }
  .erp-login h1 strong { font-weight: 700; }

  .erp-login label { display: block; font-size: 18px; color: #4a5560; margin-bottom: 11px; }
  .erp-login input[type="email"], .erp-login input[type="password"] {
    display: block; width: 100%; height: 59px; padding: 0 14px; font: inherit; font-size: 18px; color: #2f3a45;
    border: 1px solid #c6cbd0; border-radius: 3px; background: #fff; box-shadow: none; outline: none;
    transition: border-color .15s; }
  .erp-login input::placeholder { color: #b3b9bf; }
  .erp-login input:focus { border-color: #13c69c; box-shadow: none; }
  .erp-login input.is-invalid { border-color: #e5484d; }
  .erp-field + .erp-field { margin-top: 26px; }

  .erp-error { margin-top: 6px; font-size: 14px; color: #e5484d; }
  .erp-status { margin-bottom: 20px; padding: 10px 14px; font-size: 15px; color: #0c7a60; background: #e3f7f1; border-radius: 3px; }

  .erp-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; }
  .erp-login label.erp-remember { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 16px; cursor: pointer; }
  .erp-remember input { width: 16px; height: 16px; accent-color: #13c69c; }
  .erp-login a.erp-forgot { font-size: 18px; color: #2f3a45; text-decoration: none; }
  .erp-login a.erp-forgot:hover, .erp-register a:hover { text-decoration: underline; }

  .erp-login button.erp-btn { display: block; width: 100%; height: 63px; margin-top: 38px;
    background: #13c69c; color: #fff; border: 0; border-radius: 3px; font: inherit; font-size: 18px;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; transition: background .15s; }
  .erp-login button.erp-btn:hover { background: #10b38d; }
  .erp-login button:focus-visible, .erp-login a:focus-visible { outline: 2px solid #13c69c; outline-offset: 3px; }

  .erp-register { text-align: center; margin-top: 20px; font-size: 18px; }
  .erp-register a { color: inherit; text-decoration: none; }

  @media (max-height: 760px) {
    .erp-card { padding: 32px 83px 26px; }
    .erp-login h1 { font-size: 22px; margin-bottom: 26px; }
    .erp-login label { font-size: 16px; margin-bottom: 8px; }
    .erp-login input[type="email"], .erp-login input[type="password"] { height: 50px; font-size: 16px; }
    .erp-field + .erp-field { margin-top: 18px; }
    .erp-login a.erp-forgot, .erp-register { font-size: 16px; }
    .erp-row { margin-top: 14px; }
    .erp-login button.erp-btn { height: 52px; font-size: 16px; margin-top: 26px; }
    .erp-register { margin-top: 16px; }
  }
  @media (max-width: 600px) {
    .erp-card { padding: 36px 24px 28px; }
    .erp-cup { width: 170px; height: 164px; }
    .erp-notebook { width: 220px; height: 225px; }
    .erp-login h1 { font-size: 21px; margin-bottom: 28px; }
    .erp-login a.erp-forgot { font-size: 16px; }
  }
</style>

<div class="erp-login">
  <div class="erp-stage">

    <!-- Coffee cup (top right) -->
    <svg class="erp-deco erp-cup" viewBox="1234 0 270 260" preserveAspectRatio="xMaxYMin meet" aria-hidden="true">
      <circle cx="1402" cy="102" r="152" fill="#3d4650" opacity=".5"/>
      <circle cx="1392" cy="90" r="152" fill="#ececec"/>
      <circle cx="1482" cy="48" r="30" fill="#f7f7f7"/>
      <circle cx="1396" cy="100" r="96" fill="#cfd8dc"/>
      <circle cx="1386" cy="90" r="94" fill="#f7f7f7"/>
      <circle cx="1386" cy="90" r="81" fill="#b5a396"/>
    </svg>

    <!-- Notebook + pencil (bottom left) -->
    <svg class="erp-deco erp-notebook" viewBox="0 760 360 368" preserveAspectRatio="xMinYMax meet" aria-hidden="true">
      <defs>
        <g id="erp-ring">
          <rect x="-5" y="-12" width="10" height="22" rx="5" fill="#eef7f5"/>
          <rect x="-2" y="-8" width="4" height="14" rx="2" fill="#93d8c8"/>
        </g>
      </defs>
      <polygon points="0,772 296,1056 216,1128 0,1128" fill="#93d8c8"/>
      <polygon points="296,1056 216,1128 204,1128 288,1046" fill="#e8f7f3"/>
      <g id="erp-rings"></g>
      <g transform="translate(184,840) rotate(44)">
        <rect x="10" y="-6" width="240" height="12" fill="#b4bcc4"/>
        <rect x="10" y="-6" width="240" height="4" fill="#d3d9de"/>
        <polygon points="-2,0 10,-6 10,6" fill="#f6e7a6"/>
      </g>
      <script>
        (function () {
          var g = document.getElementById('erp-rings'), ns = 'http://www.w3.org/2000/svg';
          for (var i = 0; i < 15; i++) {
            var u = document.createElementNS(ns, 'use');
            u.setAttribute('href', '#erp-ring');
            u.setAttribute('transform', 'translate(' + (4 + i * 18.6) + ',' + (778 + i * 18.1) + ') rotate(-48)');
            g.appendChild(u);
          }
        })();
      </script>
    </svg>

    <!-- Login card -->
    <div class="erp-card">
      <h1>Welcome to <strong>FMS Admin</strong></h1>

      {{-- Session Status --}}
      @if (session('status'))
        <div class="erp-status">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="erp-field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}"
                 placeholder="admin@fms.com" required autofocus autocomplete="username"
                 class="@error('email') is-invalid @enderror">
          @error('email') <p class="erp-error">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div class="erp-field">
          <label for="password">Password</label>
          <input id="password" type="password" name="password"
                 placeholder="*********" required autocomplete="current-password"
                 class="@error('password') is-invalid @enderror">
          @error('password') <p class="erp-error">{{ $message }}</p> @enderror
        </div>

        <!-- Remember Me + Forgot -->
        <div class="erp-row">
          
          <label for="remember_me" class="erp-remember">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Keep me logged in</span>
          </label>
         
        </div>

        <button type="submit" class="erp-btn">Submit</button>
      </form>

     
    </div>

  </div>
</div>
</x-guest-layout>