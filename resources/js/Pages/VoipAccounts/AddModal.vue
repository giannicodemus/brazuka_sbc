<template>
  <Modal :open="open" title="Adicionar Conta VoIP" @update:open="$emit('update:open', false)">
    <ModalBody>
      <FormContainer>
        <Row>
          <Input required classInput="h-10" placeholder="Nome" label="Nome da Conta" v-model="form.name" />
        </Row>
        <Row>
          <Input required classInput="h-10" placeholder="Codec" label="Codec" v-model="form.codec" />
          <Select selectClass="h-10" label="Tipo de Autenticação" placeholder="Selecione..." v-model="form.auth_type"
            :options="[
              { value: '0', label: 'Usuário e Senha' },
              { value: '1', label: 'IP' }
            ]" />
        </Row>
        <Row v-if="form.auth_type === '0'">
          <Input required classInput="h-10" placeholder="Nome de Usuário" label="Nome de Usuário" 
            v-model="form.username" />
          <Input required classInput="h-10" placeholder="Senha" label="Senha" type="text" 
            v-model="form.password" />
        </Row>
        <Row v-if="form.auth_type === '1'">
          <Input required classInput="h-10" placeholder="192.168.0.1" label="Host" v-model="form.host" />
          <Input required classInput="h-10" placeholder="Techprefix" label="Techprefix" type="number" v-model="form.techprefix" />
        </Row>

        <ModalButtons @close="$emit('update:open', false)" @click="submitForm" :disabled="!isFormValid" />
      </FormContainer>
    </ModalBody>
  </Modal>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { toast } from "vue3-toastify";

const props = defineProps({
  open: Boolean,
  accounts: Array,
  lastId: [Number, null],
  password: String,
});

const emit = defineEmits(["update:open"]);

const form = ref({
  name: "",
  codec: "g729,alaw,ulaw",
  auth_type: "0",
  username: "",
  password: props.password || "",
  host: "",
  techprefix: "",
});

watch(
  () => [props.lastId, form.value.auth_type],
  ([lastId, authType]) => {
    if (authType === "0") {
      const id = lastId ?? 1;
      form.value.username = `brazukangn${id}`;
    } else {
      form.value.username = "";
    }
  },
  { immediate: true }
);

const resetForm = () => {
  form.value = {
    name: "",
    codec: "",
    auth_type: "0",
    username: props.lastId ? `brazukangn${props.lastId}` : "brazukangn1",
    password: props.password || "",
    host: "",
    techprefix: "",
  };
};

const isFormValid = computed(() => {
  if (!form.value.name) return false;

  if (form.value.auth_type === "0") {
    return !!form.value.username && !!form.value.password;
  } else if (form.value.auth_type === "1") {
    return !!form.value.host && !!form.value.techprefix;
  }

  return false;
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
    const response = await axios.post("/accountvoip/store", form.value);

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

      if (data.message) {
        message = data.message;
      }

      if (data.errors) {
        const errorsArray = Object.values(data.errors).flat();
        if (errorsArray.length) {
          message = errorsArray.join("\n");
        }
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
