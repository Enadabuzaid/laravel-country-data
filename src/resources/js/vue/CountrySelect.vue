<template>
  <div class="w-full">
    <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
      {{ label }}
    </label>
    <div class="relative">
      <input
          :id="id"
          v-model="search"
          type="text"
          :placeholder="placeholder"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
      />
      <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-auto shadow-md dark:bg-gray-900">
        <li
            v-for="country in filteredCountries"
            :key="country.code"
            @click="select(country)"
            class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer flex items-center gap-2"
        >
          <span v-if="withFlag">{{ country.flag }}</span>
          <span :dir="rtl ? 'rtl' : 'ltr'">
            {{ rtl ? country.name.ar : country.name.en }}
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    countries: Array,
    value: Object,
    label: { type: String, default: 'Select Country' },
    placeholder: { type: String, default: 'Search country...' },
    preferred: Array,
    rtl: Boolean,
    withFlag: Boolean,
    id: { type: String, default: 'country-select' },
  },
  data() {
    return {
      search: '',
    };
  },
  computed: {
    filteredCountries() {
      let list = this.countries;
      if (this.preferred?.length) {
        const preferred = list.filter(c => this.preferred.includes(c.code));
        const rest = list.filter(c => !this.preferred.includes(c.code));
        list = [...preferred, ...rest];
      }
      return list.filter(c =>
          (this.rtl ? c.name.ar : c.name.en).toLowerCase().includes(this.search.toLowerCase())
      );
    }
  },
  methods: {
    select(country) {
      this.$emit('input', country);
    }
  }
};
</script>