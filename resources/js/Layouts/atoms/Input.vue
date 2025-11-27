<template>
  <div :class="[widthClass]">
    <label v-if="label" class="block mb-1 text-sm font-medium text-gray-700 dark:text-white font-MuseoModerno">
      {{ label }}
    </label>
    <div class="relative z-0">

      <span v-if="type === 'value'"
        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-MuseoModerno text-sm">
        R$
      </span>
      <span v-else-if="icon" class=" cursor-pointer absolute left-3 top-1/2 transform -translate-y-1/2"
        :class="iconColor" @click="$emit('left-icon-click')">
        <Icon :icon="icon" class="w-5 h-5" />
      </span>
      <input v-if="inputType !== 'file'" :disabled="disabled" :type="inputType" :placeholder="dynamicPlaceholder"
        :value="formattedValue" :required="required" @input="updateValue" @blur="validateInput; $emit('blur', $event)"
        class="w-full focus:outline-none focus:ring-2 font-MuseoModerno dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
        :class="[paddingMain, paddingClass, borderClass, textColor, bgColor, classInput, rounded]" :min="minValue"
        :max="maxValue" />

      <input v-else type="file" @change="updateFile"
        class="w-full focus:outline-none focus:ring-2 font-MuseoModerno dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
        :class="[paddingMain, paddingClass, borderClass, textColor, bgColor, classInput, rounded]" />

      <!-- Right Icon -->
      <span v-if="rightIcon" class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer"
        :class="rightIconColor" @click="$emit('right-icon-click')">
        <Icon :icon="rightIcon" class="w-5 h-5" />
      </span>

      <span v-if="type === 'password'" class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer"
        :class="rightIconColor" @click="togglePassword">
        <Icon :icon="showPassword ? 'material-symbols:visibility' : 'material-symbols:visibility-off'"
          class="w-5 h-5" />
      </span>

      <div v-if="filePreview" class="mt-2 w-full flex flex-col items-center justify-center gap-2">
        <img v-if="file && file.type.startsWith('image/')" :src="filePreview" class="max-w-52 h-auto rounded" />
        <iframe v-else-if="file && file.type === 'application/pdf'" :src="filePreview" class="w-52 h-60 rounded" />
        <div v-else class="flex flex-col items-center">
          <Icon icon="mdi:file-document-outline" class="w-16 h-16 text-gray-500 dark:text-white" />
          <p class="text-sm text-center text-gray-700 dark:text-white break-words max-w-52">{{ file.name }}</p>
        </div>
      </div>


    </div>
    <span v-if="errorMessage" class="text-red-500 text-xs font-MuseoModerno mt-1 block">
      {{ errorMessage }}
    </span>
    <span v-if="helpText" class="text-gray-500 text-xs font-MuseoModerno mt-1 block dark:text-gray-300">
      {{ helpText }}
    </span>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  label: String,
  type: { type: String, default: "text" },
  placeholder: String,
  modelValue: [String, Number],
  icon: String,
  rightIcon: String,
  classInput: String,
  disabled: { type: Boolean, default: false },
  width: { type: String, default: "full" },
  borderColor: { type: String, default: "border-primary-brazuka" },
  textColor: { type: String, default: "text-gray-700" },
  bgColor: { type: String, default: "bg-white" },
  iconColor: { type: String, default: "text-gray-400" },
  rightIconColor: { type: String, default: "text-primary-brazuka" },
  rounded: { type: String, default: "rounded-md" },
  required: { type: Boolean, default: false },
  helpText: String
});

const emit = defineEmits(["update:modelValue", "blur", "sendMessage", 'right-icon-click']);
const paddingMain = computed(() => (props.type === "color" ? "p-1" : "p-3"));

const showPassword = ref(false);
const errorMessage = ref("");

const inputType = computed(() => {
  if (props.type === "password") return showPassword.value ? "text" : "password";
  if (props.type === "exten") return "number";
  return props.type === "value" ? "text" : props.type;
});

const minValue = computed(() => (props.type === "exten" ? 100 : null));
const maxValue = computed(() => (props.type === "exten" ? 9999 : null));

const widthClass = computed(() => (props.width === "full" ? "w-full" : `w-${props.width}`));
const borderClass = computed(() => `border ${props.borderColor}`);

const paddingClass = computed(() => {
  if (props.type === "value") return "pl-10";
  if (props.icon) return "pl-10";
  if (props.rightIcon) return "pr-10";
  if (props.type === "password") return "pr-10";
  return "";
});

const updateValue = (event) => {
  errorMessage.value = "";
  let value = event.target.value;

  if (props.type === "exten") {
    value = Math.min(Math.max(Number(value), 100), 9999);
  }

  if (props.type === "value") {
    value = value.replace(/\D/g, "");
    if (value === "") {
      emit("update:modelValue", "");
      return;
    }
    emit("update:modelValue", (parseFloat(value) / 100).toFixed(2));
  } else if (props.type === "phone") {
    const raw = value.replace(/\D/g, "");
    emit("update:modelValue", raw);
  } else {
    emit("update:modelValue", value);
  }
};

const formattedValue = computed(() => {
  if (props.type === "value" && props.modelValue) {
    const number = parseFloat(props.modelValue.replace(/\D/g, "")) / 100;
    return number.toLocaleString("pt-BR", {
      style: "currency",
      currency: "BRL"
    }).replace("R$", "").trim();
  }

  if (props.type === "phone" && props.modelValue) {
    if (props.modelValue.length >= 11) {
      return formatPhone(props.modelValue);
    }
    return props.modelValue;
  }

  return props.modelValue;
});

function formatPhone(value) {
  const digits = value.replace(/\D/g, "");
  if (digits.length < 11) return digits;

  const ddiLength = digits.length - 11;
  const ddi = digits.slice(0, ddiLength);
  const ddd = digits.slice(ddiLength, ddiLength + 2);
  const firstPart = digits.slice(ddiLength + 2, ddiLength + 7);
  const secondPart = digits.slice(ddiLength + 7);

  return `+${ddi} (${ddd}) ${firstPart}-${secondPart}`;
}

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const dynamicPlaceholder = computed(() => {
  if (props.placeholder) return props.placeholder;
  if (props.type === 'phone') return '+55 (DDD) _____-____';
  return '';
});

const validateInput = () => {
  if (props.type === "email" && props.modelValue) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(props.modelValue)) {
      errorMessage.value = "Email inválido.";
    }
  }
};


const file = ref(null);
const filePreview = ref(null);

watch(() => props.type, () => {
  if (props.type === 'file') {
    file.value = null;
    filePreview.value = null;
  }
});

const updateFile = (event) => {
  const selected = event.target.files[0];
  if (!selected) return;

  file.value = selected;

  if (selected.type.startsWith('image/')) {
    filePreview.value = URL.createObjectURL(selected);
  } else if (selected.type === 'application/pdf') {
    filePreview.value = URL.createObjectURL(selected);
  } else {
    filePreview.value = selected.name;
  }

  emit('update:modelValue', selected);
};

</script>
