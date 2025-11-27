<template>

    <Head title="Listas de Caller ID" />

    <AuthenticatedLayout routeName="Listas de Caller ID">
        <Card>
            <DataTableHeader routeName="Listas de Caller ID" :open="smallFormOpen" @update:open="smallFormOpen = $event"
                :filtersEnable="true" />

            <DataTable :columns="columns" :items="randomclids" :actions="true" :customActions="[
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

    <AddModal v-model:open="smallFormOpen" />
    <EditModal v-model:open="editModalOpen" :selected="selected" />

</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { toast } from "vue3-toastify";

import AddModal from "./AddModal.vue";
import EditModal from "./EditModal.vue";

const props = defineProps({
    randomclids: Array,
    clids: Array,
});

const columns = [
    { label: "Nome ", field: "name" },
];

const smallFormOpen = ref(false);
const editModalOpen = ref(false);
const selected = ref(null);

const fetchEdit = async (id) => {
    try {
        const { data } = await axios.get(`/clid/edit/${id}`);
        selected.value = {
            ...data.randomclids,
            clids: data.clids || [],

        };
    } catch (error) {
        console.error("Erro ao buscar Lista de Caller ID:", error);
    }
};


const handleEdit = async (randomclids) => {
    await fetchEdit(randomclids.id);
    editModalOpen.value = true;
};


const handleDelete = (randomclids) => {
    deleteItem(randomclids.id);

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
            const { data } = await axios.delete(`/clid/delete/${id}`);
            toast.success(data.success, {
                autoClose: 3000,
                position: toast.POSITION.TOP_CENTER,
            });
            setTimeout(() => {
                window.location.reload();
            }, 700);
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
