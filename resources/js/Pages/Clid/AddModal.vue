<template>
  <Modal :open="open" title="Adicionar Lista de Caller ID" @update:open="$emit('update:open', false)">
    <ModalBody>
      <FormContainer>
        <Row>
          <Input required classInput="h-10" placeholder="Nome" label="Nome da Lista" v-model="form.name" />
        </Row>

        <div class="w-full">
          <Button class="mt-4 h-10 w-full" @click="addCustom" icon="material-symbols:add">
            Adicionar Números
          </Button>
        </div>

        <div v-for="(element, index) in form.clids" :key="element.id">
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
import { reactive, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { toast } from "vue3-toastify";

const props = defineProps({
  open: Boolean,
});

const emit = defineEmits(["update:open"]);

const form = reactive({
  name: "",
  clids: [],
});

let nextId = 1;

const addCustom = () => {
  form.clids.push({ id: nextId++, order: form.clids.length + 1, number: "" });
};

const removeCustom = (index) => {
  form.clids.splice(index, 1);
  reorderClids();
};

const resetForm = () => {
  form.name = "";
  form.clids = [];
  nextId = 1;
};


const adjustOrder = (newOrder, currentIndex) => {
  if (newOrder < 1) newOrder = 1;
  if (newOrder > form.clids.length) newOrder = form.clids.length;

  const moved = form.clids.splice(currentIndex, 1)[0];
  form.clids.splice(newOrder - 1, 0, moved);

  reorderClids();
};

const reorderClids = () => {
  form.clids.forEach((c, i) => (c.order = i + 1));
};

const isFormValid = computed(() => {
  if (!form.name) return false;
  if (form.clids.length) {
    return form.clids.every((c) => c.order && c.number);
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
    const response = await axios.post("/clid/store", form);
    if ([200, 201].includes(response.status)) {
      toast.success(response.data.success, { autoClose: 3000, position: toast.POSITION.TOP_CENTER });
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
    toast.error(message, { autoClose: 3000, position: toast.POSITION.TOP_CENTER });
    console.error("Erro na requisição:", error);
  }
};
</script>
