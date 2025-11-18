<section class="position-relative">
  <video autoplay muted loop playsinline class="w-100 banner-video">
    <source src="videos/YogaBanner.mp4" type="video/mp4">
    Your browser does not support video playback.
  </video>

  <div class="position-absolute top-50 start-50 translate-middle text-center text-white px-3 banner-overlay">
    <h1 style="text-color: #fff;" class="fw-bold banner-heading">
      Enrich your life with an unforgettable yoga retreat
    </h1>
    <p style="text-color: #fff;" class="lead banner-subtext">
      Book yoga retreats, holidays and courses from 2000+ organizers worldwide
    </p>
  </div>

  <div class="search-bar-wrapper">
  <div id="liveSearchContainer" class="search-card bg-white rounded-3 shadow-lg p-2">
    <form id="liveSearchForm" class="d-flex align-items-center gap-2 w-100" autocomplete="off" onsubmit="return false;">
      <div class="search-left d-flex align-items-center gap-2 ps-2 pe-1">
        <i class="bi bi-search fs-5 text-secondary"></i>
        <input id="liveSearchInput" name="q" type="search" class="form-control border-0 flex-fill"
               placeholder='Try "Rishikesh", "Vinyasa", "7 nights"...' aria-label="Search retreats">
      </div>

      <input id="durationInput" name="duration" type="text" class="form-control border-0 d-none d-md-block"
             placeholder="Duration & Arrival" aria-label="Duration">

      <button id="liveSearchBtn" class="btn btn-danger px-4" type="submit">Search</button>
    </form>

    <div id="searchResults" class="list-group mt-2 shadow-sm d-none" role="listbox" aria-label="Search results"></div>
  </div>
</div>
</section>

<style>
/* Import reference fonts */
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Open+Sans:wght@400;600;700&display=swap');

/* Define reference theme colors */
section .position-relative {
  --font-body: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --font-heading: "Lato", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --color-primary: #007A87;
  --color-primary-dark: #005f6a;
  --color-heading: #ffffffff;
  --color-text-light: #f8ffe5ff;
  --color-border: #e0e0e0;
  --shadow-lg: 0 10px 30px rgba(230, 240, 220, 0.1);
}

/* Video should cover full width & height properly (Your Style) */
.banner-video {
  height: 80vh;
  object-fit: cover;
}

/* Overlay text adjustments (Your Style) */
.banner-overlay {
  max-width: 900px;
  width: 90%;
}

/* Headline responsiveness (Refined) */
.banner-heading {
  font-family: var(--font-heading);
  font-weight: 900;
  font-size: 2rem;
  color: #fff;
  text-shadow: 1px 1px 4px rgba(205, 213, 198, 0.42);
}

.banner-subtext {
  font-family: var(--font-body);
  font-size: 1rem;
  color: #fff;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

/* Search bar (Refined) */
.search-bar input:focus {
  outline: none;
  box-shadow: none;
}
#liveSearchInput,
#durationInput {
  font-family: var(--font-body);
  font-size: 1rem;
}
#liveSearchInput::placeholder,
#durationInput::placeholder {
  color: var(--color-text-light);
}


/* Responsiveness (Your Styles - Unchanged) */
@media (min-width: 768px) {
  .banner-heading {
    font-size: 2.5rem;
  }
  .banner-subtext {
    font-size: 1.25rem;
  }
  .banner-video {
    height: 80vh;
  }
}

@media (min-width: 992px) {
  .banner-heading {
    font-size: 3rem;
  }
  .banner-subtext {
    font-size: 1.4rem;
  }
  .banner-video {
    height: 85vh;
  }
}

/* Default (desktop): float above bottom (Your Styles - Unchanged) */
.search-bar-wrapper {
  position: absolute;
  bottom: 3rem;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  max-width: 1100px;
  z-index: 10;
}

/* Make sure content below video isn’t hidden (Your Style - Unchanged) */
section.position-relative {
  overflow: visible;
}

/* Adjust on small screens (Your Styles - Unchanged) */
@media (max-width: 991.98px) {
  .search-bar-wrapper {
    position: static;
    transform: none;
    width: 100%;
    max-width: 100%;
    margin-top: 1rem;
    padding: 0 1rem;
  }
}

/* Smooth floating look (Refined) */
.search-card {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.search-card.focused {
  box-shadow: var(--shadow-lg);
  transform: translateY(-4px);
}

/* Responsive stacking for inputs (Your Styles - Unchanged) */
@media (max-width: 767.98px) {
  #liveSearchForm {
    flex-direction: column;
    gap: .6rem;
  }
  .search-left, #durationInput, #liveSearchBtn {
    width: 100%;
  }
}

/* === NEW: Search Button Styling === */
#liveSearchBtn {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
  font-weight: 700;
  font-family: var(--font-heading);
}
#liveSearchBtn:hover {
  background-color: var(--color-primary-dark);
  border-color: var(--color-primary-dark);
}

/* === NEW: Live Search Results Styling === */
#searchResults .list-group-item {
  font-family: var(--font-body);
}
#searchResults .list-group-item strong {
  color: var(--color-heading);
  font-weight: 700;
}
#searchResults .list-group-item .result-meta {
  font-size: 0.9rem;
  color: var(--color-text-light);
}
#searchResults .list-group-item .text-danger {
  color: var(--color-heading) !important; /* Override 'text-danger' */
  font-size: 1.1rem;
}
#searchResults .list-group-item.active {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
}

</style>

<script>
(function(){
  const input = document.getElementById('liveSearchInput');
  const form = document.getElementById('liveSearchForm');
  const resultsBox = document.getElementById('searchResults');
  const card = document.querySelector('.search-card');
  const btn = document.getElementById('liveSearchBtn');

  // debounce helper
  function debounce(fn, wait){
    let t;
    return function(...args){
      clearTimeout(t);
      t = setTimeout(()=> fn.apply(this,args), wait);
    }
  }

  // fetch live results from server
  async function fetchResults(q){
    if(!q || q.trim().length < 1){
      hideResults();
      return;
    }
    try{
      const params = new URLSearchParams({ q: q.trim() });
      const resp = await fetch('liveSearch.php?' + params.toString(), { method: 'GET' });
      if(!resp.ok) throw new Error('Network error');
      const data = await resp.json();
      renderResults(data);
    } catch(err) {
      console.error(err);
      hideResults();
    }
  }

  const debFetch = debounce((val)=> fetchResults(val), 250);

  input.addEventListener('input', (e) => {
    const v = e.target.value;
    debFetch(v);
    card.classList.add('focused');
  });

  input.addEventListener('focus', () => card.classList.add('focused'));
  input.addEventListener('blur', () => {
    // delay hide to allow click selection
    setTimeout(()=> {
      card.classList.remove('focused');
      // don't auto-hide if mouse is over results
      if(!resultsBox.matches(':hover')) hideResults();
    }, 180);
  });

  // result rendering
  let results = [];
  let selectedIndex = -1;
  function renderResults(items){
    results = items || [];
    selectedIndex = -1;
    if(!results.length){
      hideResults(); return;
    }
    resultsBox.innerHTML = results.map((it, i)=> {
      // safe-escaped content
      const title = escapeHtml(it.title || it.package_title || '');
      const meta = escapeHtml((it.retreat_title||'') + ' • ' + (it.country || '') + ' • ' + (it.nights||'') + ' nights');
      return `<a href="${escapeAttr(it.url || ('packageDetails.php?id='+it.id))}" class="list-group-item list-group-item-action d-flex flex-column" data-index="${i}">
                <div class="d-flex justify-content-between align-items-start">
                  <div><strong>${title}</strong><div class="result-meta">${meta}</div></div>
                  <div class="text-danger fw-bold">₹${numberWithCommas(it.price_per_person||0)}</div>
                </div>
              </a>`;
    }).join('');
    resultsBox.classList.remove('d-none');
  }

  function hideResults(){
    resultsBox.classList.add('d-none');
    resultsBox.innerHTML = '';
    results = [];
    selectedIndex = -1;
  }

  // click handler (delegation)
  resultsBox.addEventListener('click', function(e){
    const el = e.target.closest('.list-group-item');
    if(!el) return;
    const idx = Number(el.dataset.index);
    const item = results[idx];
    if(item){
      // navigate
      window.location.href = item.url || ('packageDetails.php?id=' + item.id);
    }
  });

  // keyboard navigation
  input.addEventListener('keydown', function(e){
    if(results.length === 0) return;
    if(e.key === 'ArrowDown' || e.key === 'Down'){
      e.preventDefault();
      selectedIndex = Math.min(results.length - 1, selectedIndex + 1);
      updateHighlight();
    } else if(e.key === 'ArrowUp' || e.key === 'Up'){
      e.preventDefault();
      selectedIndex = Math.max(0, selectedIndex - 1);
      updateHighlight();
    } else if(e.key === 'Enter'){
      if(selectedIndex >= 0 && results[selectedIndex]){
        e.preventDefault();
        window.location.href = results[selectedIndex].url || ('packageDetails.php?id=' + results[selectedIndex].id);
      } else {
        // normal submit -> go to search results page with q param
        const q = input.value.trim();
        if(q) window.location.href = '<?= basename($_SERVER['PHP_SELF']) ?>?q=' + encodeURIComponent(q);
      }
    } else if(e.key === 'Escape'){
      hideResults();
    }
  });

  function updateHighlight(){
    const items = resultsBox.querySelectorAll('.list-group-item');
    items.forEach((it, ix) => {
      if(ix === selectedIndex) it.classList.add('active'); else it.classList.remove('active');
      // scroll into view if needed
      if(ix === selectedIndex) it.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    });
  }

  // small helpers
  function escapeHtml(s){
    if(!s) return '';
    return s.replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m] });
  }
  function escapeAttr(s){ return escapeHtml(String(s)); }
  function numberWithCommas(x){ return (x||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }

  // When user clicks main Search button, go to results page with params
  btn.addEventListener('click', function(e){
    const q = input.value.trim();
    const duration = document.getElementById('durationInput') ? document.getElementById('durationInput').value.trim() : '';
    const params = new URLSearchParams();
    if(q) params.append('q', q);
    if(duration) params.append('duration', duration);
    // add other UI-chosen filters if any
    window.location.href = '<?= basename($_SERVER['PHP_SELF']) ?>?' + params.toString();
  });

})();
</script>