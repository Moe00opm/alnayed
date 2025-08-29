<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change according to your settings
$password = ""; // Change according to your settings
$dbname = "portfolio_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process contact form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $success_message = "Your message has been sent successfully!";
    } else {
        $error_message = "Error sending message: " . $conn->error;
    }
}

// Fetch messages from database
$messages = array();
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

// Check if we're on the messages page
$is_messages_page = isset($_GET['page']) && $_GET['page'] == 'messages';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio | Creative Developer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS السابق يبقى كما هو مع إضافة بعض التحسينات */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #FFD700;
            --secondary-color: #000000;
            --accent-color: #1a1a1a;
            --text-color: #ffffff;
            --dashboard-bg: #0f0f0f;
        }

        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Dashboard Navigation */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--accent-color);
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 5px 0 15px rgba(255, 215, 0, 0.1);
            z-index: 100;
        }

        .profile {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            margin-bottom: 1.2rem;
        }

        .profile h2 {
            font-size: 1.6rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .profile p {
            color: #ccc;
            font-size: 1rem;
        }

        .nav-links {
            list-style: none;
            margin-top: 2rem;
        }

        .nav-links li {
            margin-bottom: 1.2rem;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s;
            font-weight: 500;
            display: block;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background-color: rgba(255, 215, 0, 0.1);
            color: var(--primary-color);
        }

        .nav-links i {
            margin-right: 10px;
            width: 25px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        /* Welcome message */
        .welcome-message {
            height: 90vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .welcome-message h1 {
            font-size: 4.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.5);
        }

        .welcome-message p {
            font-size: 1.8rem;
            max-width: 800px;
            margin-bottom: 2.5rem;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: var(--secondary-color);
            padding: 1rem 2.5rem;
            font-size: 1.3rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s;
            border: 2px solid var(--primary-color);
        }

        .cta-button:hover {
            background-color: transparent;
            color: var(--primary-color);
        }

        /* Skills section */
        .skills-section {
            padding: 5rem 0;
            text-align: center;
        }

        .section-title {
            font-size: 3.5rem;
            margin-bottom: 3rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background-color: var(--primary-color);
        }

        .skills-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-top: 4rem;
        }

        .skill-card {
            background-color: var(--accent-color);
            border-radius: 15px;
            padding: 2.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            border: 2px solid transparent;
        }

        .skill-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(255, 215, 0, 0.2);
            border-color: var(--primary-color);
        }

        .skill-icon {
            font-size: 4.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .skill-card h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .skill-card p {
            font-size: 1.2rem;
            color: #ccc;
        }

        /* Contact section */
        .contact-section {
            padding: 5rem 0;
            text-align: center;
            background-color: var(--accent-color);
            border-radius: 15px;
            margin: 2rem 0;
            padding: 3rem;
        }

        .contact-form {
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 3rem;
            border-radius: 20px;
            border: 2px solid var(--primary-color);
        }

        .form-group {
            margin-bottom: 2rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1.2rem;
            border: 2px solid #333;
            border-radius: 10px;
            background-color: #111;
            color: var(--text-color);
            font-size: 1.1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            padding: 1.2rem 3rem;
            font-size: 1.3rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        /* Messages popup */
        .message-popup {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 1.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: translateY(150%);
            transition: transform 0.5s;
            z-index: 1000;
            max-width: 400px;
        }

        .message-popup.show {
            transform: translateY(0);
        }

        .message-popup h3 {
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        /* My Mission Page */
        .my-mission-page {
            padding: 2rem;
        }

        .mission-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            border-radius: 15px;
        }

        .mission-header h2 {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .mission-header p {
            font-size: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .mission-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .mission-card {
            background-color: var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s;
            border: 2px solid transparent;
        }

        .mission-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
        }

        .mission-card i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .mission-card h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .mission-card p {
            color: #ccc;
            line-height: 1.6;
        }

        .mission-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .stat-box {
            background: linear-gradient(to bottom right, #1a1a1a, #000);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            border: 2px solid var(--primary-color);
        }

        .stat-box h3 {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-box p {
            color: #ccc;
            font-size: 1.2rem;
        }

        .mission-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .mission-content p {
            font-size: 1.4rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            padding: 2rem;
            margin: 2rem 0;
        }

        .logo {
            font-size: 3.5rem;
            font-weight: bold;
            color: var(--primary-color);
            display: inline-block;
            padding: 1rem 2rem;
            border: 3px solid var(--primary-color);
            border-radius: 15px;
            margin: 1rem 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Admin Messages Page */
        .admin-messages-page {
            padding: 2rem;
        }

        .admin-messages-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .admin-messages-header h2 {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: var(--accent-color);
            border-radius: 10px;
            overflow: hidden;
        }

        .messages-table th,
        .messages-table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        .messages-table th {
            background-color: var(--primary-color);
            color: var(--secondary-color);
            font-weight: bold;
        }

        .messages-table tr:hover {
            background-color: rgba(255, 215, 0, 0.1);
        }

        .no-messages {
            text-align: center;
            padding: 2rem;
            color: #ccc;
            font-size: 1.2rem;
        }

        /* Projects section */
        .projects-section {
            padding: 5rem 0;
            text-align: center;
        }

        /* New features */
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }

        .btn-danger {
            background-color: #ff4444;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .message-details {
            background-color: var(--accent-color);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid var(--primary-color);
        }

        .message-details h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .message-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            color: #ccc;
            font-size: 0.9rem;
        }

        /* Responsive design */
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1.5rem;
            }

            .main-content {
                margin-left: 0;
            }

            .profile-img {
                width: 120px;
                height: 120px;
            }

            .nav-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }

            .nav-links li {
                margin-bottom: 0;
            }

            .nav-links a {
                padding: 0.6rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .welcome-message h1 {
                font-size: 3rem;
            }

            .welcome-message p {
                font-size: 1.3rem;
            }

            .skills-container {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2.5rem;
            }

            .contact-form {
                padding: 1.5rem;
            }

            .mission-content p {
                font-size: 1.2rem;
            }

            .mission-header h2 {
                font-size: 2.5rem;
            }

            .mission-header p {
                font-size: 1.2rem;
            }

            .mission-cards {
                grid-template-columns: 1fr;
            }

            .mission-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .messages-table {
                display: block;
                overflow-x: auto;
            }
        }

        .page {
            display: none;
        }

        .page.active {
            display: block;
        }
    </style>
</head>

<body>
    <?php if ($is_messages_page): ?>
        <!-- صفحة الرسائل المنفصلة -->
        <div class="dashboard">
            <div class="sidebar">
                <div class="profile">
                    <img src="cea77ada-57e6-4843-8694-247f1e01bf48.jpg" alt="Profile Photo" class="profile-img">
                    <h2>Abu Al-Nayed</h2>
                    <p>Full Stack Developer</p>
                </div>

                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Back to Portfolio</a></li>
                    <li><a href="messages.php?page=messages" class="active"><i class="fas fa-comments"></i> Messages</a></li>
                </ul>

                <div class="logo-section">
                    <div class="logo">DEV&lt;&gt;SOLUTIONS</div>
                    <p>Web Development & Design</p>
                </div>
            </div>

            <div class="main-content">
                <div class="admin-messages-page">
                    <div class="admin-messages-header">
                        <h2>Messages Management</h2>
                        <p>This page displays all messages sent by visitors</p>
                    </div>

                    <div class="user-messages-list">
                        <h3>Received Messages (<?php echo count($messages); ?>)</h3>
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message-details">
                                    <h3><?php echo htmlspecialchars($message['name']); ?></h3>
                                    <div class="message-meta">
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo $message['created_at']; ?></span>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    <div class="message-actions">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">Reply</a>
                                        <a href="#" class="btn btn-danger">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-messages">No messages received yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- صفحة البورتفوليو الرئيسية -->
        <div class="dashboard">
            <!-- Sidebar Navigation -->
            <div class="sidebar">
                <div class="profile">
                    <img src="cea77ada-57e6-4843-8694-247f1e01bf48.jpg" alt="Profile Photo" class="profile-img">
                    <h2>Abu Al-Nayed</h2>
                    <p>Full Stack Developer</p>
                </div>

                <ul class="nav-links">
                    <li><a href="#home" class="nav-link active" data-page="home-page"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#skills" class="nav-link" data-page="skills-page"><i class="fas fa-code"></i> Skills</a></li>
                    <li><a href="#projects" class="nav-link" data-page="projects-page"><i class="fas fa-briefcase"></i> Projects</a></li>
                    <li><a href="#my-mission" class="nav-link" data-page="my-mission-page"><i class="fas fa-bullseye"></i> My Mission</a></li>
                    <li><a href="#contact" class="nav-link" data-page="contact-page"><i class="fas fa-envelope"></i> Contact Me</a></li>
                    <li><a href="messages.php" class="nav-link"><i class="fas fa-comments"></i> Messages</a></li>
                    
                </ul>

                <div class="logo-section">
                    <div class="logo">&lt...&gt;</div>
                    <p>Web Development & Design</p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Home Page -->
                <div id="home-page" class="page active">
                    <section class="welcome-message">
                        <h1>Welcome to My Portfolio</h1>
                        <p>I'm a passionate software developer who loves creating amazing digital experiences</p>
                        <a href="#contact" class="cta-button">Contact Me</a>
                    </section>
                </div>

                <!-- Skills Page -->
                <div id="skills-page" class="page">
                    <section class="skills-section">
                        <h2 class="section-title">Technical Skills</h2>
                        <div class="skills-container">
                            <div class="skill-card">
                                <i class="fab fa-html5 skill-icon"></i>
                                <h3>HTML5</h3>
                                <p>Structuring web pages with the latest HTML5 standards</p>
                            </div>
                            <div class="skill-card">
                                <i class="fab fa-css3-alt skill-icon"></i>
                                <h3>CSS3</h3>
                                <p>Designing and styling web pages with modern CSS3 techniques</p>
                            </div>
                            <div class="skill-card">
                                <i class="fab fa-js skill-icon"></i>
                                <h3>JavaScript</h3>
                                <p>Adding interactivity and dynamism to websites</p>
                            </div>
                            <div class="skill-card">
                                <i class="fab fa-php skill-icon"></i>
                                <h3>PHP</h3>
                                <p>Developing dynamic and robust web applications</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-database skill-icon"></i>
                                <h3>MySQL</h3>
                                <p>Designing and managing relational databases</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-filter skill-icon"></i>
                                <h3>Data Filtering</h3>
                                <p>Processing, filtering and refining data efficiently</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-paint-brush skill-icon"></i>
                                <h3>Graphic Design</h3>
                                <p>Designing user interfaces and user experiences</p>
                            </div>
                            <div class="skill-card">
                                <i class="fab fa-react skill-icon"></i>
                                <h3>React</h3>
                                <p>Building interactive web applications using React library</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- My Mission Page -->
                <div id="my-mission-page" class="page">
                    <div class="my-mission-page">
                        <div class="mission-header">
                            <h2>My Mission & Vision</h2>
                            <p>I strive to achieve technical excellence and build innovative digital solutions that meet the needs of the modern era</p>
                        </div>

                        <div class="mission-cards">
                            <div class="mission-card">
                                <i class="fas fa-lightbulb"></i>
                                <h3>Innovation</h3>
                                <p>I work on providing innovative ideas and solutions that combine aesthetics with advanced technical functions</p>
                            </div>
                            <div class="mission-card">
                                <i class="fas fa-users"></i>
                                <h3>Collaboration</h3>
                                <p>I believe in the power of teamwork and strive to build strategic partnerships with clients and colleagues</p>
                            </div>
                            <div class="mission-card">
                                <i class="fas fa-rocket"></i>
                                <h3>Development</h3>
                                <p>I am committed to continuous development of my skills and providing the latest technologies in every project</p>
                            </div>
                        </div>

                        <div class="mission-stats">
                            <div class="stat-box">
                                <h3>50+</h3>
                                <p>Completed Projects</p>
                            </div>
                            <div class="stat-box">
                                <h3>35+</h3>
                                <p>Satisfied Clients</p>
                            </div>
                            <div class="stat-box">
                                <h3>5+</h3>
                                <p>Years of Experience</p>
                            </div>
                            <div class="stat-box">
                                <h3>15+</h3>
                                <p>Mastered Technologies</p>
                            </div>
                        </div>

                        <div class="mission-content">
                            <h2 class="section-title">How I Implement My Mission</h2>
                            <p>I follow an integrated methodology that begins with understanding and accurately analyzing client requirements, then design and creativity, followed by precise technical implementation, and finally review and testing to ensure the quality of the final product.</p>
                            <p>I am keen to use the latest technologies and best practices in software development to ensure providing integrated, scalable and easy-to-maintain solutions.</p>
                            <p>My vision is to create a better digital world through smart software solutions that inspire others and make their lives easier.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Page -->
                <div id="contact-page" class="page">
                    <section class="contact-section">
                        <h2 class="section-title">Contact Me</h2>
                        <?php if (isset($success_message)): ?>
                            <div class="message-popup show" id="message-popup">
                                <h3>Thank You!</h3>
                                <p><?php echo $success_message; ?></p>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="message-popup show" id="message-popup" style="background-color: #ff4444;">
                                <h3>Sorry!</h3>
                                <p><?php echo $error_message; ?></p>
                            </div>
                        <?php endif; ?>
                        <form class="contact-form" method="POST" action="">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" required></textarea>
                            </div>
                            <button type="submit" name="submit_contact" class="submit-btn">Send Message</button>
                        </form>
                    </section>
                </div>

                <!-- Projects Page -->
                <div id="projects-page" class="page">
                    <section class="skills-section">
                        <h2 class="section-title">My Projects</h2>
                        <div class="skills-container">
                            <div class="skill-card">
                                <i class="fas fa-shopping-cart skill-icon"></i>
                                <h3>E-commerce Platform</h3>
                                <p>Online store with full specifications and payment integration</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-mobile-alt skill-icon"></i>
                                <h3>Fitness App</h3>
                                <p>Mobile application for tracking exercises and nutrition</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-chart-line skill-icon"></i>
                                <h3>Data Analytics Dashboard</h3>
                                <p>Real-time data visualization for business metrics</p>
                            </div>
                            <div class="skill-card">
                                <i class="fas fa-filter skill-icon"></i>
                                <h3>Data Filtering System</h3>
                                <p>Advanced system for filtering and processing data</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Message Popup -->
        <div class="message-popup" id="message-popup">
            <h3>Thank You!</h3>
            <p>Your message has been received successfully and you will be contacted soon.</p>
        </div>

        <script>
            // Navigation between pages
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

                    // Add active class to clicked link
                    this.classList.add('active');

                    // Hide all pages
                    document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));

                    // Show the targeted page
                    const pageId = this.getAttribute('data-page');
                    document.getElementById(pageId).classList.add('active');

                    // Scroll to top
                    window.scrollTo(0, 0);
                });
            });

            // Hide message popup after 5 seconds
            setTimeout(() => {
                const popup = document.getElementById('message-popup');
                if (popup) {
                    popup.classList.remove('show');
                }
            }, 5000);
        </script>
    <?php endif; ?>
</body>

</html>