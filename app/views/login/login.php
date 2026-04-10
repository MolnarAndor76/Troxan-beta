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
          <a href="/registration" class="login-secondary-btn" data-target="register-view">
            Register now
          </a>
        </div>
      </div>

    </section>
  </form>
  <div id="forgot-pw-modal" class="login-hidden login-modal-overlay">
    <div class="login-modal-box">

      <button type="button" id="close-forgot-btn" class="login-modal-close">&times;</button>

      <h2 class="login-modal-title">Reset Password</h2>
      <p class="login-modal-text">Enter your email address and we'll send you a link to reset your password.</p>

      <form id="forgot-pw-form" class="login-modal-form">
        <input type="email" name="forgot_email" required placeholder="example@asd.com" class="login-modal-input">

        <div id="forgot-pw-error" class="login-modal-error"></div>

        <button type="submit" class="login-modal-primary-btn">SEND LINK</button>
      </form>

    </div>
  </div>

  <!-- Notification Modal -->
  <div id="notification-modal" class="login-hidden login-modal-overlay">
    <div class="login-modal-box">
      <button type="button" id="close-notification-btn" class="login-modal-close">&times;</button>

      <h2 id="notification-title" class="login-modal-title">Message</h2>
      <p id="notification-message" class="login-modal-text"></p>

      <button type="button" id="notification-btn" class="login-modal-primary-btn">OK</button>
    </div>
  </div>

  <!-- Verification Code Input Modal -->
  <div id="code-input-modal" class="login-hidden login-modal-overlay">
    <div class="login-modal-box">
      <button type="button" id="close-code-input-btn" class="login-modal-close">&times;</button>

      <h2 id="code-input-title" class="login-modal-title">Verify Email</h2>
      <p class="login-modal-text">Please enter the verification code sent to your email:</p>

      <input type="text" id="code-input-field" placeholder="000000" maxlength="6" class="login-modal-code-input">

      <div id="code-input-error" class="login-modal-error login-modal-error-spaced"></div>

      <div class="login-modal-btn-row">
        <button type="button" id="code-input-cancel-btn" class="login-modal-btn-cancel">Cancel</button>
        <button type="button" id="code-input-submit-btn" class="login-modal-btn-submit">Verify</button>
      </div>
    </div>
  </div>

  <!-- Force Password Change Modal -->
  <div id="force-password-change-modal" class="login-hidden login-modal-overlay">
    <div class="login-modal-box">
      <button type="button" id="close-force-pw-btn" class="login-modal-close">&times;</button>

      <h2 id="force-pw-title" class="login-modal-title">Change Password</h2>
      <p class="login-modal-text">Your password has expired. Please create a new password to continue.</p>

      <div class="login-modal-form">
        <input type="password" id="force-new-password" placeholder="New Password" class="login-modal-input">

        <input type="password" id="force-confirm-password" placeholder="Confirm Password" class="login-modal-input">

        <div id="force-pw-error" class="login-modal-error"></div>

        <div class="login-modal-btn-row">
          <button type="button" id="force-pw-cancel-btn" class="login-modal-btn-cancel">Cancel</button>
          <button type="button" id="force-pw-submit-btn" class="login-modal-btn-submit">Update Password</button>
        </div>
      </div>
    </div>
  </div>
</div>