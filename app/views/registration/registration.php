<div class="registration-site">
  <form id="register-form" class="register-main">
    <section class="register-box">

      <div class="register-left-page">
        <div class="register-field-group">
          <label for="reg-user">Username</label>
          <input type="text" id="reg-user" name="username" required placeholder="Player1">
        </div>
        <div class="register-field-group">
          <label for="reg-email">Email</label>
          <input type="email" id="reg-email" name="email" required placeholder="example@asd.com">
        </div>
        <div class="register-field-group">
          <label for="reg-pw">Password</label>
          <input type="password" id="reg-pw" name="password" required placeholder="********">
        </div>
        <div class="register-field-group">
          <label for="reg-pw-confirm">Confirm Password</label>
          <input type="password" id="reg-pw-confirm" name="password_confirm" required placeholder="********">
        </div>
      </div>

      <div class="register-divider"></div>

      <div class="register-right-page">

        <div class="register-checkbox-group">
          <input type="checkbox" id="register-terms" name="terms" required>
          <label for="register-terms">I accept the <button type="button" class="register-terms-btn">terms and conditions</button></label>
        </div>

        <button type="submit" class="register-primary-btn">REGISTER</button>

        <div class="switch-prompt">
          <span>Already have an account?</span>
          <button type="button" class="register-return-btn" data-target="login-view" onclick="window.location.href='/login'">
            Back to Login
          </button>
        </div>

      </div>

    </section>
  </form>
  <div id="terms-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-orange-50 border-4 border-orange-950 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] w-[90%] max-w-2xl max-h-[80vh] flex flex-col relative">

      <div class="p-6 border-b-4 border-orange-950 flex justify-between items-center bg-orange-200/50 rounded-t-lg">
        <h2 class="text-2xl font-extrabold text-orange-950 uppercase tracking-widest">Terms and Conditions</h2>
        <button type="button" id="close-terms-btn" class="text-3xl font-bold text-red-600 hover:text-red-800 transition-colors leading-none cursor-pointer">&times;</button>
      </div>

      <div class="p-6 overflow-y-auto text-orange-950 font-medium text-lg space-y-4">
        <p>Welcome to Troxan! By registering an account, you agree to abide by the following rules:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li><strong>Be respectful:</strong> No toxicity, hate speech, or harassment of other players.</li>
          <li><strong>No cheating:</strong> Exploiting bugs, using third-party software, or hacking will result in a permanent ban.</li>
          <li><strong>Account security:</strong> You are responsible for keeping your password safe. We will never ask for your password.</li>
          <li><strong>Fair play:</strong> Keep the competition clean and enjoy the game!</li>
        </ul>
        <p class="text-sm mt-4 text-orange-800/80">These terms are subject to change. Please review them periodically.</p>
      </div>

      <div class="p-4 border-t-4 border-orange-950 bg-orange-200/50 rounded-b-lg flex justify-end">
        <button type="button" id="accept-terms-btn" class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-bold py-2 px-6 border-2 border-orange-950 rounded shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-0.5 hover:shadow-[1px_1px_0px_rgba(0,0,0,1)] cursor-pointer">Understood</button>
      </div>
    </div>
  </div>

  <!-- Notification Modal -->
  <div id="notification-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-orange-50 border-4 border-orange-950 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] w-[90%] max-w-md flex flex-col relative p-8">
      <button type="button" id="close-notification-btn" class="absolute top-4 right-5 text-4xl font-bold text-red-600 hover:text-red-800 transition-colors leading-none cursor-pointer">&times;</button>

      <h2 id="notification-title" class="text-2xl font-extrabold text-orange-950 uppercase tracking-widest mb-4">Message</h2>
      <p id="notification-message" class="text-orange-950 font-medium text-lg mb-6"></p>

      <button type="button" id="notification-btn" class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-extrabold text-2xl py-3 px-6 rounded-md border-4 border-orange-950 shadow-[4px_4px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 hover:shadow-[2px_2px_0px_rgba(0,0,0,1)] tracking-widest cursor-pointer">OK</button>
    </div>
  </div>
</div>