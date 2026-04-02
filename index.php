<?php
// Configuration
define('SITE_URL', 'https://leadstoprofit.com.au');
define('SITE_NAME', 'Leads to Profit');

// Load JSON data for programmatic SEO
$suburbs_data = json_decode(file_get_contents(__DIR__ . '/data/brisbane-suburbs.json'), true);
$marketing_services_data = json_decode(file_get_contents(__DIR__ . '/data/marketing-services.json'), true);
$business_types_data = json_decode(file_get_contents(__DIR__ . '/data/business-types.json'), true);
$service_pillars_data = json_decode(file_get_contents(__DIR__ . '/data/service-pillars.json'), true);
$industry_pillars_data = json_decode(file_get_contents(__DIR__ . '/data/industry-pillars.json'), true);
$blog_posts_data = json_decode(file_get_contents(__DIR__ . '/data/blog-posts.json'), true);

// Create lookup arrays for faster access
$suburbs_by_slug = [];
foreach ($suburbs_data as $suburb) {
    $suburbs_by_slug[$suburb['slug']] = $suburb;
}

$marketing_services_by_slug = [];
foreach ($marketing_services_data as $service) {
    $marketing_services_by_slug[$service['slug']] = $service;
}

$business_types_by_slug = [];
foreach ($business_types_data as $business) {
    $business_types_by_slug[$business['slug']] = $business;
}

$service_pillars_by_slug = [];
foreach ($service_pillars_data as $pillar) {
    $service_pillars_by_slug[$pillar['slug']] = $pillar;
}

$industry_pillars_by_slug = [];
foreach ($industry_pillars_data as $pillar) {
    $industry_pillars_by_slug[$pillar['slug']] = $pillar;
}

$blog_posts_by_slug = [];
foreach ($blog_posts_data as $post) {
    $blog_posts_by_slug[$post['slug']] = $post;
}

// Simple routing for nginx
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Define valid routes (for pages and sitemap generation)
$routes = [
    '/' => [
        'title' => 'Marketing Consulting for $1M+ Home Services Businesses',
        'description' => 'Specialised digital marketing for established home services businesses. Done for you services or advisory support. Pricing models that align with your growth.',
        'template' => 'lp-001'
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
    '/thank-you-consult' => [
        'title' => 'Thank You - Let\'s Grow Your Business',
        'description' => 'Thank you for your interest in working together. Let\'s take a look at your business.',
        'template' => 'thank-you-consult',
        'priority' => '0.3',
        'changefreq' => 'monthly'
    ],
    '/lp-001' => [
        'title' => 'Marketing Consulting for $1M+ Home Services Businesses',
        'description' => 'Specialised digital marketing for established home services businesses. Done for you services or advisory support. Pricing models that align with your growth.',
        'template' => 'lp-001',
        'priority' => '0.8',
        'changefreq' => 'monthly'
    ],
    '/brisbane-home-services-marketing' => [
        'title' => 'Marketing Services for Brisbane Home Services Businesses',
        'description' => 'Marketing services for plumbers, electricians, HVAC companies, and other home services businesses across Brisbane. Google Ads, SEO, Facebook Ads, and more.',
        'template' => 'services-directory',
        'priority' => '0.9',
        'changefreq' => 'weekly'
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

// Handle sitemap.xml - Create sitemap index (splits into multiple files)
if ($request_uri === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');

    // Calculate total URLs and number of sitemaps needed (50,000 URLs per sitemap)
    $static_urls = [];
    foreach ($routes as $path => $data) {
        if (strpos($path, '/lp-') !== 0) {
            $static_urls[] = $path;
        }
    }

    $total_programmatic = count($marketing_services_data) * count($business_types_data);
    $total_service_pillars = count($service_pillars_data);
    $total_industry_pillars = count($industry_pillars_data);
    $total_blog_posts = count($blog_posts_data);
    $total_urls = count($static_urls) + $total_programmatic + $total_service_pillars + $total_industry_pillars + $total_blog_posts;
    $urls_per_sitemap = 50000;
    $num_sitemaps = ceil($total_urls / $urls_per_sitemap);

    // Output sitemap index
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    for ($i = 1; $i <= $num_sitemaps; $i++) {
        echo '    <sitemap>' . "\n";
        echo '        <loc>' . SITE_URL . '/sitemap-' . $i . '.xml</loc>' . "\n";
        echo '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '    </sitemap>' . "\n";
    }

    echo '</sitemapindex>';
    exit;
}

// Handle individual sitemap files (sitemap-1.xml, sitemap-2.xml, etc.)
if (preg_match('/^\/sitemap-(\d+)\.xml$/', $request_uri, $matches)) {
    $sitemap_num = (int)$matches[1];
    $urls_per_sitemap = 50000;
    $offset = ($sitemap_num - 1) * $urls_per_sitemap;

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Collect all URLs first
    $all_urls = [];

    // Add static routes (exclude landing pages)
    foreach ($routes as $path => $data) {
        if (strpos($path, '/lp-') === 0) {
            continue;
        }
        $all_urls[] = [
            'loc' => SITE_URL . $path,
            'changefreq' => $data['changefreq'],
            'priority' => $data['priority']
        ];
    }

    // Add programmatic SEO pages
    foreach ($marketing_services_data as $marketing_service) {
        foreach ($business_types_data as $business_type) {
            $url = '/' . $marketing_service['slug'] . '-for-' . $business_type['slug'];
            $all_urls[] = [
                'loc' => SITE_URL . $url,
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ];
        }
    }

    // Add service pillar pages
    foreach ($service_pillars_data as $service_pillar) {
        $url = '/' . $service_pillar['slug'];
        $all_urls[] = [
            'loc' => SITE_URL . $url,
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];
    }

    // Add industry pillar pages
    foreach ($industry_pillars_data as $industry_pillar) {
        $url = '/' . $industry_pillar['slug'];
        $all_urls[] = [
            'loc' => SITE_URL . $url,
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];
    }

    // Add blog posts
    foreach ($blog_posts_data as $blog_post) {
        $url = '/blog/' . $blog_post['slug'];
        $all_urls[] = [
            'loc' => SITE_URL . $url,
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];
    }

    // Output URLs for this sitemap (with pagination)
    $urls_for_this_sitemap = array_slice($all_urls, $offset, $urls_per_sitemap);

    foreach ($urls_for_this_sitemap as $url_data) {
        echo '    <url>' . "\n";
        echo '        <loc>' . $url_data['loc'] . '</loc>' . "\n";
        echo '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '        <changefreq>' . $url_data['changefreq'] . '</changefreq>' . "\n";
        echo '        <priority>' . $url_data['priority'] . '</priority>' . "\n";
        echo '    </url>' . "\n";
    }

    echo '</urlset>';
    exit;
}

// Handle 301 redirects from old suburb-based URLs to new consolidated URLs
$uri_path = trim($request_uri, '/');
if (strpos($uri_path, '-for-') !== false && strpos($uri_path, '-in-') !== false) {
    // This is an old suburb-based URL, redirect to new format
    $for_parts = explode('-for-', $uri_path, 2);
    if (count($for_parts) === 2) {
        $marketing_slug_candidate = $for_parts[0];
        $in_parts = explode('-in-', $for_parts[1], 2);
        if (count($in_parts) === 2) {
            $business_slug_candidate = $in_parts[0];
            $suburb_slug_candidate = $in_parts[1];

            // Verify this was a valid old URL before redirecting
            if (isset($marketing_services_by_slug[$marketing_slug_candidate]) &&
                isset($business_types_by_slug[$business_slug_candidate]) &&
                isset($suburbs_by_slug[$suburb_slug_candidate])) {

                // Redirect to new URL without suburb
                $new_url = '/' . $marketing_slug_candidate . '-for-' . $business_slug_candidate;
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $new_url);
                exit;
            }
        }
    }
}

// Check for programmatic SEO pages and dynamic routes
$is_local_seo_page = false;
$is_service_pillar = false;
$is_industry_pillar = false;
$is_blog_post = false;
$marketing_service = null;
$business_type = null;
$service_pillar = null;
$industry_pillar = null;
$blog_post = null;

if (!isset($routes[$request_uri])) {
    $uri_path = trim($request_uri, '/');

    // Check for blog posts (/blog/post-slug)
    if (strpos($uri_path, 'blog/') === 0) {
        $blog_slug = substr($uri_path, 5); // Remove 'blog/' prefix
        if (isset($blog_posts_by_slug[$blog_slug])) {
            $is_blog_post = true;
            $blog_post = $blog_posts_by_slug[$blog_slug];
        }
    }

    // Check for service pillar pages (service-slug-brisbane)
    elseif (isset($service_pillars_by_slug[$uri_path])) {
        $is_service_pillar = true;
        $service_pillar = $service_pillars_by_slug[$uri_path];
    }

    // Check for industry pillar pages (marketing-for-business-brisbane)
    elseif (isset($industry_pillars_by_slug[$uri_path])) {
        $is_industry_pillar = true;
        $industry_pillar = $industry_pillars_by_slug[$uri_path];
    }

    // Check for service-for-business programmatic pages
    elseif (strpos($uri_path, '-for-') !== false) {
        // Split by "-for-"
        $for_parts = explode('-for-', $uri_path, 2);
        if (count($for_parts) === 2) {
            $marketing_slug_candidate = $for_parts[0];
            $business_slug_candidate = $for_parts[1];

            // Verify both parts exist in our data
            if (isset($marketing_services_by_slug[$marketing_slug_candidate]) &&
                isset($business_types_by_slug[$business_slug_candidate])) {

                $is_local_seo_page = true;
                $marketing_service = $marketing_services_by_slug[$marketing_slug_candidate];
                $business_type = $business_types_by_slug[$business_slug_candidate];
            }
        }
    }
}

// Set page data based on route type
if ($is_local_seo_page) {
    $page_data = [
        'title' => $marketing_service['name'] . ' for ' . $business_type['name'] . ' in Brisbane',
        'description' => $marketing_service['name'] . ' services for ' . strtolower($business_type['name']) . ' in Brisbane. ' . $marketing_service['description'] . '. Get qualified leads and grow your business.',
        'template' => 'local-marketing-service',
        'marketing_service' => $marketing_service,
        'business_type' => $business_type
    ];
} elseif ($is_service_pillar) {
    $page_data = [
        'title' => $service_pillar['title'],
        'description' => $service_pillar['meta_description'],
        'template' => 'service-pillar',
        'service_pillar' => $service_pillar
    ];
} elseif ($is_industry_pillar) {
    $page_data = [
        'title' => $industry_pillar['title'],
        'description' => $industry_pillar['meta_description'],
        'template' => 'industry-pillar',
        'industry_pillar' => $industry_pillar
    ];
} elseif ($is_blog_post) {
    $page_data = [
        'title' => $blog_post['title'],
        'description' => $blog_post['meta_description'],
        'template' => 'blog-post',
        'blog_post' => $blog_post
    ];
} elseif (!isset($routes[$request_uri])) {
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

// Helper function to check if current page is a landing page
function is_landing_page() {
    global $request_uri;
    return $request_uri === '/' || strpos($request_uri, '/lp-') === 0;
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
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Let's Grow Your Business</h1>
                <p>Ready to see real results from your digital marketing? Get in touch for a free consultation.</p>
            </div>
        </section>

        <section class="contact-section">
            <div class="container-narrow">
                <div id="cbox-hNPWgg4AnfbXuzSE"></div>
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

    case 'thank-you-consult':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Thanks for Your Interest in Working Together</h1>
                <p>Let's take a look at your business</p>
            </div>
        </section>

        <section class="content-section">
            <div class="container">
                <div class="thank-you-content">
                    <div style="text-align: center; padding: 3rem 2rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">✓</div>
                        <h2 style="color: var(--primary-green); margin-bottom: 1rem;">We're Excited to Work With You</h2>
                        <p style="font-size: 1.1rem; color: var(--text-gray); margin-bottom: 2rem;">
                            Thank you for taking the first step towards growing your business. We're looking forward to diving into your business and creating a strategy that delivers real results.
                        </p>
                        <p style="font-size: 1rem; color: var(--text-gray); margin-bottom: 2rem;">
                            We'll be in touch within 24 hours to discuss your goals and how we can help you achieve them.
                        </p>
                        <div class="cta-center" style="margin-top: 2rem;">
                            <a href="/services" class="cta-button-secondary" style="margin-right: 1rem;">Explore Our Services</a>
                            <a href="/" class="cta-button-secondary">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        break;

    case 'lp-001':
        ?>
        <section class="lp-hero">
            <div class="lp-hero-content">
                <h1>Marketing Consulting for Home Services Businesses</h1>
                <p class="lp-subtitle">Specialised for Established Businesses Doing $1M+ in Revenue</p>
                <p class="lp-subtext">You've built a solid home services business, but scaling past $1M requires different marketing strategies than what got you here. We specialise in helping plumbers, electricians, HVAC companies, and other trades businesses systematically grow revenue without burning out. We offer done-for-you services or advisory support - with pricing that aligns with your business goals.</p>
                <a href="#form" class="lp-cta-button">Get Your Free Consultation</a>
            </div>
        </section>

        <section class="lp-benefits">
            <div class="container">
                <h2 class="section-title">How We Help Home Services Businesses Scale</h2>
                <p class="section-subtitle">Two ways to work together - choose what fits your business best</p>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">🚀</div>
                        <h3>Done For You</h3>
                        <p>Perfect for busy owners who want experts to handle their marketing. We manage your Meta Ads, Google Ads, SEO, and conversion optimisation while you focus on delivering great service and running operations. We'll structure pricing based on the value created for your specific business.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">🤝</div>
                        <h3>Advisory Services</h3>
                        <p>Best for businesses with a marketing person or small team who need expert guidance. We work alongside your team, providing strategy, training, and hands-on support specific to home services marketing. Build internal capabilities while getting expert results.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-form-section" id="form">
            <div class="container-narrow">
                <h2>Let's Discuss Growing Your Home Services Business</h2>
                <p style="text-align: center; margin-bottom: 2rem; color: var(--text-gray);">Book a free consultation to see how we can help you scale past $1M</p>
                <div id="cbox-hNPWgg4AnfbXuzSE"></div>
            </div>
        </section>

        <section class="lp-why-section">
            <div class="container">
                <h2 class="section-title">Why We Focus on $1M+ Home Services Businesses</h2>
                <div class="why-grid">
                    <div class="why-item">
                        <h3>You Need Different Strategies</h3>
                        <p>The marketing that worked to get you to $1M won't get you to $5M. Established home services businesses need sophisticated lead generation, conversion optimisation, and systems - not basic Facebook ads. We specialise in the strategies that work at your scale.</p>
                    </div>
                    <div class="why-item">
                        <h3>We Understand Your Business</h3>
                        <p>Home services marketing is different from e-commerce or SaaS. Service area targeting, seasonal fluctuations, job costing, booking systems - we've built campaigns for plumbers, electricians, HVAC companies, and other trades. We speak your language.</p>
                    </div>
                    <div class="why-item">
                        <h3>Aligned on Outcomes, Not Hours</h3>
                        <p>We price based on the value we create for your business, not arbitrary hourly rates. Whether it's performance-based, fixed investment, or base + commission - we structure pricing around business outcomes. You get predictability and we're incentivised to deliver results.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- <section class="lp-testimonials">
            <div class="container">
                <h2 class="section-title">What Home Services Business Owners Say</h2>
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Finally, a marketing partner who actually understands the plumbing business. They helped us scale from $1.2M to $3.5M in 18 months by fixing our lead generation and booking systems. Worth every dollar."</p>
                        <p class="testimonial-author">- Michael S., Plumbing Company Owner</p>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"The advisory services were perfect for us. My marketing coordinator learned how to run profitable Google Ads while getting expert guidance. We've doubled our service calls and know how to maintain it ourselves."</p>
                        <p class="testimonial-author">- Jennifer K., HVAC Business Owner</p>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Best investment we made. They got us off the referral rollercoaster and built a predictable lead generation system. We went from hoping the phone rings to scheduling jobs two weeks out."</p>
                        <p class="testimonial-author">- David L., Electrical Contractor</p>
                    </div>
                </div>
            </div>
        </section> -->

        <section class="lp-faq">
            <div class="container-narrow">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <h3>Why only $1M+ revenue businesses?</h3>
                        <p>At this scale, you have the foundation to invest in sophisticated marketing systems. You're past survival mode and ready to build predictable, scalable growth. The strategies that work at your level are different from startups, and we specialise in what works for established home services businesses.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Which option is right for my business?</h3>
                        <p><strong>Done For You</strong> is best if you're too busy to manage marketing yourself and want experts to handle everything. <strong>Advisory Services</strong> works well if you have someone on your team handling marketing but need expert guidance, strategy, and hands-on support. We'll help you decide what fits best on our call.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Do you work with all types of home services?</h3>
                        <p>We specialise in trades and field services - plumbers, electricians, HVAC, roofing, landscaping, etc. If you send teams to customer locations, we likely have experience in your industry. We focus on what we know works rather than taking on any business.</p>
                    </div>
                    <div class="faq-item">
                        <h3>How does pricing work?</h3>
                        <p>We structure pricing based on the value we create for your specific business. This might be performance-based (pay per lead), fixed investment (tied to outcomes), or base + commission (predictable with upside). We'll discuss what makes sense based on your goals, current revenue, and growth targets on our consultation call.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-final-cta">
            <div class="container">
                <h2>Ready to Scale Your Home Services Business?</h2>
                <p>Book your free consultation and let's discuss your growth goals. No pressure, no commitments - just an honest conversation about what's working, what's not, and how we can help you get to the next level.</p>
                <a href="#form" class="lp-cta-button">Book Your Free Consultation</a>
            </div>
        </section>

        <section class="lp-legal">
            <div class="container">
                <p style="text-align: center; color: var(--text-gray); font-size: 0.9rem; line-height: 1.8;">
                    Results vary based on industry, market conditions, offer quality, and your level of engagement. Pricing structures are customised based on your business model, goals, and the strategic value we can create.
                    We'll discuss specific pricing options during your consultation.
                </p>
            </div>
        </section>
        <?php
        break;

    case 'services-directory':
        ?>
        <section class="page-header">
            <div class="container">
                <h1>Marketing Services for Brisbane Home Services Businesses</h1>
                <p>Find the right marketing solution for your trade or home services business</p>
            </div>
        </section>

        <section class="directory-section">
            <div class="container">
                <h2 class="section-title">Browse by Marketing Service</h2>
                <p class="section-subtitle">Select a marketing service to see which businesses we serve</p>

                <div class="directory-grid">
                    <?php
                    $mkt_categories = [];
                    foreach ($marketing_services_data as $mkt_service) {
                        $cat = $mkt_service['category'];
                        if (!isset($mkt_categories[$cat])) {
                            $mkt_categories[$cat] = [];
                        }
                        $mkt_categories[$cat][] = $mkt_service;
                    }
                    ksort($mkt_categories);

                    foreach ($mkt_categories as $category => $services):
                    ?>
                        <div class="directory-category">
                            <h3><?php echo $category; ?></h3>
                            <div class="directory-links">
                                <?php foreach ($services as $mkt_svc): ?>
                                    <details class="service-dropdown">
                                        <summary><?php echo $mkt_svc['name']; ?> (<?php echo count($business_types_data) . ' industries'; ?>)</summary>
                                        <div class="suburb-links">
                                            <?php
                                            $display_businesses = array_slice($business_types_data, 0, 10);
                                            foreach ($display_businesses as $biz):
                                                $url = '/' . $mkt_svc['slug'] . '-for-' . $biz['slug'];
                                            ?>
                                                <a href="<?php echo $url; ?>">For <?php echo $biz['name']; ?></a>
                                            <?php endforeach; ?>
                                            <?php if (count($business_types_data) > 10): ?>
                                                <span class="more-suburbs">...and <?php echo count($business_types_data) - 10; ?> more industries</span>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="directory-suburbs">
            <div class="container">
                <h2 class="section-title">Browse by Industry</h2>
                <p class="section-subtitle">Find marketing services for your type of business</p>

                <div class="suburbs-grid">
                    <?php
                    foreach ($business_types_data as $biz):
                    ?>
                        <div class="suburb-card">
                            <h3><?php echo $biz['name']; ?></h3>
                            <p class="postcode"><?php echo $biz['industry']; ?></p>
                            <p><?php echo count($marketing_services_data); ?> marketing services available</p>
                            <details class="service-dropdown">
                                <summary>View Services</summary>
                                <div class="suburb-service-links">
                                    <?php
                                    $display_services = array_slice($marketing_services_data, 0, 6);
                                    foreach ($display_services as $mkt_svc):
                                        $url = '/' . $mkt_svc['slug'] . '-for-' . $biz['slug'];
                                    ?>
                                        <a href="<?php echo $url; ?>"><?php echo $mkt_svc['name']; ?></a>
                                    <?php endforeach; ?>
                                    <span class="more-services">...and more</span>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to Grow Your Home Services Business?</h2>
                <p>Contact us for a free strategy session to discuss how we can help you generate more leads and grow your revenue.</p>
                <a href="/contact" class="cta-button">Get Your Free Strategy Session</a>
            </div>
        </section>
        <?php
        break;

    case 'local-marketing-service':
        $mkt_service = $page_data['marketing_service'];
        $business = $page_data['business_type'];

        $mkt_service_name = $mkt_service['name'];
        $mkt_service_slug = $mkt_service['slug'];
        $business_name = $business['name'];
        $business_slug = $business['slug'];
        $industry = $business['industry'];
        ?>
        <!-- Marketing Service Hero Section -->
        <section class="local-hero">
            <div class="container">
                <h1><?php echo $mkt_service_name; ?> for <?php echo $business_name; ?> in Brisbane</h1>
                <p class="local-subtitle">Generate More Leads for Your <?php echo $business['singular']; ?> in Brisbane</p>
                <div class="local-trust-badges">
                    <span>✓ Home Services Specialists</span>
                    <span>✓ Proven Results</span>
                    <span>✓ Performance-Based Options</span>
                    <span>✓ Done-For-You or Advisory</span>
                </div>
                <a href="#contact-form" class="cta-button">Get Your Free Strategy Session</a>
            </div>
        </section>

        <!-- The Problem - Brain Audit Step 1 -->
        <section class="local-problem">
            <div class="container">
                <h2>Marketing Challenges for <?php echo $business_name; ?> in Brisbane</h2>
                <p>Running a <?php echo strtolower($industry); ?> business in Brisbane means dealing with:</p>
                <div class="problem-grid">
                    <?php
                    $challenges = array_slice($business['marketing_challenges'], 0, 3);
                    $challenge_icons = ['📉', '💸', '⏰'];
                    $challenge_solutions = [
                        'We understand ' . strtolower($industry) . ' businesses and create marketing strategies that actually work for your industry.',
                        'Our pricing models align with your business goals - performance-based, fixed investment, or base + commission options available.',
                        'We handle all the marketing complexity while you focus on running your ' . strtolower($business['singular']) . '.'
                    ];
                    foreach ($challenges as $idx => $challenge):
                    ?>
                    <div class="problem-item">
                        <span class="problem-icon"><?php echo $challenge_icons[$idx]; ?></span>
                        <h3><?php echo ucfirst($challenge); ?></h3>
                        <p><?php echo $challenge_solutions[$idx]; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- The Solution - Brain Audit Step 2 -->
        <section class="local-solution">
            <div class="container">
                <h2>How <?php echo $mkt_service_name; ?> Helps <?php echo $business_name; ?> in Brisbane</h2>
                <p><?php echo $mkt_service['description']; ?>. Perfect for <?php echo strtolower($business_name); ?> in Brisbane.</p>
                <div class="services-grid">
                    <?php foreach (array_slice($mkt_service['benefits'], 0, 4) as $benefit): ?>
                    <div class="service-card">
                        <h3>✓ <?php echo $benefit['headline']; ?></h3>
                        <p><?php echo $benefit['description']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 2rem; padding: 1.5rem; background: var(--light-green); border-radius: 10px;">
                    <p style="font-size: 1.1rem; color: var(--text-dark); margin-bottom: 0.5rem;"><strong>Pricing:</strong> <?php echo $mkt_service['pricing_model']; ?></p>
                    <p style="color: var(--text-gray); margin: 0;"><strong>Ideal For:</strong> <?php echo $mkt_service['ideal_for']; ?></p>
                </div>
            </div>
        </section>

        <!-- Target Profile - Brain Audit Step 3 -->
        <section class="local-target">
            <div class="container-narrow">
                <h2>Is This Right for Your <?php echo $business['singular']; ?>?</h2>
                <p>Our <?php echo strtolower($mkt_service_name); ?> services in Brisbane are perfect for:</p>
                <ul class="target-list">
                    <li><strong>Established Businesses</strong> - <?php echo ucfirst($business_name); ?> in Brisbane doing <?php echo $business['annual_revenue_range']; ?> annually</li>
                    <li><strong>Growth-Focused Owners</strong> - Ready to invest in marketing that generates measurable results</li>
                    <li><strong>Businesses With Capacity</strong> - Can handle more work if you had consistent lead flow</li>
                    <li><strong>Quality Over Price</strong> - Compete on service quality, not just being the cheapest option</li>
                </ul>
                <div style="background: var(--white); padding: 2rem; border-radius: 10px; margin-top: 2rem; border-left: 4px solid var(--primary-green);">
                    <h3 style="color: var(--primary-green); margin-bottom: 1rem;">Your Typical Customers:</h3>
                    <p style="color: var(--text-gray); margin: 0;"><?php echo $business['ideal_customer']; ?></p>
                </div>
            </div>
        </section>

        <!-- Why Choose Us - Brain Audit Step 5 (Uniqueness) -->
        <section class="local-why">
            <div class="container">
                <h2>Why <?php echo $business_name; ?> in Brisbane Work With Us</h2>
                <div class="why-grid">
                    <div class="why-item">
                        <h3>Home Services Specialists</h3>
                        <p>We only work with trades and home services businesses. We understand your industry, your customers, and what marketing actually works for <?php echo strtolower($business_name); ?>.</p>
                    </div>
                    <div class="why-item">
                        <h3>Local Market Knowledge</h3>
                        <p>We know the Brisbane market. We understand local competition, seasonal demand, and how to target customers in your service area.</p>
                    </div>
                    <div class="why-item">
                        <h3>Performance-Based Pricing Options</h3>
                        <p>We offer pricing that aligns with results - pay per lead, base + commission, or fixed investment. You choose what makes sense for your business.</p>
                    </div>
                    <div class="why-item">
                        <h3>Done-For-You or Advisory</h3>
                        <p>Want us to handle everything? We do that. Have a team and need expert guidance? We do that too. Flexible support that fits your business.</p>
                    </div>
                    <div class="why-item">
                        <h3>Focused on $1M+ Businesses</h3>
                        <p>We specialize in established businesses ready to scale. The marketing that got you to $1M won't get you to $5M - we know what works at your level.</p>
                    </div>
                    <div class="why-item">
                        <h3>Track Everything</h3>
                        <p>Know exactly where every lead comes from, what you're spending, and your ROI. Transparent reporting so you always know what's working.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Common Questions - Brain Audit Step 4 (Objections) -->
        <section class="local-faq">
            <div class="container-narrow">
                <h2>Common Questions About <?php echo $mkt_service_name; ?> for <?php echo $business_name; ?></h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <h3>Do you only work with <?php echo strtolower($business_name); ?> in Brisbane?</h3>
                        <p>We work with <?php echo strtolower($business_name); ?> throughout Brisbane and surrounding areas. We specialize in home services businesses across all trades.</p>
                    </div>
                    <div class="faq-item">
                        <h3>What size business do you work with?</h3>
                        <p>We focus on established businesses doing $1M+ in annual revenue. At this level, you have the foundation to invest in sophisticated marketing and the capacity to handle growth.</p>
                    </div>
                    <div class="faq-item">
                        <h3>How much does <?php echo strtolower($mkt_service_name); ?> cost?</h3>
                        <p><?php echo $mkt_service['pricing_model']; ?>. We'll discuss pricing options based on your specific goals, current revenue, and growth targets during your free consultation.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Done-for-you or advisory - which is right for me?</h3>
                        <p><strong>Done-for-you</strong> is best if you want experts handling your marketing while you focus on operations. <strong>Advisory</strong> works if you have someone managing marketing but need expert guidance. We'll help you decide on our call.</p>
                    </div>
                    <div class="faq-item">
                        <h3>How long before I see results?</h3>
                        <p>With <?php echo strtolower($mkt_service_name); ?>, most <?php echo strtolower($business_name); ?> see initial results within 30-60 days. We'll set realistic expectations based on your market, competition, and current marketing foundation.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Do you work with other <?php echo strtolower($industry); ?> businesses?</h3>
                        <p>Yes! We work with multiple <?php echo strtolower($business_name); ?> in different service areas. We won't work with direct competitors in the same area, but we apply learnings from the industry to benefit all clients.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action - Brain Audit Step 9 -->
        <section class="local-cta" id="contact-form">
            <div class="container-narrow">
                <h2>Ready to Grow Your <?php echo $business['singular']; ?> in Brisbane?</h2>
                <p>Book a free strategy session to discuss how <?php echo strtolower($mkt_service_name); ?> can generate more qualified leads for your business. No pressure, no commitments - just an honest conversation about what's working, what's not, and how we can help.</p>

                <!-- <div class="contact-methods">
                    <div class="contact-method">
                        <h3>📞 Call Now</h3>
                        <p><a href="tel:1300000000">1300 000 000</a></p>
                        <p class="small">Mon-Fri 9am-5pm AEST</p>
                    </div>
                    <div class="contact-method">
                        <h3>📧 Email Us</h3>
                        <p><a href="mailto:hello@leadstoprofit.com">hello@leadstoprofit.com</a></p>
                        <p class="small">We respond within 24 hours</p>
                    </div>
                </div> -->

                <div id="cbox-hNPWgg4AnfbXuzSE" style="margin-top: 2rem;"></div>

                <p style="text-align: center; margin-top: 2rem; color: rgba(255,255,255,0.9); font-size: 0.95rem;">
                    <strong>Free Strategy Session Includes:</strong> Marketing audit, competitor analysis, and customized growth roadmap for your <?php echo strtolower($business['singular']); ?>
                </p>
            </div>
        </section>

        <!-- Local Area Info -->
        <section class="local-area-info">
            <div class="container">
                <h2><?php echo $mkt_service_name; ?> for <?php echo $business_name; ?> Across Brisbane</h2>
                <p>We help <?php echo strtolower($business_name); ?> throughout Brisbane generate more leads and grow their revenue. Whether you're servicing the inner city, suburbs, or broader Brisbane area, we understand the local market and what works for <?php echo strtolower($industry); ?> businesses.</p>

                <h3>Other Marketing Services for <?php echo $business_name; ?></h3>
                <div class="related-services">
                    <?php
                    // Display other marketing services for this business type
                    $other_services = array_slice($marketing_services_data, 0, 8);
                    foreach ($other_services as $other_svc):
                        if ($other_svc['slug'] !== $mkt_service_slug):
                            $link_url = '/' . $other_svc['slug'] . '-for-' . $business_slug;
                    ?>
                        <a href="<?php echo $link_url; ?>" class="related-service-link"><?php echo $other_svc['name']; ?> for <?php echo $business_name; ?></a>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>

                <h3>Other Home Services Businesses We Help in Brisbane</h3>
                <div class="nearby-suburbs">
                    <?php
                    // Display same service for other business types
                    foreach (array_slice($business_types_data, 0, 15) as $other_biz):
                        if ($other_biz['slug'] !== $business_slug):
                            $link_url = '/' . $mkt_service_slug . '-for-' . $other_biz['slug'];
                    ?>
                        <a href="<?php echo $link_url; ?>" class="suburb-link"><?php echo $other_biz['name']; ?></a>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
        </section>

        <!-- Local SEO Schema -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ProfessionalService",
            "name": "<?php echo SITE_NAME; ?> - <?php echo $mkt_service_name; ?> for <?php echo $business_name; ?>",
            "image": "<?php echo SITE_URL; ?>/logo.png",
            "@id": "<?php echo SITE_URL . $request_uri; ?>",
            "url": "<?php echo SITE_URL . $request_uri; ?>",
            "telephone": "1300-000-000",
            "email": "hello@leadstoprofit.com",
            "priceRange": "$$-$$$",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Brisbane",
                "addressRegion": "QLD",
                "addressCountry": "AU"
            },
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": -27.4705,
                "longitude": 153.0260
            },
            "openingHoursSpecification": {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": [
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday"
                ],
                "opens": "09:00",
                "closes": "17:00"
            },
            "areaServed": [{
                "@type": "City",
                "name": "<?php echo $suburb_name; ?>"
            }, {
                "@type": "City",
                "name": "Brisbane"
            }],
            "serviceType": "<?php echo $mkt_service_name; ?>",
            "description": "<?php echo addslashes($page_data['description']); ?>",
            "audience": {
                "@type": "BusinessAudience",
                "name": "<?php echo $business_name; ?> in <?php echo $suburb_name; ?>, Brisbane"
            }
        }
        </script>
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
            white-space: nowrap;
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

        /* Landing Page Styles */
        .lp-tagline {
            font-size: 0.9rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .lp-why-section {
            padding: 5rem 2rem;
            background-color: var(--white);
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .why-item h3 {
            color: var(--primary-green);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .why-item p {
            color: var(--text-gray);
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .lp-hero {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: var(--white);
            padding: 6rem 2rem;
            text-align: center;
        }

        .lp-hero-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .lp-hero h1 {
            font-size: clamp(2.5rem, 10vw, 5.5rem);
            margin-bottom: 1.5rem;
            color: var(--white);
            line-height: 1.1;
        }

        .lp-subtitle {
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            margin-bottom: 3rem;
            opacity: 0.9;
        }

        .lp-video-container {
            margin: 3rem 0;
        }

        .lp-video-placeholder {
            background: #000;
            border-radius: 10px;
            padding: 4rem 2rem;
            position: relative;
            border: 3px solid var(--primary-green);
        }

        .play-button {
            font-size: 4rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .lp-subtext {
            font-size: 1rem;
            margin: 2rem 0;
            opacity: 0.8;
            line-height: 1.6;
        }

        .lp-cta-button {
            display: inline-block;
            background-color: var(--primary-green);
            color: var(--white);
            padding: 1.2rem 3rem;
            text-decoration: none;
            border-radius: 50px;
            font-family: 'DINPro', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            text-transform: uppercase;
            transition: all 0.3s;
            border: 2px solid var(--primary-green);
            letter-spacing: 1px;
        }

        .lp-cta-button:hover {
            background-color: var(--dark-green);
            border-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(15, 197, 61, 0.4);
        }

        .lp-form-section {
            padding: 5rem 2rem;
            background-color: var(--light-green);
        }

        .lp-form-section h2 {
            text-align: center;
            color: var(--primary-green);
            margin-bottom: 1rem;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
        }

        .lp-benefits {
            padding: 5rem 2rem;
            background-color: var(--white);
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .benefit-item {
            text-align: center;
        }

        .benefit-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .benefit-item h3 {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .benefit-item p {
            color: var(--text-gray);
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .lp-testimonials {
            padding: 5rem 2rem;
            background-color: var(--background);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .testimonial-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-green);
        }

        .testimonial-text {
            font-style: italic;
            color: var(--text-gray);
            margin-bottom: 1.5rem;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .testimonial-author {
            font-weight: 700;
            color: var(--text-dark);
        }

        .lp-faq {
            padding: 5rem 2rem;
            background-color: var(--white);
        }

        .faq-items {
            margin-top: 3rem;
        }

        .faq-item {
            background: var(--background);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--primary-green);
        }

        .faq-item h3 {
            color: var(--primary-green);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .faq-item p {
            color: var(--text-gray);
            line-height: 1.8;
        }

        .lp-final-cta {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: 5rem 2rem;
            text-align: center;
        }

        .lp-final-cta h2 {
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--white);
        }

        .lp-final-cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .urgency-text {
            background-color: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 50px;
            margin: 1.5rem 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .lp-legal {
            padding: 3rem 2rem;
            background-color: var(--background);
        }

        /* Services Directory Styles */
        .directory-section,
        .directory-suburbs {
            padding: 5rem 2rem;
        }

        .directory-section {
            background-color: var(--white);
        }

        .directory-suburbs {
            background-color: var(--background);
        }

        .directory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .directory-category {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .directory-category h3 {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-green);
            padding-bottom: 0.5rem;
        }

        .directory-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .service-dropdown {
            cursor: pointer;
            padding: 0.8rem;
            background: var(--background);
            border-radius: 5px;
            transition: all 0.3s;
        }

        .service-dropdown:hover {
            background: var(--light-green);
        }

        .service-dropdown summary {
            font-weight: 600;
            color: var(--text-dark);
            list-style: none;
            cursor: pointer;
            user-select: none;
        }

        .service-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .service-dropdown summary::before {
            content: '▶';
            display: inline-block;
            margin-right: 0.5rem;
            transition: transform 0.3s;
            color: var(--primary-green);
        }

        .service-dropdown[open] summary::before {
            transform: rotate(90deg);
        }

        .suburb-links,
        .suburb-service-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .suburb-links a,
        .suburb-service-links a {
            display: inline-block;
            background: var(--primary-green);
            color: var(--white);
            padding: 0.4rem 0.8rem;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .suburb-links a:hover,
        .suburb-service-links a:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }

        .more-suburbs,
        .more-services {
            color: var(--text-gray);
            font-style: italic;
            font-size: 0.9rem;
        }

        .suburbs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .suburb-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .suburb-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,166,81,0.2);
        }

        .suburb-card h3 {
            font-size: 1.3rem;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .suburb-card .postcode {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .suburb-card > p {
            color: var(--text-gray);
            margin-bottom: 1rem;
        }

        /* Local Service Page Styles */
        .local-hero {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: 5rem 2rem 4rem;
            text-align: center;
        }

        .local-hero h1 {
            font-size: clamp(2rem, 6vw, 3.5rem);
            margin-bottom: 1rem;
            color: var(--white);
        }

        .local-subtitle {
            font-size: clamp(1rem, 3vw, 1.3rem);
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .local-trust-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .local-trust-badges span {
            font-size: 1rem;
            font-weight: 600;
        }

        .local-problem,
        .local-solution,
        .local-why,
        .local-area-info {
            padding: 5rem 2rem;
        }

        .local-problem {
            background-color: var(--white);
        }

        .local-solution {
            background-color: var(--background);
        }

        .local-why {
            background-color: var(--light-green);
        }

        .local-area-info {
            background-color: var(--white);
        }

        .local-problem h2,
        .local-solution h2,
        .local-why h2,
        .local-area-info h2 {
            text-align: center;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--primary-green);
        }

        .local-problem > .container > p,
        .local-solution > .container > p {
            text-align: center;
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-bottom: 3rem;
        }

        .problem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .problem-item {
            text-align: center;
        }

        .problem-icon {
            font-size: 3.5rem;
            display: block;
            margin-bottom: 1rem;
        }

        .problem-item h3 {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .problem-item p {
            color: var(--text-gray);
            line-height: 1.8;
        }

        .local-target {
            padding: 5rem 2rem;
            background-color: var(--white);
        }

        .local-target h2 {
            text-align: center;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 1.5rem;
            color: var(--primary-green);
        }

        .local-target > div > p {
            text-align: center;
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        .target-list {
            list-style: none;
            max-width: 700px;
            margin: 0 auto;
        }

        .target-list li {
            padding: 1rem 0;
            padding-left: 2rem;
            position: relative;
            color: var(--text-gray);
            line-height: 1.8;
            border-bottom: 1px solid #e0e0e0;
        }

        .target-list li:last-child {
            border-bottom: none;
        }

        .target-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-green);
            font-weight: bold;
            font-size: 1.3rem;
        }

        .local-faq {
            padding: 5rem 2rem;
            background-color: var(--background);
        }

        .local-faq h2 {
            text-align: center;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 3rem;
            color: var(--primary-green);
        }

        .local-cta {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
        }

        .local-cta h2 {
            text-align: center;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--white);
        }

        .local-cta > div > p {
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .contact-method {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        .contact-method h3 {
            color: var(--white);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .contact-method p {
            margin: 0.5rem 0;
        }

        .contact-method a {
            color: var(--white);
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .contact-method a:hover {
            text-decoration: underline;
        }

        .contact-method .small {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .local-area-info h3 {
            font-size: 1.8rem;
            color: var(--primary-green);
            margin: 3rem 0 1.5rem;
        }

        .local-area-info p {
            color: var(--text-gray);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .related-services,
        .nearby-suburbs {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .related-service-link,
        .suburb-link {
            display: inline-block;
            background: var(--light-green);
            color: var(--text-dark);
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
            border: 1px solid var(--primary-green);
        }

        .related-service-link:hover,
        .suburb-link:hover {
            background: var(--primary-green);
            color: var(--white);
            transform: translateY(-2px);
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
            .container {
                padding: 0 1rem;
            }

            .container-narrow {
                padding: 0 1rem;
            }

            nav {
                padding: 1rem;
                gap: 14px;
            }

            .logo {
                font-size: 1.2rem;
            }

            .lp-tagline {
                font-size: 0.75rem;
                gap: 4px;
            }

            .hero {
                padding: 4rem 1rem;
            }

            .lp-hero {
                padding: 4rem 1rem;
            }

            .page-header {
                padding: 3rem 1rem;
            }

            .services-preview,
            .services-detailed,
            .lp-benefits,
            .lp-why-section,
            .lp-testimonials,
            .lp-faq,
            .about-content-section,
            .content-section {
                padding: 3rem 1rem;
            }

            .contact-section,
            .lp-form-section {
                padding: 2rem 1rem;
            }

            .stats,
            .contact-info {
                padding: 3rem 1rem;
            }

            .cta-section,
            .lp-final-cta {
                padding: 3rem 1rem;
            }

            .lp-legal {
                padding: 2rem 1rem;
            }

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

            .services-grid {
                grid-template-columns: 1fr;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .why-grid {
                grid-template-columns: 1fr;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    

    <script type="text/javascript">!function(e,t){(e=t.createElement("script")).src="https://cdn.convertbox.com/convertbox/js/embed.js",e.id="app-convertbox-script",e.async=true,e.dataset.uuid="78b30db1-9aff-48e3-ad56-27f8763a2ab3",document.getElementsByTagName("head")[0].appendChild(e)}(window,document);</script>
</head>
<body>
    <header>
        <nav>
            <a href="/" class="logo">LEADS TO PROFIT</a>
            <?php if (!is_landing_page()): ?>
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
            <?php else: ?>
            <div class="lp-tagline">Home Services Marketing</div>
            <?php endif; ?>
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
