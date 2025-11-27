<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-300">
                Update Password
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Ensure your account is using a long, random password to stay
                secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <Row>
                <Input required classInput="h-10" placeholder="Senha" label="Senha" type="password"
                    v-model="form.current_password" />
            </Row>

            <Row>
                <Input required classInput="h-10" placeholder="Nova Senha" label="Nova Senha" type="password"
                    v-model="form.password" />
            </Row>

            <Row>
                <Input required classInput="h-10" placeholder="Confirme a Senha" label="Confirme a Senha"
                    type="password" v-model="form.password_confirmation" />
            </Row>

            <div class="flex items-center gap-4">
                <Button :disabled="form.processing"
                    buttonClass="d-flex justify-center items-center gap-2 bg-primary-brazuka"
                    textColor="dark:text-gray-200 text-white">Save</Button>
            </div>
        </form>
    </section>
</template>
