<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | EduTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* 🎨 الهوية الأكاديمية الموحدة للموقع */
        :root {
            --primary: #1e40af; /* أزرق ملكي أكاديمي */
            --primary-light: #3b82f6; 
            --accent: #d97706; /* لمسة ذهبية دافئة */
            --bg-gradient-1: #f8fafc;
            --bg-gradient-2: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --card-bg: rgba(255, 255, 255, 0.85); /* زجاج أبيض شفاف وناعم */
        }
        body {
            font-family: 'Cairo', sans-serif;
            margin: 0; min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-gradient-1) 0%, var(--bg-gradient-2) 100%);
            color: var(--text-dark);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow-x: hidden;
        }
        /* 🫧 مؤثرات الخلفية الناعمة جداً */
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(100px); z-index: -1; opacity: 0.6; animation: float 15s infinite alternate; }
        .shape-1 { background: rgba(59, 130, 246, 0.3); width: 600px; height: 600px; top: -200px; left: -150px; }
        .shape-2 { background: rgba(217, 119, 6, 0.2); width: 500px; height: 500px; bottom: -150px; right: -100px; animation-delay: -5s; }
        @keyframes float { 0% { transform: translate(0, 0); } 100% { transform: translate(30px, 30px); } }
        
        .login-container { width: 100%; max-width: 450px; padding: 20px; perspective: 1000px; }
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8); border-radius: 24px;
            padding: 45px 40px; text-align: center;
            box-shadow: 0 25px 50px rgba(30, 64, 175, 0.08);
            transform-style: preserve-3d; animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        .logo-icon { font-size: 3rem; color: var(--primary); margin-bottom: 10px; filter: drop-shadow(0 4px 6px rgba(30, 64, 175, 0.2)); }
        h1 { color: var(--primary); font-size: 2rem; margin-bottom: 5px; font-weight: 800; letter-spacing: -0.5px; }
        p.subtitle { color: var(--text-muted); font-size: 1rem; margin-bottom: 35px; line-height: 1.5; }
        
        /* الحقول */
        .form-group { text-align: right; margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--text-dark); font-size: 0.95rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); transition: 0.3s; }
        .form-group input {
            width: 100%; padding: 14px 15px 14px 40px; border-radius: 12px;
            border: 2px solid #e2e8f0; background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark); font-family: 'Cairo', sans-serif; font-size: 1rem;
            transition: all 0.3s ease; box-sizing: border-box; font-weight: 600;
        }
        .form-group input:focus {
            outline: none; border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: white;
        }
        .form-group input:focus + i, .input-wrapper input:focus ~ i { color: var(--primary); }
        .error-msg { color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: block; font-weight: 600;}
        
        /* الأزرار (Buttons) الموحدة الناعمة */
        .btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white; border: none; padding: 15px 25px; border-radius: 12px;
            cursor: pointer; font-size: 1.1rem; font-weight: 800; font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease; width: 100%; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.25);
            display: inline-flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px;
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(30, 64, 175, 0.35); }
        .btn:active { transform: translateY(0); }
        
        .links { margin-top: 30px; }
        .links a { color: var(--text-muted); text-decoration: none; transition: 0.3s; font-weight: 700; border-bottom: 2px dashed transparent; padding-bottom: 2px; }
        .links a:hover { color: var(--primary); border-bottom-color: var(--primary); }
    </style>
</head>
<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="login-container">
        <div class="glass-card">
            <i class="fa-solid fa-graduation-cap logo-icon"></i>
            <h1>EduTrack</h1>
            <p class="subtitle">بوابتك الذكية لإدارة مسارك التعليمي<br>سجل دخولك الآن</p>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label>البريد الإلكتروني الجامعي</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="student@university.edu" dir="ltr" style="text-align: right;">
                    </div>
                    @error('email') <span class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" required placeholder="••••••••" dir="ltr" style="text-align: right;">
                    </div>
                    @error('password') <span class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn">تسجيل الدخول <i class="fa-solid fa-arrow-left"></i></button>
                
                <div class="links">
                    <a href="{{ route('register') }}">طالب جديد؟ انضم للأكاديمية من هنا</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
