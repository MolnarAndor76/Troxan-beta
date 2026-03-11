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
          <button type="button" class="login-secondary-btn" data-target="register-view">
            Register now
          </button>
        </div>
      </div>

    </section>
  </form>
</div>