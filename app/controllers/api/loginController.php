<?php
// Login controller - Handles user authentication and password reset
function getContent() {
    ob_start(); require VIEWS . 'login/login.php'; $buffer = ob_get_clean();
    json_response(["html" => $buffer, "status" => "success"], 200);
}

// 1. Simple login function
function loginUser($input) {
    global $pdo;
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        json_response(["status" => "error", "message" => "All fields are required!"], 400);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.username, u.password, u.is_verified, u.has_temp_password, u.temp_password_expires, r.role_name, a.avatar_picture 
            FROM `User` u
            LEFT JOIN `Avatars` a ON u.avatar_id = a.id
            LEFT JOIN `Roles` r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                json_response(["status" => "error", "code" => "not_verified", "message" => "Your account is not verified yet. Please check your email and enter the verification code."], 403);
            }
            
            // Check if user has temporary password that needs to be changed
            if (isset($user['has_temp_password']) && $user['has_temp_password'] == 1) {
                // Check if temp password expired
                if (!empty($user['temp_password_expires']) && strtotime($user['temp_password_expires']) < time()) {
                    json_response(["status" => "error", "code" => "temp_password_expired", "message" => "Your temporary password has expired. Please request a new password reset."], 403);
                }
                // Return response indicating forced password change required
                json_response(["status" => "error", "code" => "force_password_change", "message" => "You must change your password before accessing your account.", "user_id" => $user['user_id'], "username" => $user['username']], 403);
            }
            
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role_name'] = $user['role_name'] ?? 'Player';
            $_SESSION['logged_in'] = true;
            session_write_close();

            $avatar_base64 = 'https://picsum.photos/id/1025/200/200';
            if (!empty($user['avatar_picture'])) {
                $avatar_base64 = 'data:image/jpeg;base64,' . base64_encode($user['avatar_picture']);
            }
            json_response(["status" => "success", "user" => ["username" => $user['username'], "avatar" => $avatar_base64]], 200);
        } else {
            json_response(["status" => "error", "message" => "Invalid email or password!"], 401);
        }
    } catch (Throwable $e) { json_response(["status" => "error", "message" => $e->getMessage()], 500); }
}

// 4. Forced password change for temporary password
function forcePasswordChange($input) {
    global $pdo;
    $user_id = $input['user_id'] ?? '';
    $old_password = $input['old_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    if (empty($user_id) || empty($old_password) || empty($new_password) || empty($confirm_password)) {
        json_response(["status" => "error", "message" => "All fields are required!"], 400);
    }

    try {
        // Validate new passwords match
        if ($new_password !== $confirm_password) {
            json_response(["status" => "error", "message" => "Passwords do not match!"], 400);
        }

        // Validate password strength (minimum 8 characters)
        if (strlen($new_password) < 8) {
            json_response(["status" => "error", "message" => "Password must be at least 8 characters long!"], 400);
        }

        // Fetch user to verify temporary password
        $stmt = $pdo->prepare("SELECT user_id, username, email, password, has_temp_password, temp_password_expires FROM `User` WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_response(["status" => "error", "message" => "User not found!"], 404);
        }

        // Verify the temporary password
        if (!password_verify($old_password, $user['password'])) {
            json_response(["status" => "error", "message" => "Invalid temporary password!"], 401);
        }

        // Check if temp password is not expired
        if (empty($user['temp_password_expires']) || strtotime($user['temp_password_expires']) < time()) {
            json_response(["status" => "error", "message" => "Temporary password has expired!"], 403);
        }

        // Check user is marked as having temporary password
        if ($user['has_temp_password'] != 1) {
            json_response(["status" => "error", "message" => "This operation is not applicable!"], 400);
        }

        // Hash the new password
        $newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password and clear temporary password flags
        $updateStmt = $pdo->prepare("UPDATE `User` SET password = ?, has_temp_password = 0, temp_password_expires = NULL WHERE user_id = ?");
        $updateStmt->execute([$newHashedPassword, $user_id]);

        // Send confirmation email
        require_once realpath(__DIR__ . '/../../PHPMailer/PHPMailer.php');
        require_once realpath(__DIR__ . '/../../PHPMailer/SMTP.php');
        require_once realpath(__DIR__ . '/../../PHPMailer/Exception.php');

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'troxangame@gmail.com';
        $mail->Password = 'rblg gswv gwqp yhzy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('troxangame@gmail.com', 'Troxan Game');
        $mail->addAddress($user['email']);
        $mail->Subject = 'Password Update Confirmation - Troxan';
        $mail->isHTML(true);

        $emailBody = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                    .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                    .header { color: #333; margin-bottom: 20px; }
                    .content { color: #666; line-height: 1.6; }
                    .highlight { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
                    .footer { color: #999; font-size: 12px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Password Successfully Changed</h2>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>{$user['username']}</strong>,</p>
                        <p>Your Troxan account password has been successfully updated. Your new password is now active.</p>
                        <div class='highlight'>
                            <strong>✓ Password changed successfully</strong><br>
                            You can now log in with your new password.
                        </div>
                        <p><strong>For your security:</strong></p>
                        <ul>
                            <li>Your old temporary password is no longer valid</li>
                            <li>Keep your new password confidential</li>
                            <li>Never share your password with anyone</li>
                        </ul>
                    </div>
                    <div class='footer'>
                        <p>If you did not make this change or need help, please contact our support team.</p>
                    </div>
                </div>
            </body>
        </html>
        ";

        $mail->Body = $emailBody;
        $mail->send();

        json_response(["status" => "success", "message" => "Password changed successfully!"], 200);
    } catch (Throwable $e) {
        json_response(["status" => "error", "message" => $e->getMessage()], 500);
    }
}

// 5. Forgot password function
function forgotPassword($input) {
    global $pdo;
    $email = trim($input['forgot_email'] ?? '');

    // PHP Email validation (Format check)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(["status" => "error", "message" => "Invalid email"], 400);
    }

    try {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT user_id, username FROM `User` WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Email not found in database
            json_response(["status" => "error", "message" => "Invalid email"], 404);
        }

        // Generate temporary password (random 12-character string)
        $tempPassword = bin2hex(random_bytes(6)); // 12 character hex string
        $hashedTempPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Mark user as having temporary password (expires in 24 hours)
        $tempExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $updateStmt = $pdo->prepare("UPDATE `User` SET has_temp_password = 1, temp_password_expires = ? WHERE user_id = ?");
        $updateStmt->execute([$tempExpires, $user['user_id']]);
        
        // Store actual temp password for verification at login (we use password_verify so we need the hashed version in session maybe, or just store it temporarily)
        // Actually, we'll store the hashed version in a separate column
        $updatePassStmt = $pdo->prepare("UPDATE `User` SET password = ? WHERE user_id = ?");
        $updatePassStmt->execute([$hashedTempPassword, $user['user_id']]);

        // Send email with temporary password
        $mailerPath = __DIR__ . '/../../mailer.php';
        if (file_exists($mailerPath)) {
            require_once $mailerPath;
            if (function_exists('sendTroxanMail')) {
                $subject = 'Troxan - Password Reset';
                $emailBody = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>"
                           . "<h2>Password Reset Request</h2>"
                           . "<p>Hello {$user['username']},</p>"
                           . "<p>We received a request to reset your Troxan account password.</p>"
                           . "<p>Your temporary password is:</p>"
                           . "<p style='font-size: 18px; font-weight: bold; background-color: #f0f0f0; padding: 10px; border-left: 4px solid #d97706;'>"
                           . "{$tempPassword}"
                           . "</p>"
                           . "<p><strong>Important:</strong></p>"
                           . "<ul>"
                           . "<li>This temporary password is valid for 24 hours only.</li>"
                           . "<li>Log in with this temporary password.</li>"
                           . "<li>You must change your password before accessing your account.</li>"
                           . "</ul>"
                           . "<p style='font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px;'>"
                           . "If you did not request this password reset, please ignore this email."
                           . "</p>"
                           . "</div>";
                @sendTroxanMail($email, $subject, $emailBody);
            }
        }

        json_response(["status" => "success", "message" => "Password reset link sent to your email!"], 200);

    } catch (Throwable $e) { 
        json_response(["status" => "error", "message" => "Server error"], 500); 
    }
}

// Router logic (RESTful endpoint behavior)
$input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

switch ($data["method"]) {
    case 'GET':
        getContent();
        break;

    case 'POST':
        // POST /api/login = authenticate
        if (isset($input['action']) && $input['action'] === 'forgot_password') {
            forgotPassword($input);
        } elseif (isset($input['action']) && $input['action'] === 'force_password_change') {
            forcePasswordChange($input);
        } else {
            loginUser($input);
        }
        break;

    case 'PUT':
        // PUT /api/login = password update action (restful form)
        if (isset($input['action']) && $input['action'] === 'force_password_change') {
            forcePasswordChange($input);
        } else {
            method_not_allowed();
        }
        break;

    case 'DELETE':
        // DELETE /api/login is not used; use /api/logout
        json_response(["status" => "error", "message" => "Use /api/logout to end session."], 405);
        break;

    default:
        method_not_allowed();
        break;
}
?>