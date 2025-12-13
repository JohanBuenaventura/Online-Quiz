<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Quiz System</title>
  <link rel="stylesheet" href="assets/css/landing.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <header class="navbar">
    <div class="logo">Online<span>Quiz</span></div>

    <!-- Mobile Menu Button -->
    <div class="menu-btn" id="menuBtn">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar / Mobile Nav -->
    <nav class="nav-links" id="mobileNav">
      <a href="#features">Features</a>
      <a href="#about">About</a>
      <a href="pages/login.php">Log In</a>
      <a href="pages/register.php">Sign Up</a>
    </nav>
</header>


  <section class="hero" id="hero">
    <div class="hero-content">
      <h1>Empower Learning Through <span style="color: #3a86ff;">Live Quizzes.</span></h1>
      <p>Join our platform where teachers create engaging quizzes and students learn interactively in real-time. Simple, fast, and effective.</p>
      
      <div class="cta-group">
        <div style="width: 100%;">
            <span class="cta-label">Join as:</span>
        </div>
        <a href="pages/register.php?role=student" class="btn-primary">
            <i class="fas fa-user-graduate"></i> Student
        </a>
        <a href="pages/register.php?role=teacher" class="btn-outline">
            <i class="fas fa-chalkboard-teacher"></i> Teacher
        </a>
      </div>
    </div>

    <div class="hero-image">
      <img src="assets/images/quiz_illustration.png" alt="Online quiz illustration">
    </div>
  </section>

  <section id="features" class="features">
    <h2 class="section-title">Why Choose Us?</h2>
    <p class="section-subtitle">Everything you need to manage quizzes effectively.</p>
    
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-magic"></i></div>
        <h3>Smart Creation</h3>
        <p>Teachers can create, edit, and publish quizzes in seconds using our intuitive builder.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-broadcast-tower"></i></div>
        <h3>Live Sessions</h3>
        <p>Host live interactive quizzes with unique session codes that students can join instantly.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-trophy"></i></div>
        <h3>Leaderboards</h3>
        <p>Boost student engagement through competitive scoring and real-time ranking.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
        <h3>Progress Tracking</h3>
        <p>Monitor student results and get detailed performance analytics after every session.</p>
      </div>
    </div>
  </section>

  <section class="about" id="about">
    <div class="about-wrapper">
      <div class="about-text">
        <h2 class="section-title">About the Platform</h2>
        <p style="line-height: 1.8; color: #555; margin-bottom: 20px;">
          <strong>Online Quiz System</strong> is built for collaboration. We bridge the gap between traditional teaching and digital engagement.
        </p>
        <p style="line-height: 1.8; color: #555; margin-bottom: 25px;">
          Whether you’re learning or teaching, we create a fun environment that makes every quiz feel like a challenge worth mastering.
        </p>
        <a href="#hero" class="btn-primary">Start Quizzing Now</a>
      </div>
      <div class="about-image">
        <img src="assets/images/learning_collab.png" alt="Students collaborating">
      </div>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <?= date('Y') ?> Online Quiz System | Developed by Johan</p>
  </footer>

  <script>
const menuBtn = document.getElementById("menuBtn");
const mobileNav = document.getElementById("mobileNav");

menuBtn.addEventListener("click", () => {
    mobileNav.classList.toggle("active");

    // Change icon
    menuBtn.innerHTML = mobileNav.classList.contains("active")
        ? '<i class="fas fa-times"></i>'
        : '<i class="fas fa-bars"></i>';
});
</script>

</body>
</html>