<template>
  <div class="w-full">
    <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
      {{ label }}
    </label>
    <div class="flex gap-2">
      <select
          :id="id"
          v-model="selectedCode"
          class="w-1/3 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
      >
        <option
            v-for="country in countries"
            :key="country.code"
            :value="country.dial"
        >
          {{ withFlag ? country.flag + ' ' : '' }}{{ country.dial }}
        </option>
      </select>

      <input
          v-model="phone"
          type="tel"
          :placeholder="inputPlaceholder"
          class="w-2/3 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    countries: Array,
    label: { type: String, default: 'Phone Number' },
    withFlag: Boolean,
    id: { type: String, default: 'phone-code-select' },
    inputPlaceholder: { type: String, default: 'Enter phone number' },
  },
  data() {
    return {
      selectedCode: '',
      phone: ''
    };
  },
  watch: {
    selectedCode() {
      this.emitValue();
    },
    phone() {
      this.emitValue();
    }
  },
  methods: {
    emitValue() {
      this.$emit('input', { dial: this.selectedCode, number: this.phone });
    }
  }
};
</script>