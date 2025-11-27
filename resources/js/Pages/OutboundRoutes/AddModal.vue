<template>
  <Modal :open="open" title="Adicionar Tronco VoIP" @update:open="$emit('update:open', false)">
    <ModalBody>
      <FormContainer>
        <Row>
          <Select selectClass="h-10" label="Conta VoIP" placeholder="Selecione..." v-model="form.voip_account_id"
            :options="accounts" />
          <Select selectClass="h-10" label="Tronco VoIP" placeholder="Selecione..." v-model="form.voip_trunk_id"
            :options="trunks" />
        </Row>
        <Row>
          <Select selectClass="h-10" label="Caller ID" placeholder="Selecione..." v-model="form.random_clid_list_id"
            :options="clids" />
          <Select selectClass="h-10" label="Gravação" placeholder="Selecione..." v-model="form.recording" :options="[
            { value: '0', label: 'Não' },
            { value: '1', label: 'Sim' }
          ]" />
        </Row>

        <ModalButtons @close="$emit('update:open', false)" @click="submitForm" :disabled="!isFormValid" />
      </FormContainer>
    </ModalBody>
  </Modal>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { toast } from "vue3-toastify";

const props = defineProps({
  open: Boolean,
  accounts: Array,
  trunks: Array,
  clids: Array,
});

const emit = defineEmits(["update:open"]);

const form = ref({
  voip_account_id: "",
  voip_trunk_id: "",
  random_clid_list_id: "",
  recording: "0",
});

const resetForm = () => {
  form.value = {
    voip_account_id: "",
    voip_trunk_id: "",
    random_clid_list_id: "",
    recording: "0",
  };
};

const isFormValid = computed(() => {
  return !!form.value.voip_account_id &&
    !!form.value.voip_trunk_id;
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
    const response = await axios.post("/outboundroutes/store", form.value);

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
