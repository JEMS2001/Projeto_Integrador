<?php
session_start();

if(isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit; 
}
?>

<?php include 'layout/header.php'; ?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">


<section id="hero" class="hero">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-6 order-2 order-lg-1">
                <h1 data-aos="fade-right" class="text-primary">
                    <i class="fas fa-tasks me-2"></i> Gestão Eficiente para PMEs
                </h1>
                <p data-aos="fade-right" data-aos-delay="100">
                    <i class="fas fa-lightbulb me-2"></i> Otimize o gerenciamento de suas equipes, aumente a produtividade e impulsione o crescimento da sua empresa.
                </p>
                <div class="d-flex justify-content-center justify-content-lg-start" data-aos="fade-right" data-aos-delay="200">
                    <a href="login.php" class="btn-get-started me-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Acessar Sistema
                    </a>
                    <a href="#features" class="btn-learn-more">
                        <i class="fas fa-info-circle me-2"></i> Saiba Mais
                    </a>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-in" data-aos-delay="200">
                <img src="img/hero/hero-img.png" class="img-fluid" alt="Dashboard">
            </div>
        </div>
    </div>
</section>

<section id="features" class="about-section">
    <div class="container">
        <h2 class="section-title text-center mb-5" data-aos="fade-up">
            <i class="fas fa-star me-2 highlight"></i> Recursos Principais
        </h2>
        <div class="row gy-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card text-center">
                    <i class="fas fa-users-cog feature-icon"></i>
                    <h3 class="feature-title">Gestão de Equipes</h3>
                    <p class="feature-description">Gerencie eficientemente suas equipes de funcionários com perfis detalhados e controle de acesso.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center">
                    <i class="fas fa-calendar-check feature-icon"></i>
                    <h3 class="feature-title">Agendamento de Reuniões</h3>
                    <p class="feature-description">Agende, realize e grave reuniões online com facilidade, melhorando a comunicação da equipe.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card text-center">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3 class="feature-title">Monitoramento de Produtividade</h3>
                    <p class="feature-description">Acompanhe o desempenho da equipe e da empresa com relatórios analíticos detalhados.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="about-section bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5" data-aos="fade-up">
            <i class="fas fa-cogs me-2 highlight"></i> Nossos Serviços
        </h2>
        <div class="row gy-4">
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <i class="fas fa-user-clock feature-icon"></i>
                    <h3 class="feature-title">Controle de Presença</h3>
                    <p class="feature-description">Monitore a presença e pontualidade dos funcionários de forma eficiente e automatizada.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <i class="fas fa-tasks feature-icon"></i>
                    <h3 class="feature-title">Gestão de Tarefas</h3>
                    <p class="feature-description">Atribua, acompanhe e avalie o progresso das tarefas utilizando metodologias ágeis.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <i class="fas fa-chart-pie feature-icon"></i>
                    <h3 class="feature-title">Relatórios Analíticos</h3>
                    <p class="feature-description">Gere relatórios detalhados sobre o desempenho da equipe e da empresa para tomadas de decisão informadas.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <h3 class="feature-title">Segurança de Dados</h3>
                    <p class="feature-description">Garanta a privacidade e segurança dos dados dos funcionários e da empresa com nossa infraestrutura robusta.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="cta" class="about-section">
    <div class="container text-center">
        <h2 data-aos="fade-up">Pronto para revolucionar a gestão da sua empresa?</h2>
        <p data-aos="fade-up" data-aos-delay="100">Junte-se a centenas de PMEs que já estão aumentando sua produtividade com nossa plataforma.</p>
        <a href="cadastro.php" class="btn-get-started btn-lg mt-3" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-rocket me-2"></i> Comece Agora
        </a>
    </div>
</section>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true
    });

    function animateValue(id, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            document.getElementById(id).innerHTML = value + (id === 'efficiencyIncrease' ? '%' : '');
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateValue("clientCount", 0, 1000, 2000);
                animateValue("efficiencyIncrease", 0, 40, 2000);
                animateValue("dataProcessed", 0, 500, 2000);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe(document.querySelector('.stats-container'));
</script>


