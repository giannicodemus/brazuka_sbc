<template>

    <Head title="Contas VoIp" />

    <AuthenticatedLayout routeName="Contas VoIp">
        <Card>
            <DataTableHeader routeName="Contas VoIp" :open="smallFormOpen" @update:open="smallFormOpen = $event"
                :filtersEnable="true" />

            <DataTable :columns="columns" :items="accounts" :actions="true" :customActions="[
                {
                    label: 'Editar',
                    icon: 'material-symbols:edit-outline',
                    onClick: handleEdit
                },
                {
                    label: 'Deletar',
                    icon: 'material-symbols:delete-outline',
                    onClick: handleDelete
                }
            ]" />



        </Card>
    </AuthenticatedLayout>

    <AddModal :password="password" :lastId="lastId" v-model:open="smallFormOpen" />
    <EditModal v-model:open="editModalOpen" :selected="selected" />

</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { toast } from "vue3-toastify";

import AddModal from "./AddModal.vue";
import EditModal from "./EditModal.vue";

const props = defineProps({
    accounts: Array,
    lastId: Number,
    password: String
});

const columns = [
    { label: "Nome", field: "name" },
];

const smallFormOpen = ref(false);
const editModalOpen = ref(false);
const selected = ref(null);

const fetchEdit = async (id) => {
    try {
        const { data } = await axios.get(`/accountvoip/edit/${id}`);
        selected.value = data.accounts
    } catch (error) {
        console.error("Erro ao buscar Conta VoIP:", error);
    }
};

const handleEdit = async (accounts) => {
    await fetchEdit(accounts.id);
    editModalOpen.value = true;
};


const handleDelete = (accounts) => {
    deleteItem(accounts.id);

};

const deleteItem = async (id) => {
    const result = await Swal.fire({
        title: 'Tem certeza?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar',
    });

    if (result.isConfirmed) {
        try {
            const { data } = await axios.delete(`/accountvoip/delete/${id}`);
            toast.success(data.success, {
                autoClose: 3000,
                position: toast.POSITION.TOP_CENTER,
            });
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } catch (error) {
            let message = "Erro ao deletar os registros!";

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
    }
};

</script>
