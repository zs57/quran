<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>نور القلوب - منصة القرآن الكريم</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700;900&family=Kalam:wght@700&display=swap" rel="stylesheet">
  
  <!-- PWA & Service Worker -->
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#e76f51">
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').then(reg => {
          console.log('SW registered!', reg);
        }).catch(err => console.log('SW registration failed', err));
      });
    }
  </script>
  
   <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  
  <!-- JSZip for Client-Side Downloading -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  
  <!-- Styles -->
  <link rel="stylesheet" href="/style.css">
</head>
<body>

  <!-- Side Drawer (Surah Index) -->
  <div id="side-drawer" class="side-drawer">
    <div class="drawer-header">
      <h3>فهرس السور</h3>
      <button class="btn-icon" onclick="toggleDrawer()"><i class="ri-close-line"></i></button>
    </div>
    <div class="drawer-content" id="drawer-surahs">
       <!-- Populated by JS -->
    </div>
  </div>

  <!-- Navigation -->
  <nav id="navbar">
    <a href="#" class="nav-logo" onclick="showView('home')">
      نور<span>القلوب</span> <i class="ri-book-open-fill"></i>
    </a>
    
    <ul class="nav-links">
      <li><a href="#" onclick="showView('home')" class="active-link" id="link-home">الرئيسية</a></li>
      <li><a href="#" onclick="showView('read')" id="link-read">قراءة وتفسير</a></li>
      <li><a href="#" onclick="showView('listen')" id="link-listen">استماع وتحميل</a></li>
      <li><a href="#" onclick="showView('radio')" id="link-radio">إذاعات القرآن <span class="badge high" style="font-size:0.6rem; margin-right:5px; background:var(--accent-red); color:#fff; border:none; animation:pulse 1s infinite;">Live</span></a></li>
      <li><a href="#" onclick="showView('api')" id="link-api" style="color:var(--accent-green);">قسم المطورين (API)</a></li>
    </ul>
    
    <button class="nav-cta" onclick="showView('read')">
      أكمل الختمة <i class="ri-bookmark-fill"></i>
    </button>
    
    <div class="hamburger" onclick="toggleDrawer()">
      <span></span><span></span><span></span>
    </div>
  </nav>

  <!-- Mobile Bottom Nav -->
  <div class="mobile-bottom-nav">
    <a href="#" onclick="showView('home')" class="active" id="mobile-link-home">
      <span class="nav-icon"><i class="ri-home-smile-2-line"></i></span>
      <span>الرئيسية</span>
    </a>
    <a href="#" onclick="showView('read')" id="mobile-link-read">
      <span class="nav-icon"><i class="ri-book-open-line"></i></span>
      <span>قراءة</span>
    </a>
    <a href="#" onclick="showView('listen')" id="mobile-link-listen">
      <span class="nav-icon"><i class="ri-headphone-line"></i></span>
      <span>استماع</span>
    </a>
    <a href="#" onclick="showView('radio')" id="mobile-link-radio">
      <span class="nav-icon"><i class="ri-radio-line"></i></span>
      <span>إحصائيات</span>
    </a>
    <a href="#" onclick="showView('api')" id="mobile-link-api">
      <span class="nav-icon"><i class="ri-braces-line"></i></span>
      <span>API</span>
    </a>
  </div>

  <main id="app-content">
    
    <!-- HOME VIEW -->
    <div id="view-home" class="view-section active">
      <section class="hero">
        <div class="hero-inner">
          <div class="hero-text reveal active">
            <span class="hero-badge">✨ سرعة فائقة • كل القراء</span>
            <h1 class="hero-title">
              طريقك إلى <br>
              <span class="highlight">القرآن الكريم</span>
            </h1>
            <p class="hero-sub">
              استمع لجميع قراء العالم الإسلامي، وحمل السور، واقرأ مع تفاسير متعددة في أسرع منصة قرآنية بتجربة مذهلة.
            </p>
            <div class="hero-buttons">
              <button class="btn-primary" onclick="showView('read')">
                ابدأ القراءة <i class="ri-book-read-line"></i>
              </button>
              <button class="btn-secondary" onclick="showView('listen')">
                المكتبة الصوتية <i class="ri-mv-fill"></i>
              </button>
            </div>
          </div>
          
          <div class="hero-graphic reveal active">
            <div class="notebook-wrap">
              <div class="notebook">
                <div class="note-header">وردك اليومي المحفوظ</div>
                <div class="note-task">
                  <div class="checkbox checked"></div>
                  <span>تذكر آخر سورة قرأتها تلقائياً</span>
                </div>
                <div class="note-task" id="last-read-box">
                  <i class="ri-bookmark-line" style="color:var(--accent-red); font-size:1.2rem; margin-left:0.5rem;"></i>
                  <span>لم تبدأ القراءة بعد</span>
                </div>
                <div class="post-it" style="right:-10px; left:auto; background:var(--accent-green); color:#fff;">سرعة كالبرق! ⚡</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section alt">
        <div class="section-inner">
          <div class="center reveal">
            <span class="section-tag">الجديد <i class="ri-star-smile-fill"></i></span>
            <h2 class="section-title">مميزات احترافية خرافية</h2>
          </div>
          
          <div class="feature-grid reveal" style="margin-top: 2rem;">
            <a href="#" onclick="showView('listen')" class="feature-link">
              <div style="font-size: 2rem; color: var(--accent-red); margin-bottom: 0.5rem;"><i class="ri-mic-2-fill"></i></div>
              <h3>+200 قارئ</h3>
              <p style="color: var(--ink-light); font-weight: 400; margin-top: 0.5rem;">مكتبة ضخمة تضم جميع القراء مع إمكانية التحميل المباشر الكامل والمفرد.</p>
            </a>
            <a href="#" onclick="showView('read')" class="feature-link">
              <div style="font-size: 2rem; color: var(--accent-green); margin-bottom: 0.5rem;"><i class="ri-book-3-fill"></i></div>
              <h3>تفاسير متعددة</h3>
              <p style="color: var(--ink-light); font-weight: 400; margin-top: 0.5rem;">اختر بين الميسر، الجلالين، أو القرطبي في واجهة قراءة مذهلة وسريعة.</p>
            </a>
            <a href="#" onclick="showView('radio')" class="feature-link">
              <div style="font-size: 2rem; color: var(--highlight-orange); margin-bottom: 0.5rem;"><i class="ri-radio-2-fill"></i></div>
              <h3>إذاعات القرآن</h3>
              <p style="color: var(--ink-light); font-weight: 400; margin-top: 0.5rem;">بث حي ومباشر لإذاعات القرآن الكريم والتلاوات المتنوعة 24/7.</p>
            </a>
            <div class="quote-card feature-link" style="border: 2px solid var(--ink); box-shadow: 4px 4px 0 var(--shadow-color);">
              <p class="quote-text">"إِنَّ هَٰذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ"</p>
              <p class="quote-author">سورة الإسراء - 9</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- READ / TAFSIR VIEW -->
    <div id="view-read" class="view-section" style="display: none;">
      <div class="page-section section-inner reveal">
        <div class="center" style="margin-bottom: 2rem;">
          <h2 class="section-title">فهرس السور</h2>
          <div class="search-box panel" style="max-width: 500px; margin: 1rem auto; position: relative;">
            <i class="ri-search-line" style="position:absolute; right:20px; top:25px; color:var(--ink-light);"></i>
            <input type="text" id="surahSearch" placeholder="ابحث عن سورة..." onkeyup="filterSurahs()" style="padding-right: 2.5rem; font-size:1.1rem; height: 50px;">
          </div>
        </div>
        
        <div class="surahs-grid" id="surahs-container">
          <!-- Surah list will be injected here via JS -->
          <div class="center" style="grid-column: 1/-1;">
            <i class="ri-loader-4-line ri-spin" style="font-size: 3rem; color: var(--accent-red);"></i>
            <p>جاري تحميل الفهرس بسرعة البرق...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- SINGLE SURAH READER VIEW -->
    <div id="view-surah" class="view-section" style="display: none;">
      <div class="page-section section-inner reveal">
        
        <div class="panel" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
          <div>
            <button class="btn-secondary" onclick="showView('read')">
              <i class="ri-arrow-right-line"></i> الفهرس
            </button>
          </div>
          <h2 class="section-title" id="reader-surah-title" style="margin: 0; font-family:'Amiri'; color:var(--accent-green);">سورة ...</h2>
          
          <div class="filters">
            <select id="tafsir-select" onchange="changeTafsirSource()" style="width: auto; padding: 0.3rem 1rem; border-radius: 20px; font-weight:700; background: var(--paper);">
              <option value="ar.muyassar">التفسير الميسر</option>
              <option value="ar.jalalayn">تفسير الجلالين</option>
              <option value="ar.qurtubi">تفسير القرطبي</option>
              <option value="ar.tabari">تفسير الطبري</option>
              <option value="ar.baghawi">تفسير البغوي</option>
              <option value="ar.saadi">تفسير السعدي</option>
              <option value="ar.waseet">التفسير الوسيط</option>
            </select>
            <button class="chip active" id="btn-read-mode" onclick="setReaderMode('read')">
              <i class="ri-book-open-line"></i> قراءة
            </button>
            <button class="chip" id="btn-tafsir-mode" onclick="setReaderMode('tafsir')">
              <i class="ri-file-list-3-line"></i> تفسير
            </button>
            <button class="chip" onclick="changeFontSize(4)" title="تكبير الخط">A+</button>
            <button class="chip" onclick="changeFontSize(-4)" title="تصغير الخط">A-</button>
            <button class="chip" onclick="toggleTheme()" style="background:var(--ink); color:#fff;">
              <i class="ri-moon-fill"></i>
            </button>
          </div>
        </div>

        <div class="surah-reader-wrap" id="surah-reader-container">
          <div class="center" style="padding: 3rem;">
            <i class="ri-loader-4-line ri-spin" style="font-size: 3rem; color: var(--accent-red);"></i>
            <p>جاري تحميل الآيات...</p>
          </div>
        </div>
        
      </div>
    </div>

    <!-- LISTEN VIEW -->
    <div id="view-listen" class="view-section" style="display: none;">
      <div class="page-section section-inner reveal">
        <div class="center" style="margin-bottom: 2rem;">
          <h2 class="section-title">المكتبة الصوتية والتحميل</h2>
          <p class="hero-sub" style="margin: 0 auto;">كل قراء العالم الإسلامي بين يديك، استمع وحمل ما تشاء.</p>
        </div>

        <div class="grid-2">
          <div class="panel">
            <h3><i class="ri-user-voice-fill" style="color: var(--accent-red);"></i> 1. اختر القارئ</h3>
            <div class="form-grid">
              <input type="text" id="reciter-search" placeholder="بحث عن قارئ (مثال: العفاسي)..." onkeyup="filterReciters()" style="margin-bottom: 0.3rem;">
              <select id="reciter-select" onchange="loadReciterMoshafs()">
                <option value="">جاري تحميل جميع القراء...</option>
              </select>
            </div>
            
            <div id="moshaf-controls" style="display: none; margin-top: 1.5rem; animation: fadeInUp 0.4s ease;">
              <h3 style="margin-top: 1.5rem;"><i class="ri-mic-line" style="color: var(--highlight-orange);"></i> 2. الرواية وتحميل المصحف كامل</h3>
              <div class="form-grid">
                <select id="moshaf-select" onchange="onMoshafChange()">
                  <option value="">اختر القارئ أولاً...</option>
                </select>
              </div>

              <div id="download-options" style="margin-top: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                 <button class="btn-primary" onclick="downloadFullZIP()" id="btn-download-zip" style="padding:0.7rem; background: var(--accent-green); border:none; box-shadow: 4px 4px 0 var(--ink);">
                   <i class="ri-file-zip-line"></i> تحميل المصحف (ZIP)
                 </button>
                 <button class="btn-secondary" onclick="downloadLinksList()" id="btn-download-txt" style="padding:0.7rem; border:2px solid var(--ink);">
                   <i class="ri-file-list-2-line"></i> قائمة الروابط (txt)
                 </button>
              </div>

              <h3 style="margin-top: 2rem;"><i class="ri-book-fill" style="color: var(--accent-green);"></i> 3. استماع لسورة محددة</h3>
              <div class="form-grid">
                <select id="audio-surah-select" onchange="changeAudioSource()">
                  <option value="">اختر المصحف أولاً...</option>
                </select>
              </div>
              
              <button class="btn-primary" onclick="downloadCurrentSurah()" id="btn-download-surah" disabled style="width:100%; margin-top:1rem; padding:0.8rem; background:var(--ink); box-shadow: 4px 4px 0 var(--accent-red);">
                 <i class="ri-download-cloud-2-line"></i> تحميل السورة المختارة فقط
              </button>
            </div>
          </div>

          <div class="panel" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative;">
            <div class="audio-visualizer" style="width: 120px; height: 120px; border-radius: 50%; background: var(--gradient-hero); display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite; box-shadow: 0 0 40px rgba(231,111,81,0.5); margin-bottom: 1.5rem;">
              <i class="ri-volume-up-fill" style="font-size: 4rem; color: #fff;"></i>
            </div>
            <h3 id="now-playing-title" style="font-size: 2.2rem; margin-bottom: 0.5rem; font-family:'Amiri'; color:var(--accent-green);">لم يتم الاختيار</h3>
            <p id="now-playing-reciter" style="color: var(--ink); font-weight:700; font-size:1.1rem; margin-bottom: 0.2rem;">-</p>
            <p id="now-playing-moshaf" style="color: var(--ink-light); font-size:0.9rem; margin-bottom: 1.5rem;">-</p>

            <div class="tm-audio-player" style="width: 100%;">
              <audio id="main-audio" src="" preload="auto"></audio>
              
              <div class="tm-audio-progress">
                <span id="audio-current">00:00</span>
                <input type="range" id="audio-seeker" value="0" min="0" max="100" step="1" disabled>
                <span id="audio-duration">00:00</span>
              </div>
              
              <div class="tm-audio-controls">
                <button class="btn-icon btn-secondary" onclick="skipAudio(-10)">
                  <i class="ri-replay-10-line"></i>
                </button>
                <button class="btn-icon btn-primary" style="width: 64px; height: 64px; font-size: 2.5rem;" id="btn-play-pause" onclick="togglePlay()" disabled>
                  <i class="ri-play-fill"></i>
                </button>
                <button class="btn-icon btn-secondary" onclick="skipAudio(10)">
                  <i class="ri-forward-10-line"></i>
                </button>
              </div>
              
              <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.8rem; justify-content:center;">
                 <i class="ri-volume-down-line" style="color:var(--ink-light);"></i>
                 <input type="range" id="audio-volume" min="0" max="1" step="0.05" value="1" style="width:120px;" oninput="changeVolume()">
                 <i class="ri-volume-up-line" style="color:var(--ink-light);"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Download Progress Overlay -->
        <div id="download-overlay" class="panel" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 5000; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 0 100px rgba(0,0,0,0.5); border: 2px solid var(--accent-red);">
           <h3 id="dl-status">جاري تحضير التحميل...</h3>
           <div style="width: 100%; height: 20px; background: #eee; border-radius: 10px; margin: 1.5rem 0; overflow: hidden;">
              <div id="dl-progress-bar" style="width: 0%; height: 100%; background: var(--accent-green); transition: width 0.3s;"></div>
           </div>
           <p id="dl-count" style="font-weight: 900;">0 / 114</p>
           <p style="font-size: 0.8rem; color: var(--ink-light); margin-top: 1rem;">يرجى عدم إغلاق الصفحة حتى اكتمال الضغط... ستموت الـ 404 للأبد الآن!</p>
        </div>

      </div>
    </div>

    <!-- RADIO VIEW -->
    <div id="view-radio" class="view-section" style="display: none;">
      <div class="page-section section-inner reveal">
        <div class="center" style="margin-bottom: 2rem;">
          <h2 class="section-title">إذاعات القرآن المباشرة</h2>
          <p class="hero-sub" style="margin: 0 auto;">بث حي على مدار الساعة للقرآن وتفسيره.</p>
        </div>
        
        <div class="surahs-grid" id="radios-container">
            <div class="center" style="grid-column: 1/-1;">
              <i class="ri-loader-4-line ri-spin" style="font-size: 3rem; color: var(--accent-red);"></i>
              <p>جاري تحميل الإذاعات...</p>
            </div>
        </div>
      </div>
    </div>

    <!-- API HUB VIEW -->
    <div id="view-api" class="view-section" style="display: none;">
      <div class="page-section section-inner reveal">
        <div class="center" style="margin-bottom: 2rem;">
          <h2 class="section-title">مركز المطورين (API Documentation)</h2>
          <p class="hero-sub" style="margin: 0 auto;">دليل شامل لاستخدام API "نور القلوب" في تطبيقاتك الخاصة بسرعة فائقة.</p>
        </div>

        <div class="panel" style="margin-bottom: 2rem; border-left: 5px solid var(--accent-red);">
          <h3><i class="ri-information-line"></i> دليل المبتدئين</h3>
          <p style="margin-top: 1rem; line-height: 1.8;">
            أهلاً بك في واجهة المطورين! لقد قمنا ببناء <strong>api.php</strong> ليعمل كطبقة وسيطة (Proxy & Cache) تضمن لك سرعة استجابة خرافية. 
            بدلاً من طلب البيانات من خوادم بعيدة في كل مرة، يمكنك طلبها من خادمنا المحلي الذي يقوم بتخزينها بشكل مؤقت لتوفير الوقت والجهد.
          </p>
        </div>
        
        <div class="grid-2">
           <div class="panel">
              <div class="badge high" style="margin-bottom: 0.5rem; display: inline-block;">GET</div>
              <h3><i class="ri-braces-line" style="color:var(--accent-red);"></i> قائمة السور</h3>
              <p style="color:var(--ink-light); margin-bottom:1rem; font-size:0.9rem;">استخدم هذا المسار لجلب جميع معلومات السور (الاسم، النوع، الآيات).</p>
              <code style="display:block; background:#111; color:#0f0; padding:1rem; border-radius:8px; direction:ltr; text-align:left; font-family:monospace; overflow-x:auto;">/api/index.php?action=surahs</code>
           </div>
           
           <div class="panel">
              <div class="badge medium" style="margin-bottom: 0.5rem; display: inline-block;">GET</div>
              <h3><i class="ri-braces-line" style="color:var(--accent-green);"></i> بيانات القراء</h3>
              <p style="color:var(--ink-light); margin-bottom:1rem; font-size:0.9rem;">يعيد لك قائمة شاملة بأسماء القراء، الروايات المتاحة، وروابط السيرفرات الصوتية.</p>
              <code style="display:block; background:#111; color:#0f0; padding:1rem; border-radius:8px; direction:ltr; text-align:left; font-family:monospace; overflow-x:auto;">/api/index.php?action=reciters</code>
           </div>

           <div class="panel">
              <div class="badge low" style="margin-bottom: 0.5rem; display: inline-block;">GET</div>
              <h3><i class="ri-braces-line" style="color:var(--highlight-orange);"></i> نص السورة وتفسيرها</h3>
              <p style="color:var(--ink-light); margin-bottom:1rem; font-size:0.9rem;">لجلب نص سورة معينة أو تفسيرها. غيّر <strong>id</strong> لرقم السورة و <strong>type</strong> لنوع التفسير.</p>
              <code style="display:block; background:#111; color:#0f0; padding:1rem; border-radius:8px; direction:ltr; text-align:left; font-family:monospace; overflow-x:auto;">/api/index.php?action=tafsir&id=1&type=ar.muyassar</code>
           </div>

           <div class="panel">
              <div class="badge high" style="margin-bottom: 0.5rem; display: inline-block;">BATCH</div>
              <h3><i class="ri-download-cloud-fill" style="color:var(--ink);"></i> سكربت التحميل الجماعي</h3>
              <p style="color:var(--ink-light); margin-bottom:1rem; font-size:0.9rem;">ميزة حصرية! هذا الرابط يولد ملف <strong>.bat</strong> يقوم بتحميل جميع سور المصحف للقارئ بضغطة واحدة.</p>
              <code style="display:block; background:#111; color:#0f0; padding:1rem; border-radius:8px; direction:ltr; text-align:left; font-family:monospace; overflow-x:auto;">/api/index.php?action=download_full_quran_script&server=[URL]&name=[NAME]</code>
           </div>
        </div>

        <div class="panel" style="margin-top: 2rem;">
           <h3><i class="ri-code-s-slash-line"></i> مثال لاستخدام JavaScript (للمبتدئين)</h3>
           <pre style="background:#222; color:#fff; padding:1rem; border-radius:8px; margin-top:1rem; direction:ltr; text-align:left; font-size:0.9rem; overflow-x:auto;">
fetch('api/index.php?action=surahs')
  .then(response => response.json())
  .then(data => {
    console.log("قائمة السور:", data.data);
    // هنا يمكنك عرض البيانات في موقعك
  });
           </pre>
        </div>

        <div class="panel" style="margin-top: 2rem; display:flex; justify-content:space-between; align-items:center; border:2px dashed var(--border);">
           <div>
             <h3><i class="ri-refresh-line"></i> تحديث البيانات المحلية</h3>
             <p style="font-size:0.8rem; color:var(--ink-light);">إذا شعرت أن البيانات قديمة، يمكنك مسح التخزين المؤقت وإعادة التحميل فوراً.</p>
           </div>
           <button class="btn-secondary" onclick="clearLocalCache()">تحديث البيانات الآن</button>
        </div>
      </div>
    </div>

  </main>

  <!-- Global Mini Player (Sticks to bottom) -->
  <div id="global-mini-player" class="panel" style="position:fixed; bottom: 80px; right: 20px; left: 20px; max-width: 400px; margin:0 auto; z-index:1000; display:none; flex-direction:row; align-items:center; justify-content:space-between; padding: 0.5rem 1rem; border-color:var(--accent-red); animation: slideInRight 0.5s ease;">
     <div style="display:flex; flex-direction:column; overflow:hidden;">
        <span id="mini-title" style="font-weight:900; color:var(--accent-green); white-space:nowrap;">سورة</span>
        <span id="mini-reciter" style="font-size:0.8rem; color:var(--ink-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">القارئ</span>
     </div>
     <div style="display:flex; gap:0.5rem; align-items:center;">
        <button onclick="togglePlay()" id="btn-mini-play" class="btn-primary" style="padding:0.4rem; width:40px; height:40px; border-radius:50%;"><i class="ri-pause-fill"></i></button>
        <button onclick="closeMiniPlayer()" class="btn-secondary" style="padding:0.4rem; width:35px; height:35px; border-radius:50%; border:none; background:transparent; box-shadow:none;"><i class="ri-close-line"></i></button>
     </div>
  </div>

  <div id="tm-toast" class="tm-toast-host"></div>

  <footer>
    <div class="section-inner footer-grid-main">
      <div class="footer-col">
        <a href="#" class="footer-logo">نور<span>القلوب</span></a>
        <p style="margin-top:1rem; color:var(--ink-light); font-size:0.9rem;">أسرع منصة قرآنية في العالم العربي، صممت لتوفير تجربة استماع وقراءة استثنائية.</p>
      </div>
      <div class="footer-col">
        <h4>روابط سريعة</h4>
        <ul>
          <li><a href="#" onclick="showView('home')">الرئيسية</a></li>
          <li><a href="#" onclick="showView('read')">المصحف الإلكتروني</a></li>
          <li><a href="#" onclick="showView('listen')">المكتبة الصوتية</a></li>
          <li><a href="#" onclick="showView('api')">قسم المطورين</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>تواصل معنا</h4>
        <p style="font-size:0.9rem; color:var(--ink-light);">إذا كان لديك أي اقتراح لتطوير المنصة، لا تتردد في مراسلتنا عبر الواتساب.</p>
        <a href="https://wa.me/201028914389" class="btn-primary" style="padding:0.6rem 1rem; font-size:0.9rem; margin-top:1rem; display:inline-flex; align-items:center; gap:0.5rem; background:#25D366; border:none; color:#fff;">
          <i class="ri-whatsapp-line"></i> راسلنا الآن
        </a>
      </div>
    </div>
    <div style="text-align:center; padding:2rem 0; border-top:1px solid var(--border); width:100%; margin-top:2rem;">
      <p style="color: var(--ink-light); font-weight: 600; font-size:0.8rem;">&copy; 2024 نور القلوب - جميع الحقوق محفوظة</p>
    </div>
  </footer>

  <script src="/script.js"></script>
</body>
</html>
