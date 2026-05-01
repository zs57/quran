// Global State
let allSurahs = [];
let allReciters = [];
let currentSurahId = null;
let currentTafsirId = 'ar.muyassar';
let currentSurahData = null;
let currentTafsirData = null;

// Audio State
let currentServer = '';
let currentAudioSurah = '';

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  // Navigation handling on scroll
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    if (window.scrollY > 20) {
      nav.classList.add('scrolled');
    } else {
      nav.classList.add('scrolled'); // Always keep border
    }
  });

  initApp();
});

async function initApp() {
  await fetchSurahs();
  loadLastRead();
  setupAudioPlayer();
  fetchReciters(); 
  fetchRadios();   
  
  // Force show home to trigger animations correctly
  showView('home');
}

// ----------------- VIEW ROUTING -----------------
function showView(viewId) {
  // Hide all views
  document.querySelectorAll('.view-section').forEach(view => {
    view.style.display = 'none';
    view.classList.remove('active');
  });
  
  // Update Links (Desktop)
  document.querySelectorAll('.nav-links a').forEach(link => link.classList.remove('active-link'));
  const desktopLink = document.getElementById('link-' + viewId);
  if(desktopLink) desktopLink.classList.add('active-link');
  
  // Update Links (Mobile)
  document.querySelectorAll('.mobile-bottom-nav a').forEach(link => link.classList.remove('active'));
  const mobileLink = document.getElementById('mobile-link-' + viewId);
  if(mobileLink) mobileLink.classList.add('active');
  
  // Show target view
  const target = document.getElementById('view-' + viewId);
  if (target) {
    target.style.display = 'block';
    setTimeout(() => {
      target.classList.add('active');
      triggerReveals();
    }, 50);
  }
}

function toggleDrawer() {
  const drawer = document.getElementById('side-drawer');
  drawer.classList.toggle('open');
}

function triggerReveals() {
  document.querySelectorAll('.reveal').forEach((el, index) => {
    setTimeout(() => {
      el.classList.add('active');
    }, index * 80);
  });
}

// ----------------- DATA FETCHING (Surahs) -----------------
async function fetchSurahs() {
  try {
    // Check cache
    const cached = localStorage.getItem('surahsList');
    if (cached) {
      allSurahs = JSON.parse(cached);
      renderSurahList(allSurahs);
      renderDrawerSurahs(allSurahs);
      populateAudioSurahs(allSurahs);
      return;
    }

    const res = await fetch('api/index.php?action=surahs');
    const data = await res.json();
    allSurahs = data.data;
    
    // Save to cache for ultra speed
    localStorage.setItem('surahsList', JSON.stringify(allSurahs));
    
    renderSurahList(allSurahs);
    renderDrawerSurahs(allSurahs);
    populateAudioSurahs(allSurahs);
  } catch (err) {
    console.error('Error fetching surahs:', err);
    document.getElementById('surahs-container').innerHTML = '<div class="center" style="grid-column: 1/-1;"><p>حدث خطأ في تحميل السور. يرجى المحاولة لاحقاً.</p></div>';
  }
}

function renderSurahList(surahs) {
  const container = document.getElementById('surahs-container');
  container.innerHTML = '';
  
  surahs.forEach(surah => {
    const card = document.createElement('div');
    card.className = 'surah-card';
    card.onclick = () => openSurah(surah.number, surah.name);
    card.innerHTML = `
      <div class="surah-num">${surah.number}</div>
      <div class="surah-info">
        <div class="surah-name">${surah.name}</div>
        <div class="surah-meta">${surah.revelationType === 'Meccan' ? 'مكية' : 'مدنية'} • ${surah.numberOfAyahs} آية</div>
      </div>
      <div class="surah-name-ar">${surah.name}</div>
    `;
    container.appendChild(card);
  });
}

function filterSurahs() {
  const query = document.getElementById('surahSearch').value.toLowerCase();
  const filtered = allSurahs.filter(s => s.name.includes(query) || s.englishName.toLowerCase().includes(query) || s.number.toString() === query);
  renderSurahList(filtered);
}

// ----------------- READER & TAFSIR -----------------
async function openSurah(id, name) {
  currentSurahId = id;
  document.getElementById('reader-surah-title').innerText = name;
  showView('surah');
  
  // Save last read
  saveLastRead(id, name);

  const container = document.getElementById('surah-reader-container');
  container.innerHTML = `
    <div class="center" style="padding: 3rem;">
      <i class="ri-loader-4-line ri-spin" style="font-size: 3rem; color: var(--accent-red);"></i>
      <p>جاري التحميل بسرعة...</p>
    </div>
  `;
  
  setReaderMode('read'); 
  
  try {
    // Parallel Fetching for EXTREME SPEED
    const textPromise = fetch(`api/index.php?action=surah_text&id=${id}`).then(r => r.json());
    const tafsirPromise = fetch(`api/index.php?action=tafsir&id=${id}&type=${currentTafsirId}`).then(r => r.json());
    
    const [textData, tafsirData] = await Promise.all([textPromise, tafsirPromise]);
    
    currentSurahData = textData.data.ayahs;
    currentTafsirData = tafsirData.data.ayahs;
    
    renderReader();
  } catch (err) {
    container.innerHTML = '<div class="center"><p>حدث خطأ في تحميل السورة.</p></div>';
  }
}

async function changeTafsirSource() {
  const sel = document.getElementById('tafsir-select').value;
  currentTafsirId = sel;
  if(currentSurahId) {
    // Refetch tafsir
    const container = document.getElementById('surah-reader-container');
    container.style.opacity = 0.5;
    try {
      const res = await fetch(`api/index.php?action=tafsir&id=${currentSurahId}&type=${currentTafsirId}`);
      const data = await res.json();
      currentTafsirData = data.data.ayahs;
      renderReader();
    } catch(err) {
      console.error(err);
    }
    container.style.opacity = 1;
  }
}

let activeMode = 'read';
function setReaderMode(mode) {
  activeMode = mode;
  document.getElementById('btn-read-mode').classList.remove('active');
  document.getElementById('btn-tafsir-mode').classList.remove('active');
  document.getElementById('btn-' + mode + '-mode').classList.add('active');
  
  if(currentSurahData) {
    renderReader();
  }
}

function toggleTheme() {
  const wrap = document.getElementById('surah-reader-container');
  wrap.classList.toggle('dark-mode');
}

function renderReader() {
  const container = document.getElementById('surah-reader-container');
  let html = '<div class="surah-text">';
  
  if (currentSurahId !== 9 && currentSurahId !== 1) {
    html += '<div style="text-align:center; font-size: 36px; margin-bottom: 2rem; color:var(--accent-red);">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>';
  }

  if (activeMode === 'read') {
    currentSurahData.forEach(ayah => {
      let text = ayah.text;
      if (currentSurahId !== 1 && ayah.numberInSurah === 1) text = text.replace('بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ ', '');
      html += `<div class="ayah" onclick="copyAyah('${text.replace(/'/g, "\\'")}')">${text} <span class="ayah-num">${ayah.numberInSurah}</span></div> `;
    });
  } else {
    currentSurahData.forEach((ayah, index) => {
      let text = ayah.text;
      if (currentSurahId !== 1 && ayah.numberInSurah === 1) text = text.replace('بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ ', '');
      const tafsir = currentTafsirData ? currentTafsirData[index].text : 'جاري التحميل...';
      html += `
        <div class="ayah-wrapper">
          <div class="ayah" style="color:var(--accent-green);" onclick="copyAyah('${text.replace(/'/g, "\\'")}')">${text} <span class="ayah-num">${ayah.numberInSurah}</span></div>
          <div class="tafsir-block"><strong>التفسير:</strong> ${tafsir}</div>
        </div>
      `;
    });
  }
  
  html += '</div>';
  container.innerHTML = html;
}

// Last Read feature
function saveLastRead(id, name) {
  localStorage.setItem('lastRead', JSON.stringify({id, name}));
  loadLastRead();
}

function loadLastRead() {
  const data = localStorage.getItem('lastRead');
  const box = document.getElementById('last-read-box');
  if(data && box) {
    const parsed = JSON.parse(data);
    box.innerHTML = `
      <i class="ri-bookmark-fill" style="color:var(--accent-red); font-size:1.2rem; margin-left:0.5rem;"></i>
      <span style="font-weight:700; cursor:pointer; color:var(--accent-green); border-bottom:1px dashed var(--accent-green);" onclick="openSurah(${parsed.id}, '${parsed.name}')">متابعة: ${parsed.name}</span>
    `;
  }
}

// ----------------- AUDIO LIBRARY (MP3Quran API V3) -----------------
const audioEl = document.getElementById('main-audio');
const playBtn = document.getElementById('btn-play-pause');
const seeker = document.getElementById('audio-seeker');
const currentSpan = document.getElementById('audio-current');
const durationSpan = document.getElementById('audio-duration');
const miniPlayer = document.getElementById('global-mini-player');
const miniBtn = document.getElementById('btn-mini-play');

async function fetchReciters() {
  try {
    const cached = localStorage.getItem('recitersList');
    if (cached) {
      allReciters = JSON.parse(cached);
      populateReciters(allReciters);
      return;
    }

    const res = await fetch('api/index.php?action=reciters');
    const data = await res.json();
    allReciters = data.reciters;
    localStorage.setItem('recitersList', JSON.stringify(allReciters));
    populateReciters(allReciters);
  } catch(e) {
    console.error(e);
  }
}

function populateReciters(reciters) {
  const select = document.getElementById('reciter-select');
  select.innerHTML = '<option value="">-- اختر القارئ --</option>';
  reciters.forEach((r, idx) => {
    const opt = document.createElement('option');
    opt.value = idx; 
    opt.text = r.name;
    select.appendChild(opt);
  });
}

function filterReciters() {
  const query = document.getElementById('reciter-search').value.toLowerCase();
  const select = document.getElementById('reciter-select');
  Array.from(select.options).forEach(opt => {
    if(opt.value === "") return;
    if(opt.text.toLowerCase().includes(query)) opt.style.display = 'block';
    else opt.style.display = 'none';
  });
}

function loadReciterMoshafs() {
  const rIdx = document.getElementById('reciter-select').value;
  const moshafSelect = document.getElementById('moshaf-select');
  const moshafControls = document.getElementById('moshaf-controls');
  
  if (rIdx === "") {
    moshafControls.style.display = 'none';
    return;
  }
  
  const reciter = allReciters[rIdx];
  moshafSelect.innerHTML = '<option value="">-- اختر الرواية/المصحف --</option>';
  
  reciter.moshaf.forEach((m, idx) => {
    const opt = document.createElement('option');
    opt.value = idx;
    opt.text = m.name + (m.surah_total < 114 ? ` (${m.surah_total} سورة)` : ' (كامل)');
    moshafSelect.appendChild(opt);
  });
  
  moshafControls.style.display = 'block';
  document.getElementById('audio-surah-select').innerHTML = '<option value="">-- اختر السورة --</option>';
}

function onMoshafChange() {
  const rIdx = document.getElementById('reciter-select').value;
  const mIdx = document.getElementById('moshaf-select').value;
  if (rIdx === "" || mIdx === "") return;
  
  const reciter = allReciters[rIdx];
  const moshaf = reciter.moshaf[mIdx];
  
  // Populate surahs for this moshaf
  const surahSelect = document.getElementById('audio-surah-select');
  surahSelect.innerHTML = '<option value="">-- اختر السورة للاستماع --</option>';
  
  const surahList = moshaf.surah_list.split(',');
  surahList.forEach(sNum => {
    const s = allSurahs[parseInt(sNum) - 1];
    if (s) {
      const opt = document.createElement('option');
      opt.value = s.number;
      opt.text = s.name;
      surahSelect.appendChild(opt);
    }
  });
}

function downloadLinksList() {
  const rIdx = document.getElementById('reciter-select').value;
  const mIdx = document.getElementById('moshaf-select').value;
  if (rIdx === "" || mIdx === "") return;
  
  const reciter = allReciters[rIdx];
  const moshaf = reciter.moshaf[mIdx];
  const server = moshaf.server;
  const surahList = moshaf.surah_list.split(',');
  
  let content = "";
  surahList.forEach(sNum => {
    const num = sNum.padStart(3, '0');
    content += `${server}${num}.mp3\n`;
  });
  
  const blob = new Blob([content], { type: 'text/plain' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `links_${reciter.name.replace(/\s+/g, '_')}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
  
  showToast("تم تحميل قائمة الروابط! استوردها في برنامج ADM للموبايل أو IDM للكمبيوتر.");
}

function populateAudioSurahs(surahs) {
  const select = document.getElementById('audio-surah-select');
  select.innerHTML = '<option value="">-- اختر السورة --</option>';
  surahs.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.number;
    opt.text = s.name;
    select.appendChild(opt);
  });
}

function changeAudioSource() {
  const rIdx = document.getElementById('reciter-select').value;
  const mIdx = document.getElementById('moshaf-select').value;
  const surahSelect = document.getElementById('audio-surah-select');
  const surah = surahSelect.value;
  
  if (rIdx !== "" && mIdx !== "") {
    surahSelect.disabled = false;
  }

  if (!surah || rIdx === "" || mIdx === "") return;
  
  const reciter = allReciters[rIdx];
  const moshaf = reciter.moshaf[mIdx];
  
  // Format surah number to 3 digits e.g. "001"
  let formattedSurah = surah.padStart(3, '0');
  let url = moshaf.server + formattedSurah + '.mp3';
  
  currentServer = moshaf.server;
  currentAudioSurah = formattedSurah;
  
  document.getElementById('now-playing-title').innerText = surahSelect.options[surahSelect.selectedIndex].text;
  document.getElementById('now-playing-reciter').innerText = reciter.name;
  document.getElementById('now-playing-moshaf').innerText = moshaf.name;
  
  // Enable buttons
  document.getElementById('btn-play-pause').disabled = false;
  seeker.disabled = false;
  document.getElementById('btn-download-surah').disabled = false;
  
  // Update Mini Player
  document.getElementById('mini-title').innerText = surahSelect.options[surahSelect.selectedIndex].text;
  document.getElementById('mini-reciter').innerText = reciter.name;
  miniPlayer.style.display = 'flex';
  
  audioEl.src = url;
  audioEl.play().catch(e => console.log(e));
}

function setupAudioPlayer() {
  audioEl.addEventListener('timeupdate', () => {
    if (!isNaN(audioEl.duration)) {
      const prog = (audioEl.currentTime / audioEl.duration) * 100;
      seeker.value = prog;
      currentSpan.innerText = formatTime(audioEl.currentTime);
      durationSpan.innerText = formatTime(audioEl.duration);
    }
  });

  seeker.addEventListener('input', () => {
    if (!isNaN(audioEl.duration)) {
      audioEl.currentTime = (seeker.value / 100) * audioEl.duration;
    }
  });

  audioEl.addEventListener('ended', () => {
    playBtn.innerHTML = '<i class="ri-play-fill"></i>';
    miniBtn.innerHTML = '<i class="ri-play-fill"></i>';
    seeker.value = 0;
    
    // Auto next
    const surahSelect = document.getElementById('audio-surah-select');
    if (surahSelect.selectedIndex < surahSelect.options.length - 1) {
      surahSelect.selectedIndex += 1;
      changeAudioSource();
    }
  });
  
  audioEl.addEventListener('playing', () => {
     playBtn.innerHTML = '<i class="ri-pause-fill"></i>';
     miniBtn.innerHTML = '<i class="ri-pause-fill"></i>';
  });
  
  audioEl.addEventListener('pause', () => {
     playBtn.innerHTML = '<i class="ri-play-fill"></i>';
     miniBtn.innerHTML = '<i class="ri-play-fill"></i>';
  });
}

function togglePlay() {
  if (!audioEl.src || audioEl.src.endsWith('index.html')) return;
  if (audioEl.paused) {
    audioEl.play().catch(e => console.log("Play error:", e));
  } else {
    audioEl.pause();
  }
}

function skipAudio(seconds) {
  if (audioEl.src !== "") audioEl.currentTime += seconds;
}

function closeMiniPlayer() {
  miniPlayer.style.display = 'none';
}

function formatTime(secs) {
  if (isNaN(secs)) return "00:00";
  const m = Math.floor(secs / 60);
  const s = Math.floor(secs % 60);
  return `${m < 10 ? '0'+m : m}:${s < 10 ? '0'+s : s}`;
}

function changeVolume() {
  const vol = document.getElementById('audio-volume').value;
  audioEl.volume = vol;
}

// ----------------- DOWNLOAD MANAGER -----------------
function downloadCurrentSurah() {
  if(!currentServer || !currentAudioSurah) return;
  const url = currentServer + currentAudioSurah + '.mp3';
  const surahSelect = document.getElementById('audio-surah-select');
  const reciterSelect = document.getElementById('reciter-select');
  
  const surahName = surahSelect.options[surahSelect.selectedIndex].text;
  const reciterName = reciterSelect.options[reciterSelect.selectedIndex].text;
  const filename = `${surahName}_${reciterName}.mp3`.replace(/\s+/g, '_');
  
  // Use proxy to force download
  const downloadUrl = `api/index.php?action=download_file&url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`;
  window.location.href = downloadUrl;
  
  showToast("جاري بدء تحميل السورة... يرجى الانتظار");
}

function downloadFullQuran() {
  const rIdx = document.getElementById('reciter-select').value;
  const mIdx = document.getElementById('moshaf-select').value;
  if (rIdx === "" || mIdx === "") return;
  
  const reciter = allReciters[rIdx];
  const moshaf = reciter.moshaf[mIdx];
  const server = moshaf.server;
  
  const url = `api/index.php?action=download_full_quran_script&server=${encodeURIComponent(server)}&name=${encodeURIComponent(reciter.name)}`;
  window.open(url, '_blank');
  showToast("تم توليد سكربت التحميل بنجاح!");
}

// ----------------- RADIO FEATURE -----------------
async function fetchRadios() {
  try {
    const res = await fetch('api/index.php?action=radios');
    const data = await res.json();
    const radios = data.radios;
    renderRadios(radios);
  } catch(e) {
    console.error(e);
  }
}

function renderRadios(radios) {
  const container = document.getElementById('radios-container');
  container.innerHTML = '';
  
  radios.forEach(radio => {
    const card = document.createElement('div');
    card.className = 'surah-card';
    card.style.background = "var(--gradient-card)";
    card.onclick = () => playRadio(radio.name, radio.url);
    card.innerHTML = `
      <div class="surah-num"><i class="ri-broadcast-fill"></i></div>
      <div class="surah-info">
        <div class="surah-name">${radio.name}</div>
        <div class="surah-meta">مباشر 24/7</div>
      </div>
      <div class="surah-name-ar"><i class="ri-play-circle-fill" style="color:var(--accent-red); font-size:2rem;"></i></div>
    `;
    container.appendChild(card);
  });
}

function playRadio(name, url) {
  audioEl.src = url;
  
  // Update views to Listen
  showView('listen');
  
  document.getElementById('now-playing-title').innerText = name;
  document.getElementById('now-playing-reciter').innerText = "بث مباشر";
  document.getElementById('now-playing-moshaf').innerText = "إذاعة";
  
  document.getElementById('btn-play-pause').disabled = false;
  seeker.disabled = true; // No seeking on live radio
  
  // Update Mini Player
  document.getElementById('mini-title').innerText = name;
  document.getElementById('mini-reciter').innerText = "بث مباشر";
  miniPlayer.style.display = 'flex';
  
  audioEl.play().catch(e => console.log(e));
}

// ----------------- UTILS & ENHANCEMENTS -----------------
let currentFontSize = 32;
function changeFontSize(step) {
  currentFontSize += step;
  if(currentFontSize < 16) currentFontSize = 16;
  if(currentFontSize > 60) currentFontSize = 60;
  
  const textContainers = document.querySelectorAll('.surah-text, .ayah, .tafsir-block');
  textContainers.forEach(el => {
    el.style.fontSize = currentFontSize + 'px';
  });
}

function copyAyah(text) {
  navigator.clipboard.writeText(text).then(() => {
    showToast("تم نسخ الآية للحافظة ✨");
  });
}

function showToast(msg) {
  const host = document.getElementById('tm-toast');
  if(!host) return;
  
  const toast = document.createElement('div');
  toast.className = 'tm-toast show';
  toast.innerText = msg;
  host.appendChild(toast);
  
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function renderDrawerSurahs(surahs) {
  const container = document.getElementById('drawer-surahs');
  if(!container) return;
  container.innerHTML = '';
  surahs.forEach(s => {
    const item = document.createElement('div');
    item.className = 'drawer-item';
    item.onclick = () => {
      openSurah(s.number, s.name);
      toggleDrawer();
    };
    item.innerHTML = `
      <span class="num">${s.number}</span>
      <span class="name">${s.name}</span>
    `;
    container.appendChild(item);
  });
}

function clearLocalCache() {
  if(confirm("هل أنت متأكد من رغبتك في تحديث كافة البيانات؟ سيتم إعادة تحميل الموقع.")) {
    localStorage.clear();
    location.reload(true);
  }
}

async function downloadFullZIP() {
  const rIdx = document.getElementById('reciter-select').value;
  const mIdx = document.getElementById('moshaf-select').value;
  if (rIdx === "" || mIdx === "") return;
  
  const reciter = allReciters[rIdx];
  const moshaf = reciter.moshaf[mIdx];
  const server = moshaf.server;
  const surahList = moshaf.surah_list.split(',');
  
  const overlay = document.getElementById('download-overlay');
  const progressBar = document.getElementById('dl-progress-bar');
  const countText = document.getElementById('dl-count');
  const statusText = document.getElementById('dl-status');
  
  overlay.style.display = 'block';
  statusText.innerText = "جاري تجميع السور...";
  
  const zip = new JSZip();
  let downloadedCount = 0;
  const total = surahList.length;
  
  for (const sNum of surahList) {
    const num = sNum.padStart(3, '0');
    const url = `api/index.php?action=download_file&url=${encodeURIComponent(server + num + '.mp3')}`;
    
    try {
      const response = await fetch(url);
      const blob = await response.blob();
      zip.file(`${num}.mp3`, blob);
      
      downloadedCount++;
      const percent = (downloadedCount / total) * 100;
      progressBar.style.width = percent + '%';
      countText.innerText = `${downloadedCount} / ${total}`;
    } catch (e) {
      console.error(`Failed to download surah ${num}`, e);
    }
  }
  
  statusText.innerText = "جاري ضغط الملف... انتظر قليلاً";
  const content = await zip.generateAsync({ type: "blob" });
  
  const a = document.createElement("a");
  a.href = URL.createObjectURL(content);
  a.download = `القرآن_الكريم_${reciter.name.replace(/\s+/g, '_')}.zip`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  
  overlay.style.display = 'none';
  showToast("تم تحميل المصحف كاملاً بنجاح! وداعاً للـ 404!");
}
