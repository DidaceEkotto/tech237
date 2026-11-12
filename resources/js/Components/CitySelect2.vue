<template>
  <div class="city-select2-wrapper" :class="{ 'select2-pill': pill, 'select2-sm': size === 'sm' }">
    <select 
      ref="selectEl" 
      :id="id"
      :name="name"
      :required="required"
      :disabled="disabled"
      class="form-select"
      :class="[customClass, { 'rounded-pill': pill, 'form-select-sm': size === 'sm' }]"
    >
      <option v-if="includeAllOption" value="">{{ allOptionLabel }}</option>
      <option v-else-if="!required || allowClear" value=""></option>
      
      <!-- Custom / current value if not in standard list -->
      <option 
        v-if="modelValue && !allCityNames.includes(modelValue)" 
        :value="modelValue"
      >
        {{ modelValue }}
      </option>

      <option 
        v-for="city in allCityNames" 
        :key="city" 
        :value="city"
      >
        {{ city }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  id: {
    type: String,
    default: () => 'city_select_' + Math.random().toString(36).substring(2, 9),
  },
  name: {
    type: String,
    default: 'city',
  },
  placeholder: {
    type: String,
    default: 'Sélectionnez une ville du Cameroun...',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  allowClear: {
    type: Boolean,
    default: true,
  },
  pill: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md', // 'sm', 'md', 'lg'
  },
  includeAllOption: {
    type: Boolean,
    default: false,
  },
  allOptionLabel: {
    type: String,
    default: 'Toutes les villes du Cameroun',
  },
  customClass: {
    type: String,
    default: '',
  },
  cities: {
    type: Array,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const selectEl = ref(null)
let isInternalChange = false

// Guaranteed complete list of 98 unique Cameroonian cities requested
const defaultCameroonCities = [
  'Douala', 'Yaoundé', 'Garoua', 'Bamenda', 'Bafoussam', 'Maroua', 'Ngaoundéré', 'Bertoua', 'Ebolowa',
  'Buea', 'Limbe', 'Kribi', 'Édéa', 'Kumba', 'Nkongsamba', 'Dschang', 'Foumban', 'Mbouda', 'Sangmélima',
  'Mbalmayo', 'Bafang', 'Yagoua', 'Mokolo', 'Kousséri', 'Guider', 'Meiganga', 'Batouri', 'Yokadouma',
  'Tiko', 'Akonolinga', 'Abong-Mbang', 'Bafia', 'Bafut', 'Bali', 'Wum', 'Kumbo', 'Nkambe', 'Fundong',
  'Ndop', 'Mbengwi', 'Mamfe', 'Mundemba', 'Fontem', 'Muyuka', 'Ekondo-Titi', 'Idenau', 'Mutengene',
  'Manjo', 'Loum', 'Melong', 'Penja', 'Souza', 'Yabassi', 'Dibombari', 'Manoka', 'Ngambe', 'Pouma',
  'Eseka', 'Ngomedzap', 'Obala', 'Soa', 'Mfou', 'Nanga-Eboko', 'Monatélé', 'Ntui', 'Mbandjock',
  'Nkoteng', 'Ayos', 'Endom', 'Ngoulemakong', 'Ambam', 'Djoum', 'Mintom', 'Oveng', 'Akom II', 'Campo',
  'Lolodorf', 'Bipindi', 'Meyomessala', 'Garoua-Boulaï', 'Bélabo', 'Dimako', 'Doumé', 'Lomié',
  'Messamena', 'Nguelemendouka', 'Kette', 'Kentzou', 'Tignère', 'Banyo', 'Tibati', 'Ngaoundal',
  'Bankim', 'Kontcha', 'Poli', 'Tcholliré', 'Rey-Bouba', 'Figuil'
]

const allCityNames = computed(() => {
  if (Array.isArray(props.cities) && props.cities.length) {
    return props.cities
  }
  if (Array.isArray(page?.props?.cameroonCities) && page.props.cameroonCities.length) {
    return page.props.cameroonCities
  }
  return defaultCameroonCities
})

function initSelect2() {
  if (typeof window === 'undefined') return
  const $ = window.jQuery || window.$
  if (!$ || !selectEl.value || !$.fn.select2) {
    return
  }

  const $el = $(selectEl.value)

  // Destroy if already initialized to prevent duplicate wrapper
  if ($el.hasClass('select2-hidden-accessible')) {
    $el.select2('destroy')
  }

  // Find if inside modal for proper z-index dropdown attachment
  const closestModal = selectEl.value.closest('.modal')

  $el.select2({
    placeholder: props.placeholder,
    allowClear: props.includeAllOption ? false : props.allowClear,
    width: '100%',
    dropdownParent: closestModal ? $(closestModal) : $(document.body),
    language: {
      noResults: () => 'Aucune ville camerounaise trouvée',
      searching: () => 'Recherche en cours...',
    },
  })

  // Set initial value
  if (props.modelValue) {
    $el.val(props.modelValue).trigger('change.select2')
  }

  // Listen to select2 change
  $el.off('change.citySelect2').on('change.citySelect2', function () {
    const val = $(this).val() || ''
    if (val !== props.modelValue) {
      isInternalChange = true
      emit('update:modelValue', val)
      emit('change', val)
      nextTick(() => {
        isInternalChange = false
      })
    }
  })
}

watch(() => props.modelValue, (newVal) => {
  if (isInternalChange) return
  const $ = window.jQuery || window.$
  if ($ && selectEl.value && $.fn.select2) {
    $(selectEl.value).val(newVal || '').trigger('change.select2')
  }
})

watch(() => props.disabled, (newVal) => {
  const $ = window.jQuery || window.$
  if ($ && selectEl.value && $.fn.select2) {
    $(selectEl.value).prop('disabled', newVal)
  }
})

onMounted(() => {
  nextTick(() => {
    initSelect2()
  })
})

onBeforeUnmount(() => {
  const $ = window.jQuery || window.$
  if ($ && selectEl.value && $.fn.select2) {
    const $el = $(selectEl.value)
    if ($el.hasClass('select2-hidden-accessible')) {
      $el.off('change.citySelect2')
      $el.select2('destroy')
    }
  }
})
</script>

<style scoped>
.city-select2-wrapper {
  width: 100%;
}
</style>
