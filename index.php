<?php
require_once 'config.php';

// Check for logout message
$showLogoutMessage = isset($_GET['logged_out']) && $_GET['logged_out'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCMP UniFa - UniKL Financial Aid System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='16' fill='%230a3d62'/><text x='50' y='58' font-size='56' text-anchor='middle' fill='white' font-family='Arial, sans-serif'>U</text></svg>">
</head>
<body>
    <?php 
    if ($showLogoutMessage) {
        include 'pages/component/MessageDialog.php';
        renderMessageDialogScript();
        showSuccessMessage('You have been successfully logged out.', true, null, 3000);
        // Clean URL
        echo '<script>setTimeout(function() { window.history.replaceState({}, document.title, window.location.pathname); }, 100);</script>';
    }
    ?>
    <!-- Header -->
    <header class="site-header" role="banner">
        <div class="container header-inner">
            <a href="#home" class="brand" aria-label="UniKL RCMP Home">
                <img src="public/unikl-rcmp.png" alt="UniKL RCMP logo" class="logo" />
            </a>

            <nav class="nav" role="navigation" aria-label="Primary">
                <button class="nav-toggle" aria-controls="primary-navigation" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <ul id="primary-navigation" class="nav-list">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About SWF</a></li>
                    <li><a href="#applications">Applications</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>

            <div class="header-cta">
                <a href="pages/login.php" class="btn btn-outline">Login</a>
            </div>
        </div>
    </header>

    <main id="home" class="main">
        <!-- Hero Section -->
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-overlay"></div>
            <div class="container hero-content">
                <h1 id="hero-title">Welcome to RCMP UniFa</h1>
                <p class="hero-sub">UniKL Financial Aid System - Supporting Student Success</p>
                <p class="hero-tag">Empowering UniKL students through accessible, transparent, and timely financial support.</p>
                <div class="hero-actions">
                    <a href="#applications" class="btn btn-primary">Apply Now</a>
                    <a href="#about" class="btn btn-light">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Introduction Section -->
        <section id="about" class="section intro" aria-labelledby="intro-title">
            <div class="container intro-grid">
                <div class="intro-text">
                    <h2 id="intro-title">History of Student Welfare Fund</h2>
                    <p>
                        The Student Welfare Fund (SWF) at UniKL RCMP was established to support students facing
                        financial challenges that may affect their academic journey. Over the years, SWF has grown
                        into a structured and accountable program, providing timely assistance to students in need.
                    </p>
                    <p>
                        Our mission is to ensure that financial barriers do not hinder student success. We collaborate
                        with the university, alumni, and partners to mobilize resources and deliver aid fairly and
                        transparently.
                    </p>
                    <ul class="bullets">
                        <li>Established to provide immediate support during financial emergencies</li>
                        <li>Committed to equity, accountability, and student development</li>
                        <li>Strengthened through partnerships and community support</li>
                    </ul>
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-value">5,200+</div>
                            <div class="stat-label">Students Helped</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">RM 3.4M</div>
                            <div class="stat-label">Funds Distributed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">96%</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                    </div>
                </div>
                <div class="intro-visual" aria-hidden="true">
                    <div class="visual-card">
                        <img src="public/rcmp-map.png" alt="Students studying together" />
                    </div>
                </div>
            </div>
        </section>

        <!-- SWF Structure Section -->
        <section class="section structure" aria-labelledby="structure-title">
            <div class="container">
                <h2 id="structure-title">How Our Fund Raises Money</h2>
                <div class="card-grid">
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">🏛️</div>
                        <h3>University Budget Allocation</h3>
                        <p>Annual allocations dedicated to student welfare and emergency support.</p>
                    </div>
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">🎓</div>
                        <h3>Alumni Donations</h3>
                        <p>Contributions from UniKL alumni who champion student success.</p>
                    </div>
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">🤝</div>
                        <h3>Corporate Sponsorships</h3>
                        <p>Strategic partnerships aligning CSR goals with student needs.</p>
                    </div>
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">🏛️</div>
                        <h3>Government Grants</h3>
                        <p>Targeted grants supporting education access and retention.</p>
                    </div>
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">🎉</div>
                        <h3>Fundraising Events</h3>
                        <p>Community-driven events to raise awareness and funds.</p>
                    </div>
                    <div class="info-card">
                        <div class="icon" aria-hidden="true">👩‍🎓</div>
                        <h3>Student Contributions</h3>
                        <p>Voluntary contributions reinforcing a culture of solidarity.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Applications Section -->
        <section id="applications" class="section applications" aria-labelledby="apps-title">
            <div class="container">
                <h2 id="apps-title">Available Financial Aid Programs</h2>
                
                <!-- Bereavement (Khairat) -->
                <div class="program-category" style="margin-bottom: 48px;">
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 2rem;">💐</span>
                        Bereavement (Khairat)
                    </h3>
                    <div class="card-grid app-grid">
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">👤</div>
                            <h3>Student</h3>
                            <p><strong>RM 500 fixed</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Financial assistance for student bereavement cases.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">👨‍👩‍👧</div>
                            <h3>Parent</h3>
                            <p><strong>RM 200 fixed</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Support for students who have lost a parent.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">👫</div>
                            <h3>Sibling</h3>
                            <p><strong>RM 100 fixed</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Assistance for students who have lost a sibling.</p>
                        </article>
                    </div>
                </div>

                <!-- Illness & Injuries -->
                <div class="program-category" style="margin-bottom: 48px;">
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 2rem;">🏥</span>
                        Illness & Injuries
                    </h3>
                    <div class="card-grid app-grid">
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">🩺</div>
                            <h3>Out-patient Treatment</h3>
                            <p><strong>RM 30 / semester</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Limited to RM 30 per semester. Allowable for two claims per year.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">🏨</div>
                            <h3>In-patient Treatment</h3>
                            <p><strong>Up to RM 1,000</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Applicable only if hospitalization cost exceeded the stipulated insurance coverage (overall annual limit per annum per student RM20,000.00). Limit up to RM 1,000.00. Provision of fund more than RM 1,000.00 requires SWF Campus committee approval.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">🦽</div>
                            <h3>Injuries</h3>
                            <p><strong>Up to RM 200</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Coverage limited to injury support equipment up to RM 200.00.</p>
                        </article>
                    </div>
                </div>

                <!-- Emergency -->
                <div class="program-category" style="margin-bottom: 48px;">
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 2rem;">🚨</span>
                        Emergency
                    </h3>
                    <div class="card-grid app-grid">
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">⚠️</div>
                            <h3>Critical Illness</h3>
                            <p><strong>Up to RM 200</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Critical illness initial diagnose, accompanied with appropriate supporting documents, up to RM 200.00 as per claim basis.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">🌪️</div>
                            <h3>Natural Disaster</h3>
                            <p><strong>RM 200 limit</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">The limit of contribution is RM200 only. A copy of certified evidence should be included for the claimed incident.</p>
                        </article>
                        <article class="app-card">
                            <div class="icon" aria-hidden="true">🆘</div>
                            <h3>Others</h3>
                            <p><strong>Subject to Approval</strong></p>
                            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 8px;">Requisition of emergency fund other than the critical illness & natural disaster cases (is subject to SWF Campus committee approval).</p>
                        </article>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 32px; padding: 24px; background: var(--light); border-radius: var(--radius);">
                    <p style="margin: 0; color: var(--muted); font-size: 0.9rem;">
                        <strong>Note:</strong> All applications are subject to review and approval by the SWF Campus Committee. 
                        Supporting documents are required for all claims. Please ensure you meet the eligibility criteria before applying.
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact/Footer Top Anchor -->
        <div id="contact"></div>
    </main>

    <!-- Footer -->
    <?php include 'pages/component/footer.php'; renderFooter('', true); ?>

    <script src="js/main.js"></script>
</body>
</html>

