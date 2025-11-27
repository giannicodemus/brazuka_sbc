<template>
  <div
    class="fixed bottom-0 right-4 shadow-lg rounded-t-lg overflow-hidden dark:bg-neutral-600 bg-blue-800 transition-transform duration-300"
    :class="[
      isOpen ? 'translate-y-0 w-56 h-90' : 'translate-y-[calc(100%-3rem)] w-56 h-90'
    ]" @click="!isOpen && toggleDialer">

    <!-- Quando fechado, mostrar só um ícone -->
    <div v-if="!isOpen" @click="toggleDialer"
      class="flex justify-center items-center h-12 cursor-pointer bg-blue-800 dark:bg-neutral-600">
      <Icon icon="mdi:phone" class="text-white text-2xl" />
    </div>

    <div v-else>
      <!-- Cabeçalho -->
      <div class="relative flex justify-center items-center py-10 pb-7">
        <img src="/images/logo-retangular-dark.png" alt="logo" class="w-[70%]" />
        <button @click.stop="showConfig = true" class="absolute right-2 top-4 -translate-y-1/2 text-white">
          <Icon icon="icon-park-outline:config" />
        </button>
        <button @click.stop="toggleDialer" class="absolute left-2 top-4 -translate-y-1/2 text-white">
          <Icon icon="mdi:chevron-down" />
        </button>
      </div>
      <!-- Campo número discado / Chamando -->
      <div class="px-2 mb-3">
        <!-- Ligação recebida -->
        <div v-if="incomingCall" class="text-center text-black dark:text-white mb-2">
          <div class="font-semibold text-base mb-3">Ligação de {{ incomingNumber }}</div>
          <div class="flex flex-col gap-2">
            <Button @click="answerCall" icon="material-symbols:call"
              bgColor="bg-green-700 hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-600"
              class="text-white py-2 rounded-md text-sm font-semibold">
              Atender
            </Button>
            <Button @click="rejectCall" icon="material-symbols:call-end"
              bgColor="bg-red-700 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600"
              class="text-white py-2 rounded-md text-sm font-semibold">
              Recusar
            </Button>
          </div>
        </div>

        <!-- Chamando alguém -->
        <div v-else-if="callStatus === 'Ligando...'" class="text-center text-white dark:text-white mb-3">
          <div class="font-semibold text-base mb-3">Chamando {{ activeNumber }}</div>
        </div>

        <!-- Em chamada -->
        <div v-else-if="inCall" class="text-center text-white dark:text-white">
          <div class="font-semibold text-base mb-3">Em chamada com {{ activeNumber }}</div>

          <!-- Teclado para envio DTMF -->
          <div
            class="grid grid-cols-3 rounded-lg overflow-hidden border border-gray-300 divide-x divide-y divide-gray-300">
            <button v-for="(num, i) in numbers" :key="i" @click="sendDTMF(num)"
              class="w-full h-12 bg-white text-black font-semibold focus:outline-none">
              {{ num }}
            </button>
          </div>

          <Button @click="hangup" icon="material-symbols:call-end"
            bgColor="bg-red-700 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600"
            class="text-white py-2 rounded-md text-sm font-semibold w-full mt-5">
            Encerrar
          </Button>
        </div>

        <!-- Input e discador quando não há chamada ativa nem ligando -->
        <div v-else>
          <!-- Input número discado -->
          <input v-model="dialedNumber" class="w-full rounded-md text-center py-2 text-gray-700 font-medium mb-3"
            placeholder="Digite o número" readonly />

          <!-- Discador -->
          <div
            class="grid grid-cols-3 rounded-lg overflow-hidden border border-gray-300 divide-x divide-y divide-gray-300">
            <button v-for="(num, i) in numbers" :key="i" @click="dial(num)"
              class="w-full h-12 bg-white text-black font-semibold focus:outline-none">
              {{ num }}
            </button>
          </div>
        </div>
      </div>

      <!-- Botões ligar/apagar -->
      <div class="px-2 pb-3 flex gap-2" v-if="!inCall && !incomingCall && callStatus !== 'Ligando...'">
        <button @click="makeCall"
          class="bg-green-600 hover:bg-green-500 text-white flex-1 py-2 rounded font-bold flex items-center justify-center gap-1">
          <Icon icon="material-symbols:call-sharp" />
          LIGAR
        </button>
        <button @click="backspace"
          class="bg-red-600 hover:bg-red-500 text-white w-12 py-2 rounded flex items-center justify-center">
          <Icon icon="material-symbols:backspace-outline-rounded" />
        </button>
      </div>


    </div>

    <!-- Modal Config -->
    <Modal v-model:open="showConfig" title="Configurações SIP">
      <div class="flex flex-col gap-2 p-4">
        <FormContainer>
          <Row>
            <Input required classInput="h-10" placeholder="Servidor" label="Servidor" v-model="config.server" />
          </Row>
          <Row>
            <Input required classInput="h-10" placeholder="Usuário" label="Usuário" v-model="config.user" />
          </Row>
          <Row>
            <Input required classInput="h-10" placeholder="Senha" label="Senha" v-model="config.password"
              type="password" />
          </Row>
        </FormContainer>

        <ModalButtons @close="showConfig = false" @click="saveConfig" />
      </div>
    </Modal>
  </div>
</template>

<script>
import JsSIP from 'jssip';

let ua = null;
let session = null;
let ringtone = null;

export default {
  data() {
    return {
      isOpen: false,
      dialedNumber: '',
      numbers: [1, 2, 3, 4, 5, 6, 7, 8, 9, '*', 0, '#'],
      callStatus: '',
      incomingCall: null,
      incomingNumber: '',
      sipHost: localStorage.getItem('sipServer') || '',
      sipUser: localStorage.getItem('sipUser') || '',
      sipPass: localStorage.getItem('sipPassword') || '',
      activeNumber: '',
      showConfig: false,
      config: {
        server: localStorage.getItem('sipServer') || '',
        user: localStorage.getItem('sipUser') || '',
        password: localStorage.getItem('sipPassword') || ''
      }
    };
  },

  computed: {
    inCall() {
      return this.callStatus === 'Chamada atendida.';
    },
    hasIncomingCall() {
      return this.incomingCall !== null;
    }
  },

  mounted() {
    ringtone = new Audio('/audio/ringtone.mp3');
    ringtone.loop = true;

    this.initJsSIP();

  },

  methods: {
    toggleDialer() {
      this.isOpen = !this.isOpen;
      this.$nextTick(() => {
        if (this.isOpen) this.$refs.dialerDisplay?.focus();
      });
    },

    toggleConfig() {
      this.showConfig = !this.showConfig;
    },

    saveConfig() {
      localStorage.setItem('sipServer', this.config.server);
      localStorage.setItem('sipUser', this.config.user);
      localStorage.setItem('sipPassword', this.config.password);

      this.sipHost = this.config.server;
      this.sipUser = this.config.user;
      this.sipPass = this.config.password;

      if (ua) {
        ua.stop();
        ua = null;
      }

      this.initJsSIP();
      this.showConfig = false;
    },

    initJsSIP() {
      if (!this.sipUser || !this.sipHost || !this.sipPass) {
        console.error('[JsSIP] Dados SIP ausentes', {
          sipUser: this.sipUser,
          sipHost: this.sipHost,
          sipPass: this.sipPass
        });
        return;
      }

      const sipWsUri = `ws://${this.sipHost.split(':')[0]}:8088/asterisk/ws`;
      const sipUri = `sip:${this.sipUser}@${this.sipHost}`;
      console.log('[JsSIP] Iniciando UA com', { sipUri, sipPass: this.sipPass, sipWsUri });

      const socket = new JsSIP.WebSocketInterface(sipWsUri);

      const configuration = {
        uri: sipUri,
        password: this.sipPass,
        sockets: [socket],
        session_timers: false,
        register: true
      };

      ua = new JsSIP.UA(configuration);

      ua.on('connecting', () => console.log('[JsSIP] Conectando ao servidor WebSocket...'));
      ua.on('connected', () => console.log('[JsSIP] Conectado ao servidor WebSocket.'));
      ua.on('disconnected', () => console.warn('[JsSIP] Desconectado do servidor WebSocket.'));
      ua.on('registered', () => console.log('[JsSIP] Registrado com sucesso no SIP.'));
      ua.on('unregistered', () => console.warn('[JsSIP] UA não está mais registrada.'));
      ua.on('registrationFailed', (e) => console.error('[JsSIP] Falha ao registrar:', e.cause));

      ua.on('newRTCSession', (data) => {
        const newSession = data.session;

        if (newSession.direction === 'incoming') {
          this.incomingCall = newSession;
          this.incomingNumber = newSession.remote_identity.uri.user;
          this.isOpen = true;

          if (ringtone) ringtone.play();

          newSession.on('ended', () => {
            this.resetCall();
            if (ringtone) ringtone.pause();
            if (ringtone) ringtone.currentTime = 0;
          });

          newSession.on('failed', () => {
            this.resetCall();
            if (ringtone) ringtone.pause();
            if (ringtone) ringtone.currentTime = 0;
          });

          newSession.on('confirmed', () => {
            this.callStatus = 'Chamada atendida.';
            if (ringtone) ringtone.pause();
            if (ringtone) ringtone.currentTime = 0;
          });
        }
      });


      ua.start();
    },

    resetCall() {
      this.callStatus = '';
      this.incomingCall = null;
      this.incomingNumber = '';
      this.activeNumber = '';
      session = null;
    },

    dial(num) {
      this.dialedNumber += num;
    },

    backspace() {
      this.dialedNumber = this.dialedNumber.slice(0, -1);
    },

    makeCall() {
      if (!ua || !ua.isRegistered()) {
        console.warn('[JsSIP] UA não registrada');
        return;
      }

      if (!this.dialedNumber) {
        console.warn('[JsSIP] Número de destino vazio');
        return;
      }

      const target = `sip:${this.dialedNumber}@${this.sipHost}`;
      const ref = this;

      const eventHandlers = {
        progress() {
          ref.callStatus = 'Ligando...';
        },
        failed() { ref.resetCall(); console.warn('[JsSIP] Chamada falhou'); },
        ended() { ref.resetCall(); console.log('[JsSIP] Chamada finalizada'); },
        confirmed() { ref.callStatus = 'Chamada atendida.'; }
      };


      this.callStatus = 'Ligando...';
      this.activeNumber = this.dialedNumber;

      session = ua.call(target, { eventHandlers, mediaConstraints: { audio: true } });

      const remoteAudio = new Audio();
      session.connection.ontrack = (e) => {
        remoteAudio.srcObject = e.streams[0];
        remoteAudio.play();
      };

      this.dialedNumber = '';
    },

    answerCall() {
      if (this.incomingCall) {
        this.incomingCall.answer({ mediaConstraints: { audio: true } });
        const remoteAudio = new Audio();
        this.incomingCall.connection.ontrack = (e) => remoteAudio.srcObject = e.streams[0];
        this.activeNumber = this.incomingNumber;
        session = this.incomingCall;
        this.incomingCall = null;
        this.incomingNumber = '';
        this.callStatus = 'Chamada atendida.';
      }
    },

    rejectCall() {
      if (this.incomingCall) {
        this.incomingCall.terminate();
        this.resetCall();
      }
    },

    hangup() {
      if (session) {
        session.terminate();
        this.resetCall();
      }
    },

    sendDTMF(num) {
      if (session) session.sendDTMF(num);
    }
  }
};
</script>
