<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-300">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>


        <Button @click="confirmUserDeletion" icon="material-symbols:delete-rounded" type="button"
            bgColor="bg-red-600 hover:bg-red-500">
            Deletar Conta
        </Button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-300">
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <Row class="mt-6">
                    <Input required classInput="h-10" placeholder="Senha" label="Senha" type="password"
                        v-model="form.password" />
                </Row>

                <div class="mt-6 flex justify-end gap-4">
                    <Button @click="closeModal" icon="material-symbols:arrow-back-ios-new-rounded" type="button"
                        bgColor="bg-yellow-600 hover:bg-yellow-500">
                        Voltar
                    </Button>

                    <Button icon="material-symbols:delete-rounded" type="button" bgColor="bg-red-600 hover:bg-red-500"
                        @click="deleteUser">
                        Deletar
                    </Button>

                </div>
            </div>
        </Modal>
    </section>
</template>
