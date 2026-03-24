<div class="login-site">
  <form id="login-form" class="login-main">
    <section class="login-box">

      <div class="login-left-page">
        <div class="login-field-group">
          <label for="login-email">Email</label>
          <input type="email" id="login-email" name="email" required placeholder="example@asd.com">
        </div>

        <div class="login-field-group">
          <label for="login-pw">Password</label>
          <input type="password" id="login-pw" name="password" required placeholder="********">
        </div>

        <button type="button" class="login-link-style-btn" data-target="forgot-pw-view">
          Forgot password?
        </button>
        <div id="login-error" class="error-message" role="alert"></div>
      </div>

      <div class="login-divider"></div>

      <div class="login-right-page">
        <button type="submit" class="login-primary-btn">LOGIN</button>

        <div class="switch-prompt">
          <span>Don't have a profile?</span>
          <button type="button" class="login-secondary-btn" data-target="register-view" onclick="window.location.href='/registration'">
            Register now
          </button>
        </div>
      </div>

    </section>
  </form>
  <div id="forgot-pw-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-orange-50 border-4 border-orange-950 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] w-[90%] max-w-md flex flex-col relative p-8">

      <button type="button" id="close-forgot-btn" class="absolute top-4 right-5 text-4xl font-bold text-red-600 hover:text-red-800 transition-colors leading-none cursor-pointer">&times;</button>

      <h2 class="text-2xl font-extrabold text-orange-950 uppercase tracking-widest mb-4">Reset Password</h2>
      <p class="text-orange-950 font-medium mb-6 text-lg">Enter your email address and we'll send you a link to reset your password.</p>

      <form id="forgot-pw-form" class="flex flex-col gap-5">
        <input type="email" name="forgot_email" required placeholder="example@asd.com" class="w-full bg-white border-4 border-orange-950 p-3 rounded-md text-lg text-gray-800 font-medium focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/30 transition-all shadow-[inset_0_4px_4px_rgba(0,0,0,0.05)]">

        <div id="forgot-pw-error" class="text-red-600 font-bold text-center text-lg h-6"></div>

        <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-extrabold text-2xl py-3 px-6 rounded-md border-4 border-orange-950 shadow-[4px_4px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 hover:shadow-[2px_2px_0px_rgba(0,0,0,1)] tracking-widest cursor-pointer">SEND LINK</button>
      </form>

    </div>
  </div>
</div>