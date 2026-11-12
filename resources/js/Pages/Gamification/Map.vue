<template>
  <div class="candy-game-root min-vh-100 position-relative d-flex flex-column overflow-x-hidden">
    <Head title="La Map des Champions & Gagnants • Tech237" />

    <!-- ====================================================================
         FLOATING TOP GAME HUD (Replaces generic website navbar & header)
         ==================================================================== -->
    <header class="game-hud-bar position-fixed top-0 start-0 w-100 px-3 px-md-4 py-2 d-flex align-items-center justify-content-between">
      
      <!-- Left HUD: Site Logo & Retour Accueil Pill -->
      <div class="d-flex align-items-center gap-2 gap-md-3">
        <!-- Site Logo (from SiteSetting) -->
        <Link href="/" class="game-hud-logo-wrap d-inline-flex align-items-center text-decoration-none shadow-sm" title="Retour au site Tech237">
          <img 
            :src="logoUrl" 
            alt="Tech237" 
            class="game-hud-logo" 
          />
        </Link>

        <!-- Back to Home Candy Button -->
        <Link 
          href="/" 
          class="btn-candy-orange d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm"
          title="Quitter la Map et revenir à l'accueil du site"
        >
          <i class="bi bi-box-arrow-left fs-5"></i>
          <span class="d-none d-sm-inline fw-black">Retour à l'accueil</span>
        </Link>
      </div>

      <!-- Center HUD: Floating Live Global Community Stats -->
      <div class="d-none d-lg-flex align-items-center gap-2 game-hud-stats-pill px-3 py-1 shadow-sm">
        <div class="d-flex align-items-center gap-2 px-2 py-1">
          <span class="hud-stat-emoji">⚡</span>
          <div class="lh-1">
            <strong class="d-block text-dark fw-bolder" style="font-size: 13px;">{{ Number(totalXp).toLocaleString() }} XP</strong>
            <span class="text-muted" style="font-size: 10px;">Énergie Totale</span>
          </div>
        </div>
        <div class="hud-stat-divider"></div>
        <div class="d-flex align-items-center gap-2 px-2 py-1">
          <span class="hud-stat-emoji">👥</span>
          <div class="lh-1">
            <strong class="d-block text-dark fw-bolder" style="font-size: 13px;">{{ totalPlayers }} Bâtisseurs</strong>
            <span class="text-muted" style="font-size: 10px;">Communauté</span>
          </div>
        </div>
        <div v-if="currentUserRank" class="hud-stat-divider"></div>
        <div v-if="currentUserRank" class="d-flex align-items-center gap-2 px-2 py-1">
          <span class="hud-stat-emoji">🥇</span>
          <div class="lh-1">
            <strong class="d-block text-emerald-700 fw-bolder" style="font-size: 13px;">Rang #{{ currentUserRank.position }}</strong>
            <span class="text-muted" style="font-size: 10px;">{{ currentUserRank.xp }} XP</span>
          </div>
        </div>
      </div>

      <!-- Right HUD: Sound Toggle & User Profile Badge -->
      <div class="d-flex align-items-center gap-2">
        <!-- Sound Effect Toggle Button -->
        <button 
          type="button" 
          @click="toggleSound" 
          class="btn-game-hud-action rounded-circle d-flex align-items-center justify-content-center shadow-sm"
          :title="soundEnabled ? 'Désactiver les effets sonores' : 'Activer les effets sonores'"
        >
          <span style="font-size: 15px;">{{ soundEnabled ? '🔊' : '🔇' }}</span>
        </button>

        <!-- User Profile shortcut if logged in -->
        <Link 
          v-if="$page.props.auth?.user" 
          :href="'/communaute/profil/' + $page.props.auth.user.id" 
          class="btn-game-user-pill d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm"
          title="Voir mon Profil Public"
        >
          <img 
            v-if="$page.props.auth.user.avatar" 
            :src="$page.props.auth.user.avatar.startsWith('/') ? $page.props.auth.user.avatar : '/' + $page.props.auth.user.avatar" 
            class="rounded-circle" 
            style="width: 26px; height: 26px; object-fit: cover;"
          />
          <span v-else class="rounded-circle bg-emerald-600 text-white fw-bold d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 11px;">
            {{ getInitials($page.props.auth.user.name) }}
          </span>
          <span class="d-none d-md-inline fw-bold text-dark small text-truncate" style="max-width: 110px;">
            {{ $page.props.auth.user.name }}
          </span>
        </Link>
        <Link 
          v-else 
          href="/login" 
          class="btn-candy-blue d-inline-flex align-items-center gap-1 text-decoration-none shadow-sm"
        >
          <span>Connexion</span>
        </Link>
      </div>

    </header>

    <!-- ====================================================================
         MAIN TWO-COLUMN SPLIT SCREEN (Full Height Arena)
         ==================================================================== -->
    <main class="game-arena-body flex-grow-1 container-fluid px-2 px-md-4 pt-5 pb-5 mt-4">
      <div class="row g-4 h-100">

        <!-- PART 1: THE CANDY CRUSH WINDING JOURNEY MAP (LARGEST SECTION: ~68% width) -->
        <section class="col-12 col-lg-7 col-xl-8 d-flex flex-column">
          <div 
            ref="mapBoardRef"
            class="candy-map-board position-relative rounded-5 shadow-lg p-3 p-sm-4 p-md-5 flex-grow-1 overflow-hidden"
          >
            
            <!-- Whimsical Layered Background Atmosphere (Candy Hills, Clouds, Floating Candies) -->
            <div class="candy-sky-backdrop"></div>
            
            <!-- Decorative Hills Background Silhouettes -->
            <div class="candy-hill-layer candy-hill-back"></div>
            <div class="candy-hill-layer candy-hill-mid"></div>
            <div class="candy-hill-layer candy-hill-front"></div>

            <!-- Floating Whimsical Clouds & Candies -->
            <div class="float-decor cloud-1">☁️</div>
            <div class="float-decor cloud-2">☁️</div>
            <div class="float-decor cloud-3">☁️</div>
            <div class="float-decor candy-1">🍭</div>
            <div class="float-decor candy-2">🍬</div>
            <div class="float-decor candy-3">🧁</div>
            <div class="float-decor sparkle-1">✨</div>
            <div class="float-decor sparkle-2">✨</div>
            <div class="float-decor balloon-1">🎈</div>

            <!-- TOP OF THE MAP: LE SOMMET DES CHAMPIONS (Highest XP Level) -->
            <div class="summit-palace-crest text-center mb-5 position-relative z-3">
              <div class="summit-castle-icon mx-auto mb-2">
                <span class="crown-bob">👑</span>
              </div>
              <h1 class="summit-title mb-1 text-dark">
                Le Sommet des Champions
              </h1>
              <p class="summit-subtitle mb-2 mx-auto text-muted">
                Grimpez les échelons du savoir : chaque POC publié, problème résolu et projet partagé vous rapproche de la Légende.
              </p>
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-95 border border-amber-300 rounded-pill shadow-xs extra-small fw-semibold text-amber-900">
                <span class="fs-6">💡</span>
                <span>Cliquez sur un palier XP pour afficher tous ses champions dans le cadre de droite</span>
              </div>
            </div>

            <!-- ==============================================================
                 THE SERPENTINE CANDY TRACK (Ordered from Top/Highest down to 0 XP at Bottom)
                 ============================================================== -->
            <div 
              ref="trackContainerRef"
              class="serpentine-track-container position-relative mx-auto my-3" 
              style="max-width: 760px;"
            >
              
              <!-- CANDY-CANE STRIPED SVG WINDING ROAD (Dynamically measured between node centers) -->
              <svg 
                class="candy-track-svg position-absolute w-100 h-100 pointer-events-none" 
                :viewBox="svgViewBox" 
                preserveAspectRatio="none"
              >
                <defs>
                  <!-- Candy Cane Diagonal Stripes Pattern (Purple & White) -->
                  <pattern id="candyStripeRoad" width="30" height="30" patternTransform="rotate(45 0 0)" patternUnits="userSpaceOnUse">
                    <rect width="15" height="30" fill="#7C3AED" />
                    <rect x="15" width="15" height="30" fill="#FFFFFF" />
                  </pattern>

                  <!-- Road Deep Shadow Filter -->
                  <filter id="roadShadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="10" stdDeviation="6" flood-color="rgba(109, 40, 217, 0.4)" />
                  </filter>
                </defs>

                <!-- Track Deep Shadow Underneath -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="rgba(0, 0, 0, 0.18)" 
                  stroke-width="44" 
                  stroke-linecap="round"
                />

                <!-- Outer Golden Curb Rails -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="#F59E0B" 
                  stroke-width="36" 
                  stroke-linecap="round"
                />

                <!-- Striped Candy Cane Main Road -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="url(#candyStripeRoad)" 
                  stroke-width="26" 
                  stroke-linecap="round"
                />

                <!-- Center Golden Dash Navigation Dots -->
                <path 
                  :d="computedCurvedSvgPath" 
                  fill="none" 
                  stroke="#FDE047" 
                  stroke-width="4.5" 
                  stroke-dasharray="10 14" 
                  stroke-linecap="round"
                  class="svg-dash-flow"
                />

                <!-- Decorative Intermediate Candy Pearls Along the Path -->
                <g v-for="(pearl, pIdx) in intermediatePearls" :key="'pearl-' + pIdx">
                  <!-- Pearl shadow -->
                  <circle :cx="pearl.x" :cy="pearl.y + 2" r="8" fill="rgba(0,0,0,0.15)" />
                  <!-- Pearl body -->
                  <circle :cx="pearl.x" :cy="pearl.y" r="7.5" :fill="pearl.color" stroke="#FFFFFF" stroke-width="2" />
                  <!-- Pearl shine -->
                  <circle :cx="pearl.x - 2.5" :cy="pearl.y - 2.5" r="2" fill="#FFFFFF" opacity="0.8" />
                </g>
              </svg>

              <!-- LIST OF XP MILESTONE NODES (Sorted: Highest at Top -> 0 XP Novice at Bottom) -->
              <div class="milestones-nodes-flow d-flex flex-column position-relative z-3">
                <div 
                  v-for="(tier, index) in sortedLevels" 
                  :key="tier.id"
                  class="milestone-station-row d-flex align-items-center my-4 position-relative"
                  :class="getSerpentineAlignment(index, sortedLevels.length)"
                >

                  <!-- OPTIONAL CANDY LANDMARK RIBBON (e.g. Tour toffee style between tiers) -->
                  <div 
                    v-if="index === Math.floor(sortedLevels.length / 2)" 
                    class="landmark-ribbon-bar position-absolute w-100 text-center pointer-events-none"
                    style="top: -42px; left: 0; z-index: 1;"
                  >
                    <div class="landmark-toffee-pill mx-auto px-4 py-1 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                      <span>🏰</span>
                      <strong class="text-uppercase tracking-wider extra-small text-amber-950">Citadelle des Bâtisseurs Tech237</strong>
                      <span>⭐</span>
                    </div>
                  </div>

                  <!-- INTERACTIVE 3D CANDY XP POD -->
                  <div 
                    :ref="el => setNodeRef(el, index)"
                    class="candy-milestone-pod text-center position-relative"
                    :class="{ 
                      'is-active-selected': selectedTier && selectedTier.id === tier.id,
                      'is-user-current': isCurrentUserTier(tier)
                    }"
                    @click="onSelectTier(tier)"
                    @mouseenter="onHoverTier(tier)"
                    :title="'Cliquer pour explorer les champions du palier ' + tier.name"
                  >
                    
                    <!-- Pulsing Halo on Selected Node -->
                    <div v-if="selectedTier && selectedTier.id === tier.id" class="pod-glow-ring"></div>

                    <!-- Summit Trophy Crown on index 0 (Highest XP) -->
                    <div v-if="index === 0" class="summit-milestone-crown position-absolute">
                      <span class="bounce-crown">🏆</span>
                    </div>

                    <!-- CANDY CRUSH SODA ICONIC YELLOW ARROW (Pointing to selected tier or user tier) -->
                    <div 
                      v-if="selectedTier && selectedTier.id === tier.id" 
                      class="candy-pointer-arrow-wrap position-absolute"
                    >
                      <div class="candy-pointer-arrow">
                        <svg width="44" height="48" viewBox="0 0 44 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <!-- Outer 3D Shadow -->
                          <path d="M22 46L6 24H14V4H30V24H38L22 46Z" fill="#DC2626" />
                          <!-- Yellow Body -->
                          <path d="M22 42L9 24H16V6H28V24H35L22 42Z" fill="#FACC15" />
                          <!-- Inner Gloss Highlight -->
                          <path d="M22 36L14 25H18V9H26V25H30L22 36Z" fill="#FEF08A" />
                        </svg>
                      </div>
                    </div>

                    <!-- 3D CANDY JEWEL BUTTON -->
                    <div 
                      class="candy-jewel-disc d-flex flex-column align-items-center justify-content-center mx-auto"
                      :style="getTierCandyDiscStyle(tier, index)"
                    >
                      <!-- Glossy Glass Arc Reflection -->
                      <div class="jewel-gloss-reflection"></div>
                      
                      <!-- Disc XP Numeric Value -->
                      <span class="jewel-xp-number fw-black">{{ tier.min_xp }}</span>
                      <span class="jewel-xp-sub">XP</span>

                      <!-- Checkmark badge if selected -->
                      <div v-if="selectedTier && selectedTier.id === tier.id" class="jewel-active-check">
                        <i class="bi bi-check-lg"></i>
                      </div>

                      <!-- Current User "Vous êtes ici !" Floating Game Pawn -->
                      <div v-if="isCurrentUserTier(tier)" class="user-game-pawn position-absolute">
                        <div class="pawn-pulse-aura"></div>
                        <div class="pawn-chip d-flex align-items-center gap-1 shadow-sm">
                          <span class="pawn-pin-icon">📍</span>
                          <span class="pawn-text">Vous êtes ici !</span>
                        </div>
                      </div>
                    </div>

                    <!-- THREE GOLDEN STARS UNDER THE BUTTON (Candy Crush Style) -->
                    <div class="candy-stars-row d-flex justify-content-center align-items-center gap-1 mt-1">
                      <span class="star-icon star-1">⭐</span>
                      <span class="star-icon star-2 star-middle">⭐</span>
                      <span class="star-icon star-3">⭐</span>
                    </div>

                    <!-- Candy Tier Name Ribbon / Capsule Under Disc -->
                    <div 
                      class="candy-tier-ribbon mt-1 px-3 py-1 rounded-pill fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                      :style="getTierRibbonStyle(tier)"
                    >
                      <i class="bi" :class="tier.badge_icon || 'bi-award-fill'"></i>
                      <span>{{ tier.name }}</span>
                    </div>

                    <!-- Members Counter Pill -->
                    <div class="mt-1">
                      <span 
                        class="badge rounded-pill extra-small px-2 py-1 shadow-2xs fw-bold"
                        :class="(tier.players && tier.players.length) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-white text-muted border'"
                      >
                        {{ (tier.players && tier.players.length) ? tier.players.length + ' champion(s)' : 'En attente' }}
                      </span>
                    </div>

                    <!-- Mini Stack of Avatars Perched at this Milestone -->
                    <div v-if="tier.players && tier.players.length" class="milestone-perched-avatars d-flex justify-content-center align-items-center mt-2">
                      <div class="d-flex align-items-center -space-x-2">
                        <div 
                          v-for="p in tier.players.slice(0, 3)" 
                          :key="p.id" 
                          class="avatar-tiny-ring rounded-circle shadow-xs"
                          :title="p.name + ' (' + (p.xp || 0) + ' XP)'"
                        >
                          <img 
                            v-if="p.avatar" 
                            :src="p.avatar.startsWith('/') ? p.avatar : '/' + p.avatar" 
                            class="w-100 h-100 rounded-circle object-fit-cover" 
                          />
                          <div 
                            v-else 
                            class="w-100 h-100 rounded-circle text-white fw-bold d-flex align-items-center justify-content-center"
                            :style="{ backgroundColor: getPlayerColor(p.name), fontSize: '9px' }"
                          >
                            {{ getInitials(p.name) }}
                          </div>
                        </div>
                        <span 
                          v-if="tier.players.length > 3" 
                          class="avatar-tiny-ring bg-white text-dark rounded-circle shadow-xs fw-bold extra-small d-flex align-items-center justify-content-center"
                          style="font-size: 9px; width: 22px; height: 22px;"
                        >
                          +{{ tier.players.length - 3 }}
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <!-- BOTTOM OF THE MAP: DÉPART DU PARCOURS • NIVEAU 0 XP (NOVICE TECH) -->
              <div class="journey-start-gate text-center mt-5 pt-3 position-relative z-3">
                <div class="start-banner-pill mx-auto px-4 py-3 rounded-pill shadow-lg d-inline-flex align-items-center gap-3">
                  <span class="fs-4">🏁</span>
                  <div class="text-start">
                    <strong class="d-block text-white text-uppercase tracking-wider fw-black" style="font-size: 14px;">
                      DÉPART DU PARCOURS &bull; NIVEAU 0 XP
                    </strong>
                    <span class="text-white opacity-90 extra-small">
                      Novice Tech &bull; Le point d'entrée officiel de tous les bâtisseurs
                    </span>
                  </div>
                  <span class="fs-4">🚀</span>
                </div>
                <p class="text-muted extra-small mt-2 mb-0">
                  Chaque nouvelle inscription démarre ici. Contribuez pour gravir la montagne des champions !
                </p>
              </div>

            </div>

          </div>
        </section>

        <!-- ==============================================================
             PART 2: THE CHAMPIONS CADRE (THE RIGHT ROSTER PANEL FOR SELECTED XP TIER)
             ============================================================== -->
        <aside class="col-12 col-lg-5 col-xl-4 align-self-start position-sticky" style="top: 85px; z-index: 10;">
          <div class="champions-cadre-board rounded-5 shadow-lg p-3 p-md-4 d-flex flex-column">
            
            <!-- Cadre Header: Selected Tier Banner with Trophy Graphic matching Screenshot 3 -->
            <transition name="tier-header-slide" mode="out-in">
              <div 
                v-if="selectedTier" 
                :key="selectedTier.id"
                class="cadre-tier-banner p-3 rounded-4 mb-3 position-relative overflow-hidden shadow-xs"
                :style="getCadreBannerStyle(selectedTier)"
              >
                <!-- 3D Gold Trophy in top right matching Screenshot 3 -->
                <div class="position-absolute end-0 top-0 mt-2 me-2 pointer-events-none" style="z-index: 1;">
                  <svg width="78" height="74" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.95; filter: drop-shadow(0 6px 10px rgba(245, 158, 11, 0.35));">
                    <path d="M35 25H85V60C85 73.8071 73.8071 85 60 85C46.1929 85 35 73.8071 35 60V25Z" fill="#F59E0B"/>
                    <path d="M40 25H80V58C80 69.0457 71.0457 78 60 78C48.9543 78 40 69.0457 40 58V25Z" fill="#FBBF24"/>
                    <path d="M35 32H18C15.7909 32 14 33.7909 14 36V48C14 56.8366 21.1634 64 30 64H35V54H28C25.7909 54 24 52.2091 24 50V40C24 38.8954 24.8954 38 26 38H35V32Z" fill="#F59E0B"/>
                    <path d="M85 32H102C104.209 32 106 33.7909 106 36V48C106 56.8366 98.8366 64 90 64H85V54H92C94.2091 54 96 52.2091 96 50V40C96 38.8954 95.1046 38 94 38H85V32Z" fill="#F59E0B"/>
                    <path d="M52 85H68V98H52V85Z" fill="#D97706"/>
                    <path d="M40 98H80V106C80 108.209 78.2091 110 76 110H44C41.7909 110 40 108.209 40 106V98Z" fill="#F59E0B"/>
                    <path d="M45 102H75" stroke="#FDE68A" stroke-width="2.5" stroke-linecap="round"/>
                  </svg>
                </div>

                <!-- Banner Content -->
                <div class="position-relative z-2">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white text-dark rounded-pill extra-small fw-bold shadow-xs">
                      Palier {{ selectedTier.min_xp }} XP
                    </span>
                    <span class="badge bg-emerald-600 text-white rounded-pill extra-small fw-bold shadow-xs">
                      Live XP ⚡
                    </span>
                    <span v-if="isCurrentUserTier(selectedTier)" class="badge bg-danger text-white rounded-pill extra-small fw-bold">
                      Votre Rang Actuel
                    </span>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <div 
                      class="cadre-icon-box rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                      :style="{ backgroundColor: selectedTier.color || '#00A859' }"
                    >
                      <i class="bi fs-4 text-white" :class="selectedTier.badge_icon || 'bi-award-fill'"></i>
                    </div>
                    <div>
                      <h2 class="h5 fw-black text-dark mb-0">
                        {{ selectedTier.name }}
                      </h2>
                      <span class="text-muted extra-small">
                        {{ selectedTierPlayers.length }} champion(s) au total
                      </span>
                    </div>
                  </div>

                  <p class="text-dark opacity-85 small mt-2 mb-0">
                    {{ selectedTier.description || 'Franchissez ce palier pour débloquer les privilèges de la communauté Tech237.' }}
                  </p>
                </div>
              </div>
            </transition>

            <!-- Search Filter Bar in this tier -->
            <div class="mb-3 position-relative">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  class="form-control bg-white border-start-0 rounded-end-pill pe-3 py-2 shadow-none" 
                  placeholder="Rechercher un champion par son nom..." 
                />
              </div>
            </div>

            <!-- Champions Roster Stream with Smooth Transitions -->
            <div class="cadre-roster-scroll flex-grow-1 overflow-y-auto pe-1">
              <transition name="roster-flip" mode="out-in">
                
                <!-- IF PLAYERS EXIST IN THIS TIER -->
                <div v-if="filteredTierPlayers.length > 0" :key="'list-' + (selectedTier ? selectedTier.id : 'none')" class="d-flex flex-column gap-2">
                  <div 
                    v-for="(player, pIdx) in filteredTierPlayers" 
                    :key="player.id"
                    class="player-roster-card p-2 px-3 rounded-4 d-flex align-items-center justify-content-between transition"
                    :class="{ 
                      'highlight-leader': pIdx === 0,
                      'highlight-self': isCurrentLoggedInUser(player)
                    }"
                    :style="pIdx === 0 ? 'background-color: #FEF9C3; border: 1.5px solid #FEF08A;' : 'background-color: #FFFFFF; border: 1px solid #F1F5F9;'"
                  >
                    <!-- Player Identity -->
                    <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
                      <!-- Medal / Rank Number -->
                      <div class="text-center flex-shrink-0" style="width: 28px;">
                        <span v-if="pIdx === 0" class="fs-5" title="1ère place">🥇</span>
                        <span v-else-if="pIdx === 1" class="fs-5" title="2ème place">🥈</span>
                        <span v-else-if="pIdx === 2" class="fs-5" title="3ème place">🥉</span>
                        <span v-else class="text-muted small fw-bold">#{{ pIdx + 1 }}</span>
                      </div>

                      <!-- Avatar -->
                      <div class="position-relative flex-shrink-0">
                        <img 
                          v-if="player.avatar" 
                          :src="player.avatar.startsWith('/') ? player.avatar : '/' + player.avatar" 
                          :alt="player.name" 
                          class="rounded-circle border" 
                          style="width: 42px; height: 42px; object-fit: cover;"
                        />
                        <div 
                          v-else 
                          class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs"
                          :style="{ width: '42px', height: '42px', backgroundColor: pIdx === 0 ? '#059669' : (pIdx === 1 ? '#2563EB' : getPlayerColor(player.name)), fontSize: '13px' }"
                        >
                          {{ getInitials(player.name) }}
                        </div>
                        <span v-if="isCurrentLoggedInUser(player)" class="user-self-pip" title="C'est vous !"></span>
                      </div>

                      <!-- Name & Rank Pill Matching Screenshot 3 -->
                      <div class="min-w-0">
                        <div class="d-flex align-items-center gap-1">
                          <Link 
                            :href="'/communaute/profil/' + player.id" 
                            class="player-name-anchor fw-bold text-dark text-truncate text-decoration-none small"
                            :title="player.name + ' - Ouvrir le profil public'"
                          >
                            {{ player.name }}
                          </Link>
                          <span v-if="isCurrentLoggedInUser(player)" class="badge bg-emerald-100 text-emerald-800 extra-small px-1 py-0 rounded">
                            Moi
                          </span>
                        </div>
                        <span class="badge rounded-pill extra-small px-2 py-0 text-truncate" style="background-color: #FEF3C7; color: #92400E; font-size: 10px;">
                          {{ player.rank_title || selectedTier?.name || 'Novice Tech 🌱' }}
                        </span>
                      </div>
                    </div>

                    <!-- Points Mint Green Badge Matching Screenshot 3 & Profile Link -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                      <span class="badge rounded-pill fw-bold extra-small px-2 py-1 shadow-2xs" style="background-color: #D1FAE5; color: #065F46; font-size: 11px;">
                        {{ player.xp || 0 }} XP
                      </span>
                      <Link 
                        :href="'/communaute/profil/' + player.id" 
                        class="btn-open-profile btn btn-sm btn-light rounded-circle shadow-2xs p-0 d-flex align-items-center justify-content-center"
                        style="width: 30px; height: 30px;"
                        title="Voir le profil public"
                      >
                        <i class="bi bi-chevron-right text-dark small"></i>
                      </Link>
                    </div>

                  </div>
                </div>

                <!-- EMPTY STATE: NO PLAYERS IN THIS TIER YET -->
                <div 
                  v-else-if="selectedTier && selectedTierPlayers.length === 0" 
                  :key="'empty-' + selectedTier.id"
                  class="empty-tier-notice text-center p-4 rounded-4 bg-white border border-dashed my-auto"
                >
                  <div class="empty-tier-icon mb-2">
                    <span class="fs-1">🚩</span>
                  </div>
                  <h3 class="h6 fw-bolder text-dark mb-1">
                    Ce sommet est encore inexploré !
                  </h3>
                  <p class="text-muted extra-small mb-3">
                    Aucun membre n'a encore atteint le palier <strong>{{ selectedTier.name }}</strong> ({{ selectedTier.min_xp }} XP requis). Soyez le premier à y hisser votre drapeau !
                  </p>
                  
                  <div class="xp-rules-box text-start p-3 rounded-3 bg-light border mb-3">
                    <strong class="d-block extra-small text-dark fw-bold mb-2 text-uppercase tracking-wider">
                      🎯 Comment remporter des XP :
                    </strong>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-1 extra-small text-muted">
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark rounded-pill px-2">+15 XP</span>
                        <span>Publier une mini-actualité (POC)</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-pill px-2">+30 XP</span>
                        <span>Partager un projet dans le showcase</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white rounded-pill px-2">+10 XP</span>
                        <span>Apporter une solution validée sur le forum</span>
                      </li>
                      <li class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary text-white rounded-pill px-2">+5 XP</span>
                        <span>Commenter et participer aux sondages</span>
                      </li>
                    </ul>
                  </div>

                  <Link 
                    v-if="$page.props.auth?.user" 
                    href="/pocs" 
                    class="btn btn-sm btn-dark rounded-pill px-3 py-2 fw-bold w-100"
                  >
                    ⚡ Partager un POC pour gagner des XP
                  </Link>
                  <Link 
                    v-else 
                    href="/login" 
                    class="btn btn-sm btn-warning rounded-pill px-3 py-2 fw-bold w-100"
                  >
                    Se connecter pour jouer
                  </Link>
                </div>

                <!-- EMPTY STATE: NO SEARCH RESULT -->
                <div v-else :key="'no-search'" class="text-center py-4 text-muted extra-small">
                  <i class="bi bi-search fs-3 d-block mb-1 text-secondary opacity-50"></i>
                  Aucun membre ne correspond à « {{ searchQuery }} » dans ce palier.
                </div>

              </transition>
            </div>

            <!-- Cadre Footer: Quick Progress & User Space Link -->
            <div class="cadre-footer pt-3 mt-3 border-top">
              <div v-if="currentUserRank" class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted extra-small d-block">Votre progression</span>
                  <strong class="text-dark small fw-bold">{{ currentUserRank.xp }} XP cumulés</strong>
                </div>
                <Link 
                  href="/espace-utilisateur" 
                  class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold extra-small"
                >
                  Mon Espace 🚀
                </Link>
              </div>
              <div v-else class="text-center">
                <span class="text-muted extra-small d-block mb-2">Rejoignez l'arène des bâtisseurs tech</span>
                <Link 
                  href="/register" 
                  class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark w-100"
                >
                  Créer un compte & Commencer à 0 XP
                </Link>
              </div>
            </div>

          </div>
        </aside>

      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  levels: {
    type: Array,
    default: () => []
  },
  players: {
    type: Array,
    default: () => []
  },
  allUsers: {
    type: Array,
    default: () => []
  },
  totalPlayers: {
    type: Number,
    default: 0
  },
  totalXp: {
    type: Number,
    default: 0
  },
  currentUserRank: {
    type: Object,
    default: null
  },
  topThree: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const searchQuery = ref('')
const selectedTier = ref(null)
const soundEnabled = ref(true)

// DOM Refs for dynamic SVG path measurement
const trackContainerRef = ref(null)
const mapBoardRef = ref(null)
const nodeElements = ref([])

function setNodeRef(el, index) {
  if (el) {
    nodeElements.value[index] = el
  }
}

// Measured node coordinates (cx, cy) relative to trackContainerRef
const measuredCoords = ref([])
const containerWidth = ref(760)
const containerHeight = ref(1500)

function updateMeasuredCoordinates() {
  if (!trackContainerRef.value || !nodeElements.value.length) return
  const containerRect = trackContainerRef.value.getBoundingClientRect()
  containerWidth.value = Math.max(containerRect.width, 320)
  containerHeight.value = Math.max(containerRect.height, 600)

  const coords = []
  for (let i = 0; i < sortedLevels.value.length; i++) {
    const el = nodeElements.value[i]
    if (el) {
      const disc = el.querySelector('.candy-jewel-disc') || el
      const rect = disc.getBoundingClientRect()
      const cx = (rect.left + rect.width / 2) - containerRect.left
      const cy = (rect.top + rect.height / 2) - containerRect.top
      coords.push({ x: cx, y: cy })
    }
  }
  if (coords.length > 0) {
    measuredCoords.value = coords
  }
}

const svgViewBox = computed(() => {
  return `0 0 ${containerWidth.value} ${containerHeight.value}`
})

// Dynamic curved SVG path between the measured node points
const computedCurvedSvgPath = computed(() => {
  const coords = measuredCoords.value
  if (!coords || coords.length === 0) {
    // Fallback static curve if not yet measured
    const n = sortedLevels.value.length || 7
    let path = ''
    const startY = 90
    const endY = containerHeight.value - 90
    const stepY = (endY - startY) / Math.max(n - 1, 1)

    for (let i = 0; i < n; i++) {
      const y = startY + i * stepY
      const mod = i % 4
      let x = containerWidth.value * 0.5
      if (mod === 1) x = containerWidth.value * 0.25
      if (mod === 3) x = containerWidth.value * 0.75

      if (i === 0) {
        path += `M ${x} ${y}`
      } else {
        const prevY = startY + (i - 1) * stepY
        const prevMod = (i - 1) % 4
        let prevX = containerWidth.value * 0.5
        if (prevMod === 1) prevX = containerWidth.value * 0.25
        if (prevMod === 3) prevX = containerWidth.value * 0.75

        const cY1 = prevY + stepY * 0.5
        const cY2 = y - stepY * 0.5
        path += ` C ${prevX} ${cY1}, ${x} ${cY2}, ${x} ${y}`
      }
    }
    return path
  }

  // Smooth cubic Bézier through measured DOM center coordinates
  let path = `M ${coords[0].x} ${coords[0].y}`
  for (let i = 1; i < coords.length; i++) {
    const prev = coords[i - 1]
    const curr = coords[i]
    const dy = curr.y - prev.y
    const c1x = prev.x
    const c1y = prev.y + dy * 0.5
    const c2x = curr.x
    const c2y = curr.y - dy * 0.5
    path += ` C ${c1x} ${c1y}, ${c2x} ${c2y}, ${curr.x} ${curr.y}`
  }
  return path
})

// Intermediate small candy pearls between nodes
const intermediatePearls = computed(() => {
  const coords = measuredCoords.value
  if (!coords || coords.length < 2) return []
  const pearls = []
  const pearlColors = ['#10B981', '#3B82F6', '#F59E0B', '#EC4899', '#8B5CF6']

  for (let i = 1; i < coords.length; i++) {
    const prev = coords[i - 1]
    const curr = coords[i]
    const dy = curr.y - prev.y

    // Calculate 2 points along the cubic bezier: t = 0.33 and t = 0.66
    for (const t of [0.33, 0.66]) {
      const c1x = prev.x
      const c1y = prev.y + dy * 0.5
      const c2x = curr.x
      const c2y = curr.y - dy * 0.5

      const mt = 1 - t
      const x = mt*mt*mt*prev.x + 3*mt*mt*t*c1x + 3*mt*t*t*c2x + t*t*t*curr.x
      const y = mt*mt*mt*prev.y + 3*mt*mt*t*c1y + 3*mt*t*t*c2y + t*t*t*curr.y

      pearls.push({
        x: Math.round(x),
        y: Math.round(y),
        color: pearlColors[(i + (t > 0.5 ? 1 : 0)) % pearlColors.length]
      })
    }
  }
  return pearls
})

// Web Audio API Synthesizer (Zero asset dependencies, instant game feedback)
let audioCtx = null

function getAudioContext() {
  if (!audioCtx && typeof window !== 'undefined') {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext
    if (AudioContextClass) {
      audioCtx = new AudioContextClass()
    }
  }
  if (audioCtx && audioCtx.state === 'suspended') {
    audioCtx.resume()
  }
  return audioCtx
}

function playHoverSound() {
  if (!soundEnabled.value) return
  try {
    const ctx = getAudioContext()
    if (!ctx) return
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.type = 'triangle'
    osc.frequency.setValueAtTime(523.25, ctx.currentTime) // C5
    osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.08) // E5
    gain.gain.setValueAtTime(0.05, ctx.currentTime)
    gain.gain.exponentialRampToValueAtTime(0.005, ctx.currentTime + 0.08)
    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.start()
    osc.stop(ctx.currentTime + 0.08)
  } catch (e) {}
}

function playPopSound() {
  if (!soundEnabled.value) return
  try {
    const ctx = getAudioContext()
    if (!ctx) return
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.type = 'sine'
    osc.frequency.setValueAtTime(587.33, ctx.currentTime) // D5
    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12) // A5
    gain.gain.setValueAtTime(0.12, ctx.currentTime)
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15)
    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.start()
    osc.stop(ctx.currentTime + 0.15)
  } catch (e) {}
}

function toggleSound() {
  soundEnabled.value = !soundEnabled.value
}

// Logo from SiteSetting
const logoUrl = computed(() => {
  const logo = page.props.siteSettings?.logo || 'assets/images/logo.png'
  return logo.startsWith('/') ? logo : '/' + logo
})

// Tiers ordered: Highest XP at the TOP (Summit) down to 0 XP at the BOTTOM (Start)
const sortedLevels = computed(() => {
  const list = [...(props.levels || [])].sort((a, b) => b.min_xp - a.min_xp)
  return list
})

// Initialize selectedTier and measure positions on mount
let resizeObserver = null

onMounted(async () => {
  if (props.currentUserRank?.tier) {
    const match = sortedLevels.value.find(
      t => t.id === props.currentUserRank.tier.id || t.min_xp === props.currentUserRank.tier.min_xp
    )
    if (match) {
      selectedTier.value = match
    }
  }

  if (!selectedTier.value) {
    // Fallback to highest tier with players, or top tier
    const withPlayers = sortedLevels.value.find(t => t.players && t.players.length > 0)
    selectedTier.value = withPlayers || sortedLevels.value[0] || null
  }

  await nextTick()
  updateMeasuredCoordinates()

  if (typeof window !== 'undefined' && 'ResizeObserver' in window && trackContainerRef.value) {
    resizeObserver = new ResizeObserver(() => {
      updateMeasuredCoordinates()
    })
    resizeObserver.observe(trackContainerRef.value)
  }
  window.addEventListener('resize', updateMeasuredCoordinates)
})

onUnmounted(() => {
  if (resizeObserver) resizeObserver.disconnect()
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateMeasuredCoordinates)
  }
})

// Players for currently selected tier
const selectedTierPlayers = computed(() => {
  if (!selectedTier.value) return []
  return selectedTier.value.players || []
})

// Filtered players by search query
const filteredTierPlayers = computed(() => {
  const players = selectedTierPlayers.value
  if (!searchQuery.value.trim()) return players
  const q = searchQuery.value.toLowerCase().trim()
  return players.filter(p => (p.name || '').toLowerCase().includes(q))
})

// Action: Click a tier node to reveal its champions in the second frame
function onSelectTier(tier) {
  selectedTier.value = tier
  searchQuery.value = ''
  playPopSound()
}

function onHoverTier(tier) {
  playHoverSound()
}

// Helpers
function isCurrentUserTier(tier) {
  if (!props.currentUserRank || !props.currentUserRank.tier) return false
  return (
    props.currentUserRank.tier.id === tier.id ||
    props.currentUserRank.tier.min_xp === tier.min_xp
  )
}

function isCurrentLoggedInUser(player) {
  return page.props.auth?.user && page.props.auth.user.id === player.id
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const colorPalette = [
  '#059669', '#2563EB', '#D97706', '#DC2626', '#7C3AED', '#DB2777', '#0891B2'
]

function getPlayerColor(name) {
  if (!name) return '#059669'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

// Serpentine alignment: zigzag pattern between center-left, center, center-right
function getSerpentineAlignment(index, total) {
  const mod = index % 4
  if (mod === 0) return 'justify-content-center'
  if (mod === 1) return 'justify-content-start ps-4 ps-md-5'
  if (mod === 2) return 'justify-content-center'
  return 'justify-content-end pe-4 pe-md-5'
}

// Candy Crush 3D Jewel Disc Styling
function getTierCandyDiscStyle(tier, index) {
  const baseColor = tier.color || '#00A859'
  const isSelected = selectedTier.value && selectedTier.value.id === tier.id

  return {
    background: `radial-gradient(circle at 35% 30%, ${lightenColor(baseColor, 38)} 0%, ${baseColor} 55%, ${darkenColor(baseColor, 35)} 100%)`,
    borderColor: '#FFFFFF',
    boxShadow: isSelected 
      ? `0 14px 28px ${hexToRgba(baseColor, 0.55)}, 0 7px 0 ${darkenColor(baseColor, 45)}, inset 0 -4px 8px rgba(0,0,0,0.3)`
      : `0 8px 20px ${hexToRgba(baseColor, 0.35)}, 0 6px 0 ${darkenColor(baseColor, 45)}, inset 0 -4px 8px rgba(0,0,0,0.25)`
  }
}

function getTierRibbonStyle(tier) {
  const baseColor = tier.color || '#00A859'
  return {
    backgroundColor: '#FFFFFF',
    color: '#0F172A',
    border: `2px solid ${baseColor}`,
    boxShadow: `0 3px 6px ${hexToRgba(baseColor, 0.2)}`
  }
}

function getCadreBannerStyle(tier) {
  const baseColor = tier.color || '#00A859'
  return {
    background: `linear-gradient(135deg, ${hexToRgba(baseColor, 0.16)} 0%, #FFFFFF 100%)`,
    border: `1.5px solid ${hexToRgba(baseColor, 0.3)}`
  }
}

// Color arithmetic helpers for custom 3D lighting
function hexToRgba(hex, alpha = 1) {
  if (!hex || !hex.startsWith('#')) return `rgba(0, 168, 89, ${alpha})`
  const clean = hex.replace('#', '')
  const r = parseInt(clean.substring(0, 2), 16) || 0
  const g = parseInt(clean.substring(2, 4), 16) || 0
  const b = parseInt(clean.substring(4, 6), 16) || 0
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

function lightenColor(col, percent) {
  if (!col || !col.startsWith('#')) return '#10B981'
  const num = parseInt(col.replace('#', ''), 16)
  const amt = Math.round(2.55 * percent)
  const R = (num >> 16) + amt
  const G = (num >> 8 & 0x00FF) + amt
  const B = (num & 0x0000FF) + amt
  return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1)
}

function darkenColor(col, percent) {
  return lightenColor(col, -percent)
}
</script>

<style scoped>
/* ==========================================================================
   CANDY CRUSH GAME ARENA ROOT & VIBRANT LANDSCAPE
   ========================================================================== */
.candy-game-root {
  background: linear-gradient(180deg, #BAE6FD 0%, #BBF7D0 35%, #FEF08A 70%, #FBCFE8 100%);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #0F172A;
  min-height: 100vh;
}

/* ==========================================================================
   FLOATING GAME HUD BAR (No default website navbar/footer)
   ========================================================================== */
.game-hud-bar {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 2px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  z-index: 1050;
}

.game-hud-logo-wrap {
  padding: 5px 12px;
  background: #FFFFFF;
  border-radius: 999px;
  border: 1.5px solid #FDE68A;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.game-hud-logo-wrap:hover {
  transform: scale(1.05);
}

.game-hud-logo {
  height: 36px;
  max-width: 140px;
  object-fit: contain;
}

/* Glossy 3D Candy Buttons */
.btn-candy-orange {
  background: linear-gradient(180deg, #F59E0B 0%, #D97706 100%);
  color: #FFFFFF !important;
  font-size: 13px;
  padding: 8px 18px;
  border-radius: 999px;
  border: 1.5px solid #FDE68A;
  box-shadow: 0 4px 0 #92400E, 0 8px 16px rgba(217, 119, 6, 0.3);
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-candy-orange:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 0 #92400E, 0 10px 20px rgba(217, 119, 6, 0.4);
  color: #FFFFFF;
}

.btn-candy-orange:active {
  transform: translateY(2px);
  box-shadow: 0 1px 0 #92400E;
}

.btn-candy-blue {
  background: linear-gradient(180deg, #3B82F6 0%, #1D4ED8 100%);
  color: #FFFFFF !important;
  font-size: 13px;
  padding: 7px 16px;
  border-radius: 999px;
  border: 1.5px solid #93C5FD;
  box-shadow: 0 3px 0 #1E40AF;
  font-weight: 700;
  transition: all 0.2s ease;
}

.btn-game-icon {
  font-size: 14px;
}

/* Floating Stats Pill */
.game-hud-stats-pill {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 999px;
  border: 1.5px solid #FDE68A;
}

.hud-stat-emoji {
  font-size: 16px;
}

.hud-stat-divider {
  width: 1px;
  height: 22px;
  background-color: #E2E8F0;
}

/* Sound & User Buttons */
.btn-game-hud-action {
  width: 38px;
  height: 38px;
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-game-hud-action:hover {
  transform: scale(1.1);
  background: #FFFBEB;
  border-color: #FCD34D;
}

.btn-game-user-pill {
  background: #FFFFFF;
  padding: 4px 12px 4px 5px;
  border-radius: 999px;
  border: 1.5px solid #E2E8F0;
  transition: all 0.2s ease;
}

.btn-game-user-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

/* ==========================================================================
   PART 1: CANDY MAP BOARD (Left Column)
   ========================================================================== */
.candy-map-board {
  background: linear-gradient(180deg, #CFF0FC 0%, #DCFCE7 40%, #FEF9C3 75%, #FCE7F3 100%);
  border: 4px solid #FFFFFF;
  min-height: calc(100vh - 120px);
}

/* Decorative Hill Silhouettes */
.candy-hill-layer {
  position: absolute;
  left: -5%;
  right: -5%;
  width: 110%;
  pointer-events: none;
  border-radius: 50% 50% 0 0 / 100% 100% 0 0;
}

.candy-hill-back {
  bottom: 0;
  height: 60%;
  background: linear-gradient(180deg, rgba(187, 247, 208, 0.45) 0%, rgba(254, 240, 138, 0.35) 100%);
  z-index: 0;
}

.candy-hill-mid {
  bottom: 0;
  height: 40%;
  background: linear-gradient(180deg, rgba(253, 230, 138, 0.45) 0%, rgba(252, 231, 243, 0.5) 100%);
  border-radius: 60% 40% 0 0 / 100% 100% 0 0;
  z-index: 0;
}

.candy-hill-front {
  bottom: 0;
  height: 22%;
  background: linear-gradient(180deg, rgba(251, 207, 232, 0.55) 0%, rgba(244, 114, 182, 0.2) 100%);
  border-radius: 40% 60% 0 0 / 100% 100% 0 0;
  z-index: 0;
}

/* Whimsical Ambient Decor */
.float-decor {
  position: absolute;
  pointer-events: none;
  user-select: none;
  z-index: 2;
  opacity: 0.88;
}

.cloud-1 { top: 60px; left: 30px; font-size: 38px; animation: floatAir 11s infinite ease-in-out alternate; }
.cloud-2 { top: 390px; right: 40px; font-size: 42px; animation: floatAir 13s infinite ease-in-out alternate-reverse; }
.cloud-3 { bottom: 270px; left: 45px; font-size: 38px; animation: floatAir 10s infinite ease-in-out alternate; }
.candy-1 { top: 220px; left: 50px; font-size: 28px; animation: floatAir 7s infinite ease-in-out alternate; }
.candy-2 { bottom: 490px; right: 50px; font-size: 26px; animation: floatAir 8s infinite ease-in-out alternate-reverse; }
.candy-3 { bottom: 120px; left: 35px; font-size: 30px; animation: floatAir 6s infinite ease-in-out alternate; }
.sparkle-1 { top: 140px; right: 80px; font-size: 22px; animation: pulseSparkle 3s infinite ease-in-out; }
.sparkle-2 { bottom: 380px; right: 90px; font-size: 24px; animation: pulseSparkle 4s infinite ease-in-out; }
.balloon-1 { top: 35px; right: 45px; font-size: 34px; animation: floatAir 14s infinite ease-in-out alternate; }

@keyframes floatAir {
  0% { transform: translateY(0) translateX(0); }
  50% { transform: translateY(-16px) translateX(10px); }
  100% { transform: translateY(8px) translateX(-8px); }
}

@keyframes pulseSparkle {
  0%, 100% { opacity: 0.25; transform: scale(0.8) rotate(0deg); }
  50% { opacity: 1; transform: scale(1.25) rotate(180deg); }
}

/* Summit Header */
.summit-castle-icon {
  width: 68px;
  height: 68px;
  border-radius: 999px;
  background: radial-gradient(circle at 35% 30%, #FEF08A 0%, #F59E0B 70%, #B45309 100%);
  border: 4px solid #FFFFFF;
  box-shadow: 0 10px 24px rgba(245, 158, 11, 0.45), 0 5px 0 #92400E;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
}

.crown-bob {
  display: inline-block;
  animation: crownHover 3s infinite ease-in-out;
}

@keyframes crownHover {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-7px); }
}

.summit-title {
  font-size: clamp(24px, 3.2vw, 36px);
  font-weight: 900;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 4px rgba(255, 255, 255, 0.85);
}

.summit-subtitle {
  max-width: 520px;
  font-size: 13px;
}

/* Landmark Ribbon (Tour toffee style from Screenshot 1) */
.landmark-toffee-pill {
  background: linear-gradient(180deg, #FDF2F8 0%, #FCE7F3 100%);
  border: 2px solid #F472B6;
  box-shadow: 0 4px 10px rgba(244, 114, 182, 0.25);
}

/* ==========================================================================
   SERPENTINE CANDY TRACK & MILESTONE JEWELS
   ========================================================================== */
.candy-track-svg {
  top: 0;
  left: 0;
  z-index: 1;
}

.svg-dash-flow {
  animation: flowDash 18s linear infinite;
}

@keyframes flowDash {
  from { stroke-dashoffset: 0; }
  to { stroke-dashoffset: 400; }
}

/* Interactive Candy Pod */
.candy-milestone-pod {
  cursor: pointer;
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  user-select: none;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  padding: 8px;
}

/* FLUID CANDY HOVER ANIMATION (Squash & stretch bounce) */
.candy-milestone-pod:hover {
  transform: translateY(-12px) scale(1.15);
}

.candy-milestone-pod:hover .candy-jewel-disc {
  filter: brightness(1.1) drop-shadow(0 0 16px rgba(245, 158, 11, 0.6));
}

.candy-milestone-pod:active {
  transform: translateY(-2px) scale(1.05);
}

/* Selection Halo */
.pod-glow-ring {
  position: absolute;
  top: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 108px;
  height: 108px;
  border-radius: 999px;
  border: 4px dashed #F59E0B;
  animation: haloSpin 10s linear infinite;
  pointer-events: none;
}

@keyframes haloSpin {
  from { transform: translateX(-50%) rotate(0deg); }
  to { transform: translateX(-50%) rotate(360deg); }
}

/* Yellow Pointer Arrow (from Screenshot 2) */
.candy-pointer-arrow-wrap {
  top: -54px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
  pointer-events: none;
}

.candy-pointer-arrow {
  animation: arrowBob 1.6s infinite ease-in-out;
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.25));
}

@keyframes arrowBob {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

/* Summit Crown on index 0 */
.summit-milestone-crown {
  top: -26px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 26px;
}

.bounce-crown {
  display: inline-block;
  animation: crownHover 2.2s infinite ease-in-out;
}

/* 3D CANDY JEWEL BUTTON */
.candy-jewel-disc {
  width: 86px;
  height: 86px;
  border-radius: 999px;
  border: 4.5px solid #FFFFFF;
  position: relative;
  overflow: visible;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Glass Reflection Arc */
.jewel-gloss-reflection {
  position: absolute;
  top: 4px;
  left: 10px;
  right: 10px;
  height: 32px;
  border-radius: 999px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.05) 100%);
  pointer-events: none;
}

.jewel-xp-number {
  font-size: 24px;
  color: #FFFFFF;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.45);
  line-height: 1;
}

.jewel-xp-sub {
  font-size: 10px;
  font-weight: 900;
  letter-spacing: 0.08em;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
}

.jewel-active-check {
  position: absolute;
  bottom: -3px;
  right: -3px;
  width: 26px;
  height: 26px;
  background: #0F172A;
  color: #FBBF24;
  border-radius: 999px;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #FFFFFF;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

/* User Pawn Pin */
.user-game-pawn {
  top: -42px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  pointer-events: none;
}

.pawn-chip {
  background: linear-gradient(180deg, #EF4444 0%, #DC2626 100%);
  color: #FFFFFF;
  padding: 3px 10px;
  border-radius: 999px;
  border: 2px solid #FFFFFF;
  font-size: 11px;
  font-weight: 800;
  box-shadow: 0 4px 10px rgba(220, 38, 38, 0.4);
  white-space: nowrap;
}

.pawn-pulse-aura {
  position: absolute;
  top: -4px;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  height: 100%;
  border-radius: 999px;
  background: rgba(239, 68, 68, 0.35);
  animation: pulsePawn 1.8s infinite;
  z-index: -1;
}

@keyframes pulsePawn {
  0% { transform: translateX(-50%) scale(0.9); opacity: 0.8; }
  100% { transform: translateX(-50%) scale(1.4); opacity: 0; }
}

/* 3 Golden Stars under Node (Candy Crush Style) */
.candy-stars-row {
  font-size: 14px;
  line-height: 1;
}

.star-middle {
  transform: translateY(-2px) scale(1.15);
}

/* Candy Ribbon Label Under Disc */
.candy-tier-ribbon {
  font-size: 12px;
  transition: all 0.25s ease;
}

.candy-milestone-pod:hover .candy-tier-ribbon {
  transform: translateY(2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
}

/* Avatars Perched at Milestone */
.avatar-tiny-ring {
  width: 24px;
  height: 24px;
  border: 2px solid #FFFFFF;
  overflow: hidden;
}

/* Start Banner at Bottom */
.start-banner-pill {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  border: 3.5px solid #A7F3D0;
  max-width: 540px;
}

/* ==========================================================================
   PART 2: THE CHAMPIONS CADRE (Right Panel)
   ========================================================================== */
.champions-cadre-board {
  background: #FFFFFF;
  border: 2px solid #F1F5F9;
  max-height: calc(100vh - 120px);
}

.cadre-tier-banner {
  border-radius: 20px;
}

.cadre-icon-box {
  width: 44px;
  height: 44px;
  flex-shrink: 0;
}

/* Champions Scroll Stream */
.cadre-roster-scroll {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E1 transparent;
}

.player-roster-card {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.player-roster-card:hover {
  transform: translateX(4px);
  background-color: #F8FAFC !important;
  border-color: #E2E8F0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
}

.player-roster-card.highlight-self {
  background-color: #ECFDF5 !important;
  border-color: #A7F3D0 !important;
}

.user-self-pip {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  background-color: #10B981;
  border: 2px solid #FFFFFF;
  border-radius: 999px;
}

.player-name-anchor {
  transition: color 0.15s ease;
}

.player-name-anchor:hover {
  color: #059669 !important;
}

.btn-open-profile:hover {
  background-color: #0F172A !important;
  color: #FFFFFF !important;
}

.btn-open-profile:hover i {
  color: #FFFFFF !important;
}

/* Smooth Vue Transitions */
.tier-header-slide-enter-active,
.tier-header-slide-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.tier-header-slide-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}

.tier-header-slide-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

.roster-flip-enter-active,
.roster-flip-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.roster-flip-enter-from {
  opacity: 0;
  transform: translateY(12px);
}

.roster-flip-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}

/* Utilities */
.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.extra-small {
  font-size: 12px;
}

.fw-black {
  font-weight: 900;
}

.-space-x-2 > * + * {
  margin-left: -6px;
}

@media (max-width: 991.98px) {
  .champions-cadre-board {
    position: static !important;
    max-height: none;
  }
}
</style>