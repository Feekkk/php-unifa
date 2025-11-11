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
            <div class="container">
                <h2 id="intro-title" style="text-align: center; margin-bottom: 24px; font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; color: var(--text);">History of Student Welfare Fund</h2>
                <p style="text-align: center; color: var(--muted); font-size: 1.125rem; max-width: 700px; margin: 0 auto 64px; line-height: 1.75; font-weight: 400; letter-spacing: 0.01em; padding: 0 24px;">
                    The Student Welfare Fund (SWF) is a fund established by the Management of UniKL to provide financial assistance to students in need. It was established in 2005 and rebranded to SWF in 2018.
                </p>
                
                <!-- Timeline -->
                <div class="timeline-container">
                    <div class="timeline-line"></div>
                    
                    <!-- Timeline Item 1 -->
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="timeline-dot"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date">September 30, 2005</div>
                            <h3 class="timeline-title">Establishment of TKS</h3>
                            <p class="timeline-description">
                                Tabung Kebajikan Siswa (TKS) was established upon endorsed and approved by the Management of UniKL.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Timeline Item 2 -->
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="timeline-dot"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date">December 12, 2017</div>
                            <h3 class="timeline-title">Rebranding to SWF</h3>
                            <p class="timeline-description">
                                TKS is rebranded to Student Welfare Fund (SWF), approved on TMM 30th Jan 2018 (TMM NO.125 ( 2/2018)).
                            </p>
                        </div>
                    </div>
                    
                    <!-- Timeline Item 3 -->
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="timeline-dot"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date">Present</div>
                            <h3 class="timeline-title">Current Management</h3>
                            <p class="timeline-description">
                                The management of UniKL empowers Campus Lifestyle Division and Campus Lifestyle Section to manage the operation of SWF.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Objectives Section -->
                <div class="objectives-section" style="margin-top: 80px; padding: 60px 40px; background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow);">
                    <div class="objectives-container" style="display: grid; grid-template-columns: 250px 1fr; gap: 60px; align-items: start; max-width: 1100px; margin: 0 auto;">
                        <div class="objectives-header">
                            <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin: 0; line-height: 1.3;">
                                SWF RCMP<br />OBJECTIVES
                            </h3>
                        </div>
                        <div class="objectives-content">
                            <p style="color: var(--text); font-size: 1.125rem; line-height: 1.8; margin: 0 0 24px; font-weight: 400;">
                                To provide essential welfare support to UniKL students, including assistance in cases of emergencies, medical conditions or injuries, and bereavement.
                            </p>
                            <p style="color: var(--text); font-size: 1.125rem; line-height: 1.8; margin: 0 0 24px; font-weight: 400;">
                                To ensure timely and transparent distribution of financial aid to eligible students who face genuine financial hardships that may impede their academic progress and personal well-being.
                            </p>
                            <p style="color: var(--text); font-size: 1.125rem; line-height: 1.8; margin: 0 0 24px; font-weight: 400;">
                                To foster a supportive community environment where students feel secure knowing that assistance is available during challenging times, promoting mental and emotional well-being alongside financial stability.
                            </p>
                            <p style="color: var(--text); font-size: 1.125rem; line-height: 1.8; margin: 0; font-weight: 400;">
                                To maintain accountability and integrity in fund management, ensuring that resources are utilized effectively and equitably for the benefit of the student community, while building trust through transparent processes and responsible stewardship.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SWF Structure Section -->
        <section class="section structure" aria-labelledby="structure-title">
            <div class="container">
                <h2 id="structure-title" style="text-align: center; margin-bottom: 16px; font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; color: var(--text);">Student Contribution SWF</h2>
                <p style="text-align: center; color: var(--muted); font-size: 1.125rem; max-width: 700px; margin: 0 auto 48px; line-height: 1.75; padding: 0 24px;">
                    The fund collection is based on SWF fees collected from registered students.
                </p>
                <div class="contribution-cards">
                    <div class="contribution-card">
                        <div class="contribution-icon" aria-hidden="true">👨‍🎓</div>
                        <div class="contribution-value" data-target="30">0</div>
                        <div class="contribution-label">RM per Semester</div>
                        <div class="contribution-description">Local Student</div>
                    </div>
                    <div class="contribution-card">
                        <div class="contribution-icon" aria-hidden="true">🌍</div>
                        <div class="contribution-value" data-target="50">0</div>
                        <div class="contribution-label">RM per Semester</div>
                        <div class="contribution-description">International Student</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SWF Campus Committee Members Section -->
        <section class="section committee" aria-labelledby="committee-title">
            <div class="container">
                <div class="committee-wrapper">
                    <!-- Left Column: Title and Description -->
                    <div class="committee-left">
                        <h2 id="committee-title" class="committee-main-title">SWF Campus<br />Committee Members</h2>
                        <p class="committee-description">
                            The organizational structure of the SWF Campus Committee, showing the hierarchy of roles and responsibilities from top to bottom.
                        </p>
                    </div>
                    
                    <!-- Right Column: Committee Members Cards -->
                    <div class="committee-right">
                        <!-- Level 1: Head of Campus / Dean -->
                        <div class="committee-card">
                            <div class="committee-card-icon">👑</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">Head of Campus / Dean</h3>
                                <p class="committee-card-description">The highest authority in the SWF Campus Committee, providing overall leadership and strategic direction for the Student Welfare Fund.</p>
                            </div>
                        </div>
                        
                        <!-- Level 2: Deputy Dean, SDCL -->
                        <div class="committee-card">
                            <div class="committee-card-icon">🎓</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">Deputy Dean, SDCL</h3>
                                <p class="committee-card-description">Supports the Head of Campus in managing student development and campus lifestyle initiatives, ensuring alignment with institutional goals.</p>
                            </div>
                        </div>
                        
                        <!-- Level 3: Campus Lifestyle Head -->
                        <div class="committee-card">
                            <div class="committee-card-icon">👔</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">Campus Lifestyle Head</h3>
                                <p class="committee-card-description">Oversees the day-to-day operations of campus lifestyle programs and coordinates SWF activities with various departments.</p>
                            </div>
                        </div>
                        
                        <!-- Level 4: Representative of Finance and Administration Department -->
                        <div class="committee-card">
                            <div class="committee-card-icon">💰</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">Representative of Finance and Administration Department</h3>
                                <p class="committee-card-description">Manages financial oversight, budget allocation, and ensures proper administrative procedures are followed for all SWF transactions.</p>
                            </div>
                        </div>
                        
                        <!-- Level 5: Executive, Campus Lifestyle Section -->
                        <div class="committee-card">
                            <div class="committee-card-icon">📋</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">Executive, Campus Lifestyle Section</h3>
                                <p class="committee-card-description">Handles administrative tasks, processes applications, and provides support for committee operations and student inquiries.</p>
                            </div>
                        </div>
                        
                        <!-- Level 6: President of Student Representative Committee -->
                        <div class="committee-card">
                            <div class="committee-card-icon">👥</div>
                            <div class="committee-card-content">
                                <h3 class="committee-card-title">President of Student Representative Committee</h3>
                                <p class="committee-card-description">Represents student interests and provides student perspective in committee decisions. Participates by invitation to ensure student voice is heard.</p>
                            </div>
                        </div>
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

