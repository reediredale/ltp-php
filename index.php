<?php
// Configuration
define('SITE_URL', 'http://ltp.test');
define('SITE_NAME', 'Leads to Profit');

// Simple routing for nginx
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Define valid routes (for pages and sitemap generation)
$routes = [
    '/' => [
        'title' => 'Digital Marketing & Lead Generation Experts',
        'description' => 'Transform your digital marketing with expert lead generation, Meta ads, Google ads, and conversion rate optimisation services.',
        'template' => 'home'
    ],
    '/services' => [
        'title' => 'Our Services - Lead Generation & Digital Marketing',
        'description' => 'Comprehensive digital marketing services including lead generation, Meta ads, Google ads, conversion optimisation, and email marketing.',
        'template' => 'services'
    ],
    '/about' => [
        'title' => 'About Us - Your Digital Marketing Growth Partners',
        'description' => 'Learn why businesses choose Leads to Profit for data-driven digital marketing that delivers measurable results and sustainable growth.',
        'template' => 'about'
    ],
    '/contact' => [
        'title' => 'Contact Us - Get Your Free Consultation',
        'description' => 'Ready to grow your business? Contact Leads to Profit today for a free consultation on lead generation and digital marketing strategies.',
        'template' => 'contact',
        'priority' => '0.9',
        'changefreq' => 'monthly'
    ],
    '/thank-you' => [
        'title' => 'Thank You - Message Received',
        'description' => 'Thank you for contacting Leads to Profit. We will get back to you within 24 hours.',
        'template' => 'thank-you',
        'priority' => '0.3',
        'changefreq' => 'monthly'
    ]
];

// Add default SEO metadata for routes if not set
foreach ($routes as $path => &$route) {
    if (!isset($route['priority'])) {
        $route['priority'] = $path === '/' ? '1.0' : '0.8';
    }
    if (!isset($route['changefreq'])) {
        $route['changefreq'] = $path === '/' ? 'weekly' : 'monthly';
    }
}
unset($route);

// Handle sitemap.xml - Auto-generate from routes
if ($request_uri === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($routes as $path => $data) {
        echo '    <url>' . "\n";
        echo '        <loc>' . SITE_URL . $path . '</loc>' . "\n";
        echo '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '        <changefreq>' . $data['changefreq'] . '</changefreq>' . "\n";
        echo '        <priority>' . $data['priority'] . '</priority>' . "\n";
        echo '    </url>' . "\n";
    }

    echo '</urlset>';
    exit;
}

// Handle form submission
if ($request_method === 'POST' && $request_uri === '/contact') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        header('Location: /contact?error=missing');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /contact?error=invalid_email');
        exit;
    }

    // Prepare email
    $to = 'reed@reediredale.com';
    $subject = 'New Contact Form Submission - Leads to Profit';

    $emailBody = "New contact form submission from Leads to Profit website\n\n";
    $emailBody .= "Name: " . $name . "\n";
    $emailBody .= "Email: " . $email . "\n";
    $emailBody .= "Phone: " . ($phone ?: 'Not provided') . "\n\n";
    $emailBody .= "Message:\n" . $message . "\n\n";
    $emailBody .= "---\n";
    $emailBody .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
    $emailBody .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

    // Email headers
    $headers = "From: noreply@leadstoprofit.com\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email
    if (mail($to, $subject, $emailBody, $headers)) {
        header('Location: /thank-you');
        exit;
    } else {
        error_log('Failed to send contact form email');
        header('Location: /contact?error=server');
        exit;
    }
}

// Check if route exists
if (!isset($routes[$request_uri])) {
    http_response_code(404);
    $page_data = [
        'title' => '404 Not Found',
        'description' => 'Page not found',
        'template' => '404'
    ];
} else {
    $page_data = $routes[$request_uri];
}

// Helper function to check if link is active
function is_active($path) {
    global $request_uri;
    return $request_uri === $path ? 'active' : '';
}

// Start output buffering for content
ob_start();

// Load the appropriate template
switch ($page_data['template']) {
    case 'home':
        ?>
        <section class="hero">
            <div class="hero-content">
                <h1>Market the S*** Out of Whatever the F*** You're Selling Online</h1>
                <p>We specialise in high-converting digital marketing strategies that transform leads into profit. From Meta and Google Ads to conversion optimisation and full web development, we handle all the tech so you can focus on growth.</p>
                <a href="/contact" class="cta-button">Get Your Free Consultation</a>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <h2 class="section-title">What We Do</h2>
                <p class="section-subtitle">Digital marketing services that drive real results</p>

                <div class="services-grid">
                    <div class="service-card">
                        <span class="service-icon">🎯</span>
                        <h3>Lead Generation</h3>
                        <p>Strategic campaigns that fill your pipeline with high-quality prospects ready to convert.</p>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">📱</span>
                        <h3>Meta Ads</h3>
                        <p>Facebook and Instagram advertising optimised for maximum ROI and engagement.</p>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">🔍</span>
                        <h3>Google Ads</h3>
                        <p>Search and display campaigns that capture high-intent buyers actively searching.</p>
                    </div>
                </div>

                <div class="cta-center">
                    <a href="/services" class="cta-button-secondary">View All Services</a>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="stats-container">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Campaigns Launched</p>
                </div>
                <div class="stat-item">
                    <h3>95%</h3>
                    <p>Client Retention</p>
                </div>
                <div class="stat-item">
                    <h3>3.2x</h3>
                    <p>Average ROI</p>
                </div>
                <div class="stat-item">
                    <h3>24h</h3>
                    <p>Response Time</p>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to Transform Your Marketing?</h2>
                <p>Let's discuss how we can help grow your business with data-driven digital marketing strategies.</p>
                <a href="/contact" class="cta-button">Start Your Free Consultation</a>
            </div>
        </section>
        <?php
        break;

    case 'services':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Our Services</h1>
                <p>Comprehensive digital marketing solutions designed to grow your business</p>
            </div>
        </section>

        <section class="services-detailed">
            <div class="container">
                <div class="services-grid">
                    <div class="service-card">
                        <span class="service-icon">🎯</span>
                        <h3>Lead Generation</h3>
                        <p>Strategic campaigns that attract high-quality leads ready to convert. We build systems that consistently fill your pipeline with prospects who want what you offer. We handle all web development and technical implementation.</p>
                        <ul class="service-features">
                            <li>Landing page optimisation</li>
                            <li>Lead magnet creation</li>
                            <li>Multi-channel campaigns</li>
                            <li>Lead scoring and qualification</li>
                            <li>Full technical setup & integration</li>
                        </ul>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">📱</span>
                        <h3>Meta Ads</h3>
                        <p>Facebook and Instagram advertising that reaches your ideal customers. Data-driven campaigns optimised for maximum ROI and engagement. We manage all tracking pixels, conversion APIs, and technical setup.</p>
                        <ul class="service-features">
                            <li>Audience research and targeting</li>
                            <li>Creative development and testing</li>
                            <li>Campaign optimisation</li>
                            <li>Retargeting strategies</li>
                            <li>Pixel & conversion API setup</li>
                        </ul>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">🔍</span>
                        <h3>Google Ads</h3>
                        <p>Search and display advertising that captures high-intent buyers. Get found by customers actively searching for your products or services. We handle all tracking, conversion setup, and technical integration.</p>
                        <ul class="service-features">
                            <li>Keyword research and strategy</li>
                            <li>Search and display campaigns</li>
                            <li>Shopping ads setup</li>
                            <li>Performance Max campaigns</li>
                            <li>Google Analytics & Tag Manager setup</li>
                        </ul>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">📊</span>
                        <h3>Conversion Rate Optimisation</h3>
                        <p>Turn more visitors into customers with data-backed optimisation. We analyse, test, and improve every step of your customer journey. Our team manages all website development and technical implementation required to maximise conversions.</p>
                        <ul class="service-features">
                            <li>Conversion funnel analysis</li>
                            <li>A/B and multivariate testing</li>
                            <li>User experience optimisation</li>
                            <li>Heat mapping and analytics</li>
                            <li>Website development & coding</li>
                        </ul>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">✉️</span>
                        <h3>Email Marketing</h3>
                        <p>Nurture relationships and drive sales with targeted email campaigns. Automated sequences that convert subscribers into loyal customers. We handle all platform setup, integrations, and technical configuration.</p>
                        <ul class="service-features">
                            <li>Email automation setup</li>
                            <li>Segmentation and personalisation</li>
                            <li>Newsletter campaigns</li>
                            <li>Drip sequence development</li>
                            <li>Platform integration & API setup</li>
                        </ul>
                    </div>

                    <div class="service-card">
                        <span class="service-icon">💡</span>
                        <h3>Strategy & Consulting</h3>
                        <p>Expert guidance to align your marketing with business goals. We develop comprehensive strategies that deliver sustainable growth. From strategy to execution, we manage all web development, integrations, and technical infrastructure needed.</p>
                        <ul class="service-features">
                            <li>Marketing audit and analysis</li>
                            <li>Strategy development</li>
                            <li>Competitor research</li>
                            <li>Performance consulting</li>
                            <li>Full-stack technical implementation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Let's Build Your Growth Strategy</h2>
                <p>Schedule a free consultation to discuss which services are right for your business.</p>
                <a href="/contact" class="cta-button">Get Started Today</a>
            </div>
        </section>
        <?php
        break;

    case 'about':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>About Leads to Profit</h1>
                <p>Your partners in digital marketing growth</p>
            </div>
        </section>

        <section class="about-content-section">
            <div class="container">
                <div class="about-grid">
                    <div class="about-main">
                        <h2>Why Choose Leads to Profit?</h2>
                        <p>
                            We're not just another digital marketing agency. We're growth partners who understand that every click, every lead, and every conversion matters to your bottom line.
                        </p>
                        <p>
                            Our team combines creative strategy with data-driven execution to deliver campaigns that don't just look good—they perform. We specialise in the metrics that matter: cost per lead, conversion rate, and return on ad spend.
                        </p>
                        <p>
                            Whether you're looking to scale your Meta and Google Ads, optimise your conversion funnel, or build an email marketing system that nurtures leads on autopilot, we have the expertise to make it happen. We manage all web development, integrations, and technical infrastructure—so you get a complete solution without juggling multiple vendors.
                        </p>
                        <p>
                            <strong>Let's turn your marketing into a profit centre.</strong>
                        </p>
                    </div>

                    <div class="about-sidebar">
                        <div class="value-box">
                            <h3>Our Approach</h3>
                            <ul class="value-list">
                                <li><strong>Data-Driven:</strong> Every decision backed by analytics</li>
                                <li><strong>Results-Focused:</strong> We optimise for ROI, not vanity metrics</li>
                                <li><strong>Transparent:</strong> Clear reporting and open communication</li>
                                <li><strong>Agile:</strong> Fast testing and continuous improvement</li>
                            </ul>
                        </div>

                        <div class="value-box">
                            <h3>What We Believe</h3>
                            <p>Marketing should be measurable, scalable, and profitable. We believe in building sustainable growth systems, not one-off campaigns.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="stats-container">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Campaigns Launched</p>
                </div>
                <div class="stat-item">
                    <h3>95%</h3>
                    <p>Client Retention</p>
                </div>
                <div class="stat-item">
                    <h3>3.2x</h3>
                    <p>Average ROI</p>
                </div>
                <div class="stat-item">
                    <h3>24h</h3>
                    <p>Response Time</p>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to Work Together?</h2>
                <p>Let's discuss how we can help achieve your marketing goals.</p>
                <a href="/contact" class="cta-button">Get In Touch</a>
            </div>
        </section>
        <?php
        break;

    case 'contact':
        $error = $_GET['error'] ?? '';
        $errorMessage = '';

        if ($error === 'missing') {
            $errorMessage = 'Please fill in all required fields.';
        } elseif ($error === 'invalid_email') {
            $errorMessage = 'Please enter a valid email address.';
        } elseif ($error === 'server') {
            $errorMessage = 'An error occurred. Please try again later.';
        }
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Let's Grow Your Business</h1>
                <p>Ready to see real results from your digital marketing? Get in touch for a free consultation.</p>
            </div>
        </section>

        <section class="contact-section">
            <div class="container-narrow">
                <?php if ($errorMessage): ?>
                    <div class="form-message error" style="display: block; margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/contact" class="contact-form">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="message">Tell us about your project *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>
        </section>

        <section class="contact-info">
            <div class="container">
                <div class="info-grid">
                    <!-- <div class="info-card">
                        <h3>📧 Email</h3>
                        <p>hello@leadstoprofit.com</p>
                    </div> -->
                    <div class="info-card">
                        <h3>⏰ Response Time</h3>
                        <p>Within 24 hours</p>
                    </div>
                    <div class="info-card">
                        <h3>💬 Free Consultation</h3>
                        <p>30-minute strategy call</p>
                    </div>
                </div>
            </div>
        </section>
        <?php
        break;

    case 'thank-you':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Thank You!</h1>
                <p>We've received your message and will get back to you within 24 hours.</p>
            </div>
        </section>

        <section class="content-section">
            <div class="container">
                <div class="thank-you-content">
                    <div style="text-align: center; padding: 3rem 2rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">✓</div>
                        <h2 style="color: var(--primary-green); margin-bottom: 1rem;">Message Received</h2>
                        <p style="font-size: 1.1rem; color: var(--text-gray); margin-bottom: 2rem;">
                            Thank you for reaching out to Leads to Profit. One of our team members will review your message and respond within 24 hours.
                        </p>
                        <p style="font-size: 1rem; color: var(--text-gray); margin-bottom: 2rem;">
                            In the meantime, feel free to explore our services or learn more about how we help businesses grow.
                        </p>
                        <div class="cta-center" style="margin-top: 2rem;">
                            <a href="/services" class="cta-button-secondary" style="margin-right: 1rem;">View Our Services</a>
                            <a href="/" class="cta-button-secondary">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        break;

    case '404':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>404 - Page Not Found</h1>
                <p>Sorry, the page you're looking for doesn't exist.</p>
            </div>
        </section>

        <section class="content-section">
            <div class="container">
                <div class="error-content">
                    <p>The page you requested could not be found. Please check the URL or navigate back to our homepage.</p>
                    <div class="cta-center">
                        <a href="/" class="cta-button">Return to Homepage</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        break;
}

$content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_data['description']); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Leads to Profit">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $request_uri; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_data['title']); ?> | <?php echo SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_data['description']); ?>">

    <title><?php echo htmlspecialchars($page_data['title']); ?> | <?php echo SITE_NAME; ?></title>

    <link rel="canonical" href="<?php echo SITE_URL . $request_uri; ?>">

    <style>
        /* CSS Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-green: #0fc53d;
            --dark-green: #0ca032;
            --light-green: #e6f9ec;
            --accent-green: #0fc53d;
            --text-dark: #1a1a1a;
            --text-gray: #666666;
            --white: #ffffff;
            --background: #fafafa;
        }

    @font-face {
    font-family: 'Futura Condensed';
    font-style: normal;
    font-weight: normal;
    src: local('Futura Condensed'), url('Futura Condensed Extra Bold.woff') format('woff');
    }

    @font-face {
    font-family: 'Tacticans Bold';
    font-style: normal;
    font-weight: normal;
    src: local('Tacticans Bold'), url('tacticsans-bld.otf') format('opentype');
    }

    @font-face {
    font-family: 'DINPro';
    font-style: normal;
    font-weight: 400;
    src: url('DINPro-Regular.woff2') format('woff2');
    font-display: swap;
    }

    @font-face {
    font-family: 'DINPro';
    font-style: normal;
    font-weight: 700;
    src: url('DINPro-Bold.woff2') format('woff2');
    font-display: swap;
    }

    @font-face {
    font-family: 'DINPro';
    font-style: normal;
    font-weight: 900;
    src: url('DINPro-Black.woff2') format('woff2');
    font-display: swap;
    }

        body {
            font-family: 'DINPro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'DINPro', sans-serif;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .container-narrow {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Header & Navigation */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            line-height: 1;
            font-weight: bold;
            color: var(--primary-green);
            text-decoration: none;
            font-family: 'Tacticans Bold', 'Arial Narrow', Arial, sans-serif;
            text-transform: uppercase;
                letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-green);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--primary-green);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            z-index: 1001;
            width: 30px;
            height: 24px;
            position: relative;
        }

        .menu-toggle span {
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--text-dark);
            border-radius: 3px;
            transition: all 0.3s ease;
            position: absolute;
        }

        .menu-toggle span:nth-child(1) {
            top: 0;
        }

        .menu-toggle span:nth-child(2) {
            top: 50%;
            transform: translateY(-50%);
        }

        .menu-toggle span:nth-child(3) {
            bottom: 0;
        }

        .menu-toggle.active span:nth-child(1) {
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            bottom: 50%;
            transform: translateY(50%) rotate(-45deg);
        }

        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: 6rem 2rem;
            text-align: center;
        }

        .hero-content {
            max-width: 1100px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: clamp(2rem, 8vw, 5rem);
            letter-spacing: -0.045em;
            margin-bottom: 1.5rem;
            color: var(--white);
            text-transform: uppercase;
        }

        .hero p {
            font-size: clamp(1rem, 4vw, 1.3rem);
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: 4rem 2rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: clamp(1.75rem, 7vw, 4.5rem);
            margin-bottom: 1rem;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: -0.045em;
        }

        .page-header p {
            font-size: clamp(1rem, 3.5vw, 1.2rem);
            opacity: 0.95;
        }

        /* Buttons */
        .cta-button {
            display: inline-block;
            background-color: var(--white);
            color: var(--primary-green);
            padding: 1rem 2.5rem;
            text-decoration: none;
            border-radius: 50px;
            font-family: 'DINPro', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            transition: all 0.3s;
            border: 2px solid var(--white);
        }

        .cta-button:hover {
            background-color: transparent;
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .cta-button-secondary {
            display: inline-block;
            background-color: var(--primary-green);
            color: var(--white);
            padding: 1rem 2.5rem;
            text-decoration: none;
            border-radius: 50px;
            font-family: 'DINPro', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            transition: all 0.3s;
            border: 2px solid var(--primary-green);
            letter-spacing: 1px;
        }

        .cta-button-secondary:hover {
            background-color: var(--dark-green);
            border-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,166,81,0.3);
        }

        .cta-center {
            text-align: center;
            margin-top: 3rem;
        }

        /* Services Section */
        .services-preview,
        .services-detailed {
            padding: 5rem 2rem;
        }

        .section-title {
            text-align: center;
            font-size: clamp(1.75rem, 6vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--primary-green);
            text-transform: uppercase;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-gray);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .service-card {
            background: var(--white);
            padding: 2.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--primary-green);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,166,81,0.2);
        }

        .service-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        .service-card h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--primary-green);
        }

        .service-card p {
            color: var(--text-gray);
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .service-features {
            list-style: none;
            margin-top: 1.5rem;
        }

        .service-features li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
            color: var(--text-gray);
        }

        .service-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-green);
            font-weight: bold;
        }

        /* Stats Section */
        .stats {
            background-color: var(--light-green);
            padding: 4rem 2rem;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            color: var(--text-gray);
            font-size: 1.1rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: 5rem 2rem;
            text-align: center;
        }

        .cta-section h2 {
            font-size: clamp(1.75rem, 6vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--white);
            text-transform: uppercase;
        }

        .cta-section p {
            font-size: clamp(1rem, 3.5vw, 1.2rem);
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        /* About Page */
        .about-content-section {
            padding: 5rem 2rem;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }

        .about-main h2 {
            font-size: 2rem;
            color: var(--primary-green);
            margin-bottom: 1.5rem;
        }

        .about-main p {
            color: var(--text-gray);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .value-box {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .value-box h3 {
            color: var(--primary-green);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .value-box p {
            color: var(--text-gray);
            line-height: 1.7;
        }

        .value-list {
            list-style: none;
        }

        .value-list li {
            padding: 0.5rem 0;
            color: var(--text-gray);
            line-height: 1.6;
        }

        .value-list strong {
            color: var(--primary-green);
        }

        /* Contact Page */
        .contact-section {
            padding: 3rem 2rem;
        }

        .contact-form {
            background: var(--white);
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-button {
            background-color: var(--primary-green);
            color: var(--white);
            padding: 1rem 3rem;
            border: none;
            border-radius: 50px;
            font-family: 'DINPro', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .submit-button:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .submit-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .form-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 5px;
            text-align: center;
            display: none;
        }

        .form-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .contact-info {
            padding: 3rem 2rem;
            background-color: var(--light-green);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .info-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-green);
        }

        .info-card p {
            color: var(--text-gray);
        }

        /* Content Section */
        .content-section {
            padding: 5rem 2rem;
        }

        .error-content {
            text-align: center;
            padding: 3rem 2rem;
        }

        .error-content p {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            background-color: var(--text-dark);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        footer p {
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                bottom: 0;
                flex-direction: column;
                background-color: var(--white);
                text-align: center;
                box-shadow: 0 10px 27px rgba(0,0,0,0.05);
                padding: 2rem 0;
                gap: 0;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                overflow-y: auto;
                z-index: 1000;
            }

            .nav-links.active {
                transform: translateX(0);
            }

            .nav-links li {
                border-bottom: 1px solid #f0f0f0;
            }

            .nav-links a {
                display: block;
                padding: 1.2rem 2rem;
            }

            .nav-links a.active::after {
                display: none;
            }

            .menu-toggle {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            body.menu-open {
                overflow: hidden;
            }

            .about-grid {
                grid-template-columns: 1fr;
            }

            .contact-form {
                padding: 2rem 1.5rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="/" class="logo">LEADS TO PROFIT</a>
            <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-links">
                <li><a href="/" class="<?php echo is_active('/'); ?>">Home</a></li>
                <li><a href="/services" class="<?php echo is_active('/services'); ?>">Services</a></li>
                <li><a href="/about" class="<?php echo is_active('/about'); ?>">About</a></li>
                <li><a href="/contact" class="<?php echo is_active('/contact'); ?>">Contact</a></li>
            </ul>
        </nav>
    </header>

    <div class="menu-overlay"></div>

    <main>
        <?php echo $content; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>

    <script src="/script.js"></script>
</body>
</html>
