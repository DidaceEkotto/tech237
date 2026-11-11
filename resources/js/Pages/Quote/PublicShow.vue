<template>
  <div class="public-quote-page min-vh-100 py-4 py-md-5 bg-slate-50 text-slate-800">
    <!-- Top Floating Actions Bar (Hidden on print) -->
    <div class="container mb-4 no-print">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <Link href="/" class="d-flex align-items-center gap-2 text-decoration-none">
            <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold fs-6">Tech237</span>
          </Link>
          <span class="text-muted d-none d-sm-inline">|</span>
          <span class="small text-muted d-none d-sm-inline">Devis professionnel certifié</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <!-- WhatsApp Contact Button -->
          <a 
            v-if="freelance.phone" 
            :href="whatsappDirectLink" 
            target="_blank" 
            class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-2xs"
          >
            <i class="bi bi-whatsapp"></i>
            <span>Contacter le prestataire</span>
          </a>

          <!-- Print / PDF Button -->
          <button 
            type="button" 
            @click="printQuote" 
            class="btn btn-sm btn-dark rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-2xs"
          >
            <i class="bi bi-printer"></i>
            <span>Imprimer / Exporter PDF</span>
          </button>

          <!-- Accept Quote Button (If not already accepted or paid) -->
          <button 
            v-if="quote.status !== 'accepted' && quote.status !== 'paid'" 
            type="button" 
            @click="acceptQuote" 
            :disabled="accepting"
            class="btn btn-sm btn-success rounded-pill px-4 fw-bold d-flex align-items-center gap-1 shadow-sm"
          >
            <span v-if="accepting" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bi bi-check2-circle"></i>
            <span>Accepter ce devis</span>
          </button>
          <span v-else class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
            <i class="bi bi-check-circle-fill me-1"></i> Devis validé
          </span>
        </div>
      </div>
    </div>

    <!-- Alert Confirmation Acceptance -->
    <div v-if="successMsg" class="container mb-4 no-print">
      <div class="alert alert-success alert-dismissible fade show rounded-4 p-4 shadow-sm border-0 d-flex align-items-center gap-3">
        <i class="bi bi-check-circle-fill fs-3 text-success"></i>
        <div>
          <strong class="d-block mb-1">Devis accepté avec succès !</strong>
          <span class="small text-muted">{{ successMsg }}</span>
        </div>
        <button type="button" class="btn-close ms-auto" @click="successMsg = ''"></button>
      </div>
    </div>

    <!-- Printable A4 Document Sheet -->
    <div class="container">
      <div class="quote-document bg-white rounded-4 shadow-sm p-4 p-md-5 mx-auto position-relative">
        
        <!-- Document Header -->
        <div class="row g-4 align-items-start pb-4 border-bottom">
          <!-- Left: Freelance Identity -->
          <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-3">
              <img 
                v-if="quote.logo_url"
                :src="quote.logo_url" 
                alt="Logo Prestataire" 
                class="rounded-3 border object-fit-contain bg-white shadow-2xs" 
                style="max-width: 150px; max-height: 65px; height: auto;" 
              />
              <img 
                v-else
                :src="freelance.avatar" 
                :alt="freelance.name" 
                class="rounded-circle border object-fit-cover shadow-2xs" 
                style="width: 58px; height: 58px;" 
              />
              <div>
                <h2 class="h5 fw-bold text-dark mb-0">{{ freelance.name }}</h2>
                <p class="text-muted small mb-0">{{ freelance.headline }}</p>
                <div class="extra-small text-muted d-flex align-items-center gap-2 mt-1">
                  <span><i class="bi bi-geo-alt me-1"></i>{{ freelance.location }}</span>
                  <span v-if="freelance.phone">• <i class="bi bi-telephone me-1"></i>{{ freelance.phone }}</span>
                </div>
              </div>
            </div>
            <div class="extra-small text-muted">
              <span>Email : <a :href="'mailto:' + freelance.email" class="text-decoration-none text-muted">{{ freelance.email }}</a></span>
              <span v-if="freelance.username" class="ms-2">• Profil : <a :href="'/@' + freelance.username" target="_blank" class="text-danger fw-bold text-decoration-none">tech237.cm/@{{ freelance.username }}</a></span>
            </div>
          </div>

          <!-- Right: Quote Meta & Status -->
          <div class="col-md-5 text-md-end">
            <div class="d-inline-block text-start text-md-end">
              <span 
                class="badge rounded-pill px-3 py-1 mb-2 fw-bold text-uppercase"
                :class="statusBadgeClass(quote.status)"
              >
                {{ statusLabel(quote.status) }}
              </span>
              <h1 class="h3 fw-bold text-dark text-uppercase tracking-wide mb-1">
                {{ quote.type === 'invoice' ? 'Facture' : 'Devis' }}
              </h1>
              <p class="font-monospace text-danger fw-bold small mb-2">{{ quote.quote_number }}</p>
              
              <div class="extra-small text-muted">
                <div><strong>Date d'émission :</strong> {{ formatDate(quote.issue_date) }}</div>
                <div v-if="quote.due_date"><strong>Date de validité :</strong> {{ formatDate(quote.due_date) }}</div>
                <div v-if="quote.accepted_at" class="text-success fw-bold mt-1">
                  <i class="bi bi-check2 me-1"></i>Accepté le {{ formatDate(quote.accepted_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Client & Mission Info -->
        <div class="row g-4 py-4 border-bottom">
          <div class="col-md-7">
            <span class="extra-small fw-bold text-uppercase text-muted tracking-wider d-block mb-1">Destinataire / Client</span>
            <h3 class="h6 fw-bold text-dark mb-1">{{ quote.client_name }}</h3>
            <div v-if="quote.client_company" class="small text-muted fw-bold">{{ quote.client_company }}</div>
            <div v-if="quote.client_address" class="small text-muted">{{ quote.client_address }}</div>
            <div class="extra-small text-muted mt-1">
              <span v-if="quote.client_email"><i class="bi bi-envelope me-1"></i>{{ quote.client_email }}</span>
              <span v-if="quote.client_phone" class="ms-3"><i class="bi bi-telephone me-1"></i>{{ quote.client_phone }}</span>
            </div>
          </div>

          <div class="col-md-5">
            <span class="extra-small fw-bold text-uppercase text-muted tracking-wider d-block mb-1">Intitulé du Projet</span>
            <div class="p-3 bg-light rounded-3 border">
              <strong class="d-block small text-dark">{{ quote.title }}</strong>
              <span class="extra-small text-muted">Prestation de services informatiques &amp; digitaux</span>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive my-4">
          <table class="table table-bordered align-middle mb-0">
            <thead class="table-light extra-small text-uppercase text-muted">
              <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Description de la Prestation</th>
                <th class="text-center" style="width: 15%;">Unité</th>
                <th class="text-center" style="width: 10%;">Qté</th>
                <th class="text-end" style="width: 20%;">Prix Unitaire</th>
                <th class="text-end" style="width: 20%;">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in quote.items" :key="idx">
                <td class="text-center small text-muted">{{ idx + 1 }}</td>
                <td>
                  <strong class="d-block small text-dark">{{ item.description }}</strong>
                </td>
                <td class="text-center small text-muted">{{ item.unit || 'Forfait' }}</td>
                <td class="text-center small fw-bold">{{ item.quantity }}</td>
                <td class="text-end small font-monospace">{{ formatCurrency(item.unit_price) }}</td>
                <td class="text-end small font-monospace fw-bold text-dark">{{ formatCurrency(item.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals & Breakdown -->
        <div class="row g-4 pt-2">
          <div class="col-md-6 order-2 order-md-1">
            <!-- Payment Milestones (Acomptes) -->
            <div v-if="quote.payment_milestones && quote.payment_milestones.length" class="p-3 bg-light rounded-3 border mb-3">
              <span class="extra-small fw-bold text-uppercase text-dark d-block mb-2">
                <i class="bi bi-pie-chart-fill text-primary me-1"></i> Échéancier de Règlement
              </span>
              <ul class="list-unstyled mb-0 extra-small">
                <li v-for="(m, midx) in quote.payment_milestones" :key="midx" class="d-flex justify-content-between py-1 border-bottom-dashed">
                  <span class="text-muted">{{ m.label }} ({{ m.percentage }}%) :</span>
                  <strong class="font-monospace text-dark">{{ formatCurrency(m.amount) }}</strong>
                </li>
              </ul>
            </div>

            <!-- Payment Methods (Mobile Money & Bank) -->
            <div v-if="quote.payment_methods" class="p-3 bg-light rounded-3 border">
              <span class="extra-small fw-bold text-uppercase text-dark d-block mb-2">
                <i class="bi bi-wallet2 text-success me-1"></i> Coordonnées de Paiement
              </span>
              <div class="row g-2 extra-small">
                <!-- Orange Money -->
                <div v-if="quote.payment_methods.orange_money?.phone" class="col-sm-6">
                  <div class="p-2 bg-white rounded-2 border">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="badge bg-warning text-dark fw-bold" style="font-size: 10px;">Orange Money</span>
                      <button 
                        type="button" 
                        class="btn btn-2xs btn-outline-secondary py-0 px-1 extra-small no-print" 
                        @click="copyText(quote.payment_methods.orange_money.phone)"
                      >
                        Copier
                      </button>
                    </div>
                    <strong class="d-block font-monospace text-dark">{{ quote.payment_methods.orange_money.phone }}</strong>
                    <span class="text-muted text-truncate d-block">{{ quote.payment_methods.orange_money.name }}</span>
                  </div>
                </div>

                <!-- MTN Mobile Money -->
                <div v-if="quote.payment_methods.mtn_momo?.phone" class="col-sm-6">
                  <div class="p-2 bg-white rounded-2 border">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="badge bg-warning text-dark fw-bold" style="font-size: 10px;">MTN MoMo</span>
                      <button 
                        type="button" 
                        class="btn btn-2xs btn-outline-secondary py-0 px-1 extra-small no-print" 
                        @click="copyText(quote.payment_methods.mtn_momo.phone)"
                      >
                        Copier
                      </button>
                    </div>
                    <strong class="d-block font-monospace text-dark">{{ quote.payment_methods.mtn_momo.phone }}</strong>
                    <span class="text-muted text-truncate d-block">{{ quote.payment_methods.mtn_momo.name }}</span>
                  </div>
                </div>

                <!-- Bank Details -->
                <div v-if="quote.payment_methods.bank?.account_number" class="col-12 mt-2">
                  <div class="p-2 bg-white rounded-2 border">
                    <span class="badge bg-primary-subtle text-primary fw-bold mb-1" style="font-size: 10px;">Virement Bancaire</span>
                    <div class="text-muted">
                      <span><strong>Banque :</strong> {{ quote.payment_methods.bank.bank_name || 'Banque' }}</span> | 
                      <span><strong>Titulaire :</strong> {{ quote.payment_methods.bank.account_name }}</span>
                    </div>
                    <div class="font-monospace text-dark fw-bold mt-1">
                      RIB : {{ quote.payment_methods.bank.account_number }}
                    </div>
                    <div v-if="quote.payment_methods.bank.iban" class="extra-small text-muted font-monospace">
                      IBAN : {{ quote.payment_methods.bank.iban }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Calculation Card -->
          <div class="col-md-6 order-1 order-md-2">
            <div class="p-3 bg-light rounded-3 border">
              <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Sous-total Hors Taxes :</span>
                <span class="font-monospace fw-bold text-dark">{{ formatCurrency(quote.subtotal) }}</span>
              </div>
              <div v-if="quote.discount_amount > 0" class="d-flex justify-content-between py-1 small text-danger">
                <span>Remise commerciale :</span>
                <span class="font-monospace fw-bold">- {{ formatCurrency(quote.discount_amount) }}</span>
              </div>
              <div v-if="quote.tax_rate > 0" class="d-flex justify-content-between py-1 small">
                <span class="text-muted">TVA / Précompte fiscal ({{ quote.tax_rate }}%) :</span>
                <span class="font-monospace text-dark">{{ formatCurrency(quote.tax_amount) }}</span>
              </div>
              <div class="border-top mt-2 pt-2 d-flex justify-content-between align-items-center">
                <strong class="text-dark">Net à payer :</strong>
                <span class="h4 fw-bold text-danger font-monospace mb-0">{{ formatCurrency(quote.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes & Terms -->
        <div v-if="quote.notes || quote.terms" class="mt-4 pt-3 border-top extra-small text-muted">
          <div v-if="quote.notes" class="mb-2">
            <strong>Notes spécifiques :</strong>
            <p class="mb-0 whitespace-pre-line">{{ quote.notes }}</p>
          </div>
          <div v-if="quote.terms">
            <strong>Conditions de prestation &amp; validité :</strong>
            <p class="mb-0 whitespace-pre-line">{{ quote.terms }}</p>
          </div>
        </div>

        <!-- Signatures & Tech237 Stamp -->
        <div class="row g-4 mt-4 pt-4 border-top align-items-center">
          <div class="col-sm-6 text-center text-sm-start">
            <div class="p-3 border rounded-3 bg-white d-inline-block text-start" style="min-width: 220px;">
              <span class="extra-small text-muted d-block mb-1">Pour le Client :</span>
              <strong class="small d-block text-dark">{{ quote.client_name }}</strong>
              <span v-if="quote.accepted_at" class="badge bg-success-subtle text-success extra-small mt-2">
                <i class="bi bi-check-circle-fill me-1"></i> Validé en ligne le {{ formatDate(quote.accepted_at) }}
              </span>
              <span v-else class="extra-small text-muted d-block mt-3 fst-italic">Date &amp; Signature</span>
            </div>
          </div>

          <div class="col-sm-6 text-center text-sm-end">
            <div class="p-3 border rounded-3 bg-white d-inline-block text-start text-sm-end" style="min-width: 220px;">
              <span class="extra-small text-muted d-block mb-1">Pour le Prestataire :</span>
              <strong class="small d-block text-dark">{{ freelance.name }}</strong>
              <span class="badge bg-danger-subtle text-danger extra-small mt-2">
                <i class="bi bi-patch-check-fill me-1"></i> Membre Vérifié Tech237
              </span>
            </div>
          </div>
        </div>

        <!-- Footer Verification & Branding Tech237 -->
        <div class="mt-5 pt-3 border-top text-center extra-small text-muted">
          <p class="mb-1">
            Document professionnel édité via le <strong>Générateur Freelance Tech237</strong> • Le réseau des bâtisseurs tech au Cameroun &amp; en Afrique.
          </p>
          <p class="mb-0">
            Vérifiez l'authenticité et le profil de ce freelance sur : 
            <a :href="'/@' + freelance.username" target="_blank" class="text-danger fw-bold text-decoration-none">
              https://tech237.cm/@{{ freelance.username }}
            </a>
          </p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  quote: { type: Object, required: true },
  freelance: { type: Object, required: true },
  freelanceSetting: { type: Object, default: () => ({}) },
})

const accepting = ref(false)
const successMsg = ref('')

const whatsappDirectLink = computed(() => {
  if (!props.freelance.phone) return '#'
  const cleanPhone = props.freelance.phone.replace(/[^0-9]/g, '')
  const text = encodeURIComponent(`Bonjour ${props.freelance.name}, je consulte votre devis ${props.quote.quote_number} sur Tech237 concernant "${props.quote.title}".`)
  return `https://wa.me/${cleanPhone}?text=${text}`
})

function formatCurrency(val) {
  const num = Number(val || 0)
  return num.toLocaleString('fr-FR') + ' ' + (props.quote.currency || 'FCFA')
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

function statusBadgeClass(status) {
  switch (status) {
    case 'accepted': return 'bg-success text-white'
    case 'paid': return 'bg-primary text-white'
    case 'sent': return 'bg-info text-dark'
    case 'rejected': return 'bg-danger text-white'
    default: return 'bg-secondary text-white'
  }
}

function statusLabel(status) {
  switch (status) {
    case 'accepted': return 'Accepté & Validé'
    case 'paid': return 'Payé & Soldé'
    case 'sent': return 'Envoyé au Client'
    case 'rejected': return 'Refusé'
    default: return 'Brouillon'
  }
}

function copyText(str) {
  if (!str) return
  navigator.clipboard.writeText(str).then(() => {
    alert(`Numéro copié : ${str}`)
  }).catch(() => {})
}

function printQuote() {
  window.print()
}

async function acceptQuote() {
  if (accepting.value) return
  if (!confirm('Souhaitez-vous confirmer l\'acceptation de ce devis ?')) return

  accepting.value = true
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch(`/devis/${props.quote.uuid}/accept`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({}),
    })

    const data = await res.json()
    if (res.ok && data.success) {
      props.quote.status = 'accepted'
      props.quote.accepted_at = data.quote.accepted_at
      successMsg.value = data.message || 'Le devis a bien été marqué comme accepté.'
    } else {
      alert(data.error || 'Erreur lors de l\'acceptation.')
    }
  } catch (err) {
    alert('Erreur réseau : ' + err.message)
  } finally {
    accepting.value = false
  }
}
</script>

<style scoped>
.quote-document {
  max-width: 860px;
  background-color: #ffffff;
}

.extra-small {
  font-size: 11px;
}

.whitespace-pre-line {
  white-space: pre-line;
}

.tracking-wide {
  letter-spacing: 0.05em;
}

.tracking-wider {
  letter-spacing: 0.08em;
}

.border-bottom-dashed {
  border-bottom: 1px dashed #e2e8f0;
}

.btn-2xs {
  font-size: 10px;
  padding: 1px 4px;
}

/* ==========================================================================
   PRINT STYLES FOR CRISP VECTOR A4 PDF (EXACT MATCH TO PREVIEW)
   ========================================================================== */
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 12mm;
  }

  *, *:before, *:after {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  html, body {
    background: #ffffff !important;
    background-color: #ffffff !important;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 13px !important;
  }

  .public-quote-page {
    background: #ffffff !important;
    background-color: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    min-height: auto !important;
  }

  .no-print {
    display: none !important;
  }

  .container {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .quote-document {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  .table-light {
    background-color: #f8fafc !important;
    color: #475569 !important;
  }

  .bg-light {
    background-color: #f8fafc !important;
  }

  .border {
    border: 1px solid #e2e8f0 !important;
  }

  .text-danger {
    color: #dc2626 !important;
  }

  .text-success {
    color: #16a34a !important;
  }

  .text-primary {
    color: #2563eb !important;
  }

  .text-warning {
    color: #d97706 !important;
  }

  .badge {
    display: inline-block !important;
  }

  .btn {
    display: none !important;
  }

  /* Keep table borders, rows, and cards intact */
  .table {
    border-color: #cbd5e1 !important;
  }

  .quote-document .row, 
  .quote-document table, 
  .quote-document tr {
    page-break-inside: avoid;
    break-inside: avoid;
  }
}
</style>
