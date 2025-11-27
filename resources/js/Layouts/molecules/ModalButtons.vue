<template>
  <div class="flex justify-end mt-4 gap-2">
    <Link
      class="flex items-center justify-center gap-2 rounded-md transition-all duration-200 px-4 py-2 bg-second-color-evence text-white border border-transparent"
      v-if="link" :href="link">
    <Icon icon="material-symbols:arrow-back-ios-new-rounded" />
    Voltar
    </Link>
    <Button v-else icon="material-symbols:arrow-back-ios-new-rounded" type="button"
      bgColor="bg-second-brazuka hover:bg-second-hover" @click="$emit('close')">
      Voltar
    </Button>

    <Button v-if="save" icon="material-symbols:save-rounded" type="button"
      bgColor="bg-primary-brazuka hover:bg-primary-hover" @click="$emit('click')">
      Salvar
    </Button>
  </div>
</template>

<script setup>
defineEmits(['close', 'click']);
defineProps({
  save: { type: Boolean, default: true },
  link: { type: String, required: false },
});
</script>
