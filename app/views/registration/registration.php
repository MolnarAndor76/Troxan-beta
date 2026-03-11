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
          <button type="button" class="register-return-btn" data-target="login-view">
            Back to Login
          </button>
        </div>

      </div>

    </section>
  </form>
</div>