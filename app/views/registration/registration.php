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
          <a href="/login" class="register-return-btn" data-target="login-view">
            Back to Login
          </a>
        </div>

      </div>

    </section>
  </form>
  <div id="terms-modal" class="register-hidden register-modal-overlay">
    <div class="register-terms-modal-box">

      <div class="register-terms-modal-head">
        <h2 class="register-terms-modal-title">Terms and Conditions</h2>
        <button type="button" id="close-terms-btn" class="register-modal-close">&times;</button>
      </div>

      <div class="register-terms-modal-body">
        <p>Welcome to Troxan! By registering an account, you agree to abide by the following rules:</p>
        <ul class="register-terms-modal-list">
          <li><strong>Be respectful:</strong> No toxicity, hate speech, or harassment of other players.</li>
          <li><strong>No cheating:</strong> Exploiting bugs, using third-party software, or hacking will result in a permanent ban.</li>
          <li><strong>Account security:</strong> You are responsible for keeping your password safe. We will never ask for your password.</li>
          <li><strong>Fair play:</strong> Keep the competition clean and enjoy the game!</li>
        </ul>
        <p class="register-terms-modal-note">These terms are subject to change. Please review them periodically.</p>
      </div>

      <div class="register-terms-modal-foot">
        <button type="button" id="accept-terms-btn" class="register-terms-modal-btn">Understood</button>
      </div>
    </div>
  </div>

  <!-- Notification Modal -->
  <div id="notification-modal" class="register-hidden register-modal-overlay">
    <div class="register-modal-box">
      <button type="button" id="close-notification-btn" class="register-modal-close">&times;</button>

      <h2 id="notification-title" class="register-modal-title">Message</h2>
      <p id="notification-message" class="register-modal-text"></p>

      <button type="button" id="notification-btn" class="register-modal-btn-primary">OK</button>
    </div>
  </div>
</div>