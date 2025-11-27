<template>
  <Modal v-if="form" :open="open" title="Editar Lista de Caller ID" @update:open="$emit('update:open', false)">
    <ModalBody>
      <FormContainer v-if="form.name !== undefined">
        <Row>
          <Input required classInput="h-10" placeholder="Nome" label="Nome da Lista" v-model="form.name" />
        </Row>

        <div class="w-full">
          <Button class="mt-4 h-10 w-full" @click="addCustom" icon="material-symbols:add">
            Adicionar Números
          </Button>
        </div>

        <div v-for="(element, index) in form.clids" :key="element.id ?? index">
          <Card class="dark:shadow-md dark:bg-gray-600">
            <div class="flex items-center">
              <h3 class="text-lg font-medium dark:text-white flex-1 font-MuseoModerno">
                Número {{ index + 1 }}
              </h3>
              <Button @click="removeCustom(index)" icon="material-symbols:delete" buttonClass="bg-red-500"
                type="button" />
            </div>
            <FormContainer>
              <Row>
                <Input required classInput="h-10" placeholder="Ordem" label="Ordem" type="number"
                  v-model.number="element.order" @change="adjustOrder(element.order, index)" />
                <Input required classInput="h-10" placeholder="Número" label="Número" v-model="element.number"
                  type="number" />
              </Row>
            </FormContainer>
          </Card>
        </div>

        <ModalButtons @close="$emit('update:open', false)" @click="submitForm" :disabled="!isFormValid" />
      </FormContainer>
    </ModalBody>
  </Modal>
</template>
<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { toast } from "vue3-toastify";

const props = defineProps({
  open: Boolean,
  selected: Object,
});

const emit = defineEmits(["update:open"]);

let nextId = 1;

const form = ref({
  id: "",
  name: "",
  clids: [],
});

const addCustom = () => {
  form.value.clids.push({
    id: nextId++,
    order: form.value.clids.length + 1,
    number: "",
  });
};

const removeCustom = (index) => {
  form.value.clids.splice(index, 1);
  reorderClids();
};

const resetForm = () => {
  form.value = { id: "", name: "", clids: [] };
  nextId = 1;
};
watch(
  () => props.selected,
  (val) => {
    if (val) {
      form.value.id = val.id ?? "";
      form.value.name = val.name ?? "";
      form.value.clids = Array.isArray(val.clids)
        ? val.clids.map((c) => ({
          id: c.id ?? null,
          order: c.order ?? 0,
          number: c.number ?? 0,
        }))
        : [];
    }
  },
  { immediate: true }
);



const adjustOrder = (newOrder, currentIndex) => {
  newOrder = Math.max(1, Math.min(newOrder, form.value.clids.length));
  const moved = form.value.clids.splice(currentIndex, 1)[0];
  form.value.clids.splice(newOrder - 1, 0, moved);
  reorderClids();
};

const reorderClids = () => {
  form.value.clids.forEach((c, i) => (c.order = i + 1));
};

const isFormValid = computed(() => {
  if (!form.value.name) return false;
  if (form.value.clids.length) {
    return form.value.clids.every((c) => c.order && c.number);
  }
  return true;
});

const submitForm = async () => {
  if (!isFormValid.value) {
    toast.error("Preencha todos os campos corretamente!", {
      autoClose: 3000,
      position: toast.POSITION.TOP_CENTER,
    });
    return;
  }

  try {
    const response = await axios.put(`/clid/update/${form.value.id}`, {
      ...form.value,
    });
    if ([200, 201].includes(response.status)) {
      toast.success(response.data.success, {
        autoClose: 3000,
        position: toast.POSITION.TOP_CENTER,
      });

      resetForm();
      emit("update:open", false);
      router.reload();
    }
  } catch (error) {
    let message = "Erro ao salvar os registros!";
    if (error.response?.data) {
      const data = error.response.data;
      if (data.message) message = data.message;
      if (data.errors) {
        const errorsArray = Object.values(data.errors).flat();
        if (errorsArray.length) message = errorsArray.join("\n");
      }
    }

    toast.error(message, {
      autoClose: 3000,
      position: toast.POSITION.TOP_CENTER,
    });

    console.error("Erro na requisição:", error);
  }
};
</script>