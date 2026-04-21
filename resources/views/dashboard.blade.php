<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة القيادة الأكاديمية | EduTrack</title>
    <!-- استدعاء خط كايرو من جوجل والأيقونات الحديثة -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Outfit:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* 🎨 الهوية الأكاديمية الموحدة (نفس ألوان صفحة الدخول بالضبط) */
        :root {
            --primary: #1e40af; 
            --primary-light: #3b82f6; 
            --accent: #d97706; 
            --bg-gradient-1: #f8fafc;
            --bg-gradient-2: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --card-bg: rgba(255, 255, 255, 0.85); 
        }
        body {
            font-family: 'Cairo', sans-serif; margin: 0; min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-gradient-1) 0%, var(--bg-gradient-2) 100%);
            color: var(--text-dark); display: flex; flex-direction: column;
            position: relative; overflow-x: hidden;
        }
        
        /* 🫧 مؤثرات الخلفية الناعمة جداً (للتوحيد مع الواجهة) */
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(100px); z-index: -1; opacity: 0.5; animation: float 15s infinite alternate; }
        .shape-1 { background: rgba(59, 130, 246, 0.3); width: 600px; height: 600px; top: -100px; left: -150px; }
        .shape-2 { background: rgba(217, 119, 6, 0.2); width: 500px; height: 500px; bottom: 50px; right: -100px; animation-delay: -5s; }
        @keyframes float { 0% { transform: translate(0, 0); } 100% { transform: translate(30px, 30px); } }

        /* 🧩 الشريط العلوي (النافيجيشن) المتصل بالزجاج */
        .navbar {
            background: var(--card-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.8); padding: 15px 50px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.05); position: sticky; top: 0; z-index: 100;
        }
        .logo { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
        .nav-links a { color: var(--text-muted); text-decoration: none; font-weight: 700; margin-right: 35px; font-size: 1.05rem; transition: 0.3s; position: relative;}
        .nav-links a:hover, .nav-links a.active { color: var(--primary); }
        .nav-links a.active::after { content: ''; position: absolute; bottom: -20px; left: 0; width: 100%; height: 3px; background: var(--primary); border-radius: 3px 3px 0 0; }
        
        .btn-glow {
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; padding: 10px 24px; border-radius: 12px;
            font-weight: 800; font-family: 'Cairo', sans-serif; cursor: pointer; box-shadow: 0 6px 15px rgba(30, 64, 175, 0.2); transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-glow:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3); }

        /* 📦 الحاوية الرئيسية للوحة (Dashboard) */
        .dashboard-container {
            width: 100%; max-width: 1200px; margin: 40px auto; padding: 0 20px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); flex-grow: 1;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* 🏷️ لافتة ترحيبية أنيقة وناعمة */
        .hero-banner {
            background: var(--card-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8); border-radius: 24px; padding: 45px 50px;
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(30, 64, 175, 0.05); position: relative; overflow: hidden;
        }
        /* لمسة هندسية جميلة في طرف المربع */
        .hero-banner::before { content: ''; position: absolute; left: 0; top: 0; width: 6px; height: 100%; background: linear-gradient(to bottom, var(--primary), var(--accent)); }
        
        .hero-content { width: 70%; z-index: 2; }
        .hero-content h1 { font-size: 2.4rem; color: var(--primary); margin-top: 0; margin-bottom: 10px; }
        .hero-content p { color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 0; font-weight: 600;}
        .hero-icon { font-size: 130px; color: var(--primary-light); opacity: 0.15; position: absolute; left: 30px; bottom: -20px; }

        /* 📊 شبكة الإحصائيات الفخمة الناعمة */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        
        .glass-card {
            background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px; padding: 35px 30px; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 30px rgba(30, 64, 175, 0.05); display: flex; flex-direction: column; align-items: center; text-align: center;
            text-decoration: none; color: inherit;
        }
        .glass-card:hover { transform: translateY(-10px); box-shadow: 0 25px 45px rgba(30, 64, 175, 0.1); border-color: var(--primary-light); }
        
        .card-icon {
            width: 75px; height: 75px; border-radius: 20px; display: flex; justify-content: center; align-items: center;
            font-size: 1.8rem; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: 0.3s;
        }
        .glass-card:hover .card-icon { transform: scale(1.1) rotate(-5deg); }
        .icon-blue { background: rgba(59, 130, 246, 0.1); color: var(--primary); border: 1px solid rgba(59, 130, 246, 0.2); }
        .icon-gold { background: rgba(217, 119, 6, 0.1); color: var(--accent); border: 1px solid rgba(217, 119, 6, 0.2); }
        .icon-purple { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2); }

        .glass-card h3 { font-size: 1.3rem; font-weight: 700; color: var(--text-dark); margin-bottom: 5px; }
        .glass-card p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 15px; font-weight: 600;}
        .card-number { font-size: 3rem; font-weight: 800; color: var(--primary); font-family: 'Outfit', sans-serif; line-height: 1; }
    </style>
</head>
<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <nav class="navbar">
        <div class="logo">
            <i class="fa-solid fa-graduation-cap"></i> EduTrack
        </div>
        <div class="nav-links">
            <a href="/dashboard" class="active">لوحة القيادة</a>
            <a href="/all-departments">الأقسام</a>
            <a href="/researches">الأبحاث العلمية</a>
        </div>
        <div style="display: flex; align-items:center; gap: 20px;">
            @auth
                 <span style="color: var(--text-dark); font-weight: 700; background: rgba(255,255,255,0.5); padding: 8px 15px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.05);">{{ Auth::user()->name }} <i class="fa-solid fa-user-circle" style="color:var(--primary); margin-right:5px;"></i></span>
                 <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-glow" style="background: white; color: #ef4444; border: 2px solid #fca5a5; box-shadow: none;">خروج <i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                 </form>
            @else
                <a href="/login" class="btn-glow">تسجيل الدخول <i class="fa-solid fa-arrow-left"></i></a>
            @endauth
        </div>
    </nav>

    <div class="dashboard-container">
        
        <div class="hero-banner">
            <div class="hero-content">
                <h1>مرحباً بك في المحور الأكاديمي ✨</h1>
                <p>لقد قمنا بتجاوز جدار الباسوورد بناءً على طلبك للدخول الحر! وتم إعادة هيكلة هذا النظام بأجمل الألوان الأكاديمية (الأزرق الملكي والذهبي) مع لمسات الزجاج الشفاف الناعمة جداً الخالية من الإزعاج لتوفير تجربة نقية.</p>
            </div>
            <i class="fa-solid fa-university hero-icon"></i>
        </div>

        <div class="stats-grid">
            
            <a href="/all-departments" style="text-decoration:none;">
                <div class="glass-card">
                    <div class="card-icon icon-blue">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3>الأقسام والكليات</h3>
                    <p>إجمالي الأقسام المسجلة في النظام بقاعدة بياناتنا حالياً</p>
                    <div class="card-number">{{\App\Models\Department::count()}}</div>
                </div>
            </a>

            <div class="glass-card">
                <div class="card-icon icon-gold">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3>الأبحاث المرفوعة</h3>
                <p>أبحاث وتقارير الطلاب والأكاديميين المُنجزة</p>
                <div class="card-number">0</div>
            </div>

            <div class="glass-card">
                <div class="card-icon icon-purple">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h3>الفعاليات الأكاديمية</h3>
                <p>الندوات واللقاءات العلمية المجدولة بالموقع</p>
                <div class="card-number">0</div>
            </div>

        </div>
    </div>

</body>
</html>
