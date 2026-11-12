<template>
  <FrontLayout>
    <!-- Sticky Reading Progress Bar -->
    <div 
      class="reading-progress-indicator" 
      :style="{ width: readingProgress + '%' }"
      aria-hidden="true"
    ></div>

    <Head>
      <title>{{ currentTitle }} - Tech237</title>
      <meta name="description" head-key="description" :content="currentSummary || currentTitle" />
      <meta property="og:type" head-key="og:type" content="article" />
      <meta property="og:title" head-key="og:title" :content="currentTitle" />
      <meta property="og:description" head-key="og:description" :content="currentSummary || currentTitle" />
      <meta property="og:site_name" head-key="og:site_name" content="Tech237" />
      <meta property="og:url" head-key="og:url" :content="currentUrl" />
      <meta property="og:image" head-key="og:image" :content="articleImage" />
      <meta property="og:image:width" content="1200" />
      <meta property="og:image:height" content="627" />
      <meta property="og:image:alt" :content="currentTitle" />
      <meta property="article:published_time" :content="article.published_at || article.created_at" />
      <meta property="article:author" :content="article.author?.name || 'Rédaction Tech237'" />
      <meta name="twitter:card" head-key="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" head-key="twitter:title" :content="currentTitle" />
      <meta name="twitter:description" head-key="twitter:description" :content="currentSummary || currentTitle" />
      <meta name="twitter:image" head-key="twitter:image" :content="articleImage" />
    </Head>

    <div class="container my-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small text-muted">
          <li class="breadcrumb-item"><Link href="/" class="text-dark text-decoration-none">Accueil</Link></li>
          <li class="breadcrumb-item">
            <Link :href="'/category/' + (article.category?.slug || 'actu')" class="text-dark text-decoration-none">
              {{ article.category?.name_fr || 'High-Tech' }}
            </Link>
          </li>
          <li class="breadcrumb-item active text-truncate" style="max-width: 300px;">
            {{ currentTitle }}
          </li>
        </ol>
      </nav>

      <div class="row g-4">
        
        <!-- Left Main Article Column -->
        <div class="col-lg-8">
          
          <article class="bg-white">
            <!-- Article Title -->
            <h1 class="fw-bold display-6 text-dark mb-3">
              {{ currentTitle }}
            </h1>

            <!-- Publication Date, Author, Reading time & Bookmark -->
            <div class="d-flex align-items-center justify-content-between gap-2 small text-muted mb-3 flex-wrap">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span>Publié le {{ article.date_formatted || 'récemment' }}</span>
                <span>&bull;</span>
                <span>Par 
                  <Link v-if="article.author" :href="'/auteur/' + article.author.id" class="text-dark fw-bold text-decoration-underline">
                    {{ article.author.name }}
                  </Link>
                  <strong v-else class="text-dark">Rédaction Tech237</strong>
                </span>
                <span>&bull;</span>
                <span><i class="bi bi-clock me-1"></i>{{ estimatedReadingTime }} min de lecture</span>
              </div>

              <!-- Bookmark Button -->
              <button 
                @click="toggleBookmark" 
                class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                :class="isBookmarkedState ? 'btn-warning text-dark' : 'btn-outline-secondary'"
                :disabled="isBookmarking"
                :title="isBookmarkedState ? 'Retirer de mes favoris' : 'Enregistrer dans mes favoris (+2 XP)'"
              >
                <i :class="isBookmarkedState ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'"></i>
                <span>{{ isBookmarkedState ? 'Sauvegardé' : 'Lire plus tard' }}</span>
              </button>
            </div>

            <!-- Audio Narrator (TTS Player) -->
            <div class="audio-narrator-card p-3 rounded-4 mb-4 border shadow-sm bg-light">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <button 
                    @click="toggleAudioSpeech" 
                    class="btn rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm"
                    :class="isSpeaking ? 'btn-danger' : 'btn-dark'"
                    style="width: 48px; height: 48px;"
                    title="Écouter l'article"
                  >
                    <i class="bi fs-4" :class="isSpeaking && !isPaused ? 'bi-pause-fill' : 'bi-play-fill'"></i>
                  </button>
                  <div>
                    <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                      <span>Écouter cet article</span>
                      <span class="badge bg-success-subtle text-success small">Voix IA {{ locale === 'en' ? 'EN' : 'FR' }}</span>
                    </div>
                    <div class="small text-muted" style="font-size: 11px;">
                      Durée estimée : ~{{ estimatedReadingTime }} min &bull; {{ speechStatusText }}
                    </div>
                  </div>
                </div>

                <div v-if="isSpeaking" class="d-flex align-items-center gap-2">
                  <button @click="stopAudioSpeech" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-stop-fill me-1"></i> Arrêter
                  </button>
                </div>
              </div>

              <!-- Animated Progress Bar during playback -->
              <div v-if="isSpeaking" class="progress mt-3" style="height: 4px;">
                <div 
                  class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                  role="progressbar" 
                  :style="{ width: speechProgress + '%' }"
                ></div>
              </div>
            </div>

            <!-- Introductory Summary Paragraph -->
            <p v-if="currentSummary" class="lead text-dark fw-normal mb-4" style="line-height: 1.7;">
              {{ currentSummary }}
            </p>

            <!-- Featured Image -->
            <div v-if="article.featured_image" class="mb-4">
              <img 
                :src="'/' + article.featured_image" 
                :alt="currentTitle" 
                class="img-fluid rounded-3 w-100 shadow-sm" 
                style="max-height: 480px; object-fit: cover;"
              >
            </div>

            <!-- EN BREF Key Takeaways Card (matching screenshot) -->
            <div class="en-bref-box p-3 rounded-3 mb-4">
              <span class="badge text-dark fw-bold px-2 py-1 mb-2 text-uppercase" style="background-color: #9be106; font-size: 11px;">
                EN BREF
              </span>
              <ul class="mb-0 ps-3 small text-dark lh-lg">
                <li>Un concentré d'analyses et de tests vérifiés par notre rédaction.</li>
                <li>Toutes les caractéristiques, le prix et les performances passés au crible.</li>
                <li>Le meilleur choix recommandé selon vos besoins et votre profil utilisateur.</li>
              </ul>
            </div>

            <!-- WYSIWYG Content Body with In-Article Ad Integration -->
            <div class="article-body-content py-2" v-html="contentWithInArticleAd"></div>

            <!-- VOUS AVEZ AIMÉ ? PARTAGEZ ! (Black CTA Box with WhatsApp 1-Click & Copy Link) -->
            <div class="bg-dark text-white p-4 rounded-3 text-center my-4 shadow-sm">
              <h5 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">VOUS AVEZ AIMÉ ? PARTAGEZ !</h5>
              <div class="d-flex justify-content-center align-items-center gap-3 fs-5 flex-wrap">
                <!-- WhatsApp 1-Click Share Button -->
                <a 
                  :href="whatsappShareUrl" 
                  target="_blank" 
                  class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                  title="Partager sur WhatsApp"
                >
                  <i class="bi bi-whatsapp fs-6"></i>
                  <span>WhatsApp</span>
                </a>

                <!-- Copy Link Button with Toast -->
                <button 
                  type="button" 
                  @click="handleCopyLink" 
                  class="btn btn-sm btn-light text-dark rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                  title="Copier le lien direct de l'article"
                >
                  <i class="bi bi-link-45deg fs-6"></i>
                  <span>Copier le lien</span>
                </button>

                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(currentUrl)" target="_blank" class="text-white social-share-btn ms-1" title="Partager sur Facebook"><i class="bi bi-facebook"></i></a>
                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(currentTitle) + '&url=' + encodeURIComponent(currentUrl)" target="_blank" class="text-white social-share-btn" title="Partager sur X (Twitter)"><i class="bi bi-twitter-x"></i></a>
                <a :href="'mailto:?subject=' + encodeURIComponent(currentTitle) + '&body=' + encodeURIComponent(currentUrl)" class="text-white social-share-btn" title="Envoyer par email"><i class="bi bi-envelope-fill"></i></a>
              </div>
            </div>

            <!-- À PROPOS DE L'AUTEUR (Author Card with Real Avatar & Link to Public Author Profile) -->
            <div class="author-box p-4 rounded-3 text-white mb-4 bg-dark shadow-sm">
              <div class="d-flex align-items-center gap-3 mb-3">
                <img 
                  v-if="article.author?.avatar" 
                  :src="'/' + article.author.avatar" 
                  :alt="article.author.name" 
                  class="rounded-circle object-fit-cover border border-2 border-white"
                  style="width: 56px; height: 56px;"
                >
                <div v-else class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center fs-4 border border-2 border-white" style="width: 56px; height: 56px;">
                  {{ (article.author?.name || 'M').charAt(0) }}
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-white fs-5">{{ article.author?.name || 'Rédaction Tech237' }}</h6>
                    <span class="badge bg-secondary small">{{ article.author?.role || 'Auteur' }}</span>
                  </div>
                  <p class="small text-white-50 mb-0 mt-1">
                    {{ article.author?.bio || 'Journaliste tech & pop culture passionné par les nouvelles technologies, le cinéma et les jeux vidéo.' }}
                  </p>
                  <Link v-if="article.author?.id" :href="'/auteur/' + article.author.id" class="small text-danger fw-bold text-decoration-none mt-1 d-inline-block">
                    Voir la page de l'auteur &rarr;
                  </Link>
                </div>
              </div>

              <!-- Author's 4 Latest Articles -->
              <div v-if="authorArticles && authorArticles.length" class="border-top border-secondary pt-3 mt-2">
                <span class="small fw-bold text-white-50 text-uppercase d-block mb-2">Ses derniers articles :</span>
                <ul class="list-unstyled small mb-0">
                  <li v-for="autArt in authorArticles" :key="autArt.id" class="mb-1 text-truncate">
                    <Link :href="'/article/' + autArt.slug" class="text-white text-decoration-none hover-underline">
                      &rarr; {{ locale === 'en' ? (autArt.title_en || autArt.title_fr) : autArt.title_fr }}
                    </Link>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Previous and Next Article Extremity Navigation -->
            <div class="row g-3 my-4">
              <div class="col-md-6" v-if="prevArticle">
                <Link :href="'/article/' + prevArticle.slug" class="card h-100 p-3 text-decoration-none border shadow-sm rounded-3 article-nav-card">
                  <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-arrow-left-circle fs-3 text-danger"></i>
                    <div>
                      <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 10px;">Article précédent</span>
                      <h6 class="fw-bold text-dark mb-0 small text-truncate" style="max-width: 240px;">
                        {{ locale === 'en' ? (prevArticle.title_en || prevArticle.title_fr) : prevArticle.title_fr }}
                      </h6>
                    </div>
                  </div>
                </Link>
              </div>
              <div class="col-md-6" :class="{ 'offset-md-6': !prevArticle }" v-if="nextArticle">
                <Link :href="'/article/' + nextArticle.slug" class="card h-100 p-3 text-decoration-none border shadow-sm rounded-3 article-nav-card text-end">
                  <div class="d-flex align-items-center justify-content-end gap-3">
                    <div>
                      <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 10px;">Article suivant</span>
                      <h6 class="fw-bold text-dark mb-0 small text-truncate" style="max-width: 240px;">
                        {{ locale === 'en' ? (nextArticle.title_en || nextArticle.title_fr) : nextArticle.title_fr }}
                      </h6>
                    </div>
                    <i class="bi bi-arrow-right-circle fs-3 text-danger"></i>
                  </div>
                </Link>
              </div>
            </div>

            <!-- COMMENTS SECTION (With 1-Level Replies & Login Guard Modal) -->
            <div class="comments-section mt-5 pt-4 border-top">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark mb-0">
                  <i class="bi bi-chat-left-text-fill text-danger me-2"></i>
                  Commentaires ({{ totalCommentsCount }})
                </h4>
              </div>

              <!-- Flash success message -->
              <div v-if="commentSuccessMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ commentSuccessMessage }}
                <button type="button" class="btn-close" @click="commentSuccessMessage = ''"></button>
              </div>

              <!-- Add Comment Input Box -->
              <div class="card border-0 shadow-sm bg-light p-3 rounded-4 mb-4">
                <h6 class="fw-bold text-dark mb-2">Laisser un commentaire</h6>
                <div @click="checkAuthForComment">
                  <textarea 
                    v-model="newCommentContent" 
                    rows="3" 
                    class="form-control bg-white border-0 shadow-none mb-2" 
                    placeholder="Partagez votre avis sur cet article..."
                    :disabled="!authUser"
                  ></textarea>
                </div>
                <div class="d-flex justify-content-end">
                  <button 
                    @click="submitComment" 
                    class="btn rounded-pill px-4 fw-bold text-white shadow-sm btn-sm"
                    style="background-color: var(--tech-green);"
                    :disabled="isSubmittingComment || (!authUser && false)"
                  >
                    <i class="bi bi-send me-1"></i> Publier
                  </button>
                </div>
              </div>

              <!-- Comments List -->
              <div v-if="comments && comments.length" class="comments-list">
                <div v-for="comment in comments" :key="comment.id" class="card border shadow-sm p-3 rounded-4 mb-3 bg-white">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <img 
                        v-if="comment.user?.avatar" 
                        :src="'/' + comment.user.avatar" 
                        :alt="comment.user.name" 
                        class="rounded-circle object-fit-cover" 
                        style="width: 38px; height: 38px;"
                      >
                      <div v-else class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 13px;">
                        {{ (comment.user?.name || 'U').charAt(0) }}
                      </div>
                      <div>
                        <div class="fw-bold small text-dark">{{ comment.user?.name || 'Membre de la communauté' }}</div>
                        <div class="text-muted small" style="font-size: 11px;">{{ new Date(comment.created_at).toLocaleDateString('fr-FR') }}</div>
                      </div>
                    </div>
                    <button 
                      @click="openReplyForm(comment.id)" 
                      class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-0"
                      style="font-size: 12px;"
                    >
                      <i class="bi bi-reply me-1"></i> Répondre
                    </button>
                  </div>

                  <p class="text-dark small mb-2 ps-1">{{ comment.content }}</p>

                  <!-- 1-Level Reply Box if toggled -->
                  <div v-if="replyingToId === comment.id" class="mt-3 ps-3 border-start border-3 border-danger">
                    <div class="card p-2 bg-light border-0 rounded-3">
                      <textarea 
                        v-model="replyContent" 
                        rows="2" 
                        class="form-control form-control-sm bg-white mb-2" 
                        placeholder="Rédigez votre réponse..."
                      ></textarea>
                      <div class="d-flex gap-2 justify-content-end">
                        <button @click="replyingToId = null" class="btn btn-sm btn-light border py-1">Annuler</button>
                        <button @click="submitReply(comment.id)" class="btn btn-sm btn-danger py-1 px-3">Envoyer la réponse</button>
                      </div>
                    </div>
                  </div>

                  <!-- Nested Replies (1-Level) -->
                  <div v-if="comment.replies && comment.replies.length" class="mt-3 ps-3 border-start border-2 border-danger-subtle">
                    <div v-for="reply in comment.replies" :key="reply.id" class="bg-light p-2 rounded-3 mb-2">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 10px;">
                          {{ (reply.user?.name || 'U').charAt(0) }}
                        </div>
                        <span class="fw-semibold small text-dark">{{ reply.user?.name || 'Membre' }}</span>
                        <span class="text-muted small ms-auto" style="font-size: 10px;">{{ new Date(reply.created_at).toLocaleDateString('fr-FR') }}</span>
                      </div>
                      <p class="small text-muted mb-0 ps-4">{{ reply.content }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4 text-muted small bg-light rounded-4">
                Soyez le premier à commenter cet article !
              </div>
            </div>

          </article>

          <!-- À LIRE AUSSI (2-Column Grid matching screenshot) -->
          <div v-if="relatedArticles.length" class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold text-uppercase mb-0 text-dark">À LIRE AUSSI</h4>
              <Link :href="'/category/' + (article.category?.slug || 'actu')" class="small text-muted text-decoration-none">
                Voir plus de contenus &rarr;
              </Link>
            </div>

            <div class="row g-4">
              <div v-for="rel in relatedArticles.slice(0, 4)" :key="rel.id" class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                  <Link :href="'/article/' + rel.slug">
                    <img 
                      :src="'/' + (rel.featured_image || 'storage/images/chatgpt_hero.jpg')" 
                      :alt="rel.title_fr" 
                      class="card-img-top" 
                      style="height: 180px; object-fit: cover;"
                    >
                  </Link>
                  <div class="card-body p-3">
                    <span class="badge text-white fw-bold mb-2" style="background-color: var(--tech-green); font-size: 10px;">
                      {{ rel.category?.name_fr || 'ACTUALITÉ' }}
                    </span>
                    <h6 class="card-title fw-bold">
                      <Link :href="'/article/' + rel.slug" class="text-dark text-decoration-none">
                        {{ locale === 'en' ? (rel.title_en || rel.title_fr) : rel.title_fr }}
                      </Link>
                    </h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Sidebar (matching screenshot) -->
        <div class="col-lg-4">
          
          <!-- 1. Dark Newsletter Card with Tech Green Button -->
          <div class="card text-white rounded-4 p-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0B2559 0%, #06152D 100%);">
            <h5 class="fw-bold mb-1">NEWSLETTER TECH237</h5>
            <p class="small text-white-50 mb-3">Toutes les analyses et opportunités tech au Cameroun.</p>
            <form @submit.prevent="submitSidebarNewsletter">
              <div class="input-group mb-2">
                <span class="input-group-text bg-white bg-opacity-10 border-0 text-white"><i class="bi bi-envelope"></i></span>
                <input type="email" v-model="sidebarEmail" class="form-control bg-white bg-opacity-10 text-white border-0" placeholder="Votre adresse mail" required>
              </div>
              <button 
                type="submit" 
                class="btn w-100 fw-bold rounded-pill text-white mt-2 shadow-sm" 
                style="background-color: var(--tech-green);"
              >
                Je m'abonne
              </button>
              <div v-if="sidebarSubscribed" class="small text-success mt-2">
                <i class="bi bi-check-circle-fill me-1"></i> Merci pour votre inscription !
              </div>
            </form>
          </div>

          <!-- Sondage Tech du Jour -->
          <DailyTechPoll />

          <!-- Sidebar Ad Banner (Pavé 300x250) -->
          <div v-if="sidebarAd" class="card rounded-4 p-3 mb-4 border-0 shadow-sm text-center bg-light">
            <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 9px; letter-spacing: 1px;">Sponsor &bull; Partenaire</div>
            <a v-if="sidebarAd.image" :href="sidebarAd.link_url || '#'" target="_blank" rel="noopener noreferrer">
              <img :src="'/' + sidebarAd.image" :alt="sidebarAd.title || 'Publicité'" class="img-fluid rounded-3 shadow-sm w-100" style="max-height: 280px; object-fit: cover;">
            </a>
            <div v-else-if="sidebarAd.custom_code" v-html="sidebarAd.custom_code"></div>
          </div>

          <!-- 2. "En ce moment" Trending Box -->
          <div class="card rounded-4 p-4 mb-4 border shadow-sm bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-fire text-danger me-1"></i>En ce moment</h5>
            <div class="list-group list-group-flush small">
              <Link 
                v-for="(trend, idx) in relatedArticles.slice(0, 5)" 
                :key="trend.id" 
                :href="'/article/' + trend.slug" 
                class="list-group-item px-0 py-2 text-decoration-none fw-semibold"
                style="color: var(--tech-navy);"
              >
                {{ idx + 1 }}. {{ locale === 'en' ? (trend.title_en || trend.title_fr) : trend.title_fr }}
              </Link>
            </div>
          </div>

          <!-- 3. Dark "NOUS SUIVRE" Social Box -->
          <div class="card text-white rounded-4 p-4 mb-4 border-0 shadow-sm text-center" style="background-color: var(--tech-navy-dark);">
            <h6 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1px;">SUIVRE TECH237</h6>
            <p class="small text-white-50 mb-3">Suivez Tech237 sur tous nos réseaux officiels.</p>
            <div class="d-flex justify-content-center gap-3 fs-5">
              <a :href="siteSettings.social_facebook || '#'" target="_blank" class="text-white"><i class="bi bi-facebook"></i></a>
              <a :href="siteSettings.social_twitter || '#'" target="_blank" class="text-white"><i class="bi bi-twitter-x"></i></a>
              <a :href="siteSettings.social_instagram || '#'" target="_blank" class="text-white"><i class="bi bi-instagram"></i></a>
              <a :href="siteSettings.social_tiktok || '#'" target="_blank" class="text-white"><i class="bi bi-tiktok"></i></a>
              <a :href="siteSettings.social_youtube || '#'" target="_blank" class="text-white"><i class="bi bi-youtube"></i></a>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- Static Login Modal when Guest attempts to Comment -->
    <div v-if="showLoginModal" class="login-modal-backdrop" @click.self="showLoginModal = false">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 p-4 text-center bg-white">
          <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-chat-heart-fill fs-3"></i>
          </div>
          <h5 class="fw-bold mb-2">Rejoignez la discussion !</h5>
          <p class="text-muted small mb-4">
            Pour publier un commentaire et échanger avec la communauté Tech237, vous devez posséder un compte vérifié.
          </p>
          <div class="d-grid gap-2">
            <Link :href="'/login?redirect=' + encodeURIComponent(currentUrl)" class="btn rounded-pill fw-bold py-2 text-white shadow-sm" style="background-color: var(--tech-green);">
              <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
            </Link>
            <Link :href="'/register?redirect=' + encodeURIComponent(currentUrl)" class="btn btn-outline-dark rounded-pill fw-bold py-2">
              Créer un compte
            </Link>
          </div>
          <button @click="showLoginModal = false" class="btn btn-link text-muted small mt-3 text-decoration-none">
            Continuer la lecture sans commenter
          </button>
        </div>
      </div>
    </div>

  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, Head, usePage, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { useAudioNarrator } from '@/Composables/useAudioNarrator'
import DailyTechPoll from '@/Components/DailyTechPoll.vue'
import { copyTextWithToast } from '@/Utils/clipboard'

const props = defineProps({
  article: { type: Object, required: true },
  authorArticles: { type: Array, default: () => [] },
  prevArticle: { type: Object, default: null },
  nextArticle: { type: Object, default: null },
  relatedArticles: { type: Array, default: () => [] },
  comments: { type: Array, default: () => [] },
  isBookmarked: { type: Boolean, default: false },
})

const isBookmarkedState = ref(props.isBookmarked || false)
const isBookmarking = ref(false)

function toggleBookmark() {
  if (isBookmarking.value) return
  isBookmarking.value = true

  fetch('/espace-utilisateur/favoris/toggle', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      item_type: 'article',
      item_id: props.article.id,
    })
  }).then(r => {
    if (r.status === 401) {
      window.location.href = '/login'
      return
    }
    return r.json()
  }).then(data => {
    if (data && data.success) {
      isBookmarkedState.value = data.bookmarked
    }
  }).finally(() => {
    isBookmarking.value = false
  })
}

const page = usePage()
const locale = computed(() => page.props.currentLocale || 'fr')
const siteSettings = computed(() => page.props.siteSettings || {})
const authUser = computed(() => page.props.auth?.user || null)

// Translations
const currentTitle = computed(() => {
  return (locale.value === 'en' && props.article.title_en) ? props.article.title_en : props.article.title_fr
})

const currentContent = computed(() => {
  return (locale.value === 'en' && props.article.content_en) ? props.article.content_en : props.article.content_fr
})

const inArticleAd = computed(() => page.props.adBanners?.in_article?.[0] || null)
const sidebarAd = computed(() => page.props.adBanners?.sidebar?.[0] || null)

// In-Article Ad Integration
const contentWithInArticleAd = computed(() => {
  const content = currentContent.value || ''
  if (!inArticleAd.value) return content

  let adHtml = ''
  if (inArticleAd.value.image) {
    const link = inArticleAd.value.link_url || '#'
    adHtml = `
      <div class="my-4 p-3 rounded-4 bg-light border shadow-sm text-center">
        <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 10px; letter-spacing: 1px;">Publicité &bull; Partenaire Tech237</div>
        <a href="${link}" target="_blank" rel="noopener noreferrer">
          <img src="/${inArticleAd.value.image}" alt="${inArticleAd.value.title || 'Publicité'}" class="img-fluid rounded-3 shadow-sm" style="max-height: 260px; width: auto; object-fit: contain;">
        </a>
      </div>
    `
  } else if (inArticleAd.value.custom_code) {
    adHtml = `
      <div class="my-4 text-center">
        <div class="extra-small text-muted mb-1 text-uppercase font-monospace" style="font-size: 10px; letter-spacing: 1px;">Publicité &bull; Partenaire Tech237</div>
        ${inArticleAd.value.custom_code}
      </div>
    `
  }

  if (!adHtml) return content

  const paragraphs = content.split('</p>')
  if (paragraphs.length >= 3) {
    return paragraphs.slice(0, 2).join('</p>') + '</p>' + adHtml + paragraphs.slice(2).join('</p>')
  } else if (paragraphs.length === 2) {
    return paragraphs[0] + '</p>' + adHtml + paragraphs[1]
  }
  return content + adHtml
})

const currentSummary = computed(() => {
  return (locale.value === 'en' && props.article.summary_en) ? props.article.summary_en : props.article.summary_fr
})

const articleImage = computed(() => {
  return props.article.featured_image ? window.location.origin + '/' + props.article.featured_image : window.location.origin + '/assets/images/og-default.jpg'
})

const currentUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : ''
})

// Reading Progress Bar
const readingProgress = ref(0)
function updateReadingProgress() {
  const scrollTop = window.scrollY || document.documentElement.scrollTop
  const docHeight = document.documentElement.scrollHeight - window.innerHeight
  if (docHeight > 0) {
    readingProgress.value = Math.min(100, Math.max(0, Math.round((scrollTop / docHeight) * 100)))
  }
}

// WhatsApp 1-Click Share & Copy Link
const whatsappShareUrl = computed(() => {
  const text = `🔥 À lire sur Tech237 : *${currentTitle.value}*\n\n${currentUrl.value}`
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`
})

function handleCopyLink() {
  copyTextWithToast(currentUrl.value, 'Lien de l\'article copié !')
}

onMounted(() => {
  window.addEventListener('scroll', updateReadingProgress, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateReadingProgress)
})

// Audio Narrator using robust Composable (Cross-browser, Mobile & Chunked)
const {
  isSpeaking,
  isPaused,
  speechProgress,
  statusText: speechStatusText,
  toggle: toggleNarrator,
  stop: stopAudioSpeech,
  calculateEstimatedMinutes
} = useAudioNarrator()

const estimatedReadingTime = computed(() => {
  return calculateEstimatedMinutes(currentContent.value)
})

function toggleAudioSpeech() {
  const fullText = (currentTitle.value + '. ' + (currentSummary.value || '') + '. ' + (currentContent.value || ''))
  const lang = locale.value === 'en' ? 'en-US' : 'fr-FR'
  toggleNarrator(fullText, lang)
}

// Comments System
const showLoginModal = ref(false)
const newCommentContent = ref('')
const isSubmittingComment = ref(false)
const commentSuccessMessage = ref('')
const replyingToId = ref(null)
const replyContent = ref('')

const totalCommentsCount = computed(() => {
  let count = (props.comments || []).length
  for (const c of (props.comments || [])) {
    if (c.replies) count += c.replies.length
  }
  return count
})

function checkAuthForComment() {
  if (!authUser.value) {
    showLoginModal.value = true
  }
}

function openReplyForm(commentId) {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  replyingToId.value = commentId
  replyContent.value = ''
}

async function submitComment() {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  if (!newCommentContent.value || newCommentContent.value.trim().length < 2) return

  isSubmittingComment.value = true
  try {
    router.post('/api/comments', {
      article_id: props.article.id,
      content: newCommentContent.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        newCommentContent.value = ''
        commentSuccessMessage.value = 'Votre commentaire a été envoyé pour modération par l\'équipe Tech237.'
      },
      onFinish: () => {
        isSubmittingComment.value = false
      }
    })
  } catch (e) {
    isSubmittingComment.value = false
  }
}

async function submitReply(parentId) {
  if (!authUser.value) {
    showLoginModal.value = true
    return
  }
  if (!replyContent.value || replyContent.value.trim().length < 2) return

  router.post('/api/comments', {
    article_id: props.article.id,
    parent_id: parentId,
    content: replyContent.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      replyContent.value = ''
      replyingToId.value = null
      commentSuccessMessage.value = 'Votre réponse a été soumise avec succès pour modération.'
    }
  })
}

// Sidebar Newsletter
const sidebarEmail = ref('')
const sidebarSubscribed = ref(false)

async function submitSidebarNewsletter() {
  if (!sidebarEmail.value) return
  try {
    await fetch('/api/newsletter/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ email: sidebarEmail.value }),
    })
    sidebarSubscribed.value = true
    sidebarEmail.value = ''
  } catch (e) {
    sidebarSubscribed.value = true
    sidebarEmail.value = ''
  }
}
</script>

<style>
.reading-progress-indicator {
  position: fixed;
  top: 0;
  left: 0;
  height: 4px;
  background: linear-gradient(90deg, #FF004B, #FF7A00, #00A859);
  z-index: 1060;
  transition: width 0.08s ease-out;
  box-shadow: 0 1px 4px rgba(255, 0, 75, 0.4);
}
.en-bref-box {
  background-color: #f7faeb;
  border-left: 4px solid #9be106;
}
.social-share-btn {
  transition: transform 0.2s ease, color 0.2s ease;
}
.social-share-btn:hover {
  transform: scale(1.15);
  color: #9be106 !important;
}

/* WYSIWYG Content Body & Image Overflow Protection */
.article-body-content {
  font-size: 1.08rem;
  line-height: 1.85;
  color: #212529;
}
.article-body-content p {
  margin-bottom: 1.4rem;
}
.article-body-content h2, .article-body-content h3 {
  font-weight: 700;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #111;
}

/* CRITICAL: Fix image overflow inside WYSIWYG content (from screenshot) */
.article-body-content img {
  max-width: 100% !important;
  height: auto !important;
  display: block;
  margin: 1.5rem auto;
  border-radius: 8px;
  object-fit: contain;
}

.article-body-content table {
  width: 100%;
  margin: 1.5rem 0;
  border-collapse: collapse;
}
.article-body-content table th {
  background-color: #9be106;
  color: #111;
  font-weight: 700;
  padding: 10px 14px;
}
.article-body-content table td {
  border: 1px solid #dee2e6;
  padding: 10px 14px;
}

.article-nav-card {
  transition: all 0.2s ease;
}
.article-nav-card:hover {
  border-color: #FF004B !important;
  transform: translateY(-2px);
}

.login-modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(4px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
</style>
