<template>

    <Head title="Rotas de Saída" />

    <AuthenticatedLayout routeName="Rotas de Saída">
        <Card>
            <DataTableHeader routeName="Rotas de Saída" :open="smallFormOpen" @update:open="smallFormOpen = $event"
                :filtersEnable="true" />

            <DataTable :columns="columns" :items="mappedOutbounds" :actions="true" :customActions="[
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

    <AddModal :outbounds="outbounds" :accounts="accounts" :trunks="trunks" :clids="clids"
        v-model:open="smallFormOpen" />
    <EditModal v-model:open="editModalOpen" :selected="selected" :accounts="accounts" :trunks="trunks" :clids="clids" />

</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { toast } from "vue3-toastify";

import AddModal from "./AddModal.vue";
import EditModal from "./EditModal.vue";

const props = defineProps({
    outbounds: Array,
    accounts: Array,
    trunks: Array,
    clids: Array,
});

const columns = [
    { label: "Conta VoIP", field: "voip_account_id" },
    { label: "Tronco VoIP", field: "voip_trunk_id" },
];

const mappedOutbounds = computed(() => {
    return props.outbounds.map(item => {
        const account = props.accounts.find(a => a.id === item.voip_account_id);
        const trunk = props.trunks.find(t => t.id === item.voip_trunk_id);

        return {
            ...item,
            voip_account_id: account ? account.name : item.voip_account_id,
            voip_trunk_id: trunk ? trunk.name : item.voip_trunk_id,
        };
    });
});

const smallFormOpen = ref(false);
const editModalOpen = ref(false);
const selected = ref(null);

const fetchEdit = async (id) => {
    try {
        const { data } = await axios.get(`/outboundroutes/edit/${id}`);
        selected.value = data.outbounds
    } catch (error) {
        console.error("Erro ao buscar Conta VoIP:", error);
    }
};

const handleEdit = async (outbounds) => {
    await fetchEdit(outbounds.id);
    editModalOpen.value = true;
};


const handleDelete = (outbounds) => {
    deleteItem(outbounds.id);

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
            const { data } = await axios.delete(`/outboundroutes/delete/${id}`);
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
