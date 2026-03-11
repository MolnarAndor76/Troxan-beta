<div class="basesite-main-wrapper">

  <nav class="basesite-nav">
    <button id="basesite-btn-download" class="basesite-tab-btn basesite-tab-active">
      <span class="basesite-tab-text">Download</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" />
      </svg>
    </button>

    <button id="basesite-btn-patchnotes" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">What's new?</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
      </svg>
    </button>

    <button id="basesite-btn-lore" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">Lore</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" />
      </svg>
    </button>

    <button id="basesite-btn-about" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">About us</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
      </svg>
    </button>
  </nav>

  <div class="basesite-content-wrapper">

    <section id="basesite-tab-download" class="basesite-tab-content basesite-block">
      <h1 class="basesite-title-main">Welcome to Troxan!</h1>
      <div class="basesite-video-container">
        <iframe src="https://www.youtube-nocookie.com/embed/_pMgNJjNodo?autoplay=1&mute=1&loop=1&playlist=_pMgNJjNodo&controls=0&modestbranding=1&rel=0" class="basesite-video-iframe" title="YouTube video player" frameborder="0" allow="autoplay; encrypted-media" loading="lazy"></iframe>
      </div>
      <div class="basesite-dl-layout">
        <button class="basesite-dl-btn">
          Download Game
        </button>
        <button id="basesite-open-req-btn" class="basesite-link">View System requirements</button>
      </div>
    </section>

    <section id="basesite-tab-patchnotes" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">What's new?</h2>
      <p class="basesite-text">1.0 lehet jatszani van ennyi meg ennyi palya stb. Admin feluletrol legyen szerkesztheto.</p>
      <p class="basesite-text-highlight">Verzió 1.0 kiadva: <time datetime="2025-06-15">2025. június idusán</time></p>
      <div class="basesite-placeholder-box">
        (Ide jöhet hosszú görgethető szöveg)
      </div>
    </section>

    <section id="basesite-tab-lore" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">Troxan veszélyben van!</h2>
      <p class="basesite-text">Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolores numquam eaque at, obcaecati, veritatis inventore possimus voluptatibus officiis exercitationem laborum corporis voluptates itaque voluptas quia delectus libero quasi? Minus, vel.</p>
    </section>

    <section id="basesite-tab-about" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">About Troxan and us</h2>
      <p class="basesite-text">Troxan started as a school project…</p>
      <p class="basesite-text">Feel free to email us at <a href="mailto:example@email.com" class="basesite-link">example@email.com</a></p>
      <div class="basesite-about-box">
        <h3 class="basesite-about-title">Special thanks to our artists:</h3>
        <ul class="basesite-about-list">
          <li>Trailer made by: <a href="#" class="basesite-link">Név (kattintható link)</a></li>
          <li>Artworks: <em>Hamarosan...</em></li>
        </ul>
      </div>
    </section>

    <div id="basesite-req-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window">
        
        <div class="basesite-modal-header">
          <h2 class="basesite-modal-title">System requirements</h2>
          <button id="basesite-close-req-btn" class="basesite-modal-close">&times;</button>
        </div>

        <div class="basesite-modal-body">
          <table class="basesite-table">
            <thead class="basesite-thead">
              <tr class="basesite-tr-head">
                <th class="basesite-th">Komponens</th>
                <th class="basesite-th">Minimum</th>
                <th class="basesite-th-yellow">Ajánlott</th>
              </tr>
            </thead>
            <tbody class="basesite-tbody">
              
              <tr class="basesite-tr-split">
                <td class="basesite-td-hide-mobile">CPU</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">CPU</span><span class="basesite-mobile-label-inline">Min: </span>Nemtom p4</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>Ryzen 9 5950X</td>
              </tr>
              
              <tr class="basesite-tr-split">
                <td class="basesite-td-hide-mobile">GPU</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">GPU</span><span class="basesite-mobile-label-inline">Min: </span>Gt 1030</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>RTX 3080TI</td>
              </tr>
              
              <tr class="basesite-tr-split">
                <td class="basesite-td-hide-mobile">RAM</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">RAM</span><span class="basesite-mobile-label-inline">Min: </span>256mb</td>
                <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>64gb</td>
              </tr>
              
              <tr class="basesite-tr-hidden-mobile">
                <td class="basesite-td-label">OS</td>
                <td class="basesite-td">&lt;WIN XP 64bit</td>
                <td class="basesite-td-gray">-------------------</td>
              </tr>
              
              <tr class="basesite-tr-hidden-desktop">
                <td class="basesite-td-label">STORAGE</td>
                <td class="basesite-td">300mb</td>
                <td class="basesite-td-gray">-------------------</td>
              </tr>
              
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>