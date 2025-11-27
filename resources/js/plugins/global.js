// Libraries
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';

// ATOMS
import Card from '@/Layouts/atoms/Card.vue';
import Button from '@/Layouts/atoms/Button.vue';
import Input from '@/Layouts/atoms/Input.vue';
import Select from '@/Layouts/atoms/Select.vue';
import TableHeader from '@/Layouts/atoms/TableHeader.vue';
import TextInput from '@/Layouts/atoms/TextInput.vue';
import Row from '@/Layouts/atoms/Row.vue';


// MOLECULES
import DataTableHeader from '@/Layouts/molecules/DataTableHeader.vue';
import TableRow from '@/Layouts/molecules/TableRow.vue';
import ModalHeader from "@/Layouts/molecules/ModalHeader.vue";
import ModalBody from '@/Layouts/molecules/ModalBody.vue';
import ModalButtons from '@/Layouts/molecules/ModalButtons.vue';
import FormContainer from '@/Layouts/molecules/FormContainer.vue';

// ORGANISMS
import DataTable from '@/Layouts/organisms/DataTable.vue';
import Modal from '@/Layouts/organisms/Modal.vue';
import Extension from '@/Layouts/organisms/Extension.vue';


// TEMPLATES
import AuthenticatedLayout from '@/Layouts/templates/AuthenticatedLayout.vue';





export default {
  components: {
    // Libraries
    Icon,
    Head,

    // ATOMS
    Card,
    Button,
    Input,
    Select,
    TableHeader,
    TextInput,
    Row,

    // MOLECULES
    DataTableHeader,
    TableRow,
    ModalHeader,
    ModalBody,
    ModalButtons,
    FormContainer,

    // ORGANISMS
    DataTable,
    Modal,
    Extension,

    // TEMPLATES
    AuthenticatedLayout,
  },
  setup() {
    return {
      ref,
    };
  }
};