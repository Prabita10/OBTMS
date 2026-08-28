<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success = false;
$error = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

    // Validation
    if (empty($fullname) || empty($email) || empty($subject) || empty($message)) {
        $error = true;
        $errorMessage = 'Please fill in all required fields.';
    } 
    // Full Name Validation: at least 3 characters (letters and spaces only)
    elseif (strlen($fullname) < 3) {
        $error = true;
        $errorMessage = 'Full name must be at least 3 characters.';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $fullname)) {
        $error = true;
        $errorMessage = 'Full name can only contain letters and spaces.';
    }
    // Email Validation: must end with @gmail.com, @hotmail.com, @outlook.com, @icloud.com, or @yahoo.com
    elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|outlook\.com|icloud\.com|yahoo\.com)$/', $email)) {
        $error = true;
        $errorMessage = 'Email must end with @gmail.com, @hotmail.com, @outlook.com, @icloud.com, or @yahoo.com';
    }
    // Phone Validation: 10 digits starting with 9
    elseif (!empty($phone) && !preg_match('/^9[0-9]{9}$/', $phone)) {
        $error = true;
        $errorMessage = 'Phone number must be 10 digits starting with 9 (e.g., 98XXXXXXXX).';
    } else {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'prabitaadhikari792@gmail.com';
            $mail->Password = 'sshe orxf nxee etgp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPDebug = 0;

            // Recipients
            $mail->setFrom($email, $fullname);
            $mail->addAddress('prabitaadhikari792@gmail.com', 'BusGo Support');
            $mail->addReplyTo($email, $fullname);

            // Content
            $mail->isHTML(false);
            $mail->Subject = "BusGo Contact Form: " . $subject;
            $mail->Body = "Name: $fullname\nEmail: $email\nPhone: $phone\nSubject: $subject\n\nMessage:\n$message";

            $mail->send();
            $success = true;
        } catch (Exception $e) {
            $error = true;
            $errorMessage = "Message could not be sent. Error: {$mail->ErrorInfo}";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BusGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f7f9fc;
        }

        /* HEADER */
        header {
            background-color: #1a2b4c;
            color: #fff;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 5px;
        }

        nav a.btn-login {
            background-color: #007bff;
        }

        nav a.btn-register {
            background-color: #007bff;
        }

        nav a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        /* PAGE BANNER */
        .page-banner {
            background: linear-gradient(135deg, #1a2b4c 0%, #2a4a7a 100%);
            color: #fff;
            padding: 60px 20px;
            text-align: center;
        }

        .page-banner h2 {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .page-banner p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* CONTACT SECTION */
        .contact-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        /* CONTACT INFO */
        .contact-info h3 {
            font-size: 28px;
            color: #1a2b4c;
            margin-bottom: 15px;
        }

        .contact-info>p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-item .icon {
            width: 50px;
            height: 50px;
            background: #e8edf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #007bff;
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-item .text h4 {
            color: #1a2b4c;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .info-item .text p {
            color: #666;
            line-height: 1.6;
        }

        .info-item .text p a {
            color: #007bff;
            text-decoration: none;
        }

        .info-item .text p a:hover {
            text-decoration: underline;
        }

        /* SOCIAL LINKS */
        .social-links {
            margin-top: 30px;
        }

        .social-links h4 {
            color: #1a2b4c;
            margin-bottom: 15px;
        }

        .social-links a {
            display: inline-block;
            width: 45px;
            height: 45px;
            background: #e8edf5;
            border-radius: 50%;
            text-align: center;
            line-height: 45px;
            color: #1a2b4c;
            margin-right: 10px;
            transition: 0.3s;
            font-size: 18px;
        }

        .social-links a:hover {
            background: #007bff;
            color: #fff;
            transform: translateY(-3px);
        }

        /* CONTACT FORM */
        .contact-form {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .contact-form h3 {
            font-size: 24px;
            color: #1a2b4c;
            margin-bottom: 8px;
        }

        .contact-form .sub {
            color: #666;
            margin-bottom: 25px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert i {
            margin-right: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #1a2b4c;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s;
            background: #fafcff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            padding: 14px 40px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        /* MAP SECTION */
        .map-section {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .map-section iframe {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* FAQ SECTION */
        .faq-section {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .faq-section h3 {
            text-align: center;
            font-size: 32px;
            color: #1a2b4c;
            margin-bottom: 40px;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .faq-item {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #007bff;
        }

        .faq-item h4 {
            color: #1a2b4c;
            margin-bottom: 8px;
            font-size: 17px;
        }

        .faq-item p {
            color: #666;
            line-height: 1.6;
            font-size: 14px;
        }

        /* FOOTER */
        footer {
            text-align: center;
            padding: 30px 20px;
            background-color: #1a2b4c;
            color: #fff;
            margin-top: 20px;
        }

        footer .footer-links {
            margin-bottom: 15px;
        }

        footer .footer-links a {
            color: #aaa;
            text-decoration: none;
            margin: 0 12px;
        }

        footer .footer-links a:hover {
            color: #fff;
        }

        /* RESPONSIVE */
        @media(max-width: 992px) {
            .contact-section {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-wrap: wrap;
            }

            header h1 {
                font-size: 20px;
            }

            nav a {
                margin: 0 8px;
                padding: 6px 10px;
                font-size: 14px;
            }

            .page-banner h2 {
                font-size: 28px;
            }

            .contact-form {
                padding: 25px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .map-section iframe {
                height: 250px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>
            <a href="contact.php">Contact Us</a>
            <a href="login.html" class="btn btn-login">Login</a>
            <a href="register.html" class="btn btn-register">Register</a>
        </nav>
    </header>

    <!-- PAGE BANNER -->
    <section class="page-banner">
        <h2>📞 Get in Touch</h2>
        <p>Have questions? We'd love to hear from you. Our team is here to help 24/7.</p>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-section">
        <!-- LEFT: Contact Info -->
        <div class="contact-info">
            <h3>Contact Information</h3>
            <p>Reach out to us through any of the channels below. We respond within 24 hours.</p>

            <div class="info-item">
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="text">
                    <h4>Visit Us</h4>
                    <p>BusGo Head Office<br>Kathmandu Baniyatar, Nepal</p>
                </div>
            </div>

            <div class="info-item">
                <div class="icon"><i class="fas fa-phone-alt"></i></div>
                <div class="text">
                    <h4>Call Us</h4>
                    <p><a href="tel:+97798016109990">+977 9816109990</a><br>Mon - Fri, 9AM - 6PM</p>
                </div>
            </div>

            <div class="info-item">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <div class="text">
                    <h4>Email Us</h4>
                    <p><a href="mailto:prabitaadhikari792@gmail.com">prabitaadhikari792@gmail.com</a><br>We reply within 12
                        hours</p>
                </div>
            </div>

            <div class="info-item">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="text">
                    <h4>Working Hours</h4>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday and Sunday Off</p>
                </div>
            </div>

            <div class="social-links">
                <h4>Follow Us</h4>
                <a href="https://www.facebook.com/profile.php?id=61590781902415"><i class="fab fa-facebook-f"></i></a>
                
            </div>
        </div>

        <!-- RIGHT: Contact Form -->
        <div class="contact-form">
            <h3>Send Us a Message</h3>
            <p class="sub">We'll get back to you as soon as possible.</p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> ✅ Thank you for reaching out! Our team will get back to you within
                    24 hours.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <form id="contactForm" action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullname">Full Name <span style="color:red;">*</span></label>
                        <input type="text" id="fullname" name="fullname" 
                            value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address <span style="color:red;">*</span></label>
                        <input type="email" id="email" name="email" 
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                        placeholder="98XXXXXXXX">
                </div>

                <div class="form-group">
                    <label for="subject">Subject <span style="color:red;">*</span></label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject...</option>
                        <option value="booking" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'booking') ? 'selected' : ''; ?>>Booking Inquiry</option>
                        <option value="payment" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'payment') ? 'selected' : ''; ?>>Payment Issue</option>
                        <option value="cancellation" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'cancellation') ? 'selected' : ''; ?>>Cancellation / Refund</option>
                        <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'complaint') ? 'selected' : ''; ?>>Complaint / Feedback</option>
                        <option value="partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
                        <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message <span style="color:red;">*</span></label>
                    <textarea id="message" name="message" placeholder="Describe your query in detail..."
                        required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </section>

    <!-- MAP SECTION -->
    <section class="map-section">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.865573883593!2d85.3240354!3d27.6921456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19b1c3d0d6c7%3A0x8e5c5e4e0e5e5e5!2sKathmandu%2C%20Nepal!5e0!3m2!1sen!2snp!4v1700000000000"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </section>

    <!-- FAQ SECTION -->
    <section class="faq-section">
        <h3>❓ Frequently Asked Questions</h3>
        <div class="faq-grid">
            <div class="faq-item">
                <h4>How do I book a bus ticket?</h4>
                <p>Simply visit our homepage, enter your travel details, select a bus, and complete the payment. Your
                    ticket will be sent via email.</p>
            </div>
            <div class="faq-item">
                <h4>What payment methods do you accept?</h4>
                <p>We accept all major credit/debit cards, mobile banking (eSewa, Khalti), and bank transfers. All
                    payments are secure and encrypted.</p>
            </div>
            <div class="faq-item">
                <h4>Can I cancel my booking?</h4>
                <p>Yes, you can cancel your booking up to 24 hours before departure for a full refund. Cancellation fees
                    may apply for last-minute changes.</p>
            </div>
            <div class="faq-item">
                <h4>How do I get a refund?</h4>
                <p>Refunds are processed within 3-5 business days to your original payment method. You'll receive a
                    confirmation email once processed.</p>
            </div>
            <div class="faq-item">
                <h4>Is my personal information safe?</h4>
                <p>Absolutely! We use industry-standard encryption and security protocols to protect your data. We never
                    share your information with third parties.</p>
            </div>
            <div class="faq-item">
                <h4>How can I contact customer support?</h4>
                <p>You can reach us via phone, email, or by filling out the contact form on this page. We're here to
                    help 24/7.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        &copy; 2026 BusGo. All rights reserved by Prabita Adhikari.
    </footer>

</body>

</html>