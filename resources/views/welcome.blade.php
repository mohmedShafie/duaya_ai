<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon | Duaya AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .container {
            text-align: center;
            z-index: 1;
            padding: 2rem;
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards 0.5s;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards 0.8s;
        }

        .countdown {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards 1.1s;
        }

        .countdown-box {
            margin: 0 1rem;
            min-width: 80px;
        }

        .countdown-value {
            font-size: 2.5rem;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .countdown-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .email-form {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards 1.4s;
        }

        input[type="email"] {
            padding: 1rem;
            width: 100%;
            max-width: 300px;
            border: none;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
        }

        button {
            padding: 1rem 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        button:hover {
            background: #2980b9;
        }

        .social-links {
            display: flex;
            justify-content: center;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards 1.7s;
        }

        .social-icon {
            margin: 0 1rem;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            transition: transform 0.3s, background 0.3s;
        }

        .social-icon:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(20vw);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }

            p {
                font-size: 1rem;
            }

            .countdown {
                flex-wrap: wrap;
            }

            .countdown-box {
                margin: 0.5rem;
                min-width: 60px;
            }

            .countdown-value {
                font-size: 1.8rem;
            }

            .email-form {
                flex-direction: column;
                align-items: center;
            }

            input[type="email"] {
                border-radius: 4px;
                margin-bottom: 1rem;
            }

            button {
                border-radius: 4px;
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
<div class="particles" id="particles"></div>

<div class="container">
    <h1>Duaya Ai is Coming Soon</h1>
    <p>We're working hard to bring you something amazing. Our website is under construction, but we're nearly there.</p>

    <div class="countdown" id="countdown">
        <div class="countdown-box">
            <div class="countdown-value" id="days">00</div>
            <div class="countdown-label">Days</div>
        </div>
        <div class="countdown-box">
            <div class="countdown-value" id="hours">00</div>
            <div class="countdown-label">Hours</div>
        </div>
        <div class="countdown-box">
            <div class="countdown-value" id="minutes">00</div>
            <div class="countdown-label">Minutes</div>
        </div>
        <div class="countdown-box">
            <div class="countdown-value" id="seconds">00</div>
            <div class="countdown-label">Seconds</div>
        </div>
    </div>

   {{-- <div class="email-form">
       <input type="email" placeholder="Enter your email">
       <button>Notify Me</button>
   </div> --}}

   {{-- <div class="social-links">
       <a href="#" class="social-icon">f</a>
       <a href="#" class="social-icon">t</a>
       <a href="#" class="social-icon">in</a>
       <a href="#" class="social-icon">ig</a>
   </div>
</div> --}}

<script>
    // Countdown Timer
    const countDownDate = new Date();
    countDownDate.setDate(countDownDate.getDate() + 30); // Launch in 30 days

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
        document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
        document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
        document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    // Particles Animation
    const particlesContainer = document.getElementById('particles');
    const particleCount = 50;

    for (let i = 0; i < particleCount; i++) {
        createParticle();
    }

    function createParticle() {
        const particle = document.createElement('div');
        particle.classList.add('particle');

        // Random size
        const size = Math.random() * 5 + 2;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;

        // Random position
        const posX = Math.random() * 100;
        const posY = Math.random() * 100 + 100; // Start below viewport
        particle.style.left = `${posX}%`;
        particle.style.bottom = `${-posY}px`;

        // Random duration and delay
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        particle.style.animation = `float ${duration}s ${delay}s`;

        particlesContainer.appendChild(particle);

        // Remove and recreate particles after animation completes
        setTimeout(() => {
            particle.remove();
            createParticle();
        }, (duration + delay) * 1000);
    }
</script>
</body>
</html>
