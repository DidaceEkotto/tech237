<template>
  <FrontLayout>
    <div class="container my-5">
      
      <!-- Top Hero -->
      <div class="text-center max-w-700 mx-auto mb-5">
        <span class="badge px-3 py-2 rounded-pill fw-bold mb-3" style="background-color: var(--tech-green-light); color: var(--tech-green); border: 1px solid rgba(0, 168, 89, 0.3);">
          <i class="bi bi-heart-fill me-1" style="color: var(--tech-green);"></i> Soutenez l'Indépendance de Tech237
        </span>
        <h1 class="display-5 fw-bold text-dark mb-3">
          {{ locale === 'en' ? 'Support Our Independent Tech Journalism' : 'Contribuez à la Vie du Média Tech237' }}
        </h1>
        <p class="lead text-muted">
          {{ locale === 'en' 
            ? 'Tech237 produces daily in-depth tech news, startup spotlights, and job opportunities in Cameroon. Your support directly empowers local innovation.' 
            : 'Tech237 - Là où les idées deviennent des projets. Nous mettons en lumière les startups, les talents tech et les opportunités du numérique au Cameroun. Vos dons permettent de soutenir l\'écosystème local.' 
          }}
        </p>
      </div>

      <div class="row g-4 justify-content-center max-w-900 mx-auto">
        
        <!-- 1. Orange Money -->
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white" style="border-top: 5px solid #FF6600 !important;">
            <div class="mb-3 d-flex justify-content-center align-items-center" style="height: 68px;">
              <img src="/assets/images/orange-money.png" alt="Orange Money" class="img-fluid rounded-3" style="max-height: 64px; object-fit: contain;">
            </div>
            <h4 class="fw-bold mb-1" style="color: #FF6600;">Orange Money</h4>
            <p class="small text-muted mb-3">Paiement Mobile Rapide & Sécurisé</p>
            
            <div class="p-3 bg-light rounded-3 mb-2 border text-start">
              <span class="small text-muted d-block">Nom du compte / Titulaire :</span>
              <strong class="text-dark fs-6">{{ settings.orange_money_name || 'Tech237 Media' }}</strong>
            </div>

            <div class="p-3 bg-light rounded-3 border text-start">
              <span class="small text-muted d-block">Numéro de téléphone :</span>
              <code class="fs-6 fw-bold text-dark">{{ settings.orange_money_phone || '+237 690 00 00 00' }}</code>
            </div>
          </div>
        </div>

        <!-- 2. MTN Mobile Money -->
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white" style="border-top: 5px solid #FFCC00 !important;">
            <div class="mb-3 d-flex justify-content-center align-items-center" style="height: 68px;">
              <img src="/assets/images/mtn-momo.jpg" alt="MTN MoMo" class="img-fluid rounded-3 shadow-sm" style="max-height: 64px; object-fit: contain;">
            </div>
            <h4 class="fw-bold mb-1 text-dark">MTN MoMo</h4>
            <p class="small text-muted mb-3">Mobile Money Afrique & International</p>
            
            <div class="p-3 bg-light rounded-3 mb-2 border text-start">
              <span class="small text-muted d-block">Nom du compte / Titulaire :</span>
              <strong class="text-dark fs-6">{{ settings.mtn_money_name || 'Tech237 Media' }}</strong>
            </div>

            <div class="p-3 bg-light rounded-3 border text-start">
              <span class="small text-muted d-block">Numéro de téléphone :</span>
              <code class="fs-6 fw-bold text-dark">{{ settings.mtn_money_phone || '+237 670 00 00 00' }}</code>
            </div>
          </div>
        </div>

        <!-- 3. RIB / Virement Bancaire -->
        <div class="col-md-12 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center" style="border-top: 5px solid #0066FF !important;">
            <div class="mb-3">
              <div class="rounded-circle d-inline-flex p-3 fs-2 text-white shadow-sm" style="background-color: #0066FF;">
                <i class="bi bi-bank"></i>
              </div>
            </div>
            <h4 class="fw-bold mb-1 text-primary">Virement Bancaire (RIB)</h4>
            <p class="small text-muted mb-3">Virement SEPA / International</p>
            
            <div class="p-3 bg-light rounded-3 border text-start font-monospace small" style="white-space: pre-line; word-break: break-all;">
              {{ settings.rib_details || "IBAN: CM21 1000 5000 0112 3456 7890 123\nBIC/SWIFT: TECH237CMXXX\nBanque: Tech237 Innovation Lab / Afriland Douala" }}
            </div>
          </div>
        </div>

      </div>

      <!-- Support Footer Note -->
      <div class="text-center mt-5">
        <p class="small text-muted">
          <i class="bi bi-shield-check text-success me-1"></i>
          Toutes les transactions sont directes et sans intermédiaire. Pour toute question ou reçu de don, contactez :
          <strong>{{ settings.primary_email }}</strong>
        </p>
      </div>

    </div>
  </FrontLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const page = usePage();
const settings = computed(() => page.props.siteSettings || {});
const locale = computed(() => page.props.currentLocale || 'fr');
</script>

<style scoped>
.max-w-700 { max-width: 700px; }
.max-w-900 { max-width: 960px; }
</style>
